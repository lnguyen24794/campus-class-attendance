<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Blade shell for dashboard (AJAX loads /api/v1/admin/dashboard).
     */
    public function __invoke(): View
    {
        return view('dashboard');
    }
}
