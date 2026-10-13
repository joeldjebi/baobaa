@php
    $isAuthenticatedMode = ($mode ?? 'guest') === 'authenticated';
    $action = $isAuthenticatedMode ? route('portals.owner.request') : route('owner.register.store');
    $countryOptions = app(\App\Services\LocationOptionsService::class)->countries();
    $partnerConditions ??= ['commission' => null, 'plans' => []];
@endphp

<x-layouts.baobaa title="Devenir partenaire - BAOBAA">
    <main class="min-h-screen bg-[#eef3ff] text-[#151821]">
        <section class="grid min-h-screen lg:grid-cols-[0.95fr_1.05fr]">
            <div class="relative order-2 overflow-hidden bg-[#07152f] px-6 py-10 text-white sm:px-10 lg:order-1 lg:px-14">
                <div class="absolute inset-0">
                    <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(7,21,47,.98),rgba(47,107,255,.72))]"></div>
                </div>

                <div class="relative z-10 mx-auto flex h-full max-w-xl flex-col justify-center gap-8">
                    <a href="{{ url('/') }}" class="hidden items-center justify-start lg:flex">
                        <img src="{{ asset('images/baobaa.jpg') }}" alt="BAOBAA" class="h-10 w-auto max-w-[160px] rounded-full object-contain bg-white/90 p-1 shadow-lg shadow-[#2f6bff]/20" loading="lazy">
                    </a>

                    <div class="max-w-2xl">
                        <p class="text-sm font-extrabold uppercase tracking-[0.24em] text-[#8dc1ff]">Devenir partenaire</p>
                        <h1 class="mt-5 text-4xl font-extrabold leading-tight tracking-[-0.05em] sm:text-5xl">Louez votre espace à des organisateurs d’événements.</h1>
                        <p class="mt-5 max-w-xl text-base font-semibold leading-8 text-white/72">L’inscription est gratuite. Nous vérifions votre profil, puis vous publiez vos espaces et recevez des demandes de réservation.</p>

                        <ol class="mt-8 grid gap-3 text-sm font-bold text-white/85">
                            <li class="flex items-start gap-3"><span class="grid size-7 shrink-0 place-items-center rounded-full bg-white/15 text-xs font-extrabold">1</span>Vous remplissez ce formulaire (2 minutes).</li>
                            <li class="flex items-start gap-3"><span class="grid size-7 shrink-0 place-items-center rounded-full bg-white/15 text-xs font-extrabold">2</span>Notre équipe vérifie votre profil et vous contacte.</li>
                            <li class="flex items-start gap-3"><span class="grid size-7 shrink-0 place-items-center rounded-full bg-white/15 text-xs font-extrabold">3</span>Vous publiez vos espaces et recevez vos premières demandes.</li>
                        </ol>
                    </div>

                    <div class="rounded-[24px] border border-white/12 bg-white/10 p-5 backdrop-blur">
                        <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#8dc1ff]">Ce que vous payez</p>
                        <ul class="mt-3 space-y-2 text-sm font-semibold leading-6 text-white/85">
                            @if ($partnerConditions['commission'])
                                <li>• <strong class="text-white">Commission BAOBAA :</strong> {{ $partnerConditions['commission'] }}. Aucun frais si vous ne recevez pas de réservation.</li>
                            @endif
                            <li>• <strong class="text-white">Acompte :</strong> le client paie un acompte pour bloquer la date. Les montants vous sont reversés après déduction de la commission.</li>
                            <li>• <strong class="text-white">Vos prix :</strong> vous fixez librement vos tarifs, vos options et vos conditions d’annulation.</li>
                        </ul>

                        @if (count($partnerConditions['plans']) > 0)
                            <p class="mt-5 text-xs font-extrabold uppercase tracking-[0.18em] text-[#8dc1ff]">Formules disponibles</p>
                            <div @class(['mt-3 grid gap-2', 'sm:grid-cols-2' => count($partnerConditions['plans']) === 2, 'sm:grid-cols-3' => count($partnerConditions['plans']) >= 3])>
                                @foreach ($partnerConditions['plans'] as $plan)
                                    <div class="rounded-2xl bg-white/10 p-3 ring-1 ring-white/10">
                                        <p class="text-sm font-extrabold text-white">{{ $plan['name'] }}</p>
                                        <p class="text-xs font-bold text-[#bcd2ff]">{{ $plan['price'] }}</p>
                                        <p class="mt-2 text-xs font-semibold text-white/75">{{ $plan['venues'] }}</p>
                                        @if ($plan['commission'])
                                            <p class="text-xs font-semibold text-white/75">{{ $plan['commission'] }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="order-1 flex items-center justify-center px-5 py-8 sm:px-8 lg:order-2 lg:py-10">
                <div class="w-full max-w-2xl">
                    <div class="mb-5 flex items-center justify-between lg:hidden">
                        <a href="{{ url('/') }}"><img src="{{ asset('images/baobaa.jpg') }}" alt="BAOBAA" class="h-10 w-auto rounded-xl object-contain"></a>
                        <a href="{{ url('/') }}" class="text-sm font-extrabold text-[#2f6bff]">← Retour au site</a>
                    </div>
                    @if ($pendingRequest)
                        <div class="mb-5 rounded-[22px] border border-[#b9d3ff] bg-white p-5 shadow-xl shadow-[#173e7a]/8">
                            <p class="text-sm font-extrabold text-[#2f6bff]">Votre demande est déjà en cours de validation.</p>
                            <p class="mt-2 text-sm font-semibold leading-6 text-[#64708a]">Nous vous contactons dès que votre profil est vérifié.</p>
                        </div>
                    @endif

                    <div class="mb-7">
                        <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#2f6bff]">{{ $isAuthenticatedMode ? 'Compte connecté' : 'Nouveau partenaire' }}</p>
                        <h2 class="mt-2 text-3xl font-extrabold tracking-[-0.04em] text-[#07152f]">Demande d’accès partenaire</h2>
                        <p class="mt-2 text-sm font-semibold leading-6 text-[#64708a]">Quelques informations suffisent. Les champs facultatifs peuvent être complétés plus tard.</p>
                    </div>

                    <form method="POST" action="{{ $action }}" class="rounded-[28px] bg-white p-5 shadow-2xl shadow-[#173e7a]/10 ring-1 ring-[#dce6f7] sm:p-6">
                        @csrf
                        <x-forms.validation-errors class="mb-5" />

                        @unless ($isAuthenticatedMode)
                            <div class="grid gap-4 sm:grid-cols-2">
                                <label class="block">
                                    <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Votre nom</span>
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
                                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Vous êtes</span>
                                <select name="applicant_type" required class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                                    <option value="company" @selected(old('applicant_type', $ownerProfile?->owner_type) === 'company')>Une entreprise ou une organisation</option>
                                    <option value="individual" @selected(old('applicant_type', $ownerProfile?->owner_type) === 'individual')>Un particulier ou un indépendant</option>
                                </select>
                            </label>
                            <label class="block">
                                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Nom de votre établissement</span>
                                <input name="business_name" value="{{ old('business_name', $ownerProfile?->business_name) }}" required class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                                @error('business_name')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                            </label>
                        </div>

                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <label class="block">
                                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Raison sociale <span class="font-semibold text-[#8a94aa]">(facultatif)</span></span>
                                <input name="legal_name" value="{{ old('legal_name', $ownerProfile?->legal_name) }}" class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                            </label>
                            <label class="block">
                                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">N° RCCM ou fiscal <span class="font-semibold text-[#8a94aa]">(facultatif)</span></span>
                                <input name="tax_identifier" value="{{ old('tax_identifier', $ownerProfile?->tax_identifier) }}" class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                            </label>
                        </div>

                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <label class="block">
                                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Pays</span>
                                @php
                                    $selectedCountry = strtoupper(old('country_code', $ownerProfile?->country_code ?? 'CI'));
                                @endphp
                                @if (count($countryOptions) > 0)
                                    <select name="country_code" required class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                                        @foreach ($countryOptions as $code => $countryName)
                                            <option value="{{ $code }}" @selected($selectedCountry === $code)>{{ $countryName }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input name="country_code" value="{{ $selectedCountry }}" required maxlength="2" class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold uppercase outline-none focus:border-[#2f6bff]">
                                @endif
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
                            <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Parlez-nous de vos espaces <span class="font-semibold text-[#8a94aa]">(facultatif)</span></span>
                            <textarea name="motivation" rows="3" placeholder="Type de lieu, capacité, ville..."  class="w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 py-3 text-sm font-bold leading-6 outline-none focus:border-[#2f6bff]">{{ old('motivation') }}</textarea>
                        </label>

                        <x-forms.terms-acceptance class="mt-5" />
                        <button class="mt-5 h-12 w-full rounded-2xl bg-[#2f6bff] px-5 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/24 transition hover:-translate-y-0.5 hover:bg-[#2258df]">Envoyer ma demande</button>
                    </form>
                </div>
            </div>
        </section>
    </main>
</x-layouts.baobaa>
