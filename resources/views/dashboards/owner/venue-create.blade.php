@php
    $steps = [
        'base' => 'Lieu et prix',
        'details' => 'Présentation',
        'inclusions' => 'Inclus et commodités',
        'modules' => 'Modules',
        'localisation' => 'Lieu et horaires',
        'conditions' => 'Règles et FAQ',
    ];
    $stepKeys = array_keys($steps);
    $currentStep = in_array($currentStep, $stepKeys, true) ? $currentStep : 'base';
    $currentIndex = array_search($currentStep, $stepKeys, true);
    $lineValue = fn ($value) => is_array($value) ? implode("\n", $value) : '';
    $paymentMethods = [
        'baobaa_checkout' => 'Paiement sécurisé BAOBAA',
        'wave' => 'Wave',
        'orange_money' => 'Orange Money',
        'mtn_money' => 'MTN Money',
        'moov_money' => 'Moov Money',
        'bank_transfer' => 'Virement bancaire',
    ];
@endphp

<x-dashboards.owner-shell title="Ajouter un espace" subtitle="Avancez étape par étape : chaque clic sur Continuer garde votre fiche en brouillon." active="venues" :owner-profile="$ownerProfile" :active-venues-count="$activeVenuesCount" :pending-bookings-count="$pendingBookingsCount" :confirmed-bookings-count="$confirmedBookingsCount" :gross-revenue="$grossRevenue" :active-subscription="$activeSubscription" :active-deposit-rule="$activeDepositRule" :billing-preference-label="$billingPreferenceLabel">
    <div id="owner-venue-draft-content">
        <div data-draft-feedback aria-live="polite">
            @if (session('draft_status'))
                <div class="mb-4 rounded-2xl border border-[#b9d3ff] bg-[#f2f7ff] px-4 py-3 text-sm font-extrabold text-[#2f6bff]">{{ session('draft_status') }}</div>
            @endif
        </div>

        <div class="mb-5 overflow-x-auto rounded-[24px] border border-white/80 bg-white p-3 shadow-sm ring-1 ring-[#dce6f7] baobaa-scrollbar-none">
        <div class="flex min-w-max gap-2">
            @foreach ($steps as $key => $label)
                @php
                    $stepUrl = $currentVenue
                        ? route('owner.venues.edit', ['venue' => $currentVenue, 'step' => $key])
                        : route('owner.venues.create', ['step' => $key]);
                @endphp
                <a href="{{ $stepUrl }}" class="rounded-2xl px-4 py-2.5 text-xs font-extrabold {{ $currentStep === $key ? 'bg-[#2f6bff] text-white shadow-lg shadow-[#2f6bff]/20' : 'bg-[#f7faff] text-[#52617b]' }}">
                    {{ $loop->iteration }}. {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1fr_340px]">
        <section class="rounded-[26px] border border-white/80 bg-white p-5 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7]">
            <form method="POST" action="{{ route('owner.venues.draft.store') }}" enctype="multipart/form-data" class="space-y-5" data-owner-venue-draft-form data-no-global-loader>
                @csrf
                <input type="hidden" name="step" value="{{ $currentStep }}">
                @if ($currentVenue)
                    <input type="hidden" name="venue_id" value="{{ $currentVenue->id }}">
                @endif

                @if ($errors->any())
                    <div class="rounded-2xl border border-[#ffd0d0] bg-[#fff6f6] px-4 py-3 text-sm font-bold text-[#b42318]">Certains champs doivent être complétés avant l’enregistrement.</div>
                @endif

                @switch($currentStep)
                    @case('base')
                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="md:col-span-2">
                                <span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Nom visible par les clients</span>
                                <input name="name" value="{{ old('name', $currentVenue?->name === 'Nouvel espace' ? '' : $currentVenue?->name) }}" placeholder="Ex : Salle prestige Cocody" class="mt-2 w-full rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
                            </label>
                            <label>
                                <span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Catégorie</span>
                                <select name="venue_category_id" class="mt-2 w-full rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected((int) old('venue_category_id', $currentVenue?->venue_category_id) === $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label>
                                <span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Ville</span>
                                <input name="city" value="{{ old('city', $currentVenue?->city ?? $ownerProfile->city) }}" placeholder="Abidjan" class="mt-2 w-full rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
                            </label>
                            <label>
                                <span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Quartier</span>
                                <input name="district" value="{{ old('district', $currentVenue?->district) }}" placeholder="Cocody, Plateau..." class="mt-2 w-full rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
                            </label>
                            <label>
                                <span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Réservation</span>
                                <select name="booking_mode" class="mt-2 w-full rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
                                    <option value="request" @selected(old('booking_mode', $currentVenue?->booking_mode) === 'request')>Validation par le propriétaire</option>
                                    <option value="instant" @selected(old('booking_mode', $currentVenue?->booking_mode) === 'instant')>Confirmation immédiate</option>
                                </select>
                            </label>
                            <label><span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Capacité minimale</span><input name="min_capacity" type="number" value="{{ old('min_capacity', $currentVenue?->min_capacity) }}" class="mt-2 w-full rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]"></label>
                            <label><span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Capacité maximale</span><input name="max_capacity" type="number" value="{{ old('max_capacity', $currentVenue?->max_capacity) }}" class="mt-2 w-full rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]"></label>
                            <label><span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Surface en m²</span><input name="surface_area" type="number" value="{{ old('surface_area', $currentVenue?->surface_area) }}" class="mt-2 w-full rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]"></label>
                            <label><span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Prix de départ</span><input name="starting_price" type="number" value="{{ old('starting_price', $currentVenue?->starting_price) }}" class="mt-2 w-full rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]"></label>
                            <div class="rounded-2xl border border-[#dce6f7] bg-white p-4">
                                <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Acompte client</p>
                                <p class="mt-2 text-sm font-bold leading-6 text-[#52617b]">Le montant payé avant confirmation est défini par le SAP pour votre compte partenaire.</p>
                            </div>
                            <div class="md:col-span-2 rounded-2xl bg-[#f7faff] p-4 ring-1 ring-[#dce6f7]">
                                <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                                    <div>
                                        <span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Moyens de paiement acceptés</span>
                                        <p class="mt-1 text-xs font-semibold leading-5 text-[#6f7890]">Le SAP définit le montant d’acompte. Ici, vous indiquez les canaux que le client pourra utiliser pour le paiement.</p>
                                    </div>
                                    <span class="rounded-full bg-white px-3 py-1 text-[11px] font-extrabold text-[#2f6bff] ring-1 ring-[#dce6f7]">Par espace</span>
                                </div>
                                <div class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                    @foreach ($paymentMethods as $value => $label)
                                        <label class="flex cursor-pointer items-center gap-3 rounded-2xl bg-white px-3 py-3 text-sm font-extrabold text-[#151821] ring-1 ring-[#e4ebf8] transition hover:ring-[#2f6bff]">
                                            <input type="checkbox" name="payment_methods[]" value="{{ $value }}" @checked(in_array($value, old('payment_methods', $currentVenue?->payment_methods ?: ['baobaa_checkout']), true)) class="size-4 rounded border-[#b9caff] text-[#2f6bff] focus:ring-[#2f6bff]">
                                            <span>{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @break

                    @case('details')
                        <label class="block"><span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Phrase d’accroche</span><input name="short_description" value="{{ old('short_description', $currentVenue?->short_description) }}" class="mt-2 w-full rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]"></label>
                        <label class="block"><span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Description complète</span><textarea name="description" rows="6" class="mt-2 w-full resize-none rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold leading-6 outline-none focus:border-[#2f6bff]">{{ old('description', $currentVenue?->description) }}</textarea></label>
                        <label class="block"><span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Points forts affichés sous les photos</span><textarea name="highlights" rows="4" placeholder="Un point fort par ligne" class="mt-2 w-full resize-none rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold leading-6 outline-none focus:border-[#2f6bff]">{{ old('highlights', $lineValue($currentVenue?->highlights)) }}</textarea></label>
                        <div class="rounded-[24px] border border-[#dce6f7] bg-[#f8fbff] p-4">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <h2 class="text-lg font-extrabold text-[#07152f]">Médias de l’espace</h2>
                                    <p class="mt-1 text-sm font-semibold leading-6 text-[#6f7890]">Ajoutez les photos et vidéos qui seront affichées publiquement avec des liens signés. Formats acceptés : JPG, PNG, WebP, MP4, MOV, WebM.</p>
                                </div>
                                <span class="rounded-full bg-white px-3 py-1 text-xs font-extrabold text-[#2f6bff] ring-1 ring-[#dce6f7]">Stockage privé Wasabi</span>
                            </div>

                            @if ($currentVenue?->media?->isNotEmpty())
                                <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                    @foreach ($currentVenue->media->sortBy('sort_order') as $media)
                                        <div data-media-card="{{ $media->id }}" class="overflow-hidden rounded-2xl bg-white ring-1 ring-[#dce6f7]">
                                            <div class="aspect-video bg-[#07152f]/5">
                                                @if ($media->type === 'video')
                                                    <video src="{{ $media->signed_url }}" controls preload="metadata" class="h-full w-full object-cover"></video>
                                                @else
                                                    <img src="{{ $media->signed_url }}" alt="{{ $media->alt_text ?? $currentVenue->name }}" referrerpolicy="no-referrer" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('images/baobaa.jpg') }}';" class="h-full w-full object-cover">
                                                @endif
                                            </div>
                                            <div class="flex items-center justify-between gap-3 p-3">
                                                <div class="min-w-0">
                                                    <span class="block text-xs font-extrabold uppercase text-[#6f7890]">{{ $media->type === 'video' ? 'Vidéo' : 'Image' }}</span>
                                                    <span class="mt-1 inline-flex rounded-full bg-[#eef4ff] px-2 py-1 text-[10px] font-extrabold text-[#2f6bff]">{{ $media->is_primary ? 'Principale' : 'Média' }}</span>
                                                </div>
                                                <button type="button" data-media-delete-url="{{ route('owner.venues.media.destroy', ['venue' => $currentVenue, 'venueMedia' => $media]) }}" data-media-delete-id="{{ $media->id }}" class="rounded-full bg-[#fff6f6] px-3 py-1.5 text-xs font-extrabold text-[#b42318] ring-1 ring-[#ffd0d0] transition hover:bg-[#ffecec]">Retirer</button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="mt-4 grid gap-3 md:grid-cols-2">
                                <label class="block cursor-pointer rounded-2xl border border-dashed border-[#b9caff] bg-white p-4 transition hover:border-[#2f6bff] hover:bg-[#f5f8ff]">
                                    <span class="block text-sm font-extrabold text-[#07152f]">Ajouter des images</span>
                                    <span class="mt-1 block text-xs font-bold leading-5 text-[#6f7890]">Jusqu’à 12 images, 8 Mo chacune.</span>
                                    <input type="file" name="media_images[]" multiple accept="image/jpeg,image/png,image/webp" data-media-preview-input="image" data-media-preview-target="venue-image-preview" class="mt-3 block w-full text-sm font-bold text-[#52617b] file:mr-3 file:rounded-full file:border-0 file:bg-[#2f6bff] file:px-4 file:py-2 file:text-sm file:font-extrabold file:text-white">
                                </label>
                                <label class="block cursor-pointer rounded-2xl border border-dashed border-[#b9caff] bg-white p-4 transition hover:border-[#2f6bff] hover:bg-[#f5f8ff]">
                                    <span class="block text-sm font-extrabold text-[#07152f]">Ajouter des vidéos</span>
                                    <span class="mt-1 block text-xs font-bold leading-5 text-[#6f7890]">Jusqu’à 4 vidéos, 100 Mo chacune.</span>
                                    <input type="file" name="media_videos[]" multiple accept="video/mp4,video/quicktime,video/webm" data-media-preview-input="video" data-media-preview-target="venue-video-preview" class="mt-3 block w-full text-sm font-bold text-[#52617b] file:mr-3 file:rounded-full file:border-0 file:bg-[#07152f] file:px-4 file:py-2 file:text-sm file:font-extrabold file:text-white">
                                </label>
                            </div>
                            <div class="mt-4 grid gap-3 md:grid-cols-2">
                                <div id="venue-image-preview" class="hidden rounded-2xl bg-white p-3 ring-1 ring-[#dce6f7]">
                                    <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Aperçu des nouvelles images</p>
                                    <div data-media-preview-list class="mt-3 grid gap-3 sm:grid-cols-2"></div>
                                </div>
                                <div id="venue-video-preview" class="hidden rounded-2xl bg-white p-3 ring-1 ring-[#dce6f7]">
                                    <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Aperçu des nouvelles vidéos</p>
                                    <div data-media-preview-list class="mt-3 grid gap-3"></div>
                                </div>
                            </div>
                        </div>
                        <div class="grid gap-3 md:grid-cols-3">
                            @foreach ([0, 1, 2] as $index)
                                @php $configuration = $currentVenue?->configurations?->values()->get($index); @endphp
                                <div class="rounded-2xl bg-[#f7faff] p-4 ring-1 ring-[#dce6f7]">
                                    <input name="configurations[{{ $index }}][name]" value="{{ old("configurations.$index.name", $configuration?->name) }}" placeholder="Configuration" class="w-full bg-transparent text-sm font-bold outline-none">
                                    <input name="configurations[{{ $index }}][capacity]" value="{{ old("configurations.$index.capacity", $configuration?->capacity) }}" type="number" placeholder="Capacité" class="mt-2 w-full bg-transparent text-sm font-bold outline-none">
                                </div>
                            @endforeach
                        </div>
                        @break

                    @case('inclusions')
                        <div>
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <h2 class="text-lg font-extrabold text-[#07152f]">Ce qui est inclus</h2>
                                    <p class="mt-1 text-sm font-semibold text-[#6f7890]">Ajoutez les éléments visibles dans le bloc “Ce qui est inclus” de la page détail.</p>
                                </div>
                                <button type="button" data-add-row="included-builder" class="rounded-full bg-[#eef4ff] px-4 py-2 text-xs font-extrabold text-[#2f6bff]">Ajouter</button>
                            </div>
                            <div id="included-builder" class="mt-4 grid gap-3">
                                @foreach (range(0, max(3, count($currentVenue?->included_items ?? []) - 1)) as $index)
                                    <div data-repeat-row class="rounded-2xl bg-[#f7faff] p-3 ring-1 ring-[#dce6f7]">
                                        <label>
                                            <span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Élément inclus</span>
                                            <div class="mt-2 grid gap-2 sm:grid-cols-[1fr_auto]">
                                                <input name="included_items[{{ $index }}]" value="{{ old("included_items.$index", $currentVenue?->included_items[$index] ?? '') }}" placeholder="Ex : Wi-Fi haut débit" class="w-full rounded-xl border border-[#dce6f7] bg-white px-3 py-2.5 text-sm font-bold outline-none focus:border-[#2f6bff]">
                                                <button type="button" data-remove-row class="rounded-xl bg-white px-3 py-2.5 text-xs font-extrabold text-[#b42318] ring-1 ring-[#ffd0d0]">Retirer</button>
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="border-t border-[#edf2fb] pt-5">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <h2 class="text-lg font-extrabold text-[#07152f]">Commodités détaillées</h2>
                                    <p class="mt-1 text-sm font-semibold text-[#6f7890]">Ajoutez les blocs déroulants visibles dans “Espace et commodités”.</p>
                                </div>
                                <button type="button" data-add-comfort-row class="rounded-full bg-[#eef4ff] px-4 py-2 text-xs font-extrabold text-[#2f6bff]">Ajouter</button>
                            </div>
                            <div id="comfort-builder" class="mt-4 grid gap-3">
                                @php $amenities = collect($currentVenue?->space_details['amenities'] ?? [])->values(); @endphp
                                @foreach (range(0, max(2, $amenities->count() - 1)) as $index)
                                    @php $amenity = $amenities->get($index); @endphp
                                    <div data-comfort-row class="grid gap-3 rounded-2xl bg-[#f7faff] p-3 ring-1 ring-[#dce6f7] md:grid-cols-[220px_1fr_auto]">
                                        <input name="amenities[{{ $index }}][name]" value="{{ old("amenities.$index.name", $amenity['name'] ?? '') }}" placeholder="Nom de la commodité" class="rounded-xl border border-[#dce6f7] bg-white px-3 py-2.5 text-sm font-bold outline-none focus:border-[#2f6bff]">
                                        <input name="amenities[{{ $index }}][detail]" value="{{ old("amenities.$index.detail", $amenity['detail'] ?? '') }}" placeholder="Détail affiché au client" class="rounded-xl border border-[#dce6f7] bg-white px-3 py-2.5 text-sm font-bold outline-none focus:border-[#2f6bff]">
                                        <button type="button" data-remove-row class="rounded-xl bg-white px-3 py-2.5 text-xs font-extrabold text-[#b42318] ring-1 ring-[#ffd0d0]">Retirer</button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @break

                    @case('modules')
                        <div>
                            <h2 class="text-lg font-extrabold text-[#07152f]">Sélectionner les modules proposés avec cet espace</h2>
                            <p class="mt-1 text-sm font-semibold text-[#6f7890]">Créez vos modules dans “Modules”, puis cochez ceux qui doivent être visibles sur cette fiche.</p>
                        </div>
                        <div class="grid gap-3 md:grid-cols-2">
                            @php
                                $selectedModuleNames = $currentVenue?->addOns?->pluck('name')->all() ?? [];
                                $oldModuleTemplateIds = collect(old('module_template_ids', []))->map(fn ($id) => (string) $id)->all();
                            @endphp
                            @forelse ($moduleTemplates as $module)
                                <label class="cursor-pointer rounded-2xl border border-[#dce6f7] bg-[#f7faff] p-4 transition has-[:checked]:border-[#2f6bff] has-[:checked]:bg-[#eef4ff]">
                                    <div class="flex items-start gap-3">
                                        <input type="checkbox" name="module_template_ids[]" value="{{ $module->id }}" class="mt-1 accent-[#2f6bff]" @checked(in_array($module->name, $selectedModuleNames, true) || in_array((string) $module->id, $oldModuleTemplateIds, true))>
                                        <span>
                                            <span class="block text-sm font-extrabold text-[#151821]">{{ $module->name }}</span>
                                            <span class="mt-1 block text-xs font-bold leading-5 text-[#6f7890]">{{ number_format($module->price, 0, ',', ' ') }} {{ $module->currency }} · {{ $module->description ?? 'Option disponible' }}</span>
                                        </span>
                                    </div>
                                </label>
                            @empty
                                <div class="rounded-2xl border border-dashed border-[#cbd8f4] bg-[#fbfcff] p-5 text-sm font-semibold text-[#6f7890]">
                                    Aucun module dans votre bibliothèque. Ajoutez-en d’abord dans la page Modules.
                                </div>
                            @endforelse
                        </div>
                        <a href="{{ route('owner.addons') }}" class="inline-flex rounded-2xl border border-[#b9caff] bg-[#eef4ff] px-5 py-3 text-sm font-extrabold text-[#2f6bff]">Gérer ma bibliothèque de modules</a>
                        @break

                    @case('localisation')
                        <label class="block"><span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Adresse complète</span><input name="address" value="{{ old('address', $currentVenue?->address) }}" class="mt-2 w-full rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]"></label>
                        <label class="block"><span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Message affiché aux clients</span><textarea name="location_details" rows="4" class="mt-2 w-full resize-none rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold leading-6 outline-none focus:border-[#2f6bff]">{{ old('location_details', $currentVenue?->location_details['public_note'] ?? '') }}</textarea></label>
                        <div class="grid gap-3 md:grid-cols-3">
                            <label><span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Date disponible</span><input name="available_date" type="date" class="mt-2 w-full rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]"></label>
                            <label><span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Début</span><input name="starts_at" type="time" class="mt-2 w-full rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]"></label>
                            <label><span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Fin</span><input name="ends_at" type="time" class="mt-2 w-full rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]"></label>
                        </div>
                        @break

                    @case('conditions')
                        <div>
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <h2 class="text-lg font-extrabold text-[#07152f]">Règles de la maison</h2>
                                    <p class="mt-1 text-sm font-semibold text-[#6f7890]">Ajoutez des règles claires, une par champ.</p>
                                </div>
                                <button type="button" data-add-row="rules-builder" class="rounded-full bg-[#eef4ff] px-4 py-2 text-xs font-extrabold text-[#2f6bff]">Ajouter</button>
                            </div>
                            <div id="rules-builder" class="mt-4 grid gap-3">
                                @foreach (range(0, max(3, count($currentVenue?->house_rules ?? []) - 1)) as $index)
                                    <div data-repeat-row class="rounded-2xl bg-[#f7faff] p-3 ring-1 ring-[#dce6f7]">
                                        <label>
                                            <span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Règle affichée au client</span>
                                            <div class="mt-2 grid gap-2 sm:grid-cols-[1fr_auto]">
                                                <input name="house_rules[{{ $index }}]" value="{{ old("house_rules.$index", $currentVenue?->house_rules[$index] ?? '') }}" placeholder="Ex : Musique amplifiée autorisée jusqu’à 23h" class="w-full rounded-xl border border-[#dce6f7] bg-white px-3 py-2.5 text-sm font-bold outline-none focus:border-[#2f6bff]">
                                                <button type="button" data-remove-row class="rounded-xl bg-white px-3 py-2.5 text-xs font-extrabold text-[#b42318] ring-1 ring-[#ffd0d0]">Retirer</button>
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="border-t border-[#edf2fb] pt-5">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <h2 class="text-lg font-extrabold text-[#07152f]">Politiques importantes</h2>
                                    <p class="mt-1 text-sm font-semibold text-[#6f7890]">Annulation, acompte, restauration, bruit ou accès : chaque politique reste indépendante.</p>
                                </div>
                                <button type="button" data-add-policy-row class="rounded-full bg-[#eef4ff] px-4 py-2 text-xs font-extrabold text-[#2f6bff]">Ajouter</button>
                            </div>
                            <div id="policies-builder" class="mt-4 grid gap-3">
                                @php $policies = $currentVenue?->policies?->values() ?? collect(); @endphp
                                @foreach (range(0, max(2, $policies->count() - 1)) as $index)
                                    @php $policy = $policies->get($index); @endphp
                                    <div data-policy-row class="grid gap-3 rounded-2xl bg-[#f7faff] p-4 ring-1 ring-[#dce6f7] lg:grid-cols-[210px_1fr_auto]">
                                        <input name="policies[{{ $index }}][title]" value="{{ old("policies.$index.title", $policy?->title) }}" placeholder="Titre de la politique" class="rounded-xl border border-[#dce6f7] bg-white px-3 py-2.5 text-sm font-bold outline-none focus:border-[#2f6bff]">
                                        <input name="policies[{{ $index }}][summary]" value="{{ old("policies.$index.summary", $policy?->summary) }}" placeholder="Résumé affiché dans la carte" class="rounded-xl border border-[#dce6f7] bg-white px-3 py-2.5 text-sm font-bold outline-none focus:border-[#2f6bff]">
                                        <button type="button" data-remove-row class="rounded-xl bg-white px-3 py-2.5 text-xs font-extrabold text-[#b42318] ring-1 ring-[#ffd0d0]">Retirer</button>
                                        <textarea name="policies[{{ $index }}][content]" rows="2" placeholder="Détail complet visible après ouverture" class="rounded-xl border border-[#dce6f7] bg-white px-3 py-2.5 text-sm font-bold outline-none focus:border-[#2f6bff] lg:col-span-3">{{ old("policies.$index.content", $policy?->content) }}</textarea>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="border-t border-[#edf2fb] pt-5">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <h2 class="text-lg font-extrabold text-[#07152f]">Questions fréquentes</h2>
                                    <p class="mt-1 text-sm font-semibold text-[#6f7890]">Ajoutez les réponses qui évitent les échanges répétitifs avant réservation.</p>
                                </div>
                                <button type="button" data-add-faq-row class="rounded-full bg-[#eef4ff] px-4 py-2 text-xs font-extrabold text-[#2f6bff]">Ajouter</button>
                            </div>
                            <div id="faqs-builder" class="mt-4 grid gap-3 md:grid-cols-2">
                                @php $faqs = $currentVenue?->faqs?->values() ?? collect(); @endphp
                                @foreach (range(0, max(1, $faqs->count() - 1)) as $index)
                                    @php $faq = $faqs->get($index); @endphp
                                    <div data-faq-row class="rounded-2xl bg-[#f7faff] p-4 ring-1 ring-[#dce6f7]">
                                        <div class="grid gap-2 sm:grid-cols-[1fr_auto]">
                                            <input name="faqs[{{ $index }}][question]" value="{{ old("faqs.$index.question", $faq?->question) }}" placeholder="Question fréquente" class="w-full rounded-xl border border-[#dce6f7] bg-white px-3 py-2.5 text-sm font-bold outline-none focus:border-[#2f6bff]">
                                            <button type="button" data-remove-row class="rounded-xl bg-white px-3 py-2.5 text-xs font-extrabold text-[#b42318] ring-1 ring-[#ffd0d0]">Retirer</button>
                                        </div>
                                        <textarea name="faqs[{{ $index }}][answer]" rows="2" placeholder="Réponse visible par les clients" class="mt-3 w-full resize-none rounded-xl border border-[#dce6f7] bg-white px-3 py-2.5 text-sm font-bold outline-none focus:border-[#2f6bff]">{{ old("faqs.$index.answer", $faq?->answer) }}</textarea>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @break
                @endswitch

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-[#edf2fb] pt-5">
                    <p class="text-xs font-bold text-[#6f7890]">Étape {{ $currentIndex + 1 }} sur {{ count($steps) }} · votre fiche reste en brouillon</p>
                    <button type="submit" data-draft-submit class="inline-flex items-center gap-2 rounded-2xl bg-[#2f6bff] px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/20 transition hover:bg-[#2258df] disabled:cursor-wait disabled:opacity-90">
                        <span data-draft-submit-label>{{ $currentStep === 'conditions' ? 'Enregistrer' : 'Continuer' }}</span>
                    </button>
                </div>
            </form>
        </section>

        <aside class="space-y-4">
            <div class="rounded-[26px] border border-white/80 bg-white p-5 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7]">
                <p class="text-sm font-extrabold text-[#07152f]">Progression de la fiche</p>
                <div class="mt-4 h-2 overflow-hidden rounded-full bg-[#e8effc]">
                    <span class="block h-full rounded-full bg-[#2f6bff]" style="width: {{ (($currentIndex + 1) / count($steps)) * 100 }}%"></span>
                </div>
                <p class="mt-3 text-xs font-semibold leading-5 text-[#6f7890]">La fiche reste invisible aux clients jusqu’à sa validation.</p>
            </div>
            <div class="rounded-[26px] border border-white/80 bg-[#07152f] p-5 text-white shadow-xl shadow-[#07152f]/12">
                <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[#8dc1ff]">À afficher côté client</p>
                <p class="mt-3 text-sm font-semibold leading-6 text-white/72">Photos, aperçu, inclusions, commodités, modules, localisation, disponibilités, règles, FAQ et avis composeront la page détail publique.</p>
            </div>
        </aside>
    </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const feedbackHtml = (message, type = 'success') => {
                const style = type === 'error'
                    ? 'border-[#ffd0d0] bg-[#fff6f6] text-[#b42318]'
                    : 'border-[#b9d3ff] bg-[#f2f7ff] text-[#2f6bff]';

                return `<div class="mb-4 rounded-2xl border px-4 py-3 text-sm font-extrabold ${style}">${message}</div>`;
            };

            const showFeedback = (message, type = 'success') => {
                const feedback = document.querySelector('[data-draft-feedback]');

                if (feedback) {
                    feedback.innerHTML = feedbackHtml(message, type);
                }
            };

            const updateSubmitState = (form, isSaving) => {
                const submitButton = form.querySelector('[data-draft-submit]');
                const submitLabel = form.querySelector('[data-draft-submit-label]');

                submitButton?.toggleAttribute('disabled', isSaving);
                form.toggleAttribute('aria-busy', isSaving);

                if (submitLabel) {
                    submitLabel.textContent = isSaving ? 'Enregistrement...' : submitLabel.dataset.defaultLabel;
                }
            };

            document.querySelectorAll('[data-draft-submit-label]').forEach((label) => {
                label.dataset.defaultLabel = label.textContent.trim();
            });

            document.addEventListener('click', (event) => {
                const button = event.target.closest('[data-add-row]');

                if (button) {
                    const builder = document.getElementById(button.dataset.addRow);

                    if (!builder) {
                        return;
                    }

                    const index = builder.querySelectorAll('[data-repeat-row]').length;
                    const inputName = button.dataset.addRow === 'rules-builder' ? `house_rules[${index}]` : `included_items[${index}]`;
                    const label = button.dataset.addRow === 'rules-builder' ? 'Règle affichée au client' : 'Élément inclus';
                    const placeholder = button.dataset.addRow === 'rules-builder' ? 'Ex : Installation autorisée 2h avant' : 'Ex : Parking sécurisé';
                    const row = document.createElement('div');
                    row.dataset.repeatRow = 'true';
                    row.className = 'rounded-2xl bg-[#f7faff] p-3 ring-1 ring-[#dce6f7]';
                    row.innerHTML = `
                        <label>
                            <span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">${label}</span>
                            <div class="mt-2 grid gap-2 sm:grid-cols-[1fr_auto]">
                                <input name="${inputName}" placeholder="${placeholder}" class="w-full rounded-xl border border-[#dce6f7] bg-white px-3 py-2.5 text-sm font-bold outline-none focus:border-[#2f6bff]">
                                <button type="button" data-remove-row class="rounded-xl bg-white px-3 py-2.5 text-xs font-extrabold text-[#b42318] ring-1 ring-[#ffd0d0]">Retirer</button>
                            </div>
                        </label>
                    `;
                    builder.appendChild(row);
                    return;
                }

                if (!event.target.closest('[data-add-comfort-row]')) {
                    return;
                }

                const builder = document.getElementById('comfort-builder');

                if (!builder) {
                    return;
                }

                const index = builder.querySelectorAll('[data-comfort-row]').length;
                const row = document.createElement('div');
                row.dataset.comfortRow = 'true';
                row.className = 'grid gap-3 rounded-2xl bg-[#f7faff] p-3 ring-1 ring-[#dce6f7] md:grid-cols-[220px_1fr_auto]';
                row.innerHTML = `
                    <input name="amenities[${index}][name]" placeholder="Nom de la commodité" class="rounded-xl border border-[#dce6f7] bg-white px-3 py-2.5 text-sm font-bold outline-none focus:border-[#2f6bff]">
                    <input name="amenities[${index}][detail]" placeholder="Détail affiché au client" class="rounded-xl border border-[#dce6f7] bg-white px-3 py-2.5 text-sm font-bold outline-none focus:border-[#2f6bff]">
                    <button type="button" data-remove-row class="rounded-xl bg-white px-3 py-2.5 text-xs font-extrabold text-[#b42318] ring-1 ring-[#ffd0d0]">Retirer</button>
                `;
                builder.appendChild(row);
            });

            document.addEventListener('click', (event) => {
                if (!event.target.closest('[data-add-policy-row]')) {
                    return;
                }

                const builder = document.getElementById('policies-builder');

                if (!builder) {
                    return;
                }

                const index = builder.querySelectorAll('[data-policy-row]').length;
                const row = document.createElement('div');
                row.dataset.policyRow = 'true';
                row.className = 'grid gap-3 rounded-2xl bg-[#f7faff] p-4 ring-1 ring-[#dce6f7] lg:grid-cols-[210px_1fr_auto]';
                row.innerHTML = `
                    <input name="policies[${index}][title]" placeholder="Titre de la politique" class="rounded-xl border border-[#dce6f7] bg-white px-3 py-2.5 text-sm font-bold outline-none focus:border-[#2f6bff]">
                    <input name="policies[${index}][summary]" placeholder="Résumé affiché dans la carte" class="rounded-xl border border-[#dce6f7] bg-white px-3 py-2.5 text-sm font-bold outline-none focus:border-[#2f6bff]">
                    <button type="button" data-remove-row class="rounded-xl bg-white px-3 py-2.5 text-xs font-extrabold text-[#b42318] ring-1 ring-[#ffd0d0]">Retirer</button>
                    <textarea name="policies[${index}][content]" rows="2" placeholder="Détail complet visible après ouverture" class="rounded-xl border border-[#dce6f7] bg-white px-3 py-2.5 text-sm font-bold outline-none focus:border-[#2f6bff] lg:col-span-3"></textarea>
                `;
                builder.appendChild(row);
            });

            document.addEventListener('click', (event) => {
                if (!event.target.closest('[data-add-faq-row]')) {
                    return;
                }

                const builder = document.getElementById('faqs-builder');

                if (!builder) {
                    return;
                }

                const index = builder.querySelectorAll('[data-faq-row]').length;
                const row = document.createElement('div');
                row.dataset.faqRow = 'true';
                row.className = 'rounded-2xl bg-[#f7faff] p-4 ring-1 ring-[#dce6f7]';
                row.innerHTML = `
                    <div class="grid gap-2 sm:grid-cols-[1fr_auto]">
                        <input name="faqs[${index}][question]" placeholder="Question fréquente" class="w-full rounded-xl border border-[#dce6f7] bg-white px-3 py-2.5 text-sm font-bold outline-none focus:border-[#2f6bff]">
                        <button type="button" data-remove-row class="rounded-xl bg-white px-3 py-2.5 text-xs font-extrabold text-[#b42318] ring-1 ring-[#ffd0d0]">Retirer</button>
                    </div>
                    <textarea name="faqs[${index}][answer]" rows="2" placeholder="Réponse visible par les clients" class="mt-3 w-full resize-none rounded-xl border border-[#dce6f7] bg-white px-3 py-2.5 text-sm font-bold outline-none focus:border-[#2f6bff]"></textarea>
                `;
                builder.appendChild(row);
            });

            document.addEventListener('click', (event) => {
                const mediaDeleteButton = event.target.closest('[data-media-delete-url]');

                if (mediaDeleteButton) {
                    if (!confirm('Retirer ce média de la fiche ?')) {
                        return;
                    }

                    mediaDeleteButton.setAttribute('disabled', 'disabled');

                    fetch(mediaDeleteButton.dataset.mediaDeleteUrl, {
                        method: 'DELETE',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    })
                        .then(async (response) => {
                            const contentType = response.headers.get('content-type') || '';
                            const payload = contentType.includes('application/json')
                                ? await response.json()
                                : { message: await response.text() };

                            if (!response.ok) {
                                throw new Error(payload.message || 'Impossible de retirer ce média.');
                            }

                            document.querySelector(`[data-media-card="${mediaDeleteButton.dataset.mediaDeleteId}"]`)?.remove();
                            showFeedback(payload.message || 'Média retiré avec succès.');
                        })
                        .catch((error) => {
                            showFeedback(error.message || 'Impossible de retirer ce média.', 'error');
                        })
                        .finally(() => {
                            mediaDeleteButton.removeAttribute('disabled');
                        });

                    return;
                }

                const button = event.target.closest('[data-remove-row]');

                if (!button) {
                    return;
                }

                const row = button.closest('[data-repeat-row], [data-comfort-row], [data-policy-row], [data-faq-row]');
                row?.remove();
            });

            document.addEventListener('change', (event) => {
                const input = event.target.closest('[data-media-preview-input]');

                if (!input) {
                    return;
                }

                const preview = document.getElementById(input.dataset.mediaPreviewTarget);
                const list = preview?.querySelector('[data-media-preview-list]');

                if (!preview || !list) {
                    return;
                }

                list.innerHTML = '';
                const files = Array.from(input.files || []);
                preview.classList.toggle('hidden', files.length === 0);

                files.forEach((file) => {
                    const url = URL.createObjectURL(file);
                    const item = document.createElement('div');
                    item.className = 'overflow-hidden rounded-xl border border-[#edf2fb] bg-[#f8fbff]';
                    const caption = document.createElement('p');
                    caption.className = 'truncate px-3 py-2 text-xs font-bold text-[#52617b]';
                    caption.textContent = file.name;

                    if (input.dataset.mediaPreviewInput === 'video') {
                        const video = document.createElement('video');
                        video.src = url;
                        video.controls = true;
                        video.preload = 'metadata';
                        video.className = 'aspect-video w-full bg-[#07152f]';
                        item.append(video, caption);
                    } else {
                        const image = document.createElement('img');
                        image.src = url;
                        image.alt = file.name;
                        image.className = 'aspect-video w-full object-cover';
                        item.append(image, caption);
                    }

                    list.appendChild(item);
                });
            });

            document.addEventListener('submit', async (event) => {
                const form = event.target.closest('[data-owner-venue-draft-form]');

                if (!form) {
                    return;
                }

                event.preventDefault();
                window.dispatchEvent(new Event('baobaa:loading-stop'));

                updateSubmitState(form, true);

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    const contentType = response.headers.get('content-type') || '';
                    const payload = contentType.includes('application/json')
                        ? await response.json()
                        : { message: await response.text() };

                    if (!response.ok) {
                        const firstError = payload.errors
                            ? Object.values(payload.errors).flat()[0]
                            : payload.message;
                        showFeedback(firstError || 'Certains champs doivent être complétés.', 'error');
                        return;
                    }

                    const nextPage = await fetch(payload.next_url, {
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'text/html',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!nextPage.ok) {
                        window.location.assign(payload.next_url);
                        return;
                    }

                    const html = await nextPage.text();
                    const nextDocument = new DOMParser().parseFromString(html, 'text/html');
                    const nextContent = nextDocument.getElementById('owner-venue-draft-content');
                    const currentContent = document.getElementById('owner-venue-draft-content');

                    if (nextContent && currentContent) {
                        currentContent.innerHTML = nextContent.innerHTML;
                        window.history.pushState({}, '', payload.next_url);
                        document.querySelectorAll('[data-draft-submit-label]').forEach((label) => {
                            label.dataset.defaultLabel = label.textContent.trim();
                        });
                        showFeedback(payload.message || 'Brouillon enregistré.');
                        currentContent.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        return;
                    }

                    window.location.assign(payload.next_url);
                } catch (error) {
                    showFeedback('Impossible d’enregistrer le brouillon pour le moment.', 'error');
                } finally {
                    updateSubmitState(form, false);
                    window.dispatchEvent(new Event('baobaa:loading-stop'));
                }
            });
        });
    </script>
</x-dashboards.owner-shell>
