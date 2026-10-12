@props([
    'title' => 'BAOBAA',
    'description' => 'Réservez des salles, jardins, rooftops et espaces événementiels vérifiés en Afrique francophone.',
    'image' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    <meta property="og:site_name" content="BAOBAA">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $image ?? asset('images/baobaa.jpg') }}">
    <meta name="twitter:card" content="summary_large_image">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>
            body { font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
            * { box-sizing: border-box; }
        </style>
    @endif
</head>
<body class="min-h-screen bg-[#f6f8fc] font-sans text-[#081225] antialiased">
    <div id="baobaa-global-loader" class="pointer-events-none fixed inset-0 z-[9999] hidden items-center justify-center bg-[#071225]/20 backdrop-blur-sm opacity-0 transition-opacity duration-200" aria-hidden="true">
        <div class="rounded-[24px] border border-white/70 bg-white px-5 py-4 shadow-2xl shadow-[#173e7a]/18">
            <div class="flex items-center gap-3">
                <span class="relative grid size-11 place-items-center rounded-2xl bg-[#eef4ff]">
                    <span class="absolute size-8 animate-ping rounded-full bg-[#2f6bff]/20"></span>
                    <span class="size-5 animate-spin rounded-full border-2 border-[#c7d8ff] border-t-[#2f6bff]"></span>
                </span>
                <span>
                    <span class="block text-sm font-extrabold text-[#151821]">Chargement BAOBAA</span>
                    <span class="block text-xs font-semibold text-[#6f7890]">Recherche des meilleurs espaces...</span>
                </span>
            </div>
        </div>
    </div>
    {{ $slot }}
</body>
</html>
