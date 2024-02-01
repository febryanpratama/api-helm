<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\ChatReply;
use App\UserFcm;
use App\Course;
use Chat;
use DB;

use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;

class ChatsController extends Controller
{
    // Initialize
    protected $database;

    public function __construct()
    {
        $this->database = app('firebase.database');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('chats.index');
    }

    public function listGroup()
    {
        // Initialize
        $data = [];
        $user = User::find(auth()->user()->id);
        
        foreach (auth()->user()->listUserConversation() as $key => $value) {
            // Initialize
            $conversation = Chat::conversations()->getById($value->conversation_id);
            $messages     = Chat::conversation($conversation)->setPaginationParams(['sorting' => 'desc'])->setParticipant($user)->limit(1)->getMessages();

            $value->last_message        = $messages;
            $participants               = $conversation->getParticipants();
            $value->totalParticipants   = count($participants);

            $unreadMessage        = Chat::conversation($conversation)->setParticipant($user)->unreadCount();
            $value->unreadMessage = $unreadMessage;
             
            $data[] = $value;
        }

        return response()->json([
            'success' => true,
            'message' => 'List Chats Group',
            'data'    => $data,
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $conversation)
    {
        // Initialize
        $conversation    = Chat::conversations()->getById($conversation);
        $getParticipants = $conversation->getParticipants();

        if (request()->message) {
            $message = Chat::message(request()->message)
                        ->from(auth()->user())
                        ->to($conversation)
                        ->send();
        }

        // Check file
        if (request()->hasFile('upload_file')) {
             // Store to storage
             $path = request('upload_file')->store('uploads/chat', 'public');
                
             // Initialize
             $extension = request('upload_file')->getClientOriginalExtension();
        
             // Check Extension
             if ($extension == 'xlsx' || $extension == 'docx' || $extension == 'pdf' || $extension == 'sql' || $extension == 'csv' || $extension == 'txt' || $extension == 'pptx') {
                 // Initialize
                 $typeFile = 'document';
             } elseif ($extension == 'jpg' || $extension == 'jpeg' || $extension == 'png' || $extension == 'gif') {
                 // Initialize
                 $typeFile = 'image';
             } elseif ($extension == 'mp4' || $extension == 'mkv') {
                 // Initialize
                 $typeFile = 'video';
             }

             if (!isset($typeFile)) {
                 return response()->json([
                     'success'    => false,
                     'message'   => 'upload gagal file tidak di izinkan'
                 ]);
             }
        
             $message = Chat::message(env('SITE_URL') . '/storage/' . $path)
                     ->type($typeFile)
                     ->from(auth()->user())
                     ->to($conversation)
                     ->send();
        }

        if (request('reply_chat_id')) {
            ChatReply::create([
                'chat_message_id' => $message->id,
                'chat_reply_id'   => request('reply_chat_id')
            ]);
        }

        // Initialize Firebase
        $getMessage  = Chat::messages()->getById($message->id);
        $getMessages = $this->manageMessage($getMessage);

        if ($getMessages) {
            $toFirbase = $this->database->getReference(env('FIREBASE_CHAT_REFERENCE'))->getChild($conversation->id)->getChild($message->id)->set($getMessages);
        }

        // Send FCM notification
        foreach($getParticipants as $val) {
            if ($val->id != auth()->user()->id) {
                // Send FCM
                $userFcm = UserFcm::where('user_id', $val->id)->first();

                if ($userFcm) {
                    $parameter = [
                        'title'     => 'Pesan Baru',
                        'message'   => $request->message,
                        'token'     => $userFcm->fcm_id,
                        'data'      => [
                            'conversation_id'   => $conversation->id,
                            'type'              => 'new_chat',
                        ]
                    ];

                    $fcm = $this->fcmSend($parameter);
                }
            }
        }

        // Get Instructor
        $administrator = DB::table('course_chat')
                            ->where('conversation_id', $conversation->id)
                            ->first();

        if ($administrator) {
            // Initialize
            $course         = Course::where('id', $administrator->course_id)->first();
            $userDetail     = User::where('id', $course->user_id)->first();
            $manageMessage  = strip_tags($request->message);

            if ($userDetail->id != auth()->user()->id) {
                \Mail::to($userDetail->email)->send(new \App\Mail\NewChatGroup($course->user, $manageMessage, auth()->user()->name));
            }
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Data ditambahkan',
            'data'      => $getMessages
        ]);
    }

    private function manageMessage($message)
    {
        // Initialize
        $row['id']                  = $message->id;
        $row['body']                = $message->body;
        $row['conversation_id']     = $message->conversation_id;
        $row['participation_id']    = $message->participation_id;
        $row['type']                = $message->type;
        $row['read_at']             = $message->read_at;
        $row['deleted_at']          = $message->deleted_at;
        $row['messageable_id']      = $message->messageable_id;
        $row['notification_id']     = $message->notification_id;
        $row['is_seen']             = $message->is_seen;
        $row['is_sender']           = $message->is_sender;
        $row['created_at']          = $message->created_at;
        $row['updated_at']          = $message->updated_at;
        $row['participation']       = $message->participation;
        $row['messageable']         = $message->participation->messageable;
        $row['sender']              = $message->sender;
        $row['receiver_id']         = $message->receiver_id;
        $row['link']                = $message->link;

        // Initialize
        $courseChat = DB::table('course_chat')->where('conversation_id', $message->conversation_id)->first();
        $courseId   = null;

        if ($courseChat) {
            $courseId = $courseChat->course_id;
        }

        $row['course_id'] = $courseId;

        // Check Reply Message
        $replyMessage = ChatReply::where('chat_message_id', $message->id)->first();

        if ($replyMessage) {
           // Get Master Message
           $masterMessage = Chat::messages()->getById($replyMessage->chat_reply_id);

           $row['replyMessage']           = true;
           $row['reply_message_type']     = $masterMessage->type;
           $row['reply_message_body']     = $masterMessage->body;
           $row['reply_message_sender']   = $masterMessage->sender->name;
           $row['reply_message_created']  = $masterMessage->created_at;
        } else {
           $row['replyMessage'] = false;
        }

        // Check Log
        if ($message->body == 'is_log' && $message->is_log) {
           $chatLog = DB::table('chat_log')->where(['chat_messages_id' => $message->id])->first();

           if ($chatLog) {
               if ($chatLog->category == '1') {
                   $addUser  = User::where('id', $chatLog->add_user_id)->first();
                   $joinUser = User::where('id', $chatLog->join_user_id)->first();

                   if (auth()->user()->id == $addUser->id) {
                       $addUserName = 'Anda ';
                   } else {
                       $addUserName = $addUser->name;
                   }

                   if (auth()->user()->id == $joinUser->id) {
                       $joinUserName = 'Anda ';
                   } else {
                       $joinUserName = $joinUser->name;
                   }

                   $logDescription = $addUserName.' telah menambahkan '.$joinUserName;
               }

               $row['is_log']          = true;
               $row['category_log']    = $chatLog->category;
               $row['log_description'] = $logDescription;
           }
        } else {
           $row['is_log'] = false;
        }

        return $row;
    }

    public function listMember($conversation)
    {
        // Initialize
        $conversation = Chat::conversations()->getById($conversation);
        $participants = $conversation->getParticipants();

        return response()->json([
            'status'        => true,
            'message'       => 'Data tersedia',
            'total_member'  => count($participants)
        ]);
    }

    public function readAllMessage($conversation)
    {
        // Initialize
        $conversation = Chat::conversations()->getById($conversation);
        $user         = User::find(auth()->user()->id);

        // Read all message
        Chat::conversation($conversation)->setParticipant($user)->readAll();

        // Count Unread Message
        $unreadCount  = Chat::messages()->setParticipant($user)->unreadCount();

        return response()->json([
            'status'    => true,
            'message'   => 'Read All Message Success',
            'data'      => [
                'unread_count' => $unreadCount
            ]
        ]);
    }

    private function fcmSend($parameter = []) {
        // Initialize
        $notificationBuilder = new PayloadNotificationBuilder($parameter['title']);
        $notificationBuilder->setBody($parameter['message'])
                            ->setSound('default');

        $param_data = ['a_data' => 'my_data'];
        
        if(isset($parameter['data'])){
            $param_data = $parameter['data'];
        }
        
        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData($param_data);
        $data        = $dataBuilder->build();

        $optionBuilder  = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60*20);
        $option         = $optionBuilder->build();
        $notification   = $notificationBuilder->build();

        $token = env("FCM_SENDER_ID");

        if(isset($parameter['token']) && !empty($parameter['token'])){
            $token = $parameter['token'];
        }

        $downstreamResponse = FCM::sendTo($token, $option, $notification, $data);

        return [
                'ns' => $downstreamResponse->numberSuccess(),
                'nf' => $downstreamResponse->numberFailure(),
                'nm' => $downstreamResponse->numberModification(),
                'te' => $downstreamResponse->tokensWithError(),
                'lr' =>$downstreamResponse->hasMissingToken()
            ];
    }
}
