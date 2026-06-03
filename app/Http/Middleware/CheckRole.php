<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login-demo')->with('error', 'Please log in to access this page.');
        }

        $user = auth()->user();

        // Map 'recruiter' alias if referenced anywhere as institution
        if ($role === 'institution' && !$user->isInstitution()) {
            return redirect()->route('login-demo')->with('error', 'Unauthorized. This area is reserved for Institutional Recruiters.');
        }

        if ($role === 'admin' && !$user->isAdmin()) {
            return redirect()->route('login-demo')->with('error', 'Unauthorized. This area is restricted to Platform Administrators.');
        }

        if ($role === 'candidate' && !$user->isCandidate()) {
            return redirect()->route('login-demo')->with('error', 'Unauthorized. This area is reserved for Faculty Candidates.');
        }

        return $next($request);
    }
}
