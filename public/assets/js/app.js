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
