<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Service;

class EnsureHasPermission
{
    public function handle(Request $request, Closure $next, string $permissionIdOrParam, string $canValue = 'can_read'): Response
    {
        $permissionId = $this->resolvePermissionId($request, $permissionIdOrParam);
        
        if (!auth()->check() || !$permissionId || !hasPermission($permissionId, $canValue)) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }

    private function resolvePermissionId(Request $request, string $permissionIdOrParam): ?int
    {
        if (is_numeric($permissionIdOrParam)) {
            return (int) $permissionIdOrParam;
        }

        $serviceId = $request->route($permissionIdOrParam);
        
        if (!$serviceId) {
            return null;
        }

        $service = Service::with('category')->find($serviceId);
        
        if (!$service) {
            return null;
        }

        return getServicePermissionId($service);
    }
}
