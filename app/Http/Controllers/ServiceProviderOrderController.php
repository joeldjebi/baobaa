<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\ServiceProviderProfile;
use App\Services\OrderFulfillmentService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServiceProviderOrderController extends Controller
{
    public function __construct(private readonly OrderFulfillmentService $orderFulfillmentService) {}

    public function index(Request $request): View
    {
        $profile = $this->profile($request);

        return view('dashboards.service-provider.orders', [
            ...app(ServiceProviderDashboardController::class)->metricsFor($profile),
            'profile' => $profile,
            'orders' => $profile->orders()
                ->with(['client', 'items'])
                ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->string('status')))
                ->when($request->filled('q'), fn (Builder $query) => $query->where(fn (Builder $query) => $query
                    ->where('reference', 'like', '%'.$request->string('q')->toString().'%')
                    ->orWhereHas('client', fn (Builder $query) => $query->where('name', 'like', '%'.$request->string('q')->toString().'%'))))
                ->latest()
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        $profile = $this->profile($request);
        $this->authorizeOrder($profile, $order);

        return view('dashboards.service-provider.order-show', [
            ...app(ServiceProviderDashboardController::class)->metricsFor($profile),
            'profile' => $profile,
            'order' => $order->load(['client', 'items']),
            'nextStatuses' => $this->orderFulfillmentService->nextStatuses($order),
        ]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOrder($this->profile($request), $order);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(OrderStatus::class)],
        ]);

        $status = OrderStatus::from($validated['status']);

        $status === OrderStatus::Cancelled
            ? $this->orderFulfillmentService->cancel($order->load('items'))
            : $this->orderFulfillmentService->advance($order, $status);

        return back()->with('pse_status', 'Commande mise à jour : '.$status->label().'.');
    }

    public function settleDeposit(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOrder($this->profile($request), $order);

        $validated = $request->validate([
            'retained_amount' => ['required', 'integer', 'min:0'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $this->orderFulfillmentService->settleDeposit($order, (int) $validated['retained_amount'], $validated['reason'] ?? null);

        return back()->with('pse_status', (int) $validated['retained_amount'] > 0 ? 'Retenue sur caution enregistrée.' : 'Caution restituée au client.');
    }

    private function authorizeOrder(ServiceProviderProfile $profile, Order $order): void
    {
        abort_unless((int) $order->service_provider_profile_id === (int) $profile->id, 404);
    }

    private function profile(Request $request): ServiceProviderProfile
    {
        return $request->user()->serviceProviderProfile()->firstOrFail();
    }
}
