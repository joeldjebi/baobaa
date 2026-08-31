@php
    $activeFilters = collect(['q', 'city', 'category', 'capacity', 'min_price', 'max_price', 'start_date', 'end_date'])
        ->contains(fn (string $filter): bool => request()->filled($filter));
@endphp

<x-layouts.baobaa title="Tous les espaces - BAOBAA">
    <main class="min-h-screen bg-[#eef3ff] text-[#151821]">
        <header class="sticky top-0 z-40 border-b border-white/70 bg-white/95 px-5 py-3 shadow-sm shadow-[#173e7a]/5 sm:px-8 baobaa-sticky-stable">
            <div class="mx-auto flex max-w-7xl items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center gap-3 text-[#2f6bff]">
                    <span class="grid size-11 place-items-center rounded-2xl bg-[#2f6bff] text-lg font-extrabold text-white shadow-lg shadow-[#2f6bff]/25">B</span>
                    <span class="leading-none">
                        <span class="block text-xl font-extrabold tracking-[-0.045em]">baobaa</span>
                        <span class="block text-[11px] font-extrabold tracking-[-0.035em] text-[#6f7890]">marché événementiel</span>
                    </span>
                </a>

                <x-navigation.public-menu active="venues" />

                <x-navigation.account-menu />
            </div>
        </header>

        <section class="relative overflow-hidden border-b border-white/80 px-5 py-8 sm:px-8 lg:py-10">
            <div class="absolute inset-0 bg-[linear-gradient(180deg,#f8fbff_0%,#eef4ff_55%,#e9eeff_100%)]"></div>
            <div class="absolute -left-24 top-14 h-60 w-[42rem] rounded-full bg-[#dce8ff] blur-3xl"></div>
            <div class="absolute -right-28 top-4 h-72 w-[46rem] rounded-full bg-[#efe7ff] blur-3xl"></div>

            <div class="relative mx-auto grid max-w-7xl gap-6 lg:grid-cols-[0.88fr_1.12fr] lg:items-end">
                <div>
                    <p class="inline-flex rounded-full bg-white px-4 py-2 text-xs font-extrabold uppercase tracking-[0.16em] text-[#2f6bff] shadow-sm ring-1 ring-[#dce6f7]">Catalogue BAOBAA</p>
                    <h1 class="mt-4 max-w-xl text-4xl font-extrabold leading-[0.96] tracking-[-0.055em] text-[#121722] sm:text-5xl">Trouvez l’espace qui donne du niveau à votre événement.</h1>
                    <p class="mt-4 max-w-xl text-sm font-semibold leading-6 text-[#667088]">Comparez les lieux publiés, vérifiez les disponibilités, ajustez votre budget et réservez avec une expérience claire et sécurisée.</p>
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-3xl border border-white bg-white/86 p-4 shadow-lg shadow-[#173e7a]/8">
                        <p class="text-2xl font-extrabold tracking-[-0.04em] text-[#151821]">{{ $venues->total() }}</p>
                        <p class="mt-1 text-xs font-bold text-[#6f7890]">espaces publiés</p>
                    </div>
                    <div class="rounded-3xl border border-white bg-white/86 p-4 shadow-lg shadow-[#173e7a]/8">
                        <p class="text-2xl font-extrabold tracking-[-0.04em] text-[#151821]">{{ $cities->count() }}</p>
                        <p class="mt-1 text-xs font-bold text-[#6f7890]">villes disponibles</p>
                    </div>
                    <div class="rounded-3xl border border-white bg-white/86 p-4 shadow-lg shadow-[#173e7a]/8">
                        <p class="text-2xl font-extrabold tracking-[-0.04em] text-[#151821]">{{ $categories->count() }}</p>
                        <p class="mt-1 text-xs font-bold text-[#6f7890]">catégories actives</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto grid max-w-7xl gap-6 px-5 py-7 sm:px-8 lg:grid-cols-[320px_1fr]">
            <aside class="lg:sticky lg:top-24 lg:self-start">
                <form method="GET" action="{{ route('venues.index') }}" class="rounded-[24px] border border-[#dce6f7] bg-white p-3.5 shadow-2xl shadow-[#173e7a]/10" data-venues-filter-form data-no-global-loader>
                    <div class="flex items-center justify-between gap-3 border-b border-[#edf2fb] pb-3">
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#2f6bff]">Filtres</p>
                            <h2 class="mt-1 text-lg font-extrabold tracking-[-0.035em]">Affiner la recherche</h2>
                        </div>
                        @if ($activeFilters)
                            <a href="{{ route('venues.index') }}" class="rounded-full bg-[#eef4ff] px-3 py-1.5 text-xs font-extrabold text-[#2f6bff]">Effacer</a>
                        @endif
                    </div>

                    <div class="mt-3 space-y-2.5">
                        <label class="relative block rounded-xl bg-[#f7faff] px-3 py-2.5 ring-1 ring-[#dce6f7]" data-autocomplete>
                            <span class="block text-[11px] font-extrabold uppercase text-[#8a94aa]">Quoi</span>
                            <input name="q" value="{{ request('q') }}" autocomplete="off" data-autocomplete-input placeholder="Salle, jardin, rooftop..." class="mt-0.5 w-full bg-transparent text-[13px] font-extrabold text-[#151821] outline-none placeholder:text-[#a0a8b8]">
                            <div data-autocomplete-list class="absolute left-0 right-0 top-[calc(100%+8px)] z-[90] hidden max-h-72 overflow-y-auto rounded-2xl border border-[#dce6f7] bg-white p-2 shadow-2xl shadow-[#173e7a]/16"></div>
                        </label>

                        <div class="grid gap-2.5 sm:grid-cols-2 lg:grid-cols-1">
                            <label class="rounded-xl bg-[#f7faff] px-3 py-2.5 ring-1 ring-[#dce6f7]">
                                <span class="block text-[11px] font-extrabold uppercase text-[#8a94aa]">Ville</span>
                                <select name="city" class="mt-0.5 w-full bg-transparent text-[13px] font-extrabold text-[#151821] outline-none">
                                    <option value="">Toutes les villes</option>
                                    @foreach ($cities as $city)
                                        <option value="{{ $city }}" @selected(request('city') === $city)>{{ $city }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="rounded-xl bg-[#f7faff] px-3 py-2.5 ring-1 ring-[#dce6f7]">
                                <span class="block text-[11px] font-extrabold uppercase text-[#8a94aa]">Catégorie</span>
                                <select name="category" class="mt-0.5 w-full bg-transparent text-[13px] font-extrabold text-[#151821] outline-none">
                                    <option value="">Toutes les catégories</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>

                        <div class="grid gap-2.5">
                            <label class="flex min-h-[58px] flex-col justify-center rounded-xl bg-[#f7faff] px-3 py-2.5 ring-1 ring-[#dce6f7]">
                                <span class="block whitespace-nowrap text-[11px] font-extrabold uppercase text-[#8a94aa]">Invités min.</span>
                                <input type="number" min="1" name="capacity" value="{{ request('capacity') }}" placeholder="120" class="mt-0.5 w-full min-w-0 bg-transparent text-[13px] font-extrabold text-[#151821] outline-none placeholder:text-[#a0a8b8]">
                            </label>
                            <label class="flex min-h-[58px] flex-col justify-center rounded-xl bg-[#f7faff] px-3 py-2.5 ring-1 ring-[#dce6f7]">
                                <span class="block text-[11px] font-extrabold uppercase text-[#8a94aa]">Budget min.</span>
                                <input type="number" min="1" name="min_price" value="{{ request('min_price') }}" placeholder="100000" class="mt-0.5 w-full min-w-0 bg-transparent text-[13px] font-extrabold text-[#151821] outline-none placeholder:text-[#a0a8b8]">
                            </label>
                            <label class="flex min-h-[58px] flex-col justify-center rounded-xl bg-[#f7faff] px-3 py-2.5 ring-1 ring-[#dce6f7]">
                                <span class="block text-[11px] font-extrabold uppercase text-[#8a94aa]">Budget max.</span>
                                <input type="number" min="1" name="max_price" value="{{ request('max_price') }}" placeholder="700000" class="mt-0.5 w-full min-w-0 bg-transparent text-[13px] font-extrabold text-[#151821] outline-none placeholder:text-[#a0a8b8]">
                            </label>
                        </div>

                        <div class="rounded-xl bg-[#f7faff] p-2.5 ring-1 ring-[#dce6f7]">
                            <div class="mb-2 flex items-center gap-2 text-[11px] font-extrabold uppercase text-[#8a94aa]">
                                <svg class="size-4 text-[#2f6bff]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 2v4M16 2v4M3 10h18"/><rect x="3" y="4" width="18" height="18" rx="2"/></svg>
                                Calendrier
                            </div>
                            <div class="grid gap-2">
                                <label>
                                    <span class="block text-[11px] font-bold text-[#6f7890]">Date de début</span>
                                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="mt-1 w-full rounded-lg border border-[#dce6f7] bg-white px-2.5 py-1.5 text-[13px] font-extrabold text-[#151821] outline-none transition focus:border-[#2f6bff]">
                                </label>
                                <label>
                                    <span class="block text-[11px] font-bold text-[#6f7890]">Date de fin</span>
                                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="mt-1 w-full rounded-lg border border-[#dce6f7] bg-white px-2.5 py-1.5 text-[13px] font-extrabold text-[#151821] outline-none transition focus:border-[#2f6bff]">
                                </label>
                            </div>
                        </div>

                        <button class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#2f6bff] px-4 py-3 text-[13px] font-extrabold text-white shadow-lg shadow-[#2f6bff]/25 transition hover:-translate-y-0.5 hover:bg-[#2258df]">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                            Appliquer les filtres
                        </button>
                    </div>
                </form>
            </aside>

            <div id="venues-results-shell" class="relative transition-opacity duration-200" data-venues-results-shell>
                @include('venues.partials.results', ['venues' => $venues])
            </div>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const suggestions = @js($searchSuggestions);
            const root = document.querySelector('[data-autocomplete]');
            const form = document.querySelector('[data-venues-filter-form]');
            const resultsShell = document.querySelector('[data-venues-results-shell]');
            let ajaxTimer = null;
            let activeRequest = null;

            if (!root || !form || !resultsShell) {
                return;
            }

            const input = root.querySelector('[data-autocomplete-input]');
            const list = root.querySelector('[data-autocomplete-list]');

            const buildUrl = () => {
                const formData = new FormData(form);
                const params = new URLSearchParams();

                formData.forEach((value, key) => {
                    if (String(value).trim() !== '') {
                        params.append(key, value);
                    }
                });

                const query = params.toString();

                return `${form.action}${query ? `?${query}` : ''}`;
            };

            const setResultsLoading = (isLoading) => {
                resultsShell.classList.toggle('opacity-55', isLoading);
                resultsShell.classList.toggle('pointer-events-none', isLoading);
                window.dispatchEvent(new CustomEvent(isLoading ? 'baobaa:loading-start' : 'baobaa:loading-stop'));
            };

            const fetchResults = async (url = buildUrl(), shouldPushState = true) => {
                activeRequest?.abort();
                activeRequest = new AbortController();
                const request = activeRequest;
                setResultsLoading(true);

                try {
                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html',
                        },
                        signal: request.signal,
                    });

                    if (!response.ok) {
                        throw new Error('La recherche a échoué.');
                    }

                    resultsShell.innerHTML = await response.text();

                    if (shouldPushState) {
                        window.history.pushState({}, '', url);
                    }
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        console.error(error);
                    }
                } finally {
                    if (activeRequest === request && !request.signal.aborted) {
                        setResultsLoading(false);
                    }
                }
            };

            const scheduleFetchResults = () => {
                clearTimeout(ajaxTimer);
                ajaxTimer = setTimeout(() => fetchResults(), 380);
            };

            const hide = () => {
                list.classList.add('hidden');
                list.innerHTML = '';
            };

            input.addEventListener('input', () => {
                const value = input.value.trim().toLowerCase();

                if (value.length < 3) {
                    hide();
                    scheduleFetchResults();
                    return;
                }

                const matches = suggestions
                    .filter((suggestion) => suggestion.label.toLowerCase().includes(value))
                    .slice(0, 8);

                if (!matches.length) {
                    hide();
                    scheduleFetchResults();
                    return;
                }

                list.innerHTML = matches.map((suggestion) => `
                    <button type="button" class="flex w-full items-center justify-between gap-3 rounded-xl px-3 py-2 text-left transition hover:bg-[#eef4ff]" data-suggestion="${suggestion.label.replace(/"/g, '&quot;')}">
                        <span class="text-sm font-extrabold text-[#151821]">${suggestion.label}</span>
                        <span class="rounded-full bg-[#edf4ff] px-2 py-0.5 text-[10px] font-extrabold uppercase text-[#2f6bff]">${suggestion.type}</span>
                    </button>
                `).join('');

                list.classList.remove('hidden');
                scheduleFetchResults();
            });

            list.addEventListener('click', (event) => {
                const button = event.target.closest('[data-suggestion]');

                if (!button) {
                    return;
                }

                input.value = button.dataset.suggestion;
                hide();
                fetchResults();
            });

            form.addEventListener('submit', (event) => {
                event.preventDefault();
                fetchResults();
            });

            form.addEventListener('input', (event) => {
                if (event.target !== input && event.target.matches('input')) {
                    scheduleFetchResults();
                }
            });

            form.addEventListener('change', (event) => {
                if (event.target.matches('select, input[type="date"]')) {
                    fetchResults();
                }
            });

            document.addEventListener('click', (event) => {
                const paginationLink = event.target.closest('[data-venues-pagination] a');

                if (paginationLink) {
                    event.preventDefault();
                    fetchResults(paginationLink.href);
                    return;
                }

                if (!root.contains(event.target)) {
                    hide();
                }
            });

            window.addEventListener('popstate', () => fetchResults(window.location.href, false));
        });
    </script>
</x-layouts.baobaa>
