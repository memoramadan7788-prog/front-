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
        // Do not close modals here — modal Escape is handled by the active modal trap.
        const openDropdowns = qsa('[data-dropdown-panel]').filter((panel) => !panel.classList.contains('hidden'));
        if (openDropdowns.length) {
            openDropdowns.forEach((panel) => closeDropdown(panel));
            return;
        }
        const drawer = qs('[data-mobile-sidebar]');
        if (drawer && drawer.classList.contains('is-open')) {
            closeMobileSidebar();
            return;
        }
        const filters = qs('[data-jobs-filters].is-open');
        if (filters) {
            qs('[data-jobs-filters-close]')?.click();
        }
    });
}

function setDrawerInert(drawer, inert) {
    if (!drawer) return;
    if (inert) {
        drawer.setAttribute('inert', '');
        drawer.setAttribute('aria-hidden', 'true');
    } else {
        drawer.removeAttribute('inert');
        drawer.setAttribute('aria-hidden', 'false');
    }
}

function openMobileSidebar() {
    const drawer = qs('[data-mobile-sidebar]');
    const backdrop = qs('[data-mobile-sidebar-backdrop]');
    if (!drawer || !backdrop) return;
    drawer.classList.remove('-translate-x-full', 'rtl:translate-x-full');
    drawer.classList.add('translate-x-0', 'is-open');
    setDrawerInert(drawer, false);
    backdrop.classList.remove('hidden');
    backdrop.hidden = false;
    backdrop.setAttribute('aria-hidden', 'false');
    document.body.classList.add('overflow-hidden');
    const toggle = qs('[data-mobile-sidebar-toggle]');
    if (toggle) toggle.setAttribute('aria-expanded', 'true');
    const closeBtn = qs('[data-mobile-sidebar-close]');
    (closeBtn || getFocusable(drawer)[0])?.focus();
}

function closeMobileSidebar() {
    const drawer = qs('[data-mobile-sidebar]');
    const backdrop = qs('[data-mobile-sidebar-backdrop]');
    if (!drawer || !backdrop) return;
    drawer.classList.add('-translate-x-full', 'rtl:translate-x-full');
    drawer.classList.remove('translate-x-0', 'is-open');
    setDrawerInert(drawer, true);
    backdrop.classList.add('hidden');
    backdrop.hidden = true;
    backdrop.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('overflow-hidden');
    const toggle = qs('[data-mobile-sidebar-toggle]');
    if (toggle) {
        toggle.setAttribute('aria-expanded', 'false');
        if (document.activeElement && drawer.contains(document.activeElement)) {
            toggle.focus();
        }
    }
}

function initMobileSidebar() {
    const drawer = qs('[data-mobile-sidebar]');
    const toggle = qs('[data-mobile-sidebar-toggle]');
    const backdrop = qs('[data-mobile-sidebar-backdrop]');
    const closeBtn = qs('[data-mobile-sidebar-close]');
    if (drawer) setDrawerInert(drawer, !drawer.classList.contains('is-open'));
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

function isModalDismissible(modal) {
    return modal && !modal.hasAttribute('data-hb-modal-static');
}

function setBackgroundInert(inert) {
    // Do not inert #main-content: shared modals are often rendered inside the page content.
    const shells = [document.querySelector('header'), ...qsa('aside')].filter(Boolean);
    shells.forEach((el) => {
        if (inert) {
            el.setAttribute('inert', '');
            el.setAttribute('aria-hidden', 'true');
        } else {
            el.removeAttribute('inert');
            el.removeAttribute('aria-hidden');
        }
    });
}

function openModal(modal, trigger = null) {
    if (!modal) return;
    modalTrigger = trigger;
    activeModal = modal;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modal.hidden = false;
    modal.setAttribute('data-hb-modal-open', 'true');
    document.body.classList.add('overflow-hidden');
    setBackgroundInert(true);

    const panel = modal.querySelector('[role="document"]') || modal.querySelector('.relative.z-10') || modal;
    const focusables = getFocusable(modal).filter((el) => el.getAttribute('data-hb-modal-overlay') == null);
    const focusTarget = focusables[0] || panel;
    if (focusTarget && typeof focusTarget.focus === 'function') {
        window.setTimeout(() => focusTarget.focus(), 0);
    }

    if (modalKeyHandler) {
        document.removeEventListener('keydown', modalKeyHandler);
    }

    modalKeyHandler = (event) => {
        if (!activeModal || activeModal !== modal) return;
        if (event.key === 'Escape') {
            if (!isModalDismissible(activeModal)) {
                event.preventDefault();
                return;
            }
            event.preventDefault();
            event.stopPropagation();
            closeModal(activeModal);
            return;
        }
        if (event.key !== 'Tab') return;
        // Focus trap is on by default for shared modals; opt out with data-hb-modal-no-trap.
        if (activeModal.hasAttribute('data-hb-modal-no-trap')) return;
        const items = getFocusable(activeModal).filter((el) => el.getAttribute('data-hb-modal-overlay') == null);
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
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    modal.hidden = true;
    modal.removeAttribute('data-hb-modal-open');
    if (activeModal === modal) {
        activeModal = null;
        document.body.classList.remove('overflow-hidden');
        setBackgroundInert(false);
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
        modal.querySelectorAll('[data-hb-modal-close]').forEach((el) => {
            el.addEventListener('click', () => closeModal(modal));
        });
        modal.querySelectorAll('[data-hb-modal-overlay]').forEach((el) => {
            el.addEventListener('click', () => {
                if (!isModalDismissible(modal) || el.hasAttribute('data-hb-modal-static-overlay')) {
                    return;
                }
                closeModal(modal);
            });
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
        const status = qs('[data-slot-status]', root);
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
                if (status) {
                    status.textContent = '';
                    status.classList.add('hidden');
                }
            });
        });

        const form = root.closest('form');
        form?.addEventListener('submit', (event) => {
            const input = qs('input[data-slot-value]', root);
            if (input && !input.value) {
                event.preventDefault();
                if (status) {
                    status.textContent = t('validation.select_slot');
                    status.classList.remove('hidden');
                }
            }
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
    if (score === 3) return t('validation.password_strong');
    if (score === 2) return t('validation.password_almost');
    return t('validation.password_weak');
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
            btn.setAttribute('aria-label', showing ? t('validation.show_password') : t('validation.hide_password'));
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
            matchEl.textContent = ok ? t('validation.passwords_match') : t('validation.passwords_mismatch');
            matchEl.classList.toggle('text-hb-success', ok);
            matchEl.classList.toggle('text-hb-error', !ok);
        };
        confirm.addEventListener('input', sync);
        source.addEventListener('input', sync);
    });
}

function syncRegisterNextSteps(type) {
    const page = qs('[data-auth-page="register"]');
    if (!page) return;
    const candidate = qs('[data-auth-next="candidate"]', page);
    const company = qs('[data-auth-next="company"]', page);
    if (candidate) candidate.classList.toggle('hidden', type === 'company');
    if (company) company.classList.toggle('hidden', type !== 'company');
}

function initAccountTypeToggle() {
    qsa('[data-account-type-group]').forEach((group) => {
        const form = group.closest('[data-auth-page]')?.querySelector('[data-auth-form]') || group.closest('form');
        const input = form ? qs('[data-account-type-input]', form) : null;
        const submit = form ? qs('[data-auth-submit]', form) : null;
        const options = qsa('[data-account-type]', group);

        options.forEach((btn) => {
            btn.addEventListener('click', () => {
                const type = btn.getAttribute('data-account-type') || 'candidate';
                options.forEach((other) => {
                    const active = other === btn;
                    other.setAttribute('aria-checked', active ? 'true' : 'false');
                    other.dataset.active = active ? 'true' : 'false';
                    other.classList.toggle('text-hb-navy', active);
                    other.classList.toggle('text-hb-muted', !active);
                });
                if (input) input.value = type;
                if (submit) {
                    const label =
                        type === 'company'
                            ? submit.getAttribute('data-submit-label-company')
                            : submit.getAttribute('data-submit-label-candidate');
                    if (label) submit.textContent = label;
                }
                syncRegisterNextSteps(type);
            });
        });

        syncRegisterNextSteps(input?.value || 'candidate');
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
        require('email', t('validation.email'), isValidEmail);
    }
    if (type === 'login') {
        require('password', t('validation.password_required'), (v) => v.length > 0);
    }
    if (type === 'register') {
        require('first_name', t('validation.first_name'), (v) => v.trim().length > 0);
        require('last_name', t('validation.last_name'), (v) => v.trim().length > 0);
        require('email', t('validation.email'), isValidEmail);
        require('password', t('validation.password_rules'), (v) => {
            const rules = passwordRules(v);
            return rules.length && rules.number && rules.symbol;
        });
        const termsField = qs('[data-field="terms"]', form);
        const terms = qs('#register-terms', form);
        const termsOk = terms?.checked;
        setFieldError(termsField, termsOk ? '' : t('validation.terms'));
        const termsError = qs('#register-terms-error', form);
        if (termsError) {
            termsError.textContent = termsOk ? '' : t('validation.terms');
            termsError.classList.toggle('hidden', termsOk);
        }
        if (!termsOk) {
            valid = false;
            firstInvalid.push(terms);
        }
    }
    if (type === 'reset-password') {
        require('password', t('validation.password_rules'), (v) => {
            const rules = passwordRules(v);
            return rules.length && rules.number && rules.symbol;
        });
        require('password_confirmation', t('validation.password_match'), (v, input) => {
            const source = qs('[data-password-confirm-source]', form);
            return v.length > 0 && source && v === source.value;
        });
    }
    if (type === 'verify-email') {
        const field = qs('[data-field="code"]', form);
        const hidden = qs('[data-otp-value]', form);
        const code = hidden?.value || '';
        const ok = /^\d{6}$/.test(code);
        setFieldError(field, ok ? '' : t('validation.otp'));
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

            const type = form.getAttribute('data-auth-form');
            if (type === 'forgot-password') {
                const email = qs('[data-field="email"] input', form)?.value?.trim();
                const continueLink = qs('[data-forgot-continue]', form);
                if (continueLink && email) {
                    try {
                        const url = new URL(continueLink.href, window.location.origin);
                        url.searchParams.set('email', email);
                        continueLink.href = url.pathname + url.search;
                    } catch {
                        // Keep existing href.
                    }
                }
            }
            if (type === 'register') {
                const accountType = qs('[data-account-type-input]', form)?.value || 'candidate';
                syncRegisterNextSteps(accountType);
                const email = qs('[data-field="email"] input', form)?.value?.trim();
                const verifyLink = qs('[data-auth-next="verify"]', form);
                if (verifyLink && email) {
                    try {
                        const url = new URL(verifyLink.href, window.location.origin);
                        url.searchParams.set('email', email);
                        verifyLink.href = url.pathname + url.search;
                    } catch {
                        // Keep existing href.
                    }
                }
            }

            if (note) {
                note.classList.remove('hidden');
                if (typeof note.focus === 'function') note.focus();
            }
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
            if (metaEl) metaEl.textContent = `${formatFileSize(file.size)} · ${t('validation.file_local_only')}`;
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

function companyStepLabels() {
    return [
        t('onboarding.company_step_1'),
        t('onboarding.company_step_2'),
        t('onboarding.company_step_3'),
        t('onboarding.company_step_4'),
    ];
}

function candidateStepLabels() {
    return [
        t('onboarding.candidate_step_1'),
        t('onboarding.candidate_step_2'),
        t('onboarding.candidate_step_3'),
        t('onboarding.candidate_step_4'),
    ];
}

function prefersReducedMotion() {
    return Boolean(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
}

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
            stepLabel.textContent = companyStepLabels()[step - 1] || `Step ${step} of ${total}`;
        }
        if (type === 'candidate') {
            stepLabel.textContent = candidateStepLabels()[step - 1] || `Step ${step} of ${total}`;
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
            next.textContent = step === 3 ? t('onboarding.continue_review') : t('onboarding.continue');
        }
        if (type === 'company') {
            next.textContent = t('onboarding.save_continue');
        }
    }
    if (finish) finish.classList.toggle('hidden', step < total);
    if (saveHint) saveHint.classList.toggle('hidden', type === 'candidate' && step !== 3);

    if (type === 'candidate') {
        syncCandidatePreview(root, step);
    }

    if (!prefersReducedMotion()) {
        const panel = qs(`[data-onboarding-panel][data-step="${step}"]`, root);
        panel?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
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
            requireText('headline', t('validation.headline'));
            requireText('location', t('validation.location'));
            requireText('years_experience', t('validation.years_experience'));
        }
        if (step === 2) {
            const field = qs('[data-field="resume"]', panel);
            const input = field ? qs('input[type="file"]', field) : null;
            const file = input?.files?.[0];
            const demoName = qs('[data-file-name]', field)?.textContent?.trim();
            let message = '';
            if (file && file.size > 5 * 1024 * 1024) {
                message = t('validation.resume_size');
            } else if (!file && !demoName) {
                message = t('validation.resume_file');
            }
            setFieldError(field, message);
            if (message) {
                valid = false;
                focusables.push(input);
            }
        }
        if (step === 3) {
            requireText('preferred_roles', t('validation.preferred_roles'));
            requireText('home_location', t('validation.home_location'));
            requireText('work_model', t('validation.work_model'));
            requireText('skills', t('validation.skills'));
        }
        if (step === 4) {
            syncCandidateReview(root);
        }
    }

    if (type === 'company') {
        if (step === 1) {
            requireText('legal_name', t('validation.legal_name'));
            const docField = qs('[data-field="document"]', panel);
            const docInput = docField ? qs('input[type="file"]', docField) : null;
            const file = docInput?.files?.[0];
            const demoName = qs('[data-file-name]', docField)?.textContent?.trim();
            const ok = Boolean(file || demoName);
            setFieldError(docField, ok ? '' : t('validation.document'));
            if (!ok) {
                valid = false;
                focusables.push(docInput);
            }
        }
        if (step === 2) {
            requireText('display_name', t('validation.display_name'));
            requireText('workspace_slug', t('validation.workspace_slug'));
            const slugField = qs('[data-field="workspace_slug"]', panel);
            const slugInput = slugField ? qs('input', slugField) : null;
            if (slugInput && slugInput.value && !/^[a-z0-9-]+$/.test(slugInput.value)) {
                setFieldError(slugField, t('validation.workspace_slug_format'));
                valid = false;
                focusables.push(slugInput);
            }
            requireText('website', t('validation.website'));
            requireText('industry', t('validation.industry'));
            requireText('headquarters', t('validation.headquarters'));
            requireText('timezone', t('validation.timezone'));
            const authInput = qs('#company-authorized', panel);
            const authOk = Boolean(authInput?.checked);
            const authError = qs('#company-authorized-error', panel);
            if (authError) {
                authError.textContent = authOk ? '' : t('validation.authorized');
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
            setFieldError(field, model ? '' : t('validation.hiring_model'));
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
                    setFieldError(field, t('validation.invite_email'));
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

    const percent = step <= 1 ? 25 : step === 2 ? 50 : step === 3 ? 75 : 100;
    if (progressEl) progressEl.textContent = t('onboarding.percent_complete', { percent });
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
                <label class="hb-label" for="invite-email-${index}">${t('onboarding.work_email')}</label>
                <input id="invite-email-${index}" name="invites[${index}][email]" type="email" class="hb-input" placeholder="name@company.example">
            </div>
            <div>
                <label class="hb-label" for="invite-role-${index}">${t('onboarding.role')}</label>
                <select id="invite-role-${index}" name="invites[${index}][role]" class="hb-input">
                    <option value="hiring_manager">${t('onboarding.hiring_manager')}</option>
                    <option value="interviewer" selected>${t('onboarding.interviewer')}</option>
                    <option value="admin">${t('onboarding.admin')}</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="button" class="hb-btn-ghost text-hb-muted" data-remove-invite>${t('onboarding.remove')}</button>
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

function collectOnboardingDraft(form) {
    const data = {};
    qsa('input, select, textarea', form).forEach((el) => {
        if (!el.name || el.type === 'file') return;
        if (el.type === 'checkbox' || el.type === 'radio') {
            data[el.name] = el.checked;
        } else {
            data[el.name] = el.value;
        }
    });
    return data;
}

function restoreOnboardingDraft(form, draft) {
    if (!draft || typeof draft !== 'object') return;
    Object.entries(draft).forEach(([name, value]) => {
        const fields = qsa(`[name="${name.replace(/\\/g, '\\\\').replace(/"/g, '\\"')}"]`, form);
        if (!fields.length) return;
        fields.forEach((el) => {
            if (el.type === 'checkbox' || el.type === 'radio') {
                el.checked = Boolean(value);
            } else {
                el.value = value ?? '';
            }
        });
    });
}

function initOnboarding() {
    qsa('[data-onboarding]').forEach((root) => {
        const total = Number(root.getAttribute('data-onboarding-total') || 1);
        let step = Number(root.getAttribute('data-onboarding-start') || 1);
        if (!Number.isFinite(step) || step < 1) step = 1;
        if (step > total) step = total;
        const form = qs('[data-onboarding-form]', root);
        const note = qs('[data-onboarding-frontend-note]', root);
        const storageKey = root.getAttribute('data-onboarding-storage');

        initFilePreviews(root);
        initCharCounters(root);
        initExperienceMode(root);
        initHiringModel(root);
        initInviteRows(root);
        initCompanyPreviewBindings(root);

        if (storageKey && form) {
            try {
                const saved = JSON.parse(window.localStorage.getItem(storageKey) || 'null');
                if (saved?.values) {
                    restoreOnboardingDraft(form, saved.values);
                }
                if (Number.isFinite(saved?.step) && saved.step >= 1 && saved.step <= total) {
                    step = saved.step;
                }
            } catch {
                // Ignore draft restore failures.
            }
        }

        const persistDraft = () => {
            if (!storageKey || !form) return;
            try {
                window.localStorage.setItem(
                    storageKey,
                    JSON.stringify({ step, values: collectOnboardingDraft(form), at: Date.now() }),
                );
            } catch {
                // Ignore quota / private mode.
            }
        };

        showOnboardingStep(root, step);
        form?.addEventListener('input', persistDraft);
        form?.addEventListener('change', persistDraft);

        qs('[data-onboarding-next]', root)?.addEventListener('click', () => {
            if (!validateOnboardingStep(root, step)) return;
            if (step < total) {
                step += 1;
                showOnboardingStep(root, step);
                persistDraft();
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
                persistDraft();
                if (note) note.classList.add('hidden');
            }
        });

        form?.addEventListener('submit', (event) => {
            event.preventDefault();
            if (!validateOnboardingStep(root, step)) return;
            persistDraft();
            if (note) {
                note.classList.remove('hidden');
                if (typeof note.focus === 'function') note.focus();
            }
        });
    });
}

function initPublicJobsDirectory() {
    const root = qs('[data-jobs-directory]');
    if (!root) return;

    const form = qs('[data-jobs-filter-form]', root) || qs('#jobs-filter-form', root);
    const resultsEl = qs('[data-jobs-results]', root);
    const emptyEl = qs('[data-jobs-empty]', root);
    const countEl = qs('[data-jobs-count]', root);
    const sortSelect = qs('[data-jobs-sort]', root);
    const pagination = qs('[data-jobs-pagination]', root);
    const pageStatus = qs('[data-jobs-page-status]', root);
    const pageNumbers = qs('[data-jobs-page-numbers]', root);
    const prevBtn = qs('[data-jobs-page-prev]', root);
    const nextBtn = qs('[data-jobs-page-next]', root);
    const filtersPanel = qs('[data-jobs-filters]', root);
    const filtersBackdrop = qs('[data-jobs-filters-backdrop]', root);
    const pageSize = Math.max(1, Number(root.getAttribute('data-page-size') || 5));
    let page = 1;

    if (!form || !resultsEl) return;

    const cards = () => qsa('[data-job-card]', resultsEl);

    const checked = (name) =>
        Array.from(form.querySelectorAll(`input[name="${name}[]"]:checked`)).map((el) => el.value);

    const isDesktopFilters = () => window.matchMedia('(min-width: 1024px)').matches;

    const closeFilters = () => {
        if (!filtersPanel) return;
        filtersPanel.classList.add('-translate-x-full', 'rtl:translate-x-full');
        filtersPanel.classList.remove('translate-x-0', 'is-open');
        if (!isDesktopFilters()) {
            setDrawerInert(filtersPanel, true);
        }
        filtersBackdrop?.classList.add('hidden');
        if (filtersBackdrop) {
            filtersBackdrop.hidden = true;
            filtersBackdrop.setAttribute('aria-hidden', 'true');
        }
        document.body.classList.remove('overflow-hidden');
        const openBtn = qs('[data-jobs-filters-open]', root);
        openBtn?.setAttribute('aria-expanded', 'false');
        if (openBtn && document.activeElement && filtersPanel.contains(document.activeElement)) {
            openBtn.focus();
        }
    };

    const openFilters = () => {
        if (!filtersPanel) return;
        filtersPanel.classList.remove('-translate-x-full', 'rtl:translate-x-full');
        filtersPanel.classList.add('translate-x-0', 'is-open');
        setDrawerInert(filtersPanel, false);
        filtersBackdrop?.classList.remove('hidden');
        if (filtersBackdrop) {
            filtersBackdrop.hidden = false;
            filtersBackdrop.setAttribute('aria-hidden', 'false');
        }
        document.body.classList.add('overflow-hidden');
        qs('[data-jobs-filters-open]', root)?.setAttribute('aria-expanded', 'true');
        qs('input, button, select', filtersPanel)?.focus();
    };

    const syncFiltersDesktopState = () => {
        if (!filtersPanel) return;
        if (isDesktopFilters()) {
            filtersPanel.classList.remove('is-open');
            setDrawerInert(filtersPanel, false);
            filtersBackdrop?.classList.add('hidden');
            if (filtersBackdrop) {
                filtersBackdrop.hidden = true;
                filtersBackdrop.setAttribute('aria-hidden', 'true');
            }
            document.body.classList.remove('overflow-hidden');
        } else if (!filtersPanel.classList.contains('is-open')) {
            setDrawerInert(filtersPanel, true);
        }
    };
    syncFiltersDesktopState();
    window.addEventListener('resize', syncFiltersDesktopState);

    const renderPagination = (visibleCount) => {
        if (!pagination) return;
        const totalPages = Math.max(1, Math.ceil(visibleCount / pageSize));
        if (page > totalPages) page = totalPages;
        const showPager = visibleCount > pageSize;
        pagination.hidden = !showPager;
        pagination.classList.toggle('hidden', !showPager);

        const from = visibleCount === 0 ? 0 : (page - 1) * pageSize + 1;
        const to = Math.min(visibleCount, page * pageSize);
        if (pageStatus) {
            if (!showPager) {
                pageStatus.textContent = '';
            } else {
                const template =
                    pagination.getAttribute('data-status-template') || 'Showing :from–:to of :total';
                pageStatus.textContent = template
                    .replace(':from', String(from))
                    .replace(':to', String(to))
                    .replace(':total', String(visibleCount));
            }
        }

        if (!showPager) {
            if (pageNumbers) pageNumbers.innerHTML = '';
            return;
        }

        if (prevBtn) prevBtn.disabled = page <= 1;
        if (nextBtn) nextBtn.disabled = page >= totalPages;
        if (pageNumbers) {
            pageNumbers.innerHTML = '';
            for (let i = 1; i <= totalPages; i += 1) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className =
                    'inline-flex h-9 min-w-9 items-center justify-center rounded-lg border px-2 text-sm font-semibold ' +
                    (i === page
                        ? 'border-hb-blue bg-hb-info-soft text-hb-blue'
                        : 'border-hb-border bg-hb-surface text-hb-muted hover:text-hb-navy');
                btn.textContent = String(i);
                btn.setAttribute('aria-label', `Page ${i}`);
                btn.setAttribute('aria-current', i === page ? 'page' : 'false');
                btn.addEventListener('click', () => {
                    page = i;
                    applyFilters({ keepPage: true });
                });
                pageNumbers.appendChild(btn);
            }
        }
    };

    const applyFilters = ({ keepPage = false } = {}) => {
        const q = (form.q?.value || '').trim().toLowerCase();
        const location = (form.location?.value || '').trim().toLowerCase();
        const models = checked('work_model');
        const employment = checked('employment');
        const disciplines = checked('discipline');
        const salaryMinRaw = form.salary_min?.value;
        const salaryMaxRaw = form.salary_max?.value;
        const salaryMin = salaryMinRaw === '' || salaryMinRaw == null ? 0 : Number(salaryMinRaw);
        const salaryMax =
            salaryMaxRaw === '' || salaryMaxRaw == null
                ? Number.MAX_SAFE_INTEGER
                : Number(salaryMaxRaw);
        const sort = sortSelect?.value || 'newest';

        let visibleCards = [];
        cards().forEach((card) => {
            const hay = card.dataset.title || '';
            const loc = card.dataset.location || '';
            const model = card.dataset.workModel || '';
            const emp = card.dataset.employment || '';
            const discipline = card.dataset.discipline || '';
            const min = Number(card.dataset.salaryMin || 0);
            const max = Number(card.dataset.salaryMax || 0);

            const matchQ = !q || hay.includes(q);
            const matchLoc = !location || loc.includes(location) || hay.includes(location);
            const matchModel = models.length === 0 || models.includes(model);
            const matchEmp = employment.length === 0 || employment.includes(emp);
            const matchDisc = disciplines.length === 0 || disciplines.includes(discipline);
            const matchSalary = max >= salaryMin && min <= salaryMax;
            const show = matchQ && matchLoc && matchModel && matchEmp && matchDisc && matchSalary;
            card.dataset.matches = show ? '1' : '0';
            if (show) visibleCards.push(card);
        });

        visibleCards.sort((a, b) => {
            if (sort === 'salary') {
                return Number(b.dataset.salaryMax || 0) - Number(a.dataset.salaryMax || 0);
            }
            if (sort === 'company') {
                return (a.dataset.company || '').localeCompare(b.dataset.company || '');
            }
            if (sort === 'relevance') {
                const aq = q ? (a.dataset.title || '').indexOf(q) : 0;
                const bq = q ? (b.dataset.title || '').indexOf(q) : 0;
                if (aq !== bq) return aq - bq;
            }
            return Number(b.dataset.postedRank || 0) - Number(a.dataset.postedRank || 0);
        });

        visibleCards.forEach((card) => resultsEl.appendChild(card));

        if (!keepPage) page = 1;
        const total = visibleCards.length;
        const start = (page - 1) * pageSize;
        const end = start + pageSize;

        cards().forEach((card) => {
            const matches = card.dataset.matches === '1';
            const index = visibleCards.indexOf(card);
            const onPage = matches && index >= start && index < end;
            card.classList.toggle('hidden', !onPage);
        });

        if (countEl) countEl.textContent = String(total);
        emptyEl?.classList.toggle('hidden', total !== 0);
        resultsEl.classList.toggle('hidden', total === 0);
        renderPagination(total);
    };

    form.addEventListener('input', () => applyFilters());
    form.addEventListener('change', () => applyFilters());
    sortSelect?.addEventListener('change', () => applyFilters());

    root.querySelectorAll('[data-clear-filters]').forEach((btn) => {
        btn.addEventListener('click', () => {
            form.reset();
            form.querySelectorAll('input[type="checkbox"]').forEach((el) => {
                el.checked = false;
            });
            if (form.salary_min) form.salary_min.value = '';
            if (form.salary_max) form.salary_max.value = '';
            if (sortSelect) sortSelect.value = 'newest';
            page = 1;
            applyFilters();
            closeFilters();
        });
    });

    qs('[data-jobs-filters-open]', root)?.addEventListener('click', openFilters);
    qs('[data-jobs-filters-close]', root)?.addEventListener('click', closeFilters);
    qs('[data-jobs-filters-apply]', root)?.addEventListener('click', () => {
        applyFilters();
        closeFilters();
    });
    filtersBackdrop?.addEventListener('click', closeFilters);
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && filtersBackdrop && !filtersBackdrop.classList.contains('hidden')) {
            closeFilters();
        }
    });

    prevBtn?.addEventListener('click', () => {
        if (page > 1) {
            page -= 1;
            applyFilters({ keepPage: true });
        }
    });
    nextBtn?.addEventListener('click', () => {
        page += 1;
        applyFilters({ keepPage: true });
    });

    applyFilters();
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
        button.textContent = t('jobs.remove_saved');
        return;
    }
    const label = qs('[data-save-label]', button) || button;
    if (label === button) {
        button.textContent = saved ? t('jobs.saved') : t('jobs.save');
    } else {
        label.textContent = saved ? t('jobs.saved') : t('jobs.save_job');
    }
}

function syncSavedCounts(root, count) {
    qsa('[data-saved-count]', root).forEach((el) => {
        el.textContent = String(count);
    });
    qsa('[data-saved-count-label]', root).forEach((el) => {
        el.textContent = count === 1 ? t('jobs.saved_role') : t('jobs.saved_roles');
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
                    ? t('jobs.recommended_one')
                    : t('jobs.recommended_many', { count: String(visibleCards.length) });
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

function initCandidateSavedJobsState() {
    const jobsRoot = qs('[data-candidate-jobs]');
    const defaults = jobsRoot ? parseDefaultSaved(jobsRoot) : [];
    let savedIds = readSavedJobIds(defaults);
    if (!window.localStorage.getItem(CANDIDATE_SAVED_STORAGE_KEY) && defaults.length) {
        writeSavedJobIds(savedIds);
    }

    const mode = jobsRoot?.getAttribute('data-candidate-jobs-mode') || '';
    if (jobsRoot && mode === 'discover') {
        initCandidateJobsDiscover(jobsRoot, savedIds);
    } else if (jobsRoot && mode === 'saved') {
        initCandidateJobsSaved(jobsRoot, savedIds);
    }

    qsa('[data-save-job]').forEach((button) => {
        const id = String(button.getAttribute('data-save-job') || '');
        if (id) {
            updateSaveButton(button, savedIds.includes(id));
        }
    });
    syncSavedCounts(document, savedIds.length);

    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-save-job]');
        if (!button) return;

        const id = String(button.getAttribute('data-save-job') || '');
        if (!id) return;

        const isSaved = savedIds.includes(id);
        savedIds = isSaved ? savedIds.filter((value) => value !== id) : [...savedIds, id];
        writeSavedJobIds(savedIds);
        syncSavedCounts(document, savedIds.length);

        qsa(`[data-save-job="${id}"]`).forEach((btn) => {
            updateSaveButton(btn, !isSaved);
        });

        if (mode === 'saved' && typeof jobsRoot?._hbRefreshSaved === 'function') {
            jobsRoot._hbRefreshSaved(savedIds);
        }

        const live = qs('[data-save-live]');
        if (live) {
            live.textContent = !isSaved ? t('jobs.saved_announce') : t('jobs.unsaved_announce');
        }
    });
}

function initCandidateJobs() {
    initCandidateSavedJobsState();
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

function messageDraftKey(threadId) {
    return `hb_candidate_message_draft_${threadId}`;
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

    const persistDraft = (threadId) => {
        if (!threadId || !compose) return;
        try {
            const value = compose.value || '';
            if (value.trim()) {
                window.localStorage.setItem(messageDraftKey(threadId), value);
            } else {
                window.localStorage.removeItem(messageDraftKey(threadId));
            }
        } catch {
            // Ignore.
        }
    };

    const restoreDraft = (threadId) => {
        if (!compose) return;
        try {
            compose.value = window.localStorage.getItem(messageDraftKey(threadId)) || '';
        } catch {
            compose.value = '';
        }
    };

    const renderThread = (thread) => {
        if (!thread || !messageList) return;
        root.setAttribute('data-active-thread', thread.id);
        const contact = qs('[data-conversation-contact]', root);
        const meta = qs('[data-conversation-meta]', root);
        const divider = qs('[data-date-divider]', root);
        const composeLabel = qs('[data-compose-label]', root);
        if (contact) contact.textContent = thread.contact;
        if (meta) meta.textContent = `${thread.company} · ${thread.role} · ${thread.code}`;
        if (divider) divider.textContent = thread.date_divider || '';
        if (composeLabel) {
            const template = composeLabel.getAttribute('data-compose-template') || 'Reply to __NAME__';
            const labelText = template.replace('__NAME__', thread.contact);
            composeLabel.textContent = labelText;
            if (compose) {
                compose.setAttribute('aria-label', labelText);
            }
        }

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

        restoreDraft(thread.id);
    };

    list?.addEventListener('click', (event) => {
        const btn = event.target.closest('[data-thread-id]');
        if (!btn) return;
        const previousId = root.getAttribute('data-active-thread');
        persistDraft(previousId);
        const thread = threads.find((item) => item.id === btn.getAttribute('data-thread-id'));
        if (thread) renderThread(thread);
    });

    compose?.addEventListener('input', () => {
        persistDraft(root.getAttribute('data-active-thread'));
        qs('[data-compose-status]', root)?.classList.add('hidden');
    });

    const requestedThread =
        new URLSearchParams(window.location.search).get('thread') ||
        root.getAttribute('data-active-thread');
    const initial =
        threads.find((item) => item.id === requestedThread) ||
        threads.find((item) => item.selected) ||
        threads[0];
    if (initial) {
        renderThread(initial);
    }

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
        const locale = window.__hbLocale === 'ar' ? 'ar' : 'en-US';
        const time = now.toLocaleTimeString(locale, { hour: 'numeric', minute: '2-digit' });
        const localMessage = {
            id: `local-${Date.now()}`,
            from: 'you',
            author: 'Maya Chen',
            role: t('messages.you'),
            time,
            body,
            status: t('messages.draft_status', { time }),
        };
        if (thread) {
            thread.messages = [...(thread.messages || []), localMessage];
            thread.snippet = body.slice(0, 48);
            try {
                window.localStorage.removeItem(messageDraftKey(thread.id));
            } catch {
                // Ignore.
            }
            renderThread(thread);
        }
        if (compose) compose.value = '';
        if (status) {
            status.textContent = t('status.message_undelivered');
            status.classList.remove('hidden');
        }
    });
}

function initCandidateProfile() {
    const root = qs('[data-candidate-profile]');
    if (!root) return;

    const storageKey = 'hb_candidate_profile_draft_v1';

    const collect = (form) => {
        const data = {};
        qsa('input, textarea, select', form).forEach((field) => {
            if (!field.name) return;
            if (field.type === 'checkbox') {
                data[field.name] = field.checked ? '1' : '0';
                return;
            }
            data[field.name] = field.value;
        });
        return data;
    };

    const apply = (form, data) => {
        Object.entries(data || {}).forEach(([key, value]) => {
            const field = form.elements.namedItem(key);
            if (!field || !('value' in field)) return;
            if (field.type === 'checkbox') {
                field.checked = value === '1' || value === true || value === 'true';
                return;
            }
            field.value = value;
        });
    };

    const refreshHeading = (values) => {
        const headline = qs('[data-profile-display-headline]', root);
        if (headline && (values.headline || values.location)) {
            headline.textContent = [values.headline, values.location].filter(Boolean).join(' · ');
        }
    };

    try {
        const saved = JSON.parse(window.localStorage.getItem(storageKey) || 'null');
        if (saved) {
            qsa('[data-profile-form]', root).forEach((form) => apply(form, saved));
            refreshHeading(saved);
        }
    } catch {
        // Ignore.
    }

    qsa('[data-profile-save]', root).forEach((button) => {
        button.addEventListener('click', () => {
            const form = button.closest('form');
            if (!form) return;
            const note = qs(`[data-profile-frontend-note="${button.getAttribute('data-profile-save')}"]`, root);
            let existing = {};
            try {
                existing = JSON.parse(window.localStorage.getItem(storageKey) || '{}') || {};
            } catch {
                existing = {};
            }
            const next = { ...existing, ...collect(form) };
            try {
                window.localStorage.setItem(storageKey, JSON.stringify(next));
            } catch {
                // Ignore.
            }
            refreshHeading(next);
            if (note) {
                note.classList.remove('hidden');
            }
            const live = qs('[data-profile-live]');
            if (live) {
                live.textContent = t('status.draft_saved');
            }
        });
    });

    qs('[data-profile-preview]')?.addEventListener('click', () => {
        const form = qs('[data-profile-form="basics"]', root);
        const values = form ? collect(form) : {};
        const title = qs('[data-profile-preview-name]');
        const meta = qs('[data-profile-preview-meta]');
        const about = qs('[data-profile-preview-about]');
        if (title) {
            title.textContent =
                values.name || qs('[data-profile-display-name]', root)?.textContent || '';
        }
        if (meta) {
            meta.textContent = [values.headline, values.location].filter(Boolean).join(' · ');
        }
        if (about) {
            about.textContent = values.about || '';
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
    if (label) {
        label.textContent = t('notifications.unread', { count: String(unread) });
    }

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
            if (toggle) {
                toggle.textContent = item.unread
                    ? t('notifications.mark_read')
                    : t('notifications.mark_unread');
            }

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

function readChecklistState(key, legacyKeys = []) {
    const tryParse = (raw) => {
        try {
            return raw ? JSON.parse(raw) : null;
        } catch {
            return null;
        }
    };
    let state = tryParse(window.localStorage.getItem(key));
    if (state && typeof state === 'object') {
        return state;
    }
    for (const legacy of legacyKeys) {
        const migrated = tryParse(window.localStorage.getItem(legacy));
        if (migrated && typeof migrated === 'object') {
            try {
                window.localStorage.setItem(key, JSON.stringify(migrated));
            } catch {
                // Preview-only storage.
            }
            return migrated;
        }
    }
    return {};
}

function bindSessionChecklist(root) {
    if (!root) return;
    const key = root.getAttribute('data-checklist-key') || '';
    if (!key) return;
    let legacyKeys = [];
    try {
        legacyKeys = JSON.parse(root.getAttribute('data-checklist-legacy-keys') || '[]') || [];
    } catch {
        legacyKeys = [];
    }
    let state = readChecklistState(key, legacyKeys);
    const status = qs('[data-checklist-status]', root);
    const savedNote = () =>
        root.getAttribute('data-saved-note') ||
        root.querySelector('#prep-checklist')?.getAttribute('data-saved-note') ||
        status?.textContent ||
        '';

    const persist = () => {
        try {
            window.localStorage.setItem(key, JSON.stringify(state));
        } catch {
            // Preview-only storage.
        }
    };

    qsa('[data-checklist-item]', root).forEach((input) => {
        const id = input.getAttribute('data-checklist-item');
        if (!id) return;
        if (Object.prototype.hasOwnProperty.call(state, id)) {
            input.checked = Boolean(state[id]);
        } else {
            state[id] = input.checked;
        }
        input.addEventListener('change', () => {
            state[id] = input.checked;
            persist();
            if (status) {
                status.textContent = savedNote();
            }
        });
    });
}

function initInterviewerDashboard() {
    const root = qs('[data-interviewer-dashboard]');
    if (!root) return;
    bindSessionChecklist(root);
}

function initInterviewerPrepare() {
    const root = qs('[data-interviewer-prepare]');
    if (!root) return;

    const prefix = root.getAttribute('data-storage-prefix') || '';
    const checklistKey = `${prefix}_role_v1`;
    const notesKey = `${prefix}_prep_notes_v1`;
    const i18n = JSON.parse(root.getAttribute('data-i18n') || '{}');

    let checklist = {};
    try {
        checklist = JSON.parse(window.localStorage.getItem(checklistKey) || '{}') || {};
    } catch {
        checklist = {};
    }

    const progressEl = qs('[data-prep-progress]', root);
    const checklistStatus = qs('[data-prep-checklist-status]', root);
    const notesStatus = qs('[data-prep-notes-status]', root);
    const notesEl = qs('[data-prep-notes]', root);

    const updateProgress = () => {
        const items = qsa('[data-prep-checklist-item]', root);
        const done = items.filter((el) => el.checked).length;
        if (progressEl) {
            progressEl.textContent = (i18n.progress || ':done of :total ready')
                .replace(':done', String(done))
                .replace(':total', String(items.length));
        }
    };

    const persistChecklist = () => {
        try {
            window.localStorage.setItem(checklistKey, JSON.stringify(checklist));
        } catch {
            // Preview-only storage.
        }
        if (checklistStatus) {
            checklistStatus.textContent = i18n.checklist_saved || '';
        }
        updateProgress();
    };

    qsa('[data-prep-checklist-item]', root).forEach((input) => {
        const id = input.getAttribute('data-prep-checklist-item');
        if (!id) return;
        if (Object.prototype.hasOwnProperty.call(checklist, id)) {
            input.checked = Boolean(checklist[id]);
        } else {
            checklist[id] = input.checked;
        }
        input.addEventListener('change', () => {
            checklist[id] = input.checked;
            persistChecklist();
        });
    });
    updateProgress();

    if (notesEl) {
        try {
            notesEl.value = window.localStorage.getItem(notesKey) || '';
        } catch {
            notesEl.value = '';
        }
        if (notesStatus) {
            notesStatus.textContent = notesEl.value.trim()
                ? i18n.notes_saved || ''
                : i18n.notes_empty || '';
        }
        let notesTimer = null;
        const saveNotes = () => {
            try {
                window.localStorage.setItem(notesKey, notesEl.value);
            } catch {
                // Preview-only storage.
            }
            if (notesStatus) {
                notesStatus.textContent = notesEl.value.trim()
                    ? i18n.notes_saved || ''
                    : i18n.notes_empty || '';
            }
        };
        notesEl.addEventListener('input', () => {
            if (notesStatus) notesStatus.textContent = i18n.notes_saved || '';
            window.clearTimeout(notesTimer);
            notesTimer = window.setTimeout(saveNotes, 300);
        });
        notesEl.addEventListener('blur', saveNotes);
    }

    qsa('[data-prep-expand]', root).forEach((btn) => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('aria-controls');
            const panel = targetId ? document.getElementById(targetId) : null;
            if (!panel) return;
            const open = btn.getAttribute('aria-expanded') === 'true';
            const next = !open;
            btn.setAttribute('aria-expanded', next ? 'true' : 'false');
            panel.hidden = !next;
            panel.classList.toggle('hidden', !next);
            const label = qs('[data-expand-label]', btn);
            if (btn.getAttribute('data-prep-expand') === 'background') {
                btn.textContent = next
                    ? i18n.hide_background || 'Hide background'
                    : i18n.show_background || 'Show background';
            } else if (label) {
                label.textContent = next
                    ? i18n.hide_question || 'Hide prompt'
                    : i18n.show_question || 'Show prompt';
            }
        });
    });

    // Session readiness shares Dashboard storage for the same interview id.
    bindSessionChecklist(qs('[data-session-checklist]', root));
}

function initInterviewerLive() {
    const root = qs('[data-interviewer-live]');
    if (!root) return;

    const prefix = root.getAttribute('data-storage-prefix') || '';
    const notesKey = `${prefix}_live_notes_v1`;
    const agendaKey = `${prefix}_live_agenda_v1`;
    const questionsKey = `${prefix}_live_q_v1`;
    const timerKey = `${prefix}_live_timer_v1`;
    const i18n = JSON.parse(root.getAttribute('data-i18n') || '{}');
    let seedNotes = {};
    try {
        seedNotes = JSON.parse(root.getAttribute('data-seed-notes') || '{}') || {};
    } catch {
        seedNotes = {};
    }

    const readJson = (key, fallback) => {
        try {
            return JSON.parse(window.localStorage.getItem(key) || 'null') ?? fallback;
        } catch {
            return fallback;
        }
    };
    const writeJson = (key, value) => {
        try {
            window.localStorage.setItem(key, JSON.stringify(value));
        } catch {
            // Preview-only storage.
        }
    };

    // Agenda
    let agendaState = readJson(agendaKey, {});
    const agendaProgress = qs('[data-live-agenda-progress]', root);
    const updateAgendaProgress = () => {
        const items = qsa('[data-live-agenda]', root);
        const done = items.filter((el) => el.checked).length;
        if (agendaProgress) {
            agendaProgress.textContent = (i18n.agenda_progress || ':done of :total sections marked')
                .replace(':done', String(done))
                .replace(':total', String(items.length));
        }
    };
    qsa('[data-live-agenda]', root).forEach((input) => {
        const id = input.getAttribute('data-live-agenda');
        if (!id) return;
        if (Object.prototype.hasOwnProperty.call(agendaState, id)) {
            input.checked = Boolean(agendaState[id]);
        } else {
            agendaState[id] = input.checked;
        }
        input.addEventListener('change', () => {
            agendaState[id] = input.checked;
            writeJson(agendaKey, agendaState);
            updateAgendaProgress();
        });
    });
    updateAgendaProgress();

    // Questions covered
    let questionState = readJson(questionsKey, {});
    qsa('[data-live-question]', root).forEach((input) => {
        const id = input.getAttribute('data-live-question');
        if (!id) return;
        if (Object.prototype.hasOwnProperty.call(questionState, id)) {
            input.checked = Boolean(questionState[id]);
        }
        input.addEventListener('change', () => {
            questionState[id] = input.checked;
            writeJson(questionsKey, questionState);
        });
    });

    // Expand questions
    qsa('[data-live-expand]', root).forEach((btn) => {
        const panelId = btn.getAttribute('aria-controls');
        const panel = panelId ? document.getElementById(panelId) : null;
        btn.addEventListener('click', () => {
            if (!panel) return;
            const open = btn.getAttribute('aria-expanded') !== 'false';
            const next = !open;
            btn.setAttribute('aria-expanded', next ? 'true' : 'false');
            panel.hidden = !next;
            panel.classList.toggle('hidden', !next);
            const label = qs('[data-expand-label]', btn);
            if (label) {
                label.textContent = next ? i18n.hide_question || 'Hide' : i18n.show_question || 'Show';
            }
        });
    });

    // Note tabs
    const switchNoteTab = (tabId) => {
        qsa('[data-live-note-tab]', root).forEach((btn) => {
            const on = btn.getAttribute('data-live-note-tab') === tabId;
            btn.setAttribute('aria-selected', on ? 'true' : 'false');
            btn.classList.toggle('bg-hb-info-soft', on);
            btn.classList.toggle('font-semibold', on);
            btn.classList.toggle('text-hb-blue', on);
            btn.classList.toggle('font-medium', !on);
            btn.classList.toggle('text-hb-muted', !on);
        });
        qsa('[data-live-note-panel]', root).forEach((panel) => {
            const on = panel.getAttribute('data-live-note-panel') === tabId;
            panel.hidden = !on;
            panel.classList.toggle('hidden', !on);
        });
    };
    qsa('[data-live-note-tab]', root).forEach((btn) => {
        btn.addEventListener('click', () => switchNoteTab(btn.getAttribute('data-live-note-tab') || ''));
    });

    // Notes
    let notes = readJson(notesKey, null);
    if (!notes || typeof notes !== 'object') {
        notes = { ...seedNotes };
    }
    const notesStatus = qs('[data-live-notes-status]', root);
    let dirty = false;
    const setNotesStatus = (saved) => {
        if (!notesStatus) return;
        notesStatus.textContent = saved ? i18n.notes_saved || '' : i18n.notes_unsaved || '';
    };
    qsa('[data-live-note-field]', root).forEach((field) => {
        const tab = field.getAttribute('data-tab');
        const id = field.getAttribute('data-field');
        if (!tab || !id) return;
        field.value = notes?.[tab]?.[id] || '';
        field.addEventListener('input', () => {
            dirty = true;
            setNotesStatus(false);
        });
    });
    // Seed/default notes are device-local content, not unsaved edits.
    setNotesStatus(true);

    const saveNotes = () => {
        const next = {};
        qsa('[data-live-note-field]', root).forEach((field) => {
            const tab = field.getAttribute('data-tab');
            const id = field.getAttribute('data-field');
            if (!tab || !id) return;
            if (!next[tab]) next[tab] = {};
            next[tab][id] = field.value;
        });
        notes = next;
        writeJson(notesKey, notes);
        dirty = false;
        setNotesStatus(true);
    };
    qs('[data-live-save-notes]', root)?.addEventListener('click', saveNotes);

    // Local timer (label may live in the page header outside the live root)
    const timerEl = qs('[data-live-timer]', root);
    const timerLabel = qs('[data-live-timer-label]', root) || qs('[data-live-timer-label]');
    const btnStart = qs('[data-live-timer-action="start"]', root);
    const btnPause = qs('[data-live-timer-action="pause"]', root);
    const btnResume = qs('[data-live-timer-action="resume"]', root);
    const btnReset = qs('[data-live-timer-action="reset"]', root);

    let timerState = readJson(timerKey, { elapsed: 0, running: false, startedAt: null });
    let tickId = null;

    const formatTime = (seconds) => {
        const s = Math.max(0, Math.floor(seconds));
        const mm = String(Math.floor(s / 60)).padStart(2, '0');
        const ss = String(s % 60).padStart(2, '0');
        return `${mm}:${ss}`;
    };

    const currentElapsed = () => {
        if (timerState.running && timerState.startedAt) {
            return timerState.elapsed + (Date.now() - timerState.startedAt) / 1000;
        }
        return timerState.elapsed;
    };

    const persistTimer = () => writeJson(timerKey, {
        elapsed: timerState.elapsed,
        running: timerState.running,
        startedAt: timerState.running ? timerState.startedAt : null,
    });

    const syncTimerUi = () => {
        const elapsed = currentElapsed();
        if (timerEl) timerEl.textContent = formatTime(elapsed);
        if (timerLabel) {
            timerLabel.textContent = timerState.running
                ? i18n.timer_running || ''
                : elapsed > 0
                  ? i18n.timer_paused || ''
                  : i18n.timer_idle || '';
        }
        if (btnStart) btnStart.disabled = timerState.running || elapsed > 0;
        if (btnPause) btnPause.disabled = !timerState.running;
        if (btnResume) btnResume.disabled = timerState.running || elapsed <= 0;
    };

    const startTick = () => {
        window.clearInterval(tickId);
        tickId = window.setInterval(() => {
            if (!timerState.running) return;
            syncTimerUi();
        }, 250);
    };

    btnStart?.addEventListener('click', () => {
        timerState.running = true;
        timerState.startedAt = Date.now();
        persistTimer();
        syncTimerUi();
        startTick();
    });
    btnPause?.addEventListener('click', () => {
        if (!timerState.running) return;
        timerState.elapsed = currentElapsed();
        timerState.running = false;
        timerState.startedAt = null;
        persistTimer();
        syncTimerUi();
    });
    btnResume?.addEventListener('click', () => {
        timerState.running = true;
        timerState.startedAt = Date.now();
        persistTimer();
        syncTimerUi();
        startTick();
    });
    btnReset?.addEventListener('click', () => {
        timerState = { elapsed: 0, running: false, startedAt: null };
        persistTimer();
        syncTimerUi();
    });

    if (timerState.running && timerState.startedAt) {
        startTick();
    } else if (timerState.running) {
        timerState.running = false;
        timerState.startedAt = null;
    }
    syncTimerUi();

    window.addEventListener('beforeunload', () => {
        if (timerState.running) {
            timerState.elapsed = currentElapsed();
            timerState.startedAt = Date.now();
            persistTimer();
        }
        if (dirty) saveNotes();
    });
}

function initInterviewerSchedule() {
    const root = qs('[data-interviewer-schedule]');
    if (!root) return;

    const i18n = JSON.parse(root.getAttribute('data-i18n') || '{}');
    const interviews = JSON.parse(root.getAttribute('data-interviews') || '[]');
    const upcoming = JSON.parse(root.getAttribute('data-upcoming') || '[]');
    const past = JSON.parse(root.getAttribute('data-past') || '[]');
    const weeks = JSON.parse(root.getAttribute('data-weeks') || '[]');
    const currentWeekId = root.getAttribute('data-current-week-id') || '';
    let weekIndex = Math.max(0, weeks.findIndex((week) => week.id === currentWeekId));
    if (weekIndex < 0) weekIndex = 0;
    let selectedId = root.getAttribute('data-selected-id') || '';
    let activeTab = 'week';

    const qInput = qs('[data-sched-q]', root);
    const jobSelect = qs('[data-sched-job]', root);
    const typeSelect = qs('[data-sched-type]', root);
    const emptyEl = qs('[data-sched-empty]', root);
    const statusEl = qs('[data-week-nav-status]', root);
    const weekEmptyEl = qs('[data-week-empty]', root);
    const gridEl = qs('[data-sched-grid]', root);
    const weekHeading = qs('[data-week-heading]', root);
    const upcomingList = qs('[data-upcoming-list]', root);
    const pastList = qs('[data-past-list]', root);
    const upcomingEmpty = qs('[data-upcoming-empty]', root);
    const pastEmpty = qs('[data-past-empty]', root);

    const filters = () => ({
        q: (qInput?.value || '').trim().toLowerCase(),
        job: jobSelect?.value || '',
        type: typeSelect?.value || '',
    });

    const matchesFilters = (row) => {
        const { q, job, type } = filters();
        if (job && (row.job_id || '') !== job) return false;
        if (type && (row.type_key || '') !== type) return false;
        if (!q) return true;
        const hay = `${row.applicant || ''} ${row.job_code || ''} ${row.title || ''}`.toLowerCase();
        return hay.includes(q);
    };

    const setSelected = (id) => {
        selectedId = id || '';
        const row = interviews.find((item) => item.id === selectedId) || null;
        const name = qs('[data-sel-name]', root);
        const status = qs('[data-sel-status]', root);
        const job = qs('[data-sel-job]', root);
        const when = qs('[data-sel-when]', root);
        const type = qs('[data-sel-type]', root);
        const prep = qs('[data-sel-prep]', root);
        if (!row) return;
        if (name) name.textContent = row.applicant || '';
        if (status) status.textContent = row.status || '';
        if (job) job.textContent = `${row.job_title || ''} · ${row.job_code || ''}`;
        if (when) when.textContent = `${row.when || ''} · ${row.time || ''}`;
        if (type) type.textContent = row.title || '';
        if (prep) prep.textContent = row.preparation || '';
        const prepareLink = qs('[data-sel-prepare]', root);
        const liveLink = qs('[data-sel-live]', root);
        if (prepareLink && selectedId) {
            prepareLink.setAttribute('href', `/interviewer/interviews/${encodeURIComponent(selectedId)}/prepare`);
        }
        if (liveLink && selectedId) {
            liveLink.setAttribute('href', `/interviewer/interviews/${encodeURIComponent(selectedId)}/live`);
        }
        qsa('[data-interview-slot]', root).forEach((btn) => {
            const active = btn.getAttribute('data-interview-id') === selectedId;
            btn.classList.toggle('ring-2', active);
            btn.classList.toggle('ring-hb-blue/50', active);
            btn.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
    };

    const renderWeek = () => {
        const week = weeks[weekIndex];
        if (!week || !gridEl) return;
        if (weekHeading) weekHeading.textContent = week.label || '';
        if (statusEl) {
            statusEl.textContent = (i18n.week_nav_status || '').replace(':label', week.label || '');
        }

        const { q, job, type } = filters();
        const hasFilter = Boolean(q || job || type);
        let visibleInterviewCount = 0;

        gridEl.innerHTML = (week.days || [])
            .map((day) => {
                const slots = (day.slots || []).filter((slot) => {
                    if (slot.available) return !hasFilter;
                    const interview = interviews.find((item) => item.id === slot.interview_id);
                    if (!interview) return false;
                    const ok = matchesFilters(interview);
                    if (ok) visibleInterviewCount += 1;
                    return ok;
                });

                const highlight = day.highlight
                    ? 'bg-hb-info-soft/60 ring-1 ring-hb-blue/20'
                    : 'bg-hb-page/40';

                const slotHtml = slots.length
                    ? slots
                          .map((slot) => {
                              if (slot.available) {
                                  return `<p class="rounded-md px-2 py-1.5 text-xs text-hb-muted">${escapeHtml(slot.time)} · ${escapeHtml(i18n.available || 'Available')}</p>`;
                              }
                              const active = slot.interview_id === selectedId;
                              return `<button type="button" class="w-full rounded-md px-2 py-1.5 text-start text-xs ${active ? 'bg-hb-blue text-white ring-2 ring-hb-blue/50' : 'bg-hb-surface text-hb-navy hover:bg-hb-info-soft'} focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-hb-blue/40" data-interview-slot data-interview-id="${escapeHtml(slot.interview_id || '')}" aria-pressed="${active ? 'true' : 'false'}">
                                <span class="block font-semibold">${escapeHtml(slot.time || '')}</span>
                                <span class="block">${escapeHtml(slot.candidate || slot.label || '')}</span>
                                <span class="block opacity-80">${escapeHtml(slot.type || '')}${slot.job_code ? ` · ${escapeHtml(slot.job_code)}` : ''}</span>
                            </button>`;
                          })
                          .join('')
                    : `<p class="text-xs text-hb-muted">${escapeHtml(i18n.empty_week || '')}</p>`;

                return `<div class="min-h-[10rem] rounded-xl p-3 ${highlight}" data-day-key="${escapeHtml(day.key || '')}">
                    <p class="text-xs font-semibold text-hb-navy">${escapeHtml(day.label || '')}</p>
                    <div class="mt-2 space-y-2">${slotHtml}</div>
                </div>`;
            })
            .join('');

        if (weekEmptyEl) {
            const show = hasFilter && visibleInterviewCount === 0;
            weekEmptyEl.hidden = !show;
            weekEmptyEl.classList.toggle('hidden', !show);
            weekEmptyEl.textContent = i18n.empty_filter || '';
        }

        qsa('[data-interview-slot]', gridEl).forEach((btn) => {
            btn.addEventListener('click', () => setSelected(btn.getAttribute('data-interview-id') || ''));
        });

        qsa('[data-detail-row]', root).forEach((row) => {
            const id = row.getAttribute('data-interview-id');
            const interview = interviews.find((item) => item.id === id);
            const show = interview ? matchesFilters(interview) : true;
            row.classList.toggle('hidden', !show);
        });
    };

    const renderList = (listEl, emptyElNode, rows, isPast = false) => {
        if (!listEl) return;
        const visible = rows.filter(matchesFilters);
        listEl.innerHTML = visible
            .map((row) => {
                const action = isPast
                    ? `<a href="/interviewer/evaluations/${encodeURIComponent(row.id || '')}" class="mt-2 inline-flex text-sm font-semibold text-hb-blue hover:underline">${escapeHtml(i18n.open_scorecard || 'Open scorecard')}</a>`
                    : `<button type="button" class="mt-2 text-sm font-semibold text-hb-blue" data-select-from-list data-interview-id="${escapeHtml(row.id || '')}">${escapeHtml(i18n.selected || 'Select')}</button>`;
                return `<li class="py-4 first:pt-0">
                    <p class="font-semibold text-hb-navy">${escapeHtml(row.applicant || '')}</p>
                    <p class="mt-1 text-sm text-hb-muted">${escapeHtml(row.title || '')} · ${escapeHtml(row.job_code || '')}</p>
                    <p class="mt-1 text-sm text-hb-text">${escapeHtml(row.when || '')} · ${escapeHtml(row.time_short || row.time || '')}</p>
                    ${action}
                </li>`;
            })
            .join('');
        if (emptyElNode) {
            emptyElNode.hidden = visible.length > 0;
            emptyElNode.classList.toggle('hidden', visible.length > 0);
        }
        qsa('[data-select-from-list]', listEl).forEach((btn) => {
            btn.addEventListener('click', () => {
                setSelected(btn.getAttribute('data-interview-id') || '');
                switchTab('week');
            });
        });
    };

    const switchTab = (tab) => {
        activeTab = tab;
        qsa('[data-sched-tab]', root).forEach((btn) => {
            const on = btn.getAttribute('data-sched-tab') === tab;
            btn.setAttribute('aria-selected', on ? 'true' : 'false');
            btn.classList.toggle('border-hb-blue', on);
            btn.classList.toggle('text-hb-blue', on);
            btn.classList.toggle('font-semibold', on);
            btn.classList.toggle('border-transparent', !on);
            btn.classList.toggle('text-hb-muted', !on);
            btn.classList.toggle('font-medium', !on);
        });
        qsa('[data-sched-panel]', root).forEach((panel) => {
            const on = panel.getAttribute('data-sched-panel') === tab;
            panel.classList.toggle('hidden', !on);
            panel.hidden = !on;
        });
        if (tab === 'upcoming') renderList(upcomingList, upcomingEmpty, upcoming, false);
        if (tab === 'past') renderList(pastList, pastEmpty, past, true);
        if (tab === 'week') renderWeek();
    };

    const applyFilters = () => {
        const { q, job, type } = filters();
        const any = Boolean(q || job || type);
        const pool = activeTab === 'past' ? past : interviews;
        const visible = pool.filter(matchesFilters);
        if (emptyEl) {
            const show = any && visible.length === 0 && activeTab !== 'week';
            emptyEl.hidden = !show;
            emptyEl.classList.toggle('hidden', !show);
            emptyEl.textContent = i18n.empty_filter || '';
        }
        if (activeTab === 'week') renderWeek();
        if (activeTab === 'upcoming') renderList(upcomingList, upcomingEmpty, upcoming, false);
        if (activeTab === 'past') renderList(pastList, pastEmpty, past, true);
    };

    qs('[data-week-nav="prev"]', root)?.addEventListener('click', () => {
        weekIndex = Math.max(0, weekIndex - 1);
        renderWeek();
    });
    qs('[data-week-nav="next"]', root)?.addEventListener('click', () => {
        weekIndex = Math.min(weeks.length - 1, weekIndex + 1);
        renderWeek();
    });
    qs('[data-week-today]', root)?.addEventListener('click', () => {
        weekIndex = Math.max(0, weeks.findIndex((week) => week.id === currentWeekId));
        if (weekIndex < 0) weekIndex = 0;
        renderWeek();
    });

    qsa('[data-sched-tab]', root).forEach((btn) => {
        btn.addEventListener('click', () => switchTab(btn.getAttribute('data-sched-tab') || 'week'));
    });
    qInput?.addEventListener('input', applyFilters);
    jobSelect?.addEventListener('change', applyFilters);
    typeSelect?.addEventListener('change', applyFilters);
    qs('[data-sched-clear]', root)?.addEventListener('click', () => {
        if (qInput) qInput.value = '';
        if (jobSelect) jobSelect.value = '';
        if (typeSelect) typeSelect.value = '';
        applyFilters();
    });

    const availKey = root.getAttribute('data-availability-key') || 'hb_interviewer_availability_prefs_v1';
    let availState = {};
    try {
        const raw = window.localStorage.getItem(availKey);
        availState = raw ? JSON.parse(raw) : {};
    } catch {
        availState = {};
    }
    qsa('[data-avail-day]', root).forEach((input) => {
        const day = input.getAttribute('data-avail-day');
        if (!day) return;
        if (Object.prototype.hasOwnProperty.call(availState, day)) {
            input.checked = Boolean(availState[day]);
        }
        input.addEventListener('change', () => {
            availState[day] = input.checked;
            try {
                window.localStorage.setItem(availKey, JSON.stringify(availState));
            } catch {
                // Ignore.
            }
            const note = qs('[data-avail-note]', root);
            if (note) {
                note.textContent =
                    document.documentElement.lang === 'ar'
                        ? 'تُحفظ النوافذ المفضّلة على هذا الجهاز فقط. لم تتم أي مزامنة.'
                        : 'Preferred windows are kept on this device only. Nothing was synced.';
            }
        });
    });

    renderWeek();
    setSelected(selectedId);
}

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

function initInterviewerScorecard() {
    const root = qs('[data-interviewer-scorecard]');
    if (!root) return;

    const key = root.getAttribute('data-storage-key') || '';
    const i18n = JSON.parse(root.getAttribute('data-i18n') || '{}');
    let seed = {};
    try {
        seed = JSON.parse(root.getAttribute('data-seed') || '{}') || {};
    } catch {
        seed = {};
    }

    const readDraft = () => {
        try {
            return JSON.parse(window.localStorage.getItem(key) || 'null');
        } catch {
            return null;
        }
    };

    let draft = readDraft();
    if (!draft || typeof draft !== 'object') {
        draft = {
            ratings: { ...(seed.ratings || {}) },
            evidence: { ...(seed.evidence || {}) },
            strengths: seed.strengths || '',
            concerns: seed.concerns || '',
            recommendation: seed.recommendation || '',
            attested: false,
        };
    }

    const draftStatus = qs('[data-score-draft-status]', root);
    const progressEl = qs('[data-score-progress]', root);
    const statusBadge = qs('[data-score-status-badge]', root);
    const ratingLabels = {
        1: i18n.rating_1 || 'Below',
        2: i18n.rating_2 || 'Developing',
        3: i18n.rating_3 || 'Meets',
        4: i18n.rating_4 || 'Strong',
        no: i18n.rating_no || 'Not observed',
    };

    // Prefer labels from radios' visible text when available
    const ratingLabelFor = (compId, value) => {
        const input = root.querySelector(`[data-score-rating="${compId}"][value="${value}"]`);
        const text = input?.closest('label')?.textContent?.trim();
        return text || ratingLabels[value] || value;
    };

    const setDraftStatus = (saved) => {
        if (draftStatus) {
            draftStatus.textContent = saved ? i18n.draft_saved || '' : i18n.draft_unsaved || '';
        }
    };

    const updateProgress = () => {
        const comps = qsa('[data-competency]', root);
        let done = 0;
        comps.forEach((li) => {
            const id = li.getAttribute('data-competency');
            if (draft.ratings?.[id]) done += 1;
            const summary = qs('[data-rating-summary]', li);
            if (summary && draft.ratings?.[id]) {
                summary.textContent = ratingLabelFor(id, draft.ratings[id]);
            } else if (summary) {
                summary.textContent = '';
            }
        });
        if (progressEl) {
            progressEl.textContent = (i18n.progress || ':done of :total competencies rated')
                .replace(':done', String(done))
                .replace(':total', String(comps.length));
        }
    };

    const collectFromDom = () => {
        const ratings = {};
        const evidence = {};
        qsa('[data-score-rating]', root).forEach((input) => {
            const id = input.getAttribute('data-score-rating');
            if (input.checked && id) ratings[id] = input.value;
        });
        qsa('[data-score-evidence]', root).forEach((field) => {
            const id = field.getAttribute('data-score-evidence');
            if (id) evidence[id] = field.value;
        });
        draft = {
            ratings,
            evidence,
            strengths: qs('[data-score-strengths]', root)?.value || '',
            concerns: qs('[data-score-concerns]', root)?.value || '',
            recommendation: qs('[data-score-recommendation]', root)?.value || '',
            attested: Boolean(qs('[data-score-attest]', root)?.checked),
        };
        return draft;
    };

    const applyDraftToDom = () => {
        Object.entries(draft.ratings || {}).forEach(([id, value]) => {
            const input = root.querySelector(`[data-score-rating="${id}"][value="${value}"]`);
            if (input) input.checked = true;
        });
        Object.entries(draft.evidence || {}).forEach(([id, value]) => {
            const field = root.querySelector(`[data-score-evidence="${id}"]`);
            if (field) field.value = value;
        });
        const strengths = qs('[data-score-strengths]', root);
        const concerns = qs('[data-score-concerns]', root);
        const recommendation = qs('[data-score-recommendation]', root);
        const attest = qs('[data-score-attest]', root);
        if (strengths) strengths.value = draft.strengths || '';
        if (concerns) concerns.value = draft.concerns || '';
        if (recommendation) recommendation.value = draft.recommendation || '';
        if (attest) attest.checked = Boolean(draft.attested);
        updateProgress();
        setDraftStatus(Boolean(window.localStorage.getItem(key)));
    };

    const saveDraft = () => {
        collectFromDom();
        try {
            window.localStorage.setItem(key, JSON.stringify(draft));
        } catch {
            // Preview-only storage.
        }
        setDraftStatus(true);
        if (statusBadge) {
            statusBadge.textContent = i18n.status_draft || statusBadge.textContent;
        }
        return draft;
    };

    const clearErrors = () => {
        qsa('[data-rating-error], [data-strengths-error], [data-concerns-error], [data-recommendation-error], [data-attest-error]', root).forEach(
            (el) => {
                el.hidden = true;
                el.classList.add('hidden');
                el.textContent = '';
            }
        );
    };

    const showError = (el, message) => {
        if (!el) return false;
        el.textContent = message;
        el.hidden = false;
        el.classList.remove('hidden');
        return false;
    };

    const validate = () => {
        clearErrors();
        collectFromDom();
        let ok = true;
        qsa('[data-competency]', root).forEach((li) => {
            const id = li.getAttribute('data-competency');
            if (!draft.ratings?.[id]) {
                ok = showError(qs(`[data-rating-error="${id}"]`, root), i18n.err_rating || '') || ok;
                ok = false;
            }
        });
        if (!(draft.strengths || '').trim()) {
            showError(qs('[data-strengths-error]', root), i18n.err_strengths || '');
            ok = false;
        }
        if (!(draft.concerns || '').trim()) {
            showError(qs('[data-concerns-error]', root), i18n.err_concerns || '');
            ok = false;
        }
        if (!(draft.recommendation || '').trim()) {
            showError(qs('[data-recommendation-error]', root), i18n.err_recommendation || '');
            ok = false;
        }
        if (!draft.attested) {
            showError(qs('[data-attest-error]', root), i18n.err_attest || '');
            ok = false;
        }
        return ok;
    };

    const setMode = (mode) => {
        qsa('[data-score-panel]', root).forEach((panel) => {
            const on = panel.getAttribute('data-score-panel') === mode;
            panel.hidden = !on;
            panel.classList.toggle('hidden', !on);
        });
    };

    const buildReview = () => {
        const body = qs('[data-score-review-body]', root);
        if (!body) return;
        collectFromDom();
        const recSelect = qs('[data-score-recommendation]', root);
        const recLabel = recSelect?.selectedOptions?.[0]?.textContent?.trim() || draft.recommendation;
        const rows = qsa('[data-competency]', root)
            .map((li) => {
                const id = li.getAttribute('data-competency');
                const label = li.querySelector('legend')?.textContent?.trim() || id;
                const rating = ratingLabelFor(id, draft.ratings?.[id] || '');
                const evidence = draft.evidence?.[id] || '—';
                return `<div class="rounded-lg border border-hb-border p-3"><p class="font-semibold text-hb-navy">${escapeHtml(label)}</p><p class="mt-1 text-hb-muted">${escapeHtml(rating)}</p><p class="mt-2 text-hb-text">${escapeHtml(evidence)}</p></div>`;
            })
            .join('');
        body.innerHTML = `${rows}
            <div class="rounded-lg border border-hb-border p-3"><p class="font-semibold text-hb-navy">${escapeHtml(i18n.strengths_label || 'Strengths')}</p><p class="mt-2">${escapeHtml(draft.strengths)}</p></div>
            <div class="rounded-lg border border-hb-border p-3"><p class="font-semibold text-hb-navy">${escapeHtml(i18n.concerns_label || 'Concerns')}</p><p class="mt-2">${escapeHtml(draft.concerns)}</p></div>
            <div class="rounded-lg border border-hb-border p-3"><p class="font-semibold text-hb-navy">${escapeHtml(i18n.overall_label || 'Recommendation')}</p><p class="mt-2">${escapeHtml(recLabel)}</p></div>
            <p class="text-hb-muted">${escapeHtml(i18n.review_ok || '')}</p>`;
    };

    root.addEventListener('change', (event) => {
        if (event.target.matches('[data-score-rating], [data-score-attest], [data-score-recommendation]')) {
            collectFromDom();
            updateProgress();
            setDraftStatus(false);
        }
    });
    root.addEventListener('input', (event) => {
        if (event.target.matches('[data-score-evidence], [data-score-strengths], [data-score-concerns]')) {
            setDraftStatus(false);
        }
    });

    qsa('[data-score-save]', root).forEach((btn) => {
        btn.addEventListener('click', () => saveDraft());
    });
    qs('[data-score-review]', root)?.addEventListener('click', () => {
        if (!validate()) return;
        saveDraft();
        buildReview();
        setMode('review');
        if (statusBadge) statusBadge.textContent = i18n.status_ready || statusBadge.textContent;
    });
    qs('[data-score-edit]', root)?.addEventListener('click', () => setMode('edit'));
    qs('[data-score-submit]', root)?.addEventListener('click', () => {
        if (!validate()) return;
        saveDraft();
        const modal = document.getElementById('score-submit-modal');
        if (modal && typeof openModal === 'function') {
            openModal(modal, qs('[data-score-submit]', root));
        } else if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modal.setAttribute('data-hb-modal-open', 'true');
        }
    });
    qs('[data-score-save-and-close]', document)?.addEventListener('click', () => {
        saveDraft();
        const modal = document.getElementById('score-submit-modal');
        if (modal && typeof closeModal === 'function') {
            closeModal(modal);
        } else if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            modal.removeAttribute('data-hb-modal-open');
        }
    });

    applyDraftToDom();
    setMode('edit');
}

function initAdminOrganizations() {
    const root = qs('[data-admin-organizations]');
    if (!root) return;

    let i18n = {};
    try {
        i18n = JSON.parse(root.getAttribute('data-i18n') || '{}');
    } catch {
        i18n = {};
    }

    const pageSize = Math.max(1, Number(root.getAttribute('data-page-size') || 7));
    let tab = root.getAttribute('data-default-tab') || 'active';
    let page = 1;
    const qInput = qs('[data-orgs-q]', root);
    const typeSelect = qs('[data-orgs-type]', root);
    const planSelect = qs('[data-orgs-plan]', root);
    const emptyEl = qs('[data-orgs-empty]', root);
    const pagination = qs('[data-orgs-pagination]', root);
    const pageStatus = qs('[data-orgs-page-status]', root);
    const pageNumbers = qs('[data-orgs-page-numbers]', root);
    const prevBtn = qs('[data-orgs-page-prev]', root);
    const nextBtn = qs('[data-orgs-page-next]', root);

    const initialQ = root.getAttribute('data-initial-q') || '';
    if (qInput && initialQ && !qInput.value) {
        qInput.value = initialQ;
    }

    const rows = () => qsa('[data-org-row]', root);

    const setTabUi = (nextTab) => {
        tab = nextTab;
        qsa('[data-orgs-tab]', root).forEach((btn) => {
            const active = btn.getAttribute('data-orgs-tab') === tab;
            btn.setAttribute('aria-selected', active ? 'true' : 'false');
            btn.classList.toggle('border-hb-blue', active);
            btn.classList.toggle('text-hb-blue', active);
            btn.classList.toggle('border-transparent', !active);
            btn.classList.toggle('text-hb-muted', !active);
        });
    };

    const labelForTab = () => {
        const key = `label_${tab}`;
        return i18n[key] || i18n.label_filtered || '';
    };

    const filteredRows = () => {
        const q = (qInput?.value || '').trim().toLowerCase();
        const type = typeSelect?.value || 'all';
        const plan = planSelect?.value || 'all';
        return rows().filter((row) => {
            if (row.getAttribute('data-tab') !== tab) return false;
            if (type !== 'all' && row.getAttribute('data-type') !== type) return false;
            if (plan !== 'all' && row.getAttribute('data-plan') !== plan) return false;
            if (q) {
                const hay = (row.getAttribute('data-search') || '').toLowerCase();
                if (!hay.includes(q)) return false;
            }
            return true;
        });
    };

    const renderPagination = (visibleCount) => {
        if (!pagination) return;
        const totalPages = Math.max(1, Math.ceil(visibleCount / pageSize));
        if (page > totalPages) page = totalPages;
        const showPager = visibleCount > 0;
        pagination.hidden = !showPager;
        pagination.classList.toggle('hidden', !showPager);

        const from = visibleCount === 0 ? 0 : (page - 1) * pageSize + 1;
        const to = Math.min(visibleCount, page * pageSize);
        if (pageStatus) {
            if (visibleCount === 0) {
                pageStatus.textContent = i18n.showing_zero || '';
            } else {
                const template = pagination.getAttribute('data-status-template') || i18n.showing_range || '';
                pageStatus.textContent = template
                    .replace(':from', String(from))
                    .replace(':to', String(to))
                    .replace(':total', String(visibleCount))
                    .replace(':label', labelForTab());
            }
        }

        if (prevBtn) prevBtn.disabled = page <= 1 || visibleCount === 0;
        if (nextBtn) nextBtn.disabled = page >= totalPages || visibleCount === 0;

        if (pageNumbers) {
            pageNumbers.innerHTML = '';
            if (visibleCount === 0) return;
            for (let i = 1; i <= totalPages; i += 1) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = String(i);
                btn.className =
                    i === page
                        ? 'inline-flex h-9 min-w-9 items-center justify-center rounded-lg bg-hb-blue px-2 text-sm font-semibold text-white'
                        : 'inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-hb-border bg-hb-surface px-2 text-sm font-semibold text-hb-navy hover:border-hb-blue/40';
                if (i === page) {
                    btn.setAttribute('aria-current', 'page');
                }
                btn.addEventListener('click', () => {
                    page = i;
                    render();
                });
                pageNumbers.appendChild(btn);
            }
        }
    };

    const render = () => {
        const matched = filteredRows();
        rows().forEach((row) => {
            row.hidden = true;
        });
        const start = (page - 1) * pageSize;
        matched.forEach((row, index) => {
            row.hidden = index < start || index >= start + pageSize;
        });
        if (emptyEl) {
            emptyEl.classList.toggle('hidden', matched.length > 0);
        }
        renderPagination(matched.length);
    };

    const resetAndRender = () => {
        page = 1;
        render();
    };

    qsa('[data-orgs-tab]', root).forEach((btn) => {
        btn.addEventListener('click', () => {
            setTabUi(btn.getAttribute('data-orgs-tab') || 'active');
            resetAndRender();
        });
    });

    qsa('[data-orgs-goto-tab]', root).forEach((btn) => {
        btn.addEventListener('click', () => {
            setTabUi(btn.getAttribute('data-orgs-goto-tab') || 'active');
            if (qInput) qInput.value = '';
            if (typeSelect) typeSelect.value = 'all';
            if (planSelect) planSelect.value = 'all';
            resetAndRender();
            qs(`[data-orgs-tab="${tab}"]`, root)?.focus();
        });
    });

    qInput?.addEventListener('input', resetAndRender);
    typeSelect?.addEventListener('change', resetAndRender);
    planSelect?.addEventListener('change', resetAndRender);
    prevBtn?.addEventListener('click', () => {
        if (page > 1) {
            page -= 1;
            render();
        }
    });
    nextBtn?.addEventListener('click', () => {
        page += 1;
        render();
    });

    setTabUi(tab);
    render();
}

function initAdminOrganizationShow() {
    const root = qs('[data-admin-org-show]');
    if (!root) return;

    const activateTab = (key) => {
        const tab = qs(`#admin-org-detail-tabs-tab-${key}`, root);
        if (!tab) return;
        tab.click();
        tab.focus();
    };

    qsa('[data-org-goto-tab]', root).forEach((btn) => {
        btn.addEventListener('click', () => {
            activateTab(btn.getAttribute('data-org-goto-tab') || 'overview');
        });
    });

    const reason = qs('[data-org-suspend-reason]', root);
    const ack = qs('[data-org-suspend-ack]', root);
    const reviewBtn = qs('[data-org-suspend-review]', root);
    const hint = qs('[data-org-suspend-hint]', root);
    const suspendModal = document.getElementById('admin-org-suspend-modal');

    const syncSuspend = () => {
        if (!reviewBtn) return;
        const ready = Boolean(reason?.value) && Boolean(ack?.checked);
        reviewBtn.disabled = !ready;
        if (hint && !ready) {
            hint.hidden = false;
        }
    };

    reason?.addEventListener('change', syncSuspend);
    ack?.addEventListener('change', syncSuspend);
    reviewBtn?.addEventListener('click', () => {
        if (reviewBtn.disabled || !suspendModal) return;
        if (typeof openModal === 'function') {
            openModal(suspendModal, reviewBtn);
        }
    });
    syncSuspend();
}

function initAdminUsers() {
    const root = qs('[data-admin-users]');
    if (!root) return;

    let i18n = {};
    try {
        i18n = JSON.parse(root.getAttribute('data-i18n') || '{}');
    } catch {
        i18n = {};
    }

    const pageSize = Math.max(1, Number(root.getAttribute('data-page-size') || 8));
    let tab = root.getAttribute('data-default-tab') || 'all';
    let page = 1;
    const qInput = qs('[data-users-q]', root);
    const roleSelect = qs('[data-users-role]', root);
    const orgSelect = qs('[data-users-org]', root);
    const sortSelect = qs('[data-users-sort]', root);
    const emptyEl = qs('[data-users-empty]', root);
    const pagination = qs('[data-users-pagination]', root);
    const pageStatus = qs('[data-users-page-status]', root);
    const pageNumbers = qs('[data-users-page-numbers]', root);
    const prevBtn = qs('[data-users-page-prev]', root);
    const nextBtn = qs('[data-users-page-next]', root);
    const tbody = qs('[data-users-tbody]', root);
    const previewModal = document.getElementById('admin-users-preview-modal');
    const previewDl = previewModal ? qs('[data-user-preview-dl]', previewModal) : null;
    const previewOrgLink = previewModal ? qs('[data-user-preview-org]', previewModal) : null;

    const initialQ = root.getAttribute('data-initial-q') || '';
    if (qInput && initialQ && !qInput.value) {
        qInput.value = initialQ;
    }

    const rows = () => qsa('[data-user-row]', root);

    const setTabUi = (nextTab) => {
        tab = nextTab;
        qsa('[data-users-tab]', root).forEach((btn) => {
            const active = btn.getAttribute('data-users-tab') === tab;
            btn.setAttribute('aria-selected', active ? 'true' : 'false');
            btn.classList.toggle('border-hb-blue', active);
            btn.classList.toggle('text-hb-blue', active);
            btn.classList.toggle('border-transparent', !active);
            btn.classList.toggle('text-hb-muted', !active);
        });
    };

    const labelForTab = () => {
        const key = `label_${tab}`;
        return i18n[key] || i18n.label_filtered || '';
    };

    const sortRows = (list) => {
        const mode = sortSelect?.value || 'name_asc';
        return [...list].sort((a, b) => {
            if (mode === 'name_desc') {
                return (b.getAttribute('data-sort-name') || '').localeCompare(a.getAttribute('data-sort-name') || '');
            }
            if (mode === 'active_asc') {
                return (a.getAttribute('data-sort-active') || '').localeCompare(b.getAttribute('data-sort-active') || '');
            }
            if (mode === 'active_desc') {
                return (b.getAttribute('data-sort-active') || '').localeCompare(a.getAttribute('data-sort-active') || '');
            }
            return (a.getAttribute('data-sort-name') || '').localeCompare(b.getAttribute('data-sort-name') || '');
        });
    };

    const filteredRows = () => {
        const q = (qInput?.value || '').trim().toLowerCase();
        const role = roleSelect?.value || 'all';
        const org = orgSelect?.value || 'all';
        const matched = rows().filter((row) => {
            if (tab !== 'all' && row.getAttribute('data-state') !== tab) return false;
            if (role !== 'all' && row.getAttribute('data-role') !== role) return false;
            if (org !== 'all' && row.getAttribute('data-org') !== org) return false;
            if (q) {
                const hay = (row.getAttribute('data-search') || '').toLowerCase();
                if (!hay.includes(q)) return false;
            }
            return true;
        });
        return sortRows(matched);
    };

    const renderPagination = (visibleCount) => {
        if (!pagination) return;
        const totalPages = Math.max(1, Math.ceil(visibleCount / pageSize));
        if (page > totalPages) page = totalPages;
        const showPager = visibleCount > 0;
        pagination.hidden = !showPager;
        pagination.classList.toggle('hidden', !showPager);

        const from = visibleCount === 0 ? 0 : (page - 1) * pageSize + 1;
        const to = Math.min(visibleCount, page * pageSize);
        if (pageStatus) {
            if (visibleCount === 0) {
                pageStatus.textContent = i18n.showing_zero || '';
            } else {
                const template = pagination.getAttribute('data-status-template') || i18n.showing_range || '';
                pageStatus.textContent = template
                    .replace(':from', String(from))
                    .replace(':to', String(to))
                    .replace(':total', String(visibleCount))
                    .replace(':label', labelForTab());
            }
        }

        if (prevBtn) prevBtn.disabled = page <= 1 || visibleCount === 0;
        if (nextBtn) nextBtn.disabled = page >= totalPages || visibleCount === 0;

        if (pageNumbers) {
            pageNumbers.innerHTML = '';
            if (visibleCount === 0) return;
            for (let i = 1; i <= totalPages; i += 1) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = String(i);
                btn.className =
                    i === page
                        ? 'inline-flex h-9 min-w-9 items-center justify-center rounded-lg bg-hb-blue px-2 text-sm font-semibold text-white'
                        : 'inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-hb-border bg-hb-surface px-2 text-sm font-semibold text-hb-navy hover:border-hb-blue/40';
                if (i === page) {
                    btn.setAttribute('aria-current', 'page');
                }
                btn.addEventListener('click', () => {
                    page = i;
                    render();
                });
                pageNumbers.appendChild(btn);
            }
        }
    };

    const render = () => {
        const matched = filteredRows();
        rows().forEach((row) => {
            row.hidden = true;
        });
        if (tbody) {
            matched.forEach((row) => tbody.appendChild(row));
        }
        const start = (page - 1) * pageSize;
        matched.forEach((row, index) => {
            row.hidden = index < start || index >= start + pageSize;
        });
        if (emptyEl) {
            emptyEl.classList.toggle('hidden', matched.length > 0);
        }
        renderPagination(matched.length);
    };

    const resetAndRender = () => {
        page = 1;
        render();
    };

    qsa('[data-users-tab]', root).forEach((btn) => {
        btn.addEventListener('click', () => {
            setTabUi(btn.getAttribute('data-users-tab') || 'all');
            resetAndRender();
        });
    });

    qs('[data-users-reset]', root)?.addEventListener('click', () => {
        if (qInput) qInput.value = '';
        if (roleSelect) roleSelect.value = 'all';
        if (orgSelect) orgSelect.value = 'all';
        if (sortSelect) sortSelect.value = 'name_asc';
        setTabUi('all');
        resetAndRender();
        qInput?.focus();
    });

    qInput?.addEventListener('input', resetAndRender);
    roleSelect?.addEventListener('change', resetAndRender);
    orgSelect?.addEventListener('change', resetAndRender);
    sortSelect?.addEventListener('change', resetAndRender);
    prevBtn?.addEventListener('click', () => {
        if (page > 1) {
            page -= 1;
            render();
        }
    });
    nextBtn?.addEventListener('click', () => {
        page += 1;
        render();
    });

    qsa('[data-user-preview]', root).forEach((btn) => {
        btn.addEventListener('click', () => {
            const row = btn.closest('[data-user-row]');
            if (!row || !previewModal || !previewDl) return;
            const fields = [
                [i18n.preview_email || 'Email', row.getAttribute('data-email') || ''],
                [i18n.preview_organization || 'Organization', row.getAttribute('data-org') || ''],
                [i18n.preview_role || 'Role', row.getAttribute('data-role-label') || ''],
                [i18n.preview_state || 'State', row.getAttribute('data-state-label') || ''],
                [i18n.preview_last_active || 'Last active', row.getAttribute('data-last-active') || ''],
            ];
            previewDl.innerHTML = fields
                .map(
                    ([label, value]) =>
                        `<div><dt class="text-xs font-semibold uppercase tracking-wide text-hb-muted">${label}</dt><dd class="mt-1 text-sm text-hb-navy" dir="auto">${value}</dd></div>`,
                )
                .join('');
            const title = qs('#admin-users-preview-modal-title', previewModal);
            if (title) {
                title.textContent = row.getAttribute('data-name') || title.textContent;
            }
            const orgHref = row.getAttribute('data-org-href') || '';
            if (previewOrgLink) {
                if (orgHref) {
                    previewOrgLink.href = orgHref;
                    previewOrgLink.classList.remove('hidden');
                } else {
                    previewOrgLink.classList.add('hidden');
                }
            }
            if (typeof openModal === 'function') {
                openModal(previewModal, btn);
            }
        });
    });

    setTabUi(tab);
    render();
}

function initAdminJobs() {
    const root = qs('[data-admin-jobs]');
    if (!root) return;

    let i18n = {};
    try {
        i18n = JSON.parse(root.getAttribute('data-i18n') || '{}');
    } catch {
        i18n = {};
    }

    const qInput = qs('[data-jobs-q]', root);
    const statusSelect = qs('[data-jobs-status]', root);
    const reasonSelect = qs('[data-jobs-reason]', root);
    const sortSelect = qs('[data-jobs-sort]', root);
    const emptyEl = qs('[data-jobs-empty]', root);
    const list = qs('[data-jobs-list]', root);
    const detailEmpty = qs('[data-jobs-detail-empty]', root);
    const detailBody = qs('[data-jobs-detail-body]', root);
    const noteInput = qs('[data-jobs-note]', root);
    const noteError = qs('[data-jobs-note-error]', root);
    const reviewModal = document.getElementById('admin-jobs-review-modal');
    const reviewBody = reviewModal ? qs('[data-jobs-review-body]', reviewModal) : null;

    const initialQ = root.getAttribute('data-initial-q') || '';
    if (qInput && initialQ && !qInput.value) {
        qInput.value = initialQ;
    }

    let selectedId = root.getAttribute('data-default-job') || '';

    const items = () => qsa('[data-job-item]', root);

    const policyTitle = (key) => {
        if (key === 'employer_verification' || key === 'verification') return i18n.verification || '';
        if (key === 'employment_type') return i18n.employment_type || '';
        return i18n.salary || '';
    };

    const filtered = () => {
        const q = (qInput?.value || '').trim().toLowerCase();
        const status = statusSelect?.value || 'pending';
        const reason = reasonSelect?.value || 'all';
        let matched = items().filter((item) => {
            if (status !== 'all' && item.getAttribute('data-status') !== status) return false;
            if (reason !== 'all' && item.getAttribute('data-reason') !== reason) return false;
            if (q) {
                const hay = (item.getAttribute('data-search') || '').toLowerCase();
                if (!hay.includes(q)) return false;
            }
            return true;
        });
        const mode = sortSelect?.value || 'newest';
        matched = [...matched].sort((a, b) => {
            if (mode === 'oldest') {
                return (a.getAttribute('data-sort-time') || '').localeCompare(b.getAttribute('data-sort-time') || '');
            }
            if (mode === 'code') {
                return (a.getAttribute('data-code') || '').localeCompare(b.getAttribute('data-code') || '');
            }
            return (b.getAttribute('data-sort-time') || '').localeCompare(a.getAttribute('data-sort-time') || '');
        });
        return matched;
    };

    const paintSelection = () => {
        items().forEach((item) => {
            const active = item.getAttribute('data-id') === selectedId && !item.hidden;
            item.setAttribute('aria-selected', active ? 'true' : 'false');
            item.classList.toggle('bg-hb-info-soft/50', active);
            item.classList.toggle('border-s-2', active);
            item.classList.toggle('border-hb-blue', active);
        });
    };

    const fillDetail = (item) => {
        if (!item || !detailBody || !detailEmpty) return;
        detailEmpty.classList.add('hidden');
        detailBody.classList.remove('hidden');
        const code = item.getAttribute('data-code') || '';
        const heading = qs('[data-jobs-detail-heading]', detailBody);
        if (heading) {
            heading.textContent = (i18n.review_heading || 'Review · :code').replace(':code', code);
        }
        const title = qs('[data-jobs-detail-title]', detailBody);
        if (title) title.textContent = item.getAttribute('data-title') || '';
        const orgLink = qs('[data-jobs-detail-org-link]', detailBody);
        if (orgLink) {
            orgLink.textContent = item.getAttribute('data-organization') || '';
            orgLink.href = item.getAttribute('data-org-href') || '#';
        }
        const location = qs('[data-jobs-detail-location]', detailBody);
        if (location) {
            location.textContent = ` · ${item.getAttribute('data-location') || ''}`;
        }
        const meta = qs('[data-jobs-detail-meta]', detailBody);
        if (meta) {
            meta.textContent = `${item.getAttribute('data-employment') || ''} · ${item.getAttribute('data-salary') || ''}`;
        }
        const submitted = qs('[data-jobs-detail-submitted]', detailBody);
        if (submitted) {
            submitted.textContent = (i18n.submitted_by || 'Submitted by :name · :when')
                .replace(':name', item.getAttribute('data-submitted-by') || '')
                .replace(':when', item.getAttribute('data-submitted-at') || '');
        }
        const policyTitleEl = qs('[data-jobs-detail-policy-title]', detailBody);
        if (policyTitleEl) {
            const prefix = i18n.policy_prefix || 'Policy:';
            policyTitleEl.textContent = `${prefix} ${policyTitle(item.getAttribute('data-policy-key') || '')}`;
        }
        const policyBody = qs('[data-jobs-detail-policy-body]', detailBody);
        if (policyBody) policyBody.textContent = item.getAttribute('data-policy-body') || '';
        if (noteInput) {
            noteInput.value = item.getAttribute('data-note') || '';
        }
        if (noteError) {
            noteError.textContent = '';
            noteError.classList.add('hidden');
        }
    };

    const clearDetail = () => {
        detailEmpty?.classList.remove('hidden');
        detailBody?.classList.add('hidden');
    };

    const render = () => {
        const matched = filtered();
        items().forEach((item) => {
            item.hidden = true;
            const row = item.closest('li');
            if (row) row.hidden = true;
        });
        if (list) {
            matched.forEach((item) => {
                const row = item.closest('li') || item;
                list.appendChild(row);
            });
        }
        matched.forEach((item) => {
            item.hidden = false;
            const row = item.closest('li');
            if (row) row.hidden = false;
        });
        if (emptyEl) {
            emptyEl.classList.toggle('hidden', matched.length > 0);
        }
        if (!matched.some((item) => item.getAttribute('data-id') === selectedId)) {
            selectedId = matched[0]?.getAttribute('data-id') || '';
        }
        const selected = matched.find((item) => item.getAttribute('data-id') === selectedId);
        paintSelection();
        if (selected) {
            fillDetail(selected);
        } else {
            clearDetail();
        }
    };

    items().forEach((item) => {
        item.addEventListener('click', () => {
            selectedId = item.getAttribute('data-id') || '';
            paintSelection();
            fillDetail(item);
        });
    });

    const openReview = (action, trigger) => {
        const note = (noteInput?.value || '').trim();
        if (!note) {
            if (noteError) {
                noteError.textContent = i18n.note_required || '';
                noteError.classList.remove('hidden');
            }
            noteInput?.focus();
            return;
        }
        if (noteError) {
            noteError.textContent = '';
            noteError.classList.add('hidden');
        }
        if (reviewBody) {
            reviewBody.textContent = i18n.review_modal_body || '';
        }
        if (reviewModal && typeof openModal === 'function') {
            openModal(reviewModal, trigger);
        }
    };

    qsa('[data-jobs-action]', root).forEach((btn) => {
        btn.addEventListener('click', () => openReview(btn.getAttribute('data-jobs-action') || '', btn));
    });

    qs('[data-jobs-reset]', root)?.addEventListener('click', () => {
        if (qInput) qInput.value = '';
        if (statusSelect) statusSelect.value = 'pending';
        if (reasonSelect) reasonSelect.value = 'all';
        if (sortSelect) sortSelect.value = 'newest';
        selectedId = root.getAttribute('data-default-job') || '';
        render();
        qInput?.focus();
    });

    qInput?.addEventListener('input', render);
    statusSelect?.addEventListener('change', render);
    reasonSelect?.addEventListener('change', render);
    sortSelect?.addEventListener('change', render);

    render();
}

function initAdminAgencies() {
    const root = qs('[data-admin-agencies]');
    if (!root) return;

    let i18n = {};
    try {
        i18n = JSON.parse(root.getAttribute('data-i18n') || '{}');
    } catch {
        i18n = {};
    }

    const qInput = qs('[data-agencies-q]', root);
    const statusSelect = qs('[data-agencies-status]', root);
    const agencySelect = qs('[data-agencies-agency]', root);
    const sortSelect = qs('[data-agencies-sort]', root);
    const emptyEl = qs('[data-agencies-empty]', root);
    const list = qs('[data-agencies-list]', root);
    const candidatesBody = qs('[data-agencies-candidates]', root);
    const heading = qs('[data-agencies-detail-heading]', root);
    const badges = qs('[data-agencies-detail-badges]', root);
    const scope = qs('[data-agencies-detail-scope]', root);
    const recruiterName = qs('[data-agencies-recruiter-name]', root);
    const ownerName = qs('[data-agencies-owner-name]', root);
    const agencyLink = qs('[data-agencies-agency-link]', root);
    const clientLink = qs('[data-agencies-client-link]', root);
    const statusLog = qs('[data-agencies-status-log]', root);

    const initialQ = root.getAttribute('data-initial-q') || '';
    if (qInput && initialQ && !qInput.value) {
        qInput.value = initialQ;
    }

    let selectedId = root.getAttribute('data-default-engagement') || '';

    const items = () => qsa('[data-engagement-item]', root);

    const parsePayload = (item) => {
        try {
            return JSON.parse(item.getAttribute('data-payload') || '{}');
        } catch {
            return {};
        }
    };

    const filtered = () => {
        const q = (qInput?.value || '').trim().toLowerCase();
        const status = statusSelect?.value || 'active';
        const agency = agencySelect?.value || 'all';
        let matched = items().filter((item) => {
            if (status !== 'all' && item.getAttribute('data-status') !== status) return false;
            if (agency !== 'all' && item.getAttribute('data-agency') !== agency) return false;
            if (q) {
                const hay = (item.getAttribute('data-search') || '').toLowerCase();
                if (!hay.includes(q)) return false;
            }
            return true;
        });
        const mode = sortSelect?.value || 'code_asc';
        matched = [...matched].sort((a, b) => {
            if (mode === 'code_desc') {
                return (b.getAttribute('data-sort-code') || '').localeCompare(a.getAttribute('data-sort-code') || '');
            }
            if (mode === 'client') {
                return (a.getAttribute('data-client') || '').localeCompare(b.getAttribute('data-client') || '');
            }
            return (a.getAttribute('data-sort-code') || '').localeCompare(b.getAttribute('data-sort-code') || '');
        });
        return matched;
    };

    const paintSelection = () => {
        items().forEach((item) => {
            const active = item.getAttribute('data-id') === selectedId && !item.hidden;
            item.setAttribute('aria-selected', active ? 'true' : 'false');
            item.classList.toggle('bg-hb-info-soft/50', active);
            item.classList.toggle('border-s-2', active);
            item.classList.toggle('border-hb-blue', active);
        });
    };

    const fillDetail = (item) => {
        if (!item) return;
        const data = parsePayload(item);
        if (heading) {
            heading.textContent = (i18n.selected || 'Selected · :code / :client')
                .replace(':code', data.code || '')
                .replace(':client', data.client || '');
        }
        badges?.classList.remove('hidden');
        if (scope) scope.textContent = data.scope || '';
        if (recruiterName) recruiterName.textContent = data.recruiter || '—';
        if (ownerName) ownerName.textContent = data.client_owner || '—';
        if (agencyLink) {
            agencyLink.textContent = data.agency || '—';
            agencyLink.href = item.getAttribute('data-agency-href') || '#';
        }
        if (clientLink) {
            clientLink.textContent = data.client || '—';
            clientLink.href = item.getAttribute('data-client-href') || '#';
        }
        if (statusLog) {
            statusLog.innerHTML = (data.status_log || [])
                .map((line) => `<li>${line}</li>`)
                .join('');
        }
        if (candidatesBody) {
            const rows = data.candidates || [];
            if (rows.length === 0) {
                candidatesBody.innerHTML = `<tr><td colspan="3" class="px-5 py-8 text-center text-hb-muted">${i18n.select_prompt || ''}</td></tr>`;
            } else {
                candidatesBody.innerHTML = rows
                    .map(
                        (row) =>
                            `<tr><td class="px-5 py-3 font-semibold text-hb-navy">${row.name || ''}</td><td class="px-4 py-3 text-hb-text">${row.stage || ''}</td><td class="px-5 py-3 text-hb-muted">${row.next || ''}</td></tr>`,
                    )
                    .join('');
            }
        }
    };

    const render = () => {
        const matched = filtered();
        items().forEach((item) => {
            item.hidden = true;
            const row = item.closest('li');
            if (row) row.hidden = true;
        });
        if (list) {
            matched.forEach((item) => {
                const row = item.closest('li') || item;
                list.appendChild(row);
            });
        }
        matched.forEach((item) => {
            item.hidden = false;
            const row = item.closest('li');
            if (row) row.hidden = false;
        });
        if (emptyEl) {
            emptyEl.classList.toggle('hidden', matched.length > 0);
        }
        if (!matched.some((item) => item.getAttribute('data-id') === selectedId)) {
            selectedId = matched[0]?.getAttribute('data-id') || '';
        }
        const selected = matched.find((item) => item.getAttribute('data-id') === selectedId);
        paintSelection();
        if (selected) {
            fillDetail(selected);
        }
    };

    items().forEach((item) => {
        item.addEventListener('click', () => {
            selectedId = item.getAttribute('data-id') || '';
            paintSelection();
            fillDetail(item);
        });
    });

    qs('[data-agencies-reset]', root)?.addEventListener('click', () => {
        if (qInput) qInput.value = '';
        if (statusSelect) statusSelect.value = 'active';
        if (agencySelect) agencySelect.value = 'all';
        if (sortSelect) sortSelect.value = 'code_asc';
        selectedId = root.getAttribute('data-default-engagement') || '';
        render();
        qInput?.focus();
    });

    qInput?.addEventListener('input', render);
    statusSelect?.addEventListener('change', render);
    agencySelect?.addEventListener('change', render);
    sortSelect?.addEventListener('change', render);

    render();
}

function initAdminSettings() {
    const root = qs('[data-admin-settings]');
    if (!root) return;

    let i18n = {};
    try {
        i18n = JSON.parse(root.getAttribute('data-i18n') || '{}');
    } catch {
        i18n = {};
    }

    const pageSize = Math.max(1, Number(root.getAttribute('data-page-size') || 8));
    let page = 1;
    const qInput = qs('[data-settings-q]', root);
    const rangeSelect = qs('[data-settings-range]', root);
    const resultSelect = qs('[data-settings-result]', root);
    const typeSelect = qs('[data-settings-type]', root);
    const sortSelect = qs('[data-settings-sort]', root);
    const emptyEl = qs('[data-settings-empty]', root);
    const pagination = qs('[data-settings-pagination]', root);
    const pageStatus = qs('[data-settings-page-status]', root);
    const pageNumbers = qs('[data-settings-page-numbers]', root);
    const prevBtn = qs('[data-settings-page-prev]', root);
    const nextBtn = qs('[data-settings-page-next]', root);
    const tbody = qs('[data-settings-tbody]', root);
    const eventModal = document.getElementById('admin-settings-event-modal');
    const eventDl = eventModal ? qs('[data-settings-event-dl]', eventModal) : null;
    const policyModal = document.getElementById('admin-settings-policy-modal');
    const roleModal = document.getElementById('admin-settings-role-modal');
    const integrationModal = document.getElementById('admin-settings-integration-modal');

    const initialQ = root.getAttribute('data-initial-q') || '';
    if (qInput && initialQ && !qInput.value) {
        qInput.value = initialQ;
    }
    if (rangeSelect) {
        rangeSelect.value = root.getAttribute('data-default-range') || 'oct19_20';
    }
    if (resultSelect) {
        resultSelect.value = root.getAttribute('data-default-result') || 'all';
    }
    if (sortSelect) {
        sortSelect.value = root.getAttribute('data-default-sort') || 'newest';
    }

    const rows = () => qsa('[data-audit-row]', root);

    const activateTab = (key) => {
        const tab = qs(`[data-settings-tab="${key}"]`, root);
        if (tab) {
            tab.click();
            tab.focus();
        }
    };

    const filteredRows = () => {
        const q = (qInput?.value || '').trim().toLowerCase();
        const range = rangeSelect?.value || 'all';
        const result = resultSelect?.value || 'all';
        const type = typeSelect?.value || 'all';
        let matched = rows().filter((row) => {
            if (range !== 'all' && row.getAttribute('data-range') !== range) return false;
            if (result !== 'all' && row.getAttribute('data-result') !== result) return false;
            if (type !== 'all' && row.getAttribute('data-type') !== type) return false;
            if (q) {
                const hay = (row.getAttribute('data-search') || '').toLowerCase();
                if (!hay.includes(q)) return false;
            }
            return true;
        });
        const mode = sortSelect?.value || 'newest';
        matched = [...matched].sort((a, b) => {
            const aTime = a.getAttribute('data-sort-time') || '';
            const bTime = b.getAttribute('data-sort-time') || '';
            return mode === 'oldest' ? aTime.localeCompare(bTime) : bTime.localeCompare(aTime);
        });
        return matched;
    };

    const renderPagination = (visibleCount) => {
        if (!pagination) return;
        const totalPages = Math.max(1, Math.ceil(visibleCount / pageSize));
        if (page > totalPages) page = totalPages;
        const showPager = visibleCount > 0;
        pagination.hidden = !showPager;
        pagination.classList.toggle('hidden', !showPager);

        const from = visibleCount === 0 ? 0 : (page - 1) * pageSize + 1;
        const to = Math.min(visibleCount, page * pageSize);
        if (pageStatus) {
            if (visibleCount === 0) {
                pageStatus.textContent = i18n.showing_zero || '';
            } else {
                const template = pagination.getAttribute('data-status-template') || i18n.showing_range || '';
                pageStatus.textContent = template
                    .replace(':from', String(from))
                    .replace(':to', String(to))
                    .replace(':total', String(visibleCount));
            }
        }

        if (prevBtn) prevBtn.disabled = page <= 1 || visibleCount === 0;
        if (nextBtn) nextBtn.disabled = page >= totalPages || visibleCount === 0;

        if (pageNumbers) {
            pageNumbers.innerHTML = '';
            if (visibleCount === 0) return;
            for (let i = 1; i <= totalPages; i += 1) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = String(i);
                btn.className =
                    i === page
                        ? 'inline-flex h-9 min-w-9 items-center justify-center rounded-lg bg-hb-blue px-2 text-sm font-semibold text-white'
                        : 'inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-hb-border bg-hb-surface px-2 text-sm font-semibold text-hb-navy hover:border-hb-blue/40';
                if (i === page) {
                    btn.setAttribute('aria-current', 'page');
                }
                btn.addEventListener('click', () => {
                    page = i;
                    render();
                });
                pageNumbers.appendChild(btn);
            }
        }
    };

    const render = () => {
        const matched = filteredRows();
        rows().forEach((row) => {
            row.hidden = true;
        });
        if (tbody) {
            matched.forEach((row) => tbody.appendChild(row));
        }
        const start = (page - 1) * pageSize;
        matched.forEach((row, index) => {
            row.hidden = index < start || index >= start + pageSize;
        });
        if (emptyEl) {
            emptyEl.classList.toggle('hidden', matched.length > 0);
        }
        renderPagination(matched.length);
    };

    const resetAndRender = () => {
        page = 1;
        render();
    };

    const openEvent = (row, trigger) => {
        if (!row || !eventModal || !eventDl) return;
        const fields = [
            [i18n.preview_id || 'Event ID', row.getAttribute('data-id') || ''],
            [i18n.preview_time || 'Time', row.getAttribute('data-time') || ''],
            [i18n.preview_actor || 'Actor', row.getAttribute('data-actor') || ''],
            [i18n.preview_action || 'Action', row.getAttribute('data-action') || ''],
            [i18n.preview_object || 'Affected object', row.getAttribute('data-object') || ''],
            [i18n.preview_result || 'Result', row.getAttribute('data-result-label') || ''],
            [i18n.preview_description || 'Context', row.getAttribute('data-description') || ''],
        ];
        eventDl.innerHTML = fields
            .map(
                ([label, value]) =>
                    `<div><dt class="text-xs font-semibold uppercase tracking-wide text-hb-muted">${label}</dt><dd class="mt-1 text-sm text-hb-navy" dir="auto">${value}</dd></div>`,
            )
            .join('');
        const title = qs('#admin-settings-event-modal-title', eventModal);
        if (title) {
            title.textContent = row.getAttribute('data-action') || title.textContent;
        }
        if (typeof openModal === 'function') {
            openModal(eventModal, trigger || row);
        }
    };

    qsa('[data-settings-goto-tab]', root).forEach((btn) => {
        btn.addEventListener('click', () => {
            activateTab(btn.getAttribute('data-settings-goto-tab') || 'audit');
        });
    });

    rows().forEach((row) => {
        row.addEventListener('click', () => openEvent(row, row));
        row.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openEvent(row, row);
            }
        });
    });

    qsa('[data-open-modal="admin-settings-policy-modal"]', root).forEach((btn) => {
        btn.addEventListener('click', () => {
            const title = policyModal ? qs('#admin-settings-policy-modal-title', policyModal) : null;
            if (title) {
                title.textContent = btn.getAttribute('data-policy-title') || title.textContent;
            }
        });
    });

    qsa('[data-open-modal="admin-settings-role-modal"]', root).forEach((btn) => {
        btn.addEventListener('click', () => {
            const body = roleModal ? qs('[data-settings-role-body]', roleModal) : null;
            const title = roleModal ? qs('#admin-settings-role-modal-title', roleModal) : null;
            if (title) {
                title.textContent = btn.getAttribute('data-role-name') || title.textContent;
            }
            if (body) {
                body.textContent = btn.getAttribute('data-role-notes') || '';
            }
        });
    });

    qsa('[data-open-modal="admin-settings-integration-modal"]', root).forEach((btn) => {
        btn.addEventListener('click', () => {
            const body = integrationModal ? qs('[data-settings-integration-body]', integrationModal) : null;
            const title = integrationModal ? qs('#admin-settings-integration-modal-title', integrationModal) : null;
            const name = btn.getAttribute('data-integration-name') || '';
            const provider = btn.getAttribute('data-integration-provider') || '';
            const status = btn.getAttribute('data-integration-status') || '';
            const statusLabel =
                status === 'connected' ? i18n.status_connected || 'Connected' : i18n.status_unavailable || 'Unavailable';
            if (title) {
                title.textContent = name || title.textContent;
            }
            if (body) {
                body.textContent = `${provider} · ${statusLabel}`;
            }
        });
    });

    qs('[data-settings-reset]', root)?.addEventListener('click', () => {
        if (qInput) qInput.value = '';
        if (rangeSelect) rangeSelect.value = root.getAttribute('data-default-range') || 'oct19_20';
        if (resultSelect) resultSelect.value = 'all';
        if (typeSelect) typeSelect.value = 'all';
        if (sortSelect) sortSelect.value = 'newest';
        resetAndRender();
        qInput?.focus();
    });

    qInput?.addEventListener('input', resetAndRender);
    rangeSelect?.addEventListener('change', resetAndRender);
    resultSelect?.addEventListener('change', resetAndRender);
    typeSelect?.addEventListener('change', resetAndRender);
    sortSelect?.addEventListener('change', resetAndRender);
    prevBtn?.addEventListener('click', () => {
        if (page > 1) {
            page -= 1;
            render();
        }
    });
    nextBtn?.addEventListener('click', () => {
        page += 1;
        render();
    });

    render();
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
    initPublicJobsDirectory();
    initCandidateJobs();
    initCandidateApply();
    initCandidateApplicationsList();
    initApplicationDetail();
    initCandidateInterviews();
    initCandidateMessages();
    initCandidateProfile();
    initAssessmentIntro();
    initAssessmentTake();
    initAssessmentResult();
    initCandidatePreferences();
    initCandidateOffer();
    initCandidateNotificationsPage();
    initNotificationsDropdownSync();
    initInterviewerDashboard();
    initInterviewerSchedule();
    initInterviewerPrepare();
    initInterviewerLive();
    initInterviewerScorecard();
    initAdminOrganizations();
    initAdminOrganizationShow();
    initAdminUsers();
    initAdminJobs();
    initAdminAgencies();
    initAdminSettings();
});




