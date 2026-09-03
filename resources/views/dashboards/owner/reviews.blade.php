@php
    $reviewStatusLabels = ['pending' => 'En validation', 'approved' => 'Publié', 'rejected' => 'Refusé'];
@endphp

<x-dashboards.owner-shell title="Avis clients" subtitle="Suivez la réputation de vos espaces et les commentaires issus de vraies réservations." active="reviews" :owner-profile="$ownerProfile" :active-venues-count="$activeVenuesCount" :pending-bookings-count="$pendingBookingsCount" :confirmed-bookings-count="$confirmedBookingsCount" :gross-revenue="$grossRevenue" :active-subscription="$activeSubscription" :active-deposit-rule="$activeDepositRule" :billing-preference-label="$billingPreferenceLabel">
    <section class="rounded-[26px] border border-white/80 bg-white p-5 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7]">
        <div class="grid gap-4 md:grid-cols-[220px_1fr]">
            <div class="rounded-[22px] bg-[#07152f] p-5 text-white">
                <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[#8dc1ff]">Note moyenne</p>
                <p class="mt-3 text-5xl font-extrabold tracking-[-0.06em]">{{ number_format($averageRating, 1, ',', ' ') }}</p>
                <p class="mt-2 text-sm font-bold text-white/65">{{ $reviewsCount }} avis au total</p>
            </div>
            <div class="grid gap-3">
                @forelse ($reviews as $review)
                    <article class="rounded-2xl border border-[#edf2fb] bg-[#fbfcff] p-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="font-extrabold text-[#151821]">{{ $review->title ?? 'Avis client' }}</p>
                                <p class="mt-1 text-sm font-semibold text-[#6f7890]">{{ $review->venue?->name }} · {{ $review->client?->name }}</p>
                            </div>
                            <span class="rounded-full bg-[#eef4ff] px-3 py-1 text-xs font-extrabold text-[#2f6bff]">{{ $review->rating }} ★ · {{ $reviewStatusLabels[$review->status] ?? 'À suivre' }}</span>
                        </div>
                        <p class="mt-3 text-sm font-medium leading-6 text-[#52617b]">{{ $review->comment }}</p>
                    </article>
                @empty
                    <p class="rounded-2xl border border-dashed border-[#cbd8f4] bg-[#fbfcff] p-5 text-sm font-semibold text-[#6f7890]">Aucun avis client pour le moment.</p>
                @endforelse
            </div>
        </div>

        <div class="mt-5">{{ $reviews->links() }}</div>
    </section>
</x-dashboards.owner-shell>
