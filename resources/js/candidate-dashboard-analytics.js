/**
 * Candidate Overview analytics — period switcher, SVG bar/line + donut charts.
 * Data is injected via [data-candidate-analytics]; never invents series values.
 */

function cssVar(name, fallback) {
    const v = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    return v || fallback;
}

function themeColors() {
    return {
        accent: cssVar('--color-hb-blue', '#2563eb'),
        accentSoft: cssVar('--color-hb-info-soft', '#dbeafe'),
        muted: cssVar('--color-hb-muted', '#64748b'),
        navy: cssVar('--color-hb-navy', '#0f172a'),
        border: cssVar('--color-hb-border', '#e2e8f0'),
        page: cssVar('--color-hb-page', '#f8fafc'),
        teal: cssVar('--color-hb-teal', '#0d9488'),
        success: cssVar('--color-hb-success', '#16a34a'),
        pending: cssVar('--color-hb-pending', '#d97706'),
        error: cssVar('--color-hb-error', '#dc2626'),
    };
}

const PIPELINE_PALETTE = {
    submitted: '#2563eb',
    screening: '#0ea5e9',
    shortlisted: '#0d9488',
    interviewing: '#7c3aed',
    offered: '#ca8a04',
    hired: '#16a34a',
    rejected: '#dc2626',
    withdrawn: '#64748b',
    unknown: '#94a3b8',
};

function parsePayload(root) {
    const raw = root.getAttribute('data-candidate-analytics');
    if (!raw) return null;
    try {
        return JSON.parse(raw);
    } catch (e) {
        return null;
    }
}

function clear(el) {
    while (el.firstChild) el.removeChild(el.firstChild);
}

function el(tag, attrs = {}, children = []) {
    const node = document.createElementNS
        && tag === 'svg' || tag === 'path' || tag === 'circle' || tag === 'rect' || tag === 'line' || tag === 'text' || tag === 'g' || tag === 'polyline'
        ? document.createElementNS('http://www.w3.org/2000/svg', tag)
        : document.createElement(tag);
    Object.entries(attrs).forEach(([k, v]) => {
        if (v === null || v === undefined) return;
        if (k === 'textContent') node.textContent = v;
        else if (k === 'className') node.setAttribute('class', v);
        else node.setAttribute(k, String(v));
    });
    (Array.isArray(children) ? children : [children]).forEach((c) => {
        if (c == null) return;
        node.appendChild(typeof c === 'string' ? document.createTextNode(c) : c);
    });
    return node;
}

function svgEl(tag, attrs = {}, children = []) {
    const node = document.createElementNS('http://www.w3.org/2000/svg', tag);
    Object.entries(attrs).forEach(([k, v]) => {
        if (v === null || v === undefined) return;
        if (k === 'textContent') node.textContent = v;
        else node.setAttribute(k, String(v));
    });
    (Array.isArray(children) ? children : [children]).forEach((c) => {
        if (c == null) return;
        node.appendChild(typeof c === 'string' ? document.createTextNode(c) : c);
    });
    return node;
}

function formatDayLabel(iso, locale) {
    try {
        const d = new Date(iso + 'T00:00:00');
        return d.toLocaleDateString(locale || undefined, { month: 'short', day: 'numeric' });
    } catch (e) {
        return iso;
    }
}

function renderActivityChart(mount, series, labels, i18n, locale, rtl) {
    clear(mount);
    const colors = themeColors();
    const width = Math.max(mount.clientWidth || 640, 280);
    const height = 220;
    const pad = { t: 16, r: 12, b: 36, l: 36 };
    const plotW = width - pad.l - pad.r;
    const plotH = height - pad.t - pad.b;
    const max = Math.max(1, ...series);
    const n = series.length;
    const gap = n > 40 ? 1 : n > 20 ? 2 : 4;
    const barW = Math.max(2, (plotW - gap * (n - 1)) / n);

    const svg = svgEl('svg', {
        viewBox: `0 0 ${width} ${height}`,
        width: '100%',
        height: String(height),
        role: 'img',
        'aria-label': i18n.activityAria || 'Application activity chart',
        class: 'overflow-visible',
    });

    // grid lines
    for (let g = 0; g <= 3; g++) {
        const y = pad.t + (plotH * g) / 3;
        svg.appendChild(svgEl('line', {
            x1: pad.l, x2: width - pad.r, y1: y, y2: y,
            stroke: colors.border, 'stroke-width': '1', 'stroke-dasharray': '3 4',
        }));
        const val = Math.round(max - (max * g) / 3);
        svg.appendChild(svgEl('text', {
            x: rtl ? width - 8 : 8,
            y: y + 4,
            fill: colors.muted,
            'font-size': '10',
            'text-anchor': rtl ? 'end' : 'start',
            textContent: String(val),
        }));
    }

    const tooltip = el('div', {
        class: 'pointer-events-none absolute z-10 hidden rounded-lg border border-hb-border bg-hb-surface px-2.5 py-1.5 text-xs shadow-hb-sm',
        role: 'tooltip',
    });
    mount.style.position = 'relative';
    mount.appendChild(tooltip);

    series.forEach((v, i) => {
        const h = Math.max(v > 0 ? 3 : 0, (v / max) * plotH);
        const x = pad.l + i * (barW + gap);
        const y = pad.t + plotH - h;
        const rect = svgEl('rect', {
            x: String(x),
            y: String(y),
            width: String(barW),
            height: String(h || 0),
            rx: '2',
            fill: colors.accent,
            opacity: v > 0 ? '0.92' : '0.15',
            tabindex: '0',
            role: 'listitem',
            'aria-label': `${formatDayLabel(labels[i], locale)}: ${v}`,
            class: 'transition-opacity duration-150 hover:opacity-100 focus:outline-none',
        });

        const showTip = () => {
            tooltip.textContent = `${formatDayLabel(labels[i], locale)} · ${v}`;
            tooltip.classList.remove('hidden');
            const left = Math.min(Math.max(x, 8), width - 120);
            tooltip.style.left = `${left}px`;
            tooltip.style.top = `${Math.max(4, y - 28)}px`;
        };
        const hideTip = () => tooltip.classList.add('hidden');
        rect.addEventListener('mouseenter', showTip);
        rect.addEventListener('focus', showTip);
        rect.addEventListener('mouseleave', hideTip);
        rect.addEventListener('blur', hideTip);
        svg.appendChild(rect);
    });

    // x labels (sparse)
    const step = n <= 7 ? 1 : n <= 30 ? 5 : 15;
    for (let i = 0; i < n; i += step) {
        const x = pad.l + i * (barW + gap) + barW / 2;
        svg.appendChild(svgEl('text', {
            x: String(x),
            y: String(height - 10),
            fill: colors.muted,
            'font-size': '10',
            'text-anchor': 'middle',
            textContent: formatDayLabel(labels[i], locale),
        }));
    }

    // polyline overlay for trend
    if (n > 1) {
        const pts = series.map((v, i) => {
            const x = pad.l + i * (barW + gap) + barW / 2;
            const y = pad.t + plotH - (v / max) * plotH;
            return `${x},${y}`;
        }).join(' ');
        svg.appendChild(svgEl('polyline', {
            points: pts,
            fill: 'none',
            stroke: colors.teal,
            'stroke-width': '2',
            'stroke-linejoin': 'round',
            'stroke-linecap': 'round',
            opacity: '0.85',
        }));
    }

    mount.appendChild(svg);
}

function renderDonut(mount, segments, i18n) {
    clear(mount);
    const size = 200;
    const cx = 100;
    const cy = 100;
    const r = 70;
    const stroke = 22;
    const total = segments.reduce((s, seg) => s + Number(seg.count || 0), 0) || 1;
    const circ = 2 * Math.PI * r;

    const svg = svgEl('svg', {
        viewBox: `0 0 ${size} ${size}`,
        width: '100%',
        height: String(size),
        role: 'img',
        'aria-label': i18n.pipelineAria || 'Recruitment pipeline chart',
        class: 'mx-auto max-w-[220px]',
    });

    let offset = 0;
    segments.forEach((seg) => {
        const count = Number(seg.count || 0);
        if (count <= 0) return;
        const len = (count / total) * circ;
        const color = PIPELINE_PALETTE[seg.key] || PIPELINE_PALETTE.unknown;
        svg.appendChild(svgEl('circle', {
            cx: String(cx),
            cy: String(cy),
            r: String(r),
            fill: 'none',
            stroke: color,
            'stroke-width': String(stroke),
            'stroke-dasharray': `${len} ${circ - len}`,
            'stroke-dashoffset': String(-offset),
            transform: `rotate(-90 ${cx} ${cy})`,
            tabindex: '0',
            'aria-label': `${seg.label || seg.key}: ${count}`,
        }));
        offset += len;
    });

    svg.appendChild(svgEl('text', {
        x: String(cx),
        y: String(cy - 4),
        'text-anchor': 'middle',
        fill: themeColors().navy,
        'font-size': '22',
        'font-weight': '600',
        textContent: String(segments.reduce((s, seg) => s + Number(seg.count || 0), 0)),
    }));
    svg.appendChild(svgEl('text', {
        x: String(cx),
        y: String(cy + 16),
        'text-anchor': 'middle',
        fill: themeColors().muted,
        'font-size': '11',
        textContent: i18n.pipelineTotal || 'Applications',
    }));

    mount.appendChild(svg);
}

function animateProfileRing(root) {
    const circle = root.querySelector('[data-profile-ring-progress]');
    if (!circle) return;
    const percent = Number(root.getAttribute('data-percent') || 0);
    const radius = Number(circle.getAttribute('r') || 54);
    const circ = 2 * Math.PI * radius;
    circle.style.strokeDasharray = String(circ);
    circle.style.strokeDashoffset = String(circ);
    requestAnimationFrame(() => {
        circle.style.transition = 'stroke-dashoffset 900ms ease-out';
        circle.style.strokeDashoffset = String(circ - (Math.min(100, Math.max(0, percent)) / 100) * circ);
    });
}

function initCandidateAnalytics() {
    const root = document.querySelector('[data-candidate-analytics]');
    if (!root) return;

    const payload = parsePayload(root);
    if (!payload) return;

    const i18n = payload.i18n || {};
    const locale = payload.locale || document.documentElement.lang || 'en';
    const rtl = document.documentElement.dir === 'rtl';

    // Activity period switcher
    const activityMount = root.querySelector('[data-activity-chart]');
    const activity = payload.activity || {};
    let period = String(activity.default_period || 30);

    const setPeriod = (next) => {
        period = String(next);
        root.querySelectorAll('[data-activity-period]').forEach((btn) => {
            const active = btn.getAttribute('data-activity-period') === period;
            btn.setAttribute('aria-pressed', active ? 'true' : 'false');
            btn.classList.toggle('bg-hb-info-soft', active);
            btn.classList.toggle('text-hb-blue', active);
            btn.classList.toggle('text-hb-muted', !active);
        });

        const emptyEl = root.querySelector('[data-activity-empty]');
        const unavailableEl = root.querySelector('[data-activity-unavailable]');
        const chartWrap = root.querySelector('[data-activity-chart-wrap]');

        if (!activity.available) {
            if (unavailableEl) unavailableEl.classList.remove('hidden');
            if (emptyEl) emptyEl.classList.add('hidden');
            if (chartWrap) chartWrap.classList.add('hidden');
            return;
        }

        if (unavailableEl) unavailableEl.classList.add('hidden');
        const series = (activity.periods && activity.periods[period]) || null;
        if (!series || !Array.isArray(series.values)) {
            if (unavailableEl) unavailableEl.classList.remove('hidden');
            if (chartWrap) chartWrap.classList.add('hidden');
            if (emptyEl) emptyEl.classList.add('hidden');
            return;
        }

        const periodEmpty = Number(series.total || 0) === 0;
        if (periodEmpty) {
            if (emptyEl) emptyEl.classList.remove('hidden');
            if (chartWrap) chartWrap.classList.add('hidden');
            return;
        }

        if (emptyEl) emptyEl.classList.add('hidden');
        if (chartWrap) chartWrap.classList.remove('hidden');
        if (activityMount) {
            renderActivityChart(activityMount, series.values, series.labels, i18n, locale, rtl);
        }

        const summary = root.querySelector('[data-activity-summary]');
        if (summary) {
            summary.textContent = (i18n.activitySummary || ':count applications in :days days')
                .replace(':count', String(series.total))
                .replace(':days', period);
        }
    };

    root.querySelectorAll('[data-activity-period]').forEach((btn) => {
        btn.addEventListener('click', () => setPeriod(btn.getAttribute('data-activity-period')));
    });
    setPeriod(period);

    // Pipeline donut
    const pipeline = payload.pipeline || {};
    const donutMount = root.querySelector('[data-pipeline-donut]');
    const pipelineEmpty = root.querySelector('[data-pipeline-empty]');
    const pipelineUnavailable = root.querySelector('[data-pipeline-unavailable]');
    const pipelineChart = root.querySelector('[data-pipeline-chart-wrap]');

    if (!pipeline.available) {
        if (pipelineUnavailable) pipelineUnavailable.classList.remove('hidden');
        if (pipelineEmpty) pipelineEmpty.classList.add('hidden');
        if (pipelineChart) pipelineChart.classList.add('hidden');
    } else if (pipeline.empty) {
        if (pipelineEmpty) pipelineEmpty.classList.remove('hidden');
        if (pipelineUnavailable) pipelineUnavailable.classList.add('hidden');
        if (pipelineChart) pipelineChart.classList.add('hidden');
    } else if (donutMount) {
        if (pipelineEmpty) pipelineEmpty.classList.add('hidden');
        if (pipelineUnavailable) pipelineUnavailable.classList.add('hidden');
        if (pipelineChart) pipelineChart.classList.remove('hidden');
        const labeled = (pipeline.segments || []).map((seg) => ({
            ...seg,
            label: (payload.statusLabels && payload.statusLabels[seg.key]) || seg.key,
        }));
        renderDonut(donutMount, labeled, i18n);
    }

    // Profile ring animation
    const ring = root.querySelector('[data-profile-ring]');
    if (ring) animateProfileRing(ring);

    // Re-render charts on theme / resize
    let resizeTimer = null;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => setPeriod(period), 150);
    });

    document.querySelectorAll('[data-theme-option]').forEach((btn) => {
        btn.addEventListener('click', () => setTimeout(() => {
            setPeriod(period);
            if (pipeline.available && !pipeline.empty && donutMount) {
                const labeled = (pipeline.segments || []).map((seg) => ({
                    ...seg,
                    label: (payload.statusLabels && payload.statusLabels[seg.key]) || seg.key,
                }));
                renderDonut(donutMount, labeled, i18n);
            }
        }, 50));
    });
}

document.addEventListener('DOMContentLoaded', initCandidateAnalytics);

export { initCandidateAnalytics };
