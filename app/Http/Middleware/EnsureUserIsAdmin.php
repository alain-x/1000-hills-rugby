<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless($user && in_array($user->role, ['admin', 'editor', 'coach'], true) && $user->is_active, 403, 'You do not have permission to access the admin panel.');

        if ($user->isCoach() && ! $request->routeIs('admin.attendance.*', 'admin.sessions.*', 'admin.reports.*', 'admin.dashboard', 'admin.dashboard.index')) {
            abort(403, 'Coaches can plan sessions, record attendance and write player reports only.');
        }

        return $next($request);
    }
}
