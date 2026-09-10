@php
    $typeLabels = [
        'country' => 'Pays',
        'city' => 'Ville',
        'district' => 'Commune',
    ];
    $inputClass = 'h-12 rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold text-[#07152f] outline-none transition focus:border-[#2f6bff] focus:ring-4 focus:ring-[#2f6bff]/10';
@endphp

<x-dashboards.sap-shell title="Pays, villes et communes" subtitle="Centralisez les localisations disponibles dans les parcours publics de réservation." active="locations" :owners-count="$ownersCount" :clients-count="$clientsCount" :published-venues-count="$publishedVenuesCount" :pending-access-requests-count="$pendingAccessRequestsCount" :pending-sponsorships-count="$pendingSponsorshipsCount" :gross-payments-amount="$grossPaymentsAmount" :active-bookings-count="$activeBookingsCount">
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['label' => 'Pays configurés', 'value' => $locationStats['countries'] ?? 0],
            ['label' => 'Villes configurées', 'value' => $locationStats['cities'] ?? 0],
            ['label' => 'Communes configurées', 'value' => $locationStats['districts'] ?? 0],
            ['label' => 'Localisations actives', 'value' => $locationStats['active'] ?? 0],
        ] as $stat)
            <div class="rounded-[24px] bg-white p-5 shadow-xl shadow-[#173e7a]/8 ring-1 ring-[#dce6f7]">
                <p class="text-[11px] font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">{{ $stat['label'] }}</p>
                <p class="mt-2 text-3xl font-extrabold tracking-[-0.04em] text-[#07152f]">{{ number_format($stat['value'], 0, ',', ' ') }}</p>
            </div>
        @endforeach
    </section>

    <section class="mt-6 rounded-[30px] bg-white p-5 shadow-2xl shadow-[#173e7a]/10 ring-1 ring-[#dce6f7] sm:p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[#2f6bff]">Référentiel de réservation</p>
                <h2 class="mt-2 text-2xl font-extrabold tracking-[-0.04em] text-[#07152f]">Ajouter une localisation</h2>
                <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-[#6f7890]">Une ville dépend d’un pays actif. Une commune dépend d’une ville active. Les localisations désactivées disparaissent automatiquement des formulaires publics.</p>
            </div>
            <span class="rounded-full bg-[#eef4ff] px-4 py-2 text-xs font-extrabold text-[#2f6bff] ring-1 ring-[#dce6f7]">Gestion SAP</span>
        </div>

        <form method="POST" action="{{ route('sap.locations.store') }}" class="mt-6 grid gap-4 xl:grid-cols-12" data-location-form>
            @csrf
            <label class="xl:col-span-2">
                <span class="text-xs font-extrabold uppercase text-[#7d879d]">Nature</span>
                <select name="type" data-location-type class="mt-2 w-full {{ $inputClass }}">
                    <option value="country" @selected(old('type') === 'country')>Pays</option>
                    <option value="city" @selected(old('type') === 'city')>Ville</option>
                    <option value="district" @selected(old('type') === 'district')>Commune</option>
                </select>
                @error('type') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
            </label>

            <label class="xl:col-span-3">
                <span class="text-xs font-extrabold uppercase text-[#7d879d]">Nom affiché</span>
                <input name="name" value="{{ old('name') }}" placeholder="Côte d’Ivoire, Abidjan, Cocody..." class="mt-2 w-full {{ $inputClass }}">
                @error('name') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
            </label>

            <label class="xl:col-span-2" data-country-code-field>
                <span class="text-xs font-extrabold uppercase text-[#7d879d]">Code pays</span>
                <input name="code" value="{{ old('code') }}" maxlength="2" placeholder="CI" class="mt-2 w-full uppercase {{ $inputClass }}">
                @error('code') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
            </label>

            <div class="hidden xl:col-span-3" data-country-field>
                <label class="block">
                    <span class="text-xs font-extrabold uppercase text-[#7d879d]">Pays parent</span>
                    <input type="search" data-parent-country-search placeholder="Filtrer les pays" class="mt-2 h-10 w-full rounded-xl border border-[#dce6f7] bg-white px-3 text-xs font-bold text-[#07152f] outline-none transition focus:border-[#2f6bff]">
                    <select name="country_id" data-parent-country-select class="mt-2 w-full {{ $inputClass }}">
                        <option value="">Choisir un pays actif</option>
                        @foreach ($countries as $country)
                            <option value="{{ $country->id }}" data-country-code="{{ $country->code }}" @selected((string) old('country_id') === (string) $country->id)>{{ $country->name }} · {{ $country->code }}</option>
                        @endforeach
                    </select>
                </label>
                @error('country_id') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
            </div>

            <div class="hidden xl:col-span-5" data-city-field>
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="block">
                        <span class="text-xs font-extrabold uppercase text-[#7d879d]">Pays</span>
                        <select data-city-country-filter class="mt-2 w-full {{ $inputClass }}">
                            <option value="">Choisir un pays</option>
                            @foreach ($countries as $country)
                                <option value="{{ $country->code }}">{{ $country->name }} · {{ $country->code }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-xs font-extrabold uppercase text-[#7d879d]">Ville parente</span>
                        <input type="search" data-parent-city-search placeholder="Filtrer les villes" class="mt-2 h-10 w-full rounded-xl border border-[#dce6f7] bg-white px-3 text-xs font-bold text-[#07152f] outline-none transition focus:border-[#2f6bff]">
                        <select name="city_id" data-parent-city-select class="mt-2 w-full {{ $inputClass }}">
                            <option value="">Choisir une ville active</option>
                            @foreach ($cities as $city)
                                <option value="{{ $city->id }}" data-country="{{ $city->country_code }}" @selected((string) old('city_id') === (string) $city->id)>{{ $city->name }} · {{ $city->parent?->name }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
                @error('city_id') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
            </div>

            <label class="xl:col-span-2">
                <span class="text-xs font-extrabold uppercase text-[#7d879d]">Ordre</span>
                <input name="sort_order" type="number" min="0" value="{{ old('sort_order') }}" placeholder="Auto" class="mt-2 w-full {{ $inputClass }}">
                @error('sort_order') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
            </label>

            <div class="flex items-end xl:col-span-2">
                <button class="h-12 w-full rounded-2xl bg-[#2f6bff] px-5 text-sm font-extrabold text-white shadow-xl shadow-[#2f6bff]/20 transition hover:-translate-y-0.5">Enregistrer</button>
            </div>
        </form>
    </section>

    <section class="mt-6 rounded-[30px] bg-white p-5 shadow-2xl shadow-[#173e7a]/10 ring-1 ring-[#dce6f7] sm:p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold tracking-[-0.04em] text-[#07152f]">Localisations enregistrées</h2>
                <p class="mt-1 text-sm font-semibold text-[#6f7890]">Filtrez, contrôlez et désactivez les valeurs qui ne doivent plus être disponibles.</p>
            </div>
            <form method="GET" class="grid w-full gap-3 lg:w-auto lg:grid-cols-[260px_170px_170px_auto]">
                <input name="q" value="{{ request('q') }}" placeholder="Rechercher" class="{{ $inputClass }}">
                <select name="type" class="{{ $inputClass }}">
                    <option value="">Tous les types</option>
                    @foreach ($typeLabels as $value => $label)
                        <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="status" class="{{ $inputClass }}">
                    <option value="">Tous les statuts</option>
                    <option value="active" @selected(request('status') === 'active')>Actifs</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactifs</option>
                </select>
                <button class="h-12 rounded-2xl bg-[#07152f] px-5 text-sm font-extrabold text-white">Filtrer</button>
            </form>
        </div>

        <div class="mt-5 overflow-hidden rounded-3xl border border-[#e4ebf7]">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#e4ebf7] text-left text-sm">
                    <thead class="bg-[#f7faff] text-xs font-extrabold uppercase text-[#7d879d]">
                        <tr>
                            <th class="px-4 py-3">Localisation</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3">Rattachement</th>
                            <th class="px-4 py-3">Ordre</th>
                            <th class="px-4 py-3">Statut</th>
                            <th class="px-4 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#eef3fb]">
                        @forelse($locations as $location)
                            <tr class="align-top transition hover:bg-[#f8fbff]">
                                <td class="px-4 py-4">
                                    <p class="font-extrabold text-[#07152f]">{{ $location->name }}</p>
                                    <p class="mt-1 text-xs font-bold text-[#6f7890]">{{ $location->code ?: $location->country_code ?: 'Aucun code' }} · {{ number_format($location->children_count, 0, ',', ' ') }} élément{{ $location->children_count > 1 ? 's' : '' }} lié{{ $location->children_count > 1 ? 's' : '' }}</p>
                                </td>
                                <td class="px-4 py-4 font-extrabold text-[#07152f]">{{ $typeLabels[$location->type] ?? $location->type }}</td>
                                <td class="px-4 py-4 text-sm font-bold text-[#64708a]">{{ $location->parent?->name ?? 'Racine' }}</td>
                                <td class="px-4 py-4 text-sm font-bold text-[#64708a]">{{ number_format($location->sort_order, 0, ',', ' ') }}</td>
                                <td class="px-4 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-extrabold {{ $location->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $location->is_active ? 'Actif' : 'Inactif' }}</span>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <form method="POST" action="{{ route('sap.locations.toggle', $location) }}">
                                        @csrf
                                        <button class="rounded-full border border-[#d9e2f6] px-3 py-1.5 text-xs font-extrabold text-[#2f6bff]">{{ $location->is_active ? 'Désactiver' : 'Activer' }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-sm font-bold text-[#7d879d]">Aucune localisation configurée pour le moment.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">{{ $locations->links() }}</div>
    </section>

    <script>
        (() => {
            const typeSelect = document.querySelector('[data-location-type]');
            const codeField = document.querySelector('[data-country-code-field]');
            const countryField = document.querySelector('[data-country-field]');
            const cityField = document.querySelector('[data-city-field]');
            const parentCountrySelect = document.querySelector('[data-parent-country-select]');
            const parentCountrySearch = document.querySelector('[data-parent-country-search]');
            const cityCountryFilter = document.querySelector('[data-city-country-filter]');
            const parentCitySelect = document.querySelector('[data-parent-city-select]');
            const parentCitySearch = document.querySelector('[data-parent-city-search]');
            const parentCountryOptions = parentCountrySelect ? [...parentCountrySelect.options] : [];
            const parentCityOptions = parentCitySelect ? [...parentCitySelect.options] : [];

            const normalize = (value) => (value || '').toString().trim().toLowerCase();
            const matchesSearch = (label, search) => normalize(label).includes(normalize(search));

            const rebuildSelect = (select, options, predicate, currentValue) => {
                if (!select) {
                    return;
                }

                select.innerHTML = '';
                options.forEach((option) => {
                    if (option.value === '' || predicate(option)) {
                        select.appendChild(option.cloneNode(true));
                    }
                });

                if ([...select.options].some((option) => option.value === currentValue)) {
                    select.value = currentValue;
                }
            };

            const syncParentCountries = () => {
                rebuildSelect(
                    parentCountrySelect,
                    parentCountryOptions,
                    (option) => matchesSearch(option.textContent, parentCountrySearch?.value || ''),
                    parentCountrySelect?.value || '',
                );
            };

            const syncParentCities = () => {
                rebuildSelect(
                    parentCitySelect,
                    parentCityOptions,
                    (option) => (!cityCountryFilter?.value || option.dataset.country === cityCountryFilter.value) && matchesSearch(option.textContent, parentCitySearch?.value || ''),
                    parentCitySelect?.value || '',
                );
            };

            const syncFields = () => {
                const type = typeSelect?.value || 'country';
                codeField?.classList.toggle('hidden', type !== 'country');
                countryField?.classList.toggle('hidden', type !== 'city');
                cityField?.classList.toggle('hidden', type !== 'district');
                syncParentCountries();
                syncParentCities();
            };

            typeSelect?.addEventListener('change', syncFields);
            parentCountrySearch?.addEventListener('input', syncParentCountries);
            cityCountryFilter?.addEventListener('change', () => {
                if (parentCitySelect) {
                    parentCitySelect.value = '';
                }

                syncParentCities();
            });
            parentCitySearch?.addEventListener('input', syncParentCities);
            syncFields();
        })();
    </script>
</x-dashboards.sap-shell>
