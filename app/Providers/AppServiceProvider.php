<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Wallet;
use App\Course;
// use App\UserCourse;
use App\Checkout;
use App\Cart;
use App\User;
use App\HistoryTransfer;
use Chat;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        View()->composer('layouts.master', function($view){
            if (auth()->check()) {
                if (auth()->user()->role_id == 10) {
                    // Initialize
                    $wallet = HistoryTransfer::sum('total_for_system');
                } else {
                    // Initialize
                    $wallet = Wallet::where('user_id', auth()->user()->id)->sum('balance');
                }

                $view->with('walletGlobal' , $wallet);
            }
        });

        View()->composer('layouts.master', function($view){
            if (auth()->check()) {
                if (auth()->user()->role_id == 1) {
                    // Initialize
                    $course = Course::where('user_id', auth()->user()->id)->count();
                } else {
                    // Initialize
                    $course = 0;
                    // $course = UserCourse::where(['user_id' => auth()->user()->id, 'status' => 1])->count();
                }

                $view->with('courseGlobal' , $course);
            }
        });

        View()->composer('layouts.master', function($view){
            if (auth()->check()) {
                // Initialize
                $nowDate        = date('Y-m-d H:i:s');
                // $waitingPayment = Checkout::where(['user_id' => auth()->user()->id, 'status_transaction' => 0, 'status_payment' => 0])->whereDate('expired_transaction', '>=', $nowDate)->count();
                $waitingPayment = 0;
                $view->with('waitingPaymentGlobal' , $waitingPayment);
            }
        });

        View()->composer('layouts.master', function($view){
            if (auth()->check()) {
                // Initialize
                $carts = Cart::where('user_id', auth()->user()->id)->count();

                $view->with('cartsGlobal' , $carts);
            }
        });

        View()->composer('layouts.master', function($view){
            if (auth()->check()) {
                // Initialize
                $participantModel = User::find(auth()->user()->id);
                $unreadCount      = Chat::messages()->setParticipant($participantModel)->unreadCount();

                $view->with('unreadCountGlobal' , $unreadCount);
            }
        });
    }
}
