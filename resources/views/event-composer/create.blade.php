@php
    $selectedProviderId = old('provider_id', $providers->first()?->id);
    $selectedServiceIds = collect(old('event_service_ids', []))->map(fn ($id) => (int) $id)->all();
@endphp

<x-layouts.baobaa title="Composer mon événement - BAOBAA">
    <main class="min-h-screen bg-[#f6f8fc] text-[#07152f]">
        <header class="sticky top-0 z-50 border-b border-white/70 bg-white/95 px-5 py-3 shadow-sm shadow-[#173e7a]/5 sm:px-8 baobaa-sticky-stable">
            <div class="mx-auto flex max-w-7xl items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center gap-3 text-[#2f6bff]">
                    <span class="grid size-11 place-items-center rounded-2xl bg-[#2f6bff] text-lg font-extrabold text-white shadow-lg shadow-[#2f6bff]/25">B</span>
                    <span class="leading-none">
                        <span class="block text-xl font-extrabold tracking-[-0.045em]">baobaa</span>
                        <span class="block text-[11px] font-extrabold tracking-[-0.035em] text-[#6f7890]">marché événementiel</span>
                    </span>
                </a>

                <x-navigation.public-menu active="composer" />
                <x-navigation.account-menu />
            </div>
        </header>

        <section class="mx-auto max-w-7xl px-5 py-8 sm:px-8">
            <div class="overflow-hidden rounded-[34px] bg-[#07162f] text-white shadow-2xl shadow-[#07162f]/18">
                <div class="grid gap-8 p-6 sm:p-8 lg:grid-cols-[1fr_380px] lg:p-10">
                    <div>
                        <p class="inline-flex rounded-full bg-white/10 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.16em] text-[#bcd2ff] ring-1 ring-white/15">Planification événementielle</p>
                        <h1 class="mt-5 max-w-3xl text-4xl font-extrabold leading-tight tracking-[-0.045em] sm:text-5xl">Composez votre événement avec les bons prestataires.</h1>
                        <p class="mt-4 max-w-2xl text-base font-semibold leading-7 text-[#c7d4ec]">Choisissez vos services, ajoutez une billetterie si besoin, puis centralisez les échanges et proformas dans votre espace client.</p>
                    </div>
                    <div class="rounded-[26px] bg-white/10 p-5 ring-1 ring-white/15">
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div class="rounded-2xl bg-white px-3 py-4 text-[#07152f]">
                                <p class="text-2xl font-extrabold">{{ number_format($providers->count(), 0, ',', ' ') }}</p>
                                <p class="mt-1 text-[11px] font-extrabold text-[#6f7890]">PSE actifs</p>
                            </div>
                            <div class="rounded-2xl bg-white px-3 py-4 text-[#07152f]">
                                <p class="text-2xl font-extrabold">{{ number_format($providers->sum(fn ($provider) => $provider->services->count()), 0, ',', ' ') }}</p>
                                <p class="mt-1 text-[11px] font-extrabold text-[#6f7890]">Services</p>
                            </div>
                            <div class="rounded-2xl bg-white px-3 py-4 text-[#07152f]">
                                <p class="text-2xl font-extrabold">SAP</p>
                                <p class="mt-1 text-[11px] font-extrabold text-[#6f7890]">Billetterie</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if (session('project_status'))
                <div class="mt-6 rounded-2xl border border-[#b9d3ff] bg-[#f2f7ff] px-5 py-4 text-sm font-extrabold text-[#2f6bff]">
                    {{ session('project_status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('event-composer.store') }}" class="mt-8 grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
                @csrf
                <section class="space-y-6">
                    <div class="rounded-[28px] bg-white p-5 shadow-xl shadow-[#173e7a]/8 ring-1 ring-[#dce6f7] sm:p-6">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[#2f6bff]">Étape 1</p>
                                <h2 class="mt-1 text-2xl font-extrabold tracking-[-0.035em]">Informations de l’événement</h2>
                            </div>
                            <span class="rounded-full bg-[#eef4ff] px-3 py-1 text-xs font-extrabold text-[#2f6bff]">Projet sans espace obligatoire</span>
                        </div>

                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            <label class="md:col-span-2">
                                <span class="text-xs font-extrabold uppercase text-[#7d879d]">Nom du projet</span>
                                <input name="name" value="{{ old('name') }}" placeholder="Gala entreprise, concert privé, séminaire annuel..." class="mt-2 h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                                @error('name') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
                            </label>
                            <label>
                                <span class="text-xs font-extrabold uppercase text-[#7d879d]">Type d’événement</span>
                                <input name="event_type" value="{{ old('event_type') }}" placeholder="Mariage, conférence, concert..." class="mt-2 h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                            </label>
                            <label>
                                <span class="text-xs font-extrabold uppercase text-[#7d879d]">Nombre d’invités</span>
                                <input type="number" min="1" name="guests_count" value="{{ old('guests_count') }}" placeholder="250" class="mt-2 h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                            </label>
                            <label>
                                <span class="text-xs font-extrabold uppercase text-[#7d879d]">Date</span>
                                <input type="date" name="event_date" value="{{ old('event_date') }}" class="mt-2 h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <label>
                                    <span class="text-xs font-extrabold uppercase text-[#7d879d]">Début</span>
                                    <input type="time" name="starts_at" value="{{ old('starts_at') }}" class="mt-2 h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                                </label>
                                <label>
                                    <span class="text-xs font-extrabold uppercase text-[#7d879d]">Fin</span>
                                    <input type="time" name="ends_at" value="{{ old('ends_at') }}" class="mt-2 h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                                </label>
                            </div>
                            <label>
                                <span class="text-xs font-extrabold uppercase text-[#7d879d]">Pays</span>
                                <input name="country_code" value="{{ old('country_code', 'CI') }}" maxlength="2" class="mt-2 h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold uppercase outline-none focus:border-[#2f6bff]">
                            </label>
                            <label>
                                <span class="text-xs font-extrabold uppercase text-[#7d879d]">Ville</span>
                                <input name="city" list="composer-cities" value="{{ old('city') }}" placeholder="Abidjan" class="mt-2 h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                                <datalist id="composer-cities">
                                    @foreach ($cities as $city)
                                        <option value="{{ $city }}"></option>
                                    @endforeach
                                </datalist>
                            </label>
                            <label class="md:col-span-2">
                                <span class="text-xs font-extrabold uppercase text-[#7d879d]">Commune ou quartier</span>
                                <input name="district" value="{{ old('district') }}" placeholder="Cocody, Plateau, Almadies..." class="mt-2 h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                            </label>
                            <label class="md:col-span-2">
                                <span class="text-xs font-extrabold uppercase text-[#7d879d]">Brief ou attentes</span>
                                <textarea name="client_notes" rows="4" placeholder="Décrivez l’ambiance, les contraintes, le budget ou les prestations attendues." class="mt-2 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 py-3 text-sm font-bold leading-6 outline-none focus:border-[#2f6bff]">{{ old('client_notes') }}</textarea>
                            </label>
                        </div>
                    </div>

                    <div class="rounded-[28px] bg-white p-5 shadow-xl shadow-[#173e7a]/8 ring-1 ring-[#dce6f7] sm:p-6">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[#2f6bff]">Étape 2</p>
                                <h2 class="mt-1 text-2xl font-extrabold tracking-[-0.035em]">Choisir les prestataires</h2>
                            </div>
                            <p class="text-xs font-extrabold text-[#7d879d]">{{ number_format($providers->sum(fn ($provider) => $provider->services->count()), 0, ',', ' ') }} services disponibles</p>
                        </div>

                        @error('event_service_ids') <div class="mt-4 rounded-2xl bg-red-50 px-4 py-3 text-sm font-bold text-red-700">{{ $message }}</div> @enderror

                        @if ($providers->isNotEmpty())
                            <div class="mt-5 flex gap-2 overflow-x-auto pb-2 baobaa-scrollbar-none" role="tablist" aria-label="Prestataires PSE">
                                @foreach ($providers as $provider)
                                    <button type="button" data-provider-tab="{{ $provider->id }}" class="shrink-0 rounded-full px-4 py-2 text-sm font-extrabold transition {{ (int) $selectedProviderId === (int) $provider->id ? 'bg-[#2f6bff] text-white shadow-lg shadow-[#2f6bff]/20' : 'bg-[#f2f6ff] text-[#52617b] hover:bg-white hover:text-[#2f6bff]' }}">
                                        {{ $provider->business_name }}
                                    </button>
                                @endforeach
                            </div>

                            <div class="mt-5">
                                @foreach ($providers as $provider)
                                    <div data-provider-panel="{{ $provider->id }}" class="{{ (int) $selectedProviderId === (int) $provider->id ? '' : 'hidden' }}">
                                        <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-[#f7faff] p-4 ring-1 ring-[#dce6f7]">
                                            <div>
                                                <p class="text-lg font-extrabold text-[#07152f]">{{ $provider->business_name }}</p>
                                                <p class="mt-1 text-sm font-semibold text-[#6f7890]">{{ $provider->city }}{{ $provider->district ? ' · '.$provider->district : '' }}</p>
                                            </div>
                                            <span class="rounded-full bg-white px-3 py-1 text-xs font-extrabold text-[#2f6bff] ring-1 ring-[#dce6f7]">{{ $provider->services->count() }} service(s)</span>
                                        </div>

                                        <div class="grid gap-3 md:grid-cols-2">
                                            @foreach ($provider->services as $service)
                                                <label class="group flex cursor-pointer gap-3 rounded-[22px] border border-[#e2eaf8] bg-white p-4 transition hover:-translate-y-0.5 hover:border-[#2f6bff] hover:shadow-xl hover:shadow-[#173e7a]/8">
                                                    <input type="checkbox" name="event_service_ids[]" value="{{ $service->id }}" data-service-checkbox data-service-name="{{ $service->name }}" data-service-price="{{ $service->starting_price }}" class="mt-1 size-5 shrink-0 rounded border-[#cbd8f4] accent-[#2f6bff]" @checked(in_array((int) $service->id, $selectedServiceIds, true))>
                                                    <span class="min-w-0">
                                                        <span class="block text-sm font-extrabold text-[#07152f]">{{ $service->name }}</span>
                                                        <span class="mt-1 block text-xs font-bold leading-5 text-[#6f7890]">{{ $service->type?->name ?? 'Service événementiel' }} · {{ $service->service_area ?: $service->city }}</span>
                                                        <span class="mt-3 inline-flex rounded-full bg-[#eef4ff] px-3 py-1 text-xs font-extrabold text-[#2f6bff]">À partir de {{ number_format($service->starting_price, 0, ',', ' ') }} {{ $service->currency }}</span>
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="mt-5 rounded-2xl border border-dashed border-[#cbd8f4] bg-[#fbfcff] p-8 text-center">
                                <p class="text-lg font-extrabold text-[#07152f]">Aucun service PSE publié pour le moment.</p>
                                <p class="mt-2 text-sm font-semibold text-[#6f7890]">Le SAP doit créer des types de services et les PSE doivent publier leurs offres.</p>
                            </div>
                        @endif
                    </div>
                </section>

                <aside class="lg:sticky lg:top-24 lg:self-start">
                    <div class="rounded-[28px] bg-white p-5 shadow-2xl shadow-[#173e7a]/12 ring-1 ring-[#dce6f7]">
                        <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[#2f6bff]">Étape 3</p>
                        <h2 class="mt-1 text-2xl font-extrabold tracking-[-0.035em]">Résumé</h2>

                        <label class="mt-5 flex cursor-pointer items-start gap-3 rounded-[18px] border border-[#cfe0ff] bg-[#f4f8ff] p-4 transition hover:border-[#2f6bff]">
                            <input type="checkbox" name="ticketing_requested" value="1" data-ticketing-checkbox class="mt-1 size-5 shrink-0 rounded border-[#cbd8f4] accent-[#2f6bff]" @checked(old('ticketing_requested'))>
                            <span>
                                <span class="block text-sm font-extrabold text-[#07152f]">Billetterie BAOBAA</span>
                                <span class="mt-1 block text-xs font-semibold leading-5 text-[#6f7890]">Le SAP proposera le modèle de commission ou montant fixe négocié hors ligne.</span>
                            </span>
                        </label>

                        <div class="mt-5 rounded-[20px] bg-[#f7faff] p-4 ring-1 ring-[#dce6f7]">
                            <div class="flex justify-between gap-3 text-sm font-extrabold">
                                <span class="text-[#6f7890]">Services sélectionnés</span>
                                <span id="composer-services-count" class="text-[#07152f]">0</span>
                            </div>
                            <div class="mt-3 flex justify-between gap-3 text-sm font-extrabold">
                                <span class="text-[#6f7890]">Total estimé</span>
                                <span id="composer-total" class="text-[#07152f]">0 XOF</span>
                            </div>
                            <div id="composer-selection" class="mt-4 space-y-2 text-xs font-bold text-[#52617b]"></div>
                        </div>

                        @guest
                            <a href="{{ route('portal.login', ['portal' => 'client', 'redirect' => route('event-composer.create')]) }}" class="mt-5 flex w-full justify-center rounded-2xl bg-[#2f6bff] px-5 py-3 text-sm font-extrabold text-white shadow-xl shadow-[#2f6bff]/20 transition hover:-translate-y-0.5">Me connecter pour composer</a>
                            <p class="mt-3 text-center text-xs font-semibold leading-5 text-[#6f7890]">Connexion client obligatoire avant de sauvegarder le projet.</p>
                        @else
                            @if (auth()->user()?->hasPortal(\App\Enums\UserRole::Client))
                                <button class="mt-5 w-full rounded-2xl bg-[#2f6bff] px-5 py-3 text-sm font-extrabold text-white shadow-xl shadow-[#2f6bff]/20 transition hover:-translate-y-0.5">Créer mon projet événementiel</button>
                            @else
                                <p class="mt-5 rounded-2xl bg-[#fff8e8] p-4 text-sm font-bold leading-6 text-[#8a5b00]">Vous devez disposer du portail client pour composer un événement.</p>
                            @endif
                        @endguest
                    </div>
                </aside>
            </form>
        </section>
    </main>

    <script>
        (() => {
            const tabs = document.querySelectorAll('[data-provider-tab]');
            const panels = document.querySelectorAll('[data-provider-panel]');
            const serviceCheckboxes = document.querySelectorAll('[data-service-checkbox]');
            const ticketingCheckbox = document.querySelector('[data-ticketing-checkbox]');
            const count = document.getElementById('composer-services-count');
            const total = document.getElementById('composer-total');
            const selection = document.getElementById('composer-selection');
            const formatter = new Intl.NumberFormat('fr-FR');

            const updateSummary = () => {
                const selected = [...serviceCheckboxes].filter((checkbox) => checkbox.checked);
                const amount = selected.reduce((sum, checkbox) => sum + Number(checkbox.dataset.servicePrice || 0), 0);

                count.textContent = selected.length.toString();
                total.textContent = `${formatter.format(amount)} XOF`;
                selection.innerHTML = '';

                selected.slice(0, 5).forEach((checkbox) => {
                    const row = document.createElement('p');
                    row.className = 'rounded-xl bg-white px-3 py-2 ring-1 ring-[#e2eaf8]';
                    row.textContent = checkbox.dataset.serviceName;
                    selection.appendChild(row);
                });

                if (ticketingCheckbox?.checked) {
                    const row = document.createElement('p');
                    row.className = 'rounded-xl bg-[#eef4ff] px-3 py-2 font-extrabold text-[#2f6bff] ring-1 ring-[#cfe0ff]';
                    row.textContent = 'Billetterie BAOBAA demandée';
                    selection.appendChild(row);
                }
            };

            tabs.forEach((tab) => {
                tab.addEventListener('click', () => {
                    tabs.forEach((item) => {
                        item.classList.remove('bg-[#2f6bff]', 'text-white', 'shadow-lg', 'shadow-[#2f6bff]/20');
                        item.classList.add('bg-[#f2f6ff]', 'text-[#52617b]');
                    });

                    tab.classList.add('bg-[#2f6bff]', 'text-white', 'shadow-lg', 'shadow-[#2f6bff]/20');
                    tab.classList.remove('bg-[#f2f6ff]', 'text-[#52617b]');

                    panels.forEach((panel) => {
                        panel.classList.toggle('hidden', panel.dataset.providerPanel !== tab.dataset.providerTab);
                    });
                });
            });

            serviceCheckboxes.forEach((checkbox) => checkbox.addEventListener('change', updateSummary));
            ticketingCheckbox?.addEventListener('change', updateSummary);
            updateSummary();
        })();
    </script>
</x-layouts.baobaa>
