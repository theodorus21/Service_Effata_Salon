<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureMemberRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('member')->user();

        abort_unless(
            $user?->role === User::ROLE_STUDENT
                && $user->status === 'active',
            403,
        );

        return $next($request);
    }
}
