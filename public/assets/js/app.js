/**
 * GCIRMS — Main JavaScript
 * Handles: CSRF injection, AJAX helpers, sidebar, notifications, auto-save
 */

'use strict';

// ── CSRF Token (injected into all AJAX requests) ───────────────────────────
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

// ── Fetch helper with CSRF + JSON ──────────────────────────────────────────
async function apiFetch(url, options = {}) {
    const defaults = {
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': CSRF_TOKEN,
            'X-Requested-With': 'XMLHttpRequest',
        },
    };
    const config = { ...defaults, ...options };
    if (options.headers) {
        config.headers = { ...defaults.headers, ...options.headers };
    }
    const response = await fetch(url, config);
    return response.json();
}

// ── Flash message auto-dismiss ─────────────────────────────────────────────
document.querySelectorAll('.alert.fade.show').forEach(alert => {
    setTimeout(() => {
        const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
        bsAlert?.close();
    }, 5000);
});

// ── Notification badge update ──────────────────────────────────────────────
async function updateNotificationCount() {
    try {
        const data = await apiFetch('/api/v1/notifications/unread-count');
        if (data.success) {
            const dot   = document.querySelector('.badge-dot');
            const count = data.data?.count ?? 0;
            if (dot) dot.style.display = count > 0 ? 'block' : 'none';
        }
    } catch (e) { /* silent */ }
}

// Poll every 60 seconds for new notifications
if (document.getElementById('topnav')) {
    updateNotificationCount();
    setInterval(updateNotificationCount, 60000);
}

// ── Draft Auto-save ────────────────────────────────────────────────────────
function initAutoSave(formId, draftUrl, intervalMs = 60000) {
    const form = document.getElementById(formId);
    if (!form) return;

    const saveStatus = document.getElementById('draft-status');

    setInterval(async () => {
        const formData = new FormData(form);
        const body = {};
        formData.forEach((v, k) => { body[k] = v; });

        try {
            const data = await apiFetch(draftUrl, {
                method: 'POST',
                body: JSON.stringify(body),
            });
            if (saveStatus) {
                saveStatus.textContent = data.success
                    ? 'Draft saved ' + new Date().toLocaleTimeString()
                    : 'Save failed';
            }
        } catch (e) {
            if (saveStatus) saveStatus.textContent = 'Auto-save unavailable';
        }
    }, intervalMs);
}

// ── Table export helpers ───────────────────────────────────────────────────
function exportTable(format) {
    const url = new URL(window.location.href);
    url.searchParams.set('export', format);
    window.location.href = url.toString();
}

// ── GPS Coordinate Picker ──────────────────────────────────────────────────
function initLocationPicker(mapId, latInputId, lngInputId) {
    const mapEl = document.getElementById(mapId);
    if (!mapEl || typeof L === 'undefined') return;

    const latInput = document.getElementById(latInputId);
    const lngInput = document.getElementById(lngInputId);

    // Center on Tanzania
    const map = L.map(mapId).setView([-6.369, 34.889], 6);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 18,
    }).addTo(map);

    let marker = null;

    // If existing coords, show marker
    if (latInput?.value && lngInput?.value) {
        const lat = parseFloat(latInput.value);
        const lng = parseFloat(lngInput.value);
        marker = L.marker([lat, lng]).addTo(map);
        map.setView([lat, lng], 14);
    }

    map.on('click', (e) => {
        const { lat, lng } = e.latlng;
        if (marker) marker.setLatLng([lat, lng]);
        else marker = L.marker([lat, lng]).addTo(map);
        if (latInput) latInput.value = lat.toFixed(7);
        if (lngInput) lngInput.value = lng.toFixed(7);
    });

    // Try browser geolocation
    document.getElementById('btn-locate')?.addEventListener('click', () => {
        navigator.geolocation?.getCurrentPosition(pos => {
            const { latitude, longitude } = pos.coords;
            map.setView([latitude, longitude], 15);
            if (marker) marker.setLatLng([latitude, longitude]);
            else marker = L.marker([latitude, longitude]).addTo(map);
            if (latInput) latInput.value = latitude.toFixed(7);
            if (lngInput) lngInput.value = longitude.toFixed(7);
        });
    });
}

// ── Cascading Location Dropdowns ───────────────────────────────────────────
function initLocationCascade() {
    const regionSel   = document.getElementById('region_id');
    const districtSel = document.getElementById('district_id');
    const wardSel     = document.getElementById('ward_id');
    const villageSel  = document.getElementById('village_id');

    async function loadOptions(selectEl, url, placeholder) {
        if (!selectEl) return;
        selectEl.disabled = true;
        selectEl.innerHTML = `<option value="">${placeholder}</option>`;
        try {
            const data = await apiFetch(url);
            if (data.success) {
                data.data.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.textContent = item.name;
                    selectEl.appendChild(opt);
                });
            }
        } catch (e) { /* silent */ }
        selectEl.disabled = false;
    }

    regionSel?.addEventListener('change', () => {
        const rid = regionSel.value;
        if (!rid) return;
        loadOptions(districtSel, `/api/v1/locations/districts?region_id=${rid}`, 'Select District');
        if (wardSel)    wardSel.innerHTML    = '<option value="">Select Ward</option>';
        if (villageSel) villageSel.innerHTML = '<option value="">Select Village</option>';
    });

    districtSel?.addEventListener('change', () => {
        const did = districtSel.value;
        if (!did) return;
        loadOptions(wardSel, `/api/v1/locations/wards?district_id=${did}`, 'Select Ward');
        if (villageSel) villageSel.innerHTML = '<option value="">Select Village</option>';
    });

    wardSel?.addEventListener('change', () => {
        const wid = wardSel.value;
        if (!wid) return;
        loadOptions(villageSel, `/api/v1/locations/villages?ward_id=${wid}`, 'Select Village');
    });
}

// Initialise cascade on pages that have location dropdowns
document.addEventListener('DOMContentLoaded', () => {
    initLocationCascade();
});

// ── Enterprise UX Polish (Sprint 5) ──────────────────────────────────────────

// 1. High-Contrast Accessibility Mode
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('btn-high-contrast');
    if (!btn) return;
    
    // Check localStorage
    if (localStorage.getItem('gcirms_high_contrast') === '1') {
        document.body.classList.add('high-contrast');
    }
    
    btn.addEventListener('click', () => {
        const isActive = document.body.classList.toggle('high-contrast');
        localStorage.setItem('gcirms_high_contrast', isActive ? '1' : '0');
    });
});

// 2. Collapsible Dashboard Widgets
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.card-header').forEach((header, index) => {
        // Only target headers that have an icon and some text, ignoring ones with complex structures if needed
        const cardBody = header.nextElementSibling;
        if (!cardBody || !cardBody.classList.contains('card-body')) return;

        // Create toggle button
        const btn = document.createElement('button');
        btn.className = 'btn btn-sm btn-link text-muted p-0 float-end no-print';
        btn.innerHTML = '<i class="bi bi-chevron-up"></i>';
        
        const storageKey = 'gcirms_card_collapse_' + index;
        
        // Initial state
        if (localStorage.getItem(storageKey) === '1') {
            cardBody.style.display = 'none';
            btn.innerHTML = '<i class="bi bi-chevron-down"></i>';
        }

        btn.addEventListener('click', (e) => {
            e.preventDefault();
            if (cardBody.style.display === 'none') {
                cardBody.style.display = 'block';
                btn.innerHTML = '<i class="bi bi-chevron-up"></i>';
                localStorage.setItem(storageKey, '0');
            } else {
                cardBody.style.display = 'none';
                btn.innerHTML = '<i class="bi bi-chevron-down"></i>';
                localStorage.setItem(storageKey, '1');
            }
        });
        
        header.appendChild(btn);
    });
});

// 3. Quick Export to CSV for Tables
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('table.table-gcirms').forEach((table, index) => {
        const container = table.closest('.card-body') || table.parentElement;
        if (!container || table.classList.contains('no-export')) return;
        
        // Create export button
        const exportBtn = document.createElement('button');
        exportBtn.className = 'btn btn-sm btn-outline-secondary mb-2 float-end no-print';
        exportBtn.innerHTML = '<i class="bi bi-filetype-csv"></i> Export CSV';
        
        exportBtn.addEventListener('click', () => {
            let csv = [];
            const rows = table.querySelectorAll('tr');
            
            for (let i = 0; i < rows.length; i++) {
                let row = [], cols = rows[i].querySelectorAll('td, th');
                
                for (let j = 0; j < cols.length; j++) {
                    // Clean text (remove extra spaces and commas)
                    let text = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, " ").replace(/"/g, '""');
                    row.push('"' + text + '"');
                }
                csv.push(row.join(','));
            }
            
            const csvFile = new Blob([csv.join('\n')], {type: "text/csv"});
            const downloadLink = document.createElement("a");
            downloadLink.download = 'export_' + new Date().getTime() + '.csv';
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = "none";
            document.body.appendChild(downloadLink);
            downloadLink.click();
            downloadLink.remove();
        });
        
        // Insert before the table
        table.parentNode.insertBefore(exportBtn, table);
    });
});

// 4. Quick Print Button
document.addEventListener('DOMContentLoaded', () => {
    const pageHeader = document.querySelector('.page-header');
    if (!pageHeader) return;
    
    let actionsDiv = document.querySelector('.page-header-actions');
    if (!actionsDiv) {
        actionsDiv = document.createElement('div');
        actionsDiv.className = 'page-header-actions';
        pageHeader.appendChild(actionsDiv);
    }

    const printBtn = document.createElement('button');
    printBtn.className = 'btn btn-sm btn-outline-secondary ms-2 no-print';
    printBtn.title = 'Print or Save as PDF';
    printBtn.innerHTML = '<i class="bi bi-printer"></i>';
    printBtn.onclick = () => window.print();
    actionsDiv.appendChild(printBtn);
});
