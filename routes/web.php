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

// Initialize

use App\Company;

Auth::routes();

// BULK
Route::get('/bulk/store', 'BulkController@bulkStore');

// Lang
Route::get('lang/{lang}', 'LangController@switchLang')->name('lang.switch');

Route::get('up-com', function () {

    // get company
    $com = Company::whereNull('status')->whereNull('city_id')->get();
    foreach ($com as $key => $value) {
        $value->update([
            'status' => 1,
            'city_id' => 444,
        ]);
    }
});

Route::get('corn/expired-auction', 'Api\AuctionBidController@expiredAuctionTransaction');

// Sites
Route::get('/', 'LandingPageController@index')->name('landing');
Route::get('produk', 'LandingPageController@product')->name('product.index');
Route::get('layanan', 'LandingPageController@service')->name('service.index');
Route::get('bantuan', 'LandingPageController@help')->name('help.index');
Route::get('institution/{any}', 'SearchInstitutionController@index')->name('search.institution.index');
Route::get('paket-kursus/list', 'SearchCoursePackageController@index')->name('search.course.package.index');
Route::get('cari-mentor', 'SearchInstructorController@index')->name('search.instructor.index');
Route::get('faq/{categoryFaq}', 'LandingPageController@faq')->name('faq');

// Lang
Route::get('lang/{lang}', 'LangController@switchLang')->name('lang.switch');

// Auth Before Login
Route::get('/auth', 'AuthController@index')->name('auth.index');

// Auth For Admin Application
Route::get('authorization/admin-panel', 'AdminPanel\AuthorizationController@index');
Route::post('authorization/admin-panel', 'AdminPanel\AuthorizationController@signin')->name('authorization.signin');

// Auth For Management or Pic
// Route::get('management/auth/signin', 'Management\AuthorizationController@index');
// Route::post('management/auth/signin', 'Management\AuthorizationController@signin')->name('management.authorization.signin');

// Admin
Route::group(['middleware' => ['auth', 'roles:10']], function(){
    Route::group(['prefix' => 'admin-panel'], function(){
        Route::name('admin-panel.')->group(function () {
            Route::get('dashboard', 'AdminPanel\AdminPanelController@dashboard')->name('dashboard');
        });

        // Transaction
        Route::group(['prefix' => 'transaction'], function(){
            Route::name('admin.transaction.')->group(function () {
                Route::get('/', 'AdminPanel\TransactionController@index')->name('index');
                Route::post('update/transaction/{checkout}', 'AdminPanel\TransactionController@update')->name('update');
            });
        });

        // Withdraw
        Route::group(['prefix' => 'withdraw'], function(){
            Route::name('admin.withdraw.')->group(function () {
                Route::post('update', 'AdminPanel\WithdrawController@update')->name('update');
            });
        });

        // Users
        Route::group(['prefix' => 'users'], function(){
            Route::name('admin.users.')->group(function () {
                Route::get('/', 'AdminPanel\UsersController@index')->name('index');
                Route::get('sellers', 'AdminPanel\UsersController@sellers')->name('sellers');
                Route::post('sellers/delete/{id}', 'AdminPanel\UsersController@sellersDelete');
            });
        });

        // Instructor
        Route::group(['prefix' => 'mentor'], function(){
            Route::name('admin.instructor.')->group(function () {
                Route::get('/', 'AdminPanel\InstructorController@index')->name('index');
            });
        });

        // Config Autocomplete
        Route::group(['prefix' => 'config-autocomplete'], function(){
            Route::name('admin.autocomplete.')->group(function () {
                Route::get('/', 'AdminPanel\ConfigAutocomplateController@index')->name('config.autocomplete');
                Route::get('autocompletes', 'AdminPanel\ConfigAutocomplateController@listAutocomplete')->name('config.autocomplete.list');
                Route::post('store', 'AdminPanel\ConfigAutocomplateController@store');
            });
        });

        // Config Autocomplete
        Route::group(['prefix' => 'config-autocomplete-transaction'], function(){
            Route::name('list.transaction.')->group(function () {
                Route::get('/', 'AdminPanel\ConfigAutocomplateController@indexTransaction')->name('autocomplete');
                Route::get('autocompletes', 'AdminPanel\ConfigAutocomplateController@listAutocompleteTransaction');
                Route::post('store', 'AdminPanel\ConfigAutocomplateController@storeTransaction');
            });
        });

        // Take Down Data
        Route::group(['prefix' => 'take-down-data'], function(){
            Route::name('admin.take.down.data.')->group(function () {
                Route::get('/', 'AdminPanel\TakeDownDataController@coursePackage')->name('course.package');
                Route::post('{course}/true', 'AdminPanel\TakeDownDataController@updateCourse');
                Route::get('users', 'AdminPanel\TakeDownDataController@users')->name('users');
                Route::post('users/{user}', 'AdminPanel\TakeDownDataController@updateUser');
                Route::get('institution', 'AdminPanel\TakeDownDataController@institution')->name('institution');
                Route::post('institution/{company}', 'AdminPanel\TakeDownDataController@updateInstitution');
                Route::get('video', 'AdminPanel\TakeDownDataController@video')->name('video');
                Route::post('video/{subject}', 'AdminPanel\TakeDownDataController@updateVideo');
            });
        });
    });
});

// Management
Route::group(['middleware' => ['auth', 'roles:2']], function(){
    Route::group(['prefix' => 'management'], function() {
        Route::name('management.')->group(function () {
            Route::get('dashboard', 'Management\DashboardController@index')->name('dashboard');

            // Course Package
            Route::group(['prefix' => 'paket-kursus'], function () {
                Route::name('course.package.')->group(function () {
                    Route::get('/', 'Management\CoursePackageController@index')->name('index');
                });
            });

            // Users
            Route::group(['prefix' => 'users'], function () {
                Route::name('users.')->group(function () {
                    Route::get('{course}/{partner}', 'Management\UsersController@index')->name('index');
                });
            });

            // Transaction
            Route::group(['prefix' => 'daftar-transaksi'], function () {
                Route::name('transaction.')->group(function () {
                    Route::get('/', 'Management\CourseTransactionController@index')->name('index');
                    Route::get('show/{course}/{partner}', 'Management\CourseTransactionController@show')->name('show');
                });
            });

            // Checkout
            Route::group(['prefix' => 'checkout'], function () {
                Route::name('checkout.')->group(function () {
                    Route::get('{course}', 'Management\CheckoutController@index')->name('index');
                    Route::post('store', 'Management\CheckoutController@store')->name('store');
                    Route::get('show/{coursetransactionpartner}', 'Management\CheckoutController@show')->name('show');
                });
            });
        });
    });
});

// Auth After Login
Route::group(['prefix' => 'auth'], function(){
    Route::name('auth.')->group(function () {
        Route::get('signin', 'AuthController@signin')->name('signin');
        Route::post('resend/otp', 'AuthController@resendOtp');
        Route::post('signin/verify', 'AuthController@siginVerify')->name('signin.post');
        Route::post('auth/verification', 'AuthController@validationOtp')->name('otp_verify');
    });
});

Route::middleware(['auth'])->group(function () {
    Route::get('/home', 'HomeController@index')->name('home');
    Route::get('logout-process', 'AuthController@logout')->name('auth.logout_process');
});

// Instructor
Route::group(['middleware' => ['auth', 'roles:1,2', 'tourwidget']], function(){
    // Transaction
    Route::group(['prefix' => 'transaction'], function(){
        Route::name('transaction.')->group(function () {
            Route::get('agency', 'TransactionController@agency')->name('agency');
            Route::post('company', 'TransactionController@transactionCompany')->name('company');
            Route::get('package', 'TransactionController@listPackage')->name('package');
            Route::get('payment', 'TransactionController@payment')->name('payment');
            Route::get('detail/{payment}', 'TransactionController@detail')->name('detail');
            Route::post('payment/store', 'TransactionController@paymentStore')->name('store');

            Route::get('register', 'TransactionController@registerTransaction')->name('register');
            Route::post('register/store', 'TransactionController@registerStore')->name('register_store');
        });
    });

    Route::group([
        'prefix'     => '/{company_name}',
        'middleware' => \App\Http\Middleware\CheckCompany::class,
        'as'         => 'company:',
    ], function () {
        Route::middleware(['auth'])->group(function () {
            Route::get('/notification', 'NotificationController@index')->name('notif.index');
        
            Route::get('logout', 'AuthController@logoutIndex')->name('auth.logout');
            Route::post('logout-process', 'AuthController@logout')->name('auth.logout_process');
        });

        Route::get('/company/edit', 'ProfileController@editCompany')->name('profile.company_edit');
    });

    // Company
    Route::group(['prefix' => 'company'], function(){
        Route::name('company.')->group(function () {
            Route::post('update/{company}', 'CompanyController@update')->name('update');
        });
    });

    // Category
    Route::group(['prefix' => 'category'], function(){
        Route::name('category.')->group(function () {
            Route::get('/', 'CategoryController@index')->name('index');
        });
    });

    // Package Course Page
    Route::group(['prefix' => 'paket-kursus'], function(){
        Route::name('package.')->group(function () {
            Route::get('/', 'CourseController@indexPage')->name('course.index');
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
            Route::get('preview/{slug}/overview/{subject}', 'CourseController@preview');
            Route::get('count/data', 'CourseController@countData')->name('count.data');
            Route::post('new-group-chat/{course}', 'CourseController@createNewGroupChat');
        });
    });

    // Majors
    Route::group(['prefix' => 'majors'], function(){
        Route::name('majors.')->group(function () {
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
            Route::post('/store', 'SubjectController@store')->name('store');
            Route::get('/edit/{subject}', 'SubjectController@edit')->name('edit');
            Route::post('/update/{subject}', 'SubjectController@update')->name('update');
            Route::delete('/delete/{subject}', 'SubjectController@destroy')->name('destroy');
            Route::delete('/detach/{division}', 'SubjectController@destroySubjectRelation')->name('destroy.relation');
        });
    });

    // Task
    Route::group(['prefix' => 'task'], function(){
        Route::name('task.')->group(function () {
            Route::get('create', 'TasksController@create')->name('create');
            Route::post('store', 'TasksController@store')->name('store');
            Route::get('edit/{task}', 'TasksController@edit')->name('edit');
            Route::post('update/{task}', 'TasksController@update')->name('update');
            Route::delete('delete/{task}', 'TasksController@destroy')->name('destroy');

            // Adding Score
            Route::get('give-score/{taskattachment}', 'TasksController@giveScore')->name('giveScore');
            Route::post('give-score/store', 'TasksController@giveScoreStore')->name('giveScoreStore');
            Route::delete('give-score/delete/{taskmentorassessment}', 'TasksController@giveScoreDestroy')->name('giveScoreDestroy');
        });
    });

    // Partner
    Route::group(['prefix' => 'partner'], function(){
        Route::name('partner.')->group(function () {
            Route::get('/', 'PartnerController@index')->name('index');
            Route::post('store', 'PartnerController@store')->name('store');
            Route::get('list/users/{partner}/{course}', 'PartnerController@show')->name('show');
            Route::get('edit/{partner}', 'PartnerController@edit')->name('edit');
            Route::post('update/{partner}', 'PartnerController@update')->name('update');
            Route::delete('delete/{partner}/{course}', 'PartnerController@destroy')->name('destroy');
            Route::post('send-invoice/{partner}/{course}', 'PartnerController@invoice')->name('invoice');
        });
    });

    // Course User Partner
    Route::group(['prefix' => 'course-user-partner'], function(){
        Route::name('course.user.partner.')->group(function () {
            Route::post('store', 'CourseUserPartnerController@store')->name('store');
            Route::get('edit/{user}', 'CourseUserPartnerController@edit')->name('edit');
            Route::post('update/{user}', 'CourseUserPartnerController@update')->name('update');
            Route::delete('destroy/{partner}/{user}', 'CourseUserPartnerController@destroy')->name('destroy');
        });
    });

    // Cart
    Route::group(['prefix' => 'offline-cart'], function(){
        Route::name('offline.cart.')->group(function () {
            Route::get('/', 'OfflineCartController@index')->name('index');
            Route::post('store', 'OfflineCartController@store')->name('store');
            Route::delete('delete/{cart}', 'OfflineCartController@destroy')->name('destroy');
        });
    });

    // Offline Transaction
    Route::group(['prefix' => 'offline-transaction'], function(){
        Route::name('offline.transaction.')->group(function () {
            Route::post('store', 'OfflineTransactionController@store')->name('store');
            Route::get('show/{checkout}', 'OfflineTransactionController@show')->name('show');
            Route::post('update/{checkout}', 'OfflineTransactionController@update')->name('update');
        });
    });
});

// Upload Img Tinymce
Route::post('/tinymce/upload-image', 'TasksController@uploadImage')->name('tinymce.upload.image');

// Member
Route::middleware(['auth', 'roles:6,2'])->group(function () {
    // Profile
    Route::get('/profile', 'ProfileController@index')->name('profile.index');
    Route::post('/profile/upload/file', 'ProfileController@uploadFile')->name('profile.upload.file');
    Route::patch('/profile/update/{user}', 'ProfileController@update')->name('profile.update');

    // Course
    Route::group(['prefix' => 'student/course'], function(){
        Route::name('member.course.')->group(function () { 
            Route::get('/', 'Member\CourseController@index')->name('index');
            Route::get('list', 'Member\CourseController@showAll')->name('list');
            Route::get('show/member/{slug}', 'Member\CourseController@show')->name('show');
            Route::get('learn/{slug}/overview/{subject}', 'Member\CourseController@learn');
        });
    });

    // Checkout
    Route::group(['prefix' => 'checkout'], function(){
        Route::name('member.checkout.')->group(function () { 
            Route::get('/', 'Member\CheckoutController@index')->name('index');
            Route::post('store', 'Member\CheckoutController@store')->name('store');
            Route::get('detail-pembayaran/{checkout}', 'Member\CheckoutController@show')->name('show');
            Route::get('buy-now/{slug}', 'Member\CheckoutController@buyNow')->name('buy.now');
        });
    });

    // Chat
    Route::get('chat/area', 'ChatsController@index')->name('chat.area.index');
    
    // Reward
    Route::group(['prefix' => 'reward'], function(){
        Route::name('reward.')->group(function () {
            Route::post('store', 'RewardController@store')->name('store');
            Route::post('store-user-reward', 'RewardController@storeUserReward')->name('store_user_reward');
        });
    });

    // Rating
    Route::group(['prefix' => 'rating'], function(){
        Route::name('rating.')->group(function () {
            Route::post('store', 'RatingController@store')->name('store');
            Route::post('update/{rating}', 'RatingController@update')->name('update');
            Route::post('delete/{rating}', 'RatingController@destroy')->name('destroy');
        });
    });

    // Cart
    Route::group(['prefix' => 'cart'], function(){
        Route::name('cart.')->group(function () {
            Route::get('/', 'CartController@index')->name('index');
            Route::post('store', 'CartController@store')->name('store');
            Route::delete('delete/{cart}', 'CartController@destroy')->name('destroy');
        });
    });
});

// Instructor, Member, Admin Panel Role and Management
Route::middleware(['auth', 'roles:1,6,10,2'])->group(function () {
    // Dashboard
    Route::group(['prefix' => 'dashboard'], function(){
        Route::name('dashboard.')->group(function () {
            Route::get('/', 'DashboardController@index')->name('index');
        });
    });

    // Majors
    Route::group(['prefix' => 'majors'], function(){
        Route::name('majors.')->group(function () {
            Route::get('/', 'MajorsController@index')->name('index');
        });
    });
    
    // Subject
    Route::group(['prefix' => 'subject'], function(){
        Route::name('subject.')->group(function () {
            Route::get('/', 'SubjectController@index')->name('index');
        });
    });

    // Task
    Route::group(['prefix' => 'task'], function(){
        Route::name('task.')->group(function () {
            Route::get('index', 'TasksController@index')->name('index');
            Route::get('show/{task}', 'TasksController@show')->name('show');
            Route::post('upload/report', 'TasksController@uploadReport')->name('upload.report');
        });
    });

    // Rating
    Route::group(['prefix' => 'rating'], function(){
        Route::name('rating.')->group(function () {
            Route::get('/', 'RatingController@index')->name('index');
        });
    });

    // E-wallet
    Route::group(['prefix' => 'e-wallet'], function(){
        Route::name('e.wallet.')->group(function () {
            Route::get('/', 'WalletController@index')->name('index');
            Route::post('store', 'WalletController@store')->name('store');
        });
    });

    // Course Transaction
    Route::group(['prefix' => 'daftar-transaksi'], function(){
        Route::name('course.transaction.')->group(function () {
            Route::get('/', 'CourseTransactionController@index')->name('index');
            Route::get('create', 'CourseTransactionController@create')->name('create');
            Route::get('search-course-package', 'CourseTransactionController@search')->name('search-course-package');
        });
    });

    // Profile Account
    Route::group(['prefix' => 'profil'], function(){
        Route::name('profile.')->group(function () {
            Route::get('/', 'Member\ProfileAccountController@index')->name('index');
            Route::post('update/{user}', 'Member\ProfileAccountController@update')->name('update');
            Route::delete('delete/cv', 'Member\ProfileAccountController@destroy')->name('destroy');
        });
    });

    // Meet
    Route::group(['prefix' => 'meet'], function(){
        Route::name('meet.')->group(function () {
            Route::get('/', 'MeetController@index')->name('index');
            Route::get('{any}', 'MeetController@index');
        });
    });

    // Check In Meet
    Route::group(['prefix' => 'check-in/meet'], function(){
        Route::name('checkin.meet.')->group(function () {
            Route::post('store', 'CheckInMeetController@store');
        });
    });

    // Hint Widget
    Route::group(['prefix' => 'hint-widget'], function(){
        Route::name('hint.widget.')->group(function () {
            Route::post('/', 'HintWidgetController@store')->name('store');
        });
    });

    // Chat
    Route::group(['prefix' => 'chat'], function(){
        Route::name('chats.')->group(function () {
            Route::get('/','ChatsController@index')->name('index');
            Route::get('list','ChatsController@listGroup');
            Route::post('store/{conversation}','ChatsController@store');
            Route::get('list/member/{conversation}','ChatsController@listMember');
            Route::post('read-all-message/conversation/{conversation}','ChatsController@readAllMessage');
        });
    });

    // Meeting Online Or Offline
    Route::group(['prefix' => 'meeting-room'], function() {
        Route::name('meeting.room.')->group(function () {
            Route::get('/', 'MeetingRoomController@index')->name('index');
            Route::get('create/{majors}', 'MeetingRoomController@create')->name('create');
            Route::post('store', 'MeetingRoomController@store')->name('store');
            Route::get('show/{meetingroom}', 'MeetingRoomController@show')->name('show');
            Route::get('edit/{meetingroom}', 'MeetingRoomController@edit')->name('edit');
            Route::post('update/{meetingroom}', 'MeetingRoomController@update')->name('update');
            Route::delete('delete/{meetingroom}', 'MeetingRoomController@destroy')->name('destroy');
        });
    });
});

// Not Logged In - End Point
Route::group(['prefix' => 'not-logged-in/endpoint'], function() {
    Route::name('not.logged.in.')->group(function () {
        Route::get('courses/list/with/instructor', 'SearchInstitutionController@courses')->name('courses.with.instructor');
        Route::get('courses/list', 'SearchCoursePackageController@courses')->name('courses');
        Route::get('search/instructor/list', 'SearchInstructorController@instructor')->name('instructor');
        Route::get('majors/index', 'SearchCoursePackageController@majors')->name('majors.index');
        Route::get('rating/index', 'SearchCoursePackageController@rating')->name('rating.index');
        Route::get('subject/index', 'SearchCoursePackageController@subject')->name('subject.index');
        Route::post('checkout/course-package', 'SearchCoursePackageController@checkout')->name('checkout.store');
    });
});

Route::get('course/detail/student/{slug}', 'SearchCoursePackageController@redirect')->name('global.redirect.course.package');
Route::get('detail/paket-kursus/{slug}', 'SearchCoursePackageController@show')->name('global.detail.course.package');
Route::get('beli/paket-kursus', 'SearchCoursePackageController@buy')->name('global.buy.course.package');
Route::get('verify/otp', 'AuthController@verifyOtp');

// Embed To APPS
Route::get('course/learn/embed-to-apps/{id}', 'EmbedToAppsController@index');