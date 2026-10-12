@props([
    'name' => 'accept_terms',
    'label' => 'J’accepte les',
    'form' => null,
])

<label {{ $attributes->merge(['class' => 'flex cursor-pointer items-start gap-3 rounded-2xl border border-[#dce6f7] bg-[#f8fbff] p-3.5 text-left']) }}>
    <input type="checkbox" name="{{ $name }}" value="1" required @if ($form) form="{{ $form }}" @endif @checked(old($name)) class="mt-0.5 size-5 shrink-0 rounded border-[#cbd8f4] accent-[#2f6bff]">
    <span class="text-xs font-semibold leading-5 text-[#52617b]">
        {{ $label }} <a href="{{ route('pages.terms') }}" target="_blank" class="font-extrabold text-[#2f6bff] underline underline-offset-2">conditions générales d’utilisation</a>
        et la <a href="{{ route('pages.privacy') }}" target="_blank" class="font-extrabold text-[#2f6bff] underline underline-offset-2">politique de confidentialité</a> de BAOBAA.
    </span>
</label>
@error($name)
    <p class="mt-1 text-xs font-bold text-[#b42318]">{{ $message }}</p>
@enderror
