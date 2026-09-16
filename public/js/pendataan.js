/**
 * Sistem Pendataan Pemuda Kabupaten Sragen
 * Pendataan Form Interactive Logic with Tom Select Searchable Dropdowns
 */

// Master data desa/kelurahan per kecamatan di Sragen dengan ID Database yang akurat
const sragenVillages = {
    "1": [
        { id: 1, name: "Sragen Wetan" }, { id: 2, name: "Sragen Kulon" }, { id: 3, name: "Sragen Tengah" },
        { id: 4, name: "Nglorog" }, { id: 5, name: "Sine" }, { id: 6, name: "Karangtengah" },
        { id: 7, name: "Kroyo" }, { id: 8, name: "Tangkil" }
    ],
    "2": [
        { id: 9, name: "Kujon" }, { id: 10, name: "Plumbungan" }, { id: 11, name: "Puro" },
        { id: 12, name: "Saradan" }, { id: 13, name: "Guworejo" }, { id: 14, name: "Mojorejo" },
        { id: 15, name: "Jurangjero" }, { id: 16, name: "Pelemgadung" }, { id: 17, name: "Kedungwaduk" },
        { id: 18, name: "Ngringkwit" }
    ],
    "3": [
        { id: 19, name: "Sidoharjo" }, { id: 20, name: "Jetak" }, { id: 21, name: "Purwosuman" },
        { id: 22, name: "Patihan" }, { id: 23, name: "Bentak" }, { id: 24, name: "Duyungan" },
        { id: 25, name: "Sribit" }, { id: 26, name: "Taraman" }, { id: 27, name: "Tenggak" },
        { id: 28, name: "Jambanan" }, { id: 29, name: "Pandak" }, { id: 30, name: "Singopadu" }
    ],
    "4": [
        { id: 31, name: "Gemolong" }, { id: 32, name: "Kwangen" }, { id: 33, name: "Ngembatpadas" },
        { id: 34, name: "Kragilan" }, { id: 35, name: "Jenalas" }, { id: 36, name: "Kaloran" },
        { id: 37, name: "Purworejo" }, { id: 38, name: "Peleman" }, { id: 39, name: "Brangkal" },
        { id: 40, name: "Tlogotirto" }, { id: 41, name: "Jatibatur" }, { id: 42, name: "Nganti" },
        { id: 43, name: "Kalenan" }
    ],
    "5": [
        { id: 44, name: "Kalijambe" }, { id: 45, name: "Banaran" }, { id: 46, name: "Donoyudan" },
        { id: 47, name: "Krikilan" }, { id: 48, name: "Ngetal" }, { id: 49, name: "Saren" },
        { id: 50, name: "Tegaldowo" }, { id: 51, name: "Trobayan" }, { id: 52, name: "Wonorejo" },
        { id: 53, name: "Bukuran" }, { id: 54, name: "Karangjati" }
    ],
    "6": [
        { id: 55, name: "Plupuh" }, { id: 56, name: "Dari" }, { id: 57, name: "Gedongan" },
        { id: 58, name: "Gentanbanaran" }, { id: 59, name: "Jabung" }, { id: 60, name: "Karanganyar" },
        { id: 61, name: "Karangwaru" }, { id: 62, name: "Krikil" }, { id: 63, name: "Manyarejo" },
        { id: 64, name: "Ngrombo" }, { id: 65, name: "Padas" }, { id: 66, name: "Sambirejo" },
        { id: 67, name: "Somomorodukuh" }
    ],
    "7": [
        { id: 68, name: "Masaran" }, { id: 69, name: "Dawungan" }, { id: 70, name: "Gebang" },
        { id: 71, name: "Jati" }, { id: 72, name: "Karangmalang" }, { id: 73, name: "Kliwonan" },
        { id: 74, name: "Krebet" }, { id: 75, name: "Pilang" }, { id: 76, name: "Pringanom" },
        { id: 77, name: "Sepat" }, { id: 78, name: "Sidodadi" }
    ],
    "8": [
        { id: 79, name: "Kedawung" }, { id: 80, name: "Bendungan" }, { id: 81, name: "Celep" },
        { id: 82, name: "Jatimulyo" }, { id: 83, name: "Karangpelem" }, { id: 84, name: "Mojokerto" },
        { id: 85, name: "Pengkok" }, { id: 86, name: "Wonokerso" }, { id: 87, name: "Wonorejo" }
    ],
    "9": [
        { id: 88, name: "Sambirejo" }, { id: 89, name: "Blimbing" }, { id: 90, name: "Dawung" },
        { id: 91, name: "Jambeyan" }, { id: 92, name: "Jetis" }, { id: 93, name: "Musuk" },
        { id: 94, name: "Sukorejo" }
    ],
    "10": [
        { id: 95, name: "Gondang" }, { id: 96, name: "Banyurip" }, { id: 97, name: "Glonggong" },
        { id: 98, name: "Kaliwedi" }, { id: 99, name: "Plosorejo" }, { id: 100, name: "Tegalrejo" },
        { id: 101, name: "Tunggul" }, { id: 102, name: "Wonotolo" }
    ],
    "11": [
        { id: 103, name: "Sambungmacan" }, { id: 104, name: "Banaran" }, { id: 105, name: "Bedoro" },
        { id: 106, name: "Cemeng" }, { id: 107, name: "Gringging" }, { id: 108, name: "Karanganyar" },
        { id: 109, name: "Plumbon" }, { id: 110, name: "Toyogo" }
    ],
    "12": [
        { id: 111, name: "Ngrampal" }, { id: 112, name: "Bener" }, { id: 113, name: "Gabus" },
        { id: 114, name: "Karangudi" }, { id: 115, name: "Kebonromo" }, { id: 116, name: "Klandungan" },
        { id: 117, name: "Pilangsari" }, { id: 118, name: "Ngarum" }
    ],
    "13": [
        { id: 119, name: "Tanon" }, { id: 120, name: "Bonagung" }, { id: 121, name: "Gading" },
        { id: 122, name: "Gentan" }, { id: 123, name: "Kalikobok" }, { id: 124, name: "Karangtalun" },
        { id: 125, name: "Karangasem" }, { id: 126, name: "Ketro" }, { id: 127, name: "Padas" },
        { id: 128, name: "Pengkol" }, { id: 129, name: "Sambiduwur" }, { id: 130, name: "Slogo" },
        { id: 131, name: "Suwatu" }
    ],
    "14": [
        { id: 132, name: "Sumberlawang" }, { id: 133, name: "Cepoko" }, { id: 134, name: "Hadiluwih" },
        { id: 135, name: "Jati" }, { id: 136, name: "Kacangan" }, { id: 137, name: "Mojopuro" },
        { id: 138, name: "Ngandul" }, { id: 139, name: "Ngargosari" }, { id: 140, name: "Ngargotirto" },
        { id: 141, name: "Pagak" }, { id: 142, name: "Pendem" }, { id: 143, name: "Tlogorejo" }
    ],
    "15": [
        { id: 144, name: "Mondokan" }, { id: 145, name: "Gemantar" }, { id: 146, name: "Jekawal" },
        { id: 147, name: "Kedawung" }, { id: 148, name: "Pare" }, { id: 149, name: "Sono" },
        { id: 150, name: "Sumberejo" }, { id: 151, name: "Tempelrejo" }, { id: 152, name: "Trombol" }
    ],
    "16": [
        { id: 153, name: "Sukodono" }, { id: 154, name: "Baleharjo" }, { id: 155, name: "Bendo" },
        { id: 156, name: "Gebang" }, { id: 157, name: "Jatitengah" }, { id: 158, name: "Juwok" },
        { id: 159, name: "Karang Anom" }, { id: 160, name: "Majenang" }, { id: 161, name: "Newung" },
        { id: 162, name: "Pantirejo" }
    ],
    "17": [
        { id: 163, name: "Gesi" }, { id: 164, name: "Blangu" }, { id: 165, name: "Poleng" },
        { id: 166, name: "Slendro" }, { id: 167, name: "Srawung" }, { id: 168, name: "Tanggan" }
    ],
    "18": [
        { id: 169, name: "Tangen" }, { id: 170, name: "Denanyar" }, { id: 171, name: "Dukuh" },
        { id: 172, name: "Galeh" }, { id: 173, name: "Katelan" }, { id: 174, name: "Ngrombo" },
        { id: 175, name: "Sigit" }
    ],
    "19": [
        { id: 176, name: "Jenar" }, { id: 177, name: "Banyurip" }, { id: 178, name: "Dawung" },
        { id: 179, name: "Japoh" }, { id: 180, name: "Kandangsapi" }, { id: 181, name: "Mlale" },
        { id: 182, name: "Ngepringan" }
    ],
    "20": [
        { id: 183, name: "Miri" }, { id: 184, name: "Bagor" }, { id: 185, name: "Doyong" },
        { id: 186, name: "Geneng" }, { id: 187, name: "Girimargo" }, { id: 188, name: "Jeruk" },
        { id: 189, name: "Soko" }, { id: 190, name: "Sunggingan" }, { id: 191, name: "Brojol" }
    ]
};

let currentStep = 1;
const totalSteps = 8;
let orgCount = 1;

// Tom Select Instances
let cabangTomSelect = null;
let districtTomSelect = null;
let villageTomSelect = null;

/**
 * Initialize Tom Select on Cabang, Kecamatan, and Desa
 */
function initSearchableSelects() {
    if (typeof TomSelect === 'undefined') return;

    // 1. Cabang Searchable Select
    const cabangEl = document.getElementById('cabang_id');
    if (cabangEl && !cabangTomSelect) {
        cabangTomSelect = new TomSelect(cabangEl, {
            create: false,
            placeholder: '-- Ketik nama cabang untuk mencari... --',
            allowEmptyOption: true,
            maxOptions: 100,
            searchField: ['text'],
            render: {
                no_results: function(data, escape) {
                    return '<div class="no-results text-muted small p-2"><i class="bi bi-search me-1"></i> Cabang tidak ditemukan</div>';
                }
            },
            onChange: function(val) {
                cabangEl.classList.remove('is-invalid');
                const tsWrap = cabangEl.nextElementSibling;
                if (tsWrap && tsWrap.classList.contains('ts-wrapper')) {
                    tsWrap.classList.remove('is-invalid');
                }
                const nameInput = document.getElementById('name');
                if (nameInput && nameInput.value.trim().length >= 2) {
                    nameInput.dispatchEvent(new Event('input'));
                }
            }
        });
    }

    // 2. Kecamatan Searchable Select
    const districtEl = document.getElementById('district_id');
    if (districtEl && !districtTomSelect) {
        districtTomSelect = new TomSelect(districtEl, {
            create: false,
            placeholder: '-- Ketik nama kecamatan untuk mencari... --',
            allowEmptyOption: true,
            render: {
                no_results: function(data, escape) {
                    return '<div class="no-results text-muted small p-2"><i class="bi bi-search me-1"></i> Kecamatan tidak ditemukan</div>';
                }
            },
            onChange: function(value) {
                handleDistrictChange(value);
                districtEl.classList.remove('is-invalid');
                const tsWrap = districtEl.nextElementSibling;
                if (tsWrap && tsWrap.classList.contains('ts-wrapper')) {
                    tsWrap.classList.remove('is-invalid');
                }
            }
        });
    }

    // 3. Desa / Kelurahan Searchable Select
    const villageEl = document.getElementById('village_id');
    if (villageEl && !villageTomSelect) {
        villageTomSelect = new TomSelect(villageEl, {
            create: false,
            placeholder: '-- Pilih kecamatan terlebih dahulu --',
            allowEmptyOption: true,
            render: {
                no_results: function(data, escape) {
                    return '<div class="no-results text-muted small p-2"><i class="bi bi-search me-1"></i> Desa tidak ditemukan</div>';
                }
            },
            onChange: function() {
                villageEl.classList.remove('is-invalid');
                const tsWrap = villageEl.nextElementSibling;
                if (tsWrap && tsWrap.classList.contains('ts-wrapper')) {
                    tsWrap.classList.remove('is-invalid');
                }
            }
        });
    }
}

const stepNames = [
    'Data Pribadi & Pengecekan',
    'Alamat Domisili',
    'Riwayat Pendidikan',
    'Status Pekerjaan & Karir',
    'Element Dakwah & Penugasan',
    'Keahlian & Keterampilan',
    'Minat & Pengembangan Diri',
    'Konfirmasi & Ringkasan Data'
];

function updateProgress(step) {
    const percentage = Math.round(((step - 1) / (totalSteps - 1)) * 100);
    const fillEl = document.getElementById('stepperProgressFill');
    if (fillEl) {
        fillEl.style.width = percentage + '%';
    }

    document.querySelectorAll('.stepper-item').forEach(item => {
        const itemStep = parseInt(item.getAttribute('data-step'), 10);
        item.classList.remove('active', 'completed');
        if (itemStep === step) {
            item.classList.add('active');
            // Scroll active step item horizontally into view on mobile
            if (window.innerWidth <= 768 && typeof item.scrollIntoView === 'function') {
                item.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
            }
        } else if (itemStep < step) {
            item.classList.add('completed');
        }
    });

    // Update Mobile Step Progress Header
    const mobileBadge   = document.getElementById('mobileStepBadge');
    const mobileName    = document.getElementById('mobileStepName');
    const mobilePercent = document.getElementById('mobileStepPercent');
    const mobileBar     = document.getElementById('mobileProgressBar');

    if (mobileBadge) mobileBadge.textContent = `Langkah ${step}/${totalSteps}`;
    if (mobileName) mobileName.textContent = stepNames[step - 1] || '';
    if (mobilePercent) mobilePercent.textContent = `${percentage}%`;
    if (mobileBar) mobileBar.style.width = `${Math.max(12.5, percentage)}%`;
}

/**
 * Helper to escape HTML characters safely
 */
function escapeHtml(str) {
    if (!str) return '';
    return str.toString()
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

/**
 * Helper to focus next field on Step 1 after checking data
 */
function focusNextField() {
    const birthPlaceEl = document.getElementById('birth_place');
    if (birthPlaceEl) {
        birthPlaceEl.focus();
        birthPlaceEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

/**
 * Manual trigger for Cek Data button
 */
function handleManualCheckData() {
    checkDataPemuda(true);
}

/**
 * Populates all form fields with existing youth data retrieved from database
 */
function populateExistingData(pemuda) {
    if (!pemuda) return;

    // 1. Existing ID & Identitas Utama
    const existIdEl = document.getElementById('existing_pemuda_id');
    if (existIdEl) {
        existIdEl.value = pemuda.id || '';
    }

    if (pemuda.name) {
        const nameEl = document.getElementById('name');
        if (nameEl) {
            nameEl.value = pemuda.name;
            nameEl.classList.remove('is-invalid');
        }
    }

    if (pemuda.gender) {
        const isMale = pemuda.gender.toUpperCase() === 'L';
        const radioL = document.getElementById('gender_l');
        const radioP = document.getElementById('gender_p');
        if (isMale && radioL) radioL.checked = true;
        if (!isMale && radioP) radioP.checked = true;
        const errGender = document.getElementById('gender-error');
        if (errGender) errGender.style.display = 'none';
    }

    if (pemuda.birth_date) {
        const birthDateEl = document.getElementById('birth_date');
        if (birthDateEl) {
            birthDateEl.value = pemuda.birth_date;
            birthDateEl.classList.remove('is-invalid');
        }
    }

    if (pemuda.mta_warga_uuid) {
        const mtaUuidEl = document.getElementById('mta_warga_uuid');
        if (mtaUuidEl) {
            mtaUuidEl.value = pemuda.mta_warga_uuid;
        }
    }

    // 2. Data Pribadi
    const maritalEl = document.getElementById('marital_status');
    if (maritalEl && pemuda.marital_status) {
        maritalEl.value = pemuda.marital_status;
        maritalEl.classList.remove('is-invalid');
    }

    const bloodEl = document.getElementById('blood_type');
    if (bloodEl && pemuda.blood_type) {
        bloodEl.value = pemuda.blood_type;
    }

    const birthPlaceEl = document.getElementById('birth_place');
    if (birthPlaceEl && pemuda.birth_place) {
        birthPlaceEl.value = pemuda.birth_place;
        birthPlaceEl.classList.remove('is-invalid');
    }

    const phoneEl = document.getElementById('phone');
    if (phoneEl && pemuda.phone) {
        phoneEl.value = pemuda.phone;
        phoneEl.classList.remove('is-invalid');
    }

    const emailEl = document.getElementById('email');
    if (emailEl) {
        emailEl.value = pemuda.email || '';
    }

    // 3. Alamat Domisili
    const provEl = document.getElementById('province_id');
    if (provEl && pemuda.province_id) provEl.value = pemuda.province_id;

    const regEl = document.getElementById('regency_id');
    if (regEl && pemuda.regency_id) regEl.value = pemuda.regency_id;

    const distEl = document.getElementById('district_id');
    if (pemuda.district_id) {
        if (districtTomSelect) {
            districtTomSelect.setValue(pemuda.district_id.toString());
        } else if (distEl) {
            distEl.value = pemuda.district_id;
            handleDistrictChange(pemuda.district_id);
        }
        handleDistrictChange(pemuda.district_id);
    }

    if (pemuda.village_id) {
        setTimeout(() => {
            if (villageTomSelect) {
                villageTomSelect.setValue(pemuda.village_id.toString());
            } else {
                const villEl = document.getElementById('village_id');
                if (villEl) villEl.value = pemuda.village_id;
            }
        }, 150);
    }

    const dusunEl = document.getElementById('dusun');
    if (dusunEl) dusunEl.value = pemuda.dusun || '';

    const rtEl = document.getElementById('rt');
    if (rtEl) rtEl.value = pemuda.rt || '';

    const rwEl = document.getElementById('rw');
    if (rwEl) rwEl.value = pemuda.rw || '';

    const addrDetailEl = document.getElementById('address_detail');
    if (addrDetailEl && pemuda.address_detail) {
        addrDetailEl.value = pemuda.address_detail;
        addrDetailEl.classList.remove('is-invalid');
    }

    // 4. Pendidikan
    const eduLevelEl = document.getElementById('education_level_id');
    if (eduLevelEl && pemuda.education_level_id) {
        eduLevelEl.value = pemuda.education_level_id;
        eduLevelEl.classList.remove('is-invalid');
    }

    const eduStatusEl = document.getElementById('education_status');
    if (eduStatusEl && pemuda.education_status) {
        eduStatusEl.value = pemuda.education_status;
        eduStatusEl.classList.remove('is-invalid');
    }

    const schoolEl = document.getElementById('school_name');
    if (schoolEl && pemuda.school_name) {
        schoolEl.value = pemuda.school_name;
        schoolEl.classList.remove('is-invalid');
    }

    const majorEl = document.getElementById('major');
    if (majorEl) majorEl.value = pemuda.major || '';

    const gradYearEl = document.getElementById('graduation_year');
    if (gradYearEl) gradYearEl.value = pemuda.graduation_year || '';

    // 5. Pekerjaan
    const jobStatusEl = document.getElementById('job_status_id');
    if (jobStatusEl && pemuda.job_status_id) {
        jobStatusEl.value = pemuda.job_status_id;
        jobStatusEl.classList.remove('is-invalid');
        handleJobStatusChange(pemuda.job_status_id.toString());
    }

    const jobTitleEl = document.getElementById('job_title');
    if (jobTitleEl) jobTitleEl.value = pemuda.job_title || '';

    const compNameEl = document.getElementById('company_name');
    if (compNameEl) compNameEl.value = pemuda.company_name || '';

    const fieldEl = document.getElementById('business_field');
    if (fieldEl) fieldEl.value = pemuda.business_field || '';

    // Business details (Wirausaha)
    const bizNameEl = document.getElementById('business_name');
    if (bizNameEl) bizNameEl.value = pemuda.business_name || '';

    const bizFieldWirausahaEl = document.getElementById('business_field_wirausaha');
    if (bizFieldWirausahaEl) bizFieldWirausahaEl.value = pemuda.business_field || '';

    const bizAddressEl = document.getElementById('business_address');
    if (bizAddressEl) bizAddressEl.value = pemuda.business_address || '';

    const bizContactEl = document.getElementById('business_contact');
    if (bizContactEl) bizContactEl.value = pemuda.business_contact || '';

    const bizSocialEl = document.getElementById('business_social');
    if (bizSocialEl) bizSocialEl.value = pemuda.business_social || '';

    // 6. Element Dakwah
    document.querySelectorAll('.org-toggle-check').forEach(chk => {
        chk.checked = false;
        const key = chk.getAttribute('data-key');
        if (key) toggleOrgDetail(key);
    });

    const knownOrgKeys = {
        'satgas': 'Satgas',
        'bankom': 'Bankom',
        'parkir': 'Parkir',
        'pemuda': 'Pemuda',
        'tim_ikhrom': 'Tim Ikhrom'
    };
    const otherOrgs = [];

    if (Array.isArray(pemuda.organisasi)) {
        pemuda.organisasi.forEach(org => {
            let matchedKey = null;
            const orgName = (org.organization_name || '').toLowerCase().replace(/[\s_\-]/g, '');
            for (const [k, title] of Object.entries(knownOrgKeys)) {
                const cleanTitle = title.toLowerCase().replace(/[\s_\-]/g, '');
                if (orgName === cleanTitle || orgName.includes(cleanTitle)) {
                    matchedKey = k;
                    break;
                }
            }
            if (matchedKey) {
                const chk = document.getElementById('org_' + matchedKey);
                if (chk) {
                    chk.checked = true;
                    toggleOrgDetail(matchedKey);
                }
            } else if (org.organization_name) {
                otherOrgs.push(org.organization_name);
            }
        });
    }
    const otherOrgInput = document.querySelector('input[name="other_organization"]');
    if (otherOrgInput) {
        otherOrgInput.value = otherOrgs.join(', ');
    }

    // 7. Keahlian (Skills)
    document.querySelectorAll('.skill-toggle-check').forEach(chk => {
        chk.checked = false;
        toggleSkillLevel(chk.value);
    });

    if (Array.isArray(pemuda.skills)) {
        pemuda.skills.forEach(s => {
            const chk = document.getElementById('skill_' + s.skill_id);
            if (chk) {
                chk.checked = true;
                toggleSkillLevel(s.skill_id);
                const levelSelect = document.querySelector(`select[name="skills[${s.skill_id}][level]"]`);
                if (levelSelect && s.level) {
                    levelSelect.value = s.level;
                }
            }
        });
    }

    // 8. Minat (Interests)
    document.querySelectorAll('.interest-tag-checkbox').forEach(chk => {
        chk.checked = false;
    });

    if (Array.isArray(pemuda.interests)) {
        pemuda.interests.forEach(i => {
            const chk = document.getElementById('interest_' + i.interest_id);
            if (chk) {
                chk.checked = true;
            }
        });
    }

    // 9. Foto Profil
    const existingInfoBox = document.getElementById('existing-foto-info');
    const previewImg      = document.getElementById('foto-preview-img');
    const placeholder     = document.getElementById('foto-preview-placeholder');

    if (pemuda.foto) {
        const base = (typeof PENDATAAN_CONFIG !== 'undefined' && PENDATAAN_CONFIG.baseUrl) ? PENDATAAN_CONFIG.baseUrl : '';
        existingFotoUrl = base + '/uploads/pemuda/' + pemuda.foto;
    } else if (pemuda.mta_foto_url) {
        existingFotoUrl = pemuda.mta_foto_url;
    } else {
        existingFotoUrl = null;
    }

    if (existingFotoUrl) {
        if (previewImg) {
            previewImg.src = existingFotoUrl;
            previewImg.style.display = 'block';
        }
        if (placeholder) placeholder.style.display = 'none';
        if (existingInfoBox) existingInfoBox.style.display = 'block';
    } else {
        if (existingInfoBox) existingInfoBox.style.display = 'none';
    }
    updateFotoRequirement();
}

/**
 * Asynchronously checks if Pemuda with given name, gender, birth_date, and cabang_id already exists.
 * Returns true if proceedable (either found and loaded, or not found and new entry).
 */
async function checkDataPemuda(isManual = false) {
    const nameEl = document.getElementById('name');
    const birthDateEl = document.getElementById('birth_date');
    const cabangEl = document.getElementById('cabang_id');
    const genderEl = document.querySelector('input[name="gender"]:checked');
    const resultWrapper = document.getElementById('check-data-result-wrapper');
    const resultContent = document.getElementById('check-data-result-content');
    const btnCekData = document.getElementById('btnCekData');
    const btnNext1 = document.getElementById('btnNextStep1') || document.querySelector('#step-1 button.btn-primary-pmd');

    const name = nameEl ? nameEl.value.trim() : '';
    const birthDate = birthDateEl ? birthDateEl.value.trim() : '';
    const gender = genderEl ? genderEl.value : '';
    let cabangId = cabangEl ? cabangEl.value.trim() : '';
    if (typeof cabangTomSelect !== 'undefined' && cabangTomSelect) {
        cabangId = cabangTomSelect.getValue();
    }

    // Validation for check
    if (!cabangId || !name || !gender || !birthDate) {
        if (isManual) {
            if (resultWrapper && resultContent) {
                resultContent.innerHTML = `
                    <div class="alert alert-warning card-custom border-warning p-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-exclamation-triangle-fill fs-5 text-warning flex-shrink-0"></i>
                            <div>
                                <strong class="d-block mb-1">Mohon lengkapi 4 data utama berikut terlebih dahulu:</strong>
                                <ul class="mb-0 ps-3 small text-dark">
                                    ${!cabangId ? '<li>Cabang Pemuda MTA wajib dipilih</li>' : ''}
                                    ${!name ? '<li>Nama Lengkap wajib diisi (minimal 3 karakter)</li>' : ''}
                                    ${!gender ? '<li>Jenis Kelamin wajib dipilih (Laki-laki / Perempuan)</li>' : ''}
                                    ${!birthDate ? '<li>Tanggal Lahir wajib diisi</li>' : ''}
                                </ul>
                            </div>
                        </div>
                    </div>
                `;
                resultWrapper.style.display = 'block';
                resultWrapper.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
        return false;
    }

    let originalBtnHtml = '';
    if (btnCekData) {
        originalBtnHtml = btnCekData.innerHTML;
        btnCekData.disabled = true;
        btnCekData.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Memeriksa data...';
    }
    if (btnNext1) {
        btnNext1.disabled = true;
    }

    if (resultWrapper && resultContent) {
        resultContent.innerHTML = `
            <div class="alert alert-light border card-custom p-3 text-center text-muted">
                <div class="spinner-border spinner-border-sm text-danger me-2" role="status"></div>
                <span class="small fw-semibold">Sedang memeriksa data pemuda di sistem cabang terkait...</span>
            </div>
        `;
        resultWrapper.style.display = 'block';
    }

    try {
        const formData = new FormData();
        formData.append('name', name);
        formData.append('gender', gender);
        formData.append('birth_date', birthDate);
        formData.append('cabang_id', cabangId);

        if (typeof PENDATAAN_CONFIG !== 'undefined' && PENDATAAN_CONFIG.csrfToken && PENDATAAN_CONFIG.csrfHash) {
            formData.append(PENDATAAN_CONFIG.csrfToken, PENDATAAN_CONFIG.csrfHash);
        }

        const url = (typeof PENDATAAN_CONFIG !== 'undefined' && (PENDATAAN_CONFIG.checkDataUrl || PENDATAAN_CONFIG.checkDuplicateUrl))
            ? (PENDATAAN_CONFIG.checkDataUrl || PENDATAAN_CONFIG.checkDuplicateUrl)
            : '/pendataan/check-data';

        const response = await fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error('HTTP error ' + response.status);
        }

        const res = await response.json();

        // Update CSRF token
        if (res.csrfHash) {
            if (typeof PENDATAAN_CONFIG !== 'undefined') {
                PENDATAAN_CONFIG.csrfHash = res.csrfHash;
            }
            const tokenName = (typeof PENDATAAN_CONFIG !== 'undefined' && PENDATAAN_CONFIG.csrfToken) ? PENDATAAN_CONFIG.csrfToken : 'csrf_test_name';
            document.querySelectorAll('input[name="' + tokenName + '"]').forEach(el => {
                el.value = res.csrfHash;
            });
        }

        if (res.status === 'found' && res.data) {
            // Existing data found -> load data and enable update mode
            const pemuda = res.data;
            populateExistingData(pemuda);

            const formModeBanner = document.getElementById('form-mode-banner');
            const formModeReg = document.getElementById('form-mode-reg');
            const formModeDesc = document.getElementById('form-mode-desc');
            if (formModeBanner) {
                if (formModeReg) formModeReg.innerText = 'No. Reg: ' + (pemuda.registration_number || '-');
                if (formModeDesc) formModeDesc.innerHTML = `Data <strong>${escapeHtml(pemuda.name)}</strong> ditemukan di sistem cabang ini. Anda tinggal melengkapi dan memperbarui data formulir ini.`;
                formModeBanner.style.display = 'block';
            }

            const revBanner = document.getElementById('review-mode-banner');
            const revReg = document.getElementById('review-mode-reg');
            if (revBanner) {
                if (revReg) revReg.innerText = 'No. Reg: ' + (pemuda.registration_number || '-');
                revBanner.style.display = 'block';
            }

            const btnSubmitText = document.getElementById('btnSubmitFormText');
            if (btnSubmitText) {
                btnSubmitText.innerText = 'Simpan & Lengkapi Data Pendataan';
            }

            if (resultWrapper && resultContent) {
                resultContent.innerHTML = `
                    <div class="alert alert-success card-custom border-success p-3">
                        <div class="d-flex align-items-start gap-3">
                            <i class="bi bi-patch-check-fill text-success fs-3 flex-shrink-0"></i>
                            <div class="w-100">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                                    <h6 class="fw-bold text-success mb-0">Data Pemuda Ditemukan!</h6>
                                    <span class="badge bg-success rounded-pill px-3 py-1">No. Reg: ${escapeHtml(pemuda.registration_number || '-')}</span>
                                </div>
                                <p class="small text-dark mb-2">
                                    Data pemuda atas nama <strong>"${escapeHtml(pemuda.name)}"</strong> sudah terdaftar di cabang ini. 
                                    Formulir telah <strong>otomatis terisi</strong> dengan data yang ada di sistem. 
                                    Silakan periksa dan <strong>lengkapi data</strong> Anda pada langkah berikutnya.
                                </p>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle py-1 px-2 small">
                                        <i class="bi bi-pencil-square me-1"></i> Mode: Melengkapi &amp; Memperbarui Data
                                    </span>
                                    <button type="button" class="btn btn-sm btn-success px-3" onclick="goToStep(2)">
                                        Lanjut Lengkapi Alamat <i class="bi bi-arrow-right ms-1"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                resultWrapper.style.display = 'block';
                resultWrapper.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

            return true;
        } else {
            // Not found -> clean up any existing_pemuda_id and allow new entry
            const existIdEl = document.getElementById('existing_pemuda_id');
            if (existIdEl) existIdEl.value = '';

            const formModeBanner = document.getElementById('form-mode-banner');
            if (formModeBanner) formModeBanner.style.display = 'none';

            const revBanner = document.getElementById('review-mode-banner');
            if (revBanner) revBanner.style.display = 'none';

            const btnSubmitText = document.getElementById('btnSubmitFormText');
            if (btnSubmitText) {
                btnSubmitText.innerText = 'Kirim Data Pendataan';
            }

            if (resultWrapper && resultContent) {
                resultContent.innerHTML = `
                    <div class="alert alert-info card-custom border-info p-3">
                        <div class="d-flex align-items-start gap-3">
                            <i class="bi bi-info-circle-fill text-info fs-3 flex-shrink-0"></i>
                            <div class="w-100">
                                <h6 class="fw-bold text-primary mb-1">Data Belum Terdaftar</h6>
                                <p class="small text-dark mb-2">
                                    Data pemuda atas nama <strong>"${escapeHtml(name)}"</strong> belum ada di cabang ini. 
                                    Silakan <strong>lanjutkan pengisian formulir pendataan baru</strong> sampai selesai.
                                </p>
                                <button type="button" class="btn btn-sm btn-primary-pmd px-3" onclick="focusNextField()">
                                    Lanjut Isi Data Pribadi <i class="bi bi-arrow-down ms-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                resultWrapper.style.display = 'block';
                resultWrapper.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

            return true;
        }
    } catch (err) {
        console.warn('Gagal memverifikasi data pemuda:', err);
        if (resultWrapper && resultContent) {
            resultContent.innerHTML = `
                <div class="alert alert-warning card-custom border-warning p-2 small">
                    <i class="bi bi-exclamation-circle me-1"></i> Pengecekan server tidak dapat terhubung saat ini, Anda tetap dapat melanjutkan pengisian form.
                </div>
            `;
            resultWrapper.style.display = 'block';
        }
        return true;
    } finally {
        if (btnCekData) {
            btnCekData.disabled = false;
            btnCekData.innerHTML = originalBtnHtml;
        }
        if (btnNext1) {
            btnNext1.disabled = false;
        }
    }
}

async function goToStep(step) {
    if (step < 1 || step > totalSteps) return;

    // If trying to move forward, check validation of current step
    if (step > currentStep) {
        if (!validateStep(currentStep)) {
            return;
        }

        // If moving forward from Step 1, perform check
        if (currentStep === 1) {
            await checkDataPemuda(false);
        }
    }

    // Hide all step sections
    document.querySelectorAll('.form-step-section').forEach(sec => {
        sec.classList.remove('active');
    });

    // Show target step section
    const targetSection = document.getElementById('step-' + step);
    if (targetSection) {
        targetSection.classList.add('active');
        currentStep = step;
        updateProgress(step);
        if (step === 8) {
            prepareReview();
        }
        const mobileHeader = document.querySelector('.mobile-stepper-header');
        const scrollTarget = (window.innerWidth <= 768 && mobileHeader) ? mobileHeader : targetSection;
        const targetOffset = scrollTarget.getBoundingClientRect().top + window.pageYOffset - (window.innerWidth <= 768 ? 70 : 80);
        window.scrollTo({ top: Math.max(0, targetOffset), behavior: 'smooth' });
    }
}

function validateStep(step) {
    const currentSection = document.getElementById('step-' + step);
    if (!currentSection) return true;

    const inputs = currentSection.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (input.disabled || input.offsetParent === null) return;
        const tsWrapper = input.nextElementSibling && input.nextElementSibling.classList.contains('ts-wrapper') 
                          ? input.nextElementSibling 
                          : null;

        if (input.type === 'radio') {
            const radioGroup = currentSection.querySelectorAll(`input[name="${input.name}"]`);
            const isChecked = Array.from(radioGroup).some(r => r.checked);
            if (!isChecked) {
                isValid = false;
                const errEl = document.getElementById(input.name + '-error');
                if (errEl) errEl.style.display = 'block';
            }
        } else if (!input.checkValidity() || !input.value || input.value.trim() === '') {
            isValid = false;
            input.classList.add('is-invalid');
            if (tsWrapper) {
                tsWrapper.classList.add('is-invalid');
            }
        } else {
            input.classList.remove('is-invalid');
            if (tsWrapper) {
                tsWrapper.classList.remove('is-invalid');
            }
        }
    });

    if (step === 8) {
        if (!validateFotoProfile()) {
            isValid = false;
        }
    }

    return isValid;
}

async function validateAndNext(step) {
    if (!validateStep(step)) {
        return;
    }

    if (step === 1) {
        await checkDataPemuda(false);
    }

    goToStep(step + 1);
}

// Dependent Dropdown for Kecamatan -> Desa (handles both TomSelect and standard HTML select)
function handleDistrictChange(districtId) {
    const villageSelect = document.getElementById('village_id');
    const villages = sragenVillages[districtId] || [];

    if (villageTomSelect) {
        villageTomSelect.clear();
        villageTomSelect.clearOptions();

        if (villages.length > 0) {
            const options = villages.map(v => ({
                value: v.id.toString(),
                text: v.name
            }));
            villageTomSelect.addOptions(options);
            villageTomSelect.settings.placeholder = '-- Ketik nama desa/kelurahan untuk mencari... --';
            villageTomSelect.inputState();
            villageTomSelect.enable();
        } else {
            villageTomSelect.settings.placeholder = '-- Desa tidak ditemukan --';
            villageTomSelect.inputState();
            villageTomSelect.disable();
        }
    } else if (villageSelect) {
        if (villages.length > 0) {
            villageSelect.innerHTML = '<option value="" selected disabled>-- Pilih Desa / Kelurahan --</option>';
            villages.forEach(v => {
                const opt = document.createElement('option');
                opt.value = v.id;
                opt.textContent = v.name;
                villageSelect.appendChild(opt);
            });
        } else {
            villageSelect.innerHTML = '<option value="" selected disabled>-- Desa tidak ditemukan --</option>';
        }
    }
}

// Toggle skill level dropdown
function toggleSkillLevel(skillId) {
    const checkbox = document.getElementById('skill_' + skillId);
    const levelBox = document.getElementById('skill_level_box_' + skillId);
    if (checkbox && levelBox) {
        levelBox.style.display = checkbox.checked ? 'block' : 'none';
        levelBox.querySelectorAll('select, input').forEach(el => {
            el.disabled = !checkbox.checked;
        });
    }
}

// Handle job status toggles & detail wirausaha panel
function handleJobStatusChange(statusId) {
    const statusStr = statusId ? statusId.toString() : '';
    const isNotWorking = (statusStr === '1'); // Belum Bekerja
    
    const selectEl = document.getElementById('job_status_id');
    let selectedText = '';
    if (selectEl && selectEl.selectedIndex >= 0) {
        selectedText = selectEl.options[selectEl.selectedIndex].text.toLowerCase();
    }
    const isWirausaha = (statusStr === '5') || selectedText.includes('wirausaha') || selectedText.includes('pemilik usaha');

    const titleWrapper   = document.getElementById('wrapper-job-title');
    const compWrapper    = document.getElementById('wrapper-company-name');
    const fieldWrapper   = document.getElementById('wrapper-business-field');
    const wirausahaPanel = document.getElementById('panel-detail-wirausaha');

    if (wirausahaPanel) {
        if (isWirausaha) {
            wirausahaPanel.style.display = 'block';
            wirausahaPanel.querySelectorAll('input, textarea').forEach(el => {
                el.disabled = false;
            });

            // Mark required on core business inputs
            const reqIds = ['business_name', 'business_field_wirausaha', 'business_address', 'business_contact'];
            reqIds.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.setAttribute('required', 'required');
            });

            // Hide standard company and sector wrappers for cleaner UI
            if (compWrapper) compWrapper.style.display = 'none';
            if (fieldWrapper) {
                fieldWrapper.style.display = 'none';
                const fInput = document.getElementById('business_field');
                if (fInput) fInput.disabled = true;
            }
            if (titleWrapper) {
                titleWrapper.style.display = 'block';
                titleWrapper.style.opacity = '1';
                const jobTitleInput = document.getElementById('job_title');
                if (jobTitleInput && !jobTitleInput.value.trim()) {
                    jobTitleInput.value = 'Owner / Pengelola Usaha';
                }
            }
        } else {
            wirausahaPanel.style.display = 'none';
            wirausahaPanel.querySelectorAll('input, textarea').forEach(el => {
                el.disabled = true;
                el.removeAttribute('required');
                el.classList.remove('is-invalid');
            });

            if (compWrapper) compWrapper.style.display = 'block';
            if (fieldWrapper) {
                fieldWrapper.style.display = 'block';
                const fInput = document.getElementById('business_field');
                if (fInput) fInput.disabled = false;
            }
            if (titleWrapper) {
                titleWrapper.style.display = 'block';
                const jobTitleInput = document.getElementById('job_title');
                if (jobTitleInput && jobTitleInput.value === 'Owner / Pengelola Usaha') {
                    jobTitleInput.value = '';
                }
            }
        }
    }

    if (titleWrapper && compWrapper && fieldWrapper) {
        if (isNotWorking) {
            titleWrapper.style.opacity = '0.5';
            compWrapper.style.opacity = '0.5';
            fieldWrapper.style.opacity = '0.5';
        } else if (!isWirausaha) {
            titleWrapper.style.opacity = '1';
            compWrapper.style.opacity = '1';
            fieldWrapper.style.opacity = '1';
        }
    }
}

// Toggle Organization detail panel
function toggleOrgDetail(orgKey) {
    const checkbox = document.getElementById('org_' + orgKey);
    const detailBox = document.getElementById('org_detail_' + orgKey);
    const cardBox = document.getElementById('org_card_box_' + orgKey);
    if (checkbox) {
        if (detailBox) {
            detailBox.style.display = checkbox.checked ? 'block' : 'none';
            detailBox.querySelectorAll('input, select, textarea').forEach(el => {
                el.disabled = !checkbox.checked;
            });
        }
        if (cardBox) {
            if (checkbox.checked) {
                cardBox.classList.add('selected');
            } else {
                cardBox.classList.remove('selected');
            }
        }
    }
}

// Prepare Review Step 8
function prepareReview() {
    // Pribadi
    const nameEl = document.getElementById('name');
    if (nameEl) document.getElementById('rev_name').innerText = nameEl.value || '-';
    
    const genderEl = document.querySelector('input[name="gender"]:checked');
    const genderText = genderEl ? (genderEl.value === 'L' ? 'Laki-laki' : 'Perempuan') : '-';
    const birthPlace = document.getElementById('birth_place')?.value || '-';
    const birthDate = document.getElementById('birth_date')?.value || '-';
    const revTtl = document.getElementById('rev_ttl');
    if (revTtl) revTtl.innerText = `${genderText} | ${birthPlace}, ${birthDate}`;
    
    // Status Pernikahan & Golongan Darah
    const maritalEl = document.getElementById('marital_status');
    const maritalText = maritalEl && maritalEl.value ? (maritalEl.options[maritalEl.selectedIndex]?.text || '-') : '-';
    const bloodEl = document.getElementById('blood_type');
    const bloodText = bloodEl && bloodEl.value ? (bloodEl.options[bloodEl.selectedIndex]?.text || '-') : '-';
    const revMaritalBlood = document.getElementById('rev_marital_blood');
    if (revMaritalBlood) {
        revMaritalBlood.innerText = `${maritalText} | Gol. Darah: ${bloodText}`;
    }

    const phone = document.getElementById('phone')?.value || '-';
    const email = document.getElementById('email')?.value || '(Tanpa email)';
    const revContact = document.getElementById('rev_contact');
    if (revContact) revContact.innerText = `${phone} / ${email}`;

    // Cabang text from TomSelect or native option
    const cabangEl = document.getElementById('cabang_id');
    const revCabang = document.getElementById('rev_cabang');
    if (cabangEl && revCabang) {
        if (cabangTomSelect && cabangTomSelect.getValue()) {
            const item = cabangTomSelect.getItem(cabangTomSelect.getValue());
            revCabang.innerText = item ? item.innerText.trim() : (cabangEl.options[cabangEl.selectedIndex]?.text || '-');
        } else {
            revCabang.innerText = cabangEl.options[cabangEl.selectedIndex]?.text || '-';
        }
    }

    // Foto Profil Review
    const revFotoStatus = document.getElementById('rev_foto_status');
    if (revFotoStatus) {
        const fotoInput = document.getElementById('foto');
        const isMale = document.getElementById('gender_l')?.checked;
        if (fotoInput && fotoInput.files && fotoInput.files.length > 0) {
            revFotoStatus.innerHTML = '<span class="badge bg-success bg-opacity-10 text-success border border-success-subtle"><i class="bi bi-image me-1"></i>' + escapeHtml(fotoInput.files[0].name) + '</span>';
        } else if (existingFotoUrl) {
            revFotoStatus.innerHTML = '<span class="badge bg-info bg-opacity-10 text-info border border-info-subtle"><i class="bi bi-cloud-check me-1"></i>Tercatat di sistem</span>';
        } else if (isMale) {
            revFotoStatus.innerHTML = '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle"><i class="bi bi-asterisk me-1"></i>Wajib diunggah</span>';
        } else {
            revFotoStatus.innerHTML = '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle"><i class="bi bi-dash-circle me-1"></i>Tidak diunggah (Opsional)</span>';
        }
    }
    updateFotoRequirement();

    // Alamat text from TomSelect or native options
    const kecEl = document.getElementById('district_id');
    const desEl = document.getElementById('village_id');
    let kecText = '';
    let desText = '';

    if (districtTomSelect && districtTomSelect.getValue()) {
        const item = districtTomSelect.getItem(districtTomSelect.getValue());
        kecText = item ? item.innerText.trim() : (kecEl ? kecEl.options[kecEl.selectedIndex]?.text || '' : '');
    } else {
        kecText = kecEl ? (kecEl.options[kecEl.selectedIndex]?.text || '') : '';
    }

    if (villageTomSelect && villageTomSelect.getValue()) {
        const item = villageTomSelect.getItem(villageTomSelect.getValue());
        desText = item ? item.innerText.trim() : (desEl ? desEl.options[desEl.selectedIndex]?.text || '' : '');
    } else {
        desText = desEl ? (desEl.options[desEl.selectedIndex]?.text || '') : '';
    }

    const dusun = document.getElementById('dusun')?.value || '';
    const rt = document.getElementById('rt')?.value || '';
    const rw = document.getElementById('rw')?.value || '';
    const detail = document.getElementById('address_detail')?.value || '';
    
    const revAddress = document.getElementById('rev_address');
    if (revAddress) {
        revAddress.innerText = `${detail}, ${dusun ? dusun + ',' : ''} RT ${rt || '00'}/RW ${rw || '00'}, Desa ${desText}, Kec. ${kecText}, Kab. Sragen`;
    }

    // Pendidikan
    const eduEl = document.getElementById('education_level_id');
    const eduLevel = eduEl ? (eduEl.options[eduEl.selectedIndex]?.text || '-') : '-';
    const school = document.getElementById('school_name')?.value || '-';
    const major = document.getElementById('major')?.value || '';
    const gradYear = document.getElementById('graduation_year')?.value || '';
    const revEducation = document.getElementById('rev_education');
    if (revEducation) {
        revEducation.innerText = `${eduLevel} - ${school} ${major ? '(' + major + ')' : ''} ${gradYear ? 'Th. ' + gradYear : ''}`;
    }

    // Pekerjaan
    const jobStatusEl = document.getElementById('job_status_id');
    const jobStatus = jobStatusEl ? (jobStatusEl.options[jobStatusEl.selectedIndex]?.text || '-') : '-';
    const statusVal = jobStatusEl ? jobStatusEl.value : '';
    const isWirausahaReview = (statusVal === '5') || jobStatus.toLowerCase().includes('wirausaha') || jobStatus.toLowerCase().includes('pemilik usaha');

    const jobTitle = document.getElementById('job_title')?.value || '';
    const compName = document.getElementById('company_name')?.value || '';
    const revJob = document.getElementById('rev_job');
    if (revJob) {
        if (isWirausahaReview) {
            const bizName = document.getElementById('business_name')?.value || '';
            const bizField = document.getElementById('business_field_wirausaha')?.value || '';
            const bizAddress = document.getElementById('business_address')?.value || '';
            const bizContact = document.getElementById('business_contact')?.value || '';
            const bizSocial = document.getElementById('business_social')?.value || '';

            let details = `${jobStatus}`;
            if (bizName) details += ` | Usaha: ${bizName}`;
            if (bizField) details += ` (${bizField})`;
            if (bizAddress) details += ` - Alamat: ${bizAddress}`;
            if (bizContact) details += ` - CP: ${bizContact}`;
            if (bizSocial) details += ` - Sosmed: ${bizSocial}`;
            revJob.innerText = details;
        } else {
            revJob.innerText = `${jobStatus} ${jobTitle ? ' | ' + jobTitle : ''} ${compName ? ' di ' + compName : ''}`;
        }
    }

    // Element Dakwah / Divisi
    const checkedOrgs = [];
    document.querySelectorAll('.org-toggle-check:checked').forEach(chk => {
        const title = chk.getAttribute('data-title') || chk.value;
        checkedOrgs.push(title);
    });
    const otherOrgInput = document.querySelector('input[name="other_organization"]');
    if (otherOrgInput && otherOrgInput.value.trim()) {
        checkedOrgs.push(otherOrgInput.value.trim());
    }
    const revOrgs = document.getElementById('rev_organizations');
    if (revOrgs) {
        revOrgs.innerText = checkedOrgs.length > 0 ? checkedOrgs.join(', ') : 'Tidak ada / Belum mengikuti';
    }

    // Keahlian & Minat
    const checkedSkills = [];
    document.querySelectorAll('.skill-toggle-check:checked').forEach(chk => {
        const label = chk.closest('.form-check')?.querySelector('label')?.innerText.trim();
        if (label) checkedSkills.push(label);
    });
    
    const checkedInterests = [];
    document.querySelectorAll('.interest-tag-checkbox:checked').forEach(chk => {
        const name = chk.getAttribute('data-name');
        if (name) checkedInterests.push(name);
    });

    const skillText = checkedSkills.length > 0 ? checkedSkills.join(', ') : 'Belum memilih keahlian spesifik';
    const interestText = checkedInterests.length > 0 ? checkedInterests.join(', ') : 'Belum memilih minat';

    const revSkillsInterests = document.getElementById('rev_skills_interests');
    if (revSkillsInterests) {
        revSkillsInterests.innerHTML = `
            <div><strong>Keahlian:</strong> ${skillText}</div>
            <div><strong>Minat:</strong> ${interestText}</div>
        `;
    }
}

// DOM Ready initialization
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Searchable Dropdowns (Tom Select)
    initSearchableSelects();

    // Initialize Job Status toggle if already selected (e.g. from old input / refresh)
    const initialJobStatus = document.getElementById('job_status_id');
    if (initialJobStatus && initialJobStatus.value) {
        handleJobStatusChange(initialJobStatus.value);
    }

    // Input constraints (Digits only for Phone)
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9+]/g, '');
        });
    }

    // Reset check data feedback & error state on user input
    ['name', 'birth_date', 'cabang_id'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', function() {
                const resWrap = document.getElementById('check-data-result-wrapper');
                if (resWrap && resWrap.style.display !== 'none') {
                    resWrap.style.display = 'none';
                }
                el.classList.remove('is-invalid');
                const tsWrap = el.nextElementSibling;
                if (tsWrap && tsWrap.classList.contains('ts-wrapper')) {
                    tsWrap.classList.remove('is-invalid');
                }
            });
            el.addEventListener('change', function() {
                const resWrap = document.getElementById('check-data-result-wrapper');
                if (resWrap && resWrap.style.display !== 'none') {
                    resWrap.style.display = 'none';
                }
                el.classList.remove('is-invalid');
                const tsWrap = el.nextElementSibling;
                if (tsWrap && tsWrap.classList.contains('ts-wrapper')) {
                    tsWrap.classList.remove('is-invalid');
                }
            });
        }
    });

    // Reset gender error on select
    document.querySelectorAll('input[name="gender"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const errEl = document.getElementById('gender-error');
            if (errEl) errEl.style.display = 'none';
            const resWrap = document.getElementById('check-data-result-wrapper');
            if (resWrap && resWrap.style.display !== 'none') {
                resWrap.style.display = 'none';
            }
        });
    });

    // Initialize Organization and Skill checkboxes state (in case of browser restore or validation bounce back)
    document.querySelectorAll('.org-toggle-check').forEach(chk => {
        const orgKey = chk.getAttribute('data-key');
        if (orgKey) {
            toggleOrgDetail(orgKey);
        }
    });

    document.querySelectorAll('.skill-toggle-check').forEach(chk => {
        const skillId = chk.value;
        if (skillId) {
            toggleSkillLevel(skillId);
        }
    });

    // Form Submit Handler
    const form = document.getElementById('formPendataanPemuda');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validateFotoProfile()) {
                e.preventDefault();
                return false;
            }

            const agreement = document.getElementById('agreement_check');
            if (agreement && !agreement.checked) {
                e.preventDefault();
                agreement.classList.add('is-invalid');
                agreement.focus();
                return false;
            }

            // Show loading state on submit button
            const btnSubmit = document.getElementById('btnSubmitForm');
            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan Data...';
            }
        });
    }

    // Initialize Warga MTA Autocomplete on Cabang & Name
    initWargaMtaAutocomplete();

    // Initialize Photo Upload logic
    initFotoUpload();
});

/**
 * =========================================================================
 * Foto Profil Upload & Dynamic Validation
 * =========================================================================
 */
let existingFotoUrl = null;

function updateFotoRequirement() {
    const genderEl = document.querySelector('input[name="gender"]:checked');
    const isMale = genderEl ? (genderEl.value === 'L') : true;

    const reqStar = document.getElementById('foto-required-star');
    const labelStar = document.getElementById('foto-label-star');
    const badge = document.getElementById('foto-requirement-badge');
    const instruction = document.getElementById('foto-instruction-text');
    const fotoInput = document.getElementById('foto');

    if (isMale) {
        if (reqStar) reqStar.style.display = 'inline';
        if (labelStar) labelStar.style.display = 'inline';
        if (badge) {
            badge.className = 'badge bg-danger rounded-pill px-3 py-2';
            badge.innerHTML = '<i class="bi bi-asterisk me-1"></i> Wajib untuk Laki-laki';
        }
        if (instruction) {
            instruction.innerHTML = '<i class="bi bi-info-circle text-danger me-1"></i> <strong>Ketentuan:</strong> Pendaftar <strong>Laki-laki wajib</strong> mengunggah pas foto formal / setengah badan tampak depan.';
        }
        if (fotoInput && !existingFotoUrl) {
            fotoInput.setAttribute('required', 'required');
        }
    } else {
        if (reqStar) reqStar.style.display = 'none';
        if (labelStar) labelStar.style.display = 'none';
        if (badge) {
            badge.className = 'badge bg-secondary rounded-pill px-3 py-2';
            badge.innerHTML = '<i class="bi bi-info-circle me-1"></i> Opsional untuk Perempuan';
        }
        if (instruction) {
            instruction.innerHTML = '<i class="bi bi-info-circle text-primary me-1"></i> <strong>Ketentuan:</strong> Pendaftar <strong>Perempuan tidak diwajibkan</strong> mengunggah foto profil (opsional).';
        }
        if (fotoInput) {
            fotoInput.removeAttribute('required');
            fotoInput.classList.remove('is-invalid');
            const errEl = document.getElementById('foto-error-msg');
            if (errEl) errEl.style.display = 'none';
        }
    }
}

function validateFotoProfile() {
    const genderEl = document.querySelector('input[name="gender"]:checked');
    const isMale = genderEl ? (genderEl.value === 'L') : false;
    const fotoInput = document.getElementById('foto');
    const errEl = document.getElementById('foto-error-msg');

    if (!isMale) {
        // Female: optional
        if (fotoInput) fotoInput.classList.remove('is-invalid');
        if (errEl) errEl.style.display = 'none';
        return true;
    }

    // Male: required unless a file is selected or already exists
    const hasFile = fotoInput && fotoInput.files && fotoInput.files.length > 0;
    const hasExisting = Boolean(existingFotoUrl);

    if (!hasFile && !hasExisting) {
        if (fotoInput) fotoInput.classList.add('is-invalid');
        if (errEl) {
            errEl.innerText = 'Foto profil wajib diunggah untuk pendaftar laki-laki.';
            errEl.style.display = 'block';
        }
        const section = document.getElementById('section-foto-profil');
        if (section) section.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return false;
    }

    if (fotoInput) fotoInput.classList.remove('is-invalid');
    if (errEl) errEl.style.display = 'none';
    return true;
}

function initFotoUpload() {
    const fotoInput   = document.getElementById('foto');
    const previewImg  = document.getElementById('foto-preview-img');
    const placeholder = document.getElementById('foto-preview-placeholder');
    const btnRemove   = document.getElementById('btn-remove-foto');
    const errEl       = document.getElementById('foto-error-msg');

    if (!fotoInput) return;

    // Listen to gender changes to dynamically update required indicators
    const genderRadios = document.querySelectorAll('input[name="gender"]');
    genderRadios.forEach(radio => {
        radio.addEventListener('change', updateFotoRequirement);
    });

    fotoInput.addEventListener('change', function() {
        if (!this.files || !this.files[0]) {
            return;
        }

        const file = this.files[0];
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        const maxBytes = 2 * 1024 * 1024; // 2MB

        if (!allowedTypes.includes(file.type.toLowerCase())) {
            alert('Format berkas tidak didukung! Mohon pilih foto dengan format JPG, JPEG, PNG, atau WEBP.');
            this.value = '';
            return;
        }

        if (file.size > maxBytes) {
            alert('Ukuran berkas foto terlalu besar (' + (file.size / (1024 * 1024)).toFixed(2) + ' MB). Maksimal ukuran file adalah 2 MB.');
            this.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            if (previewImg) {
                previewImg.src = e.target.result;
                previewImg.style.display = 'block';
            }
            if (placeholder) placeholder.style.display = 'none';
            if (btnRemove) btnRemove.style.display = 'block';
            fotoInput.classList.remove('is-invalid');
            if (errEl) errEl.style.display = 'none';

            const revStatus = document.getElementById('rev_foto_status');
            if (revStatus) {
                revStatus.innerHTML = '<span class="text-success fw-semibold"><i class="bi bi-check-circle me-1"></i>Foto dipilih (' + escapeHtml(file.name) + ')</span>';
            }
        };
        reader.readAsDataURL(file);
    });

    if (btnRemove) {
        btnRemove.addEventListener('click', function() {
            fotoInput.value = '';
            btnRemove.style.display = 'none';

            if (existingFotoUrl) {
                if (previewImg) {
                    previewImg.src = existingFotoUrl;
                    previewImg.style.display = 'block';
                }
                if (placeholder) placeholder.style.display = 'none';
            } else {
                if (previewImg) {
                    previewImg.src = '';
                    previewImg.style.display = 'none';
                }
                if (placeholder) placeholder.style.display = 'block';
            }

            updateFotoRequirement();
        });
    }

    updateFotoRequirement();
}

/**
 * =========================================================================
 * Warga MTA Autocomplete & Auto-Populate on Cabang Select & Name Typing
 * =========================================================================
 */
let wargaSearchTimer = null;
let currentWargaMta = null;

function initWargaMtaAutocomplete() {
    const nameInput     = document.getElementById('name');
    const cabangSelect  = document.getElementById('cabang_id');
    const dropdown      = document.getElementById('warga-suggestions-dropdown');
    const listContainer = document.getElementById('warga-suggestions-list');
    const spinner       = document.getElementById('name-search-spinner');

    if (!nameInput || !dropdown || !listContainer) return;

    // 1. Event saat mengetikkan nama di input
    nameInput.addEventListener('input', function() {
        const query = this.value.trim();
        clearTimeout(wargaSearchTimer);

        // Jika query kurang dari 2 karakter, sembunyikan dropdown
        if (query.length < 2) {
            closeWargaSuggestions();
            return;
        }

        const cabangId = (cabangTomSelect && cabangTomSelect.getValue()) ? cabangTomSelect.getValue() : (cabangSelect ? cabangSelect.value : '');

        // Jika cabang belum dipilih
        if (!cabangId) {
            listContainer.innerHTML = `
                <div class="p-3 text-center text-muted small">
                    <i class="bi bi-exclamation-circle text-warning fs-5 d-block mb-1"></i>
                    Silakan pilih <strong>Cabang Pemuda MTA</strong> di samping terlebih dahulu agar sistem dapat mencari data warga pada cabang tersebut.
                </div>
                <div class="p-2 bg-light border-top text-end">
                    <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 text-muted" onclick="closeWargaSuggestions()">Tutup</button>
                </div>
            `;
            dropdown.style.display = 'block';
            return;
        }

        // Tampilkan spinner loading
        if (spinner) spinner.style.display = 'inline-block';

        // Debounce pencarian selama 300ms
        wargaSearchTimer = setTimeout(async () => {
            try {
                const searchUrl = (typeof PENDATAAN_CONFIG !== 'undefined' && PENDATAAN_CONFIG.searchWargaUrl)
                    ? PENDATAAN_CONFIG.searchWargaUrl
                    : '/pendataan/search-warga';

                const response = await fetch(`${searchUrl}?cabang_id=${encodeURIComponent(cabangId)}&q=${encodeURIComponent(query)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!response.ok) throw new Error('HTTP error ' + response.status);

                const res = await response.json();

                // Update CSRF token jika dikembalikan
                if (res.csrfHash && typeof PENDATAAN_CONFIG !== 'undefined') {
                    PENDATAAN_CONFIG.csrfHash = res.csrfHash;
                }

                if (res.success && Array.isArray(res.data) && res.data.length > 0) {
                    renderWargaSuggestions(res.data, res.cabang_name || 'Cabang Terpilih', query);
                } else {
                    renderEmptyWargaSuggestions(query, res.cabang_name || 'cabang ini');
                }
            } catch (err) {
                console.warn('Gagal mencari data warga MTA:', err);
                renderErrorWargaSuggestions();
            } finally {
                if (spinner) spinner.style.display = 'none';
            }
        }, 300);
    });

    // 2. Focus kembali menampilkan saran jika query valid
    nameInput.addEventListener('focus', function() {
        if (this.value.trim().length >= 2 && listContainer.children.length > 0 && !currentWargaMta) {
            dropdown.style.display = 'block';
        }
    });

    // 3. Menutup dropdown saat klik di luar area
    document.addEventListener('click', function(e) {
        if (!nameInput.contains(e.target) && !dropdown.contains(e.target)) {
            closeWargaSuggestions();
        }
    });

    // 4. Reset autocomplete saat ganti cabang
    if (cabangSelect) {
        cabangSelect.addEventListener('change', function() {
            if (currentWargaMta) {
                resetWargaMtaSelection();
            }
            closeWargaSuggestions();
            // Jika sudah ada nama diketik, trigger ulang pencarian untuk cabang baru
            if (nameInput.value.trim().length >= 2) {
                nameInput.dispatchEvent(new Event('input'));
            }
        });
    }
}

function closeWargaSuggestions() {
    const dropdown = document.getElementById('warga-suggestions-dropdown');
    if (dropdown) dropdown.style.display = 'none';
}

function formatDateDisplay(dateStr) {
    if (!dateStr) return '';
    try {
        const clean = dateStr.split(' ')[0];
        const parts = clean.split('-');
        if (parts.length === 3) {
            return parts[2] + '/' + parts[1] + '/' + parts[0];
        }
        return dateStr;
    } catch (e) {
        return dateStr;
    }
}

let currentWargaList = [];

function selectSuggestionByIndex(idx) {
    idx = parseInt(idx, 10);
    if (isNaN(idx) || !currentWargaList || !currentWargaList[idx]) return;
    const w = currentWargaList[idx];

    if (w.local_pemuda_id && parseInt(w.local_pemuda_id, 10) > 0) {
        selectLocalPemuda(parseInt(w.local_pemuda_id, 10));
    } else if (w.is_registered_pmd && w.id) {
        selectLocalPemuda(parseInt(w.id, 10));
    } else if (w.uuid && w.uuid !== 'null' && w.uuid !== 'undefined' && w.uuid !== '') {
        if (/^\d+$/.test(w.uuid.toString())) {
            selectLocalPemuda(parseInt(w.uuid, 10));
        } else {
            selectWargaMta(w.uuid);
        }
    } else if (w.nama) {
        selectNewPemudaInput(w.nama);
    }
}

function handleSelectSuggestion(source, uuid, localPemudaId) {
    if (localPemudaId && parseInt(localPemudaId, 10) > 0) {
        selectLocalPemuda(parseInt(localPemudaId, 10));
    } else if (uuid && uuid !== 'null' && uuid !== 'undefined' && uuid !== '') {
        if (/^\d+$/.test(uuid.toString())) {
            selectLocalPemuda(parseInt(uuid, 10));
        } else {
            selectWargaMta(uuid);
        }
    }
}

function renderWargaSuggestions(wargaList, cabangName, query) {
    const dropdown      = document.getElementById('warga-suggestions-dropdown');
    const listContainer = document.getElementById('warga-suggestions-list');
    if (!dropdown || !listContainer) return;

    currentWargaList = wargaList || [];

    let itemsHtml = '';
    currentWargaList.forEach((w, idx) => {
        const isMale = (w.kelamin || 'L').toUpperCase() === 'L';
        const genderLabel = isMale ? 'L' : 'P';

        let usiaText = '';
        if (w.usia) {
            usiaText = `${w.usia} Th`;
        } else if (w.lahir) {
            const cleanDate = w.lahir.split(' ')[0];
            const birthYear = parseInt(cleanDate.split('-')[0], 10);
            if (!isNaN(birthYear) && birthYear > 1920) {
                const curYear = new Date().getFullYear();
                usiaText = `${curYear - birthYear} Th`;
            }
        }

        const lahirText = formatDateDisplay(w.lahir);

        itemsHtml += `
            <div class="warga-item-row p-2 px-3 border-bottom d-flex justify-content-between align-items-center" 
                 role="button" 
                 style="cursor: pointer; transition: background 0.15s ease;"
                 onmouseover="this.style.backgroundColor='#f8fafc'"
                 onmouseout="this.style.backgroundColor=''"
                 onclick="selectSuggestionByIndex(${idx})">
                <div class="me-2 text-truncate">
                    <div class="fw-bold text-dark text-truncate mb-1">
                        ${highlightMatch(escapeHtml(w.nama), query)}
                    </div>
                    <div class="small text-muted d-flex align-items-center flex-wrap gap-2">
                        <span class="badge ${isMale ? 'bg-primary' : 'badge-pink'}" style="font-size: 0.72rem; padding: 2px 7px; ${!isMale ? 'background-color:#e83e8c;color:#fff;' : ''}" title="${isMale ? 'Laki-laki' : 'Perempuan'}">
                            ${genderLabel}
                        </span>
                        ${usiaText ? `<span title="Umur"><i class="bi bi-clock-history me-1 text-secondary"></i>${usiaText}</span>` : ''}
                        ${lahirText ? `<span title="Tanggal Lahir"><i class="bi bi-calendar-date me-1 text-secondary"></i>${escapeHtml(lahirText)}</span>` : ''}
                    </div>
                </div>
                <div class="text-end flex-shrink-0 ms-2">
                    <button type="button" class="btn btn-sm btn-outline-success px-2 py-1 small fw-semibold text-nowrap" onclick="event.stopPropagation(); selectSuggestionByIndex(${idx})">
                        Pilih
                    </button>
                </div>
            </div>
        `;
    });

    listContainer.innerHTML = `
        <div class="dropdown-header bg-light py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
            <span class="small fw-bold text-dark">
                <i class="bi bi-search text-danger me-1"></i> Hasil Pencarian (${escapeHtml(cabangName)}):
            </span>
            <span class="badge bg-secondary rounded-pill small">${wargaList.length} ditemukan</span>
        </div>
        <div class="warga-items-scroll" style="max-height: 280px; overflow-y: auto;">
            ${itemsHtml}
        </div>
        <div class="p-2 px-3 bg-light border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-outline-primary fw-semibold" onclick="selectNewPemudaInput('${escapeHtml(query)}')">
                <i class="bi bi-person-plus-fill me-1"></i> Nama Tidak Tercantum? Input Data Baru
            </button>
            <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 text-muted" onclick="closeWargaSuggestions()">Tutup</button>
        </div>
    `;

    dropdown.style.display = 'block';
}

function renderEmptyWargaSuggestions(query, cabangName) {
    const dropdown      = document.getElementById('warga-suggestions-dropdown');
    const listContainer = document.getElementById('warga-suggestions-list');
    if (!dropdown || !listContainer) return;

    listContainer.innerHTML = `
        <div class="p-3 text-center">
            <div class="mb-2">
                <span class="badge bg-secondary bg-opacity-10 text-secondary border p-2 rounded-circle">
                    <i class="bi bi-person-x fs-3"></i>
                </span>
            </div>
            <h6 class="fw-bold text-dark mb-1">Nama "${escapeHtml(query)}" Belum Ada di Cabang ${escapeHtml(cabangName)}</h6>
            <p class="small text-muted mb-3">
                Tidak ditemukan di database <strong>MTA Pusat</strong> maupun <strong>PMD Cabang ${escapeHtml(cabangName)}</strong>.<br>
                Silakan lanjutkan untuk mendaftar sebagai pemuda baru di cabang ini.
            </p>
            <button type="button" class="btn btn-sm btn-primary-pmd px-3 py-2 fw-semibold" onclick="selectNewPemudaInput('${escapeHtml(query)}')">
                <i class="bi bi-person-plus-fill me-1"></i> Input Data Baru dengan Nama Ini
            </button>
        </div>
        <div class="p-2 bg-light border-top text-end">
            <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 text-muted" onclick="closeWargaSuggestions()">Tutup</button>
        </div>
    `;

    dropdown.style.display = 'block';
}

function renderErrorWargaSuggestions() {
    const dropdown      = document.getElementById('warga-suggestions-dropdown');
    const listContainer = document.getElementById('warga-suggestions-list');
    if (!dropdown || !listContainer) return;

    listContainer.innerHTML = `
        <div class="p-2 px-3 text-muted small text-center">
            <i class="bi bi-wifi-off text-secondary me-1"></i> Pencarian nama warga MTA belum dapat terhubung. Anda tetap dapat mengetikkan nama secara manual.
        </div>
        <div class="p-2 bg-light border-top text-end">
            <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 text-muted" onclick="closeWargaSuggestions()">Tutup</button>
        </div>
    `;

    dropdown.style.display = 'block';
}

function highlightMatch(text, query) {
    if (!query) return text;
    const escapedQuery = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const regex = new RegExp('(' + escapedQuery + ')', 'gi');
    return text.replace(regex, '<span class="text-success fw-bold">$1</span>');
}

async function selectLocalPemuda(id) {
    if (!id) return;

    closeWargaSuggestions();
    const spinner = document.getElementById('name-search-spinner');
    if (spinner) spinner.style.display = 'inline-block';

    try {
        const pemudaUrl = (typeof PENDATAAN_CONFIG !== 'undefined' && PENDATAAN_CONFIG.pemudaDetailUrl)
            ? PENDATAAN_CONFIG.pemudaDetailUrl
            : (typeof PENDATAAN_CONFIG !== 'undefined' && PENDATAAN_CONFIG.baseUrl ? PENDATAAN_CONFIG.baseUrl + '/pendataan/pemuda-detail' : '/pendataan/pemuda-detail');

        let res = null;
        try {
            const response = await fetch(`${pemudaUrl}/${encodeURIComponent(id)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (response.ok) {
                res = await response.json();
            }
        } catch (e) {
            console.warn('Gagal fetch pemudaDetail, mencoba wargaDetail fallback:', e);
        }

        // Fallback ke wargaDetail jika pemudaDetail gagal
        if (!res || !res.success || !res.data) {
            const detailUrl = (typeof PENDATAAN_CONFIG !== 'undefined' && PENDATAAN_CONFIG.wargaDetailUrl)
                ? PENDATAAN_CONFIG.wargaDetailUrl
                : '/pendataan/warga-detail';
            const fbResponse = await fetch(`${detailUrl}/${encodeURIComponent(id)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (fbResponse.ok) {
                const fbRes = await fbResponse.json();
                if (fbRes.success && fbRes.data) {
                    res = {
                        success: true,
                        data: fbRes.data.full_pmd_data || fbRes.data,
                        csrfHash: fbRes.csrfHash
                    };
                }
            }
        }

        if (res && res.csrfHash && typeof PENDATAAN_CONFIG !== 'undefined') {
            PENDATAAN_CONFIG.csrfHash = res.csrfHash;
        }

        if (!res || !res.success || !res.data) {
            alert((res && res.message) || 'Gagal memuat detail data pemuda lokal.');
            return;
        }

        const pemuda = res.data;
        populateExistingData(pemuda);

        // Tampilkan banner mode pemuda terdaftar
        const formModeBanner = document.getElementById('form-mode-banner');
        const formModeReg = document.getElementById('form-mode-reg');
        const formModeDesc = document.getElementById('form-mode-desc');
        if (formModeBanner) {
            if (formModeReg) formModeReg.innerText = 'No. Reg: ' + (pemuda.registration_number || '-');
            if (formModeDesc) formModeDesc.innerHTML = `Data pemuda atas nama <strong>${escapeHtml(pemuda.name)}</strong> sudah terdaftar di cabang ini. Formulir telah otomatis diisikan dengan data Anda.`;
            formModeBanner.style.display = 'block';
        }

        const revBanner = document.getElementById('review-mode-banner');
        const revReg = document.getElementById('review-mode-reg');
        if (revBanner) {
            if (revReg) revReg.innerText = 'No. Reg: ' + (pemuda.registration_number || '-');
            revBanner.style.display = 'block';
        }

        const btnSubmitText = document.getElementById('btnSubmitFormText');
        if (btnSubmitText) {
            btnSubmitText.innerText = 'Simpan & Lengkapi Data Pendataan';
        }

        // Tampilkan di check-data-result-wrapper
        const resultWrapper = document.getElementById('check-data-result-wrapper');
        const resultContent = document.getElementById('check-data-result-content');
        if (resultWrapper && resultContent) {
            resultContent.innerHTML = `
                <div class="alert alert-success card-custom border-success p-3">
                    <div class="d-flex align-items-start gap-3">
                        <i class="bi bi-patch-check-fill text-success fs-3 flex-shrink-0"></i>
                        <div class="w-100">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                                <strong class="text-success fs-6">Data Pemuda Ditemukan di Database Cabang</strong>
                                <span class="badge bg-success rounded-pill px-3 py-1">Mode Melengkapi Data</span>
                            </div>
                            <p class="mb-2 text-dark small">
                                Pemuda dengan nama <strong>${escapeHtml(pemuda.name)}</strong> telah terdaftar di cabang ini dengan No. Registrasi <code class="fw-bold text-dark">${escapeHtml(pemuda.registration_number || '-')}</code>.
                            </p>
                            <div class="alert alert-light border small text-muted mb-0 py-2">
                                <i class="bi bi-info-circle me-1 text-primary"></i> Data formulir telah otomatis dimuat. Anda dapat langsung melanjutkan ke langkah berikutnya untuk melengkapi atau memperbarui kolom yang belum lengkap.
                            </div>
                        </div>
                    </div>
                </div>
            `;
            resultWrapper.style.display = 'block';
        }

        // Scroll halus ke konfirmasi / field selanjutnya
        const targetScroll = resultWrapper || document.getElementById('birth_date');
        if (targetScroll) {
            targetScroll.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

    } catch (err) {
        console.error('Error saat memilih pemuda lokal:', err);
        alert('Terjadi kesalahan saat memuat data pemuda.');
    } finally {
        if (spinner) spinner.style.display = 'none';
    }
}

function selectNewPemudaInput(name) {
    closeWargaSuggestions();
    resetWargaMtaSelection();

    const nameInput = document.getElementById('name');
    if (nameInput && name) {
        nameInput.value = name;
        nameInput.classList.remove('is-invalid');
    }

    const checkResWrap = document.getElementById('check-data-result-wrapper');
    const checkResContent = document.getElementById('check-data-result-content');
    if (checkResWrap && checkResContent) {
        checkResContent.innerHTML = `
            <div class="alert alert-primary card-custom border-primary p-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-person-plus-fill fs-4 text-primary flex-shrink-0"></i>
                        <div>
                            <strong class="text-primary d-block">Mode Pendaftaran Pemuda Baru</strong>
                            <div class="small text-dark">Anda mendaftarkan pemuda baru atas nama <strong>${escapeHtml(name)}</strong>. Silakan lanjutkan mengisi seluruh data formulir hingga selesai.</div>
                        </div>
                    </div>
                    <span class="badge bg-primary rounded-pill px-3 py-2">Pendaftaran Baru</span>
                </div>
            </div>
        `;
        checkResWrap.style.display = 'block';
        checkResWrap.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    const btnSubmitText = document.getElementById('btnSubmitFormText');
    if (btnSubmitText) {
        btnSubmitText.innerText = 'Kirim Data Pendataan';
    }

    // Arahkan fokus ke input selanjutnya
    const birthDateEl = document.getElementById('birth_date');
    const genderRadio = document.getElementById('gender_l');
    if (genderRadio && !document.querySelector('input[name="gender"]:checked')) {
        genderRadio.focus();
    } else if (birthDateEl && !birthDateEl.value) {
        birthDateEl.focus();
    }
}

async function selectWargaMta(uuid) {
    if (!uuid) return;

    // Jika parameter adalah ID numerik lokal, delegasikan langsung ke selectLocalPemuda
    if (typeof uuid === 'number' || (typeof uuid === 'string' && /^\d+$/.test(uuid.trim()))) {
        return selectLocalPemuda(parseInt(uuid, 10));
    }

    closeWargaSuggestions();
    const spinner = document.getElementById('name-search-spinner');
    if (spinner) spinner.style.display = 'inline-block';

    try {
        const detailUrl = (typeof PENDATAAN_CONFIG !== 'undefined' && PENDATAAN_CONFIG.wargaDetailUrl)
            ? PENDATAAN_CONFIG.wargaDetailUrl
            : '/pendataan/warga-detail';

        const response = await fetch(`${detailUrl}/${encodeURIComponent(uuid)}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        if (!response.ok) throw new Error('HTTP error ' + response.status);

        const res = await response.json();

        if (res.csrfHash && typeof PENDATAAN_CONFIG !== 'undefined') {
            PENDATAAN_CONFIG.csrfHash = res.csrfHash;
        }

        if (!res.success || !res.data) {
            alert(res.message || 'Gagal mengambil detail data warga MTA.');
            return;
        }

        const data = res.data;
        currentWargaMta = data;

        // Jika respons mengembalikan data pemuda lokal terdaftar
        if (data.is_registered_pmd && data.full_pmd_data) {
            populateExistingData(data.full_pmd_data);

            const formModeBanner = document.getElementById('form-mode-banner');
            const formModeReg = document.getElementById('form-mode-reg');
            const formModeDesc = document.getElementById('form-mode-desc');
            if (formModeBanner) {
                if (formModeReg) formModeReg.innerText = 'No. Reg: ' + (data.local_reg_number || '-');
                if (formModeDesc) formModeDesc.innerHTML = `Data pemuda atas nama <strong>${escapeHtml(data.name)}</strong> sudah terdaftar di cabang ini. Formulir telah otomatis diisikan dengan data Anda.`;
                formModeBanner.style.display = 'block';
            }

            const revBanner = document.getElementById('review-mode-banner');
            const revReg = document.getElementById('review-mode-reg');
            if (revBanner) {
                if (revReg) revReg.innerText = 'No. Reg: ' + (data.local_reg_number || '-');
                revBanner.style.display = 'block';
            }

            const btnSubmitText = document.getElementById('btnSubmitFormText');
            if (btnSubmitText) {
                btnSubmitText.innerText = 'Simpan & Lengkapi Data Pendataan';
            }

            return;
        }

        // 1. Isi data pokok (Nama, UUID, Gender, Tanggal Lahir, Tempat Lahir)
        const nameEl = document.getElementById('name');
        if (nameEl && data.name) {
            nameEl.value = data.name;
            nameEl.classList.remove('is-invalid');
        }

        const mtaUuidEl = document.getElementById('mta_warga_uuid');
        if (mtaUuidEl) {
            mtaUuidEl.value = data.uuid || '';
        }

        if (data.gender) {
            const isMale = data.gender.toUpperCase() === 'L';
            const radioL = document.getElementById('gender_l');
            const radioP = document.getElementById('gender_p');
            if (isMale && radioL) radioL.checked = true;
            if (!isMale && radioP) radioP.checked = true;
            const errGender = document.getElementById('gender-error');
            if (errGender) errGender.style.display = 'none';
        }

        const birthDateEl = document.getElementById('birth_date');
        if (birthDateEl && data.birth_date) {
            birthDateEl.value = data.birth_date;
            birthDateEl.classList.remove('is-invalid');
        }

        const birthPlaceEl = document.getElementById('birth_place');
        if (birthPlaceEl && data.birth_place) {
            birthPlaceEl.value = data.birth_place;
            birthPlaceEl.classList.remove('is-invalid');
        }

        const phoneEl = document.getElementById('phone');
        if (phoneEl && data.phone) {
            phoneEl.value = data.phone;
            phoneEl.classList.remove('is-invalid');
        }

        const maritalEl = document.getElementById('marital_status');
        if (maritalEl && data.marital_status) {
            maritalEl.value = data.marital_status;
            maritalEl.classList.remove('is-invalid');
        }

        const bloodEl = document.getElementById('blood_type');
        if (bloodEl && data.blood_type) {
            bloodEl.value = data.blood_type;
        }

        // 2. Isi data domisili
        const addrEl = document.getElementById('address_detail');
        if (addrEl && data.address_detail) {
            addrEl.value = data.address_detail;
            addrEl.classList.remove('is-invalid');
        }

        const dusunEl = document.getElementById('dusun');
        if (dusunEl && data.dusun) dusunEl.value = data.dusun;

        const rtEl = document.getElementById('rt');
        if (rtEl && data.rt) rtEl.value = data.rt;

        const rwEl = document.getElementById('rw');
        if (rwEl && data.rw) rwEl.value = data.rw;

        // Auto-select Kecamatan & Desa jika ada kecocokan
        if (data.district_id) {
            if (districtTomSelect) {
                districtTomSelect.setValue(data.district_id.toString());
            } else {
                const distEl = document.getElementById('district_id');
                if (distEl) distEl.value = data.district_id;
            }
            handleDistrictChange(data.district_id);

            if (data.village_id) {
                setTimeout(() => {
                    if (villageTomSelect) {
                        villageTomSelect.setValue(data.village_id.toString());
                    } else {
                        const villEl = document.getElementById('village_id');
                        if (villEl) villEl.value = data.village_id;
                    }
                }, 200);
            }
        }

        // 3. Jika warga sudah terdaftar di PMD Lokal -> muat data lengkapnya
        if (data.is_registered_pmd && data.full_pmd_data) {
            populateExistingData(data.full_pmd_data);

            const formModeBanner = document.getElementById('form-mode-banner');
            const formModeReg = document.getElementById('form-mode-reg');
            const formModeDesc = document.getElementById('form-mode-desc');
            if (formModeBanner) {
                if (formModeReg) formModeReg.innerText = 'No. Reg: ' + (data.local_reg_number || '-');
                if (formModeDesc) formModeDesc.innerHTML = `Data <strong>${escapeHtml(data.name)}</strong> sudah terdaftar di sistem PMD cabang ini. Formulir telah otomatis diisikan, Anda tinggal melengkapi data yang masih kosong.`;
                formModeBanner.style.display = 'block';
            }
        } else {
            // Belum di PMD -> tampilkan banner sukses pilih warga MTA
            const selectedBanner = document.getElementById('warga-mta-selected-banner');
            const nameBanner = document.getElementById('warga-selected-name');
            const nomorBanner = document.getElementById('warga-selected-nomor');
            if (selectedBanner) {
                if (nameBanner) nameBanner.innerText = data.name;
                if (nomorBanner) nomorBanner.innerText = data.nomor || '-';
                selectedBanner.style.display = 'block';
            }

            const existIdEl = document.getElementById('existing_pemuda_id');
            if (existIdEl) existIdEl.value = '';

            const formModeBanner = document.getElementById('form-mode-banner');
            if (formModeBanner) formModeBanner.style.display = 'none';
        }

        // Scroll halus ke banner
        const bannerTarget = document.getElementById('warga-mta-selected-banner') || document.getElementById('form-mode-banner');
        if (bannerTarget && bannerTarget.style.display !== 'none') {
            bannerTarget.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

    } catch (err) {
        console.error('Error saat memilih warga MTA:', err);
        alert('Terjadi kesalahan saat memuat detail data warga.');
    } finally {
        if (spinner) spinner.style.display = 'none';
    }
}

function resetWargaMtaSelection() {
    currentWargaMta = null;
    const mtaUuidEl = document.getElementById('mta_warga_uuid');
    if (mtaUuidEl) mtaUuidEl.value = '';

    const selectedBanner = document.getElementById('warga-mta-selected-banner');
    if (selectedBanner) selectedBanner.style.display = 'none';

    const existIdEl = document.getElementById('existing_pemuda_id');
    if (existIdEl) existIdEl.value = '';

    const formModeBanner = document.getElementById('form-mode-banner');
    if (formModeBanner) formModeBanner.style.display = 'none';

    const revBanner = document.getElementById('review-mode-banner');
    if (revBanner) revBanner.style.display = 'none';

    const btnSubmitText = document.getElementById('btnSubmitFormText');
    if (btnSubmitText) {
        btnSubmitText.innerText = 'Kirim Data Pendataan';
    }

    existingFotoUrl = null;
    const existingInfoBox = document.getElementById('existing-foto-info');
    if (existingInfoBox) existingInfoBox.style.display = 'none';
    const previewImg = document.getElementById('foto-preview-img');
    if (previewImg) {
        previewImg.src = '';
        previewImg.style.display = 'none';
    }
    const placeholder = document.getElementById('foto-preview-placeholder');
    if (placeholder) placeholder.style.display = 'block';
    const btnRemove = document.getElementById('btn-remove-foto');
    if (btnRemove) btnRemove.style.display = 'none';
    const fotoInput = document.getElementById('foto');
    if (fotoInput) fotoInput.value = '';
    updateFotoRequirement();
}
