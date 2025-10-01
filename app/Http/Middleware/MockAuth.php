<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MockAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Mock user authentication for demo purposes
        if (!auth()->check()) {
            // Create a mock user session
            session([
                'mock_user' => [
                    'id' => 1,
                    'name' => 'Demo User',
                    'email' => 'demo@shop66.com',
                    'role' => 'admin'
                ],
                'current_store_id' => 1,
                'current_store_name' => 'Demo Store'
            ]);
        }

        return $next($request);
    }
}