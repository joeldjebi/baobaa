<x-dashboards.owner-shell title="Disponibilités" subtitle="Visualisez les créneaux ouverts et les périodes bloquées de vos espaces." active="calendar" :owner-profile="$ownerProfile" :active-venues-count="$activeVenuesCount" :pending-bookings-count="$pendingBookingsCount" :confirmed-bookings-count="$confirmedBookingsCount" :gross-revenue="$grossRevenue" :active-subscription="$activeSubscription" :active-deposit-rule="$activeDepositRule" :billing-preference-label="$billingPreferenceLabel">
    <section class="min-w-0 rounded-[26px] border border-white/80 bg-white p-4 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7] sm:p-5">
        <div class="grid gap-4 md:grid-cols-3">
            <div class="min-w-0 rounded-2xl bg-[#f7faff] p-4">
                <p class="text-xs font-extrabold uppercase text-[#7d8aa7]">Créneaux à venir</p>
                <p class="mt-2 text-3xl font-extrabold text-[#07152f]">{{ $availabilities->total() }}</p>
            </div>
            <div class="min-w-0 rounded-2xl bg-[#f7faff] p-4 md:col-span-2">
                <p class="text-sm font-extrabold text-[#151821]">Comment ça marche ?</p>
                <p class="mt-2 text-sm font-semibold leading-6 text-[#6f7890]">Vos espaces sont réservables par défaut. Bloquez les jours où ils ne sont pas disponibles : ils disparaissent des recherches par date et apparaissent en gris dans le calendrier public. Les réservations confirmées bloquent automatiquement leur date.</p>
            </div>
        </div>

        @if (session('calendar_status'))
            <div class="mt-5 rounded-2xl border border-[#b9d3ff] bg-[#f2f7ff] px-4 py-3 text-sm font-extrabold text-[#2f6bff]">{{ session('calendar_status') }}</div>
        @endif

        <x-forms.validation-errors class="mt-5" />

        <form method="POST" action="{{ route('owner.calendar.store') }}" class="mt-5 grid min-w-0 gap-3 rounded-[22px] border border-[#dce6f7] bg-white p-4 md:grid-cols-2 xl:grid-cols-3">
            @csrf
            <p class="text-sm font-extrabold text-[#151821] md:col-span-2 xl:col-span-3">Bloquer ou ouvrir une période</p>
            <label class="min-w-0">
                <span class="text-xs font-extrabold uppercase text-[#7d8aa7]">Espace</span>
                <select name="venue_id" required class="mt-1 w-full min-w-0 rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
                    @foreach ($calendarVenues as $venue)
                        <option value="{{ $venue->id }}" @selected((int) old('venue_id', request('venue_id')) === $venue->id)>{{ $venue->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="min-w-0">
                <span class="text-xs font-extrabold uppercase text-[#7d8aa7]">Action</span>
                <select name="status" class="mt-1 w-full min-w-0 rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
                    <option value="blocked" @selected(old('status', 'blocked') === 'blocked')>Bloquer (indisponible)</option>
                    <option value="available" @selected(old('status') === 'available')>Ouvrir (disponible)</option>
                </select>
            </label>
            <label class="min-w-0">
                <span class="text-xs font-extrabold uppercase text-[#7d8aa7]">Motif (privé)</span>
                <input name="block_reason" value="{{ old('block_reason') }}" placeholder="Travaux, événement privé..." class="mt-1 w-full min-w-0 rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
            </label>
            <label class="min-w-0">
                <span class="text-xs font-extrabold uppercase text-[#7d8aa7]">Du</span>
                <input type="date" name="start_date" required min="{{ now()->toDateString() }}" value="{{ old('start_date') }}" class="mt-1 w-full min-w-0 rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
            </label>
            <label class="min-w-0">
                <span class="text-xs font-extrabold uppercase text-[#7d8aa7]">Au (inclus)</span>
                <input type="date" name="end_date" min="{{ now()->toDateString() }}" value="{{ old('end_date') }}" class="mt-1 w-full min-w-0 rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
            </label>
            <div class="flex items-end">
                <button class="w-full rounded-2xl bg-[#2f6bff] px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/20">Enregistrer</button>
            </div>
        </form>

        <form method="GET" class="mt-5 grid min-w-0 gap-3 rounded-[22px] bg-[#f7faff] p-3 ring-1 ring-[#dce6f7] sm:p-4 md:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_150px_160px_160px_auto]">
            <select name="venue_id" class="min-w-0 rounded-2xl border border-[#dce6f7] bg-white px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
                <option value="">Tous les espaces</option>
                @foreach ($calendarVenues as $venue)
                    <option value="{{ $venue->id }}" @selected((int) request('venue_id') === $venue->id)>{{ $venue->name }}</option>
                @endforeach
            </select>
            <select name="status" class="min-w-0 rounded-2xl border border-[#dce6f7] bg-white px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
                <option value="">Tous</option>
                <option value="available" @selected(request('status') === 'available')>Disponible</option>
                <option value="blocked" @selected(request('status') === 'blocked')>Bloqué</option>
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="min-w-0 rounded-2xl border border-[#dce6f7] bg-white px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="min-w-0 rounded-2xl border border-[#dce6f7] bg-white px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
            <button class="rounded-2xl bg-[#2f6bff] px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/20 md:col-span-2 xl:col-span-1">Filtrer</button>
        </form>

        <div class="mt-5 grid gap-3">
            @forelse ($availabilities as $availability)
                <article class="flex min-w-0 flex-col gap-3 rounded-2xl border border-[#edf2fb] bg-[#fbfcff] p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <p class="break-words font-extrabold text-[#151821]">{{ $availability->venue?->name }}</p>
                        <p class="mt-1 text-sm font-semibold text-[#6f7890]">{{ $availability->available_date?->format('d/m/Y') }} · {{ substr($availability->starts_at, 0, 5) }} - {{ substr($availability->ends_at, 0, 5) }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span @class([
                            'w-max rounded-full px-3 py-1 text-xs font-extrabold',
                            'bg-[#ecfdf5] text-[#047857]' => $availability->status === 'available',
                            'bg-[#fff6f6] text-[#b42318]' => $availability->status !== 'available',
                        ])>{{ $availability->status === 'available' ? 'Disponible' : 'Bloqué' }}{{ $availability->block_reason ? ' · '.$availability->block_reason : '' }}</span>
                        <form method="POST" action="{{ route('owner.calendar.destroy', $availability) }}">
                            @csrf
                            @method('DELETE')
                            <button class="rounded-full bg-white px-3 py-1 text-xs font-extrabold text-[#52617b] ring-1 ring-[#dce6f7] hover:text-[#b42318]">Retirer</button>
                        </form>
                    </div>
                </article>
            @empty
                <p class="rounded-2xl border border-dashed border-[#cbd8f4] bg-[#fbfcff] p-5 text-sm font-semibold text-[#6f7890]">Aucune disponibilité à venir.</p>
            @endforelse
        </div>

        <div class="mt-5">{{ $availabilities->links() }}</div>
    </section>
</x-dashboards.owner-shell>
