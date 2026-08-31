<x-dashboards.sap-shell title="Vue d’ensemble" subtitle="Pilotage global des accès, revenus, espaces et validations." active="overview" :owners-count="$ownersCount ?? 0" :clients-count="$clientsCount ?? 0" :published-venues-count="$publishedVenuesCount ?? 0" :pending-access-requests-count="$pendingAccessRequestsCount ?? 0" :pending-sponsorships-count="$pendingSponsorshipsCount ?? 0" :gross-payments-amount="$grossPaymentsAmount ?? 0" :active-bookings-count="$activeBookingsCount ?? 0">
    <div class="rounded-[28px] bg-white p-5 shadow-xl shadow-[#173e7a]/8 ring-1 ring-[#dce6f7]">
        <p class="text-lg font-extrabold text-[#07152f]">Dashboard SAP</p>
        <p class="mt-2 text-sm font-semibold text-[#64708a]">Utilisez les pages du menu pour gérer les partenaires, espaces, clients, réservations, paiements, commissions et forfaits.</p>
    </div>
</x-dashboards.sap-shell>
