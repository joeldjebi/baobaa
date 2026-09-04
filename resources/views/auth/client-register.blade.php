<x-layouts.baobaa title="Créer un compte client - BAOBAA">
    <main class="grid min-h-screen bg-[#f6f8fc] lg:grid-cols-[1fr_0.95fr]">
        <section class="relative flex min-h-[38vh] flex-col justify-between overflow-hidden bg-[#07152f] px-6 py-8 text-white sm:px-10 lg:min-h-screen lg:px-14">
            <div class="absolute inset-0">
                <img src="https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&w=1600&q=80" alt="" class="h-full w-full object-cover opacity-24">
                <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(7,21,47,.96),rgba(47,107,255,.74))]"></div>
            </div>

            <a href="{{ url('/') }}" class="relative z-10 flex items-center justify-start">
                <img src="{{ asset('images/baobaa.jpg') }}" alt="BAOBAA" class="h-10 w-auto max-w-[160px] rounded-full object-contain bg-white/90 p-1 shadow-lg shadow-[#2f6bff]/20" loading="lazy">
            </a>

            <div class="relative z-10 max-w-2xl py-14 lg:py-0">
                <p class="mb-5 text-sm font-semibold uppercase tracking-[0.24em] text-[#7dd3fc]">Compte client</p>
                <h1 class="max-w-xl text-4xl font-semibold leading-tight sm:text-6xl">Réservez vos espaces avec un suivi clair.</h1>
                <p class="mt-5 max-w-lg text-base leading-7 text-white/72">Créez votre compte pour enregistrer vos réservations, suivre vos paiements et publier vos avis après événement.</p>
            </div>

            <div class="relative z-10"></div>
        </section>

        <section class="flex items-center justify-center px-6 py-10 sm:px-10">
            <div class="w-full max-w-md">
                <div class="mb-8">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#0d47a1]">Inscription</p>
                    <h2 class="mt-3 text-4xl font-semibold text-[#081225]">Créer mon compte</h2>
                    <p class="mt-3 text-sm leading-6 text-[#5d6d89]">Vous pourrez devenir partenaire plus tard depuis votre espace client.</p>
                </div>

                <form method="POST" action="{{ route('client.register.store') }}" class="space-y-5 rounded-2xl bg-white p-6 shadow-xl shadow-[#0d47a1]/8 ring-1 ring-[#dce6f7]">
                    @csrf
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-[#17233f]">Nom complet</span>
                        <input name="name" value="{{ old('name') }}" required autocomplete="name" class="h-12 w-full rounded-xl border border-[#c9d8ef] bg-[#f8fbff] px-4 text-sm font-medium outline-none focus:border-[#0d47a1] focus:bg-white">
                        @error('name')<span class="mt-2 block text-sm font-medium text-red-700">{{ $message }}</span>@enderror
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-[#17233f]">Adresse email</span>
                        <input name="email" type="email" value="{{ old('email') }}" required autocomplete="email" class="h-12 w-full rounded-xl border border-[#c9d8ef] bg-[#f8fbff] px-4 text-sm font-medium outline-none focus:border-[#0d47a1] focus:bg-white">
                        @error('email')<span class="mt-2 block text-sm font-medium text-red-700">{{ $message }}</span>@enderror
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-[#17233f]">Téléphone</span>
                        <input name="phone" value="{{ old('phone') }}" autocomplete="tel" class="h-12 w-full rounded-xl border border-[#c9d8ef] bg-[#f8fbff] px-4 text-sm font-medium outline-none focus:border-[#0d47a1] focus:bg-white">
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-[#17233f]">Mot de passe</span>
                        <input name="password" type="password" required autocomplete="new-password" class="h-12 w-full rounded-xl border border-[#c9d8ef] bg-[#f8fbff] px-4 text-sm font-medium outline-none focus:border-[#0d47a1] focus:bg-white">
                        @error('password')<span class="mt-2 block text-sm font-medium text-red-700">{{ $message }}</span>@enderror
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-[#17233f]">Confirmer le mot de passe</span>
                        <input name="password_confirmation" type="password" required autocomplete="new-password" class="h-12 w-full rounded-xl border border-[#c9d8ef] bg-[#f8fbff] px-4 text-sm font-medium outline-none focus:border-[#0d47a1] focus:bg-white">
                    </label>
                    <button class="h-12 w-full rounded-xl bg-[#0d47a1] px-5 text-sm font-semibold text-white shadow-lg shadow-[#0d47a1]/24 transition hover:-translate-y-0.5 hover:bg-[#0b3b86]">Créer mon compte client</button>
                    <p class="text-center text-sm font-semibold text-[#5d6d89]">Déjà inscrit ? <a href="{{ route('portal.login', ['portal' => 'client']) }}" class="font-extrabold text-[#0d47a1] hover:underline">Se connecter</a></p>
                </form>
            </div>
        </section>
    </main>
</x-layouts.baobaa>
