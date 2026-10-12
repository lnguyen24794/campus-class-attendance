<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use Illuminate\View\View;

class AttendanceSessionLiveController extends Controller
{
    /**
     * Blade shell to open a session (options via AJAX).
     */
    public function create(): View
    {
        return view('sessions.create');
    }

    /**
     * Blade shell for live QR + realtime list (data via AJAX + Echo).
     */
    public function show(AttendanceSession $session): View
    {
        return view('sessions.live', [
            'sessionId' => $session->id,
        ]);
    }
}
