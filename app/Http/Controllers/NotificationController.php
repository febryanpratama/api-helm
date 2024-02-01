<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notif = array();
        if (auth()->check()) {
			$user = \App\User::findOrFail(auth()->user()->id);
			$notif = $user->unreadNotifications()->limit(10)->get()->toArray();
		}
        return view('notification.index', compact('notif'));
    }

    public function readNotif($id)
	{
		if (auth()->check()) {
			$userUnreadNotification = auth()->user()
                                  ->notifications
                                  ->where('id', $id)
                                  ->first();
    
			if($userUnreadNotification) {
				$userUnreadNotification->markAsRead();

				if ($userUnreadNotification->type == 'App\Notifications\Task') { // redirect detail task
					return redirect()->route('todo.detail_task', $userUnreadNotification->data['task']['id']);
				}

				if ($userUnreadNotification->type == 'App\Notifications\Todo') { // redirect detail todo
					return redirect()->route('todo.detail', $userUnreadNotification->data['todo']['id']);
				}

				if ($userUnreadNotification->type == 'App\Notifications\TransactionSuperAdmin') { // redirect detail transaction (for role super admin)
					return redirect()->route('superadmin.detail_transaction', $userUnreadNotification->data['transaction']['ID']);
				}

				if ($userUnreadNotification->type == 'App\Notifications\TransactionApprove') { // redirect detail transaction (for role super admin)
					return redirect()->route('profile.company_edit', ['company' => $userUnreadNotification->data['company']['ID']]);
				}

				if ($userUnreadNotification->type == 'App\Notifications\TaskDiscuss') { // redirect detail task (for discuss)
					return redirect()->route('todo.detail_task', $userUnreadNotification->data['task']['id']);
				}

				if ($userUnreadNotification->type == 'App\Notifications\TodoDiscuss') { // redirect detail todo (for discuss)
					return redirect()->route('todo.detail', $userUnreadNotification->data['todo']['id']);
				}
			}

			return redirect()->route('company:home', \Str::slug(auth()->user()->company->Name));
		}
	}

    /**
	 * notifications
	 *
	 * @return array
	 */
	public function notifications()
	{
		if (auth()->check()) {
			$user = \App\User::findOrFail(auth()->user()->id);
			return $user->notifications()->limit(10)->get()->toArray();
		}
	}
}
