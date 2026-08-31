<x-layouts.baobaa :title="$meta['label'].' - BAOBAA'">
    <main class="grid min-h-screen bg-[#f6f8fc] lg:grid-cols-[1.02fr_0.98fr]">
        <section class="relative flex min-h-[42vh] flex-col justify-between overflow-hidden bg-[#07152f] px-6 py-8 text-white sm:px-10 lg:min-h-screen lg:px-14">
            <div class="absolute inset-0">
                <img src="https://images.unsplash.com/photo-1515169067865-5387ec356754?auto=format&fit=crop&w=1600&q=80" alt="" class="h-full w-full object-cover opacity-24">
                <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(7,21,47,.96),rgba(13,71,161,.72))]"></div>
            </div>

            <div class="relative z-10 flex items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <span class="grid size-10 place-items-center rounded-full bg-white text-sm font-black text-[#0d47a1]">B</span>
                    <span class="text-lg font-semibold tracking-[0.22em]">BAOBAA</span>
                </a>
                <span class="rounded-full border border-white/18 bg-white/8 px-4 py-2 text-xs font-semibold text-white/82">{{ $meta['label'] }}</span>
            </div>

            <div class="relative z-10 max-w-2xl py-14 lg:py-0">
                <p class="mb-5 text-sm font-semibold uppercase tracking-[0.24em] text-[#7dd3fc]">Acces securise</p>
                <h1 class="max-w-xl text-4xl font-semibold leading-tight sm:text-6xl">{{ $meta['title'] }}</h1>
                <p class="mt-5 max-w-lg text-base leading-7 text-white/72">{{ $meta['subtitle'] }}</p>
            </div>

            <div class="relative z-10 grid max-w-xl gap-3 sm:grid-cols-3">
                <div class="border border-white/12 bg-white/8 p-4 backdrop-blur">
                    <p class="text-sm font-semibold">Session protegee</p>
                    <p class="mt-1 text-xs text-white/58">CSRF et regeneration</p>
                </div>
                <div class="border border-white/12 bg-white/8 p-4 backdrop-blur">
                    <p class="text-sm font-semibold">Role dedie</p>
                    <p class="mt-1 text-xs text-white/58">Portail isole</p>
                </div>
                <div class="border border-white/12 bg-white/8 p-4 backdrop-blur">
                    <p class="text-sm font-semibold">Limite active</p>
                    <p class="mt-1 text-xs text-white/58">Anti brute-force</p>
                </div>
            </div>
        </section>

        <section class="flex items-center justify-center px-6 py-10 sm:px-10">
            <div class="w-full max-w-md">
                <div class="mb-8">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#0d47a1]">{{ $meta['label'] }}</p>
                    <h2 class="mt-3 text-4xl font-semibold text-[#081225]">Connexion</h2>
                    <p class="mt-3 text-sm leading-6 text-[#5d6d89]">Utilise uniquement le compte associe a ce portail BAOBAA.</p>
                </div>

                <form method="POST" action="{{ route('portal.login.store', ['portal' => $portal]) }}" class="space-y-5 rounded-2xl bg-white p-6 shadow-xl shadow-[#0d47a1]/8 ring-1 ring-[#dce6f7]">
                    @csrf

                    <div>
                        <label for="email" class="mb-2 block text-sm font-semibold text-[#17233f]">Adresse email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="h-12 w-full rounded-xl border border-[#c9d8ef] bg-[#f8fbff] px-4 text-sm font-medium text-[#081225] outline-none transition placeholder:text-[#8a98b5] focus:border-[#0d47a1] focus:bg-white focus:ring-4 focus:ring-[#0d47a1]/10">
                        @error('email')
                            <p class="mt-2 text-sm font-medium text-red-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-sm font-semibold text-[#17233f]">Mot de passe</label>
                        <input id="password" name="password" type="password" required autocomplete="current-password" class="h-12 w-full rounded-xl border border-[#c9d8ef] bg-[#f8fbff] px-4 text-sm font-medium text-[#081225] outline-none transition focus:border-[#0d47a1] focus:bg-white focus:ring-4 focus:ring-[#0d47a1]/10">
                        @error('password')
                            <p class="mt-2 text-sm font-medium text-red-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex items-center gap-3 text-sm font-medium text-[#5d6d89]">
                        <input type="checkbox" name="remember" value="1" class="size-4 rounded border-[#c9d8ef] text-[#0d47a1]">
                        Garder ma session active
                    </label>

                    <button type="submit" class="h-12 w-full rounded-xl bg-[#0d47a1] px-5 text-sm font-semibold text-white shadow-lg shadow-[#0d47a1]/24 transition hover:-translate-y-0.5 hover:bg-[#0b3b86] focus:outline-none focus:ring-4 focus:ring-[#0d47a1]/18">
                        Se connecter
                    </button>

                    @if ($portal === 'client')
                        <p class="text-center text-sm font-semibold text-[#5d6d89]">
                            Nouveau sur BAOBAA ?
                            <a href="{{ route('client.register') }}" class="font-extrabold text-[#0d47a1] hover:underline">Créer mon compte client</a>
                        </p>
                    @endif
                </form>
            </div>
        </section>
    </main>
</x-layouts.baobaa>
