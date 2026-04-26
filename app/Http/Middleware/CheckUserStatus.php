<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return $next($request);
        }

        if ($user->status === 'banned') {
            auth()->logout();
            session()->flash('failed_message', 'Akun Anda telah dibanned. Silakan hubungi administrator untuk informasi lebih lanjut.');
            return to_route('auth.view');
        }

        if ($user->isSuspended()) {
            $until = $user->suspended_until;
            $reasonText = $user->suspension_reason;
            auth()->logout();

            $label = \App\Models\User::formatDurationLabel((int) now()->diffInSeconds($until, false));
            $reason = $reasonText ? ' Alasan: ' . $reasonText . '.' : '';
            session()->flash('failed_message', "Akun Anda sedang disuspend selama {$label} lagi.{$reason} Silakan coba lagi setelah periode suspensi berakhir.");
            session()->flash('suspended_until_iso', $until->toIso8601String());
            session()->flash('suspension_reason_text', $reasonText);
            return to_route('auth.view');
        }

        if ($user->is_suspended && !$user->isSuspended()) {
            $user->is_suspended = false;
            $user->suspended_until = null;
            $user->suspension_reason = null;
            $user->suspended_by = null;
            $user->suspended_at = null;
            $user->save();
        }

        return $next($request);
    }
}
