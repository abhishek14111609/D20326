<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Controllers\Admin\GiftController;

// Redirect root URL to admin login
Route::get('/', function () {
    return redirect()->route('admin.login');
});

// Redirect /login to /admin/login
Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

// Laravel Auth Routes - Disable default auth routes since we're using custom ones
// Auth::routes();

// Admin Authentication Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Public routes (no auth required)

    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login'); // admin.login
    Route::post('/login', [AuthController::class, 'login'])->name('login');       // admin.login
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/setup', [AuthController::class, 'createDefaultAdmin'])->name('setup');

    // Protected routes (admin auth required)
    Route::middleware(['auth:admin', 'admin'])->group(function () {
        // Search API
        Route::get('/api/search', [SearchController::class, 'search'])->name('api.search');

        // Dashboard
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // Revenue Data API
        Route::get('/dashboard/revenue-data', [AdminController::class, 'getRevenueData'])->name('dashboard.revenue-data');

        // Users Routes
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [AdminController::class, 'users'])->name('index');
            Route::get('/create', [AdminController::class, 'createUser'])->name('create');
            Route::post('/', [AdminController::class, 'storeUser'])->name('store');
            Route::get('/{user}', [AdminController::class, 'showUser'])->name('show');
            Route::get('/{user}/edit', [AdminController::class, 'editUser'])->name('edit');
            Route::put('/{user}', [AdminController::class, 'updateUser'])->name('update');
            Route::delete('/{user}', [AdminController::class, 'deleteUser'])->name('destroy');

            // Payment History
            Route::prefix('{user}/payments')->name('payments.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\UserPaymentController::class, 'index'])->name('index');
                Route::get('/create', [\App\Http\Controllers\Admin\UserPaymentController::class, 'create'])->name('create');
                Route::post('/', [\App\Http\Controllers\Admin\UserPaymentController::class, 'store'])->name('store');
                Route::get('/{payment}', [\App\Http\Controllers\Admin\UserPaymentController::class, 'show'])->name('show');
                Route::get('/{payment}/edit', [\App\Http\Controllers\Admin\UserPaymentController::class, 'edit'])->name('edit');
                Route::put('/{payment}', [\App\Http\Controllers\Admin\UserPaymentController::class, 'update'])->name('update');
                Route::delete('/{payment}', [\App\Http\Controllers\Admin\UserPaymentController::class, 'destroy'])->name('destroy');
                Route::put('/{payment}/status', [\App\Http\Controllers\Admin\UserPaymentController::class, 'updateStatus'])->name('status.update');
            });
        });

        // Swipes Routes
        Route::prefix('swipes')->name('swipes.')->group(function () {
            Route::get('/', [AdminController::class, 'swipes'])->name('index');
            Route::get('/{swipe}/edit', [AdminController::class, 'editSwipe'])->name('edit');
            Route::get('/{swipe}', [AdminController::class, 'showSwipe'])->name('show');
            Route::delete('/{swipe}', [AdminController::class, 'deleteSwipe'])->name('destroy');
            Route::patch('/{swipe}/match', [AdminController::class, 'matchSwipe'])->name('match');
            Route::patch('/{swipe}/unmatch', [AdminController::class, 'unmatchSwipe'])->name('unmatch');
        });

        // Chats Routes
        Route::get('/chats', [AdminController::class, 'chats'])->name('chats.index');

        // Membership Plans Routes
        Route::resource('membership-plans', 'App\Http\Controllers\Admin\MembershipController')
            ->names('memberships')
            ->parameters(['membership-plans' => 'membership']);

        // User Membership Purchases
        Route::get('memberships/user-purchases', [App\Http\Controllers\Admin\MembershipController::class, 'userPurchases'])
            ->name('memberships.user-purchases');

        // User Memberships Routes
        Route::resource('user-memberships', 'App\Http\Controllers\Admin\UserMembershipController')
            ->names('user-memberships');

        // Payments Routes
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\PaymentController::class, 'index'])->name('index');
            Route::get('/export', [\App\Http\Controllers\Admin\PaymentController::class, 'export'])->name('export');
            Route::get('/create', [\App\Http\Controllers\Admin\PaymentController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\PaymentController::class, 'store'])->name('store');
            Route::get('/{payment}', [\App\Http\Controllers\Admin\PaymentController::class, 'show'])->name('show');
            Route::get('/{payment}/edit', [\App\Http\Controllers\Admin\PaymentController::class, 'edit'])->name('edit');
            Route::put('/{payment}', [\App\Http\Controllers\Admin\PaymentController::class, 'update'])->name('update');
            Route::delete('/{payment}', [\App\Http\Controllers\Admin\PaymentController::class, 'destroy'])->name('destroy');
        });

        // Challenges Routes
        Route::prefix('challenges')->name('challenges.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\ChallengeController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\ChallengeController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\ChallengeController::class, 'store'])->name('store');
            Route::get('/{challenge}', [\App\Http\Controllers\Admin\ChallengeController::class, 'show'])->name('show');
            Route::get('/{challenge}/edit', [\App\Http\Controllers\Admin\ChallengeController::class, 'edit'])->name('edit');
            Route::put('/{challenge}', [\App\Http\Controllers\Admin\ChallengeController::class, 'update'])->name('update');
            Route::delete('/{challenge}', [\App\Http\Controllers\Admin\ChallengeController::class, 'destroy'])->name('destroy');
            Route::patch('/{challenge}/cancel', [\App\Http\Controllers\Admin\ChallengeController::class, 'cancel'])->name('cancel');
        });

        // Competitions Routes
        Route::prefix('competitions')->name('competitions.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\CompetitionController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\CompetitionController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\CompetitionController::class, 'store'])->name('store');
            Route::get('/{competition}', [\App\Http\Controllers\Admin\CompetitionController::class, 'show'])->name('show');
            Route::get('/{competition}/edit', [\App\Http\Controllers\Admin\CompetitionController::class, 'edit'])->name('edit');
            Route::put('/{competition}', [\App\Http\Controllers\Admin\CompetitionController::class, 'update'])->name('update');
            Route::delete('/{competition}', [\App\Http\Controllers\Admin\CompetitionController::class, 'destroy'])->name('destroy');


            Route::prefix('{competition}/quizzes')->name('quizzes.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\QuizController::class, 'index'])->name('index');
                Route::get('/create', [\App\Http\Controllers\Admin\QuizController::class, 'create'])->name('create');
                Route::post('/', [\App\Http\Controllers\Admin\QuizController::class, 'store'])->name('store');
                Route::get('/{quiz}', [\App\Http\Controllers\Admin\QuizController::class, 'show'])->name('show');
                Route::get('/{quiz}/edit', [\App\Http\Controllers\Admin\QuizController::class, 'edit'])->name('edit');
                Route::put('/{quiz}', [\App\Http\Controllers\Admin\QuizController::class, 'update'])->name('update');
                Route::delete('/{quiz}', [\App\Http\Controllers\Admin\QuizController::class, 'destroy'])->name('destroy');
                Route::post('/{quiz}/toggle-status', [\App\Http\Controllers\Admin\QuizController::class, 'toggleStatus'])->name('toggle-status');
                Route::get('/{quiz}/statistics', [\App\Http\Controllers\Admin\QuizController::class, 'statistics'])->name('statistics');
                // Route::get('statistics/export', [QuizStatisticsController::class, 'export'])->name('statistics.export');

                // Route::get('participants', [QuizParticipantController::class, 'index'])->name('participants');
                // Questions Management
                Route::prefix('{quiz}/questions')->name('questions.')->group(function () {
                    Route::get('/', [\App\Http\Controllers\Admin\QuestionController::class, 'index'])->name('index');
                    Route::get('/create', [\App\Http\Controllers\Admin\QuestionController::class, 'create'])->name('create');
                    Route::post('/', [\App\Http\Controllers\Admin\QuestionController::class, 'store'])->name('store');
                    Route::get('/{question}/edit', [\App\Http\Controllers\Admin\QuestionController::class, 'edit'])->name('edit');
                    Route::put('/{question}', [\App\Http\Controllers\Admin\QuestionController::class, 'update'])->name('update');
                    Route::delete('/{question}', [\App\Http\Controllers\Admin\QuestionController::class, 'destroy'])->name('destroy');
                    Route::post('/reorder', [\App\Http\Controllers\Admin\QuestionController::class, 'reorder'])->name('reorder');
                });
            });
        });

        // Legacy membership route (kept for backward compatibility)
        Route::get('/memberships', [AdminController::class, 'memberships'])->name('memberships.legacy');

        // Account Settings Routes
        Route::prefix('account')->name('account.')->group(function () {
            Route::get('/settings', [AdminController::class, 'accountSettings'])->name('settings');
            Route::put('/settings/update', [AdminController::class, 'updateAccountSettings'])->name('settings.update');
            Route::put('/password/update', [AdminController::class, 'updatePassword'])->name('password.update');
        });

        // Gifts Routes
        Route::prefix('gifts')->name('gifts.')->group(function () {
            Route::get('/', [GiftController::class, 'index'])->name('index');
            Route::get('/create', [GiftController::class, 'create'])->name('create');
            Route::post('/', [GiftController::class, 'store'])->name('store');
            Route::get('/{gift}', [GiftController::class, 'show'])->name('show');
            Route::get('/{gift}/edit', [GiftController::class, 'edit'])->name('edit');
            Route::post('/{gift}', [GiftController::class, 'update'])->name('update');
            Route::delete('/{gift}', [GiftController::class, 'destroy'])->name('destroy');
        });

        // Leaderboard Route
        Route::get('/leaderboard', [AdminController::class, 'leaderboard'])->name('leaderboard.index');

        // Reports Routes
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/users', [AdminController::class, 'userReports'])->name('users');
            Route::get('/system', [AdminController::class, 'systemReports'])->name('system');
        });

        // Settings Routes
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [AdminController::class, 'settings'])->name('index');
            Route::put('/', [AdminController::class, 'updateSettings'])->name('update');
        });

        Route::get('/profile', [AdminController::class, 'profile'])->name('profile');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});

// WebSocket Test Route
Route::get('/test-websocket', function () {
    return view('websocket-test');
});

Route::get('/chat-demo', function () {
    if (!auth()->check()) {
        return redirect('/login');
    }
    return view('chat-demo');
})->middleware('auth');

// Test broadcast endpoint (for sending test messages)
Route::post('/test-broadcast', function () {
    $message = request()->input('message', 'Test message');
    $userId = request()->input('user_id', 1);

    event(new \App\Events\MessageSent(
        (object)['id' => 1, 'message' => $message, 'user_id' => auth()->id() ?? 1],
        $userId
    ));

    return response()->json(['status' => 'Message sent']);
})->middleware('auth:sanctum');
