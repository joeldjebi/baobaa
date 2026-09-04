@php
    $typeLabels = [
        'percentage' => 'Pourcentage',
        'fixed' => 'Montant fixe',
    ];
@endphp

<x-dashboards.sap-shell title="Acomptes réservation" subtitle="Définissez le montant que chaque client doit payer avant confirmation, partenaire par partenaire." active="deposit-rules" :owners-count="$ownersCount" :clients-count="$clientsCount" :published-venues-count="$publishedVenuesCount" :pending-access-requests-count="$pendingAccessRequestsCount" :pending-sponsorships-count="$pendingSponsorshipsCount" :gross-payments-amount="$grossPaymentsAmount" :active-bookings-count="$activeBookingsCount">
    <div class="grid w-full max-w-full gap-6">
        <form method="POST" action="{{ route('sap.deposit-rules.store') }}" class="w-full max-w-full rounded-[28px] bg-white p-5 shadow-xl shadow-[#173e7a]/8 ring-1 ring-[#dce6f7]">
            @csrf
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-xl font-extrabold text-[#07152f]">Nouvelle règle</h2>
                    <p class="mt-1 text-sm font-semibold leading-6 text-[#64708a]">Une nouvelle règle active désactive automatiquement l’ancienne règle active du même partenaire.</p>
                </div>
                <span class="rounded-full bg-[#eef4ff] px-3 py-1 text-xs font-extrabold text-[#2f6bff]">SAP</span>
            </div>

            <div class="mt-5 space-y-3">
                <label class="block">
                    <span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Partenaire concerné</span>
                    <select name="owner_profile_id" required class="mt-2 h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                        <option value="">Choisir un partenaire</option>
                        @foreach ($owners as $owner)
                            <option value="{{ $owner->id }}" @selected((int) old('owner_profile_id') === $owner->id)>{{ $owner->business_name }} · {{ $owner->city }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block">
                    <span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Nom interne</span>
                    <input name="name" value="{{ old('name') }}" required placeholder="Ex : Acompte standard 30%" class="mt-2 h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                </label>

                <div class="grid grid-cols-2 gap-3">
                    <label class="block">
                        <span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Mode</span>
                        <select name="deposit_type" class="mt-2 h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                            <option value="percentage" @selected(old('deposit_type') === 'percentage')>Pourcentage</option>
                            <option value="fixed" @selected(old('deposit_type') === 'fixed')>Montant fixe</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Pourcentage</span>
                        <input name="percentage_rate" type="number" step="0.01" min="0" max="100" value="{{ old('percentage_rate') }}" placeholder="30" class="mt-2 h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                    </label>
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    <input name="fixed_amount" type="number" min="0" value="{{ old('fixed_amount') }}" placeholder="Montant fixe" class="h-12 rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                    <input name="minimum_amount" type="number" min="0" value="{{ old('minimum_amount') }}" placeholder="Minimum" class="h-12 rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                    <input name="maximum_amount" type="number" min="0" value="{{ old('maximum_amount') }}" placeholder="Maximum" class="h-12 rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <input name="starts_at" type="date" value="{{ old('starts_at') }}" class="h-12 rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                    <input name="ends_at" type="date" value="{{ old('ends_at') }}" class="h-12 rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                </div>

                @if ($errors->any())
                    <div class="rounded-2xl border border-[#ffd0d0] bg-[#fff6f6] px-4 py-3 text-sm font-bold text-[#b42318]">{{ $errors->first() }}</div>
                @endif

                <button class="h-12 w-full rounded-2xl bg-[#2f6bff] text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/20">Ajouter la règle</button>
            </div>
        </form>

        <section class="rounded-[28px] bg-white p-5 shadow-xl shadow-[#173e7a]/8 ring-1 ring-[#dce6f7]">
            <form method="GET" class="mb-4 grid gap-3 rounded-2xl bg-[#f7faff] p-3 ring-1 ring-[#dce6f7] md:grid-cols-[1fr_150px_auto]">
                <select name="owner_profile_id" class="h-11 min-w-0 rounded-2xl border border-[#dce6f7] bg-white px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                    <option value="">Tous les partenaires</option>
                    @foreach ($owners as $owner)
                        <option value="{{ $owner->id }}" @selected((int) request('owner_profile_id') === $owner->id)>{{ $owner->business_name }}</option>
                    @endforeach
                </select>
                <select name="status" class="h-11 min-w-0 rounded-2xl border border-[#dce6f7] bg-white px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                    <option value="">Tous</option>
                    <option value="active" @selected(request('status') === 'active')>Actives</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactives</option>
                </select>
                <button class="h-11 rounded-2xl bg-[#07152f] px-5 text-sm font-extrabold text-white">Filtrer</button>
            </form>

            <div class="overflow-x-auto rounded-2xl border border-[#edf2fb]">
                <table class="w-full min-w-[920px] text-left text-sm">
                    <thead class="bg-[#f7faff] text-xs font-extrabold uppercase text-[#7d8aa7]">
                        <tr><th class="px-4 py-3">Partenaire</th><th class="px-4 py-3">Règle</th><th class="px-4 py-3">Calcul</th><th class="px-4 py-3">Bornes</th><th class="px-4 py-3">Validité</th><th class="px-4 py-3">Statut</th><th class="px-4 py-3 text-right">Action</th></tr>
                    </thead>
                    <tbody class="divide-y divide-[#edf2fb]">
                        @forelse ($rules as $rule)
                            <tr class="transition hover:bg-[#fbfcff]">
                                <td class="px-4 py-4 font-extrabold text-[#07152f]">{{ $rule->ownerProfile?->business_name }}</td>
                                <td class="px-4 py-4 font-bold text-[#52617b]">{{ $rule->name }}</td>
                                <td class="px-4 py-4 font-bold text-[#52617b]">{{ $typeLabels[$rule->deposit_type] ?? 'Règle' }} · {{ $rule->deposit_type === 'fixed' ? number_format((int) $rule->fixed_amount, 0, ',', ' ').' '.$rule->currency : $rule->percentage_rate.'%' }}</td>
                                <td class="px-4 py-4 font-bold text-[#52617b]">{{ number_format((int) $rule->minimum_amount, 0, ',', ' ') }} - {{ $rule->maximum_amount ? number_format((int) $rule->maximum_amount, 0, ',', ' ') : 'sans plafond' }} {{ $rule->currency }}</td>
                                <td class="px-4 py-4 font-semibold text-[#64708a]">{{ $rule->starts_at?->format('d/m/Y') ?? 'Immédiate' }} · {{ $rule->ends_at?->format('d/m/Y') ?? 'sans fin' }}</td>
                                <td class="px-4 py-4"><span class="rounded-full px-3 py-1 text-xs font-extrabold {{ $rule->is_active ? 'bg-[#eaf8f0] text-[#087443]' : 'bg-[#f3f5f9] text-[#64708a]' }}">{{ $rule->is_active ? 'Active' : 'Inactive' }}</span></td>
                                <td class="px-4 py-4 text-right">
                                    <x-dashboards.action-menu>
                                        <form method="POST" action="{{ route('sap.deposit-rules.toggle', $rule) }}">
                                            @csrf
                                            <button class="w-full rounded-xl px-3 py-2 text-left {{ $rule->is_active ? 'text-[#b42318] hover:bg-[#fff6f6]' : 'text-[#2f6bff] hover:bg-[#f2f7ff]' }}">{{ $rule->is_active ? 'Désactiver' : 'Activer' }}</button>
                                        </form>
                                    </x-dashboards.action-menu>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-10 text-center font-semibold text-[#64708a]">Aucune règle d’acompte configurée.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-5">{{ $rules->links() }}</div>
        </section>
    </div>
</x-dashboards.sap-shell>
