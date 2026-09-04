<x-dashboards.service-provider-shell title="Paramètres prestataire" subtitle="Gérez votre identité publique et vos informations de contact." active="settings" :profile="$profile" :active-services-count="$activeServicesCount" :draft-services-count="$draftServicesCount" :requests-count="$requestsCount" :gross-revenue="$grossRevenue">
    <form method="POST" action="{{ route('service-provider.settings.update') }}" enctype="multipart/form-data" class="rounded-[28px] bg-white p-5 shadow-2xl shadow-[#173e7a]/10 ring-1 ring-[#dce6f7] sm:p-6">
        @csrf
        @method('PATCH')

        <div class="grid gap-4 md:grid-cols-2">
            <label class="block">
                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Nom commercial</span>
                <input name="business_name" value="{{ old('business_name', $profile->business_name) }}" required class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
            </label>
            <label class="block">
                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Logo</span>
                <input type="file" name="logo" accept="image/*" class="block w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 py-3 text-sm font-bold outline-none file:mr-4 file:rounded-full file:border-0 file:bg-[#2f6bff] file:px-4 file:py-2 file:text-xs file:font-extrabold file:text-white">
            </label>
        </div>

        <div class="mt-4 grid gap-4 md:grid-cols-[110px_1fr_1fr]">
            <label class="block">
                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Pays</span>
                <input name="country_code" value="{{ old('country_code', $profile->country_code ?? 'CI') }}" maxlength="2" required class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold uppercase outline-none focus:border-[#2f6bff]">
            </label>
            <label class="block">
                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Ville</span>
                <input name="city" value="{{ old('city', $profile->city) }}" required class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
            </label>
            <label class="block">
                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Commune</span>
                <input name="district" value="{{ old('district', $profile->district) }}" class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
            </label>
        </div>

        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <label class="block">
                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Téléphone WhatsApp</span>
                <input name="whatsapp_phone" value="{{ old('whatsapp_phone', $profile->whatsapp_phone) }}" class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
            </label>
            <label class="block">
                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Zone d’intervention</span>
                <input name="service_area" value="{{ old('service_area', $profile->service_area) }}" class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
            </label>
        </div>

        <label class="mt-4 block">
            <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Présentation publique</span>
            <textarea name="description" rows="5" class="w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 py-3 text-sm font-bold leading-6 outline-none focus:border-[#2f6bff]">{{ old('description', $profile->description) }}</textarea>
        </label>

        <button class="mt-5 rounded-2xl bg-[#2f6bff] px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/24">Enregistrer</button>
    </form>
</x-dashboards.service-provider-shell>
