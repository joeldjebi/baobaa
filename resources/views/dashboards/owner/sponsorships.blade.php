@php
    $statusLabels = ['pending' => 'Validation SAP', 'active' => 'Active', 'paused' => 'En pause', 'cancelled' => 'Annulée', 'rejected' => 'Refusée'];
    $goalLabels = ['visibility' => 'Visibilité', 'booking' => 'Réservations', 'launch' => 'Lancement'];
    $placementLabels = ['home_featured' => 'Accueil premium', 'catalog_top' => 'Haut du catalogue', 'category_boost' => 'Catégorie ciblée'];
@endphp

<x-dashboards.owner-shell title="Sponsoriser mes espaces" subtitle="Créez des campagnes premium pour donner plus de visibilité à vos lieux." active="sponsorships" :owner-profile="$ownerProfile" :active-venues-count="$activeVenuesCount" :pending-bookings-count="$pendingBookingsCount" :confirmed-bookings-count="$confirmedBookingsCount" :gross-revenue="$grossRevenue" :active-subscription="$activeSubscription" :active-deposit-rule="$activeDepositRule" :billing-preference-label="$billingPreferenceLabel">
    @if (session('sponsorship_status'))
        <div class="mb-4 rounded-2xl border border-[#b9d3ff] bg-white px-4 py-3 text-sm font-extrabold text-[#2f6bff]">{{ session('sponsorship_status') }}</div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[420px_1fr]">
        <section class="rounded-[28px] bg-white p-5 shadow-xl shadow-[#173e7a]/8 ring-1 ring-[#dce6f7]">
            <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[#2f6bff]">Nouvelle campagne</p>
            <h2 class="mt-2 text-2xl font-extrabold tracking-[-0.04em] text-[#07152f]">Mettre un espace en avant</h2>
            <p class="mt-2 text-sm font-semibold leading-6 text-[#64708a]">Le SAP valide chaque campagne avant diffusion pour garder une expérience client premium.</p>

            <form method="POST" action="{{ route('owner.sponsorships.store') }}" class="mt-5 space-y-4">
                @csrf
                <label class="block">
                    <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Espace à sponsoriser</span>
                    <select name="venue_id" required class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                        <option value="">Choisir un espace publié</option>
                        @foreach ($venuesForSponsoring as $venue)
                            <option value="{{ $venue->id }}" @selected((string) old('venue_id', request('venue_id')) === (string) $venue->id)>{{ $venue->name }} · {{ $venue->city }}</option>
                        @endforeach
                    </select>
                    @error('venue_id')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                </label>

                <label class="block">
                    <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Nom de campagne</span>
                    <input name="name" value="{{ old('name') }}" required placeholder="Ex. Boost mariage septembre" class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                </label>

                <label class="block">
                    <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Forfait défini par le SAP</span>
                    <select name="sponsorship_plan_id" required class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                        <option value="">Choisir un forfait</option>
                        @foreach ($sponsorshipPlans as $plan)
                            <option value="{{ $plan->id }}" @selected(old('sponsorship_plan_id') == $plan->id)>{{ $plan->name }} · {{ number_format($plan->price, 0, ',', ' ') }} {{ $plan->currency }} · {{ $plan->duration_days }} jour{{ $plan->duration_days > 1 ? 's' : '' }}</option>
                        @endforeach
                    </select>
                    @error('sponsorship_plan_id')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                    @if ($sponsorshipPlans->isEmpty())
                        <span class="mt-2 block text-xs font-bold text-[#b42318]">Aucun forfait sponsoring actif. Le SAP doit en créer un.</span>
                    @endif
                </label>

                <label class="block">
                    <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Objectif</span>
                    <select name="goal" class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                        @foreach ($goalLabels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block">
                    <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Date de début souhaitée</span>
                    <input name="starts_on" type="date" value="{{ old('starts_on', now()->addDay()->toDateString()) }}" required class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                    <span class="mt-2 block text-xs font-bold text-[#64708a]">La date de fin sera calculée automatiquement selon la durée du forfait.</span>
                </label>

                <label class="block">
                    <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Villes ciblées</span>
                    <input name="target_cities" value="{{ old('target_cities') }}" placeholder="Abidjan, Dakar, Cotonou" class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                </label>

                <button class="h-12 w-full rounded-2xl bg-[#2f6bff] px-5 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/24">Envoyer au SAP</button>
            </form>
        </section>

        <section class="rounded-[28px] bg-white p-5 shadow-xl shadow-[#173e7a]/8 ring-1 ring-[#dce6f7]">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-extrabold text-[#07152f]">Campagnes</h2>
                    <p class="mt-1 text-sm font-semibold text-[#64708a]">Suivez budget, validation et performances.</p>
                </div>
                <form method="GET">
                    <select name="status" onchange="this.form.submit()" class="h-11 rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-3 text-sm font-bold">
                        <option value="">Tous les statuts</option>
                        @foreach ($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div class="mt-5 overflow-x-auto rounded-2xl border border-[#edf2fb]">
                <table class="w-full min-w-[860px] text-left text-sm">
                    <thead class="bg-[#f7faff] text-xs font-extrabold uppercase text-[#7d8aa7]">
                        <tr>
                            <th class="px-4 py-3">Campagne</th>
                            <th class="px-4 py-3">Période</th>
                            <th class="px-4 py-3">Budget</th>
                            <th class="px-4 py-3">Statut</th>
                            <th class="px-4 py-3">Performance</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#edf2fb]">
                        @forelse ($campaigns as $campaign)
                            <tr>
                                <td class="px-4 py-4"><p class="font-extrabold text-[#07152f]">{{ $campaign->name }}</p><p class="text-xs font-bold text-[#64708a]">{{ $campaign->venue?->name }} · {{ $placementLabels[$campaign->placement] ?? $campaign->placement }}</p></td>
                                <td class="px-4 py-4 font-bold text-[#52617b]">{{ $campaign->starts_on?->format('d/m/Y') }} - {{ $campaign->ends_on?->format('d/m/Y') }}</td>
                                <td class="px-4 py-4 font-extrabold text-[#07152f]">{{ number_format($campaign->budget_amount, 0, ',', ' ') }} {{ $campaign->currency }}</td>
                                <td class="px-4 py-4"><span class="rounded-full bg-[#eef4ff] px-3 py-1 text-xs font-extrabold text-[#2f6bff]">{{ $statusLabels[$campaign->status] ?? $campaign->status }}</span></td>
                                <td class="px-4 py-4 font-bold text-[#52617b]">{{ number_format($campaign->impressions_count, 0, ',', ' ') }} vues · {{ number_format($campaign->clicks_count, 0, ',', ' ') }} clics</td>
                                <td class="px-4 py-4">
                                    @if (in_array($campaign->status, ['active', 'pending'], true))
                                        <div class="flex justify-end gap-2">
                                            <form method="POST" action="{{ route('owner.sponsorships.status', $campaign) }}">
                                                @csrf
                                                <input type="hidden" name="status" value="paused">
                                                <button class="rounded-full bg-[#eef4ff] px-3 py-2 text-xs font-extrabold text-[#2f6bff]">Pause</button>
                                            </form>
                                            <form method="POST" action="{{ route('owner.sponsorships.status', $campaign) }}">
                                                @csrf
                                                <input type="hidden" name="status" value="cancelled">
                                                <button class="rounded-full bg-[#fff2f2] px-3 py-2 text-xs font-extrabold text-[#b42318]">Annuler</button>
                                            </form>
                                        </div>
                                    @else
                                        <p class="text-right text-xs font-bold text-[#7d8aa7]">Aucune action</p>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center font-semibold text-[#64708a]">Aucune campagne sponsorisée.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-5">{{ $campaigns->links() }}</div>
        </section>
    </div>
</x-dashboards.owner-shell>
