/**
 * PWA Registration & Mobile App Install Prompt Handler
 * Pemuda MTA Perwakilan Sragen
 */

(function () {
    'use strict';

    // 1. Register Service Worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
                .then((registration) => {
                    // Service worker registered successfully
                })
                .catch((err) => {
                    console.debug('SW registration notice:', err);
                });
        });
    }

    // 2. Check if already running in standalone (Installed PWA) mode
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches || 
                         window.navigator.standalone === true;

    if (isStandalone) {
        document.documentElement.classList.add('pwa-standalone');
        return;
    }

    let deferredPrompt = null;
    const DISMISS_KEY = 'pmd_pwa_install_dismissed';
    const DISMISS_DURATION_DAYS = 7;

    function isDismissedRecently() {
        const dismissedAt = localStorage.getItem(DISMISS_KEY);
        if (!dismissedAt) return false;
        const days = (Date.now() - parseInt(dismissedAt, 10)) / (1000 * 60 * 60 * 24);
        return days < DISMISS_DURATION_DAYS;
    }

    function recordDismissal() {
        localStorage.setItem(DISMISS_KEY, Date.now().toString());
    }

    // 3. Create and inject PWA Install Banner UI
    function createInstallBanner() {
        if (document.getElementById('pwa-install-banner')) return;

        const banner = document.createElement('div');
        banner.id = 'pwa-install-banner';
        banner.className = 'pwa-install-banner shadow-lg animate-slide-up';
        banner.innerHTML = `
            <div class="d-flex align-items-center gap-3 p-3">
                <img src="/icons/icon-96x96.png" alt="Pemuda MTA" class="pwa-banner-icon rounded-3 shadow-sm" width="46" height="46">
                <div class="flex-grow-1 min-w-0">
                    <h6 class="fw-bold mb-0 text-dark text-truncate" style="font-size: 0.95rem;">Pasang Aplikasi Pemuda MTA</h6>
                    <small class="text-muted d-block" style="font-size: 0.78rem;">Akses cepat, ringan &amp; praktis langsung dari layar HP Anda</small>
                </div>
                <div class="d-flex align-items-center gap-1 flex-shrink-0">
                    <button type="button" id="pwa-btn-install" class="btn btn-sm btn-danger fw-bold rounded-pill px-3 py-1 shadow-sm" style="font-size: 0.8rem; background: linear-gradient(135deg, #991b1b, #dc2626); border: none;">
                        <i class="bi bi-download me-1"></i> Pasang
                    </button>
                    <button type="button" id="pwa-btn-dismiss" class="btn btn-sm btn-light rounded-circle text-muted" style="width: 32px; height: 32px; padding: 0;" aria-label="Tutup">
                        <i class="bi bi-x-lg" style="font-size: 0.75rem;"></i>
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(banner);

        // Bind Install Action
        document.getElementById('pwa-btn-install').addEventListener('click', async () => {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                deferredPrompt = null;
                banner.remove();
            } else if (isIosSafari()) {
                showIosInstructions();
            }
        });

        // Bind Dismiss Action
        document.getElementById('pwa-btn-dismiss').addEventListener('click', () => {
            recordDismissal();
            banner.classList.add('animate-slide-down');
            setTimeout(() => banner.remove(), 300);
        });
    }

    // iOS Detection
    function isIosSafari() {
        const ua = window.navigator.userAgent;
        const isIos = /iPad|iPhone|iPod/.test(ua) && !window.MSStream;
        const isSafari = /Safari/.test(ua) && !/CriOS|FxiOS|OPiOS|mercury/i.test(ua);
        return isIos && isSafari;
    }

    function showIosInstructions() {
        const existing = document.getElementById('pwa-ios-modal');
        if (existing) existing.remove();

        const modal = document.createElement('div');
        modal.id = 'pwa-ios-modal';
        modal.className = 'pwa-ios-sheet';
        modal.innerHTML = `
            <div class="pwa-ios-backdrop"></div>
            <div class="pwa-ios-content shadow-lg animate-slide-up">
                <div class="pwa-ios-drag-handle mx-auto mb-3"></div>
                <div class="text-center mb-3">
                    <img src="/icons/icon-96x96.png" class="rounded-3 shadow-sm mb-2" width="56" height="56" alt="Icon">
                    <h6 class="fw-bold mb-1">Pasang di Perangkat Apple (iOS)</h6>
                    <p class="text-muted small mb-0">Ikuti 2 langkah mudah berikut untuk menambahkan ke Layar Utama:</p>
                </div>
                <div class="ios-steps list-group list-group-flush small mb-3">
                    <div class="list-group-item d-flex align-items-center gap-3 px-2 py-2 border-0">
                        <span class="badge bg-danger rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">1</span>
                        <div>Ketuk tombol <strong>Bagikan / Share</strong> (<i class="bi bi-box-arrow-up text-primary fs-6"></i>) di bilah bawah peramban Safari Anda.</div>
                    </div>
                    <div class="list-group-item d-flex align-items-center gap-3 px-2 py-2 border-0">
                        <span class="badge bg-danger rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">2</span>
                        <div>Gulir ke bawah dan ketuk <strong>Tambahkan ke Layar Utama</strong> (<i class="bi bi-plus-square text-dark fs-6"></i> Add to Home Screen).</div>
                    </div>
                </div>
                <button type="button" class="btn btn-secondary w-100 rounded-pill py-2 small fw-semibold" id="pwa-ios-close">
                    Saya Mengerti
                </button>
            </div>
        `;

        document.body.appendChild(modal);

        modal.querySelector('.pwa-ios-backdrop').addEventListener('click', () => modal.remove());
        modal.querySelector('#pwa-ios-close').addEventListener('click', () => {
            recordDismissal();
            modal.remove();
        });
    }

    // 4. Capture beforeinstallprompt (Android / Chrome)
    window.addEventListener('beforeinstallprompt', (e) => {
        // Prevent immediate Chrome mini-infobar
        e.preventDefault();
        deferredPrompt = e;

        // Show our banner only on mobile devices if not recently dismissed
        if (window.innerWidth <= 768 && !isDismissedRecently()) {
            // Delay slightly for smooth page load
            setTimeout(createInstallBanner, 2500);
        }
    });

    // 5. iOS Safari fallback banner trigger
    if (isIosSafari() && window.innerWidth <= 768 && !isDismissedRecently()) {
        setTimeout(createInstallBanner, 3000);
    }

    // 6. Global trigger for any "Pasang Aplikasi" buttons
    window.triggerPwaInstall = function () {
        if (deferredPrompt) {
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then(() => {
                deferredPrompt = null;
                const b = document.getElementById('pwa-install-banner');
                if (b) b.remove();
            });
        } else if (isIosSafari()) {
            showIosInstructions();
        } else {
            alert('Untuk memasang aplikasi, buka menu peramban Anda (titik tiga ⋮) dan pilih "Tambahkan ke Layar Utama" atau "Install Aplikasi".');
        }
    };
})();
