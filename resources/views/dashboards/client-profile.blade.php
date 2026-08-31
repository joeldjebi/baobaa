<x-dashboards.client-shell title="Profil et sécurité" subtitle="Mettez à jour vos informations personnelles et protégez votre compte." active="profile" :client="$client" :upcoming-bookings-count="$upcomingBookingsCount" :confirmed-payments-amount="$confirmedPaymentsAmount" :reserved-venues-count="$reservedVenuesCount" :pending-payments-count="$pendingPaymentsCount">
    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-[26px] border border-white/80 bg-white p-5 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7]">
            @if (session('profile_status'))
                <div class="mb-4 rounded-2xl border border-[#b9d3ff] bg-[#f2f7ff] px-4 py-3 text-sm font-extrabold text-[#2f6bff]">{{ session('profile_status') }}</div>
            @endif
            <h2 class="text-xl font-extrabold tracking-[-0.03em] text-[#07152f]">Informations du profil</h2>
            <form method="POST" action="{{ route('client.profile.update') }}" class="mt-5 grid gap-4">
                @csrf
                @method('PATCH')
                <label>
                    <span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Nom complet</span>
                    <input name="name" value="{{ old('name', $client->name) }}" class="mt-2 w-full rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
                    @error('name')<span class="mt-2 block text-sm font-semibold text-[#b42318]">{{ $message }}</span>@enderror
                </label>
                <label>
                    <span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Email</span>
                    <input value="{{ $client->email }}" disabled class="mt-2 w-full rounded-2xl border border-[#dce6f7] bg-[#edf2fb] px-4 py-3 text-sm font-bold text-[#6f7890]">
                </label>
                <label>
                    <span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Téléphone</span>
                    <input name="phone" value="{{ old('phone', $client->phone) }}" class="mt-2 w-full rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
                    @error('phone')<span class="mt-2 block text-sm font-semibold text-[#b42318]">{{ $message }}</span>@enderror
                </label>
                <button class="justify-self-start rounded-2xl bg-[#2f6bff] px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/20">Enregistrer le profil</button>
            </form>
        </section>

        <section class="rounded-[26px] border border-white/80 bg-white p-5 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7]">
            @if (session('password_status'))
                <div class="mb-4 rounded-2xl border border-[#b9d3ff] bg-[#f2f7ff] px-4 py-3 text-sm font-extrabold text-[#2f6bff]">{{ session('password_status') }}</div>
            @endif
            <h2 class="text-xl font-extrabold tracking-[-0.03em] text-[#07152f]">Mot de passe</h2>
            <form method="POST" action="{{ route('client.password.update') }}" class="mt-5 grid gap-4">
                @csrf
                @method('PUT')
                <label>
                    <span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Mot de passe actuel</span>
                    <input name="current_password" type="password" autocomplete="current-password" class="mt-2 w-full rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
                    @error('current_password')<span class="mt-2 block text-sm font-semibold text-[#b42318]">{{ $message }}</span>@enderror
                </label>
                <label>
                    <span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Nouveau mot de passe</span>
                    <input name="password" type="password" autocomplete="new-password" class="mt-2 w-full rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
                    @error('password')<span class="mt-2 block text-sm font-semibold text-[#b42318]">{{ $message }}</span>@enderror
                </label>
                <label>
                    <span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Confirmer le nouveau mot de passe</span>
                    <input name="password_confirmation" type="password" autocomplete="new-password" class="mt-2 w-full rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
                </label>
                <button class="justify-self-start rounded-2xl bg-[#07152f] px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-[#07152f]/15">Mettre à jour le mot de passe</button>
            </form>
        </section>
    </div>
</x-dashboards.client-shell>
