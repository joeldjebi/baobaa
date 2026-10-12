@props([
    'clientType' => null,
    'organizationName' => null,
    'labelClass' => 'mb-2 block text-sm font-semibold text-[#17233f]',
    'inputClass' => 'h-12 w-full rounded-xl border border-[#c9d8ef] bg-[#f8fbff] px-4 text-sm font-medium outline-none focus:border-[#0d47a1] focus:bg-white',
])

@php
    $selectedType = old('client_type', $clientType instanceof \App\Enums\ClientType ? $clientType->value : ($clientType ?? \App\Enums\ClientType::Individual->value));
@endphp

<div {{ $attributes->merge(['class' => 'grid gap-4']) }} data-client-type-fields>
    <label class="block">
        <span class="{{ $labelClass }}">Vous êtes</span>
        <select name="client_type" required data-client-type-select class="{{ $inputClass }}">
            @foreach (\App\Enums\ClientType::cases() as $type)
                <option value="{{ $type->value }}" data-requires-organization="{{ $type->requiresOrganizationName() ? '1' : '0' }}" @selected($selectedType === $type->value)>{{ $type->label() }}</option>
            @endforeach
        </select>
        @error('client_type')<span class="mt-2 block text-sm font-medium text-red-700">{{ $message }}</span>@enderror
    </label>
    <label class="block" data-organization-field>
        <span class="{{ $labelClass }}">Nom de la structure</span>
        <input name="organization_name" value="{{ old('organization_name', $organizationName) }}" autocomplete="organization" placeholder="Entreprise, association, institution..." class="{{ $inputClass }}">
        @error('organization_name')<span class="mt-2 block text-sm font-medium text-red-700">{{ $message }}</span>@enderror
    </label>
</div>

@once
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-client-type-fields]').forEach((root) => {
                const select = root.querySelector('[data-client-type-select]');
                const organizationField = root.querySelector('[data-organization-field]');

                const sync = () => {
                    const requiresOrganization = select.selectedOptions[0]?.dataset.requiresOrganization === '1';
                    organizationField.classList.toggle('hidden', ! requiresOrganization);
                    organizationField.querySelector('input').required = requiresOrganization;
                };

                select.addEventListener('change', sync);
                sync();
            });
        });
    </script>
@endonce
