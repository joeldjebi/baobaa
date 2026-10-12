<x-dashboards.service-provider-shell title="Livraison" subtitle="Fixez un tarif de livraison par ville. Le retrait chez vous reste toujours gratuit." active="delivery" :profile="$profile" :active-services-count="$activeServicesCount" :draft-services-count="$draftServicesCount" :requests-count="$requestsCount" :gross-revenue="$grossRevenue">
    @if (session('pse_status'))
        <div class="mb-4 rounded-2xl border border-[#b9d3ff] bg-[#f2f7ff] px-4 py-3 text-sm font-extrabold text-[#2f6bff]">{{ session('pse_status') }}</div>
    @endif
    <x-forms.validation-errors class="mb-4" />

    <div class="grid gap-5 lg:grid-cols-[360px_1fr]">
        <form method="POST" action="{{ route('service-provider.delivery-zones.store') }}" class="rounded-[26px] bg-white p-5 shadow-xl shadow-[#173e7a]/8 ring-1 ring-[#dce6f7]">
            @csrf
            <h2 class="text-lg font-extrabold text-[#07152f]">Ajouter ou modifier une ville</h2>
            <label class="mt-4 block">
                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Ville desservie</span>
                <input name="city" list="delivery-cities" required value="{{ old('city') }}" class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
                <datalist id="delivery-cities">
                    @foreach ($cityOptions as $cityOption)
                        <option value="{{ $cityOption['name'] }}"></option>
                    @endforeach
                </datalist>
            </label>
            <label class="mt-3 block">
                <span class="mb-2 block text-sm font-extrabold text-[#17233f]">Frais de livraison (XOF)</span>
                <input type="number" name="fee" min="0" required value="{{ old('fee', 0) }}" class="h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
            </label>
            <button class="mt-4 w-full rounded-2xl bg-[#2f6bff] px-5 py-3 text-sm font-extrabold text-white">Enregistrer</button>
        </form>

        <section class="rounded-[26px] bg-white p-5 shadow-xl shadow-[#173e7a]/8 ring-1 ring-[#dce6f7]">
            <h2 class="text-lg font-extrabold text-[#07152f]">Villes desservies</h2>
            <div class="mt-4 grid gap-2">
                @forelse ($zones as $zone)
                    <div class="flex items-center justify-between gap-3 rounded-2xl bg-[#f7faff] p-3 ring-1 ring-[#edf2fb]">
                        <p class="font-extrabold text-[#151821]">{{ $zone->city }} <span class="ml-2 text-sm font-bold text-[#52617b]">{{ number_format($zone->fee, 0, ',', ' ') }} {{ $zone->currency }}</span></p>
                        <form method="POST" action="{{ route('service-provider.delivery-zones.destroy', $zone) }}">
                            @csrf
                            @method('DELETE')
                            <button class="rounded-full bg-white px-3 py-1 text-xs font-extrabold text-[#b42318] ring-1 ring-[#ffd0d0]">Retirer</button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm font-semibold text-[#6f7890]">Aucune ville de livraison : vos clients pourront seulement retirer le matériel chez vous.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-dashboards.service-provider-shell>
