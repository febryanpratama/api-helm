<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('application/info', 'Api\Landing\CmsController@information');

// cron
Route::get('cron-expired/transaction', 'Api\Buyer\TransactionController@expiredTransaction');
Route::get('cron-finish/transaction', 'Api\Buyer\TransactionController@finishedTransactionAuto');
Route::get('cron-automatic/approval/bast', 'Api\TerminScheduleBastController@automaticApprove');
Route::post('vendor/imports/store-bulk', 'Api\ImportVendorController@importBulk');

// no auth checkout
Route::post('v2/checkout/store/no-login', 'Api\Buyer\CheckoutControllerDev@store');
Route::get('v2/checkout/check/single-checkout-no-login', 'Api\Buyer\CheckSingleCheckoutController@index');

Route::group(['prefix' => 'auth'], function(){
    Route::post('login', 'Api\AuthController@siginVerify');
    Route::post('otp-verify', 'Api\AuthController@validationOtp');
    Route::post('resend-otp', 'Api\AuthController@resendOtp');
    Route::post('password-verify', 'Api\AuthController@validationPassword');
});

Route::get('city', 'Api\AddressController@listCity');
Route::post('check-platinum', 'Api\Seller\CheckStatusVendorController@checkPlatinumExist');


// Instructor
Route::middleware(['auth:api', 'roles:1,10'])->group(function () {

    // PROJECT
    // Project
    Route::group(['prefix' => 'seller/projects'], function(){
        Route::post('store', 'Api\Seller\ProjectController@store');
        Route::post('update/{id}', 'Api\Seller\ProjectController@update');
        Route::post('question-store/{id}', 'Api\Seller\ProjectController@questionProject');
        Route::delete('delete/{id}', 'Api\Seller\ProjectController@delete');
        Route::delete('delete-media/{id}', 'Api\Seller\ProjectController@deleteMedia');
        Route::post('update-media/{id}', 'Api\Seller\ProjectController@updateMedia');
    });

    // Event
    Route::group(['prefix' => 'seller/events'], function(){
        Route::post('store', 'Api\Seller\EventController@store');
        Route::post('update/{id}', 'Api\Seller\EventController@update');
        Route::post('question-store/{id}', 'Api\Seller\EventController@questionEvent');
        Route::delete('delete/{id}', 'Api\Seller\EventController@delete');
        Route::delete('delete-media/{id}', 'Api\Seller\EventController@deleteMedia');
        Route::post('update-media/{id}', 'Api\Seller\EventController@updateMedia');
    });

    // Course Institute
    Route::group(['prefix' => 'profile'], function(){
        Route::get('course', 'Api\CourseInstituteController@show');
        Route::post('course/{id}', 'Api\CourseInstituteController@update');
        Route::get('login-activity', 'Api\AuthController@loginActivity');
    });

    // Course Package
    Route::group(['prefix' => 'vendor/course'], function(){
        Route::get('all', 'Api\Instructor\CourseController@index');
        Route::get('{courseId}', 'Api\Instructor\CourseController@show');
        Route::post('add', 'Api\Instructor\CourseController@store');
        Route::post('edit/{courseId}', 'Api\Instructor\CourseController@update');
        Route::delete('delete/{courseId}', 'Api\Instructor\CourseController@delete');
        Route::post('update/status/{courseId}', 'Api\Instructor\CourseController@publishUnpublish');
    });

    // Vendor Import
    Route::group(['prefix' => 'vendor/imports'], function(){
        Route::post('upload-template', 'Api\ImportVendorController@uploadImport');
        Route::get('download-template', 'Api\ImportVendorController@downloadImportTemplate');
        Route::post('store', 'Api\ImportVendorController@import');

        Route::get('bulk', 'Api\ImportVendorController@bulkFile');
        Route::get('bulk/error/{bulk_data}', 'Api\ImportVendorController@bulkFileError');
    });

    // Wholesale Price
    Route::group(['prefix' => 'wholesale-price'], function(){
        Route::get('/', 'Api\Instructor\WholesalePriceController@index');
        Route::post('store', 'Api\Instructor\WholesalePriceController@store');
        Route::get('show/{id}', 'Api\Instructor\WholesalePriceController@show');
        Route::post('update/{id}', 'Api\Instructor\WholesalePriceController@update');
        Route::delete('delete/{id}', 'Api\Instructor\WholesalePriceController@destroy');
    });

    // Armada
    Route::group(['prefix' => 'fleet'], function(){
        Route::get('/', 'Api\Instructor\FleetController@index');
        Route::post('store', 'Api\Instructor\FleetController@store');
        Route::get('show/{id}', 'Api\Instructor\FleetController@show');
        Route::post('update/{id}', 'Api\Instructor\FleetController@update');
        Route::delete('delete/{id}', 'Api\Instructor\FleetController@destroy');
    });

    // Session
    Route::group(['prefix' => 'course/session'], function () {
        Route::get('/', 'Api\Instructor\SessionController@index');
        Route::post('store', 'Api\Instructor\SessionController@store');
        Route::post('update/{id}', 'Api\Instructor\SessionController@update');
        Route::delete('delete/{id}', 'Api\Instructor\SessionController@destroy');
    });

    // Theory
    Route::group(['prefix' => 'course/theory'], function () {
        Route::get('/', 'Api\Instructor\TheoryController@index');
        Route::get('show/{id}', 'Api\Instructor\TheoryController@show');
        Route::post('store', 'Api\Instructor\TheoryController@store');
        Route::post('update/{id}', 'Api\Instructor\TheoryController@update');
        Route::delete('delete/{id}', 'Api\Instructor\TheoryController@destroy');
    });

    // Offline Cart
    Route::group(['prefix' => 'transaction/offline-cart'], function () {
        Route::get('/', 'Api\Instructor\OfflineCartController@index');
        Route::post('store', 'Api\Instructor\OfflineCartController@store');
        Route::delete('delete/{id}', 'Api\Instructor\OfflineCartController@destroy');
        Route::get('search-course', 'Api\Instructor\OfflineCartController@searchCourse');
    });

    // Offline Transaction
    Route::group(['prefix' => 'transaction/offline-transaction'], function () {
        Route::get('/', 'Api\Instructor\OfflineTransactionController@index');
        Route::post('store', 'Api\Instructor\OfflineTransactionController@store');
        Route::get('show/{id}', 'Api\Instructor\OfflineTransactionController@show');
        Route::post('update/{id}', 'Api\Instructor\OfflineTransactionController@update');
    });

    // Transaction V2
    Route::group(['prefix' => 'seller/v2/transaction'], function () {
        Route::get('/', 'Api\Seller\TransactionController@index');
        Route::get('show/{id}', 'Api\Seller\TransactionController@show');
        Route::get('show/termin/{id}', 'Api\Seller\TransactionController@showTermin');
        Route::post('update/{id}', 'Api\Seller\TransactionController@update');
        Route::post('reject/{id_transaction}', 'Api\Seller\TransactionController@rejectByProduct');

        Route::post('update-queue/{id}', 'Api\Seller\TransactionController@updateTransactionQueue');

        Route::group(['prefix' => 'work/report'], function () {
            Route::get('/', 'Api\WorkReportController@index');
            Route::post('store', 'Api\WorkReportController@store');
            Route::delete('delete/{id}', 'Api\WorkReportController@destroy');
        });

        Route::group(['prefix' => 'mou'], function () {
            Route::get('upload', 'Api\MOUDocumentController@index');
            Route::post('upload/store', 'Api\MOUDocumentController@store');
            Route::delete('delete/{id}', 'Api\MOUDocumentController@destroy');
        });
    });

    // Tracking fleet report location
    Route::group(['prefix' => 'tracking/{transaction}'], function () {
        Route::post('fleet-position/store', 'Api\TrackingController@positionReportFleet');
        Route::get('fleet-position/list', 'Api\TrackingController@listPositionReportFleet');
    });

    // waybill (resi/armada)
    Route::group(['prefix' => 'transaction/waybill/'], function () {
        Route::post('store/{transaction}', 'Api\Seller\TransactionController@waybillStore');
    });

    // report sampai tujuan (armada)
    Route::group(['prefix' => 'transaction/fleet/arrive-destination'], function () {
        Route::post('store/{transaction}', 'Api\Seller\TransactionController@arriveAtDestinationFleet');
    });

    // Reject Cancel transaction
    Route::group(['prefix' => 'transaction'], function () {
        Route::post('cancel-reject/{transaction}', 'Api\Seller\TransactionController@rejectCancelTransaction');
        Route::get('cancel-approve/{transaction}', 'Api\Seller\TransactionController@approveCancelTransaction');
    });

    // Cancel transaction
    Route::group(['prefix' => 'transaction'], function () {
        Route::post('seller-cancel/{transaction}', 'Api\Seller\TransactionController@cancelTransaction');
    });

    // Config Expedition
    Route::group(['prefix' => 'config'], function () {
        Route::group(['prefix' => 'expedition'], function () {
            Route::get('/', 'Api\Seller\ConfigExpeditionController@index');
            Route::post('update', 'Api\Seller\ConfigExpeditionController@update');
        });
    });

    // approve/reject complain
    Route::group(['prefix' => 'transaction'], function () {
        Route::get('complain-detail/{transaction}', 'Api\Seller\TransactionController@detailComplain');
        Route::post('complain-approve/{transaction}', 'Api\Seller\TransactionController@approveComplain');
        Route::post('complain-reject/{transaction}', 'Api\Seller\TransactionController@rejectComplain');
    });

    // Portfolio
    Route::group(['prefix' => 'portfolio'], function () {
        Route::post('store', 'Api\Instructor\PortfolioController@store');
        Route::post('update/{id}', 'Api\Instructor\PortfolioController@update');
        Route::delete('delete/{id}', 'Api\Instructor\PortfolioController@destroyV2');
    });

    Route::group(['prefix' => 'v2/portfolio'], function () {
        Route::post('store', 'Api\Instructor\PortfolioController@storeV2');
        Route::post('update/{id}', 'Api\Instructor\PortfolioController@updateV2');
        Route::delete('delete/{id}', 'Api\Instructor\PortfolioController@destroyV2');


        // Jadikan Product
        Route::get('make-product/{id}', 'Api\Instructor\PortfolioController@portofolioProduct');

        // Media
        Route::group(['prefix' => 'media/{portofolio_id}'], function () {
            Route::get('list', 'Api\Instructor\PortfolioController@mediaIndex');
            Route::post('store', 'Api\Instructor\PortfolioController@mediaStore');
            Route::post('update/{id}', 'Api\Instructor\PortfolioController@mediaUpdate');
            Route::get('show/{id}', 'Api\Instructor\PortfolioController@mediaShow');
            Route::delete('delete/{id}', 'Api\Instructor\PortfolioController@mediaDelete');
        });
    });

    // Competence
    Route::group(['prefix' => 'competence'], function () {
        Route::post('store', 'Api\Instructor\CompetenceController@store');
        Route::post('update/{id}', 'Api\Instructor\CompetenceController@update');
        Route::delete('delete/{id}', 'Api\Instructor\CompetenceController@destroy');
    });

    // Photo Kantor
    Route::group(['prefix' => 'office-photo'], function () {
        Route::post('store', 'Api\Seller\OfficePhotoController@store');
        Route::post('update/{id}', 'Api\Seller\OfficePhotoController@update');
        Route::delete('delete/{id}', 'Api\Seller\OfficePhotoController@destroy');
    });

    // Team Photo
    Route::group(['prefix' => 'team-photo'], function () {
        Route::post('store', 'Api\Seller\TeamPhotoController@store');
        Route::post('update/{id}', 'Api\Seller\TeamPhotoController@update');
        Route::delete('delete/{id}', 'Api\Seller\TeamPhotoController@destroy');
    });

    // Re Submission
    Route::group(['prefix' => 're-submission/store/status'], function () {
        Route::post('update', 'Api\ApproveStatusStoreController@reSubmission');
    });

    // Transaction termin schedule
    Route::group(['prefix' => 'transaction/termin-schedule'], function () {
        Route::post('edit/{id}', 'Api\TransactionTerminController@editTermin');
        Route::post('edit-schedule', 'Api\TransactionTerminController@editTerminSchedule');
    });

    // transaction edit date service
    Route::group(['prefix' => 'transaction'], function () {
        Route::post('edit-service/{transaction}', 'Api\Seller\TransactionController@editDateService');
    });

    // Autocomplete
    Route::group(['prefix' => 'config-autocomplete'], function () {
        Route::get('list/product/details', 'Api\Seller\ConfigAutocomplateController@index');
    });

    // Bidding Projects (OLD ARCHILOKA)
    Route::group(['prefix' => 'bidding-project'], function () {
        Route::get('/', 'Api\Seller\BiddingProjectController@index');
        Route::post('store', 'Api\Seller\BiddingProjectController@store');
        Route::get('detail/{id}', 'Api\Seller\BiddingProjectController@show');
        Route::post('cancel/{id}', 'Api\Seller\BiddingProjectController@destroy');
    });

    // Bidding Projects (ALUR BARU)
    Route::group(['prefix' => 'seller/bidding-project'], function () {
        Route::get('/', 'Api\Seller\BiddingProjectV2Controller@index');
        Route::get('show/{id}', 'Api\Seller\BiddingProjectV2Controller@show');
        Route::post('approve', 'Api\Seller\BiddingProjectV2Controller@store');
        Route::post('reject/{id}', 'Api\Seller\BiddingProjectV2Controller@update');
    });

    // Bidding Event (ALUR BARU)
    Route::group(['prefix' => 'seller/bidding-event'], function () {
        Route::get('/', 'Api\Seller\BiddingEventController@index');
        Route::get('show/{id}', 'Api\Seller\BiddingEventController@show');
        Route::post('approve', 'Api\Seller\BiddingEventController@store');
        Route::post('reject/{id}', 'Api\Seller\BiddingEventController@update');
    });


    // Story
    Route::group(['prefix' => 'stories'], function () {
        Route::get('/', 'Api\ApiStoriesController@myStory');
        Route::post('store', 'Api\ApiStoriesController@store');
        Route::get('detail/{story}', 'Api\ApiStoriesController@detail');
        Route::post('update/{story}', 'Api\ApiStoriesController@update');
        Route::delete('delete/{story}', 'Api\ApiStoriesController@delete');

        Route::get('make-popular', 'Api\Landing\StoryPopularController@makePopular');
        Route::get('remove-popular', 'Api\Landing\StoryPopularController@removePopular');

        // Media
        Route::get('{story}/medias', 'Api\ApiStoriesController@storyMedia');
        Route::post('{story}/medias/store', 'Api\ApiStoriesController@storyMediaStore');
        Route::delete('{story}/medias/delete/{media}', 'Api\ApiStoriesController@storyMediaDelete');
    });

    // Account
    Route::group(['prefix' => 'accounts'], function () {
        Route::name('accounts.')->group(function () {
            Route::get('group', 'Api\Seller\AccountController@listGroup')->name('group');
            Route::get('type', 'Api\Seller\AccountController@listType')->name('type');
            Route::get('/', 'Api\Seller\AccountController@index')->name('index');
            Route::get('detail/{account}', 'Api\Seller\AccountController@show')->name('show');
            Route::post('store', 'Api\Seller\AccountController@store')->name('store');
            Route::post('update/{account}', 'Api\Seller\AccountController@update')->name('update');
            Route::delete('delete/{account}', 'Api\Seller\AccountController@destroy')->name('destroy');
        });
    });

    // Journals
    Route::group(['prefix' => 'journals'], function () {
        Route::name('journals.')->group(function () {
            Route::get('/', 'Api\Seller\JournalController@index')->name('index');
            Route::get('detail/{journal}', 'Api\Seller\JournalController@show')->name('show');
            Route::post('store', 'Api\Seller\JournalController@store')->name('store');
            Route::post('update/{journal}', 'Api\Seller\JournalController@update')->name('update');
            Route::delete('delete/{journal}', 'Api\Seller\JournalController@destroy')->name('destroy');
        });
    });

    // BEGIN BALANCE
    Route::group(['prefix' => 'begin-balances'], function () {
        Route::name('begin-balances.')->group(function () {
            Route::get('/', 'Api\Seller\BeginBalanceController@index')->name('index');
            Route::get('detail/{begin_balance}', 'Api\Seller\BeginBalanceController@show')->name('show');
            Route::post('store', 'Api\Seller\BeginBalanceController@store')->name('store');
            Route::post('update/{begin_balance}', 'Api\Seller\BeginBalanceController@update')->name('update');
            Route::delete('delete/{begin_balance}', 'Api\Seller\BeginBalanceController@destroy')->name('destroy');
        });
    });

    // Inventory
    Route::group(['prefix' => 'inventory'], function () {
        Route::group(['prefix' => 'purchases'], function () {
            Route::get('/', 'Api\Seller\Inventory\PurchasesController@index');
            Route::post('store', 'Api\Seller\Inventory\PurchasesController@store');
            Route::get('show/{id}', 'Api\Seller\Inventory\PurchasesController@show');
            Route::post('update/{id}', 'Api\Seller\Inventory\PurchasesController@update');

            Route::group(['prefix' => 'payment-history'], function () {
                Route::get('/', 'Api\Seller\Inventory\PaymentHistoryController@index');
                Route::post('store', 'Api\Seller\Inventory\PaymentHistoryController@store');
                Route::get('show/{id}', 'Api\Seller\Inventory\PaymentHistoryController@show');
                Route::post('update/{id}', 'Api\Seller\Inventory\PaymentHistoryController@update');
                Route::delete('delete/{id}', 'Api\Seller\Inventory\PaymentHistoryController@destroy');
            });
        });

        // Route::group(['prefix' => 'indirect-purchases'], function () {
        //     Route::get('/', 'Api\Seller\Inventory\IndirectPurchasesController@index');
        //     Route::post('store', 'Api\Seller\Inventory\IndirectPurchasesController@store');
        //     Route::get('show/{id}', 'Api\Seller\Inventory\IndirectPurchasesController@show');
        //     Route::post('update/{id}', 'Api\Seller\Inventory\IndirectPurchasesController@update');
        //     Route::delete('delete/{id}', 'Api\Seller\Inventory\PurchasesController@destroy');
        // });
    });
});

// User
Route::middleware(['auth:api', 'roles:6'])->group(function () {
    // Course
    Route::group(['prefix' => 'student/course'], function(){
        Route::get('/', 'Api\Student\CourseController@index');
        Route::get('show/{slug}', 'Api\Student\CourseController@show');
        Route::get('unlock/theory/{id}','Api\Student\CourseController@unlockTheory');
        // Route::get('show/theory/file/{id}','Api\Student\CourseController@showFile');
    });

    // Cart
    Route::group(['prefix' => 'student/cart'], function(){
        Route::get('/', 'Api\Student\CartController@index');
        Route::get('/v2', 'Api\Student\CartController@indexV2');
        Route::get('termin/{id}', 'Api\Student\CartController@courseTerminSchedule');
        Route::post('store', 'Api\Student\CartController@store');
        Route::delete('delete/{cartId}', 'Api\Student\CartController@destroy');
        Route::post('category-detail-inputs/store/{id}', 'Api\Student\CartController@categoryDetailInputsStore');
    });

    // Armada
    Route::group(['prefix' => 'student/fleet'], function(){
        Route::post('check-shipping', 'Api\Instructor\FleetController@checkShipping');
    });

    // Checkout
    Route::group(['prefix' => 'student/checkout'], function(){
        Route::post('store', 'Api\Student\CheckoutController@store');
        Route::post('store/multiple', 'Api\Student\CheckoutController@storeMultiple');
    });

    // Rating
    Route::group(['prefix' => 'student/course/rating'], function(){
        Route::post('store', 'Api\Student\RatingController@store');
        Route::get('show/{id}', 'Api\Student\RatingController@show');
        Route::post('update/{id}', 'Api\Student\RatingController@update');
        Route::delete('delete/{id}', 'Api\Student\RatingController@destroy');
    });

    // Version 2.0
    Route::group(['prefix' => 'v2'], function(){
        // Checkout
        Route::group(['prefix' => 'checkout'], function(){
            Route::post('store', 'Api\Buyer\CheckoutController@store');
            Route::post('store/multiple', 'Api\Buyer\CheckoutController@storeMultiple');
            Route::post('store/dev', 'Api\Buyer\CheckoutControllerDev@store');

            // Check Question - Details Transaction
            Route::get('check-question', 'Api\Buyer\CheckQuestionDetailsTransactionController@index');
            Route::get('check-question/additional-items', 'Api\Buyer\CheckQuestionDetailsTransactionController@show');

            // Check Single Checkout
            Route::get('check/single-checkout', 'Api\Buyer\CheckSingleCheckoutController@index');
        });

        // Transaction
        Route::group(['prefix' => 'transaction'], function(){
            Route::get('/', 'Api\Buyer\TransactionController@index');
            Route::get('v2', 'Api\Buyer\TransactionController@indexV2');
            Route::get('payment-step/{id}', 'Api\Buyer\TransactionController@paymentStep');
            Route::get('show/{id}', 'Api\Buyer\TransactionController@show');
            Route::get('show/by-store/{id}', 'Api\Buyer\TransactionController@showByTransaction');
            Route::get('show/top-up/{id}', 'Api\Buyer\TransactionController@showTopUp');
            Route::get('show/termin/{id}', 'Api\Buyer\TransactionController@showTermin');

            Route::group(['prefix' => 'work/report'], function () {
                Route::get('/', 'Api\WorkReportController@index');
            });

            Route::group(['prefix' => 'mou'], function () {
                Route::get('upload', 'Api\MOUDocumentController@index');
                Route::post('upload/update', 'Api\MOUDocumentController@update');
                Route::delete('delete/{id}', 'Api\MOUDocumentController@destroyFromBuyer');
            });
        });
    });

    // Cancel Transaction
    Route::group(['prefix' => 'transaction'], function () {
        Route::post('cancel/all/{id}', 'Api\Buyer\TransactionController@cancelTransactionAll');
        Route::post('cancel/{transaction}', 'Api\Buyer\TransactionController@cancelTransaction');
    });

    // Finished Transaction
    Route::group(['prefix' => 'transaction'], function () {
        Route::get('finish/{transaction}', 'Api\Buyer\TransactionController@finishTransaction');
    });

    // Complain Transaction
    Route::group(['prefix' => 'transaction'], function () {
        Route::post('complain/{transaction}', 'Api\Buyer\TransactionController@complain');
    });

    // Shop Testimonials
    Route::group(['prefix' => 'shop/testimonials'], function () {
        // Route::get('/', 'Api\Buyer\ShopTestimonialsController@index');
        Route::post('store', 'Api\Buyer\ShopTestimonialsController@store');
        Route::get('show/{id}', 'Api\Buyer\ShopTestimonialsController@show');
        Route::post('update/{id}', 'Api\Buyer\ShopTestimonialsController@update');
        Route::delete('delete/{id}', 'Api\Buyer\ShopTestimonialsController@destroy');
    });

    // Project
    Route::group(['prefix' => 'projects'], function(){
        Route::post('store', 'Api\Buyer\ProjectController@store');
        Route::post('update/{id}', 'Api\Buyer\ProjectController@update');
        Route::post('question-store/{id}', 'Api\Buyer\ProjectController@questionProject');
        Route::delete('delete/{id}', 'Api\Buyer\ProjectController@delete');
        Route::delete('delete-media/{id}', 'Api\Buyer\ProjectController@deleteMedia');
        Route::post('update-media/{id}', 'Api\Buyer\ProjectController@updateMedia');
    });

    // Bidding Projects (OLD Versi Archiloka)
    Route::group(['prefix' => 'buyer/bidding-project'], function () {
        Route::get('/', 'Api\Buyer\BiddingProjectController@index');
        Route::get('show/{id}', 'Api\Buyer\BiddingProjectController@show');
        Route::post('approve', 'Api\Buyer\BiddingProjectController@store');
        Route::post('reject/{id}', 'Api\Buyer\BiddingProjectController@update');
    });

    // Bidding Projects (ALUR BARU)
    Route::group(['prefix' => 'bidding-project-v2'], function () {
        Route::get('/', 'Api\Buyer\BiddingProjectV2Controller@index');
        Route::post('store', 'Api\Buyer\BiddingProjectV2Controller@store');
        Route::get('detail/{id}', 'Api\Buyer\BiddingProjectV2Controller@show');
        Route::post('cancel/{id}', 'Api\Buyer\BiddingProjectV2Controller@destroy');
    });

    // Bidding Projects (ALUR BARU)
    Route::group(['prefix' => 'bidding-event'], function () {
        Route::get('/', 'Api\Buyer\BiddingEventController@index');
        Route::post('store', 'Api\Buyer\BiddingEventController@store');
        Route::get('detail/{id}', 'Api\Buyer\BiddingEventController@show');
        Route::post('cancel/{id}', 'Api\Buyer\BiddingEventController@destroy');
    });
});

// Insturctor and User
Route::middleware(['auth:api', 'roles:1,6'])->group(function () {
    // Profile
    Route::group(['prefix' => 'profile'], function(){
        Route::get('owned-category', 'Api\ProfileController@ownedCategory');
        Route::get('me', 'Api\ProfileController@show');
        Route::post('me/{user}', 'Api\ProfileController@update');
        Route::delete('delete/cv', 'Api\ProfileController@destroyCV');
    });

    // Address
    Route::group(['prefix' => 'address'], function(){
        Route::get('/', 'Api\AddressController@index');
        Route::get('search', 'Api\AddressController@search');
        Route::post('store', 'Api\AddressController@store');
        Route::get('show/{id}', 'Api\AddressController@show');
        Route::post('update/{id}', 'Api\AddressController@update');
        Route::delete('delete/{id}', 'Api\AddressController@destroy');
    });

    // Check Shipping
    Route::group(['prefix' => 'check-shipping'], function () {
        Route::post('/', 'Api\CheckShippingController@index');
    });

    // Expedition
    Route::group(['prefix' => 'expedition'], function () {
        Route::get('/', 'Api\ExpeditionControler@index');
    });

    // Transaction
    Route::group(['prefix' => 'transaction'], function () {
        Route::get('/', 'Api\TransactionController@index');
        Route::post('approve/{id}', 'Api\TransactionController@approve');
        Route::post('rejected/{id}', 'Api\TransactionController@rejected');
    });

    // Wallet
    Route::group(['prefix' => 'wallet'], function () {
        Route::get('/', 'Api\WalletController@index');
        Route::get('history', 'Api\WalletController@history');
        Route::post('withdraw', 'Api\WalletController@withdraw');
        
        Route::group(['prefix' => 'topup'], function () {
            Route::get('/', 'Api\TopUpController@index');
            Route::post('store', 'Api\TopUpController@store');
            Route::get('show/{id}', 'Api\TopUpController@show');
        });
    });

    // Chats
    Route::group(['prefix' => 'chat'], function () {
        Route::get('group', 'Api\ChatsController@listGroup');
        Route::get('group/{id}', 'Api\ChatsController@listGroupByCourse');
        Route::post('store/{id}', 'Api\ChatsController@store');
        Route::post('read-all-message/{id}', 'Api\ChatsController@readAllMessage');
    });

    // Termin Schedule
    Route::group(['prefix' => 'termin-schedule'], function () {
        Route::get('/{id}', 'Api\Instructor\TerminScheduleController@index');
        Route::get('show/{id}', 'Api\Instructor\TerminScheduleController@show');
        Route::post('store', 'Api\Instructor\TerminScheduleController@store');
    });

    // Notification
    Route::group(['prefix' => 'notification'], function () {
        Route::get('/', 'Api\NotificationController@index');
        Route::post('read/{id}', 'Api\NotificationController@read');
        Route::post('read-all', 'Api\NotificationController@readAll');
    });

    // Agreement Letter
    Route::group(['prefix' => 'agreement-letter'], function () {
        Route::post('store', 'Api\AgreementLetterController@store');
        Route::delete('delete/{id}', 'Api\AgreementLetterController@destroy');
    });

    // Reports Content
    Route::group(['prefix' => 'reports-content'], function () {
        Route::get('/', 'Api\ReportsContentController@index');
        Route::post('store', 'Api\ReportsContentController@store');
        Route::get('show/{id}', 'Api\ReportsContentController@show');
    });

    // Reports List
    Route::group(['prefix' => 'reports-list'], function () {
        Route::get('/', 'Api\ReportsListController@index');
        Route::post('store/{id}', 'Api\ReportsListController@store');
        Route::get('show/{id}', 'Api\ReportsListController@show');
    });

    // Quotation
    Route::group(['prefix' => 'quotation'], function () {
        Route::group(['prefix' => 'buyer'], function () {
            Route::get('list-request', 'Api\CourseQuotationController@index');
            Route::post('send-request', 'Api\CourseQuotationController@store');
        });

        Route::group(['prefix' => 'seller'], function () {
            Route::get('request-quotation/{id}', 'Api\CourseQuotationController@show');
            // Route::post('send-request', 'Api\CourseQuotationController@store');
        });
    });

    // Tracking
    Route::group(['prefix' => 'tracking/{transaction}'], function () {
        Route::get('/', 'Api\TrackingController@tracking');
    });

    // Approve Store Status
    Route::group(['prefix' => 'approve/store/status'], function () {
        Route::get('/', 'Api\ApproveStatusStoreController@index');
        Route::post('update/{id}', 'Api\ApproveStatusStoreController@update');
    });

    // Shop Testimonials - Admin
    Route::group(['prefix' => 'admin/shop/testimonials'], function () {
        Route::get('/', 'Api\Seller\ShopTestimonialsController@index');
        Route::get('show/{id}', 'Api\Seller\ShopTestimonialsController@show');
        Route::post('update/{id}', 'Api\Seller\ShopTestimonialsController@update');
    });

    // Upload Global File
    Route::group(['prefix' => 'upload/global/file'], function () {
        Route::post('store', 'Api\UploadGlobalFileController@store');
        Route::delete('destroy', 'Api\UploadGlobalFileController@destroy');
        Route::get('show', 'Api\UploadGlobalFileController@show');
    });
    
    // Transaction termin schedule
    Route::group(['prefix' => 'transaction/termin-schedule'], function () {
        Route::get('{id}', 'Api\TransactionTerminController@listTransactionTerminSchedule');
        Route::get('show/{id}', 'Api\TransactionTerminController@show');
        Route::post('pay-installments', 'Api\TransactionTerminController@payInstallment');
        Route::post('edit/{id}', 'Api\TransactionTerminController@editTermin');

        Route::group(['prefix' => 'upload-bast'], function () {
            Route::get('show/{id}', 'Api\TerminScheduleBastController@show');
            Route::get('show/v2/{id}', 'Api\TerminScheduleBastController@show');
            Route::post('store', 'Api\TerminScheduleBastController@store');
            Route::post('store/file/{id}', 'Api\TerminScheduleBastController@storeFile');
            Route::post('update/file/{id}', 'Api\TerminScheduleBastController@update');
            Route::delete('delete/file/{id}', 'Api\TerminScheduleBastController@destroyFile');
            Route::delete('delete/{id}', 'Api\TerminScheduleBastController@destroy');
            Route::post('update/status/{id}', 'Api\TerminScheduleBastController@updateStatus');
        });
    });

    // Project (OLD ARCHILOKA)
    // Route::group(['prefix' => 'projects'], function(){
    //     Route::get('/', 'Api\Buyer\ProjectController@index');
    //     Route::get('detail/{id}', 'Api\Buyer\ProjectController@show');
    // });

    // Project (ALUR BARU)
    Route::group(['prefix' => 'projects'], function(){
        Route::get('/', 'Api\Seller\ProjectController@index');
        Route::get('detail/{id}', 'Api\Seller\ProjectController@show');
    });
    Route::group(['prefix' => 'seller/projects'], function(){
        Route::get('/', 'Api\Seller\ProjectController@index');
        Route::get('detail/{id}', 'Api\Seller\ProjectController@show');
    });

    // Event (ALUR BARU)
    Route::group(['prefix' => 'events'], function(){
        Route::get('/', 'Api\Seller\EventController@index');
        Route::get('detail/{id}', 'Api\Seller\EventController@show');
    });
    Route::group(['prefix' => 'seller/events'], function(){
        Route::get('/', 'Api\Seller\EventController@index');
        Route::get('detail/{id}', 'Api\Seller\EventController@show');
    });


    // Transaction Immovable Object
    Route::group(['prefix' => 'transaction-update'], function(){
        Route::post('transaction/{id}', 'Api\Buyer\TransactionController@updateStatusImmovableObject');
    });
});

// Chats
Route::middleware(['auth:api'])->group(function () {
    Route::group(['prefix' => 'chat'], function () {
        // tanya penjual
        Route::get('group/create-store/{company}', 'Api\ChatsController@createGroupCompanyCustomer')->middleware('roles:6');

        // tanya pembeli
        Route::get('group/create-customer/{user}', 'Api\ChatsController@createGroupCustomerCompany')->middleware('roles:1');
    });
});

// Category
Route::group(['prefix' => 'category'], function(){
    Route::get('all', 'Api\CategoryController@index')->name('index');
});

// Unit
Route::group(['prefix' => 'unit'], function(){
    Route::get('all', 'Api\UnitController@index')->name('index');
});

// Portfolio
Route::group(['prefix' => 'portfolio'], function () {
    Route::get('/{id}', 'Api\Instructor\PortfolioController@index');
    Route::get('show/{id}', 'Api\Instructor\PortfolioController@show');
});

Route::group(['prefix' => 'v2/portfolio'], function () {
    Route::get('/{id}', 'Api\Instructor\PortfolioController@indexV2');
    Route::get('show/{id}', 'Api\Instructor\PortfolioController@showV2');
});

// Competence
Route::group(['prefix' => 'competence'], function () {
    Route::get('/{id}', 'Api\Instructor\CompetenceController@index');
    Route::get('show/{id}', 'Api\Instructor\CompetenceController@show');
});

// Office Photo
Route::group(['prefix' => 'office-photo'], function () {
    Route::get('/{id}', 'Api\Seller\OfficePhotoController@index');
    Route::get('show/{id}', 'Api\Seller\OfficePhotoController@show');
});

// Team Photo
Route::group(['prefix' => 'team-photo'], function () {
    Route::get('/{id}', 'Api\Seller\TeamPhotoController@index');
    Route::get('show/{id}', 'Api\Seller\TeamPhotoController@show');
});

// Shop Testimonials
Route::group(['prefix' => 'shop/testimonials'], function () {
    Route::get('/', 'Api\Buyer\ShopTestimonialsController@index');
    Route::get('show/{id}', 'Api\Buyer\ShopTestimonialsController@show');
});

// Open Api
Route::group(['prefix' => 'open-api'], function(){
    // Date Time From Server
    Route::get('date-from-server', 'Api\ServerConfigController@date');
    
    // List Data
    Route::group(['prefix' => 'list-data'], function(){
        Route::get('student/course/{id}', 'Api\Open\CourseController@listStudent');
        Route::get('course-package-category', 'Api\Open\CourseController@coursePackageCategory');

        // Marketplace Version
        Route::group(['prefix' => 'count'], function(){
            Route::get('total-buyers', 'Api\Open\CountController@countBuyers');
            Route::get('total-seller', 'Api\Open\CountController@countSeller');
            Route::get('total-project', 'Api\Open\CountController@countProject');
            Route::get('total-project-done', 'Api\Open\CountController@countProjectDone');
            Route::get('total-project-on-going', 'Api\Open\CountController@countProjectOnGoing');
            Route::get('total-company', 'Api\Open\CountController@countCompany');
            Route::get('total-portfolio', 'Api\Open\CountController@countPortfolio');
            Route::get('total-done-transaction', 'Api\Open\CountController@countDoneTransaction');
            Route::get('all-data', 'Api\Open\CountController@index');
        });
    });

    // Course
    Route::group(['prefix' => 'course'], function(){
        Route::get('/', 'Api\Open\CourseController@index');
        Route::get('category/{id}', 'Api\Open\CourseController@courseCategory');
        Route::get('instructor/{id}', 'Api\Open\CourseController@courseInstructor');
        Route::get('show/{slug}', 'Api\Student\CourseController@show');
        Route::get('list-student/{id}', 'Api\Open\CourseController@listStudentCount');
        Route::get('avg-rating/{id}', 'Api\Open\CourseController@avgRating');
        Route::get('list-rating/{id}', 'Api\Student\RatingController@index');
    });

    // Instructor
    Route::group(['prefix' => 'instructor'], function(){
        Route::get('/', 'Api\Open\InstructorController@index');
        Route::get('show/{id}', 'Api\Open\InstructorController@show');
    });

    // Institution
    Route::group(['prefix' => 'institution'], function(){
        Route::get('/', 'Api\Open\InstitutionController@index');
        Route::get('show/{slug}', 'Api\Open\InstitutionController@show');
    });

    // Main Page
    Route::group(['prefix' => 'main-page'], function(){
        Route::get('carousel', 'Api\Open\MainPageController@carousel');
        Route::get('video-highlights', 'Api\Open\MainPageController@videohighlights');
    });

    // Project
    Route::group(['prefix' => 'projects'], function(){
        Route::get('/', 'Api\Open\ProjectController@index');
        Route::get('show/{id}', 'Api\Open\ProjectController@show');
    });

    // Menu For Apps
    Route::group(['prefix' => 'menu-for-apps'], function() {
        Route::get('mengapa-memilih-kami', 'Api\Open\MenuForAppsController@index');
    });

    // Menu For Tags
    Route::group(['prefix' => 'tags'], function() {
        Route::get('/', 'Api\Open\TagsController@index');
    });

    // Story
    Route::group(['prefix' => 'stories'], function () {
        Route::get('/', 'Api\Open\StoriesController@index');
        Route::get('show/{story}', 'Api\Open\StoriesController@show');
    });

    // checkship
    Route::group(['prefix' => 'student/fleet'], function(){
        Route::post('check-shipping', 'Api\Instructor\FleetController@checkShipping');
    });

    // Address
    Route::group(['prefix' => 'address'], function(){
        Route::get('search', 'Api\AddressController@search');
    });

    // Expedition
    Route::group(['prefix' => 'expedition'], function () {
        Route::get('/', 'Api\ExpeditionControler@index');
    });

    // Check Shipping
    Route::group(['prefix' => 'check-shipping'], function () {
        Route::post('/', 'Api\CheckShippingController@index');
    });
});

// Auth With Param Token
Route::get('student/course/show/theory/file/{id}','Api\Student\CourseController@showFile');

// Moota
Route::group(['prefix' => 'v2/moota'], function(){
    Route::name('moota.')->group(function () {
        Route::post('/transaction/verification', 'MootaController@index')->name('index');
    });
});

// SQL Execute
Route::group(['prefix' => 'mysql/exect'], function(){
    Route::get('/', 'Api\MysqlController@index')->name('index');
});


// CMS
Route::group(['prefix' => 'cms'], function(){
    Route::group(['prefix' => 'auth'], function(){
        Route::post('login', 'Api\AuthController@validationAuthCms');
        Route::get('logout', 'Api\AuthController@logoutCms')->middleware('auth:api');
    });

    // Setting WEB
    Route::group(['prefix' => 'setting'], function(){
        Route::get('/', 'Api\Landing\CmsController@setting');
        Route::post('store', 'Api\Landing\CmsController@settingStore')->middleware(['auth:api', 'roles:10']);
    });

    // NAVBAR WEB
    Route::group(['prefix' => 'navbar'], function(){
        Route::get('/', 'Api\Landing\CmsController@navbar');
        Route::post('store', 'Api\Landing\CmsController@navbarStore')->middleware(['auth:api', 'roles:10']);
        Route::get('detail/{navbar}', 'Api\Landing\CmsController@navbarDetail');
        Route::delete('delete/{navbar}', 'Api\Landing\CmsController@navbarDelete')->middleware(['auth:api', 'roles:10']);
    });

    // Carousel WEB
    Route::group(['prefix' => 'carousel'], function(){
        Route::get('/', 'Api\Landing\CmsController@carousel');
        Route::post('store', 'Api\Landing\CmsController@carouselStore')->middleware(['auth:api', 'roles:10']);
        Route::get('detail/{carousel}', 'Api\Landing\CmsController@carouselDetail');
        Route::delete('delete/{carousel}', 'Api\Landing\CmsController@carouselDelete')->middleware(['auth:api', 'roles:10']);

        Route::get('popup/event/{carousel}', 'Api\Landing\CmsController@carouselPopUpEvent');
        Route::get('popup/product/{carousel}', 'Api\Landing\CmsController@carouselPopUpProduct');

        Route::group(['prefix' => 'event/{carousel}'], function(){
            Route::get('list', 'Api\Landing\CmsController@carouselEvent');
            Route::post('store', 'Api\Landing\CmsController@carouselEventStore')->middleware(['auth:api', 'roles:10']);
            Route::get('detail/{carousel_event}', 'Api\Landing\CmsController@carouselEventDetail');
            Route::delete('delete/{carousel_event}', 'Api\Landing\CmsController@carouselEventDelete')->middleware(['auth:api', 'roles:10']);
        });
    });

    // Promo WEB
    Route::group(['prefix' => 'promo'], function(){
        Route::get('/', 'Api\PromoController@promo');
        Route::get('my-list', 'Api\PromoController@myPromo')->middleware(['auth:api', 'roles:10,1']);
        Route::post('store', 'Api\PromoController@promoStore')->middleware(['auth:api', 'roles:10,1']);
        Route::get('detail/{promo}', 'Api\PromoController@promoDetail');
        Route::delete('delete/{promo}', 'Api\PromoController@promoDelete')->middleware(['auth:api', 'roles:10,1']);
    });

    // category WEB
    Route::group(['prefix' => 'category-popular'], function(){
        Route::get('/', 'Api\Landing\CmsController@category');
        Route::post('store', 'Api\Landing\CmsController@categoryStore')->middleware(['auth:api', 'roles:10']);
        Route::get('detail/{category}', 'Api\Landing\CmsController@categoryDetail');
        Route::delete('delete/{category}', 'Api\Landing\CmsController@categoryDelete')->middleware(['auth:api', 'roles:10']);

        Route::get('popup', 'Api\Landing\CmsController@categoryPopUpProduct');

    });

    // product WEB
    Route::group(['prefix' => 'product-popular'], function(){
        Route::get('/', 'Api\Landing\CmsController@product');
        Route::post('store', 'Api\Landing\CmsController@productStore')->middleware(['auth:api', 'roles:10']);
        Route::delete('delete/{product}', 'Api\Landing\CmsController@productDelete')->middleware(['auth:api', 'roles:10']);
    });

    // article populer WEB
    Route::group(['prefix' => 'article-popular'], function(){
        Route::get('/', 'Api\Landing\CmsController@article');
        Route::post('store', 'Api\Landing\CmsController@articleStore')->middleware(['auth:api', 'roles:10']);
        Route::get('detail/{article}', 'Api\Landing\CmsController@articleDetail');
        Route::delete('delete/{article}', 'Api\Landing\CmsController@articleDelete')->middleware(['auth:api', 'roles:10']);
    });

    // ARTICLE CRUD
    Route::group(['prefix' => 'article'], function(){
        Route::get('/', 'Api\ArticleController@index');
        Route::get('detail/{article}', 'Api\ArticleController@show');
        Route::post('store', 'Api\ArticleController@store')->middleware(['auth:api']);
        Route::post('update/{article}', 'Api\ArticleController@update')->middleware(['auth:api']);
        Route::delete('delete/{article}', 'Api\ArticleController@destroy')->middleware(['auth:api']);

        Route::group(['prefix' => 'comment/{article}'], function(){
            Route::get('list', 'Api\ArticleCommentController@index');
            Route::get('detail/{comment}', 'Api\ArticleCommentController@show');
            Route::post('store', 'Api\ArticleCommentController@store')->middleware(['auth:api']);
            Route::post('update/{comment}', 'Api\ArticleCommentController@update')->middleware(['auth:api']);
            Route::delete('delete/{comment}', 'Api\ArticleCommentController@destroy')->middleware(['auth:api']);
        });
    });

    // Discuss CRUD
    Route::group(['prefix' => 'discuss'], function(){
        Route::get('/', 'Api\DiscussController@index');
        Route::get('detail/{discuss}', 'Api\DiscussController@show');
        Route::post('store', 'Api\DiscussController@store')->middleware(['auth:api']);
        Route::post('update/{discuss}', 'Api\DiscussController@update')->middleware(['auth:api']);
        Route::delete('delete/{discuss}', 'Api\DiscussController@destroy')->middleware(['auth:api']);

        Route::group(['prefix' => 'comment/{discuss}'], function(){
            Route::get('list', 'Api\DiscussCommentController@index');
            Route::get('detail/{comment}', 'Api\DiscussCommentController@show');
            Route::post('store', 'Api\DiscussCommentController@store')->middleware(['auth:api']);
            Route::post('update/{comment}', 'Api\DiscussCommentController@update')->middleware(['auth:api']);
            Route::delete('delete/{comment}', 'Api\DiscussCommentController@destroy')->middleware(['auth:api']);
        });
    });

    // youtube WEB
    Route::group(['prefix' => 'youtube'], function(){
        Route::get('/', 'Api\Landing\CmsController@youtube');
        Route::post('store', 'Api\Landing\CmsController@youtubeStore')->middleware(['auth:api', 'roles:10']);
        Route::delete('delete/{youtube}', 'Api\Landing\CmsController@youtubeDelete')->middleware(['auth:api', 'roles:10']);
    });

    // CRUD CATEGORY
    Route::group(['prefix' => 'category'], function(){
        Route::get('make-popular', 'Api\Landing\CmsController@makeCategoryPopular');
        Route::get('remove-popular', 'Api\Landing\CmsController@removeCategoryPopular');
        Route::get('/', 'Api\Landing\CmsController@categoryList');
        Route::post('store', 'Api\CategoryController@store')->middleware(['auth:api', 'roles:10']);
        Route::post('update/{category}', 'Api\CategoryController@update')->middleware(['auth:api', 'roles:10']);
        Route::get('detail/{category}', 'Api\CategoryController@detail');
        Route::delete('delete/{category}', 'Api\CategoryController@delete')->middleware(['auth:api', 'roles:10']);

    });

    // CRUD TEMPLATE
    Route::group(['prefix' => 'template'], function(){
        Route::get('/', 'Api\TemplateController@index');
        Route::get('detail/{template}', 'Api\TemplateController@show');
        Route::post('store', 'Api\TemplateController@store')->middleware(['auth:api', 'roles:10']);
        Route::post('update/{template}', 'Api\TemplateController@update')->middleware(['auth:api', 'roles:10']);
        Route::delete('delete/{template}', 'Api\TemplateController@destroy')->middleware(['auth:api', 'roles:10']);
    });


    // V2 Navigation //
    Route::prefix('/v2')->group(function(){

        // Dynamic Navigation
        Route::group([
            'prefix' => '/navigation',
        ], function(){
            Route::get('/', 'Api\Landing\V2\CmsController@indexNavigation');
            Route::post('/', 'Api\Landing\V2\CmsController@storeNavigation');
            Route::get('/{navigation}', 'Api\Landing\V2\CmsController@showNavigation');
            Route::put('/{navigation}', 'Api\Landing\V2\CmsController@updateNavigation');
            Route::delete('/{navigation}', 'Api\Landing\V2\CmsController@destroyNavigation');
        });

        Route::group([
            'prefix' => '/generate-ai'
        ], function(){
            Route::post('/', 'Api\Landing\V2\CmsController@OpenAi');
        });
        Route::group([
            'prefix' => '/generate-ai-image'
        ], function(){
            Route::post('/', 'Api\Landing\V2\CmsController@OpenAiImage');
        });

        Route::group([
            'prefix' => '/content',
        ], function(){
            // Route::post('/', 'Api\Landing\V2\CmsController@getContent');
            Route::get('/{navigation_id}', 'Api\Landing\V2\CmsController@detailNavigation');
            Route::post('/store/{navigation_id}', 'Api\Landing\V2\CmsController@storeContent');
        });
    });

    // Route::get('category', 'Api\Landing\CmsController@categoryList');
    Route::get('product', 'Api\Landing\CmsController@productList');
    Route::get('product/make-popular', 'Api\Landing\CmsController@makeProductPopular');

    Route::get('stores', 'Api\Landing\CmsController@companyList');
});


// CRUD AUCTION
Route::group(['prefix' => 'auction'], function(){
    Route::get('/', 'Api\AuctionController@index')->middleware(['auth:api']);
    Route::get('detail/{auction}', 'Api\AuctionController@show')->middleware(['auth:api']);;
    Route::post('store', 'Api\AuctionController@store')->middleware(['auth:api', 'roles:1']);
    Route::post('update/{auction}', 'Api\AuctionController@update')->middleware(['auth:api', 'roles:1']);
    Route::delete('delete/{auction}', 'Api\AuctionController@destroy')->middleware(['auth:api', 'roles:1']);

    Route::group(['prefix' => 'bid'], function(){
        Route::post('store', 'Api\AuctionBidController@store')->middleware(['auth:api', 'roles:6']);
        Route::get('list', 'Api\AuctionBidController@index')->middleware(['auth:api']);
    });

});

// ---- Belum Berguna
// Partner
// Route::group(['prefix' => 'partner'], function () {
//     Route::get('/', 'Api\Instructor\PartnerController@index');
//     Route::get('show/{id}', 'Api\Instructor\PartnerController@show');
//     Route::post('store', 'Api\Instructor\PartnerController@store');
//     Route::post('update/{id}', 'Api\Instructor\PartnerController@update');
//     Route::delete('delete/{partnerId}/{courseId}', 'Api\Instructor\PartnerController@destroy');
// });

// User By Partner
// Route::group(['prefix' => 'course/user/partner'], function () {
//     Route::get('/', 'Api\Instructor\CourseUserPartnerController@index');
//     // Route::get('show/{id}', 'Api\Instructor\CourseUserPartnerController@show');
//     Route::post('store', 'Api\Instructor\CourseUserPartnerController@store');
//     Route::post('update/{id}', 'Api\Instructor\CourseUserPartnerController@update');
//     Route::delete('delete/{partnerId}/{courseId}', 'Api\Instructor\CourseUserPartnerController@destroy');
// });

// Meeting Room
// Route::group(['prefix' => 'meeting-room'], function () {
//     Route::get('/', 'Api\MeetingRoomController@index');
//     Route::post('store', 'Api\MeetingRoomController@store');
//     Route::get('show/{id}', 'Api\MeetingRoomController@show');
//     Route::post('update/{id}', 'Api\MeetingRoomController@update');
//     Route::delete('delete/{id}', 'Api\MeetingRoomController@destroy');

//     // Check In
//     Route::group(['prefix' => 'check-in'], function () {
//         Route::post('store', 'Api\Student\CheckInMeetController@store');
//         Route::get('show/{id}', 'Api\Student\CheckInMeetController@show');
//     });
// });

// Task
// Route::group(['prefix' => 'course/task'], function () {
//     Route::get('/', 'Api\Instructor\TaskController@index');
//     Route::get('show/{id}', 'Api\Instructor\TaskController@show');
//     Route::post('store', 'Api\Instructor\TaskController@store');
//     Route::post('update/{id}', 'Api\Instructor\TaskController@update');
//     Route::delete('delete/{id}', 'Api\Instructor\TaskController@destroy');

//     // Report
//     Route::group(['prefix' => 'report/student'], function () {
//         Route::get('/', 'Api\Instructor\TaskReportStudentController@index');
//         Route::post('mentors-assessment/store', 'Api\Instructor\TaskReportStudentController@store');
//         Route::post('mentors-assessment/show/{taskmentorassessment}', 'Api\Instructor\TaskReportStudentController@show');
//         Route::post('mentors-assessment/update/{taskmentorassessment}', 'Api\Instructor\TaskReportStudentController@update');
//         Route::delete('mentors-assessment/delete/{taskmentorassessment}', 'Api\Instructor\TaskReportStudentController@destroy');
//     });

//     // Report Task from Student
//     Route::group(['prefix' => 'report'], function () {
//         Route::post('store', 'Api\Student\ReportTaskController@store');
//     });

//     // Rating Resulst
//     Route::group(['prefix' => 'mentors-assessment/results'], function () {
//         Route::get('/', 'Api\Student\TaskMentorAssessmentResultsController@index');
//     });
// });