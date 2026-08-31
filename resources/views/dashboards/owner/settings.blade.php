<x-dashboards.owner-shell title="Paramètres partenaire" subtitle="Configurez votre identité publique, votre compte de reversement et votre mode de paiement à BAOBAA." active="settings" :owner-profile="$ownerProfile" :active-venues-count="$activeVenuesCount" :pending-bookings-count="$pendingBookingsCount" :confirmed-bookings-count="$confirmedBookingsCount" :gross-revenue="$grossRevenue" :active-subscription="$activeSubscription" :billing-preference-label="$billingPreferenceLabel">
    @if (session('settings_status'))
        <div class="mb-4 rounded-2xl border border-[#b9d3ff] bg-[#f2f7ff] px-4 py-3 text-sm font-extrabold text-[#2f6bff]">{{ session('settings_status') }}</div>
    @endif

    <form method="POST" action="{{ route('owner.settings.update') }}" class="grid gap-6 xl:grid-cols-[1fr_380px]">
        @csrf
        <section class="rounded-[26px] border border-white/80 bg-white p-5 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7]">
            <h2 class="text-xl font-extrabold tracking-[-0.03em] text-[#07152f]">Informations publiques</h2>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <label>
                    <span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Nom visible par les clients</span>
                    <input name="business_name" value="{{ old('business_name', $ownerProfile->business_name) }}" class="mt-2 w-full rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
                </label>
                <label>
                    <span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Ville principale</span>
                    <input name="city" value="{{ old('city', $ownerProfile->city) }}" class="mt-2 w-full rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
                </label>
                <label>
                    <span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Pays</span>
                    <input name="country_code" maxlength="2" value="{{ old('country_code', $ownerProfile->country_code) }}" class="mt-2 w-full rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold uppercase outline-none focus:border-[#2f6bff]">
                </label>
                <label>
                    <span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">WhatsApp commercial</span>
                    <input name="whatsapp_phone" value="{{ old('whatsapp_phone', $ownerProfile->whatsapp_phone) }}" class="mt-2 w-full rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
                </label>
            </div>

            <div class="mt-7 border-t border-[#edf2fb] pt-5">
                <h2 class="text-xl font-extrabold tracking-[-0.03em] text-[#07152f]">Compte de reversement</h2>
                <p class="mt-1 text-sm font-semibold text-[#6f7890]">C’est le compte sur lequel BAOBAA versera vos gains après les réservations terminées.</p>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <label>
                        <span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Moyen de réception</span>
                        <select name="payout_provider" class="mt-2 w-full rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
                            @foreach (['mobile_money' => 'Mobile Money', 'bank_transfer' => 'Virement bancaire', 'wave' => 'Wave'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('payout_provider', $ownerProfile->payout_provider) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Numéro ou compte</span>
                        <input name="payout_account_reference" value="{{ old('payout_account_reference', $ownerProfile->payout_account_reference) }}" placeholder="+225..." class="mt-2 w-full rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
                    </label>
                </div>
            </div>

            <div class="mt-7 border-t border-[#edf2fb] pt-5">
                <h2 class="text-xl font-extrabold tracking-[-0.03em] text-[#07152f]">Paiement à BAOBAA</h2>
                <div class="mt-4 grid gap-3 md:grid-cols-3">
                    @foreach (['commission' => 'Commission seulement', 'subscription' => 'Abonnement seulement', 'hybrid' => 'Abonnement + commission'] as $value => $label)
                        <label class="cursor-pointer rounded-2xl border border-[#dce6f7] bg-[#f7faff] p-4 transition has-[:checked]:border-[#2f6bff] has-[:checked]:bg-[#eef4ff]">
                            <input type="radio" name="billing_preference" value="{{ $value }}" class="accent-[#2f6bff]" @checked(old('billing_preference', $ownerProfile->billing_preference ?? 'commission') === $value)>
                            <span class="ml-2 text-sm font-extrabold text-[#151821]">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <button class="mt-6 rounded-2xl bg-[#2f6bff] px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/20">Enregistrer mes paramètres</button>
        </section>

        <aside class="space-y-4">
            <div class="rounded-[26px] border border-white/80 bg-[#07152f] p-5 text-white shadow-xl shadow-[#07152f]/12">
                <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[#8dc1ff]">État du compte</p>
                <h2 class="mt-2 text-2xl font-extrabold tracking-[-0.04em]">{{ $ownerProfile->verification_status->value === 'verified' ? 'Profil vérifié' : 'Validation en cours' }}</h2>
                <p class="mt-3 text-sm font-semibold leading-6 text-white/70">Votre page partenaire utilise un identifiant public sécurisé. Les informations privées ne sont pas affichées aux clients.</p>
            </div>

            <div class="rounded-[26px] border border-white/80 bg-white p-5 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7]">
                <h2 class="text-lg font-extrabold text-[#07152f]">Formules disponibles</h2>
                <div class="mt-4 space-y-3">
                    @foreach ($plans as $plan)
                        <div class="rounded-2xl bg-[#f7faff] p-4 ring-1 ring-[#dce6f7]">
                            <p class="text-sm font-extrabold text-[#151821]">{{ $plan->name }}</p>
                            <p class="mt-1 text-xs font-bold text-[#6f7890]">{{ number_format($plan->price, 0, ',', ' ') }} {{ $plan->currency }} / mois</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </aside>
    </form>
</x-dashboards.owner-shell>
