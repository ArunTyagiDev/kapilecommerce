<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VendorMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || ! $user->isVendor() || ! $user->shop) {
            return redirect()->route('login')->with('error', 'Vendor access required.');
        }

        if (! $user->shop->is_active || ! $user->shop->is_approved) {
            return redirect()->route('login')->with('error', 'Your shop is not active or approved yet.');
        }

        return $next($request);
    }
}
