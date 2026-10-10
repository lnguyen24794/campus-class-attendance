<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class PublicCheckInController extends Controller
{
    /**
     * Blade shell for public QR check-in (AJAX calls /api/v1/public/check-in).
     */
    public function show(string $qrToken): View
    {
        return view('check-in.show', [
            'qrToken' => $qrToken,
        ]);
    }
}
