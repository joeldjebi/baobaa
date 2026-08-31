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
@endphp

<x-dashboards.owner-shell title="Ajouter un espace" subtitle="Avancez étape par étape : chaque clic sur Continuer garde votre fiche en brouillon." active="venues" :owner-profile="$ownerProfile" :active-venues-count="$activeVenuesCount" :pending-bookings-count="$pendingBookingsCount" :confirmed-bookings-count="$confirmedBookingsCount" :gross-revenue="$grossRevenue" :active-subscription="$activeSubscription" :billing-preference-label="$billingPreferenceLabel">
    <div id="owner-venue-draft-content">
        <div data-draft-feedback>
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
            <form method="POST" action="{{ route('owner.venues.draft.store') }}" class="space-y-5">
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
                            <label><span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Montant à réserver</span><input name="reservation_amount" type="number" value="{{ old('reservation_amount', $currentVenue?->reservation_amount) }}" class="mt-2 w-full rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]"></label>
                        </div>
                        @break

                    @case('details')
                        <label class="block"><span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Phrase d’accroche</span><input name="short_description" value="{{ old('short_description', $currentVenue?->short_description) }}" class="mt-2 w-full rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]"></label>
                        <label class="block"><span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Description complète</span><textarea name="description" rows="6" class="mt-2 w-full resize-none rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold leading-6 outline-none focus:border-[#2f6bff]">{{ old('description', $currentVenue?->description) }}</textarea></label>
                        <label class="block"><span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Points forts affichés sous les photos</span><textarea name="highlights" rows="4" placeholder="Un point fort par ligne" class="mt-2 w-full resize-none rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold leading-6 outline-none focus:border-[#2f6bff]">{{ old('highlights', $lineValue($currentVenue?->highlights)) }}</textarea></label>
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
                    <button data-draft-submit class="inline-flex items-center gap-2 rounded-2xl bg-[#2f6bff] px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/20">
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
                const button = event.target.closest('[data-remove-row]');

                if (!button) {
                    return;
                }

                const row = button.closest('[data-repeat-row], [data-comfort-row], [data-policy-row], [data-faq-row]');
                row?.remove();
            });

            document.addEventListener('submit', async (event) => {
                const form = event.target.closest('form[action="{{ route('owner.venues.draft.store') }}"]');

                if (!form) {
                    return;
                }

                event.preventDefault();

                const submitButton = form.querySelector('[data-draft-submit]');
                const submitLabel = form.querySelector('[data-draft-submit-label]');
                const originalLabel = submitLabel?.textContent || 'Continuer';
                submitButton?.setAttribute('disabled', 'disabled');
                submitButton?.classList.add('opacity-80');
                submitLabel && (submitLabel.textContent = 'Enregistrement...');

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

                    const payload = await response.json();

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
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    const html = await nextPage.text();
                    const nextDocument = new DOMParser().parseFromString(html, 'text/html');
                    const nextContent = nextDocument.getElementById('owner-venue-draft-content');
                    const currentContent = document.getElementById('owner-venue-draft-content');

                    if (nextContent && currentContent) {
                        currentContent.innerHTML = nextContent.innerHTML;
                        window.history.pushState({}, '', payload.next_url);
                        showFeedback(payload.message || 'Brouillon enregistré.');
                        currentContent.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                } catch (error) {
                    showFeedback('Impossible d’enregistrer le brouillon pour le moment.', 'error');
                } finally {
                    submitButton?.removeAttribute('disabled');
                    submitButton?.classList.remove('opacity-80');
                    submitLabel && (submitLabel.textContent = originalLabel);
                }
            });
        });
    </script>
</x-dashboards.owner-shell>
