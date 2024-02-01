<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\UserFcm;
use App\ChatReply;
use App\Company;
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

        // Checking User Joined to this grup
        if ($conversation) {
            // Initialize
            $chatParticipation = DB::table('chat_participation')
                                    ->where([
                                        'conversation_id'   => $conversation->id,
                                        'messageable_id'    => auth()->user()->id
                                    ])
                                    ->first();

            if (!$chatParticipation) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Anda tidak masuk dalam grup.'
                ]);
            }
        }

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
                     'success'   => false,
                     'message'   => 'upload gagal file tidak di izinkan'
                 ]);
             }
        
             $message = Chat::message(env('SITE_URL') . '/storage/' . $path)
                     ->type($typeFile)
                     ->from(auth()->user())
                     ->to($conversation)
                     ->send();
        }

        if (!request()->message && !request()->hasFile('upload_file')) {
            return response()->json([
                'message'   => 'The given data was invalid.',
                'errors'    => [
                    'message' => [
                        'message or upload_file required.'
                    ]
                ]
            ]);
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
            'status'    => 'success',
            'message'   => 'Berhasil mengirim chat',
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

    public function listGroup()
    {
        // Initialize
        $data = [];
        $user = User::find(auth()->user()->id);
        
        foreach (auth()->user()->listUserConversation() as $key => $val) {
            // Initialize
            $conversation = Chat::conversations()->getById($val->conversation_id);
            $lastMessages = Chat::conversation($conversation)->setPaginationParams(['sorting' => 'desc'])->setParticipant($user)->limit(1)->getMessages();
            $details      = json_decode($val->data, true);
            $dataLast     = [];

            foreach($lastMessages as $lastMessage) {
                $rowLast['id']                  = $lastMessage->id;
                $rowLast['conversation_id']     = $lastMessage->conversation_id;
                $rowLast['participation_id']    = $lastMessage->participation_id;
                $rowLast['body']                = $lastMessage->body;
                $rowLast['type']                = $lastMessage->type;

                $dataLast[] = $rowLast;
            }

            // Initialize
            $courseChat = DB::table('course_chat')->where('conversation_id', $val->conversation_id)->first();
            $courseId   = null;
            $group_name_exp = explode('|', $details['title']);

            if ($courseChat) {
                $courseId = $courseChat->course_id;
            }

            $row['conversation_id'] = $val->conversation_id;
            $row['messageable_id']  = $val->messageable_id;
            $row['course_id']       = $courseId;
            
            $row['group_name']      = $group_name_exp[0];
            if (auth()->user()->role_id == 1) { // group name for admin toko
                $row['group_name']      = isset($group_name_exp[1]) ? $group_name_exp[1] : $group_name_exp[0];
            }
            $row['group_avatar']    = env('SITE_URL').'/img/auth/group.png';
            $row['last_message']    = $dataLast;

            // Initialize
            $unreadMessage          = Chat::conversation($conversation)->setParticipant($user)->unreadCount();
            $row['unread_message']  = $unreadMessage;
            $row['created_at']      = $val->created_at;
            $row['updated_at']      = $val->updated_at;
             
            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan Obrolan Grup',
            'data'      => $data,
        ], 200);
    }

    public function listGroupByCourse($courseId)
    {
        // Initialize
        $data       = [];
        $user       = User::find(auth()->user()->id);
        $courseChat = DB::table('course_chat')->where('course_id', $courseId)->first();

        if (!$courseChat) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data ('.$courseId.') tidak ditemukan',
            ]);
        }

        // Initialize
        $conversation = Chat::conversations()->getById($courseChat->conversation_id);
        $lastMessage  = DB::table('chat_messages')->where('conversation_id', $courseChat->conversation_id)->latest()->first();

        if ($conversation) {
            $row['conversation_id'] = $conversation['id'];
            $row['course_id']       = $courseId;
            $row['group_name']      = $conversation['data']['title'];
            $row['group_avatar']    = env('SITE_URL').'/img/auth/group.png';
            $row['last_message']    = $lastMessage;

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan Obrolan Grup',
            'data'      => $data
        ], 200);
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
            'status'    => 'success',
            'message'   => 'Read All Message Success',
            'data'      => [
                'unread_count' => $unreadCount
            ]
        ]);
    }


    // Tanya Penjual (Create group)
    public function createGroupCompanyCustomer(Company $company)
    {
        $group_chat = \DB::table('chat_conversations')->where('company_id', $company->ID)->where('customer_id', auth()->user()->id)->first();

        if (!$group_chat) { // not exist create new group
            
            $participants = [auth()->user()];
    
            // Chat
            $conversation = Chat::createConversation($participants)->makePrivate(false);
    
    
            if ($conversation) {
                $data = ['title' => $company->Name . '|' . auth()->user()->name, 'description' => 'chat group toko dan customer'];
                $conversation->update(['data' => $data]);

                $user_id = auth()->user()->id;
                \DB::statement("UPDATE chat_conversations SET chat_conversations.company_id = '$company->ID', chat_conversations.customer_id = '$user_id' where chat_conversations.id = '$conversation->id'");
                    
                $company_participant = $company->admin;
    
                // admin toko
                if (count($company_participant) > 0) {
                    for ($i=0; $i < count($company_participant) ; $i++) { 
                        $user = \App\User::find($company_participant[$i]->id);
    
                        $check = \DB::table('chat_participation')->where('conversation_id', $conversation->id)->where('messageable_id', $user->id)->first();
    
                        if (!$check) {
                            $add_participants = Chat::conversation($conversation)->addParticipants([$user]);
                        }
                    }
                }
    
                return response()->json([
                    'status'    => 'success',
                    'code'      => 200,
                    'message'   => 'Pesan Grup berhasil dibuat',
                    'data'    => $conversation
                ]);
    
                die;
            }
        } else {
            $conversation = Chat::conversations()->getById($group_chat->id);
            return response()->json([
                'status'    => 'success',
                'code'      => 200,
                'message'   => 'Group chat',
                'data'    => $conversation
            ]);
        }

        return response()->json([
            'status'    => 'error',
            'code'      => 400,
            'message'   => 'Pesan Grup gagal dibuat',
            'data'    => null
        ]);
    }

    // Tanya Pembeli (Create group)
    public function createGroupCustomerCompany(User $user)
    {
        $company = Company::find(auth()->user()->company_id);
        $group_chat = \DB::table('chat_conversations')->where('company_id', $company->ID)->where('customer_id', $user->id)->first();

        if (!$group_chat) { // not exist create new group
            
            $participants = [auth()->user()];
    
            // Chat
            $conversation = Chat::createConversation($participants)->makePrivate(false);
    
    
            if ($conversation) {
                $data = ['title' => $company->Name . '|' . auth()->user()->name, 'description' => 'chat group toko dan customer'];
                $conversation->update(['data' => $data]);

                $user_id = auth()->user()->id;
                \DB::statement("UPDATE chat_conversations SET chat_conversations.company_id = '$company->ID', chat_conversations.customer_id = '$user_id' where chat_conversations.id = '$conversation->id'");
                    
                $company_participant = $company->admin;
    
                // admin toko
                if (count($company_participant) > 0) {
                    for ($i=0; $i < count($company_participant) ; $i++) { 
                        $user = \App\User::find($company_participant[$i]->id);
    
                        $check = \DB::table('chat_participation')->where('conversation_id', $conversation->id)->where('messageable_id', $user->id)->first();
    
                        if (!$check) {
                            $add_participants = Chat::conversation($conversation)->addParticipants([$user]);
                        }
                    }
                }
    
                return response()->json([
                    'status'    => 'success',
                    'code'      => 200,
                    'message'   => 'Pesan Grup berhasil dibuat',
                    'data'    => $conversation
                ]);
    
                die;
            }
        } else {
            $conversation = Chat::conversations()->getById($group_chat->id);
            return response()->json([
                'status'    => 'success',
                'code'      => 200,
                'message'   => 'Group chat',
                'data'    => $conversation
            ]);
        }

        return response()->json([
            'status'    => 'error',
            'code'      => 400,
            'message'   => 'Pesan Grup gagal dibuat',
            'data'    => null
        ]);
    }
}
