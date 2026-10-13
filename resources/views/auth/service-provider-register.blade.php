@php
    $isAuthenticatedMode = ($mode ?? 'guest') === 'authenticated';
    $action = $isAuthenticatedMode ? route('portals.service-provider.request') : route('service-provider.register.store');
    $countryOptions = app(\App\Services\LocationOptionsService::class)->countries();
    $selectedCountry = strtoupper(old('country_code', $serviceProviderProfile?->country_code ?? 'CI'));
    $labelClass = 'mb-1 block text-xs font-bold text-[#17233f]';
    $inputClass = 'h-11 w-full rounded-xl border border-[#dce6f7] bg-[#f8fbff] px-3.5 text-sm font-semibold text-[#07152f] outline-none transition focus:border-[#2f6bff] focus:bg-white';
@endphp

<x-layouts.baobaa title="Devenir prestataire événementiel - BAOBAA">
    <x-navigation.public-header active="event-services" />
    <main class="bg-[#eef3ff] text-[#151821] lg:h-[calc(100vh-64px)] lg:min-h-[640px]">
        <section class="grid h-full lg:grid-cols-[0.85fr_1.15fr]">
            <div class="relative hidden overflow-hidden bg-[#07152f] px-14 text-white lg:flex lg:items-center">
                <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(7,21,47,.98),rgba(47,107,255,.72))]" aria-hidden="true"></div>

                <div class="relative z-10 max-w-md">
                    <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#8dc1ff]">Professionnel de l’événement</p>
                    <h1 class="mt-3 text-3xl font-extrabold leading-tight sm:text-4xl">Proposez vos prestations aux organisateurs.</h1>
                    <p class="mt-3 text-sm font-medium leading-6 text-white/75">Son, lumière, décor, photo, matériel… Inscription gratuite, profil vérifié par notre équipe.</p>

                    <ol class="mt-6 grid gap-2.5 text-sm font-semibold text-white/85">
                        @foreach (['Vous créez votre compte (1 minute).', 'Notre équipe vérifie votre profil.', 'Vous publiez vos prestations et recevez des demandes.'] as $stepIndex => $step)
                            <li class="flex items-center gap-3"><span class="grid size-6 shrink-0 place-items-center rounded-full bg-white/15 text-xs font-extrabold">{{ $stepIndex + 1 }}</span>{{ $step }}</li>
                        @endforeach
                    </ol>
                </div>
            </div>

            <div class="flex items-center justify-center px-5 py-4 sm:px-8 lg:order-2">
                <div class="w-full max-w-xl">
                    <x-navigation.account-switcher mode="register" current="prestataire" class="mb-3 max-w-md" />

                    @if ($pendingRequest)
                        <p class="mb-4 rounded-xl border border-[#b9d3ff] bg-white px-4 py-3 text-sm font-semibold text-[#2f6bff]">Votre demande est déjà en cours de validation par notre équipe.</p>
                    @endif

                    <h2 class="text-2xl font-extrabold text-[#07152f]">Créer mon compte professionnel</h2>

                    <form method="POST" action="{{ $action }}" class="mt-3 rounded-[24px] bg-white p-5 shadow-2xl shadow-[#173e7a]/10 ring-1 ring-[#dce6f7]">
                        @csrf
                        <x-forms.validation-errors class="mb-4" />

                        <div class="grid grid-cols-2 gap-3">
                            @unless ($isAuthenticatedMode)
                                <label class="col-span-2 block sm:col-span-1">
                                    <span class="{{ $labelClass }}">Nom et prénom</span>
                                    <input name="name" value="{{ old('name') }}" required autocomplete="name" class="{{ $inputClass }}">
                                </label>
                            @endunless
                            <label class="col-span-2 block sm:col-span-1">
                                <span class="{{ $labelClass }}">Nom commercial</span>
                                <input name="business_name" value="{{ old('business_name', $serviceProviderProfile?->business_name) }}" required autocomplete="organization" placeholder="Ex : Studio Son Lagune" class="{{ $inputClass }}">
                            </label>
                            <label class="col-span-2 block sm:col-span-1">
                                <span class="{{ $labelClass }}">Téléphone WhatsApp</span>
                                @if ($isAuthenticatedMode)
                                    <input name="whatsapp_phone" value="{{ old('whatsapp_phone', $serviceProviderProfile?->whatsapp_phone ?? auth()->user()->phone) }}" required autocomplete="tel" class="{{ $inputClass }}">
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
                                <input name="city" value="{{ old('city', $serviceProviderProfile?->city) }}" required autocomplete="address-level2" placeholder="Ex : Abidjan" class="{{ $inputClass }}">
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
                            <p class="mt-3 text-center text-sm font-semibold text-[#5d6d89]">Déjà inscrit ? <a href="{{ route('portal.login', ['portal' => 'prestataire']) }}" class="font-extrabold text-[#2f6bff] hover:underline">Se connecter</a></p>
                        @endunless
                    </form>
                </div>
            </div>
        </section>
    </main>
</x-layouts.baobaa>
