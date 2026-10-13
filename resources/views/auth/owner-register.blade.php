@php
    $isAuthenticatedMode = ($mode ?? 'guest') === 'authenticated';
    $action = $isAuthenticatedMode ? route('portals.owner.request') : route('owner.register.store');
    $countryOptions = app(\App\Services\LocationOptionsService::class)->countries();
    $partnerConditions ??= ['commission' => null, 'plans' => []];
    $selectedCountry = strtoupper(old('country_code', $ownerProfile?->country_code ?? 'CI'));
    $selectedApplicantType = old('applicant_type', $ownerProfile?->owner_type ?? 'company');
    $labelClass = 'mb-1 block text-xs font-bold text-[#17233f]';
    $inputClass = 'h-11 w-full rounded-xl border border-[#dce6f7] bg-[#f8fbff] px-3.5 text-sm font-semibold text-[#07152f] outline-none transition focus:border-[#2f6bff] focus:bg-white';
@endphp

<x-layouts.baobaa title="Devenir partenaire - BAOBAA">
    <x-navigation.public-header active="partners" />
    <main class="bg-[#eef3ff] text-[#151821] lg:h-[calc(100vh-64px)] lg:min-h-[660px]">
        <section class="grid h-full lg:grid-cols-[0.85fr_1.15fr]">
            <div class="relative hidden overflow-hidden bg-[#07152f] px-14 text-white lg:flex lg:items-center">
                <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(7,21,47,.98),rgba(47,107,255,.72))]" aria-hidden="true"></div>

                <div class="relative z-10 max-w-md">
                    <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#8dc1ff]">Propriétaire d’espace</p>
                    <h1 class="mt-3 text-3xl font-extrabold leading-tight sm:text-4xl">Louez votre lieu à des organisateurs d’événements.</h1>
                    <p class="mt-3 text-sm font-medium leading-6 text-white/75">Inscription gratuite. Nous vérifions votre profil, puis vous publiez vos espaces et recevez des demandes.</p>

                    <div class="mt-6 rounded-2xl border border-white/12 bg-white/10 p-4 text-sm backdrop-blur">
                        <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[#8dc1ff]">Ce que vous payez</p>
                        @if ($partnerConditions['commission'])
                            <p class="mt-2 font-medium leading-6 text-white/85"><strong class="text-white">Commission :</strong> {{ $partnerConditions['commission'] }}, uniquement si vous louez.</p>
                        @endif
                        <p class="mt-1 font-medium leading-6 text-white/85"><strong class="text-white">Vos prix :</strong> vous fixez librement tarifs, options et conditions.</p>

                        @if (count($partnerConditions['plans']) > 0)
                            <div class="mt-3 grid gap-2">
                                @foreach ($partnerConditions['plans'] as $plan)
                                    <p class="rounded-xl bg-white/10 px-3 py-2 text-xs font-semibold text-white/80"><strong class="text-white">{{ $plan['name'] }}</strong> · {{ $plan['price'] }}@if ($plan['commission']) · {{ $plan['commission'] }}@endif</p>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-center px-5 py-4 sm:px-8 lg:order-2">
                <div class="w-full max-w-xl">
                    <x-navigation.account-switcher mode="register" current="proprietaire" class="mb-3 max-w-md" />

                    @if ($pendingRequest)
                        <p class="mb-4 rounded-xl border border-[#b9d3ff] bg-white px-4 py-3 text-sm font-semibold text-[#2f6bff]">Votre demande est déjà en cours de validation. Nous vous contactons dès que votre profil est vérifié.</p>
                    @endif

                    <h2 class="text-2xl font-extrabold text-[#07152f]">Inscrire mon espace</h2>

                    <form method="POST" action="{{ $action }}" class="mt-3 rounded-[24px] bg-white p-5 shadow-2xl shadow-[#173e7a]/10 ring-1 ring-[#dce6f7]">
                        @csrf
                        <x-forms.validation-errors class="mb-4" />

                        <fieldset class="mb-3 flex items-center justify-between gap-3">
                            <legend class="sr-only">Vous êtes</legend>
                            <span class="text-xs font-bold text-[#17233f]" aria-hidden="true">Vous êtes</span>
                            <div class="grid flex-1 grid-cols-2 gap-1 rounded-xl bg-[#eef3fb] p-1 text-sm font-semibold sm:max-w-xs">
                                @foreach (['company' => 'Une entreprise', 'individual' => 'Un particulier'] as $applicantType => $applicantLabel)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="applicant_type" value="{{ $applicantType }}" class="peer sr-only" @checked($selectedApplicantType === $applicantType)>
                                        <span class="block rounded-lg py-1.5 text-center text-[#5d6d89] transition peer-checked:bg-white peer-checked:text-[#07152f] peer-checked:shadow-sm peer-focus-visible:ring-2 peer-focus-visible:ring-[#2f6bff]">{{ $applicantLabel }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        <div class="grid grid-cols-2 gap-3">
                            @unless ($isAuthenticatedMode)
                                <label class="col-span-2 block sm:col-span-1">
                                    <span class="{{ $labelClass }}">Nom et prénom</span>
                                    <input name="name" value="{{ old('name') }}" required autocomplete="name" class="{{ $inputClass }}">
                                </label>
                            @endunless
                            <label class="col-span-2 block sm:col-span-1">
                                <span class="{{ $labelClass }}">Nom de l’établissement</span>
                                <input name="business_name" value="{{ old('business_name', $ownerProfile?->business_name) }}" required autocomplete="organization" placeholder="Ex : Villa Lagune" class="{{ $inputClass }}">
                            </label>
                            <label class="col-span-2 block sm:col-span-1">
                                <span class="{{ $labelClass }}">Téléphone WhatsApp</span>
                                @if ($isAuthenticatedMode)
                                    <input name="whatsapp_phone" value="{{ old('whatsapp_phone', $ownerProfile?->whatsapp_phone ?? auth()->user()->phone) }}" required autocomplete="tel" class="{{ $inputClass }}">
                                @else
                                    <input name="phone" value="{{ old('phone') }}" required autocomplete="tel" placeholder="+225 07 00 00 00 00" class="{{ $inputClass }}">
                                @endif
                            </label>
                            @unless ($isAuthenticatedMode)
                                <label class="col-span-2 block sm:col-span-1">
                                    <span class="{{ $labelClass }}">Adresse email</span>
                                    <input name="email" type="email" value="{{ old('email') }}" required autocomplete="email" class="{{ $inputClass }}">
                                </label>
                            @endunless
                            <label class="block">
                                <span class="{{ $labelClass }}">Pays</span>
                                @if (count($countryOptions) > 0)
                                    <select name="country_code" required class="{{ $inputClass }}">
                                        @foreach ($countryOptions as $code => $countryName)
                                            <option value="{{ $code }}" @selected($selectedCountry === $code)>{{ $countryName }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input name="country_code" value="{{ $selectedCountry }}" required maxlength="2" class="{{ $inputClass }} uppercase">
                                @endif
                            </label>
                            <label class="block">
                                <span class="{{ $labelClass }}">Ville</span>
                                <input name="city" value="{{ old('city', $ownerProfile?->city) }}" required autocomplete="address-level2" placeholder="Ex : Abidjan" class="{{ $inputClass }}">
                            </label>
                            @unless ($isAuthenticatedMode)
                                <label class="block">
                                    <span class="{{ $labelClass }}">Mot de passe</span>
                                    <input name="password" type="password" required autocomplete="new-password" class="{{ $inputClass }}">
                                </label>
                                <label class="block">
                                    <span class="{{ $labelClass }}">Confirmation</span>
                                    <input name="password_confirmation" type="password" required autocomplete="new-password" class="{{ $inputClass }}">
                                </label>
                            @endunless
                        </div>

                        <x-forms.terms-acceptance class="mt-4" />
                        <button class="mt-4 h-11 w-full rounded-xl bg-[#2f6bff] px-5 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/24 transition hover:-translate-y-0.5 hover:bg-[#2258df]">Créer mon compte</button>
                        @unless ($isAuthenticatedMode)
                            <p class="mt-3 text-center text-sm font-semibold text-[#5d6d89]">Déjà inscrit ? <a href="{{ route('portal.login', ['portal' => 'proprietaire']) }}" class="font-extrabold text-[#2f6bff] hover:underline">Se connecter</a></p>
                        @endunless
                    </form>
                </div>
            </div>
        </section>
    </main>
</x-layouts.baobaa>
