<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PlayerAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!session('player_id')) {
            return redirect('/login')->with('error', 'Please log in to access this area.');
        }
        return $next($request);
    }
}
