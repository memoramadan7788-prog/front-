// TalentBridge frontend behaviour.
// Hooks are plain data-attributes in the Blade views (topbar, layouts, modal).
import Alpine from 'alpinejs';
import './candidate-dashboard-analytics.js';
import './hall-landing.js';

window.Alpine = Alpine;
Alpine.start();

/* ---------- Dropdowns: [data-dropdown-trigger] + [data-dropdown-panel] ---------- */
function closeDropdown(trigger) {
    const panel = document.getElementById(trigger.getAttribute('aria-controls'));
    if (!panel) return;
    panel.classList.add('hidden');
    trigger.setAttribute('aria-expanded', 'false');
}

function closeAllDropdowns(except = null) {
    document.querySelectorAll('[data-dropdown-trigger]').forEach((trigger) => {
        if (trigger !== except) closeDropdown(trigger);
    });
}

document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-dropdown-trigger]');

    if (trigger) {
        const panel = document.getElementById(trigger.getAttribute('aria-controls'));
        if (!panel) return;
        const willOpen = panel.classList.contains('hidden');
        closeAllDropdowns(trigger);
        panel.classList.toggle('hidden', !willOpen);
        trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        return;
    }

    // click outside any open panel closes them
    if (!event.target.closest('[data-dropdown-panel]')) closeAllDropdowns();
});

/* ---------- Sidebar workspace nav collapse: [data-sidebar-nav-toggle] ---------- */
document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-sidebar-nav-toggle]');
    if (!toggle) return;

    const listId = toggle.getAttribute('aria-controls');
    const list = listId ? document.getElementById(listId) : null;
    if (!list) return;

    const expanded = toggle.getAttribute('aria-expanded') === 'true';
    const next = !expanded;
    toggle.setAttribute('aria-expanded', next ? 'true' : 'false');
    // Keep items in the DOM; only toggle visibility. Independent of the mobile drawer.
    list.classList.toggle('hidden', !next);
});

/* ---------- Theme switch: light | dark | system (key: hb_theme) ---------- */
function applyTheme(mode) {
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    const dark = mode === 'dark' || (mode === 'system' && prefersDark);
    document.documentElement.classList.toggle('dark', dark);
    document.documentElement.dataset.theme = mode;
}

function currentTheme() {
    try {
        return localStorage.getItem('hb_theme') || 'system';
    } catch (e) {
        return 'system';
    }
}

function paintThemeButtons() {
    const mode = currentTheme();
    document.querySelectorAll('[data-theme-option]').forEach((btn) => {
        const active = btn.dataset.themeOption === mode;
        btn.setAttribute('aria-pressed', active ? 'true' : 'false');
        btn.classList.toggle('bg-hb-info-soft', active);
        btn.classList.toggle('text-hb-blue', active);
        btn.classList.toggle('text-hb-muted', !active);
    });
}

document.addEventListener('click', (event) => {
    const btn = event.target.closest('[data-theme-option]');
    if (!btn) return;
    const mode = btn.dataset.themeOption;
    try {
        localStorage.setItem('hb_theme', mode);
    } catch (e) { /* storage blocked: still apply for this page */ }
    applyTheme(mode);
    paintThemeButtons();
});

if (window.matchMedia) {
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        if (currentTheme() === 'system') applyTheme('system');
    });
}
paintThemeButtons();

/* ---------- Mobile sidebar drawer ---------- */
const drawer = document.querySelector('[data-mobile-sidebar]');
const backdrop = document.querySelector('[data-mobile-sidebar-backdrop]');
const drawerToggle = document.querySelector('[data-mobile-sidebar-toggle]');

function setDrawer(open) {
    if (!drawer) return;
    drawer.classList.toggle('-translate-x-full', !open);
    drawer.classList.toggle('rtl:translate-x-full', !open);
    drawer.classList.toggle('translate-x-0', open);
    drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
    if (open) drawer.removeAttribute('inert');
    else drawer.setAttribute('inert', '');
    if (backdrop) {
        backdrop.classList.toggle('hidden', !open);
        backdrop.hidden = !open;
        backdrop.setAttribute('aria-hidden', open ? 'false' : 'true');
    }
    if (drawerToggle) drawerToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
}

document.addEventListener('click', (event) => {
    if (event.target.closest('[data-mobile-sidebar-toggle]')) setDrawer(true);
    else if (event.target.closest('[data-mobile-sidebar-close]') || event.target.closest('[data-mobile-sidebar-backdrop]')) setDrawer(false);
});

/* ---------- Public site mobile nav ---------- */
document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-public-nav-toggle]');
    if (!toggle) return;
    const nav = document.getElementById(toggle.getAttribute('aria-controls'));
    if (!nav) return;
    const open = nav.classList.toggle('hidden') === false;
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
});

/* ---------- Modals: [data-hb-modal] opened with [data-open-modal="id"] ---------- */
let lastFocused = null;

function openModal(modal) {
    lastFocused = document.activeElement;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    const focusable = modal.querySelector('input, select, textarea, button:not([data-hb-modal-close]), a[href]');
    (focusable || modal.querySelector('[data-hb-modal-close]'))?.focus();
}

function closeModal(modal) {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    if (lastFocused && typeof lastFocused.focus === 'function') lastFocused.focus();
}

document.addEventListener('click', (event) => {
    const opener = event.target.closest('[data-open-modal]');
    if (opener) {
        const modal = document.getElementById(opener.dataset.openModal);
        if (modal) openModal(modal);
        return;
    }

    if (event.target.closest('[data-hb-modal-close]')) {
        const modal = event.target.closest('[data-hb-modal]');
        if (modal) closeModal(modal);
        return;
    }

    const overlay = event.target.closest('[data-hb-modal-overlay]');
    if (overlay && !overlay.hasAttribute('data-hb-modal-static-overlay')) {
        const modal = overlay.closest('[data-hb-modal]');
        if (modal) closeModal(modal);
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    closeAllDropdowns();
    setDrawer(false);
    document.querySelectorAll('[data-hb-modal]:not(.hidden)').forEach((modal) => {
        if (!modal.hasAttribute('data-hb-modal-static')) closeModal(modal);
    });
});

/* ---------- Guard duplicate submits on Welcome Back login ---------- */
document.addEventListener('submit', (event) => {
    const form = event.target.closest('[data-login-form]');
    if (!form) return;
    if (form.dataset.submitting === '1') {
        event.preventDefault();
        return;
    }
    form.dataset.submitting = '1';
    const btn = form.querySelector('[data-login-submit]');
    if (btn) {
        btn.disabled = true;
        btn.setAttribute('aria-busy', 'true');
        const idle = btn.querySelector('[data-login-idle]');
        const loading = btn.querySelector('[data-login-loading]');
        if (idle) idle.classList.add('hidden');
        if (loading) {
            loading.classList.remove('hidden');
            loading.classList.add('inline-flex');
            loading.removeAttribute('aria-hidden');
        }
    }
});

/* ---------- Guard duplicate submits on apply forms ---------- */
document.addEventListener('submit', (event) => {
    const form = event.target.closest('[data-apply-form]');
    if (!form || form.dataset.submitting === '1') {
        if (form && form.dataset.submitting === '1') event.preventDefault();
        return;
    }
    form.dataset.submitting = '1';
    const btn = form.querySelector('[data-apply-submit]');
    if (btn) {
        btn.disabled = true;
        btn.setAttribute('aria-busy', 'true');
        btn.classList.add('opacity-50', 'cursor-not-allowed');
    }
});

/* ---------- Busy state for synchronous onboarding and list filter submissions ---------- */
document.addEventListener('submit', (event) => {
    const form = event.target.closest('[data-loading-form]');
    if (!form) return;
    if (form.dataset.submitting === '1') {
        event.preventDefault();
        return;
    }
    form.dataset.submitting = '1';
    const button = form.querySelector('[data-loading-submit]');
    if (!button) return;
    button.disabled = true;
    button.setAttribute('aria-busy', 'true');
    const idle = button.querySelector('[data-submit-idle]');
    const loading = button.querySelector('[data-submit-loading]');
    if (idle) idle.classList.add('hidden');
    if (loading) {
        loading.classList.remove('hidden');
        loading.removeAttribute('aria-hidden');
    }
});

/* ---------- Guard duplicate submits on change-password forms ---------- */
document.addEventListener('submit', (event) => {
    const form = event.target.closest('[data-password-change-form]');
    if (!form) return;
    if (form.dataset.submitting === '1') {
        event.preventDefault();
        return;
    }
    form.dataset.submitting = '1';
    const btn = form.querySelector('[data-password-change-submit]');
    if (btn) {
        btn.disabled = true;
        btn.setAttribute('aria-busy', 'true');
        btn.classList.add('opacity-50', 'cursor-not-allowed');
        const idle = btn.querySelector('[data-password-change-idle]');
        const loading = btn.querySelector('[data-password-change-loading]');
        if (idle) idle.classList.add('hidden');
        if (loading) {
            loading.classList.remove('hidden');
            loading.classList.add('inline-flex');
            loading.removeAttribute('aria-hidden');
        }
    }
});

/* ---------- Guard duplicate submits on candidate skills forms ---------- */
document.addEventListener('submit', (event) => {
    const form = event.target.closest('[data-skills-add-form], [data-skills-remove-form]');
    if (!form) return;
    if (form.dataset.submitting === '1') {
        event.preventDefault();
        return;
    }
    form.dataset.submitting = '1';
    const btn = form.querySelector('[data-skills-add-submit], [data-skills-remove-submit]');
    if (btn) {
        btn.disabled = true;
        btn.setAttribute('aria-busy', 'true');
        btn.classList.add('opacity-50', 'cursor-not-allowed');
        const idle = btn.querySelector('[data-skills-add-idle]');
        const loading = btn.querySelector('[data-skills-add-loading]');
        if (idle) idle.classList.add('hidden');
        if (loading) {
            loading.classList.remove('hidden');
            loading.classList.add('inline-flex');
            loading.removeAttribute('aria-hidden');
        }
    }
});

/* ---------- Profile avatar local preview (never Base64-in-session; no upload without API) ---------- */
(function initProfileAvatar() {
    const root = document.querySelector('[data-profile-avatar]');
    if (!root) return;

    const input = root.querySelector('[data-avatar-input]');
    const clearBtn = root.querySelector('[data-avatar-clear]');
    const image = root.querySelector('[data-avatar-image]');
    const initials = root.querySelector('[data-avatar-initials]');
    const errorEl = root.querySelector('[data-avatar-error]');
    const statusEl = root.querySelector('[data-avatar-status]');
    const maxBytes = Number(root.getAttribute('data-max-bytes') || 0);
    const acceptExt = String(root.getAttribute('data-accept-ext') || '')
        .split(',')
        .map((s) => s.trim().toLowerCase())
        .filter(Boolean);
    const msgInvalid = root.getAttribute('data-msg-invalid') || 'Use a JPEG, PNG, or WebP image.';
    const msgTooLarge = root.getAttribute('data-msg-too-large') || 'Choose an image under 2 MB.';
    const msgPreviewReady = root.getAttribute('data-msg-preview-ready') || 'Local preview ready.';
    const msgPreviewCleared = root.getAttribute('data-msg-preview-cleared') || 'Local preview cleared.';
    let objectUrl = null;

    function setError(msg) {
        if (!errorEl) return;
        errorEl.textContent = msg || '';
        errorEl.classList.toggle('hidden', !msg);
    }

    function setStatus(msg) {
        if (!statusEl) return;
        statusEl.textContent = msg || '';
        statusEl.classList.toggle('hidden', !msg);
    }

    function revoke() {
        if (objectUrl) {
            URL.revokeObjectURL(objectUrl);
            objectUrl = null;
        }
    }

    function showInitials() {
        if (image) {
            image.src = '';
            image.classList.add('hidden');
        }
        if (initials) initials.classList.remove('hidden');
    }

    function showPreview(url) {
        if (image) {
            image.src = url;
            image.classList.remove('hidden');
        }
        if (initials) initials.classList.add('hidden');
    }

    if (input) {
        input.addEventListener('change', () => {
            setError('');
            setStatus('');
            const file = input.files && input.files[0];
            if (!file) return;

            const name = String(file.name || '');
            const ext = name.includes('.') ? name.split('.').pop().toLowerCase() : '';
            const typeOk = acceptExt.length === 0 || acceptExt.includes(ext) || String(file.type || '').startsWith('image/');
            if (!typeOk || (file.type && !['image/jpeg', 'image/png', 'image/webp'].includes(file.type))) {
                setError(msgInvalid);
                input.value = '';
                return;
            }
            if (maxBytes > 0 && file.size > maxBytes) {
                setError(msgTooLarge);
                input.value = '';
                return;
            }

            revoke();
            objectUrl = URL.createObjectURL(file);
            showPreview(objectUrl);
            if (clearBtn) clearBtn.disabled = false;
            setStatus(msgPreviewReady);
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            revoke();
            if (input) input.value = '';
            showInitials();
            clearBtn.disabled = true;
            setError('');
            setStatus(msgPreviewCleared);
        });
    }

    window.addEventListener('beforeunload', revoke);
})();