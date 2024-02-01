<?php

namespace App\Http\Middleware;

use Closure;
use App\HintWidget;

class TourWidget
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Initialize
        $userId = auth()->user()->id;

        if ($request->url() == env('SITE_URL').'/paket-kursus') {
            // Initialize
            $page           = 'course-package-page';
            $checkExistData = HintWidget::where(['user_id' => $userId, 'page' => $page])->first();
            
            if (!$checkExistData) {
                HintWidget::create([
                    'user_id' => $userId,
                    'page'    => $page
                ]);
            }
        }

        return $next($request);
    }
}
