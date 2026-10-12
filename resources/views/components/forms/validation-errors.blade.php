@if ($errors->any())
    <div {{ $attributes->merge(['class' => 'rounded-2xl border border-[#ffd0d0] bg-[#fff6f6] px-4 py-3 text-sm font-bold text-[#b42318]']) }} role="alert">
        <p class="font-extrabold">Merci de corriger les points suivants :</p>
        <ul class="mt-2 list-disc space-y-1 pl-5 font-semibold">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
