<x-layouts.baobaa title="Créer un compte client - BAOBAA">
    <x-navigation.public-header />
    <main class="grid bg-[#eef3ff] lg:h-[calc(100vh-64px)] lg:min-h-[640px] lg:grid-cols-[0.85fr_1.15fr]">
        <section class="relative hidden items-center overflow-hidden bg-[#07152f] px-14 text-white lg:flex">
            <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(7,21,47,.98),rgba(47,107,255,.72))]" aria-hidden="true"></div>

            <div class="relative z-10 max-w-md">
                <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#8dc1ff]">Compte client</p>
                <h1 class="mt-3 text-4xl font-extrabold leading-tight">Organisez votre événement, sans stress.</h1>
                <p class="mt-3 text-sm font-medium leading-6 text-white/75">Un seul compte pour réserver le lieu, ajouter les prestations des pros et suivre vos paiements.</p>

                <ol class="mt-6 grid gap-2.5 text-sm font-semibold text-white/85">
                    @foreach (['Vous trouvez le lieu libre à vos dates.', 'Vous ajoutez options et prestations.', 'Vous payez l’acompte et la date est bloquée.'] as $stepIndex => $step)
                        <li class="flex items-center gap-3"><span class="grid size-6 shrink-0 place-items-center rounded-full bg-white/15 text-xs font-extrabold">{{ $stepIndex + 1 }}</span>{{ $step }}</li>
                    @endforeach
                </ol>
            </div>
        </section>

        <section class="flex items-center justify-center px-5 py-4 sm:px-8">
            <div class="w-full max-w-xl">
                <x-navigation.account-switcher mode="register" current="client" class="mb-3 max-w-md" />

                <h2 class="text-2xl font-extrabold text-[#07152f]">Créer mon compte client</h2>

                <form method="POST" action="{{ route('client.register.store') }}" class="mt-3 rounded-[24px] bg-white p-5 shadow-2xl shadow-[#173e7a]/10 ring-1 ring-[#dce6f7]">
                    @csrf
                    <x-forms.validation-errors class="mb-4" />

                    <div class="grid grid-cols-2 gap-3">
                        <label class="col-span-2 block sm:col-span-1">
                            <span class="mb-1 block text-xs font-bold text-[#17233f]">Nom et prénom</span>
                            <input name="name" value="{{ old('name') }}" required autocomplete="name" class="h-11 w-full rounded-xl border border-[#dce6f7] bg-[#f8fbff] px-3.5 text-sm font-semibold text-[#07152f] outline-none transition focus:border-[#2f6bff] focus:bg-white">
                        </label>
                        <label class="col-span-2 block sm:col-span-1">
                            <span class="mb-1 block text-xs font-bold text-[#17233f]">Téléphone <span class="font-medium text-[#8a94aa]">(facultatif)</span></span>
                            <input name="phone" value="{{ old('phone') }}" autocomplete="tel" placeholder="+225 07 00 00 00 00" class="h-11 w-full rounded-xl border border-[#dce6f7] bg-[#f8fbff] px-3.5 text-sm font-semibold text-[#07152f] outline-none transition focus:border-[#2f6bff] focus:bg-white">
                        </label>
                        <label class="col-span-2 block">
                            <span class="mb-1 block text-xs font-bold text-[#17233f]">Adresse email</span>
                            <input name="email" type="email" value="{{ old('email') }}" required autocomplete="email" class="h-11 w-full rounded-xl border border-[#dce6f7] bg-[#f8fbff] px-3.5 text-sm font-semibold text-[#07152f] outline-none transition focus:border-[#2f6bff] focus:bg-white">
                        </label>
                        <x-forms.client-type-fields class="col-span-2 gap-3 sm:grid-cols-2" label-class="mb-1 block text-xs font-bold text-[#17233f]" input-class="h-11 w-full rounded-xl border border-[#dce6f7] bg-[#f8fbff] px-3.5 text-sm font-semibold text-[#07152f] outline-none transition focus:border-[#2f6bff] focus:bg-white" />
                        <label class="block">
                            <span class="mb-1 block text-xs font-bold text-[#17233f]">Mot de passe</span>
                            <input name="password" type="password" required autocomplete="new-password" class="h-11 w-full rounded-xl border border-[#dce6f7] bg-[#f8fbff] px-3.5 text-sm font-semibold text-[#07152f] outline-none transition focus:border-[#2f6bff] focus:bg-white">
                        </label>
                        <label class="block">
                            <span class="mb-1 block text-xs font-bold text-[#17233f]">Confirmation</span>
                            <input name="password_confirmation" type="password" required autocomplete="new-password" class="h-11 w-full rounded-xl border border-[#dce6f7] bg-[#f8fbff] px-3.5 text-sm font-semibold text-[#07152f] outline-none transition focus:border-[#2f6bff] focus:bg-white">
                        </label>
                    </div>

                    <x-forms.terms-acceptance class="mt-4" />
                    <button class="mt-4 h-11 w-full rounded-xl bg-[#2f6bff] px-5 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/24 transition hover:-translate-y-0.5 hover:bg-[#2258df]">Créer mon compte</button>
                    <p class="mt-3 text-center text-sm font-semibold text-[#5d6d89]">Déjà inscrit ? <a href="{{ route('portal.login', ['portal' => 'client']) }}" class="font-extrabold text-[#2f6bff] hover:underline">Se connecter</a></p>
                </form>
            </div>
        </section>
    </main>
</x-layouts.baobaa>
