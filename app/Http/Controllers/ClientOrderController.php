<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientOrderController extends Controller
{
    public function index(Request $request): View
    {
        return view('dashboards.client-orders', [
            ...app(DashboardController::class)->clientMetricsFor($request->user()),
            'client' => $request->user(),
            'orders' => $request->user()->orders()
                ->with(['items', 'serviceProviderProfile'])
                ->latest()
                ->paginate(10),
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        abort_unless((int) $order->client_id === (int) $request->user()->id, 404);

        return view('dashboards.client-order-show', [
            ...app(DashboardController::class)->clientMetricsFor($request->user()),
            'client' => $request->user(),
            'order' => $order->load(['items', 'serviceProviderProfile']),
        ]);
    }
}
