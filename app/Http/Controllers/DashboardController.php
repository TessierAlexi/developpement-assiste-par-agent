<?php

namespace App\Http\Controllers;

use App\Services\InventoryDashboardService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the inventory dashboard.
     */
    public function __invoke(InventoryDashboardService $dashboard): Response
    {
        return Inertia::render('Dashboard', $dashboard->summary());
    }
}
