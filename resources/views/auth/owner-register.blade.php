@php
    $isAuthenticatedMode = ($mode ?? 'guest') === 'authenticated';
    $action = $isAuthenticatedMode ? route('portals.owner.request') : route('owner.register.store');
@endphp

<x-layouts.baobaa title="Devenir partenaire - BAOBAA">
    <main class="min-h-screen bg-[#eef3ff] text-[#151821]">
        <section class="grid min-h-screen lg:grid-cols-[0.95fr_1.05fr]">
            <div class="relative overflow-hidden bg-[#07152f] px-6 py-8 text-white sm:px-10 lg:px-14">
                <div class="absolute inset-0">
                    <img src="https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=1600&q=80" alt="" class="h-full w-full object-cover opacity-28">
                    <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(7,21,47,.98),rgba(47,107,255,.72))]"></div>
                </div>

                <div class="relative z-10 flex h-full min-h-[48vh] flex-col justify-between">
                    <a href="{{ url('/') }}" class="flex items-center gap-3">
                        <span class="grid size-10 place-items-center rounded-full bg-white text-sm font-black text-[#0d47a1]">B</span>
                        <span class="text-lg font-semibold tracking-[0.22em]">BAOBAA</span>
                    </a>

                    <div class="max-w-2xl py-14">
                        <p class="text-sm font-extrabold uppercase tracking-[0.24em] text-[#8dc1ff]">Candidature partenaire</p>
                        <h1 class="mt-5 text-4xl font-extrabold leading-tight tracking-[-0.05em] sm:text-6xl">Faites découvrir vos lieux aux bons organisateurs.</h1>
                        <p class="mt-5 max-w-xl text-base font-semibold leading-8 text-white/72">Le SAP vérifie chaque profil avant d’ouvrir le portail propriétaire afin de protéger les clients, les paiements et la qualité du catalogue BAOBAA.</p>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-3">
                        @foreach (['Dossier vérifié', 'Accès sécurisé', 'Mise en avant possible'] as $item)
                            <div class="rounded-2xl border border-white/12 bg-white/10 px-4 py-3 text-sm font-extrabold backdrop-blur">{{ $item }}</div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-center px-5 py-10 sm:px-8">
                <div class="w-full max-w-2xl">
                    @if ($pendingRequest)
                        <div class="mb-5 rounded-[22px] border border-[#b9d3ff] bg-white p-5 shadow-xl shadow-[#173e7a]/8">
                            <p class="text-sm font-extrabold text-[#2f6bff]">Votre demande est déjà en cours de validation.</p>
                            <p class="mt-2 text-sm font-semibold leading-6 text-[#64708a]">Le SAP analysera vos informations avant d’activer votre espace propriétaire.</p>
                        </div>
                    @endif

                    <div class="mb-7">
                        <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#2f6bff]">{{ $isAuthenticatedMode ? 'Compte connecté' : 'Nouveau partenaire' }}</p>
                        <h2 class="mt-2 text-3xl font-extrabold tracking-[-0.04em] text-[#07152f]">Demande d’accès PEE</h2>
                        <p class="mt-2 text-sm font-semibold leading-6 text-[#64708a]">Renseignez des informations exactes. Elles seront utilisées par le SAP pour valider votre profil partenaire.</p>
                    </div>

                    <form method="POST" action="{{ $action }}" class="rounded-[28px] bg-white p-5 shadow-2xl shadow-[#173e7a]/10 ring-1 ring-[#dce6f7] sm:p-6">
                        @csrf

                        @unless ($isAuthenticatedMode)
                            <div class="grid gap-4 sm:grid-cols-2">
                                <label class="block">
                                    <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Nom du responsable</span>
                                    <input name="name" value="{{ old('name') }}" required autocomplete="name" class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                                    @error('name')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                                </label>
                                <label class="block">
                                    <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Téléphone WhatsApp</span>
                                    <input name="phone" value="{{ old('phone') }}" required autocomplete="tel" class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                                    @error('phone')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                                </label>
                            </div>

                            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                <label class="block">
                                    <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Adresse email</span>
                                    <input name="email" type="email" value="{{ old('email') }}" required autocomplete="email" class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                                    @error('email')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                                </label>
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <label class="block">
                                        <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Mot de passe</span>
                                        <input name="password" type="password" required autocomplete="new-password" class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                                    </label>
                                    <label class="block">
                                        <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Confirmation</span>
                                        <input name="password_confirmation" type="password" required autocomplete="new-password" class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                                    </label>
                                </div>
                            </div>
                            @error('password')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                        @endunless

                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <label class="block">
                                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Type de partenaire</span>
                                <select name="applicant_type" required class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                                    <option value="company" @selected(old('applicant_type', $ownerProfile?->owner_type) === 'company')>Entreprise ou organisation</option>
                                    <option value="individual" @selected(old('applicant_type', $ownerProfile?->owner_type) === 'individual')>Indépendant</option>
                                </select>
                            </label>
                            <label class="block">
                                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Nom commercial</span>
                                <input name="business_name" value="{{ old('business_name', $ownerProfile?->business_name) }}" required class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                                @error('business_name')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                            </label>
                        </div>

                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <label class="block">
                                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Nom légal</span>
                                <input name="legal_name" value="{{ old('legal_name', $ownerProfile?->legal_name) }}" class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                            </label>
                            <label class="block">
                                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Identifiant fiscal ou RCCM</span>
                                <input name="tax_identifier" value="{{ old('tax_identifier', $ownerProfile?->tax_identifier) }}" class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                            </label>
                        </div>

                        <div class="mt-4 grid gap-4 sm:grid-cols-[120px_1fr_1fr]">
                            <label class="block">
                                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Pays</span>
                                <input name="country_code" value="{{ old('country_code', $ownerProfile?->country_code ?? 'CI') }}" required maxlength="2" class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold uppercase outline-none focus:border-[#2f6bff]">
                            </label>
                            <label class="block">
                                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Ville</span>
                                <input name="city" value="{{ old('city', $ownerProfile?->city) }}" required class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                            </label>
                            @if ($isAuthenticatedMode)
                                <label class="block">
                                    <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Téléphone WhatsApp</span>
                                    <input name="whatsapp_phone" value="{{ old('whatsapp_phone', $ownerProfile?->whatsapp_phone ?? auth()->user()->phone) }}" required class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                                </label>
                            @endif
                        </div>

                        <label class="mt-4 block">
                            <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Pourquoi voulez-vous rejoindre BAOBAA ?</span>
                            <textarea name="motivation" rows="4" class="w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 py-3 text-sm font-bold leading-6 outline-none focus:border-[#2f6bff]">{{ old('motivation') }}</textarea>
                        </label>

                        <button class="mt-5 h-12 w-full rounded-2xl bg-[#2f6bff] px-5 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/24 transition hover:-translate-y-0.5 hover:bg-[#2258df]">Envoyer au SAP</button>
                    </form>
                </div>
            </div>
        </section>
    </main>
</x-layouts.baobaa>
