<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Public classroom channel for live attendance list (seminar demo)
Broadcast::channel('attendance-sessions.{sessionId}', function () {
    return true;
});
