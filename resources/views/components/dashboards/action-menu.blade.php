@props([
    'label' => 'Gérer',
])

<details {{ $attributes->merge(['class' => 'baobaa-action-menu group relative ml-auto w-max text-left']) }}>
    <summary class="inline-flex cursor-pointer list-none items-center gap-2 rounded-full bg-[#07152f] px-3 py-2 text-xs font-extrabold text-white shadow-sm transition hover:bg-[#2f6bff] focus:outline-none focus:ring-4 focus:ring-[#2f6bff]/15 [&::-webkit-details-marker]:hidden">
        {{ $label }}
        <svg class="size-3.5 transition group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
        </svg>
    </summary>

    <div class="absolute right-0 top-[calc(100%+8px)] z-[80] w-56 rounded-2xl border border-[#dce6f7] bg-white p-2 text-xs font-extrabold shadow-2xl shadow-[#173e7a]/18 ring-1 ring-white/80">
        {{ $slot }}
    </div>
</details>

@once
    <script>
        document.addEventListener('click', (event) => {
            document.querySelectorAll('.baobaa-action-menu[open]').forEach((menu) => {
                if (! menu.contains(event.target)) {
                    menu.removeAttribute('open');
                }
            });
        });

        document.addEventListener('toggle', (event) => {
            if (! event.target.matches('.baobaa-action-menu') || ! event.target.open) {
                return;
            }

            document.querySelectorAll('.baobaa-action-menu[open]').forEach((menu) => {
                if (menu !== event.target) {
                    menu.removeAttribute('open');
                }
            });
        }, true);
    </script>
@endonce
