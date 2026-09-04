@php
    $isAuthenticatedMode = ($mode ?? 'guest') === 'authenticated';
    $action = $isAuthenticatedMode ? route('portals.service-provider.request') : route('service-provider.register.store');
@endphp

<x-layouts.baobaa title="Devenir prestataire événementiel - BAOBAA">
    <main class="min-h-screen bg-[#eef3ff] text-[#151821]">
        <section class="grid min-h-screen lg:grid-cols-[0.95fr_1.05fr]">
            <div class="relative overflow-hidden bg-[#07152f] px-6 py-8 text-white sm:px-10 lg:px-14">
                <div class="absolute inset-0">
                    <img src="https://images.unsplash.com/photo-1527529482837-4698179dc6ce?auto=format&fit=crop&w=1600&q=80" alt="" class="h-full w-full object-cover opacity-28">
                    <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(7,21,47,.98),rgba(47,107,255,.70))]"></div>
                </div>

                <div class="relative z-10 flex h-full min-h-[48vh] flex-col justify-between">
                    <a href="{{ url('/') }}" class="flex items-center justify-start">
                        <img src="{{ asset('images/baobaa.jpg') }}" alt="BAOBAA" class="h-10 w-auto max-w-[160px] rounded-full object-contain bg-white/90 p-1 shadow-lg shadow-[#2f6bff]/20" loading="lazy">
                    </a>

                    <div class="max-w-2xl py-14">
                        <p class="text-sm font-extrabold uppercase tracking-[0.24em] text-[#8dc1ff]">Prestataire événementiel</p>
                        <h1 class="mt-5 text-4xl font-extrabold leading-tight tracking-[-0.05em] sm:text-6xl">Proposez vos services aux bons organisateurs.</h1>
                        <p class="mt-5 max-w-xl text-base font-semibold leading-8 text-white/72">Son, lumière, podium, photo, vidéo, mobilier ou services terrain : le SAP vérifie les profils pour garder une marketplace fiable et premium.</p>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-3">
                        @foreach (['Profil vérifié', 'Services catalogués', 'Demandes qualifiées'] as $item)
                            <div class="rounded-2xl border border-white/12 bg-white/10 px-4 py-3 text-sm font-extrabold backdrop-blur">{{ $item }}</div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-center px-5 py-10 sm:px-8">
                <div class="w-full max-w-2xl">
                    @if ($pendingRequest)
                        <div class="mb-5 rounded-[22px] border border-[#b9d3ff] bg-white p-5 shadow-xl shadow-[#173e7a]/8">
                            <p class="text-sm font-extrabold text-[#2f6bff]">Votre demande PSE est déjà en cours de validation.</p>
                            <p class="mt-2 text-sm font-semibold leading-6 text-[#64708a]">Le SAP analysera vos informations avant d’activer votre portail prestataire.</p>
                        </div>
                    @endif

                    <div class="mb-7">
                        <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#2f6bff]">{{ $isAuthenticatedMode ? 'Compte connecté' : 'Nouveau prestataire' }}</p>
                        <h2 class="mt-2 text-3xl font-extrabold tracking-[-0.04em] text-[#07152f]">Demande d’accès PSE</h2>
                        <p class="mt-2 text-sm font-semibold leading-6 text-[#64708a]">Renseignez vos informations professionnelles. Les types de services disponibles seront définis par le SAP.</p>
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
                                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Nom commercial</span>
                                <input name="business_name" value="{{ old('business_name', $serviceProviderProfile?->business_name) }}" required class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                                @error('business_name')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                            </label>
                            <label class="block">
                                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Zone d’intervention</span>
                                <input name="service_area" value="{{ old('service_area', $serviceProviderProfile?->service_area) }}" required placeholder="Abidjan, Grand-Bassam, intérieur du pays..." class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                                @error('service_area')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
                            </label>
                        </div>

                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <label class="block">
                                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Nom légal</span>
                                <input name="legal_name" value="{{ old('legal_name', $serviceProviderProfile?->legal_name) }}" class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                            </label>
                            <label class="block">
                                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Identifiant fiscal ou RCCM</span>
                                <input name="tax_identifier" value="{{ old('tax_identifier', $serviceProviderProfile?->tax_identifier) }}" class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                            </label>
                        </div>

                        <div class="mt-4 grid gap-4 sm:grid-cols-[110px_1fr_1fr]">
                            <label class="block">
                                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Pays</span>
                                <input name="country_code" value="{{ old('country_code', $serviceProviderProfile?->country_code ?? 'CI') }}" required maxlength="2" class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold uppercase outline-none focus:border-[#2f6bff]">
                            </label>
                            <label class="block">
                                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Ville</span>
                                <input name="city" value="{{ old('city', $serviceProviderProfile?->city) }}" required class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                            </label>
                            <label class="block">
                                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Commune</span>
                                <input name="district" value="{{ old('district', $serviceProviderProfile?->district) }}" class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                            </label>
                        </div>

                        @if ($isAuthenticatedMode)
                            <label class="mt-4 block">
                                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Téléphone WhatsApp</span>
                                <input name="whatsapp_phone" value="{{ old('whatsapp_phone', $serviceProviderProfile?->whatsapp_phone ?? auth()->user()->phone) }}" required class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                            </label>
                        @endif

                        <label class="mt-4 block">
                            <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Présentation de vos services</span>
                            <textarea name="description" rows="4" placeholder="Décrivez vos spécialités, votre équipe, votre matériel et vos zones couvertes." class="w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 py-3 text-sm font-bold leading-6 outline-none focus:border-[#2f6bff]">{{ old('description', $serviceProviderProfile?->description) }}</textarea>
                        </label>

                        <label class="mt-4 block">
                            <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Message au SAP</span>
                            <textarea name="motivation" rows="3" class="w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 py-3 text-sm font-bold leading-6 outline-none focus:border-[#2f6bff]">{{ old('motivation') }}</textarea>
                        </label>

                        <button class="mt-5 h-12 w-full rounded-2xl bg-[#2f6bff] px-5 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/24 transition hover:-translate-y-0.5 hover:bg-[#2258df]">Envoyer au SAP</button>
                    </form>
                </div>
            </div>
        </section>
    </main>
</x-layouts.baobaa>
