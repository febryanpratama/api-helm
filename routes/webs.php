<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

use App\Events\TestingEvent;

Auth::routes();

Route::get('/', 'LandingPageController@index')->name('landing');
Route::get('/harga', 'LandingPageController@price')->name('landing.price');
Route::get('/faq', 'LandingPageController@faq')->name('landing.faq');
Route::get('/daftar/artikel', 'LandingPageController@article')->name('landing.article');
Route::get('/artikel/read/{slug}', 'LandingPageController@articleRead')->name('landing.article.read');

Route::get('/tesing-push', function() {
    return view('testing');
});

Route::get('/check-firebase', 'FirebaseController@check');

Route::get('/tes-mail', function() {
    return \Mail::to(request()->get('email'))->send(new \App\Mail\Tes());
});


// ==== DEMO ====
Route::group(['prefix' => 'demo'], function(){
    Route::name('demo.')->group(function () {
        Route::get('/auth', 'DemoController@index')->name('auth.index');
    });
});
// ==== DEMO ====

// Route::get('/broadcast', function () {
//     broadcast(new \App\Events\RealTimeNotif());
// });

Route::get('/testing-env', function () {
    event(new TestingEvent(request()->get('message') . ' - ' . env('PUSHER_APP_KEY')));
});

Route::get('/product', 'LandingPageController@product')->name('landing.product');
Route::get('/service', 'LandingPageController@service')->name('landing.service');
Route::post('/push', 'LangController@tesPush');

Route::get('lang/{lang}', 'LangController@switchLang')->name('lang.switch');
Route::post('sign', 'AuthController@validation')->name('auth.sign_process');
Route::post('upload-foto', 'AuthController@uploadFoto')->name('auth.upload_foto');
Route::get('/auth', 'AuthController@index')->name('auth.index');
Route::get('/verify', 'AuthController@verifyOtp')->name('auth.verify');
Route::post('/verify/otp', 'AuthController@otpStore')->name('auth.otp_store');
Route::post('/auth/verification', 'AuthController@validationOtp')->name('auth.otp_verify');

Route::get('/user/save-people', 'ProfileController@userToPeople');

// route supervisor, member, admin, management
Route::middleware(['auth', 'roles:8,6,1,2'])->group(function () {
    // Route::get('/list-users/todo/{task}', 'TodosController@showUsers');
    // Route::get('/list-discussion/todo', 'TodosController@listDiscussion');
});

Route::get('download/{file?}', 'AuthController@download')->name('download');
Route::post('api/konverter/user', 'KonverterController@insertUser');

$prefix = \App\Company::where('IsConfirmed', 'y')->get();

foreach ($prefix as $key => $value) {
    $slug = $value->Type . '-'. \Str::slug($value->Name);

    Route::group(['prefix'=>$slug, 'as' => 'company:'], function(){
        Route::get('/auth', 'AuthController@index')->name('auth.index');
    });
}

Route::middleware(['auth'])->group(function () {

    // home all user (dashboard)
    Route::get('/home', 'HomeController@index')->name('home');
    Route::get('/notification', 'NotificationController@index')->name('notif.index');
    Route::get('/get/notification', 'NotificationController@notifications')->name('notif.list');
    Route::get('/get/notification/read/{id}', 'NotificationController@readNotif')->name('notif.read');

    Route::get('logout', 'AuthController@logoutIndex')->name('auth.logout');
    Route::post('logout-process', 'AuthController@logout')->name('auth.logout_process');

    Route::post('message/store', 'MessagesController@store')->name('messages.store');
    Route::post('/conversation/create', 'ChatsController@newConversation')->name('conversation.create');
    Route::get('/conversation/list/{user_id}', 'ChatsController@listUserConversation')->name('conversation.list');
    Route::post('/conversation/add-participants/{conversation_id}', 'ChatsController@addParticipants')->name('conversation.add_participant');
    Route::post('/conversation/remove-participants/{conversation_id}', 'ChatsController@removeParticipants')->name('conversation.remove_participant');
    Route::post('/conversation/chat/{conversation_id}', 'ChatsController@message')->name('conversation.chat');
    Route::post('/conversation/update/{conversation_id}', 'ChatsController@updateConversation')->name('conversation.update');
    Route::post('/conversation/read-all-message-conversation/{conversation_id}', 'ChatsController@readAllMessageByConversation');

    Route::delete('/conversation/delete/{id}', 'ChatsController@deleteConversation')->name('conversation.delete');

    // Knowledge
    Route::get('/knowledge/{knowledge}', 'KnowledgesController@index')->name('knowledge.index');
    Route::post('/knowledge/create', 'KnowledgesController@store')->name('knowledge.store');
    Route::delete('/knowledge/delete/{knowledge}', 'KnowledgesController@destroy')->name('knowledge.delete');

    // response json
    Route::get('/group/list', 'ChatsController@listGroup')->name('group.list');
    Route::get('/group/list/message/{conversation}', 'ChatsController@listMessage')->name('group.chat');
    Route::post('/group/list/send-message/{conversation}', 'ChatsController@messageJson')->name('group.chat_send');
    Route::get('/group/detail/{id}', 'ChatsController@detailGroup')->name('group.detail');

    // Chat
    Route::get('/list/member/chat', 'ChatsController@listMemberAll')->name('chat.list.member.all');
    Route::get('/list/member/chat/{conversation}', 'ChatsController@listMember')->name('chat.list.member');
    Route::get('/count/unread-message', 'ChatsController@countUnReadMessage')->name('count.unread.message');
    Route::get('/subscribe/expired', 'SubscriptionController@memberExpired')->name('subscribe.member_expired');
    Route::get('/search-list-member/chat', 'ChatsController@searchListMember');
});

// route super admin
Route::group(['middleware' => ['auth', 'roles:10']], function(){
    Route::get('/manage', 'SuperUserAdminController@index')->name('superadmin.index');
    Route::get('/manage/transaction', 'SuperUserAdminController@listTransaction')->name('superadmin.list_transaction');
    Route::get('/manage/company', 'SuperUserAdminController@listCompany')->name('superadmin.list_company');
    Route::get('/manage/company/{company}', 'SuperUserAdminController@detailCompany')->name('superadmin.detail_company');
    Route::get('/manage/payment/{payment}', 'SuperUserAdminController@detailTransaction')->name('superadmin.detail_transaction');
    Route::post('/manage/payment/{payment}', 'SuperUserAdminController@paymentVerify')->name('superadmin.transaction_verify');
});

// route admin
Route::group(['middleware' => ['auth', 'roles:1']], function(){
    Route::get('/admin', 'HomeController@admin')->name('admin.index');
    Route::get('/user', 'HomeController@dataUser')->name('user.data');
    Route::get('/user/create', 'HomeController@userForm')->name('user.form');
    Route::get('/user/edit/{user}', 'HomeController@editUser')->name('user.edit');
    Route::post('/user/store', 'HomeController@store')->name('user.store');
    Route::post('/user/delete/{user}', 'HomeController@delete')->name('user.delete');

    Route::get('/report', 'ReportsController@index')->name('report.index');
    Route::get('/report/attendance', 'ReportsController@attendance')->name('report.attendance');
    Route::get('/reports', 'ReportsController@reportTaskAttendance')->name('report.task_attendance');
    Route::get('/reports/task-list/{user}', 'ReportsController@taskListByUser')->name('report.task_list_by_user');
    Route::get('/reports/attendance-count/{user}', 'ReportsController@attendanceCountDay');
    Route::get('/reports/attendance/{user}', 'ReportsController@attendanceByUser')->name('report.attendance_by_user');
    Route::get('/reports/excel', 'ReportsController@downloadExcelAttendanceTask')->name('report.excel_attendance_task');
    Route::get('/reports/{user}', 'ReportsController@detialReportTaskAttendance')->name('report.detail_task_attendance');

    Route::get('/report/task', 'ReportsController@task')->name('report.task');
    Route::get('/report/task-download', 'ReportsController@downloadExcelTask')->name('report.task_download');

    Route::get('/report/attendance/{user}', 'ReportsController@userAttendance')->name('report.attendance_user_detail');
    Route::get('/report/attendance-download', 'ReportsController@downloadExcel')->name('report.attendance_download');

    // update profile
    Route::get('/profile/edit/{user}', 'ProfileController@edit')->name('profile.edit');
    Route::patch('/profile/update/{user}', 'ProfileController@update')->name('profile.update');

    Route::get('/transaction/agency', 'TransactionController@agency')->name('transaction.agency');
    Route::post('/transaction/company', 'TransactionController@transactionCompany')->name('transaction.company');
    Route::get('/transaction/package', 'TransactionController@listPackage')->name('transaction.package');
    Route::get('/transaction/payment', 'TransactionController@payment')->name('transaction.payment');
    Route::get('/transaction/detail/{payment}', 'TransactionController@detail')->name('transaction.detail');
    Route::post('/transaction/payment/store', 'TransactionController@paymentStore')->name('transaction.store');

    Route::get('/transaction/register', 'TransactionController@registerTransaction')->name('transaction.register');
    Route::post('/transaction/register/store', 'TransactionController@registerStore')->name('transaction.register_store');
    
    // === Demo ===
    Route::group(['prefix' => 'demo'], function(){
        Route::name('demo.')->group(function () {
            Route::post('/active-account', 'DemoController@activeAccount')->name('active.account');
        });
    });
    // === Demo ===
    
    // === Upgrade Account ===
    Route::group(['prefix' => 'upgrade-akun'], function(){
        Route::name('upgrade.account.')->group(function () {
            Route::get('/', 'UpgradeAccountController@index')->name('index');
            Route::post('/register', 'UpgradeAccountController@register')->name('register');
        });
    });
    // === Upgrade Account ===

    Route::get('/propose', 'TransactionController@propose')->name('transaction.propose');
    Route::post('/propose', 'TransactionController@proposeStore')->name('transaction.propose_store');
    Route::get('/propose/detail', 'TransactionController@proposeDetail')->name('transaction.propose_detail');
    Route::get('/propose/approve/{user}', 'HomeController@proposeApprove')->name('admin.propose_approve');

    Route::get('/company/create', 'ProfileController@company')->name('profile.company_create');
    Route::get('/company/edit', 'ProfileController@editCompany')->name('profile.company_edit');
    Route::post('/company/store', 'ProfileController@companyStore')->name('profile.company_store');

    // memo
    Route::post('/memo/store', 'MemoController@store')->name('memo.store');
    Route::delete('/memo/delete/{memo}', 'MemoController@delete')->name('memo.delete');

    // motivation
    Route::post('/motivation/store', 'CompanyMotivationController@store')->name('motivation.store');
    Route::delete('/motivation/delete/{motivation}', 'CompanyMotivationController@delete')->name('motivation.delete');

    // Roles
    Route::get('/role/show', 'RoleController@show');

    // Hint Widget
    Route::post('/hint/widget/store', 'ReportsController@hintWidget')->name('hint.widget.store');

    // re subscribe
    Route::get('/subscribe/package', 'SubscriptionController@package')->name('subscribe.package');
    Route::get('/subscribe/transaction', 'SubscriptionController@transaction')->name('subscribe.transaction');
    Route::get('/subscribe/transaction/detail/{payment}', 'SubscriptionController@detail')->name('subscribe.transaction_detail');

    // Article
    Route::group(['prefix' => 'artikel'], function(){
        Route::name('article.')->group(function () {
            Route::get('/', 'ArticleController@index')->name('index');
            Route::post('store', 'ArticleController@store')->name('store');
            Route::get('show/{article}', 'ArticleController@show')->name('show');
            Route::post('update/{article}', 'ArticleController@update')->name('update');
            Route::post('destroy/{article}', 'ArticleController@destroy')->name('destroy');
        });
    });

    // Company
    Route::group(['prefix' => 'company'], function(){
        Route::name('company.')->group(function () {
            Route::post('update/{company}', 'CompanyController@update')->name('update');
        });
    });

    // Course
    Route::group(['prefix' => 'course'], function(){
        Route::name('course.')->group(function () {
            Route::get('/', 'CourseController@index')->name('index');
            Route::post('store', 'CourseController@store')->name('store');
            Route::get('show/{course}', 'CourseController@show')->name('show');
            Route::get('edit/{course}', 'CourseController@edit')->name('edit');
            Route::post('update/{course}', 'CourseController@update')->name('update');
            Route::delete('delete/{course}', 'CourseController@destroy')->name('destroy');
            Route::get('count', 'CourseController@courseCount')->name('count');
            Route::post('publish/{course}', 'CourseController@publish')->name('publish');
        });
    });

    // Majors
    Route::group(['prefix' => 'majors'], function(){
        Route::name('majors.')->group(function () {
            Route::get('/', 'MajorsController@index')->name('index');
            Route::post('store', 'MajorsController@store')->name('store');
            Route::get('{majors}', 'MajorsController@show')->name('show');
            Route::get('edit/{majors}', 'MajorsController@edit')->name('edit');
            Route::post('update/{majors}', 'MajorsController@update')->name('update');
            Route::delete('delete/{majors}', 'MajorsController@destroy')->name('destroy');
        });
    });

    // Subject
    Route::group(['prefix' => 'subject'], function(){
        Route::name('subject.')->group(function () {
            Route::get('/', 'SubjectController@index')->name('index');
            Route::post('/store', 'SubjectController@store')->name('store');
            Route::get('/edit/{subject}', 'SubjectController@edit')->name('edit');
            Route::post('/update/{subject}', 'SubjectController@update')->name('update');
            Route::delete('/delete/{subject}', 'SubjectController@destroy')->name('destroy');
            Route::delete('/detach/{division}', 'SubjectController@destroySubjectRelation')->name('destroy.relation');
        });
    });
});


// route supervisor
Route::group(['middleware' => ['auth', 'roles:8']], function(){
    // Route::get('/supervisor', 'TasksController@supervisor')->name('home.supervisor');
    // Route::get('/supervisor/task-create', 'TasksController@supervisorCreateTask')->name('task.supervisor_create_task');
    // Route::get('/supervisor/task-edit/{task}', 'TasksController@supervisorEditTask')->name('task.supervisor_edit_task');
    // Route::post('/supervisor/task-store', 'TasksController@supervisorStoreTask')->name('task.supervisor_store_task');
    // Route::delete('/supervisor/task-delete/{task}', 'TasksController@supervisorDeleteTask')->name('task.supervisor_delete_task');

    // Route::post('/project/store', 'ProjectController@store')->name('project.store');
    // Route::patch('/project/update/{project}', 'ProjectController@update')->name('project.update');
});

// route supervisor, member
Route::middleware(['auth', 'roles:8,6'])->group(function () {
    // dipindah
    // Route::post('todo/{task}', 'TodosController@store')->name('todo.store');
    // Route::post('todo-done/{todo}', 'TodosController@isDone')->name('todo.done');
    // Route::delete('todo-delete/{todo}', 'TodosController@delete')->name('todo.delete');
    // end dipindah
    Route::get('tes', function() {
        return view('supervisor.tes');
    });

    Route::get('home/task', 'TasksController@indexMember')->name('task.index_member');
});

Route::middleware(['auth', 'roles:9'])->group(function () {
    Route::get('/client', 'ClientUserController@index')->name('client.index');
    Route::get('/client/project-detail/{project}', 'ClientUserController@detailProject')->name('client.detail_project');
});   

// route supervisor, client
Route::middleware(['auth', 'roles:8,9'])->group(function () {
    // Route::get('/task/download/{id}', 'TasksController@download')->name('task.download');
});

// route supervisor, member, admin, management
Route::middleware(['auth', 'roles:8,6,1,2'])->group(function () {
    // Profile
    Route::get('/profile', 'ProfileController@index')->name('profile.index');
    Route::post('/profile/upload/file', 'ProfileController@uploadFile')->name('profile.upload.file');
    Route::patch('/profile/update/{user}', 'ProfileController@update')->name('profile.update');

    // Report
    Route::get('/report/area', 'UserReportController@index')->name('user.report.index');

    // Route::get('/supervisor', 'TasksController@supervisor')->name('home.supervisor');
    Route::get('/supervisor/task-create', 'TasksController@supervisorCreateTask')->name('task.supervisor_create_task');
    Route::get('/supervisor/task-edit/{task}', 'TasksController@supervisorEditTask')->name('task.supervisor_edit_task');
    Route::post('/supervisor/task-store', 'TasksController@store')->name('task.supervisor_store_task');
    Route::delete('/supervisor/task-delete/{task}', 'TasksController@supervisorDeleteTask')->name('task.supervisor_delete_task');
    Route::get('/supervisor/search/task', 'TasksController@searchTask')->name('task.supervisor_search_task');
    Route::post('/supervisor/task-update-status/{task}', 'TasksController@updateStatus')->name('task.supervisor_update_status_task');
    Route::post('/tinymce/upload-image', 'TasksController@uploadImage')->name('tinymce.upload.image');
    Route::get('/task/assigned/to/{task}', 'TasksController@assignedTo');

    // Project
    Route::post('/project/store', 'ProjectController@store')->name('project.store');
    // Route::get('/project/show/{project}', 'ProjectController@show')->name('project.show');
    // Route::get('/list-users/project/{project}', 'ProjectController@showUsers');
    Route::patch('/project/update/{project}', 'ProjectController@update')->name('project.update');
    Route::delete('/project/delete/{project}', 'ProjectController@destroy')->name('project.delete');

    Route::post('todo/{task}', 'TodosController@store')->name('todo.store');
    Route::post('todo-done/{todo}', 'TodosController@isDone')->name('todo.done');
    Route::delete('todo-delete/{todo}', 'TodosController@delete')->name('todo.delete');
    Route::post('todo/search', 'TodosController@searchToDo')->name('todo.search');

    Route::post('task-upload/{task}', 'TasksController@uploadReport')->name('task.upload');

    // user checks
    Route::post('/check/store', 'UserCheckController@store')->name('check.store');

    // activities
    Route::post('activity/todo/store', 'TodoActivitiesController@store')->name('todo_activity.store');
    Route::post('activity/todo-doing/{todo}', 'TodoActivitiesController@isDoing')->name('todo_activity.doing');
    Route::post('activity/todo-trouble/{todo}', 'TodoActivitiesController@isTrouble')->name('todo_activity.trouble');
    Route::post('activity/todo-hold/{todo}', 'TodoActivitiesController@isHold')->name('todo_activity.hold');
    Route::post('activity/todo-cancel/{todo}', 'TodoActivitiesController@isCancel')->name('todo_activity.cancel');
    Route::post('activity/todo-done/{todo}', 'TodoActivitiesController@isDone')->name('todo_activity.done');
    Route::post('activity/todo-revisi/{todo}', 'TodoActivitiesController@isRevisi')->name('todo_activity.revisi');
    Route::get('list/activity/{task}', 'TodoActivitiesController@listActivity')->name('todo_activity.list');

    // Chat
    Route::get('chat/area', 'ChatsController@index')->name('chat.area.index');
    
    // Division
    Route::group(['prefix' => 'division'], function(){
        Route::name('division.')->group(function () {
            Route::get('/', 'DivisionController@index')->name('index');
            Route::post('/store', 'DivisionController@store')->name('store');
            Route::get('/{division}', 'DivisionController@show')->name('show');
            Route::post('/update/{division}', 'DivisionController@update')->name('update');
            Route::delete('/delete/{division}', 'DivisionController@destroy')->name('destroy');

            // List Member
            Route::get('/member/{division}', 'DivisionController@member')->name('member');
        });
    });

    // Reward
    Route::group(['prefix' => 'reward'], function(){
        Route::name('reward.')->group(function () {
            Route::post('/store', 'RewardController@store')->name('store');
            Route::post('/store-user-reward', 'RewardController@storeUserReward')->name('store_user_reward');
        });
    });
});

// route supervisor, member, client, admin, management
Route::middleware(['auth', 'roles:8,6,9,1,2'])->group(function () {
    // To Do
    Route::get('todo/detail/{todo}', 'TodosController@detail')->name('todo.detail');
    Route::get('task/{task}', 'TodosController@detailTask')->name('todo.detail_task');
    Route::post('task-reply/{task}', 'TasksController@reply')->name('task.reply');
    Route::post('todo-reply/{todo}', 'TodosController@reply')->name('todo.reply');

    // Memo
    Route::get('memo/{memo}', 'MemoController@index')->name('memo.index');
    Route::get('memo/users/list', 'MemoController@memoUser')->name('memo.user');

    // Tasks
    Route::get('task/detail/{task}', 'TasksController@index')->name('task.index');
    Route::get('/list-task/user-acitivty', 'TasksController@listActivity');
    Route::get('task/edit/{task}', 'TasksController@edit')->name('task.edit');
    Route::get('task/project/show', 'TasksController@projects')->name('task.project');

    // Report Task
    Route::get('task/report/user/show', 'UserReportController@showReportUser');
    Route::get('task/report/user/assigned/show/{task}', 'UserReportController@showReportUserAssigned');

    // Project
    Route::get('/project/show/{project}', 'ProjectController@show')->name('project.show');
    Route::get('/list-users/project/{project}', 'ProjectController@showUsers');
    Route::get('/list-project/project', 'ProjectController@listProject');
    Route::get('/list-project/user-acitivty/{project}', 'ProjectController@listActivity');

    // list user todo, diskusi todo
    Route::get('/list-users/todo/{task}', 'TodosController@showUsers');
    Route::get('/list-discussion/todo', 'TodosController@listDiscussion');
    Route::get('/list-todo/task/{task}', 'TodosController@listToDo');

    // downlaod report task
    Route::get('/task/download/{id}', 'TasksController@download')->name('task.download');

    // Meet
    Route::group(['prefix' => 'meet'], function(){
        Route::name('meet.')->group(function () {
            Route::get('/', 'MeetController@index')->name('index');
            Route::get('{any}', 'MeetController@index');
        });
    });
});  

Route::group([
    'prefix'     => '/{company_name}',
    'middleware' => \App\Http\Middleware\CheckCompany::class,
    'as'         => 'company:',
], function () {
    Route::middleware(['auth'])->group(function () {

        // home all user (dashboard)
        Route::get('/home', 'HomeController@index')->name('home');
        Route::get('/notification', 'NotificationController@index')->name('notif.index');
    
        Route::get('logout', 'AuthController@logoutIndex')->name('auth.logout');
        Route::post('logout-process', 'AuthController@logout')->name('auth.logout_process');
    
        Route::post('message/store', 'MessagesController@store')->name('messages.store');
        Route::post('/conversation/create', 'ChatsController@newConversation')->name('conversation.create');
        Route::get('/conversation/list/{user_id}', 'ChatsController@listUserConversation')->name('conversation.list');
        Route::post('/conversation/add-participants/{conversation_id}', 'ChatsController@addParticipants')->name('conversation.add_participant');
        Route::post('/conversation/remove-participants/{conversation_id}', 'ChatsController@removeParticipants')->name('conversation.remove_participant');
        Route::post('/conversation/chat/{conversation_id}', 'ChatsController@message')->name('conversation.chat');
        Route::post('/conversation/update/{conversation_id}', 'ChatsController@updateConversation')->name('conversation.update');
    
        Route::delete('/conversation/delete/{id}', 'ChatsController@deleteConversation')->name('conversation.delete');
    
        Route::post('/knowledge/create', 'KnowledgesController@store')->name('knowledge.store');
        Route::delete('/knowledge/delete/{knowledge}', 'KnowledgesController@destroy')->name('knowledge.delete');
    });
    
    // route super admin
    Route::group(['middleware' => ['auth', 'roles:10']], function(){
        Route::get('/manage', 'SuperUserAdminController@index')->name('superadmin.index');
        Route::get('/manage/payment/{payment}', 'SuperUserAdminController@detailTransaction')->name('superadmin.detail_transaction');
        Route::post('/manage/payment/{payment}', 'SuperUserAdminController@paymentVerify')->name('superadmin.transaction_verify');
    });
    
    // route admin
    Route::group(['middleware' => ['auth', 'roles:1']], function(){
        Route::get('/admin', 'HomeController@admin')->name('admin.index');
        Route::get('/user', 'HomeController@dataUser')->name('user.data');
        Route::get('/user/create', 'HomeController@userForm')->name('user.form');
        Route::post('/user/store', 'HomeController@store')->name('user.store');
        Route::post('/user/delete/{user}', 'HomeController@delete')->name('user.delete');
    
        Route::get('/report', 'ReportsController@index')->name('report.index');
        Route::get('/report/attendance', 'ReportsController@attendance')->name('report.attendance');
        Route::get('/reports', 'ReportsController@reportTaskAttendance')->name('report.task_attendance');
        Route::get('/reports/excel', 'ReportsController@downloadExcelAttendanceTask')->name('report.excel_attendance_task');
        Route::get('/reports/{user}', 'ReportsController@detialReportTaskAttendance')->name('report.detail_task_attendance');
    
        Route::get('/report/task', 'ReportsController@task')->name('report.task');
        Route::get('/report/task-download', 'ReportsController@downloadExcelTask')->name('report.task_download');
    
        Route::get('/report/attendance/{user}', 'ReportsController@userAttendance')->name('report.attendance_user_detail');
        Route::get('/report/attendance-download', 'ReportsController@downloadExcel')->name('report.attendance_download');
    
        // update profile
        Route::get('/profile/edit/{user}', 'ProfileController@edit')->name('profile.edit');
        Route::patch('/profile/update/{user}', 'ProfileController@update')->name('profile.update');
    
        Route::get('/transaction/agency', 'TransactionController@agency')->name('transaction.agency');
        Route::post('/transaction/company', 'TransactionController@transactionCompany')->name('transaction.company');
        Route::get('/transaction/package', 'TransactionController@listPackage')->name('transaction.package');
        Route::get('/transaction/payment', 'TransactionController@payment')->name('transaction.payment');
        Route::get('/transaction/detail/{payment}', 'TransactionController@detail')->name('transaction.detail');
        Route::post('/transaction/payment/store', 'TransactionController@paymentStore')->name('transaction.store');

        Route::get('/propose', 'TransactionController@propose')->name('transaction.propose');
        Route::post('/propose', 'TransactionController@proposeStore')->name('transaction.propose_store');
        Route::get('/propose/detail', 'TransactionController@proposeDetail')->name('transaction.propose_detail');
        Route::post('/propose', 'HomeController@proposeApprove')->name('admin.propose_approve');
    
        Route::get('/company/create', 'ProfileController@company')->name('profile.company_create');
        Route::get('/company/edit', 'ProfileController@editCompany')->name('profile.company_edit');
        Route::post('/company/store', 'ProfileController@companyStore')->name('profile.company_store');

        Route::post('/memo/store', 'MemoController@store')->name('memo.store');
        Route::delete('/memo/delete/{memo}', 'MemoController@delete')->name('memo.delete');
    });
    
    
    // route supervisor
    Route::group(['middleware' => ['auth', 'roles:8']], function(){
        // Route::get('/supervisor', 'TasksController@supervisor')->name('home.supervisor');
        // Route::get('/supervisor/task-create', 'TasksController@supervisorCreateTask')->name('task.supervisor_create_task');
        // Route::get('/supervisor/task-edit/{task}', 'TasksController@supervisorEditTask')->name('task.supervisor_edit_task');
        // Route::post('/supervisor/task-store', 'TasksController@supervisorStoreTask')->name('task.supervisor_store_task');
        // Route::delete('/supervisor/task-delete/{task}', 'TasksController@supervisorDeleteTask')->name('task.supervisor_delete_task');
    
        // Route::post('/project/store', 'ProjectController@store')->name('project.store');
        // Route::patch('/project/update/{project}', 'ProjectController@update')->name('project.update');
    });
    
    // route supervisor, member
    Route::middleware(['auth', 'roles:8,6'])->group(function () {
        // dipindah
        // Route::post('todo/{task}', 'TodosController@store')->name('todo.store');
        // Route::post('todo-done/{todo}', 'TodosController@isDone')->name('todo.done');
        // Route::delete('todo-delete/{todo}', 'TodosController@delete')->name('todo.delete');
        // end dipindah
        Route::get('tes', function() {
            return view('supervisor.tes');
        });
    
        Route::get('home/task', 'TasksController@indexMember')->name('task.index_member');
    });
    
    Route::middleware(['auth', 'roles:9'])->group(function () {
        Route::get('/client', 'ClientUserController@index')->name('client.index');
        Route::get('/client/project-detail/{project}', 'ClientUserController@detailProject')->name('client.detail_project');
    });   
    
    // route supervisor, client
    Route::middleware(['auth', 'roles:8,9'])->group(function () {
        // Route::get('/task/download/{id}', 'TasksController@download')->name('task.download');
    });
    
    // route supervisor, member, admin, management
    Route::middleware(['auth', 'roles:8,6,1,2'])->group(function () {
        // Route::get('/supervisor', 'TasksController@supervisor')->name('home.supervisor');
        Route::get('/supervisor/task-create', 'TasksController@supervisorCreateTask')->name('task.supervisor_create_task');
        Route::get('/supervisor/task-edit/{task}', 'TasksController@supervisorEditTask')->name('task.supervisor_edit_task');
        Route::post('/supervisor/task-store', 'TasksController@store')->name('task.supervisor_store_task');
        Route::delete('/supervisor/task-delete/{task}', 'TasksController@supervisorDeleteTask')->name('task.supervisor_delete_task');
    
        Route::post('/project/store', 'ProjectController@store')->name('project.store');
        Route::patch('/project/update/{project}', 'ProjectController@update')->name('project.update');
        Route::delete('/project/delete/{project}', 'ProjectController@destroy')->name('project.delete');
    
        Route::post('todo/{task}', 'TodosController@store')->name('todo.store');
        Route::post('todo-done/{todo}', 'TodosController@isDone')->name('todo.done');
        Route::delete('todo-delete/{todo}', 'TodosController@delete')->name('todo.delete');

        Route::post('task-upload/{task}', 'TasksController@uploadReport')->name('task.upload');
        // user checks
        Route::post('/check/store', 'UserCheckController@store')->name('check.store');
    });
    
    // route supervisor, member, client, admin, management
    Route::middleware(['auth', 'roles:8,6,9,1,2'])->group(function () {
        Route::get('todo/detail/{todo}', 'TodosController@detail')->name('todo.detail');
        Route::get('task/{task}', 'TodosController@detailTask')->name('todo.detail_task');
        Route::post('task-reply/{task}', 'TasksController@reply')->name('task.reply');
        Route::post('todo-reply/{todo}', 'TodosController@reply')->name('todo.reply');

        Route::get('/project/show/{project}', 'ProjectController@show')->name('project.show');
        Route::get('/list-users/project/{project}', 'ProjectController@showUsers');
    
        // downlaod report task
        Route::get('/task/download/{id}', 'TasksController@download')->name('task.download');
    });
});