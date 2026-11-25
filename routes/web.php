<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ContactMessageController as AdminContactMessageController;
use App\Http\Controllers\Admin\CountryController as AdminCountryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\SupplierLogController;
use App\Http\Controllers\Admin\SupplierController as AdminSupplierController;
use App\Http\Controllers\Admin\SupplierSettingController as AdminSupplierSettingController;
use App\Http\Controllers\Admin\ServiceManagementController as AdminServiceManagementController;
use App\Http\Controllers\Admin\TicketController as AdminTicketController;
use App\Http\Controllers\CatalogueController;
use App\Http\Controllers\Customer\ApiKeyController;
use App\Http\Controllers\Customer\DashboardController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Customer\TicketController;
use App\Http\Controllers\Customer\WalletController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/faq', [PageController::class, 'faq'])->name('faq');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [PageController::class, 'storeContact'])->middleware('throttle:6,1')->name('contact.store');
Route::get('/services', [CatalogueController::class, 'index'])->name('services.index');
Route::get('/services/smm-products', [CatalogueController::class, 'smmProducts'])->name('services.smm-products');
Route::get('/services/{product}', [CatalogueController::class, 'show'])->name('services.show');
Route::get('/verification', [VerificationController::class, 'index'])->name('verification.index');
Route::get('/pricing', [PageController::class, 'pricing'])->name('pricing');
Route::get('/api', [PageController::class, 'apiDocs'])->name('api.docs');
Route::get('/support', [PageController::class, 'support'])->name('support');
Route::get('/blog', [PageController::class, 'blog'])->name('blog');
Route::get('/pages/terms', [PageController::class, 'terms'])->name('terms');
Route::get('/pages/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/pages/refund-policy', [PageController::class, 'refundPolicy'])->name('refund');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::middleware(['auth'])->prefix('account')->name('dashboard.')->group(function () {
    Route::get('/', [DashboardController::class, 'overview'])->name('overview');
    Route::get('/orders', [DashboardController::class, 'orders'])->name('orders.index');
    Route::get('/orders/{order}', [DashboardController::class, 'order'])->name('orders.show');
    Route::get('/numbers', [DashboardController::class, 'numbers'])->name('numbers.index');
    Route::post('/verification/orders', [VerificationController::class, 'store'])->name('verification.orders.store');
    Route::get('/verification/orders/{order}', [VerificationController::class, 'show'])->name('verification.orders.show');
    Route::get('/verification/orders/{order}/status', [VerificationController::class, 'status'])->name('verification.orders.status')->middleware('throttle:30,1');
    Route::post('/verification/orders/{order}/cancel', [VerificationController::class, 'cancel'])->name('verification.orders.cancel');
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('/wallet/deposit', [WalletController::class, 'deposit'])->name('wallet.deposit');
    Route::get('/api-keys', [ApiKeyController::class, 'index'])->name('api-keys.index');
    Route::post('/api-keys', [ApiKeyController::class, 'store'])->name('api-keys.store');
    Route::delete('/api-keys/{apiKey}', [ApiKeyController::class, 'destroy'])->name('api-keys.destroy');
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{ticket}/messages', [TicketController::class, 'reply'])->name('tickets.messages.store');
    Route::get('/orders/{product}/create', [OrderController::class, 'create'])->name('orders.create');
    Route::post('/orders/{product}', [OrderController::class, 'store'])->name('orders.store');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::post('/providers/{provider}/toggle', [AdminDashboardController::class, 'toggleProvider'])->name('providers.toggle');
    Route::post('/suppliers/{provider}/test', [AdminSupplierController::class, 'test'])->name('suppliers.test');
    Route::post('/suppliers/{provider}/sync', [AdminSupplierController::class, 'sync'])->name('suppliers.sync');
    Route::get('/suppliers/{provider}/settings', [AdminSupplierSettingController::class, 'edit'])->name('suppliers.settings');
    Route::patch('/suppliers/{provider}/settings', [AdminSupplierSettingController::class, 'update'])->name('suppliers.settings.update');
    Route::get('/service-management', [AdminServiceManagementController::class, 'index'])->name('service-management.index');
    Route::patch('/service-management', [AdminServiceManagementController::class, 'update'])->name('service-management.update');
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}', [AdminOrderController::class, 'update'])->name('orders.update');
    Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [AdminProductController::class, 'create'])->name('products.create');
    Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [AdminProductController::class, 'edit'])->name('products.edit');
    Route::patch('/products/{product}', [AdminProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');
    Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [AdminCategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}/edit', [AdminCategoryController::class, 'edit'])->name('categories.edit');
    Route::patch('/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');
    Route::get('/countries', [AdminCountryController::class, 'index'])->name('countries.index');
    Route::get('/countries/create', [AdminCountryController::class, 'create'])->name('countries.create');
    Route::post('/countries', [AdminCountryController::class, 'store'])->name('countries.store');
    Route::get('/countries/{country}/edit', [AdminCountryController::class, 'edit'])->name('countries.edit');
    Route::patch('/countries/{country}', [AdminCountryController::class, 'update'])->name('countries.update');
    Route::delete('/countries/{country}', [AdminCountryController::class, 'destroy'])->name('countries.destroy');
    Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
    Route::post('/payments/{payment}/verify', [AdminPaymentController::class, 'verify'])->name('payments.verify');
    Route::post('/payments/{payment}/reject', [AdminPaymentController::class, 'reject'])->name('payments.reject');
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/tickets', [AdminTicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/{ticket}', [AdminTicketController::class, 'show'])->name('tickets.show');
    Route::patch('/tickets/{ticket}', [AdminTicketController::class, 'update'])->name('tickets.update');
    Route::post('/tickets/{ticket}/messages', [AdminTicketController::class, 'reply'])->name('tickets.messages.store');
    Route::get('/contact-messages', [AdminContactMessageController::class, 'index'])->name('contact-messages.index');
    Route::get('/contact-messages/{contactMessage}', [AdminContactMessageController::class, 'show'])->name('contact-messages.show');
    Route::patch('/contact-messages/{contactMessage}', [AdminContactMessageController::class, 'update'])->name('contact-messages.update');
    Route::get('/supplier-logs', [SupplierLogController::class, 'index'])->name('supplier-logs.index');
    Route::get('/settings', [AdminSettingController::class, 'index'])->name('settings.index');
    Route::patch('/settings', [AdminSettingController::class, 'update'])->name('settings.update');
});

Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/');
})->middleware('auth')->name('logout');

Route::redirect('dashboard', '/account')->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
