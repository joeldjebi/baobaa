@php
    $title = $venue->name;
    $deposit = number_format($depositAmount, 0, ',', ' ').' '.$venue->currency;
    $acceptedPaymentMethods = collect($venue->payment_methods ?: ['baobaa_checkout']);
    $selectedEventServiceIds = collect(old('event_service_ids', []))->map(fn ($id) => (int) $id)->all();
    $selectedAddOnIds = collect(old('selected_add_on_ids', []))->map(fn ($id) => (int) $id)->all();
    $selectedModuleTemplateIds = collect(old('selected_module_template_ids', []))->map(fn ($id) => (int) $id)->all();
    $maxEventServices = 6;
@endphp

<x-layouts.baobaa :title="$title.' - BAOBAA'">
    <main class="min-h-screen bg-[#f6f8fc] text-[#07152f]">
        <x-navigation.public-header active="venues" />

        <section class="mx-auto max-w-7xl px-5 py-8 sm:px-8">
            <div class="overflow-hidden rounded-[34px] bg-[#07162f] text-white shadow-2xl shadow-[#07162f]/18">
                <div class="grid gap-8 p-6 sm:p-8 lg:grid-cols-[1fr_380px] lg:p-10">
                    <div>
                        <p class="inline-flex rounded-full bg-white/10 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.16em] text-[#bcd2ff] ring-1 ring-white/15">Package événementiel</p>
                        <h1 class="mt-5 max-w-3xl text-4xl font-extrabold leading-tight tracking-[-0.01em] sm:text-5xl">Composez votre événement avec les bons prestataires.</h1>
                        <p class="mt-4 max-w-2xl text-base font-semibold leading-7 text-[#c7d4ec]">Choisissez les dates, les options du lieu, les services événementiels et la billetterie pour lancer une demande claire et complète.</p>
                    </div>
                    <div class="rounded-[26px] bg-white/10 p-5 ring-1 ring-white/15">
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div class="rounded-2xl bg-white px-3 py-4 text-[#07152f]">
                                <p class="text-2xl font-extrabold">{{ $venue->max_capacity }}</p>
                                <p class="mt-1 text-[11px] font-extrabold text-[#6f7890]">Invités</p>
                            </div>
                            <div class="rounded-2xl bg-white px-3 py-4 text-[#07152f]">
                                <p class="text-2xl font-extrabold">{{ count($reservationAddOns) }}</p>
                                <p class="mt-1 text-[11px] font-extrabold text-[#6f7890]">Options</p>
                            </div>
                            <div class="rounded-2xl bg-white px-3 py-4 text-[#07152f]">
                                <p class="text-2xl font-extrabold">Billets</p>
                                <p class="mt-1 text-[11px] font-extrabold text-[#6f7890]">Billetterie</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if (session('booking_status'))
                <div class="mt-6 rounded-2xl border border-[#b9d3ff] bg-[#f2f7ff] px-5 py-4 text-sm font-extrabold text-[#2f6bff]">
                    {{ session('booking_status') }}
                </div>
            @endif

            <x-forms.validation-errors class="mt-6" />

            <form method="POST" action="{{ route('bookings.store', $venue) }}" class="mt-8 grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px]" data-reservation-form>
                @csrf

                <section class="space-y-6">
                    <div class="rounded-[28px] bg-white p-5 shadow-xl shadow-[#173e7a]/8 ring-1 ring-[#dce6f7] sm:p-6">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[#2f6bff]">Étape 1</p>
                                <h2 class="mt-1 text-2xl font-extrabold tracking-[-0.015em]">Informations de l’événement</h2>
                            </div>
                            <span class="rounded-full bg-[#eef4ff] px-3 py-1 text-xs font-extrabold text-[#2f6bff]">{{ $venue->name }}</span>
                        </div>

                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            <label class="block rounded-[16px] border border-[#dce6f7] bg-[#fbfcff] px-3 py-2.5">
                                <span class="block text-[10px] font-extrabold uppercase text-[#8a94aa]">Date de début</span>
                                <input type="date" name="start_date" value="{{ old('start_date', request('start_date')) }}" min="{{ now()->toDateString() }}" data-start-date class="mt-0.5 w-full bg-transparent text-sm font-bold text-[#151821] outline-none" required>
                            </label>
                            <label class="block rounded-[16px] border border-[#dce6f7] bg-[#fbfcff] px-3 py-2.5">
                                <span class="block text-[10px] font-extrabold uppercase text-[#8a94aa]">Heure</span>
                                <input type="time" name="starts_at" value="{{ old('starts_at') }}" class="mt-0.5 w-full bg-transparent text-sm font-bold text-[#151821] outline-none" required>
                            </label>
                            <label class="block rounded-[16px] border border-[#dce6f7] bg-[#fbfcff] px-3 py-2.5">
                                <span class="block text-[10px] font-extrabold uppercase text-[#8a94aa]">Date de fin</span>
                                <input type="date" name="end_date" value="{{ old('end_date', request('end_date')) }}" min="{{ now()->toDateString() }}" data-end-date class="mt-0.5 w-full bg-transparent text-sm font-bold text-[#151821] outline-none">
                            </label>
                            <label class="block rounded-[16px] border border-[#dce6f7] bg-[#fbfcff] px-3 py-2.5">
                                <span class="block text-[10px] font-extrabold uppercase text-[#8a94aa]">Heure de fin</span>
                                <input type="time" name="ends_at" value="{{ old('ends_at') }}" class="mt-0.5 w-full bg-transparent text-sm font-bold text-[#151821] outline-none" required>
                            </label>
                            @if ($venue->openingHoursLabel())
                                <p class="text-xs font-semibold text-[#6f7890] md:col-span-2">Cet espace accueille les événements de {{ $venue->openingHoursLabel() }}.</p>
                            @endif
                            <label class="block md:col-span-2">
                                <span class="text-xs font-extrabold uppercase text-[#7d879d]">Type d’événement</span>
                                <select name="event_type" class="mt-2 h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                                    <option value="conference" @selected(old('event_type') === 'conference')>Conférence / Séminaire</option>
                                    <option value="mariage" @selected(old('event_type') === 'mariage')>Mariage / Célébration</option>
                                    <option value="concert" @selected(old('event_type') === 'concert')>Concert / Spectacle</option>
                                    <option value="lancement" @selected(old('event_type') === 'lancement')>Lancement de produit</option>
                                </select>
                            </label>
                            <label>
                                <span class="text-xs font-extrabold uppercase text-[#7d879d]">Nombre d’invités</span>
                                <input type="number" min="1" max="{{ $venue->max_capacity }}" name="guests_count" value="{{ old('guests_count') }}" placeholder="120" class="mt-2 h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]" required>
                            </label>
                            <label>
                                <span class="text-xs font-extrabold uppercase text-[#7d879d]">Paiement de l’acompte</span>
                                <select name="payment_method" class="mt-2 h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]" required>
                                    @foreach ($acceptedPaymentMethods as $method)
                                        <option value="{{ $method }}" @selected(old('payment_method') === $method)>{{ \App\Enums\PaymentMethod::labelFor($method) }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="md:col-span-2">
                                <span class="text-xs font-extrabold uppercase text-[#7d879d]">Message au partenaire</span>
                                <textarea name="client_notes" rows="4" placeholder="Décrivez l’ambiance, les contraintes, le budget ou les attentes." class="mt-2 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 py-3 text-sm font-bold leading-6 outline-none focus:border-[#2f6bff]">{{ old('client_notes') }}</textarea>
                            </label>
                        </div>
                    </div>

                    <div class="rounded-[28px] bg-white p-5 shadow-xl shadow-[#173e7a]/8 ring-1 ring-[#dce6f7] sm:p-6">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[#2f6bff]">Étape 2</p>
                                <h2 class="mt-1 text-2xl font-extrabold tracking-[-0.015em]">Ajouter des services additionnels</h2>
                            </div>
                            <p class="text-xs font-extrabold text-[#7d879d]">{{ count($reservationAddOns) }} option(s)</p>
                        </div>

                        @if (count($reservationAddOns) > 0)
                            <div class="mt-5 grid gap-3 md:grid-cols-2">
                                @foreach ($reservationAddOns as $addOn)
                                    <label class="group flex cursor-pointer gap-3 rounded-[22px] border border-[#e2eaf8] bg-[#fbfcff] p-4 transition hover:-translate-y-0.5 hover:border-[#2f6bff] hover:bg-white hover:shadow-xl hover:shadow-[#173e7a]/8">
                                        <input type="checkbox" name="{{ ($addOn['source'] ?? 'venue_add_on') === 'owner_module_template' ? 'selected_module_template_ids[]' : 'selected_add_on_ids[]' }}" value="{{ $addOn['id'] }}" data-addon-checkbox data-addon-name="{{ $addOn['name'] }}" data-addon-price="{{ $addOn['amount'] }}" class="mt-1 size-5 shrink-0 rounded border-[#cbd8f4] accent-[#2f6bff]" @checked(($addOn['source'] ?? 'venue_add_on') === 'owner_module_template' ? in_array((int) $addOn['id'], $selectedModuleTemplateIds, true) : in_array((int) $addOn['id'], $selectedAddOnIds, true))>
                                        <span class="min-w-0">
                                            <span class="block text-sm font-extrabold text-[#07152f]">{{ $addOn['name'] }}</span>
                                            @if (filled($addOn['description'] ?? null))
                                                <span class="mt-1 block text-xs font-semibold leading-5 text-[#6f7890]">{{ $addOn['description'] }}</span>
                                            @endif
                                            <span class="mt-2 inline-flex rounded-full bg-[#eef4ff] px-3 py-1 text-xs font-extrabold text-[#2f6bff]">{{ $addOn['price'] }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <div class="mt-5 rounded-2xl border border-dashed border-[#cbd8f4] bg-[#fbfcff] p-8 text-center">
                                <p class="text-lg font-extrabold text-[#07152f]">Aucun service additionnel n’est proposé pour cet espace.</p>
                                <p class="mt-2 text-sm font-semibold text-[#6f7890]">Vous pouvez continuer avec la réservation de l’espace et les services événementiels disponibles.</p>
                            </div>
                        @endif
                    </div>

                    <div class="rounded-[28px] bg-white p-5 shadow-xl shadow-[#173e7a]/8 ring-1 ring-[#dce6f7] sm:p-6">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[#2f6bff]">Étape 3</p>
                                <h2 class="mt-1 text-2xl font-extrabold tracking-[-0.015em]">Choisir les prestataires événementiels</h2>
                            </div>
                            <p class="text-xs font-extrabold text-[#7d879d]"><span data-services-selected-count>0</span> / {{ $maxEventServices }} services sélectionnés</p>
                        </div>

                        @if (($publicEventProviders ?? collect())->isNotEmpty())
                            <div class="mt-5 flex gap-2 overflow-x-auto pb-2 baobaa-scrollbar-none" role="tablist" aria-label="Prestataires événementiels">
                                @foreach ($publicEventProviders as $providerGroup)
                                    @php $provider = $providerGroup['provider']; @endphp
                                    <button type="button" data-provider-tab="{{ $provider->id }}" class="shrink-0 rounded-full px-4 py-2 text-sm font-extrabold transition {{ $loop->first ? 'bg-[#2f6bff] text-white shadow-lg shadow-[#2f6bff]/20' : 'bg-[#f2f6ff] text-[#52617b] hover:bg-white hover:text-[#2f6bff]' }}">
                                        {{ $provider->business_name }}
                                    </button>
                                @endforeach
                            </div>

                            <div class="mt-5 space-y-5">
                                @foreach ($publicEventProviders as $providerGroup)
                                    @php $provider = $providerGroup['provider']; @endphp
                                    <div data-provider-panel="{{ $provider->id }}" class="{{ $loop->first ? '' : 'hidden' }} rounded-[24px] border border-[#e2eaf8] bg-[#f9fbff] p-4">
                                        <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-white p-4 ring-1 ring-[#dce6f7]">
                                            <div>
                                                <p class="text-lg font-extrabold text-[#07152f]">{{ $provider->business_name }}</p>
                                                <p class="mt-1 text-sm font-semibold text-[#6f7890]">{{ $provider->city }}{{ $provider->district ? ' · '.$provider->district : '' }}</p>
                                            </div>
                                            <span class="rounded-full bg-[#eef4ff] px-3 py-1 text-xs font-extrabold text-[#2f6bff] ring-1 ring-[#dce6f7]">{{ $providerGroup['services']->count() }} service(s)</span>
                                        </div>

                                        <p class="mb-3 text-xs font-extrabold uppercase tracking-[0.12em] text-[#7d879d]">Services du prestataire</p>
                                        <div class="grid gap-3 md:grid-cols-2">
                                            @foreach ($providerGroup['services'] as $service)
                                                <label class="group flex cursor-pointer gap-3 rounded-[22px] border border-[#e2eaf8] bg-white p-4 transition hover:-translate-y-0.5 hover:border-[#2f6bff] hover:shadow-xl hover:shadow-[#173e7a]/8">
                                                    <input type="checkbox" name="event_service_ids[]" value="{{ $service->id }}" data-service-checkbox data-service-name="{{ $service->name }}" data-service-price="{{ $service->starting_price }}" class="mt-1 size-5 shrink-0 rounded border-[#cbd8f4] accent-[#2f6bff]" @checked(in_array((int) $service->id, $selectedEventServiceIds, true))>
                                                    <img src="{{ $service->cover_image_url }}" alt="" referrerpolicy="no-referrer" loading="lazy" onerror="this.onerror=null;this.src='{{ \App\Models\Venue::placeholderImageUrl() }}';" class="size-16 shrink-0 rounded-2xl object-cover ring-1 ring-[#dce6f7]">
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
                                <p class="text-lg font-extrabold text-[#07152f]">Aucun service événementiel publié pour le moment.</p>
                            </div>
                        @endif
                    </div>
                </section>

                <aside class="lg:sticky lg:top-24 lg:self-start">
                    <div class="rounded-[28px] bg-white p-5 shadow-2xl shadow-[#173e7a]/12 ring-1 ring-[#dce6f7]">
                        <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[#2f6bff]">Étape 4</p>
                        <h2 class="mt-1 text-2xl font-extrabold tracking-[-0.015em]">Résumé</h2>

                        <div class="mt-5 rounded-[20px] bg-[#f7faff] p-4 ring-1 ring-[#dce6f7]">
                            <div class="flex justify-between gap-3 text-sm font-extrabold">
                                <span class="text-[#6f7890]">Prix de base</span>
                                <span class="text-right text-[#07152f]">{{ number_format($venue->starting_price, 0, ',', ' ') }} {{ $venue->currency }}<span class="block text-[11px] font-bold text-[#6f7890]">{{ ($venue->price_unit ?? \App\Enums\PriceUnit::Event)->label() }}</span></span>
                            </div>
                            @if ($venue->minimumDurationLabel())
                                <p class="mt-1 text-[11px] font-semibold text-[#8a94aa]">{{ $venue->minimumDurationLabel() }}</p>
                            @endif
                            <div class="mt-3 flex justify-between gap-3 text-sm font-extrabold">
                                <span class="text-[#6f7890]">Acompte estimé</span>
                                <span class="text-[#07152f]">{{ $deposit }}</span>
                            </div>
                            <p class="mt-1 text-[11px] font-semibold leading-4 text-[#8a94aa]">{{ $depositDescription }} Il est recalculé sur la proforma avec les options choisies.</p>
                            <div class="mt-3 flex justify-between gap-3 text-sm font-extrabold">
                                <span class="text-[#6f7890]">Options sélectionnées</span>
                                <span id="composer-addons-count" class="text-[#07152f]">0</span>
                            </div>
                            <div class="mt-3 flex justify-between gap-3 text-sm font-extrabold">
                                <span class="text-[#6f7890]">Services sélectionnés</span>
                                <span id="composer-services-count" class="text-[#07152f]">0</span>
                            </div>
                            <div class="mt-3 flex justify-between gap-3 text-sm font-extrabold">
                                <span class="text-[#6f7890]">Total estimé</span>
                                <span id="composer-total" class="text-[#07152f]">0 {{ $venue->currency }}</span>
                            </div>
                            <div id="composer-selection" class="mt-4 space-y-2 text-xs font-bold text-[#52617b]"></div>
                        </div>

                        <div class="mt-4 rounded-[18px] bg-[#f7faff] p-4 ring-1 ring-[#dce6f7]">
                            <p class="text-[11px] font-extrabold uppercase tracking-[0.12em] text-[#2f6bff]">Moyens de paiement acceptés</p>
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                @foreach ($acceptedPaymentMethods as $method)
                                    <span class="rounded-full bg-white px-2.5 py-1 text-[11px] font-extrabold text-[#52617b] ring-1 ring-[#dce6f7]">{{ \App\Enums\PaymentMethod::labelFor($method) }}</span>
                                @endforeach
                            </div>
                        </div>

                        <label class="mt-5 flex cursor-pointer items-start gap-3 rounded-[18px] border border-[#cfe0ff] bg-[#f4f8ff] p-4 transition hover:border-[#2f6bff]">
                            <input type="checkbox" name="ticketing_requested" value="1" data-ticketing-checkbox class="mt-1 size-5 shrink-0 rounded border-[#cbd8f4] accent-[#2f6bff]" @checked(old('ticketing_requested'))>
                            <span>
                                <span class="block text-sm font-extrabold text-[#07152f]">Billetterie digitale BAOBAA</span>
                                <span class="mt-1 block text-xs font-semibold leading-5 text-[#6f7890]">Créez une expérience fluide pour vendre les billets, suivre les accès et accueillir vos invités avec sérénité.</span>
                            </span>
                        </label>

                        @guest
                            <a href="{{ route('portal.login', ['portal' => 'client', 'redirect' => route('venues.reservation', $venue->slug)]) }}" class="mt-5 flex w-full justify-center rounded-2xl bg-[#2f6bff] px-5 py-3 text-sm font-extrabold text-white shadow-xl shadow-[#2f6bff]/20 transition hover:-translate-y-0.5">Me connecter pour lancer la demande</a>
                            <p class="mt-3 text-center text-xs font-semibold leading-5 text-[#6f7890]">Connexion client obligatoire avant la proforma et le paiement de l’acompte.</p>
                        @else
                            <x-forms.terms-acceptance class="mt-5" />
                            <button type="submit" class="mt-4 w-full rounded-2xl bg-[#2f6bff] px-5 py-3 text-sm font-extrabold text-white shadow-xl shadow-[#2f6bff]/20 transition hover:-translate-y-0.5">Valider la demande</button>
                        @endguest
                    </div>
                </aside>
            </form>
        </section>
        <x-navigation.public-footer />
    </main>

    <script>
        (() => {
            const tabs = document.querySelectorAll('[data-provider-tab]');
            const panels = document.querySelectorAll('[data-provider-panel]');
            const serviceCheckboxes = document.querySelectorAll('[data-service-checkbox]');
            const addOnCheckboxes = document.querySelectorAll('[data-addon-checkbox]');
            const ticketingCheckbox = document.querySelector('[data-ticketing-checkbox]');
            const addOnsCount = document.getElementById('composer-addons-count');
            const count = document.getElementById('composer-services-count');
            const total = document.getElementById('composer-total');
            const selection = document.getElementById('composer-selection');
            const formatter = new Intl.NumberFormat('fr-FR');
            const servicesSelectedCount = document.querySelectorAll('[data-services-selected-count]');
            const maxEventServices = @json($maxEventServices);
            const startDate = document.querySelector('[data-start-date]');
            const endDate = document.querySelector('[data-end-date]');

            const syncEndDateMinimum = () => {
                if (! startDate || ! endDate) {
                    return;
                }

                endDate.min = startDate.value || startDate.min;

                if (endDate.value && startDate.value && endDate.value < startDate.value) {
                    endDate.value = startDate.value;
                }
            };

            startDate?.addEventListener('change', syncEndDateMinimum);
            syncEndDateMinimum();

            const updateSummary = () => {
                const selected = [...serviceCheckboxes].filter((checkbox) => checkbox.checked);
                const selectedAddOns = [...addOnCheckboxes].filter((checkbox) => checkbox.checked);
                const servicesAmount = selected.reduce((sum, checkbox) => sum + Number(checkbox.dataset.servicePrice || 0), 0);
                const addOnsAmount = selectedAddOns.reduce((sum, checkbox) => sum + Number(checkbox.dataset.addonPrice || 0), 0);
                const venueAmount = Number(@json((int) $venue->starting_price));
                const amount = venueAmount + servicesAmount + addOnsAmount;

                addOnsCount.textContent = selectedAddOns.length.toString();
                count.textContent = selected.length.toString();
                total.textContent = `${formatter.format(amount)} ${@json($venue->currency)}`;
                servicesSelectedCount.forEach((element) => {
                    element.textContent = selected.length.toString();
                });
                serviceCheckboxes.forEach((checkbox) => {
                    checkbox.disabled = ! checkbox.checked && selected.length >= maxEventServices;
                    checkbox.closest('label')?.classList.toggle('opacity-50', checkbox.disabled);
                });
                selection.innerHTML = '';

                selectedAddOns.slice(0, 5).forEach((checkbox) => {
                    const row = document.createElement('p');
                    row.className = 'rounded-xl bg-white px-3 py-2 ring-1 ring-[#e2eaf8]';
                    row.textContent = checkbox.dataset.addonName;
                    selection.appendChild(row);
                });

                selected.slice(0, 5).forEach((checkbox) => {
                    const row = document.createElement('p');
                    row.className = 'rounded-xl bg-white px-3 py-2 ring-1 ring-[#e2eaf8]';
                    row.textContent = checkbox.dataset.serviceName;
                    selection.appendChild(row);
                });

                if (ticketingCheckbox?.checked) {
                    const row = document.createElement('p');
                    row.className = 'rounded-xl bg-[#eef4ff] px-3 py-2 font-extrabold text-[#2f6bff] ring-1 ring-[#cfe0ff]';
                    row.textContent = 'Billetterie digitale demandée';
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
            addOnCheckboxes.forEach((checkbox) => checkbox.addEventListener('change', updateSummary));
            ticketingCheckbox?.addEventListener('change', updateSummary);
            updateSummary();
        })();
    </script>
</x-layouts.baobaa>
