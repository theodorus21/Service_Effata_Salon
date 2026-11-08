<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRolePermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if ($permission === 'master_data') {
            $role = (string) $request->route('role');
            $action = match ($request->route()->getName()) {
                'master-data.index' => 'view',
                'master-data.create', 'master-data.store' => 'create',
                'master-data.show' => 'detail',
                'master-data.image' => 'images',
                'master-data.edit', 'master-data.update' => 'update',
                'master-data.destroy' => 'delete',
                default => '',
            };
            $permission = "master_data.{$role}.{$action}";
        }

        abort_unless($request->user()?->hasPermission($permission), 403);

        return $next($request);
    }
}
