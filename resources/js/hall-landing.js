/* ---------- Public landing page category lookup ---------- */
(function initLandingCategories() {
    const root = document.querySelector('[data-landing-categories]');
    if (!root) return;

    const list = root.querySelector('[data-category-list]');
    const states = [...root.querySelectorAll('[data-category-state]')];
    const retry = root.querySelector('[data-category-retry]');

    function showState(state) {
        states.forEach((item) => {
            const active = item.dataset.categoryState === state;
            item.classList.toggle('hidden', !active);
            item.setAttribute('aria-hidden', active ? 'false' : 'true');
        });
    }

    async function loadCategories() {
        list.replaceChildren();
        showState('loading');

        try {
            const response = await fetch(root.dataset.categoriesUrl, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) throw new Error('Category lookup failed.');

            const payload = await response.json();
            if (!payload || !Array.isArray(payload.categories)) throw new Error('Invalid category response.');

            const categories = payload.categories.filter((category) => (
                category
                && (typeof category.id === 'number' || (typeof category.id === 'string' && category.id.trim() !== ''))
                && typeof category.name === 'string'
                && category.name.trim() !== ''
            ));

            if (!categories.length) {
                showState('empty');
                return;
            }

            categories.forEach((category) => {
                const url = new URL(root.dataset.jobsUrl, window.location.origin);
                url.searchParams.set('category_id', String(category.id));

                const link = document.createElement('a');
                link.href = `${url.pathname}${url.search}`;
                link.className = 'inline-flex min-h-11 items-center rounded-full border border-sky-200 bg-white px-5 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-sky-500 hover:text-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2';
                link.textContent = category.name;
                list.append(link);
            });

            showState('success');
        } catch (error) {
            showState('error');
        }
    }

    retry?.addEventListener('click', loadCategories);
    loadCategories();
})();
