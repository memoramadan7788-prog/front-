@props(['scene'])

<div
    id="hallScene"
    class="relative w-screen overflow-hidden bg-ink"
    style="height: calc(100svh - var(--hall-nav, 0rem));"
>
    {{-- Full-screen scene; preserve hotspot coordinates --}}
    <div
        class="absolute inset-0 overflow-hidden"
        style="container-type: inline-size;"
    >
        @if (! empty($scene['image']) && file_exists(public_path($scene['image'])))
            <img
                src="{{ asset($scene['image']) }}"
                alt="{{ $scene['alt'] ?? '' }}"
                class="scene-img absolute inset-0 h-full w-full object-cover"
                fetchpriority="high"
            >
        @endif

        {{-- everything the page puts inside the scene fades out during the entrance animation --}}
        <div class="scene-ui contents">
            {{ $slot }}
        </div>

        @foreach ($scene['hotspots'] as $h)
            @php
                // play the zoom for everyone; guests land on login afterwards
                $animate = ($h['transition'] ?? null) === 'enter';
            @endphp
            <a
                href="{{ \App\Support\Hall::url($h['target']) }}"
                aria-label="{{ $h['label'] }}"
                @if ($animate)
                    data-enter
                    data-origin-x="{{ $h['x'] + $h['w'] / 2 }}"
                    data-origin-y="{{ $h['y'] + $h['h'] / 2 }}"
                @endif
                class="hotspot scene-ui group absolute focus:outline-none focus-visible:ring-4 focus-visible:ring-white"
                style="
                    z-index: 20;
                    left: {{ $h['x'] }}%;
                    top: {{ $h['y'] }}%;
                    width: {{ $h['w'] }}%;
                    height: {{ $h['h'] }}%;
                "
            >
                <span class="absolute inset-0 grid place-items-center">
                    <span class="rounded-full bg-black/50 px-5 py-2 text-base font-bold text-white backdrop-blur transition group-hover:bg-accent-600 md:text-xl">
                        {{ $h['label'] }}
                    </span>
                </span>
            </a>
        @endforeach

        <div class="scene-fade" aria-hidden="true"></div>
    </div>
</div>

@once
<script>
(function () {
    const scene = document.getElementById('hallScene');
    if (!scene) return;

    const DURATION = 2700; // ms: zoom (2.6s) + small buffer, then navigate

    // arrival effect on the next page
    try {
        if (sessionStorage.getItem('hallEntered')) {
            scene.classList.add('scene-arrive');
            sessionStorage.removeItem('hallEntered');
        }
    } catch (e) {}

    // coming back with the browser back button: reset the animation state
    window.addEventListener('pageshow', (e) => {
        if (e.persisted) scene.classList.remove('scene-entering');
    });

    scene.querySelectorAll('a[data-enter]').forEach((link) => {
        link.addEventListener('click', (e) => {
            // let ctrl/cmd/middle-click open normally
            if (e.metaKey || e.ctrlKey || e.shiftKey || e.button !== 0) return;
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

            e.preventDefault();
            if (scene.classList.contains('scene-entering')) return;

            scene.style.setProperty('--enter-x', link.dataset.originX + '%');
            scene.style.setProperty('--enter-y', link.dataset.originY + '%');
            scene.classList.add('scene-entering');

            try { sessionStorage.setItem('hallEntered', '1'); } catch (err) {}
            setTimeout(() => { window.location.href = link.href; }, DURATION);
        });
    });
})();
</script>
@endonce
