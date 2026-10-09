<!-- PHOTO VIEWER & ZOOM MODAL (LIGHTBOX) -->
<div id="globalPhotoModal" class="fixed inset-0 z-[9999] hidden select-none transition-opacity duration-200" role="dialog" aria-modal="true" aria-labelledby="photoModalTitle">
    <!-- Backdrop with blur -->
    <div id="photoModalBackdrop" class="fixed inset-0 bg-slate-950/85 backdrop-blur-md transition-opacity cursor-pointer"></div>

    <!-- Modal Content Layout -->
    <div class="fixed inset-0 flex flex-col justify-between pointer-events-none p-3 sm:p-5">

        <!-- Top Header & Controls Toolbar -->
        <div class="pointer-events-auto flex items-center justify-between gap-3 bg-slate-900/95 border border-white/10 rounded-2xl px-4 py-2.5 text-white shadow-2xl backdrop-blur-md max-w-4xl mx-auto w-full z-20">
            <!-- Title & Subtitle -->
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-red-600/30 border border-red-500/40 text-red-300 flex items-center justify-center flex-shrink-0">
                    <i class="bi bi-person-bounding-box text-lg"></i>
                </div>
                <div class="min-w-0">
                    <h4 id="photoModalTitle" class="text-xs sm:text-sm font-bold text-white truncate leading-tight">Foto Profil</h4>
                    <p id="photoModalSubtitle" class="text-[10px] sm:text-[11px] text-slate-300 truncate leading-tight mt-0.5 font-mono"></p>
                </div>
            </div>

            <!-- Toolbar Controls -->
            <div class="flex items-center gap-1 sm:gap-1.5 flex-shrink-0">
                <!-- Zoom Level Indicator -->
                <span id="photoModalZoomLevel" class="hidden sm:inline-flex items-center px-2 py-1 rounded-lg bg-white/10 text-slate-200 text-xs font-mono font-bold mr-1">
                    100%
                </span>

                <!-- Zoom In -->
                <button type="button" id="photoModalZoomInBtn" class="p-2 rounded-xl bg-white/10 hover:bg-white/20 active:bg-white/30 text-white transition flex items-center justify-center text-sm" title="Perbesar (+) / Scroll Atas">
                    <i class="bi bi-zoom-in"></i>
                </button>

                <!-- Zoom Out -->
                <button type="button" id="photoModalZoomOutBtn" class="p-2 rounded-xl bg-white/10 hover:bg-white/20 active:bg-white/30 text-white transition flex items-center justify-center text-sm" title="Perkecil (-) / Scroll Bawah">
                    <i class="bi bi-zoom-out"></i>
                </button>

                <!-- Reset Zoom -->
                <button type="button" id="photoModalResetBtn" class="p-2 rounded-xl bg-white/10 hover:bg-white/20 active:bg-white/30 text-white transition flex items-center justify-center text-sm" title="Reset Ukuran (100% / R)">
                    <i class="bi bi-arrows-angle-contract"></i>
                </button>

                <!-- Rotate -->
                <button type="button" id="photoModalRotateBtn" class="p-2 rounded-xl bg-white/10 hover:bg-white/20 active:bg-white/30 text-white transition flex items-center justify-center text-sm" title="Putar 90°">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>

                <div class="h-5 w-px bg-white/20 mx-1 hidden sm:block"></div>

                <!-- Open Original in New Tab -->
                <a id="photoModalExternalLink" href="#" target="_blank" rel="noopener noreferrer" class="p-2 rounded-xl bg-white/10 hover:bg-white/20 active:bg-white/30 text-white transition flex items-center justify-center text-sm" title="Buka Gambar Penuh di Tab Baru">
                    <i class="bi bi-box-arrow-up-right"></i>
                </a>

                <!-- Download -->
                <a id="photoModalDownloadBtn" href="#" download class="p-2 rounded-xl bg-white/10 hover:bg-white/20 active:bg-white/30 text-white transition flex items-center justify-center text-sm" title="Unduh Foto">
                    <i class="bi bi-download"></i>
                </a>

                <!-- Close Button -->
                <button type="button" id="photoModalCloseBtn" class="ml-1 p-2 rounded-xl bg-red-600/80 hover:bg-red-600 active:bg-red-700 text-white transition flex items-center justify-center text-sm" title="Tutup (Esc)">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>

        <!-- Center Image Viewport -->
        <div id="photoModalViewport" class="pointer-events-auto relative flex-1 flex items-center justify-center overflow-hidden my-2 sm:my-3 select-none touch-none">
            <!-- Loading Spinner -->
            <div id="photoModalLoading" class="absolute inset-0 flex items-center justify-center text-white z-0 pointer-events-none">
                <div class="flex flex-col items-center gap-2 bg-slate-900/60 px-5 py-4 rounded-2xl backdrop-blur-sm border border-white/10">
                    <div class="w-8 h-8 border-4 border-white/20 border-t-red-500 rounded-full animate-spin"></div>
                    <span class="text-xs text-slate-300 font-medium">Memuat foto...</span>
                </div>
            </div>

            <!-- Error message if image cannot load -->
            <div id="photoModalError" class="hidden absolute inset-0 flex items-center justify-center text-white z-0 pointer-events-none">
                <div class="flex flex-col items-center gap-2 bg-slate-900/80 px-6 py-5 rounded-2xl backdrop-blur-sm border border-white/10 max-w-sm text-center pointer-events-auto">
                    <i class="bi bi-exclamation-triangle text-amber-400 text-3xl"></i>
                    <span class="text-sm font-bold text-white">Gagal Memuat Foto</span>
                    <span class="text-xs text-slate-300">Berkas foto tidak ditemukan atau link tidak valid.</span>
                    <a id="photoModalErrorLink" href="#" target="_blank" class="mt-2 text-xs text-red-400 hover:text-red-300 underline">Coba Buka Tautan Asli</a>
                </div>
            </div>

            <!-- Image Element -->
            <img id="photoModalImage" src="" alt="Foto Profil" class="max-h-[75vh] max-w-[90vw] object-contain rounded-2xl shadow-2xl z-10 opacity-0" style="transform-origin: center center;" draggable="false">
        </div>

        <!-- Bottom Footer Information & Helper Tips -->
        <div class="pointer-events-auto flex items-center justify-between gap-3 bg-slate-900/90 border border-white/10 rounded-2xl px-4 py-2 text-white shadow-xl backdrop-blur-md max-w-2xl mx-auto w-full text-[11px] text-slate-300 z-20">
            <div class="flex items-center gap-1.5 truncate">
                <i class="bi bi-info-circle text-red-400 flex-shrink-0"></i>
                <span class="truncate">Scroll mouse / tombol +/- untuk zoom • Geser saat di-zoom • Dobel klik untuk zoom 2x</span>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <a id="photoModalExternalTextLink" href="#" target="_blank" rel="noopener noreferrer" class="text-red-400 hover:text-red-300 font-semibold underline underline-offset-2 flex items-center gap-1">
                    <span>Buka Penuh</span>
                    <i class="bi bi-arrow-up-right text-[10px]"></i>
                </a>
            </div>
        </div>

    </div>
</div>

<script>
(function() {
    let scale = 1;
    let rotation = 0;
    let translateX = 0;
    let translateY = 0;
    let isDragging = false;
    let startX = 0;
    let startY = 0;
    let hasMoved = false;

    // Touch variables
    let initialTouchDistance = null;
    let initialTouchScale = 1;
    let lastTouchX = 0;
    let lastTouchY = 0;

    const modal         = document.getElementById('globalPhotoModal');
    const backdrop      = document.getElementById('photoModalBackdrop');
    const viewport      = document.getElementById('photoModalViewport');
    const img           = document.getElementById('photoModalImage');
    const loadingEl     = document.getElementById('photoModalLoading');
    const errorEl       = document.getElementById('photoModalError');
    const errorLink     = document.getElementById('photoModalErrorLink');
    const titleEl       = document.getElementById('photoModalTitle');
    const subtitleEl    = document.getElementById('photoModalSubtitle');
    const zoomLevelEl   = document.getElementById('photoModalZoomLevel');
    const extLink       = document.getElementById('photoModalExternalLink');
    const extTextLink   = document.getElementById('photoModalExternalTextLink');
    const downloadBtn   = document.getElementById('photoModalDownloadBtn');
    const closeBtn      = document.getElementById('photoModalCloseBtn');
    const zoomInBtn     = document.getElementById('photoModalZoomInBtn');
    const zoomOutBtn    = document.getElementById('photoModalZoomOutBtn');
    const resetBtn      = document.getElementById('photoModalResetBtn');
    const rotateBtn     = document.getElementById('photoModalRotateBtn');

    function applyTransform(withTransition = true) {
        if (!img) return;
        img.style.transition = withTransition ? 'transform 120ms ease-out' : 'none';
        img.style.transform = `translate(${translateX}px, ${translateY}px) scale(${scale}) rotate(${rotation}deg)`;
        if (zoomLevelEl) {
            zoomLevelEl.textContent = `${Math.round(scale * 100)}%`;
        }
        if (viewport) {
            if (scale > 1) {
                viewport.style.cursor = isDragging ? 'grabbing' : 'grab';
            } else {
                viewport.style.cursor = 'default';
            }
        }
    }

    function resetTransforms() {
        scale = 1;
        rotation = 0;
        translateX = 0;
        translateY = 0;
        applyTransform(true);
    }

    function zoomIn(step = 0.25) {
        scale = Math.min(+(scale + step).toFixed(2), 4.5);
        applyTransform(true);
    }

    function zoomOut(step = 0.25) {
        scale = Math.max(+(scale - step).toFixed(2), 0.5);
        if (scale <= 1) {
            translateX = 0;
            translateY = 0;
        }
        applyTransform(true);
    }

    function rotate() {
        rotation = (rotation + 90) % 360;
        applyTransform(true);
    }

    window.openPhotoModal = function(url, title = 'Foto Profil', subtitle = '') {
        if (!modal || !img || !url) return;

        titleEl.textContent = title || 'Foto Profil';
        subtitleEl.textContent = subtitle || '';
        subtitleEl.style.display = subtitle ? 'block' : 'none';

        extLink.href = url;
        extTextLink.href = url;
        downloadBtn.href = url;
        if (errorLink) errorLink.href = url;

        // Try extracting filename from url for download attribute
        try {
            const parsed = new URL(url, window.location.origin);
            const pathParts = parsed.pathname.split('/');
            const filename = pathParts[pathParts.length - 1];
            if (filename && filename.includes('.')) {
                downloadBtn.setAttribute('download', filename);
            } else {
                downloadBtn.setAttribute('download', (title ? title.replace(/[^a-zA-Z0-9_-]/g, '_') : 'foto') + '.jpg');
            }
        } catch(e) {
            downloadBtn.setAttribute('download', 'foto-profil.jpg');
        }

        resetTransforms();

        img.classList.add('opacity-0');
        if (loadingEl) loadingEl.classList.remove('hidden');
        if (errorEl) errorEl.classList.add('hidden');

        img.onload = function() {
            if (loadingEl) loadingEl.classList.add('hidden');
            img.classList.remove('opacity-0');
        };

        img.onerror = function() {
            if (loadingEl) loadingEl.classList.add('hidden');
            if (errorEl) errorEl.classList.remove('hidden');
        };

        img.src = url;

        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };

    window.openPhotoViewer = window.openPhotoModal;

    window.closePhotoModal = function() {
        if (!modal) return;
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        if (img) {
            img.src = '';
            img.classList.add('opacity-0');
        }
        resetTransforms();
    };

    // Close listeners
    if (closeBtn) closeBtn.addEventListener('click', window.closePhotoModal);
    if (backdrop) backdrop.addEventListener('click', window.closePhotoModal);

    // Toolbar buttons
    if (zoomInBtn) zoomInBtn.addEventListener('click', () => zoomIn(0.25));
    if (zoomOutBtn) zoomOutBtn.addEventListener('click', () => zoomOut(0.25));
    if (resetBtn) resetBtn.addEventListener('click', resetTransforms);
    if (rotateBtn) rotateBtn.addEventListener('click', rotate);

    // Mouse wheel zoom
    if (viewport) {
        viewport.addEventListener('wheel', function(e) {
            e.preventDefault();
            if (e.deltaY < 0) {
                zoomIn(0.2);
            } else {
                zoomOut(0.2);
            }
        }, { passive: false });

        // Double click to toggle zoom
        viewport.addEventListener('dblclick', function(e) {
            e.preventDefault();
            if (scale > 1) {
                resetTransforms();
            } else {
                scale = 2;
                applyTransform(true);
            }
        });

        // Click outside image on viewport background to close when not zoomed
        viewport.addEventListener('click', function(e) {
            if (e.target === viewport && !hasMoved) {
                window.closePhotoModal();
            }
        });

        // Mouse Drag / Pan
        viewport.addEventListener('mousedown', function(e) {
            if (e.button !== 0) return; // Only left click
            hasMoved = false;
            if (scale > 1) {
                isDragging = true;
                startX = e.clientX - translateX;
                startY = e.clientY - translateY;
                viewport.style.cursor = 'grabbing';
            }
        });

        // Touch Pinch & Pan
        viewport.addEventListener('touchstart', function(e) {
            hasMoved = false;
            if (e.touches.length === 1) {
                if (scale > 1) isDragging = true;
                lastTouchX = e.touches[0].clientX;
                lastTouchY = e.touches[0].clientY;
                startX = lastTouchX - translateX;
                startY = lastTouchY - translateY;
            } else if (e.touches.length === 2) {
                isDragging = false;
                const dx = e.touches[0].clientX - e.touches[1].clientX;
                const dy = e.touches[0].clientY - e.touches[1].clientY;
                initialTouchDistance = Math.hypot(dx, dy);
                initialTouchScale = scale;
            }
        }, { passive: true });

        viewport.addEventListener('touchmove', function(e) {
            hasMoved = true;
            if (e.touches.length === 1 && isDragging && scale > 1) {
                e.preventDefault();
                translateX = e.touches[0].clientX - startX;
                translateY = e.touches[0].clientY - startY;
                applyTransform(false);
            } else if (e.touches.length === 2 && initialTouchDistance) {
                e.preventDefault();
                const dx = e.touches[0].clientX - e.touches[1].clientX;
                const dy = e.touches[0].clientY - e.touches[1].clientY;
                const currentDistance = Math.hypot(dx, dy);
                const factor = currentDistance / initialTouchDistance;
                scale = Math.min(Math.max(+(initialTouchScale * factor).toFixed(2), 0.5), 4.5);
                applyTransform(false);
            }
        }, { passive: false });

        viewport.addEventListener('touchend', function(e) {
            if (e.touches.length < 2) {
                initialTouchDistance = null;
            }
            if (e.touches.length === 0) {
                isDragging = false;
                applyTransform(true);
            }
        }, { passive: true });
    }

    window.addEventListener('mousemove', function(e) {
        if (!isDragging) return;
        hasMoved = true;
        translateX = e.clientX - startX;
        translateY = e.clientY - startY;
        applyTransform(false);
    });

    window.addEventListener('mouseup', function() {
        if (isDragging) {
            isDragging = false;
            applyTransform(true);
        }
    });

    // Keyboard Shortcuts
    window.addEventListener('keydown', function(e) {
        if (!modal || modal.classList.contains('hidden')) return;

        if (e.key === 'Escape') {
            window.closePhotoModal();
        } else if (e.key === '+' || e.key === '=') {
            e.preventDefault();
            zoomIn(0.25);
        } else if (e.key === '-' || e.key === '_') {
            e.preventDefault();
            zoomOut(0.25);
        } else if (e.key === '0' || e.key === 'r' || e.key === 'R') {
            if (e.key === 'R' && e.shiftKey) {
                rotate();
            } else {
                resetTransforms();
            }
        }
    });
})();
</script>
