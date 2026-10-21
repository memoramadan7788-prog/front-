/**
 * Hirebridge frontend interactions (Stage 2).
 * Lightweight, dependency-free UI behaviors.
 */

function qs(selector, root = document) {
    return root.querySelector(selector);
}

function qsa(selector, root = document) {
    return Array.from(root.querySelectorAll(selector));
}

function closeDropdown(root) {
    if (!root) return;
    root.classList.add('hidden');
    const trigger = document.querySelector(`[aria-controls="${root.id}"]`);
    if (trigger) {
        trigger.setAttribute('aria-expanded', 'false');
    }
}

function openDropdown(root) {
    if (!root) return;
    root.classList.remove('hidden');
    const trigger = document.querySelector(`[aria-controls="${root.id}"]`);
    if (trigger) {
        trigger.setAttribute('aria-expanded', 'true');
    }
}

function initDropdowns() {
    qsa('[data-dropdown-trigger]').forEach((trigger) => {
        const id = trigger.getAttribute('aria-controls');
        const panel = id ? document.getElementById(id) : null;
        if (!panel) return;

        trigger.addEventListener('click', (event) => {
            event.stopPropagation();
            const isOpen = !panel.classList.contains('hidden');
            qsa('[data-dropdown-panel]').forEach((other) => {
                if (other !== panel) closeDropdown(other);
            });
            if (isOpen) {
                closeDropdown(panel);
            } else {
                openDropdown(panel);
            }
        });
    });

    document.addEventListener('click', (event) => {
        qsa('[data-dropdown-panel]').forEach((panel) => {
            const trigger = document.querySelector(`[aria-controls="${panel.id}"]`);
            if (
                !panel.classList.contains('hidden') &&
                !panel.contains(event.target) &&
                trigger &&
                !trigger.contains(event.target)
            ) {
                closeDropdown(panel);
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        qsa('[data-dropdown-panel]').forEach((panel) => closeDropdown(panel));
        closeMobileSidebar();
        qsa('[data-hb-modal].flex').forEach((modal) => closeModal(modal));
    });
}

function openMobileSidebar() {
    const drawer = qs('[data-mobile-sidebar]');
    const backdrop = qs('[data-mobile-sidebar-backdrop]');
    if (!drawer || !backdrop) return;
    drawer.classList.remove('-translate-x-full', 'rtl:translate-x-full');
    drawer.classList.add('translate-x-0');
    drawer.classList.add('is-open');
    backdrop.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    const toggle = qs('[data-mobile-sidebar-toggle]');
    if (toggle) toggle.setAttribute('aria-expanded', 'true');
}

function closeMobileSidebar() {
    const drawer = qs('[data-mobile-sidebar]');
    const backdrop = qs('[data-mobile-sidebar-backdrop]');
    if (!drawer || !backdrop) return;
    drawer.classList.add('-translate-x-full', 'rtl:translate-x-full');
    drawer.classList.remove('translate-x-0', 'is-open');
    backdrop.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    const toggle = qs('[data-mobile-sidebar-toggle]');
    if (toggle) toggle.setAttribute('aria-expanded', 'false');
}

function initMobileSidebar() {
    const toggle = qs('[data-mobile-sidebar-toggle]');
    const backdrop = qs('[data-mobile-sidebar-backdrop]');
    const closeBtn = qs('[data-mobile-sidebar-close]');
    if (toggle) toggle.addEventListener('click', openMobileSidebar);
    if (backdrop) backdrop.addEventListener('click', closeMobileSidebar);
    if (closeBtn) closeBtn.addEventListener('click', closeMobileSidebar);
}

let activeModal = null;
let modalTrigger = null;
let modalKeyHandler = null;

function getFocusable(container) {
    return qsa(
        'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])',
        container,
    ).filter((el) => !el.hasAttribute('disabled') && el.getAttribute('aria-hidden') !== 'true');
}

function openModal(modal, trigger = null) {
    modalTrigger = trigger;
    activeModal = modal;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modal.setAttribute('data-hb-modal-open', 'true');
    document.body.classList.add('overflow-hidden');

    const focusables = getFocusable(modal);
    const focusTarget = focusables[0] || modal;
    window.setTimeout(() => focusTarget.focus(), 0);

    modalKeyHandler = (event) => {
        if (!activeModal) return;
        if (event.key === 'Escape') {
            event.preventDefault();
            closeModal(activeModal);
            return;
        }
        if (event.key !== 'Tab' || !activeModal.hasAttribute('data-hb-modal-focus-trap')) return;
        const items = getFocusable(activeModal);
        if (items.length === 0) return;
        const first = items[0];
        const last = items[items.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    };
    document.addEventListener('keydown', modalKeyHandler);
}

function closeModal(modal) {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    modal.removeAttribute('data-hb-modal-open');
    if (activeModal === modal) {
        activeModal = null;
        document.body.classList.remove('overflow-hidden');
        if (modalKeyHandler) {
            document.removeEventListener('keydown', modalKeyHandler);
            modalKeyHandler = null;
        }
        if (modalTrigger && typeof modalTrigger.focus === 'function') {
            modalTrigger.focus();
        }
        modalTrigger = null;
    }
}

function initModals() {
    qsa('[data-open-modal]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const modal = document.getElementById(btn.getAttribute('data-open-modal'));
            if (modal) openModal(modal, btn);
        });
    });

    qsa('[data-hb-modal]').forEach((modal) => {
        modal.querySelectorAll('[data-hb-modal-close], [data-hb-modal-overlay]').forEach((el) => {
            el.addEventListener('click', () => closeModal(modal));
        });
    });
}

function initTabs() {
    qsa('[data-tabs]').forEach((root) => {
        const tabs = qsa('[role="tab"]', root);
        const panels = qsa('[role="tabpanel"]', root);

        const activate = (tab) => {
            const target = tab.getAttribute('aria-controls');
            tabs.forEach((t) => {
                const selected = t === tab;
                t.setAttribute('aria-selected', selected ? 'true' : 'false');
                t.tabIndex = selected ? 0 : -1;
                t.classList.toggle('border-hb-blue', selected);
                t.classList.toggle('text-hb-blue', selected);
                t.classList.toggle('border-transparent', !selected);
                t.classList.toggle('text-hb-muted', !selected);
            });
            panels.forEach((panel) => {
                const show = panel.id === target;
                panel.classList.toggle('hidden', !show);
                panel.hidden = !show;
            });
        };

        // Ensure initial panel visibility matches aria-selected.
        const selected = tabs.find((t) => t.getAttribute('aria-selected') === 'true') || tabs[0];
        if (selected) activate(selected);

        tabs.forEach((tab, index) => {
            tab.addEventListener('click', () => activate(tab));
            tab.addEventListener('keydown', (event) => {
                let next = null;
                if (event.key === 'ArrowRight' || event.key === 'ArrowLeft') {
                    event.preventDefault();
                    const rtl = document.documentElement.dir === 'rtl';
                    const delta = event.key === 'ArrowRight' ? (rtl ? -1 : 1) : rtl ? 1 : -1;
                    next = tabs[(index + delta + tabs.length) % tabs.length];
                } else if (event.key === 'Home') {
                    event.preventDefault();
                    next = tabs[0];
                } else if (event.key === 'End') {
                    event.preventDefault();
                    next = tabs[tabs.length - 1];
                }
                if (next) {
                    next.focus();
                    activate(next);
                }
            });
        });
    });
}

function initSlotPicker() {
    qsa('[data-slot-picker]').forEach((root) => {
        qsa('[data-slot-option]', root).forEach((option) => {
            if (option.hasAttribute('disabled') || option.getAttribute('aria-disabled') === 'true') {
                return;
            }
            option.addEventListener('click', () => {
                qsa('[data-slot-option]', root).forEach((o) => {
                    o.setAttribute('aria-pressed', 'false');
                    o.classList.remove('border-hb-blue', 'bg-hb-info-soft', 'ring-2', 'ring-hb-blue/30');
                });
                option.setAttribute('aria-pressed', 'true');
                option.classList.add('border-hb-blue', 'bg-hb-info-soft', 'ring-2', 'ring-hb-blue/30');
                const input = qs('input[data-slot-value]', root);
                if (input) {
                    input.value = option.getAttribute('data-slot-value') || '';
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        });
    });
}

function t(key, replace = {}) {
    if (typeof window.__ === 'function') {
        return window.__(key, replace);
    }
    return key;
}

function applyTheme(preference) {
    const stored = preference || localStorage.getItem('hb_theme') || 'system';
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    const dark = stored === 'dark' || (stored === 'system' && prefersDark);
    document.documentElement.classList.toggle('dark', dark);
    document.documentElement.dataset.theme = stored;
    try {
        localStorage.setItem('hb_theme', stored);
    } catch {
        // Ignore quota / private mode.
    }

    qsa('[data-theme-option]').forEach((btn) => {
        const active = btn.getAttribute('data-theme-option') === stored;
        btn.classList.toggle('bg-hb-info-soft', active);
        btn.classList.toggle('text-hb-blue', active);
        btn.classList.toggle('text-hb-muted', !active);
        btn.setAttribute('aria-pressed', active ? 'true' : 'false');
    });
}

function initThemeSwitch() {
    applyTheme(localStorage.getItem('hb_theme') || 'system');

    qsa('[data-theme-option]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const value = btn.getAttribute('data-theme-option');
            if (!value) return;
            applyTheme(value);
        });
    });

    if (window.matchMedia) {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
            if ((localStorage.getItem('hb_theme') || 'system') === 'system') {
                applyTheme('system');
            }
        });
    }
}

function initLanguagePreview() {
    // Locale is applied server-side via cookie + full page navigation.
    // Keep a light client sync so aria state stays accurate after soft navigations.
    const current = document.documentElement.lang?.startsWith('ar') ? 'ar' : 'en';
    try {
        localStorage.setItem('hb_locale', current);
    } catch {
        // Ignore.
    }
}

function initPublicMobileNav() {
    const toggle = qs('[data-public-nav-toggle]');
    const panel = document.getElementById('public-mobile-nav');
    if (!toggle || !panel) return;
    toggle.addEventListener('click', () => {
        const open = panel.classList.toggle('hidden') === false;
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
}

function setFieldError(field, message) {
    if (!field) return;
    const input = qs('input, select, textarea', field) || qs('[data-otp-digit]', field);
    const error = qs('.hb-field-error', field) || qs('[role="alert"]', field);
    if (input && input.hasAttribute) {
        qsa('input', field).forEach((el) => el.setAttribute('aria-invalid', message ? 'true' : 'false'));
    }
    if (error) {
        error.textContent = message || '';
        error.classList.toggle('hidden', !message);
    }
}

function isValidEmail(value) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(value || '').trim());
}

function passwordRules(value) {
    const v = String(value || '');
    return {
        length: v.length >= 12,
        number: /\d/.test(v),
        symbol: /[^A-Za-z0-9]/.test(v),
    };
}

function passwordStrengthLabel(rules) {
    const score = Object.values(rules).filter(Boolean).length;
    if (!score) return '';
    if (score === 3) return '● Strong password';
    if (score === 2) return '● Almost there';
    return '● Needs more strength';
}

function initPasswordToggles() {
    qsa('[data-password-toggle]').forEach((btn) => {
        const id = btn.getAttribute('aria-controls');
        const input = id ? document.getElementById(id) : qs('[data-password-input]', btn.parentElement);
        if (!input) return;
        const showIcon = qs('[data-icon-show]', btn);
        const hideIcon = qs('[data-icon-hide]', btn);

        btn.addEventListener('click', () => {
            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            btn.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
            if (showIcon) showIcon.classList.toggle('hidden', !showing);
            if (hideIcon) hideIcon.classList.toggle('hidden', showing);
        });
    });
}

function updatePasswordRequirements(root, value) {
    const rules = passwordRules(value);
    qsa('[data-req]', root).forEach((item) => {
        const key = item.getAttribute('data-req');
        const ok = Boolean(rules[key]);
        const icon = qs('[data-req-icon]', item);
        item.classList.toggle('text-hb-success', ok);
        item.classList.toggle('text-hb-muted', !ok);
        if (icon) icon.textContent = ok ? '☑' : '○';
    });
    const strength = qs('[data-password-strength]', root);
    if (strength) {
        strength.textContent = value ? passwordStrengthLabel(rules) : '';
        strength.classList.toggle('text-hb-success', Object.values(rules).every(Boolean));
    }
    return rules;
}

function initPasswordRules() {
    qsa('[data-password-rules]').forEach((input) => {
        const root = input.closest('[data-auth-page]') || document;
        const sync = () => updatePasswordRequirements(root, input.value);
        input.addEventListener('input', sync);
        sync();
    });

    qsa('[data-password-confirm]').forEach((confirm) => {
        const form = confirm.closest('form');
        const source = form ? qs('[data-password-confirm-source]', form) : null;
        const matchEl = form ? qs('[data-password-match]', form) : null;
        if (!source || !matchEl) return;

        const sync = () => {
            if (!confirm.value) {
                matchEl.textContent = '';
                return;
            }
            const ok = confirm.value === source.value;
            matchEl.textContent = ok ? 'Passwords match.' : 'Passwords do not match.';
            matchEl.classList.toggle('text-hb-success', ok);
            matchEl.classList.toggle('text-hb-error', !ok);
        };
        confirm.addEventListener('input', sync);
        source.addEventListener('input', sync);
    });
}

function initAccountTypeToggle() {
    qsa('[data-account-type-group]').forEach((group) => {
        const form = group.closest('[data-auth-page]')?.querySelector('[data-auth-form]') || group.closest('form');
        const input = form ? qs('[data-account-type-input]', form) : null;
        const submit = form ? qs('[data-auth-submit]', form) : null;
        const options = qsa('[data-account-type]', group);

        options.forEach((btn) => {
            btn.addEventListener('click', () => {
                const type = btn.getAttribute('data-account-type');
                options.forEach((other) => {
                    const active = other === btn;
                    other.setAttribute('aria-checked', active ? 'true' : 'false');
                    other.dataset.active = active ? 'true' : 'false';
                    other.classList.toggle('text-hb-navy', active);
                    other.classList.toggle('text-hb-muted', !active);
                });
                if (input) input.value = type || 'candidate';
                if (submit) {
                    const label =
                        type === 'company'
                            ? submit.getAttribute('data-submit-label-company')
                            : submit.getAttribute('data-submit-label-candidate');
                    if (label) submit.textContent = label;
                }
            });
        });
    });
}

function initOtpInputs() {
    qsa('[data-otp-group]').forEach((group) => {
        const digits = qsa('[data-otp-digit]', group);
        const hidden = qs('[data-otp-value]', group.closest('form') || group.parentElement);
        if (!digits.length) return;

        const syncValue = () => {
            if (hidden) hidden.value = digits.map((d) => d.value).join('');
        };

        digits.forEach((digit, index) => {
            digit.addEventListener('input', (event) => {
                const raw = String(event.target.value || '').replace(/\D/g, '');
                if (raw.length > 1) {
                    // Paste into this cell
                    raw.slice(0, digits.length - index).split('').forEach((ch, offset) => {
                        digits[index + offset].value = ch;
                    });
                    const focusIndex = Math.min(index + raw.length, digits.length - 1);
                    digits[focusIndex].focus();
                } else {
                    digit.value = raw.slice(-1);
                    if (digit.value && digits[index + 1]) digits[index + 1].focus();
                }
                syncValue();
            });

            digit.addEventListener('keydown', (event) => {
                if (event.key === 'Backspace' && !digit.value && digits[index - 1]) {
                    digits[index - 1].focus();
                }
                if (event.key === 'ArrowLeft' && digits[index - 1]) {
                    event.preventDefault();
                    digits[index - 1].focus();
                }
                if (event.key === 'ArrowRight' && digits[index + 1]) {
                    event.preventDefault();
                    digits[index + 1].focus();
                }
            });

            digit.addEventListener('paste', (event) => {
                event.preventDefault();
                const text = (event.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
                text.slice(0, digits.length).split('').forEach((ch, offset) => {
                    digits[offset].value = ch;
                });
                syncValue();
                digits[Math.min(text.length, digits.length) - 1]?.focus();
            });
        });
    });
}

function setSubmitting(form, submitting) {
    const btn = qs('[data-auth-submit]', form);
    if (!btn) return;
    btn.disabled = submitting;
    btn.setAttribute('aria-busy', submitting ? 'true' : 'false');
    if (submitting) {
        btn.dataset.originalLabel = btn.textContent;
        btn.textContent = t('status.please_wait');
    } else if (btn.dataset.originalLabel) {
        btn.textContent = btn.dataset.originalLabel;
    }
}

function validateAuthForm(form) {
    const type = form.getAttribute('data-auth-form');
    let valid = true;
    const firstInvalid = [];

    const require = (name, message, check) => {
        const field = qs(`[data-field="${name}"]`, form);
        const input = field ? qs('input', field) : null;
        const value = input ? input.value : '';
        const ok = check(value, input);
        setFieldError(field, ok ? '' : message);
        if (!ok) {
            valid = false;
            firstInvalid.push(input || qs('[data-otp-digit]', field));
        }
    };

    if (type === 'login' || type === 'forgot-password') {
        require('email', 'Enter a valid email address.', isValidEmail);
    }
    if (type === 'login') {
        require('password', 'Enter your password.', (v) => v.length > 0);
    }
    if (type === 'register') {
        require('first_name', 'First name is required.', (v) => v.trim().length > 0);
        require('last_name', 'Last name is required.', (v) => v.trim().length > 0);
        require('email', 'Enter a valid email address.', isValidEmail);
        require('password', 'Use at least 12 characters, with a number and a symbol.', (v) => {
            const rules = passwordRules(v);
            return rules.length && rules.number && rules.symbol;
        });
        const termsField = qs('[data-field="terms"]', form);
        const terms = qs('#register-terms', form);
        const termsOk = terms?.checked;
        setFieldError(termsField, termsOk ? '' : 'Agree to the Terms of Service to continue.');
        const termsError = qs('#register-terms-error', form);
        if (termsError) {
            termsError.textContent = termsOk ? '' : 'Agree to the Terms of Service to continue.';
            termsError.classList.toggle('hidden', termsOk);
        }
        if (!termsOk) {
            valid = false;
            firstInvalid.push(terms);
        }
    }
    if (type === 'reset-password') {
        require('password', 'Use at least 12 characters, with a number and a symbol.', (v) => {
            const rules = passwordRules(v);
            return rules.length && rules.number && rules.symbol;
        });
        require('password_confirmation', 'Passwords must match.', (v, input) => {
            const source = qs('[data-password-confirm-source]', form);
            return v.length > 0 && source && v === source.value;
        });
    }
    if (type === 'verify-email') {
        const field = qs('[data-field="code"]', form);
        const hidden = qs('[data-otp-value]', form);
        const code = hidden?.value || '';
        const ok = /^\d{6}$/.test(code);
        setFieldError(field, ok ? '' : 'Enter the 6-digit verification code.');
        if (!ok) {
            valid = false;
            firstInvalid.push(qs('[data-otp-digit]', field));
        }
    }

    if (!valid && firstInvalid[0]) firstInvalid[0].focus();
    return valid;
}

function initAuthForms() {
    qsa('[data-auth-form]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            const note = qs('[data-auth-frontend-note]', form);
            if (note) note.classList.add('hidden');

            if (!validateAuthForm(form)) return;

            setSubmitting(form, true);
            window.setTimeout(() => {
                setSubmitting(form, false);
                if (note) {
                    note.classList.remove('hidden');
                    note.focus?.();
                }
            }, 450);
        });
    });

    qsa('[data-resend-code]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const note = qs('[data-resend-note]', btn.closest('[data-auth-page]') || document);
            btn.disabled = true;
            if (note) note.classList.remove('hidden');
        });
    });
}

function formatFileSize(bytes) {
    if (!bytes && bytes !== 0) return '';
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

function initFilePreviews(root) {
    qsa('[data-file-input]', root).forEach((input) => {
        input.addEventListener('change', () => {
            const file = input.files && input.files[0];
            const field = input.closest('[data-field]') || input.parentElement;
            const nameEl = qs('[data-file-name]', field);
            const metaEl = qs('[data-file-meta]', field);
            const chip = qs('[data-file-chip]', field);
            const kind = input.getAttribute('data-file-preview');

            if (!file) {
                if (nameEl) {
                    nameEl.textContent = '';
                    nameEl.classList.add('hidden');
                }
                if (chip) chip.classList.add('hidden');
                return;
            }

            if (nameEl) {
                nameEl.textContent = file.name;
                nameEl.classList.remove('hidden');
            }
            if (metaEl) metaEl.textContent = `${formatFileSize(file.size)} · Local selection only`;
            if (chip) chip.classList.remove('hidden');

            if (kind === 'photo' || kind === 'logo') {
                const preview = qs(kind === 'photo' ? '[data-photo-preview]' : '[data-logo-preview]', root);
                if (preview && file.type.startsWith('image/')) {
                    const url = URL.createObjectURL(file);
                    preview.innerHTML = `<img src="${url}" alt="" class="h-full w-full object-cover">`;
                }
            }

            setFieldError(field, '');
        });
    });
}

function initCharCounters(root) {
    qsa('[data-char-count-input]', root).forEach((input) => {
        const counter = qs('[data-char-count]', input.closest('[data-field]') || root);
        if (!counter) return;
        const max = Number(input.getAttribute('maxlength') || 0);
        const sync = () => {
            counter.textContent = max ? `${input.value.length} / ${max}` : String(input.value.length);
        };
        input.addEventListener('input', sync);
        sync();
    });
}

function initExperienceMode(root) {
    const group = qs('[data-experience-mode-group]', root);
    if (!group) return;
    const hidden = qs('[data-has-no-experience]', root);
    const yearsField = qs('[data-years-field]', root);
    const options = qsa('[data-experience-mode]', group);

    options.forEach((btn) => {
        btn.addEventListener('click', () => {
            const mode = btn.getAttribute('data-experience-mode');
            options.forEach((other) => {
                const active = other === btn;
                other.setAttribute('aria-checked', active ? 'true' : 'false');
                other.dataset.active = active ? 'true' : 'false';
                other.classList.toggle('border-hb-blue', active);
                other.classList.toggle('bg-hb-info-soft', active);
                other.classList.toggle('text-hb-blue', active);
                other.classList.toggle('border-hb-border', !active);
                other.classList.toggle('bg-hb-surface', !active);
                other.classList.toggle('text-hb-muted', !active);
            });
            const none = mode === 'none';
            if (hidden) hidden.value = none ? '1' : '0';
            if (yearsField) {
                yearsField.classList.toggle('hidden', none);
                const yearsInput = qs('input', yearsField);
                if (yearsInput) yearsInput.disabled = none;
            }
        });
    });
}

const COMPANY_STEP_LABELS = [
    'Step 1 of 4 · Company identity',
    'Step 2 of 4 · Workspace details',
    'Step 3 of 4 · Hiring preferences',
    'Step 4 of 4 · Invite your team',
];

const CANDIDATE_STEP_LABELS = [
    'Step 1 of 4 · Basics',
    'Step 2 of 4 · Resume import',
    'Step 3 of 4 · Skills & work preferences',
    'Step 4 of 4 · Review & finish',
];

function updateStepper(root, current) {
    const stepper = qs('[data-onboarding-stepper]', root);
    const variant = stepper?.getAttribute('data-stepper-variant') || 'markers';

    qsa('[data-stepper-item]', root).forEach((item) => {
        const number = Number(item.getAttribute('data-step'));
        const marker = qs('[data-stepper-marker]', item);
        const label = qs('[data-stepper-label]', item);
        const isComplete = number < current;
        const isCurrent = number === current;
        if (isCurrent) item.setAttribute('aria-current', 'step');
        else item.removeAttribute('aria-current');

        if (variant === 'underline') {
            if (marker) {
                marker.textContent = isComplete ? '✓' : String(number);
                marker.classList.toggle('sr-only', !isComplete);
                marker.classList.toggle('text-hb-success', isComplete);
            }
            if (label) {
                label.classList.toggle('border-hb-blue', isCurrent);
                label.classList.toggle('text-hb-navy', isCurrent);
                label.classList.toggle('border-transparent', !isCurrent);
                label.classList.toggle('text-hb-muted', !isCurrent);
            }
            return;
        }

        if (marker) {
            marker.textContent = isComplete ? '✓' : String(number);
            marker.className =
                'inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-semibold ' +
                (isComplete
                    ? 'bg-hb-success text-white'
                    : isCurrent
                      ? 'bg-hb-blue text-white'
                      : 'bg-hb-page text-hb-muted ring-1 ring-hb-border');
        }
        if (label) {
            label.classList.toggle('text-hb-navy', isCurrent || isComplete);
            label.classList.toggle('text-hb-muted', !isCurrent && !isComplete);
        }
    });
}

function showOnboardingStep(root, step) {
    const total = Number(root.getAttribute('data-onboarding-total') || 1);
    root.dataset.currentStep = String(step);

    qsa('[data-onboarding-panel]', root).forEach((panel) => {
        const panelStep = Number(panel.getAttribute('data-step'));
        const show = panelStep === step;
        panel.classList.toggle('hidden', !show);
        panel.hidden = !show;
    });

    updateStepper(root, step);

    const pageRoot = root.closest('main') || document;
    const stepLabel = qs('[data-onboarding-step-label]', pageRoot);
    const type = root.getAttribute('data-onboarding');
    if (stepLabel) {
        if (type === 'company') {
            stepLabel.textContent = COMPANY_STEP_LABELS[step - 1] || `Step ${step} of ${total}`;
        }
        if (type === 'candidate') {
            stepLabel.textContent = CANDIDATE_STEP_LABELS[step - 1] || `Step ${step} of ${total}`;
        }
    }

    const back = qs('[data-onboarding-back]', root);
    const next = qs('[data-onboarding-next]', root);
    const finish = qs('[data-onboarding-finish]', root);
    const saveHint = qs('[data-onboarding-save-hint]', root);
    if (back) back.disabled = step <= 1;
    if (next) {
        next.classList.toggle('hidden', step >= total);
        if (type === 'candidate') {
            next.textContent = step === 3 ? 'Continue to review' : 'Continue';
        }
        if (type === 'company') {
            next.textContent = 'Save & continue';
        }
    }
    if (finish) finish.classList.toggle('hidden', step < total);
    if (saveHint) saveHint.classList.toggle('hidden', type === 'candidate' && step !== 3);

    if (type === 'candidate') {
        syncCandidatePreview(root, step);
    }
}

function validateOnboardingStep(root, step) {
    const type = root.getAttribute('data-onboarding');
    const panel = qs(`[data-onboarding-panel][data-step="${step}"]`, root);
    if (!panel) return true;
    let valid = true;
    const focusables = [];

    const requireText = (name, message) => {
        const field = qs(`[data-field="${name}"]`, panel);
        const input = field ? qs('input, select, textarea', field) : null;
        const ok = input && String(input.value || '').trim().length > 0;
        setFieldError(field, ok ? '' : message);
        if (!ok) {
            valid = false;
            focusables.push(input);
        }
    };

    if (type === 'candidate') {
        if (step === 1) {
            requireText('headline', 'Enter a professional headline.');
            requireText('location', 'Enter your location.');
            requireText('years_experience', 'Enter years of experience.');
        }
        if (step === 2) {
            const field = qs('[data-field="resume"]', panel);
            const input = field ? qs('input[type="file"]', field) : null;
            const file = input?.files?.[0];
            const demoName = qs('[data-file-name]', field)?.textContent?.trim();
            let message = '';
            if (file && file.size > 5 * 1024 * 1024) {
                message = 'Choose a file under 5 MB for this frontend check.';
            } else if (!file && !demoName) {
                message = 'Select a CV / resume file to continue.';
            }
            setFieldError(field, message);
            if (message) {
                valid = false;
                focusables.push(input);
            }
        }
        if (step === 3) {
            requireText('preferred_roles', 'Enter at least one preferred role.');
            requireText('home_location', 'Enter your home location.');
            requireText('work_model', 'Select a work model.');
            requireText('skills', 'Enter at least one skill.');
        }
        if (step === 4) {
            syncCandidateReview(root);
        }
    }

    if (type === 'company') {
        if (step === 1) {
            requireText('legal_name', 'Enter the legal company name.');
            const docField = qs('[data-field="document"]', panel);
            const docInput = docField ? qs('input[type="file"]', docField) : null;
            const file = docInput?.files?.[0];
            setFieldError(docField, file ? '' : 'Select a verification document.');
            if (!file) {
                valid = false;
                focusables.push(docInput);
            }
        }
        if (step === 2) {
            requireText('display_name', 'Enter a company display name.');
            requireText('workspace_slug', 'Enter a workspace URL slug.');
            const slugField = qs('[data-field="workspace_slug"]', panel);
            const slugInput = slugField ? qs('input', slugField) : null;
            if (slugInput && slugInput.value && !/^[a-z0-9-]+$/.test(slugInput.value)) {
                setFieldError(slugField, 'Use lowercase letters, numbers, and hyphens only.');
                valid = false;
                focusables.push(slugInput);
            }
            requireText('website', 'Enter a company website.');
            requireText('industry', 'Select an industry.');
            requireText('headquarters', 'Enter headquarters location.');
            requireText('timezone', 'Select a default timezone.');
            const authField = qs('[data-field="authorized"]', panel);
            const authInput = qs('#company-authorized', panel);
            const authOk = Boolean(authInput?.checked);
            const authError = qs('#company-authorized-error', panel);
            if (authError) {
                authError.textContent = authOk ? '' : 'Confirm you are authorized to manage this workspace.';
                authError.classList.toggle('hidden', authOk);
            }
            if (!authOk) {
                valid = false;
                focusables.push(authInput);
            }
            syncCompanyPreview(root);
        }
        if (step === 3) {
            const model = qs('[data-hiring-model-input]', panel)?.value;
            const field = qs('[data-field="hiring_model"]', panel);
            setFieldError(field, model ? '' : 'Choose a hiring model.');
            if (!model) {
                valid = false;
                focusables.push(qs('[data-hiring-model]', panel));
            }
        }
        if (step === 4) {
            qsa('input[type="email"]', panel).forEach((input) => {
                if (input.value && !isValidEmail(input.value)) {
                    valid = false;
                    focusables.push(input);
                    const field = input.closest('[data-field]');
                    setFieldError(field, 'Enter a valid work email, or leave the row blank.');
                }
            });
        }
    }

    if (!valid && focusables[0]) focusables[0].focus();
    return valid;
}

function syncCandidateReview(root) {
    const form = qs('[data-onboarding-form]', root);
    if (!form) return;
    const workModel = qs('[name="work_model"]', form);
    const resumeInput = qs('[name="resume"]', form);
    const resumeChip = qs('[data-file-name]', qs('[data-field="resume"]', form) || form);
    const set = (key, value) => {
        const el = qs(`[data-review="${key}"]`, root);
        if (el) el.textContent = value;
    };
    set('headline', qs('[name="headline"]', form)?.value || '—');
    set('location', qs('[name="location"]', form)?.value || '—');
    set('roles', qs('[name="preferred_roles"]', form)?.value || '—');
    set('skills', qs('[name="skills"]', form)?.value || '—');
    set(
        'work_model',
        workModel?.selectedOptions?.[0]?.textContent?.trim() || workModel?.value || '—'
    );
    set(
        'resume',
        resumeInput?.files?.[0]?.name || resumeChip?.textContent?.trim() || 'No file selected'
    );
}

function syncCandidatePreview(root, step) {
    const form = qs('[data-onboarding-form]', root);
    if (!form) return;
    const headline = qs('[name="headline"]', form)?.value || 'Senior Product Designer';
    const location = qs('[name="location"]', form)?.value || 'Austin, TX';
    const years = qs('[name="years_experience"]', form)?.value || '8';
    const resumeInput = qs('[name="resume"]', form);
    const resumeChip = qs('[data-file-name]', qs('[data-field="resume"]', form) || form);
    const resumeName =
        resumeInput?.files?.[0]?.name || resumeChip?.textContent?.trim() || 'Maya_Chen_Resume.pdf';

    const headlineEl = qs('[data-preview-headline]', root);
    const locationEl = qs('[data-preview-location]', root);
    const yearsEl = qs('[data-preview-years]', root);
    const resumeEl = qs('[data-preview-resume]', root);
    const progressEl = qs('[data-profile-progress]', root);
    const nextEl = qs('[data-profile-next]', root);

    if (headlineEl) headlineEl.textContent = headline;
    if (locationEl) locationEl.textContent = location.split(',')[0]?.trim() || location;
    if (yearsEl) yearsEl.textContent = years;
    if (resumeEl) resumeEl.textContent = resumeName;

    const progress = step <= 1 ? '25% complete' : step === 2 ? '50% complete' : step === 3 ? '75% complete' : '100% complete';
    if (progressEl) progressEl.textContent = progress;
    if (nextEl) {
        nextEl.textContent =
            step >= 4
                ? t('status.profile_ready')
                : t('status.profile_next');
    }
}

function syncCompanyPreview(root) {
    const form = qs('[data-onboarding-form]', root);
    if (!form) return;
    const name = qs('[name="display_name"]', form)?.value || 'Northstar Labs';
    const industrySelect = qs('[name="industry"]', form);
    const industry =
        industrySelect?.selectedOptions?.[0]?.textContent?.trim() || 'Software & collaboration';
    const hq = qs('[name="headquarters"]', form)?.value || 'Austin, TX';
    const shortLocation = hq.split(',')[0]?.trim() || hq;
    const nameEl = qs('[data-preview-name]', root);
    const industryEl = qs('[data-preview-industry]', root);
    const locationEl = qs('[data-preview-location]', root);
    if (nameEl) nameEl.textContent = name;
    if (industryEl) industryEl.textContent = industry;
    if (locationEl) locationEl.textContent = shortLocation;
}

function initHiringModel(root) {
    const group = qs('[data-hiring-model-group]', root);
    if (!group) return;
    const input = qs('[data-hiring-model-input]', root);
    const options = qsa('[data-hiring-model]', group);

    options.forEach((btn) => {
        btn.addEventListener('click', () => {
            const value = btn.getAttribute('data-hiring-model');
            options.forEach((other) => {
                const active = other === btn;
                other.setAttribute('aria-checked', active ? 'true' : 'false');
                other.dataset.active = active ? 'true' : 'false';
                other.classList.toggle('border-hb-blue', active);
                other.classList.toggle('bg-hb-info-soft', active);
                other.classList.toggle('border-hb-border', !active);
                other.classList.toggle('bg-hb-surface', !active);
                const title = qs('span.block.text-sm.font-semibold', other);
                if (title) {
                    title.classList.toggle('text-hb-blue', active);
                    title.classList.toggle('text-hb-navy', !active);
                }
            });
            if (input) input.value = value || '';
        });
    });
}

function initInviteRows(root) {
    const list = qs('[data-invite-rows]', root);
    const addBtn = qs('[data-add-invite]', root);
    if (!list || !addBtn) return;

    addBtn.addEventListener('click', () => {
        const index = qsa('[data-invite-row]', list).length;
        const row = document.createElement('div');
        row.className = 'grid gap-3 sm:grid-cols-[1fr_180px_auto]';
        row.setAttribute('data-invite-row', '');
        row.innerHTML = `
            <div data-field="invite_email_${index}">
                <label class="hb-label" for="invite-email-${index}">Work email</label>
                <input id="invite-email-${index}" name="invites[${index}][email]" type="email" class="hb-input" placeholder="name@company.example">
            </div>
            <div>
                <label class="hb-label" for="invite-role-${index}">Role</label>
                <select id="invite-role-${index}" name="invites[${index}][role]" class="hb-input">
                    <option value="hiring_manager">Hiring manager</option>
                    <option value="interviewer" selected>Interviewer</option>
                    <option value="admin">Company admin</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="button" class="hb-btn-ghost text-hb-muted" data-remove-invite>Remove</button>
            </div>
        `;
        list.appendChild(row);
    });

    list.addEventListener('click', (event) => {
        const btn = event.target.closest('[data-remove-invite]');
        if (!btn) return;
        const rows = qsa('[data-invite-row]', list);
        if (rows.length <= 1) return;
        btn.closest('[data-invite-row]')?.remove();
    });
}

function initCompanyPreviewBindings(root) {
    qsa('[data-preview-source]', root).forEach((input) => {
        input.addEventListener('input', () => syncCompanyPreview(root));
        input.addEventListener('change', () => syncCompanyPreview(root));
    });
    syncCompanyPreview(root);
}

function initOnboarding() {
    qsa('[data-onboarding]').forEach((root) => {
        const total = Number(root.getAttribute('data-onboarding-total') || 1);
        let step = Number(root.getAttribute('data-onboarding-start') || 1);
        if (!Number.isFinite(step) || step < 1) step = 1;
        if (step > total) step = total;
        const form = qs('[data-onboarding-form]', root);
        const note = qs('[data-onboarding-frontend-note]', root);

        initFilePreviews(root);
        initCharCounters(root);
        initExperienceMode(root);
        initHiringModel(root);
        initInviteRows(root);
        initCompanyPreviewBindings(root);
        showOnboardingStep(root, step);

        qs('[data-onboarding-next]', root)?.addEventListener('click', () => {
            if (!validateOnboardingStep(root, step)) return;
            if (step < total) {
                step += 1;
                showOnboardingStep(root, step);
                if (root.getAttribute('data-onboarding') === 'candidate') {
                    if (step === total) syncCandidateReview(root);
                    syncCandidatePreview(root, step);
                }
            }
        });

        qs('[data-onboarding-back]', root)?.addEventListener('click', () => {
            if (step > 1) {
                step -= 1;
                showOnboardingStep(root, step);
                if (note) note.classList.add('hidden');
            }
        });

        form?.addEventListener('submit', (event) => {
            event.preventDefault();
            if (!validateOnboardingStep(root, step)) return;
            const finish = qs('[data-onboarding-finish]', root);
            if (finish) {
                finish.disabled = true;
                finish.setAttribute('aria-busy', 'true');
                const original = finish.textContent;
                finish.textContent = t('status.please_wait');
                window.setTimeout(() => {
                    finish.disabled = false;
                    finish.removeAttribute('aria-busy');
                    finish.textContent = original;
                    if (note) note.classList.remove('hidden');
                }, 450);
            }
        });
    });
}

const CANDIDATE_SAVED_STORAGE_KEY = 'hb_candidate_saved_jobs_v2';

function readSavedJobIds(defaults = []) {
    try {
        const raw = window.localStorage.getItem(CANDIDATE_SAVED_STORAGE_KEY);
        if (!raw) {
            return [...defaults];
        }
        const parsed = JSON.parse(raw);
        if (!Array.isArray(parsed)) {
            return [...defaults];
        }
        return parsed.map(String);
    } catch {
        return [...defaults];
    }
}

function writeSavedJobIds(ids) {
    try {
        window.localStorage.setItem(CANDIDATE_SAVED_STORAGE_KEY, JSON.stringify([...new Set(ids.map(String))]));
    } catch {
        // Preview-only storage; ignore quota / private-mode failures.
    }
}

function parseDefaultSaved(root) {
    try {
        const raw = root.getAttribute('data-default-saved') || '[]';
        const parsed = JSON.parse(raw);
        return Array.isArray(parsed) ? parsed.map(String) : [];
    } catch {
        return [];
    }
}

function updateSaveButton(button, saved) {
    if (!button) return;
    button.setAttribute('aria-pressed', saved ? 'true' : 'false');
    if (button.hasAttribute('data-remove-saved')) {
        button.textContent = 'Remove';
        return;
    }
    const label = qs('[data-save-label]', button) || button;
    if (label === button) {
        button.textContent = saved ? 'Saved' : 'Save';
    } else {
        label.textContent = saved ? 'Saved' : 'Save';
    }
}

function syncSavedCounts(root, count) {
    qsa('[data-saved-count]', root).forEach((el) => {
        el.textContent = String(count);
    });
    qsa('[data-saved-count-label]', root).forEach((el) => {
        el.textContent = count === 1 ? 'saved role' : 'saved roles';
    });
}

function matchesLocationFilter(card, filter) {
    const loc = (card.dataset.location || '').toLowerCase();
    const model = (card.dataset.workModel || '').toLowerCase();
    if (filter === 'all') return true;
    if (filter === 'austin') return loc.includes('austin');
    if (filter === 'remote') return model.includes('remote') || loc.includes('united states');
    if (filter === 'austin_remote') {
        return loc.includes('austin') || model.includes('remote') || loc.includes('united states');
    }
    return true;
}

function initCandidateJobsDiscover(root, savedIds) {
    const results = qs('[data-jobs-results]', root);
    const empty = qs('[data-jobs-empty]', root);
    const qInput = qs('[data-discover-q]', root);
    const locationSelect = qs('[data-discover-location]', root);
    const sortSelect = qs('[data-discover-sort]', root);
    if (!results) return;

    const cards = () => qsa('[data-candidate-job-card]', results);

    const apply = () => {
        const q = (qInput?.value || '').trim().toLowerCase();
        const location = locationSelect?.value || 'all';
        const sort = sortSelect?.value || 'recommended';

        let visibleCards = [];
        cards().forEach((card) => {
            const hay = card.dataset.title || '';
            const tokens = q.split(/\s+/).filter(Boolean);
            const matchQ = !q || tokens.some((part) => hay.includes(part));
            const matchLoc = matchesLocationFilter(card, location);
            const show = matchQ && matchLoc;
            card.classList.toggle('hidden', !show);
            if (show) visibleCards.push(card);
        });

        visibleCards.sort((a, b) => {
            if (sort === 'salary_desc') {
                return Number(b.dataset.salaryMax || 0) - Number(a.dataset.salaryMax || 0);
            }
            if (sort === 'company') {
                return (a.dataset.company || '').localeCompare(b.dataset.company || '');
            }
            if (sort === 'newest') {
                return Number(b.dataset.postedRank || 0) - Number(a.dataset.postedRank || 0);
            }
            return Number(a.dataset.recommendedRank || 99) - Number(b.dataset.recommendedRank || 99);
        });

        visibleCards.forEach((card) => results.appendChild(card));

        qsa('[data-jobs-count]', root).forEach((el) => {
            el.textContent = String(visibleCards.length);
        });
        const countLabel = qs('[data-jobs-count-label]', root);
        if (countLabel) {
            countLabel.textContent =
                visibleCards.length === 1
                    ? '1 recommended role'
                    : `${visibleCards.length} recommended roles`;
        }
        empty?.classList.toggle('hidden', visibleCards.length !== 0);
        results.classList.toggle('hidden', visibleCards.length === 0);
    };

    qInput?.addEventListener('input', apply);
    locationSelect?.addEventListener('change', apply);
    sortSelect?.addEventListener('change', apply);

    qs('[data-clear-discover]', root)?.addEventListener('click', () => {
        if (qInput) qInput.value = '';
        if (locationSelect) locationSelect.value = 'austin_remote';
        if (sortSelect) sortSelect.value = 'recommended';
        apply();
    });

    qs('[data-save-search]', root)?.addEventListener('click', () => {
        qs('[data-save-search-note]', root)?.classList.remove('hidden');
    });

    cards().forEach((card) => {
        updateSaveButton(qs('[data-save-job]', card), savedIds.includes(card.dataset.jobId));
    });

    apply();
}

function initCandidateJobsSaved(root, savedIds) {
    const rows = qsa('[data-saved-row]', root);
    const empty = qs('[data-saved-empty]', root);
    const results = qs('[data-saved-results]', root);
    const qInput = qs('[data-saved-q]', root);
    const modelSelect = qs('[data-saved-work-model]', root);
    const sortSelect = qs('[data-saved-sort]', root);

    const refresh = (ids) => {
        const q = (qInput?.value || '').trim().toLowerCase();
        const model = modelSelect?.value || 'all';
        const sort = sortSelect?.value || 'recent';

        let visible = [];
        rows.forEach((row) => {
            const id = row.dataset.jobId;
            const inSaved = ids.includes(id);
            const hay = row.dataset.title || '';
            const matchQ = !q || hay.includes(q);
            const matchModel = model === 'all' || row.dataset.workModel === model;
            const show = inSaved && matchQ && matchModel;
            row.classList.toggle('hidden', !show);
            if (show) visible.push(row);
        });

        visible.sort((a, b) => {
            if (sort === 'salary_desc') {
                return Number(b.dataset.salaryMax || 0) - Number(a.dataset.salaryMax || 0);
            }
            if (sort === 'company') {
                return (a.dataset.company || '').localeCompare(b.dataset.company || '');
            }
            return (b.dataset.savedRank || '').localeCompare(a.dataset.savedRank || '');
        });
        visible.forEach((row) => results?.appendChild(row));

        syncSavedCounts(root, visible.length);
        const isEmpty = visible.length === 0;
        empty?.classList.toggle('hidden', !isEmpty);
        if (empty) empty.hidden = !isEmpty;
        results?.classList.toggle('hidden', isEmpty);
    };

    qInput?.addEventListener('input', () => refresh(savedIds));
    modelSelect?.addEventListener('change', () => refresh(savedIds));
    sortSelect?.addEventListener('change', () => refresh(savedIds));

    refresh(savedIds);
    root._hbRefreshSaved = refresh;
}

function initCandidateJobs() {
    const root = qs('[data-candidate-jobs]');
    if (!root) return;

    const defaults = parseDefaultSaved(root);
    let savedIds = readSavedJobIds(defaults);
    if (!window.localStorage.getItem(CANDIDATE_SAVED_STORAGE_KEY)) {
        writeSavedJobIds(savedIds);
    }

    const mode = root.getAttribute('data-candidate-jobs-mode') || 'discover';
    if (mode === 'discover') {
        initCandidateJobsDiscover(root, savedIds);
    } else {
        initCandidateJobsSaved(root, savedIds);
    }

    syncSavedCounts(document, savedIds.length);

    root.addEventListener('click', (event) => {
        const button = event.target.closest('[data-save-job]');
        if (!button || !root.contains(button)) return;

        const id = String(button.getAttribute('data-save-job') || '');
        if (!id) return;

        const isSaved = savedIds.includes(id);
        savedIds = isSaved ? savedIds.filter((value) => value !== id) : [...savedIds, id];
        writeSavedJobIds(savedIds);
        syncSavedCounts(document, savedIds.length);

        qsa(`[data-save-job="${id}"]`, root).forEach((btn) => {
            updateSaveButton(btn, !isSaved);
        });

        if (mode === 'saved' && typeof root._hbRefreshSaved === 'function') {
            root._hbRefreshSaved(savedIds);
        }
    });
}

function readApplyDraft(key) {
    try {
        const raw = window.localStorage.getItem(key);
        return raw ? JSON.parse(raw) : null;
    } catch {
        return null;
    }
}

function writeApplyDraft(key, data) {
    try {
        window.localStorage.setItem(key, JSON.stringify(data));
    } catch {
        // Ignore quota / private mode.
    }
}

function collectApplyValues(form) {
    const resume = qs('[data-apply-resume]', form);
    return {
        full_name: qs('[data-apply-field="full_name"]', form)?.value?.trim() || '',
        email: qs('[data-apply-field="email"]', form)?.value?.trim() || '',
        portfolio: qs('[data-apply-field="portfolio"]', form)?.value?.trim() || '',
        hybrid: qs('[data-apply-field="hybrid"]', form)?.value || '',
        interest: qs('[data-apply-field="interest"]', form)?.value?.trim() || '',
        salary: qs('[data-apply-field="salary"]', form)?.value?.trim() || '',
        consent: Boolean(qs('[data-apply-field="consent"]', form)?.checked),
        talent_pool: Boolean(qs('[data-apply-field="talent_pool"]', form)?.checked),
        resume_filename: resume?.dataset.resumeFilename || '',
        resume_size: resume?.dataset.resumeSize || '',
        resume_updated: resume?.dataset.resumeUpdated || '',
    };
}

function setApplyFieldError(form, field, message) {
    const input = qs(`[data-apply-field="${field}"]`, form);
    const error = qs(`[data-error-for="${field}"]`, form);
    if (input) {
        input.setAttribute('aria-invalid', message ? 'true' : 'false');
    }
    if (error) {
        error.textContent = message || '';
        error.classList.toggle('hidden', !message);
    }
}

function clearApplyErrors(form) {
    qsa('[data-error-for]', form).forEach((el) => {
        el.textContent = '';
        el.classList.add('hidden');
    });
    qsa('[data-apply-field]', form).forEach((el) => el.setAttribute('aria-invalid', 'false'));
    const formError = qs('[data-apply-form-error]', form);
    if (formError) {
        formError.textContent = '';
        formError.classList.add('hidden');
    }
}

function validateApplyForm(form) {
    clearApplyErrors(form);
    const values = collectApplyValues(form);
    let valid = true;
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!values.full_name) {
        setApplyFieldError(form, 'full_name', 'Enter your full name.');
        valid = false;
    }
    if (!values.email || !emailPattern.test(values.email)) {
        setApplyFieldError(form, 'email', 'Enter a valid email address.');
        valid = false;
    }
    if (!values.resume_filename) {
        setApplyFieldError(form, 'resume', 'Attach or keep a resume on file.');
        valid = false;
    }
    if (values.portfolio) {
        try {
            // eslint-disable-next-line no-new
            new URL(values.portfolio);
        } catch {
            setApplyFieldError(form, 'portfolio', 'Enter a valid portfolio URL.');
            valid = false;
        }
    }
    if (!values.hybrid) {
        setApplyFieldError(form, 'hybrid', t('validation.hybrid'));
        valid = false;
    }
    if (!values.interest || values.interest.length < 20) {
        setApplyFieldError(form, 'interest', t('validation.interest_min'));
        valid = false;
    }
    if (!values.consent) {
        setApplyFieldError(form, 'consent', t('validation.consent_continue'));
        valid = false;
    }

    if (!valid) {
        const formError = qs('[data-apply-form-error]', form);
        if (formError) {
            formError.textContent = t('validation.fix_highlighted');
            formError.classList.remove('hidden');
        }
    }

    return valid;
}

function fillApplyReview(form, values) {
    const setText = (sel, text) => {
        const el = qs(sel, form);
        if (el) el.textContent = text;
    };
    setText('[data-review-profile]', `${values.full_name} · ${values.email}`);
    setText(
        '[data-review-resume]',
        values.resume_filename
            ? `${values.resume_filename}${values.resume_size ? ` · ${values.resume_size}` : ''}`
            : '—',
    );
    setText('[data-review-portfolio]', values.portfolio || '—');
    setText('[data-review-hybrid]', values.hybrid || '—');
    setText('[data-review-interest]', values.interest || '—');
    setText('[data-review-salary]', values.salary || '—');
}

function showApplyPanel(form, panel) {
    const questions = qs('[data-apply-panel="questions"]', form);
    const review = qs('[data-apply-panel="review"]', form);
    const showQuestions = panel === 'questions';
    if (questions) {
        questions.classList.toggle('hidden', !showQuestions);
        questions.hidden = !showQuestions;
    }
    if (review) {
        review.classList.toggle('hidden', showQuestions);
        review.hidden = showQuestions;
    }

    const stepper = form.closest('[data-candidate-apply-page]')?.parentElement?.querySelector('[data-apply-stepper]')
        || document.querySelector('[data-apply-stepper]');
    if (stepper) {
        const items = qsa('[data-stepper-item]', stepper);
        items.forEach((item, index) => {
            const step = index + 1;
            const current = showQuestions ? 2 : 3;
            item.toggleAttribute('aria-current', step === current);
            const label = qs('[data-stepper-label]', item);
            if (!label) return;
            label.classList.toggle('border-hb-blue', step === current);
            label.classList.toggle('text-hb-navy', step === current);
            label.classList.toggle('text-hb-muted', step !== current);
            label.classList.toggle('border-transparent', step !== current);
        });
    }
}

function restoreApplyDraft(form) {
    const key = form.getAttribute('data-storage-key');
    if (!key) return;
    const draft = readApplyDraft(key);
    if (!draft) return;

    Object.entries(draft).forEach(([field, value]) => {
        if (field.startsWith('resume_')) return;
        const input = qs(`[data-apply-field="${field}"]`, form);
        if (!input) return;
        if (input.type === 'checkbox') {
            input.checked = Boolean(value);
        } else {
            input.value = value ?? '';
        }
    });

    const resume = qs('[data-apply-resume]', form);
    if (resume && draft.resume_filename) {
        resume.dataset.resumeFilename = draft.resume_filename;
        resume.dataset.resumeSize = draft.resume_size || '';
        resume.dataset.resumeUpdated = draft.resume_updated || 'Local draft';
        const label = qs('[data-resume-label]', resume);
        const meta = qs('[data-resume-meta]', resume);
        if (label) {
            label.textContent = `${draft.resume_filename}${draft.resume_size ? ` · ${draft.resume_size}` : ''}`;
        }
        if (meta) {
            meta.textContent = `Saved draft resume · ${draft.resume_updated || 'Local only'}`;
        }
    }
}

function initCandidateApplyForm(form) {
    restoreApplyDraft(form);

    qs('[data-apply-resume-pick]', form)?.addEventListener('click', () => {
        qs('[data-apply-resume-input]', form)?.click();
    });

    qs('[data-apply-resume-input]', form)?.addEventListener('change', (event) => {
        const file = event.target.files?.[0];
        const resume = qs('[data-apply-resume]', form);
        if (!file || !resume) return;
        const sizeKb = Math.max(1, Math.round(file.size / 1024));
        resume.dataset.resumeFilename = file.name;
        resume.dataset.resumeSize = `${sizeKb} KB`;
        resume.dataset.resumeUpdated = 'Just now';
        const label = qs('[data-resume-label]', resume);
        const meta = qs('[data-resume-meta]', resume);
        if (label) label.textContent = `${file.name} · ${sizeKb} KB`;
        if (meta) meta.textContent = t('status.resume_selected');
        setApplyFieldError(form, 'resume', '');
    });

    qs('[data-apply-save-draft]', form)?.addEventListener('click', () => {
        const key = form.getAttribute('data-storage-key');
        const values = collectApplyValues(form);
        if (key) writeApplyDraft(key, values);
        const status = qs('[data-apply-draft-status]', form);
        if (status) {
            status.textContent = t('status.draft_saved');
            status.classList.remove('hidden');
        }
    });

    qs('[data-apply-review]', form)?.addEventListener('click', () => {
        if (!validateApplyForm(form)) return;
        const values = collectApplyValues(form);
        fillApplyReview(form, values);
        showApplyPanel(form, 'review');
        qs('[data-apply-back]', form)?.focus();
    });

    qs('[data-apply-back]', form)?.addEventListener('click', () => {
        showApplyPanel(form, 'questions');
        qs('[data-apply-review]', form)?.focus();
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!validateApplyForm(form)) {
            showApplyPanel(form, 'questions');
            return;
        }

        const submitBtn = qs('[data-apply-submit]', form);
        const label = qs('[data-apply-submit-label]', form);
        const loading = qs('[data-apply-submit-loading]', form);
        const errorBox = qs('[data-apply-loading-error]', form);

        if (submitBtn) submitBtn.disabled = true;
        label?.classList.add('hidden');
        loading?.classList.remove('hidden');
        errorBox?.classList.add('hidden');

        const key = form.getAttribute('data-storage-key');
        if (key) writeApplyDraft(key, collectApplyValues(form));

        window.setTimeout(() => {
            const url = form.getAttribute('data-submitted-url');
            if (!url) {
                if (errorBox) {
                    errorBox.textContent = 'Preview URL missing. Reload the page and try again.';
                    errorBox.classList.remove('hidden');
                }
                if (submitBtn) submitBtn.disabled = false;
                label?.classList.remove('hidden');
                loading?.classList.add('hidden');
                return;
            }
            window.location.assign(url);
        }, 450);
    });
}

function initCandidateApply() {
    qsa('[data-candidate-apply-form]').forEach((form) => initCandidateApplyForm(form));
}

function initCandidateApplicationsList() {
    const root = qs('[data-candidate-applications]');
    if (!root) return;

    const search = qs('[data-apps-search]', root);
    const stage = qs('[data-apps-stage]', root);
    const sort = qs('[data-apps-sort]', root);
    const cards = qsa('[data-app-card]', root);

    const refresh = () => {
        const q = (search?.value || '').trim().toLowerCase();
        const stageValue = stage?.value || 'all';
        const sortValue = sort?.value || 'latest';
        const activePanel = qs('[data-apps-panel="active"]', root);
        const archivedPanel = qs('[data-apps-panel="archived"]', root);

        ['active', 'archived'].forEach((scope) => {
            const scopeCards = cards.filter((card) => card.dataset.appScope === scope);
            let visible = scopeCards.filter((card) => {
                const matchQ = !q || (card.dataset.title || '').includes(q);
                const matchStage = stageValue === 'all' || card.dataset.stage === stageValue;
                const show = matchQ && matchStage;
                card.classList.toggle('hidden', !show);
                return show;
            });

            visible.sort((a, b) => {
                if (sortValue === 'company') {
                    return (a.dataset.company || '').localeCompare(b.dataset.company || '');
                }
                if (sortValue === 'stage') {
                    return (a.dataset.stage || '').localeCompare(b.dataset.stage || '');
                }
                return (b.dataset.activity || '').localeCompare(a.dataset.activity || '');
            });

            const listParent = scope === 'active'
                ? (qs('[data-apps-compact-list]', root) || activePanel)
                : qs('[data-apps-panel="archived"] .space-y-4', root);
            visible.forEach((card) => {
                if (card.dataset.featured === 'true') return;
                listParent?.appendChild(card);
            });

            const empty = qs(`[data-apps-empty="${scope}"]`, root);
            const isEmpty = visible.length === 0;
            empty?.classList.toggle('hidden', !isEmpty);
            if (empty) empty.hidden = !isEmpty;
        });

        void activePanel;
        void archivedPanel;
    };

    search?.addEventListener('input', refresh);
    stage?.addEventListener('change', refresh);
    sort?.addEventListener('change', refresh);
    refresh();
}

function initApplicationDetail() {
    const btn = qs('[data-withdraw-preview]');
    if (!btn || btn.disabled) return;
    btn.addEventListener('click', () => {
        const status = qs('[data-withdraw-status]');
        if (status) {
            status.textContent = t('status.withdraw_unavailable');
            status.classList.remove('hidden');
        }
        btn.disabled = true;
        btn.textContent = t('status.withdraw_button');
    });
}

function initCandidateInterviews() {
    const root = qs('[data-candidate-interviews]');
    if (!root) return;

    qsa('[data-prep-checklist]', root).forEach((list) => {
        const key = list.getAttribute('data-prep-storage');
        let saved = {};
        try {
            saved = key ? JSON.parse(window.localStorage.getItem(key) || '{}') : {};
        } catch {
            saved = {};
        }
        qsa('[data-prep-item]', list).forEach((input) => {
            const index = input.getAttribute('data-prep-item');
            if (Object.prototype.hasOwnProperty.call(saved, index)) {
                input.checked = Boolean(saved[index]);
            }
            input.addEventListener('change', () => {
                saved[index] = input.checked;
                if (key) {
                    try {
                        window.localStorage.setItem(key, JSON.stringify(saved));
                    } catch {
                        // Ignore.
                    }
                }
            });
        });
    });

    const weekStatus = qs('[data-week-nav-status]', root);
    qsa('[data-week-nav], [data-week-today]', root).forEach((btn) => {
        btn.addEventListener('click', () => {
            if (!weekStatus) return;
            weekStatus.textContent = t('status.week_note');
            weekStatus.classList.remove('hidden');
        });
    });

    const rescheduleSubmit = qs('[data-reschedule-submit]');
    rescheduleSubmit?.addEventListener('click', () => {
        const note = qs('[data-reschedule-note]')?.value?.trim();
        const status = qs('[data-reschedule-status]');
        if (!note) {
            if (status) {
                status.textContent = t('validation.reschedule_window');
                status.classList.remove('hidden', 'text-hb-success');
                status.classList.add('text-hb-error');
            }
            return;
        }
        if (status) {
            status.textContent = t('status.reschedule_unavailable');
            status.classList.remove('hidden', 'text-hb-error');
            status.classList.add('text-hb-success');
        }
    });
}

function initCandidateMessages() {
    const root = qs('[data-candidate-messages]');
    if (!root) return;

    const jsonEl = qs('[data-message-threads-json]');
    let threads = [];
    try {
        threads = JSON.parse(jsonEl?.textContent || '[]');
    } catch {
        threads = [];
    }

    const search = qs('[data-message-search]', root);
    const list = qs('[data-thread-list]', root);
    const empty = qs('[data-thread-empty]', root);
    const messageList = qs('[data-message-list]', root);
    const compose = qs('[data-message-compose]', root);

    const renderThread = (thread) => {
        if (!thread || !messageList) return;
        root.setAttribute('data-active-thread', thread.id);
        const contact = qs('[data-conversation-contact]', root);
        const meta = qs('[data-conversation-meta]', root);
        const divider = qs('[data-date-divider]', root);
        const composeName = qs('[data-compose-name]', root);
        if (contact) contact.textContent = thread.contact;
        if (meta) meta.textContent = `${thread.company} · ${thread.role} · ${thread.code}`;
        if (divider) divider.textContent = thread.date_divider || '';
        if (composeName) composeName.textContent = thread.contact;

        messageList.innerHTML = '';
        const date = document.createElement('p');
        date.className = 'text-center text-xs text-hb-muted';
        date.textContent = thread.date_divider || '';
        messageList.appendChild(date);

        (thread.messages || []).forEach((message) => {
            const wrap = document.createElement('div');
            wrap.className = `max-w-[90%] sm:max-w-[80%]${message.from === 'you' ? ' ms-auto' : ''}`;
            const metaLine = document.createElement('p');
            metaLine.className = 'mb-1 text-xs text-hb-muted';
            metaLine.textContent = `${message.time} · ${message.role}`;
            const bubble = document.createElement('div');
            bubble.className = `rounded-lg px-4 py-3 text-sm leading-relaxed ${
                message.from === 'you' ? 'bg-hb-info-soft text-hb-navy' : 'bg-hb-page text-hb-text'
            }`;
            bubble.textContent = message.body;
            wrap.appendChild(metaLine);
            wrap.appendChild(bubble);
            if (message.attachment) {
                const att = document.createElement('p');
                att.className = 'mt-2 text-sm font-semibold text-hb-blue';
                att.textContent = `↳ ${message.attachment.name} · ${message.attachment.size}`;
                wrap.appendChild(att);
            }
            if (message.status) {
                const st = document.createElement('p');
                st.className = 'mt-1 text-end text-xs text-hb-muted';
                st.textContent = message.status;
                wrap.appendChild(st);
            }
            messageList.appendChild(wrap);
        });

        const stage = qs('[data-context-stage]', root);
        const company = qs('[data-context-company]', root);
        const role = qs('[data-context-role]', root);
        const code = qs('[data-context-code]', root);
        const event = qs('[data-context-event]', root);
        const appLink = qs('[data-context-application]', root);
        if (stage) stage.textContent = thread.stage;
        if (company) company.textContent = thread.company;
        if (role) role.textContent = thread.role;
        if (code) code.textContent = `${thread.code} · ${thread.source}`;
        if (event) event.textContent = thread.event;
        if (appLink && thread.application_id) {
            appLink.setAttribute('href', `/candidate/applications/${thread.application_id}`);
        }

        qsa('[data-thread-id]', list).forEach((btn) => {
            const selected = btn.getAttribute('data-thread-id') === thread.id;
            btn.setAttribute('aria-selected', selected ? 'true' : 'false');
            btn.classList.toggle('bg-hb-info-soft', selected);
        });
    };

    list?.addEventListener('click', (event) => {
        const btn = event.target.closest('[data-thread-id]');
        if (!btn) return;
        const thread = threads.find((item) => item.id === btn.getAttribute('data-thread-id'));
        if (thread) renderThread(thread);
    });

    search?.addEventListener('input', () => {
        const q = (search.value || '').trim().toLowerCase();
        let visible = 0;
        qsa('[data-thread-id]', list).forEach((btn) => {
            const hay = btn.getAttribute('data-thread-search') || '';
            const show = !q || hay.includes(q);
            btn.closest('li')?.classList.toggle('hidden', !show);
            if (show) visible += 1;
        });
        empty?.classList.toggle('hidden', visible !== 0);
        if (empty) empty.hidden = visible !== 0;
    });

    qs('[data-toggle-context]', root)?.addEventListener('click', (event) => {
        const context = qs('[data-message-context]', root);
        if (!context) return;
        const open = context.classList.toggle('hidden') === false;
        context.classList.toggle('block', open);
        event.currentTarget.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    qs('[data-message-send]', root)?.addEventListener('click', () => {
        const body = (compose?.value || '').trim();
        const error = qs('[data-compose-error]', root);
        const status = qs('[data-compose-status]', root);
        if (!body) {
            if (error) {
                error.textContent = t('validation.write_message');
                error.classList.remove('hidden');
            }
            return;
        }
        error?.classList.add('hidden');

        const activeId = root.getAttribute('data-active-thread');
        const thread = threads.find((item) => item.id === activeId);
        const now = new Date();
        const time = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
        const localMessage = {
            id: `local-${Date.now()}`,
            from: 'you',
            author: 'Maya Chen',
            role: 'You',
            time,
            body,
            status: `Draft · ${time}`,
        };
        if (thread) {
            thread.messages = [...(thread.messages || []), localMessage];
            thread.snippet = body.slice(0, 48);
            renderThread(thread);
        }
        if (compose) compose.value = '';
        if (status) {
            status.textContent = t('status.message_undelivered');
            status.classList.remove('hidden');
        }
    });
}

function initAssessmentIntro() {
    const root = qs('[data-assessment-intro]');
    if (!root) return;
    const start = qs('[data-assessment-start]', root);
    start?.addEventListener('click', (event) => {
        const consent = qs('[data-assessment-consent]', root);
        const error = qs('[data-consent-error]', root);
        if (!consent?.checked) {
            event.preventDefault();
            if (error) {
                error.textContent = t('validation.acknowledge');
                error.classList.remove('hidden');
            }
        }
    });
}

function initAssessmentTake() {
    const root = qs('[data-assessment-take]');
    if (!root) return;

    const panels = qsa('[data-question-panel]', root);
    const total = panels.length;
    let index = 0;
    const timerEl = qs('[data-assessment-timer]');
    let remaining = Number(root.getAttribute('data-time-limit') || 45) * 60;

    const showQuestion = (next) => {
        index = Math.max(0, Math.min(total - 1, next));
        panels.forEach((panel, i) => {
            const show = i === index;
            panel.classList.toggle('hidden', !show);
            panel.hidden = !show;
        });
        qsa('[data-goto-question]', root).forEach((btn) => {
            const active = Number(btn.getAttribute('data-goto-question')) === index;
            btn.classList.toggle('border-hb-blue', active);
            btn.classList.toggle('bg-hb-info-soft', active);
            btn.classList.toggle('text-hb-blue', active);
        });
        const pos = qs('[data-question-position]', root);
        if (pos) pos.textContent = String(index + 1);
        const prev = qs('[data-question-prev]', root);
        const nextBtn = qs('[data-question-next]', root);
        const submit = qs('[data-assessment-submit]', root);
        if (prev) prev.disabled = index === 0;
        if (nextBtn) {
            nextBtn.classList.toggle('hidden', index === total - 1);
            nextBtn.hidden = index === total - 1;
        }
        if (submit) {
            submit.classList.toggle('hidden', index !== total - 1);
            submit.hidden = index !== total - 1;
        }
    };

    qs('[data-question-prev]', root)?.addEventListener('click', () => showQuestion(index - 1));
    qs('[data-question-next]', root)?.addEventListener('click', () => showQuestion(index + 1));
    qsa('[data-goto-question]', root).forEach((btn) => {
        btn.addEventListener('click', () => showQuestion(Number(btn.getAttribute('data-goto-question'))));
    });

    const tick = () => {
        remaining = Math.max(0, remaining - 1);
        const mins = String(Math.floor(remaining / 60)).padStart(2, '0');
        const secs = String(remaining % 60).padStart(2, '0');
        if (timerEl) timerEl.textContent = `${mins}:${secs}`;
        if (remaining === 0) {
            window.clearInterval(timerId);
            qs('[data-assessment-submit]', root)?.click();
        }
    };
    const timerId = window.setInterval(tick, 1000);

    qs('[data-assessment-submit]', root)?.addEventListener('click', () => {
        const mcIds = [...new Set(
            qsa('input[type="radio"][data-answer-field]', root).map((el) => el.getAttribute('data-answer-field')),
        )];
        let correct = 0;
        let answered = 0;
        mcIds.forEach((id) => {
            const checked = qs(`input[type="radio"][data-answer-field="${id}"]:checked`, root);
            if (checked) {
                answered += 1;
                if (checked.getAttribute('data-correct') === '1') {
                    correct += 1;
                }
            }
        });
        qsa('textarea[data-answer-field]', root).forEach((field) => {
            if ((field.value || '').trim()) {
                answered += 1;
            }
        });
        const mcTotal = mcIds.length || 1;
        const score = Math.round((correct / mcTotal) * 100);
        const payload = {
            score,
            correct,
            mcTotal,
            answered,
            at: new Date().toISOString(),
        };
        try {
            window.localStorage.setItem(`hb_assessment_result_${root.getAttribute('data-assessment-id')}`, JSON.stringify(payload));
        } catch {
            // Ignore.
        }
        window.clearInterval(timerId);
        const url = root.getAttribute('data-result-url');
        if (url) window.location.assign(url);
    });

    showQuestion(0);
}

function initAssessmentResult() {
    const root = qs('[data-assessment-result]');
    if (!root) return;
    const idMatch = window.location.pathname.match(/assessments\/([^/]+)\/result/);
    const id = idMatch?.[1];
    let payload = null;
    try {
        payload = id ? JSON.parse(window.localStorage.getItem(`hb_assessment_result_${id}`) || 'null') : null;
    } catch {
        payload = null;
    }
    const scoreEl = qs('[data-result-score]', root);
    const outcome = qs('[data-result-outcome]', root);
    const passing = Number(root.getAttribute('data-passing') || 70);
    if (payload && scoreEl) {
        scoreEl.textContent = `${payload.score}%`;
        if (outcome) {
            outcome.textContent = payload.score >= passing
                ? t('status.score_pass')
                : t('status.score_fail');
        }
    } else if (scoreEl) {
        scoreEl.textContent = '—';
        if (outcome) outcome.textContent = t('status.no_attempt');
    }
}

function collectPreferenceValues(form) {
    const values = {};
    qsa('[data-pref-field]', form).forEach((field) => {
        const key = field.getAttribute('data-pref-field');
        if (!key) return;
        values[key] = field.type === 'checkbox' ? field.checked : field.value;
    });
    return values;
}

function applyPreferenceValues(form, values) {
    Object.entries(values || {}).forEach(([key, value]) => {
        const field = qs(`[data-pref-field="${key}"]`, form);
        if (!field) return;
        if (field.type === 'checkbox') {
            field.checked = Boolean(value);
        } else {
            field.value = value ?? '';
        }
    });
}

function initCandidatePreferences() {
    const form = qs('[data-candidate-preferences]');
    if (!form) return;

    const key = form.getAttribute('data-storage-key');
    const defaults = collectPreferenceValues(form);
    let baseline = { ...defaults };

    try {
        const saved = key ? JSON.parse(window.localStorage.getItem(key) || 'null') : null;
        if (saved) {
            applyPreferenceValues(form, saved);
            baseline = collectPreferenceValues(form);
            const banner = qs('[data-prefs-saved-banner]');
            if (banner) {
                banner.classList.remove('hidden');
                banner.hidden = false;
            }
        }
    } catch {
        // Ignore.
    }

    const dirty = qs('[data-prefs-dirty]', form);
    const markDirty = () => {
        const current = JSON.stringify(collectPreferenceValues(form));
        const isDirty = current !== JSON.stringify(baseline);
        dirty?.classList.toggle('hidden', !isDirty);
        if (dirty) dirty.hidden = !isDirty;
    };

    form.addEventListener('input', markDirty);
    form.addEventListener('change', markDirty);

    qs('[data-prefs-cancel]', form)?.addEventListener('click', () => {
        applyPreferenceValues(form, baseline);
        markDirty();
        const error = qs('[data-prefs-form-error]', form);
        error?.classList.add('hidden');
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        const values = collectPreferenceValues(form);
        const error = qs('[data-prefs-form-error]', form);
        let valid = true;
        ['titles', 'location', 'salary'].forEach((field) => {
            const el = qs(`[data-error-for="${field}"]`, form);
            const input = qs(`[data-pref-field="${field}"]`, form);
            const ok = Boolean(String(values[field] || '').trim());
            if (el) {
                el.textContent = ok ? '' : t('validation.required');
                el.classList.toggle('hidden', ok);
            }
            input?.setAttribute('aria-invalid', ok ? 'false' : 'true');
            if (!ok) valid = false;
        });
        if (!valid) {
            if (error) {
                error.textContent = t('validation.fix_before_save');
                error.classList.remove('hidden');
            }
            return;
        }
        error?.classList.add('hidden');
        if (key) {
            try {
                window.localStorage.setItem(key, JSON.stringify(values));
            } catch {
                // Ignore.
            }
        }
        baseline = { ...values };
        markDirty();
        const banner = qs('[data-prefs-saved-banner]');
        if (banner) {
            banner.classList.remove('hidden');
            banner.hidden = false;
        }
    });
}

function initCandidateOffer() {
    const root = qs('[data-candidate-offer]');
    if (!root) return;

    const requireReviewed = () => {
        const reviewed = qs('[data-offer-reviewed]', root);
        const error = qs('[data-offer-reviewed-error]', root);
        if (reviewed?.checked) {
            error?.classList.add('hidden');
            return true;
        }
        if (error) {
            error.textContent = t('validation.offer_reviewed');
            error.classList.remove('hidden');
        }
        return false;
    };

    qsa('[data-open-modal="offer-accept-modal"], [data-open-modal="offer-decline-modal"]').forEach((btn) => {
        btn.addEventListener('click', (event) => {
            if (!requireReviewed()) {
                event.stopImmediatePropagation();
                event.preventDefault();
            }
        }, true);
    });

    const status = qs('[data-offer-decision-status]', root);
    const closeOpenModals = () => {
        qsa('[data-hb-modal]').forEach((modal) => {
            if (!modal.classList.contains('hidden')) closeModal(modal);
        });
    };

    qs('[data-offer-confirm-accept]')?.addEventListener('click', () => {
        if (status) {
            status.textContent = t('status.offer_accept_unavailable');
            status.classList.remove('hidden');
        }
        closeOpenModals();
    });

    qs('[data-offer-confirm-decline]')?.addEventListener('click', () => {
        if (status) {
            status.textContent = t('status.offer_decline_unavailable');
            status.classList.remove('hidden');
        }
        closeOpenModals();
    });
}

function readNotificationState(key, defaults) {
    try {
        const raw = key ? window.localStorage.getItem(key) : null;
        if (!raw) return defaults.map((item) => ({ ...item }));
        const saved = JSON.parse(raw);
        return defaults.map((item) => ({
            ...item,
            unread: Object.prototype.hasOwnProperty.call(saved, item.id) ? Boolean(saved[item.id]) : item.unread,
        }));
    } catch {
        return defaults.map((item) => ({ ...item }));
    }
}

function writeNotificationState(key, items) {
    if (!key) return;
    const map = {};
    items.forEach((item) => {
        map[item.id] = Boolean(item.unread);
    });
    try {
        window.localStorage.setItem(key, JSON.stringify(map));
    } catch {
        // Ignore.
    }
}

function syncNotificationDropdown(items) {
    const dropdown = qs('[data-notifications-dropdown]');
    if (!dropdown) return;
    const unread = items.filter((item) => item.unread).length;
    const dot = qs('[data-dropdown-unread-dot]', dropdown);
    const label = qs('[data-dropdown-unread-label]', dropdown);
    dot?.classList.toggle('hidden', unread === 0);
    if (label) label.textContent = `${unread} unread`;

    qsa('[data-dropdown-item]', dropdown).forEach((row) => {
        const id = row.getAttribute('data-notification-id');
        const item = items.find((entry) => entry.id === id);
        if (!item) return;
        row.setAttribute('data-unread', item.unread ? 'true' : 'false');
        const link = qs('[data-dropdown-item-link]', row);
        const unreadDot = qs('[data-unread-dot]', row);
        link?.classList.toggle('bg-hb-info-soft/50', item.unread);
        link?.classList.toggle('bg-hb-surface', !item.unread);
        unreadDot?.classList.toggle('bg-hb-blue', item.unread);
        unreadDot?.classList.toggle('bg-transparent', !item.unread);
    });
}

function initCandidateNotificationsPage() {
    const root = qs('[data-candidate-notifications]');
    if (!root) return;

    const key = root.getAttribute('data-storage-key');
    let defaults = [];
    try {
        defaults = JSON.parse(root.getAttribute('data-default-json') || '[]');
    } catch {
        defaults = [];
    }
    let items = readNotificationState(key, defaults);

    const rows = qsa('[data-notification-item]', root);
    const empty = qs('[data-notifications-empty]', root);
    const countEl = qs('[data-notifications-unread-count]', root);
    const filter = qs('[data-notifications-filter]', root);

    const paint = () => {
        const mode = filter?.value || 'all';
        let visible = 0;
        rows.forEach((row) => {
            const id = row.getAttribute('data-notification-id');
            const item = items.find((entry) => entry.id === id);
            if (!item) return;
            row.setAttribute('data-unread', item.unread ? 'true' : 'false');
            row.classList.toggle('bg-hb-info-soft/40', item.unread);
            const dot = qs('[data-unread-dot]', row);
            dot?.classList.toggle('bg-hb-blue', item.unread);
            dot?.classList.toggle('bg-transparent', !item.unread);
            const toggle = qs('[data-notification-toggle-read]', row);
            if (toggle) toggle.textContent = item.unread ? 'Mark read' : 'Mark unread';

            const matchFilter = mode === 'all'
                || (mode === 'unread' && item.unread)
                || row.getAttribute('data-category') === mode;
            row.classList.toggle('hidden', !matchFilter);
            if (matchFilter) visible += 1;
        });
        if (countEl) countEl.textContent = String(items.filter((item) => item.unread).length);
        empty?.classList.toggle('hidden', visible !== 0);
        if (empty) empty.hidden = visible !== 0;
        writeNotificationState(key, items);
        syncNotificationDropdown(items);
    };

    rows.forEach((row) => {
        qs('[data-notification-toggle-read]', row)?.addEventListener('click', () => {
            const id = row.getAttribute('data-notification-id');
            items = items.map((item) => (item.id === id ? { ...item, unread: !item.unread } : item));
            paint();
        });
        qs('[data-notification-link]', row)?.addEventListener('click', () => {
            const id = row.getAttribute('data-notification-id');
            items = items.map((item) => (item.id === id ? { ...item, unread: false } : item));
            writeNotificationState(key, items);
            syncNotificationDropdown(items);
        });
    });

    qs('[data-notifications-mark-all]', document)?.addEventListener('click', () => {
        items = items.map((item) => ({ ...item, unread: false }));
        paint();
    });

    filter?.addEventListener('change', paint);
    paint();
}

function initNotificationsDropdownSync() {
    const dropdown = qs('[data-notifications-dropdown]');
    if (!dropdown || qs('[data-candidate-notifications]')) return;
    const key = dropdown.getAttribute('data-storage-key');
    const defaults = qsa('[data-dropdown-item]', dropdown).map((row) => ({
        id: row.getAttribute('data-notification-id'),
        unread: row.getAttribute('data-unread') === 'true',
    }));
    const items = readNotificationState(key, defaults);
    syncNotificationDropdown(items);
    qsa('[data-dropdown-item-link]', dropdown).forEach((link) => {
        link.addEventListener('click', () => {
            const row = link.closest('[data-dropdown-item]');
            const id = row?.getAttribute('data-notification-id');
            if (!id) return;
            const next = items.map((item) => (item.id === id ? { ...item, unread: false } : item));
            writeNotificationState(key, next);
            syncNotificationDropdown(next);
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initDropdowns();
    initMobileSidebar();
    initModals();
    initTabs();
    initSlotPicker();
    initLanguagePreview();
    initThemeSwitch();
    initPublicMobileNav();
    initPasswordToggles();
    initPasswordRules();
    initAccountTypeToggle();
    initOtpInputs();
    initAuthForms();
    initOnboarding();
    initCandidateJobs();
    initCandidateApply();
    initCandidateApplicationsList();
    initApplicationDetail();
    initCandidateInterviews();
    initCandidateMessages();
    initAssessmentIntro();
    initAssessmentTake();
    initAssessmentResult();
    initCandidatePreferences();
    initCandidateOffer();
    initCandidateNotificationsPage();
    initNotificationsDropdownSync();
});



