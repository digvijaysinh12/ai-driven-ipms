<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Models\MentorAssignment;

class EnsureNotAssigned
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        $hasAssignment = MentorAssignment::where('intern_id',$user->id)
                        ->where('is_active',true)
                        ->exists();

        if($hasAssignment){
            return redirect()->route('intern.dashboard');
        }
        return $next($request);
    }
}
