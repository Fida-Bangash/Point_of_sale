<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web routes
|--------------------------------------------------------------------------
| Har page ka URL = /file-name aur route name = file-name (pehle jaisa).
|
| Kisi page ko doosre role ko dena ho to uska naam neeche ki ek list se
| kaat kar doosri list mein daal do. Naya page = list mein naam add karo.
|
| NOTE: 'role' middleware bootstrap/app.php mein alias ho chuka hona chahiye
| aur 3 controllers (Login, Register, User) bane hone chahiye, warna
| `php artisan route:list` error dega.
*/

// Helper: naamon ki list se Route::view banata hai (URL = /naam, name = naam)
$pages = function (array $names): void {
    foreach ($names as $name) {
        Route::view("/{$name}", "pages.{$name}")->name($name);
    }
};

/*
|--------------------------------------------------------------------------
| 1) Bina login wale (guest) pages: sign in, register
|--------------------------------------------------------------------------
| Login ho chuka user inhe kholega to '/' par bhej diya jayega.
| /register sirf tab khulta hai jab koi user mojood na ho (controller check karta hai).
*/
Route::middleware('guest')->group(function () {
    Route::get('/signin', [LoginController::class, 'show'])->name('signin');
    Route::post('/signin', [LoginController::class, 'store'])->middleware('throttle:5,1');

    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:10,1');
});

/*
|--------------------------------------------------------------------------
| 2) Public pages (login ke baghair khulne wale)
|--------------------------------------------------------------------------
| forgot/reset password abhi sirf design hain, baad mein asli banenge.
*/
$pages([
    'coming-soon', 'under-maintenance', 'error-404',
    'error-500', 'forgot-password', 'reset-password',
]);

/*
|--------------------------------------------------------------------------
| 3) Sirf login wale users
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () use ($pages) {

    // Logout POST se hona chahiye (GET se koi bhi logout karwa sakta hai)
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // ---- Sab roles: dashboard, profile ----
    Route::view('/', 'pages.index')->name('index');
    $pages([
    'notification',
    ]);

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // ---- POS / bikri: admin, manager, cashier ----
    Route::middleware('role:admin,manager,cashier')->group(function () use ($pages) {
        $pages([
            'pos', 'sales-list', 'sales-returns',
            'customers', 'quotation-list',
        ]);
    });

    // ---- Products / stock / purchase: admin, manager, stock ----
    Route::middleware('role:admin,manager,stock')->group(function () use ($pages) {
        $pages([
            'product-list', 'add-product', 'edit-product',
            'product-details', 'brand-list',
            'sub-categories', 'units', 'varriant-attributes',
            'warranty', 'barcode', 'qrcode',
            'expired-products', 'low-stocks', 'manage-stocks',
            'stock-adjustment', 'stock-transfer', 'purchase-list',
            'purchase-returns', 'suppliers', 'warehouse',
        ]);
        
             Route::get('/category-list', [CategoryController::class, 'index'])->name('category-list');
             Route::post('/category-list', [CategoryController::class, 'store'])->name('categories.store');
             Route::put('/category-list/{category}', [CategoryController::class, 'update'])->name('categories.update');
             Route::delete('/category-list/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    });

    // ---- Reports aur hisab-kitab: admin, manager ----
    Route::middleware('role:admin,manager')->group(function () use ($pages) {
        $pages([
            'sales-dashboard', 'sales-report', 'purchase-report',
            'purchase-order-report', 'inventory-report', 'invoice-report',
            'supplier-report', 'customer-report', 'expense-report',
            'income-report', 'tax-reports', 'profit-and-loss',
            'expense-list', 'expense-category', 'coupons',
        ]);
    });

    // ---- Sirf admin: users, roles, settings, HR ----
    Route::middleware('role:admin')->group(function () use ($pages) {
        Route::resource('users', UserController::class)->only(['index', 'store', 'update']);

        $pages([
            'roles-permissions', 'permissions', 'company-settings',
            'general-settings', 'currency-settings', 'system-settings',
            'invoice-settings', 'pos-settings', 'printer-settings',
            'email-settings', 'sms-gateway', 'otp-settings',
            'payment-gateway-settings', 'bank-settings-grid', 'bank-settings-list',
            'language-settings', 'language-settings-web', 'localization-settings',
            'storage-settings', 'security-settings', 'gdpr-settings',
            'social-authentication', 'connected-apps', 'ban-ip-address',
            'appearance', 'theme-settings', 'preference',
            'prefixes', 'custom-fields', 'tax-rates',
            'countries', 'states', 'store-list',
            'delete-account', 'activities', 'email',
            'employees-grid', 'employees-list', 'add-employee',
            'edit-employee', 'department-grid', 'department-list',
            'designation', 'shift', 'holidays',
            'attendance-admin', 'attendance-employee', 'leaves-admin',
            'leaves-employee', 'leave-types', 'payroll-list',
            'payslip',
        ]);
    });
});

/*
|--------------------------------------------------------------------------
| 4) Template ke demo pages (icons, charts, form demos, -2/-3 login pages...)
|--------------------------------------------------------------------------
| Ye asli POS ka hissa nahi, sirf design dekhne ke liye hain. Production mein
| band rehte hain (sirf APP_ENV=local par khulte hain).
*/
if (app()->environment('local')) {
    $pages([
        'blank-page', 'data-tables', 'tables-basic',
        'lock-screen', 'chart-apex', 'chart-c3',
        'chart-flot', 'chart-js', 'chart-morris',
        'chart-peity', 'icon-feather', 'icon-flag',
        'icon-fontawesome', 'icon-ionic', 'icon-material',
        'icon-pe7', 'icon-simpleline', 'icon-themify',
        'icon-typicon', 'icon-weather', 'form-basic-inputs',
        'form-checkbox-radios', 'form-elements', 'form-fileupload',
        'form-floating-labels', 'form-grid-gutters', 'form-horizontal',
        'form-input-groups', 'form-mask', 'form-select',
        'form-select2', 'form-validation', 'form-vertical',
        'form-wizard', 'signin-2', 'signin-3',
        'register-2', 'register-3', 'forgot-password-2',
        'forgot-password-3', 'reset-password-2', 'reset-password-3',
        'email-verification', 'email-verification-2', 'email-verification-3',
        'two-step-verification', 'two-step-verification-2', 'two-step-verification-3',
        'success', 'success-2', 'success-3',
    ]);
}