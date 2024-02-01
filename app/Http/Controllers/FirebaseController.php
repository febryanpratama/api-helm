<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;
use Chat;

class FirebaseController extends Controller
{

    protected $database;
    public function __construct()
    {
        $this->database = app('firebase.database');
    }

    public function store()
    {
        $conversation = Chat::conversations()->getById(34);
        $message = Chat::message("testing")
                    ->from(auth()->user())
                    ->to($conversation)
                    ->send();
        // $message->receiver_id = $receiver;
        // $message->link = $link;
        $message->save();

        $newPostKey = $this->database->getReference('chat_message')->push($message);
    }

    public function check()
    {
        $check = $this->database->getReference('chat_m')->getValue();

        dd($check);
    }
}
