@php
    $service = $service ?? null;
    $attributesText = collect($service?->attributes ?: [])->map(fn ($value, $key) => $key.': '.$value)->implode("\n");
    $availabilityText = collect($service?->availability_notes ?: [])->implode("\n");
@endphp

<x-dashboards.service-provider-shell :title="$service ? 'Modifier un service' : 'Ajouter un service'" subtitle="Structurez une offre claire, comparable et prête pour les demandes clients." active="services" :profile="$profile" :active-services-count="$activeServicesCount" :draft-services-count="$draftServicesCount" :requests-count="$requestsCount" :gross-revenue="$grossRevenue">
    <form method="POST" action="{{ $service ? route('service-provider.services.update', $service) : route('service-provider.services.store') }}" enctype="multipart/form-data" class="rounded-[28px] bg-white p-5 shadow-2xl shadow-[#173e7a]/10 ring-1 ring-[#dce6f7] sm:p-6">
        @csrf
        @if ($service)
            @method('PATCH')
        @endif

        <x-forms.validation-errors class="mb-5" />

        <div class="grid gap-4 md:grid-cols-2">
            <label class="block">
                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Type de service</span>
                <select name="event_service_type_id" required class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                    @foreach ($types as $type)
                        <option value="{{ $type->id }}" @selected((int) old('event_service_type_id', $service?->event_service_type_id) === (int) $type->id)>{{ $type->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block">
                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Nom commercial du service</span>
                <input name="name" value="{{ old('name', $service?->name) }}" required class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
            </label>
        </div>

        <label class="mt-4 block">
            <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Accroche courte</span>
            <input name="short_description" value="{{ old('short_description', $service?->short_description) }}" class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
        </label>

        <label class="mt-4 block">
            <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Description détaillée</span>
            <textarea name="description" rows="5" class="w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 py-3 text-sm font-bold leading-6 outline-none focus:border-[#2f6bff]">{{ old('description', $service?->description) }}</textarea>
        </label>

        <div class="mt-4 grid gap-4 md:grid-cols-[110px_1fr_1fr]">
            <label class="block">
                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Pays</span>
                <input name="country_code" value="{{ old('country_code', $service?->country_code ?? $profile->country_code ?? 'CI') }}" maxlength="2" required class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold uppercase outline-none focus:border-[#2f6bff]">
            </label>
            <label class="block">
                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Ville</span>
                <input name="city" value="{{ old('city', $service?->city ?? $profile->city) }}" required class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
            </label>
            <label class="block">
                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Commune</span>
                <input name="district" value="{{ old('district', $service?->district ?? $profile->district) }}" class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
            </label>
        </div>

        <div class="mt-4 grid gap-4 md:grid-cols-4">
            <label class="block md:col-span-2">
                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Zone d’intervention</span>
                <input name="service_area" value="{{ old('service_area', $service?->service_area ?? $profile->service_area) }}" class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
            </label>
            <label class="block">
                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Prix de départ</span>
                <input type="number" min="0" name="starting_price" value="{{ old('starting_price', $service?->starting_price ?? 0) }}" required class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
            </label>
            <label class="block">
                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Acompte souhaité</span>
                <input type="number" min="0" name="deposit_amount" value="{{ old('deposit_amount', $service?->deposit_amount) }}" class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
            </label>
        </div>

        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <label class="block">
                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Unité de prix</span>
                <select name="pricing_unit" class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                    @foreach (['event' => 'Par événement', 'day' => 'Par jour', 'hour' => 'Par heure'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('pricing_unit', $service?->pricing_unit ?? 'event') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block">
                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Statut</span>
                <select name="status" class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                    <option value="draft" @selected(old('status', $service?->status->value ?? 'draft') === 'draft')>Brouillon</option>
                    <option value="published" @selected(old('status', $service?->status->value) === 'published')>Publié</option>
                </select>
            </label>
        </div>

        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <label class="block">
                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Caractéristiques</span>
                <textarea name="attributes_text" rows="5" placeholder="Puissance: 5000W&#10;Technicien inclus: Oui" class="w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 py-3 text-sm font-bold leading-6 outline-none focus:border-[#2f6bff]">{{ old('attributes_text', $attributesText) }}</textarea>
            </label>
            <label class="block">
                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Disponibilités et conditions</span>
                <textarea name="availability_notes_text" rows="5" placeholder="Réservation 72h à l’avance&#10;Installation incluse à Abidjan" class="w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 py-3 text-sm font-bold leading-6 outline-none focus:border-[#2f6bff]">{{ old('availability_notes_text', $availabilityText) }}</textarea>
            </label>
        </div>

        <label class="mt-4 block cursor-pointer rounded-2xl border border-dashed border-[#b9caff] bg-[#f8fbff] p-4 transition hover:border-[#2f6bff]">
            <span class="block text-sm font-extrabold text-[#17233f]">Photos du service</span>
            <span class="mt-1 block text-xs font-bold leading-5 text-[#6f7890]">Jusqu’à 8 photos (JPG, PNG ou WebP, 8 Mo maximum chacune). La première devient la photo principale.</span>
            <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp" class="mt-3 block w-full text-sm font-bold text-[#52617b] file:mr-3 file:rounded-full file:border-0 file:bg-[#2f6bff] file:px-4 file:py-2 file:text-sm file:font-extrabold file:text-white">
        </label>

        <div class="mt-5 flex flex-wrap gap-2">
            <button class="rounded-2xl bg-[#2f6bff] px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/24">Enregistrer</button>
            <a href="{{ route('service-provider.services') }}" class="rounded-2xl border border-[#c9d8ef] px-5 py-3 text-sm font-extrabold text-[#2f6bff]">Retour</a>
        </div>
    </form>

    @if ($service && $service->media->isNotEmpty())
        <section class="mt-5 rounded-[28px] bg-white p-5 shadow-xl shadow-[#173e7a]/8 ring-1 ring-[#dce6f7]">
            <h2 class="text-lg font-extrabold text-[#07152f]">Photos publiées</h2>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($service->media as $media)
                    <div class="overflow-hidden rounded-2xl ring-1 ring-[#dce6f7]">
                        <img src="{{ $media->url }}" alt="{{ $media->alt_text ?? $service->name }}" referrerpolicy="no-referrer" loading="lazy" class="aspect-[4/3] w-full object-cover">
                        <div class="flex items-center justify-between gap-2 p-2">
                            <span class="text-[11px] font-extrabold text-[#2f6bff]">{{ $media->is_primary ? 'Principale' : 'Photo' }}</span>
                            <form method="POST" action="{{ route('service-provider.services.media.destroy', [$service, $media]) }}">
                                @csrf
                                @method('DELETE')
                                <button class="rounded-full bg-[#fff6f6] px-3 py-1 text-xs font-extrabold text-[#b42318] ring-1 ring-[#ffd0d0]">Retirer</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</x-dashboards.service-provider-shell>
