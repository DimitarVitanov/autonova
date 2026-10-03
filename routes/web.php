<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ListingController as AdminListingController;
use App\Http\Controllers\Admin\TranslationController as AdminTranslationController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DealerController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SavedController;
use App\Http\Controllers\SavedSearchController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public marketplace
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/search', [VehicleController::class, 'index'])->name('vehicles.index');
Route::get('/vehicle/{vehicle:slug}', [VehicleController::class, 'show'])->name('vehicles.show');
Route::get('/dealers', [DealerController::class, 'index'])->name('dealers.index');
Route::get('/dealer/{dealer:slug}', [DealerController::class, 'show'])->name('dealers.show');
Route::get('/pricing', [PricingController::class, 'index'])->name('pricing');

// Language switch (Macedonian / English)
Route::get('/locale/{locale}', function (string $locale) {
    if (in_array($locale, ['mk', 'en'], true)) {
        session(['locale' => $locale]);
    }

    return back();
})->name('locale.switch');

// Dependent-select helpers
Route::get('/api/makes', [CatalogController::class, 'makes'])->name('api.makes');
Route::get('/api/models', [CatalogController::class, 'models'])->name('api.models');
Route::get('/api/versions', [CatalogController::class, 'versions'])->name('api.versions');

/*
|--------------------------------------------------------------------------
| Authenticated members (private sellers & dealers)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Listings (posting needs a confirmed email address)
    Route::get('/sell', [VehicleController::class, 'create'])->middleware('verified')->name('vehicles.create');
    Route::post('/vehicles', [VehicleController::class, 'store'])->middleware('verified')->name('vehicles.store');
    Route::get('/vehicles/{vehicle:slug}/edit', [VehicleController::class, 'edit'])->name('vehicles.edit');
    Route::put('/vehicles/{vehicle:slug}', [VehicleController::class, 'update'])->name('vehicles.update');
    Route::delete('/vehicles/{vehicle:slug}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');

    // Favorites & saved searches
    Route::post('/favorites/{vehicle:slug}', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
    Route::get('/saved', [SavedController::class, 'index'])->name('saved');
    Route::post('/saved-searches', [SavedSearchController::class, 'store'])->name('saved-searches.store');
    Route::delete('/saved-searches/{savedSearch}', [SavedSearchController::class, 'destroy'])->name('saved-searches.destroy');

    // Messaging
    Route::get('/inbox', [InboxController::class, 'index'])->name('inbox');
    Route::post('/vehicle/{vehicle:slug}/contact', [ConversationController::class, 'start'])->middleware('verified')->name('conversations.start');
    Route::post('/conversations/{conversation}/messages', [ConversationController::class, 'message'])->middleware('verified')->name('conversations.message');

    // Profile (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Staff area — moderators & admins
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin,moderator'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/listings', [AdminListingController::class, 'index'])->name('listings');
    Route::post('/listings/{vehicle:slug}/approve', [AdminListingController::class, 'approve'])->name('listings.approve');
    Route::post('/listings/{vehicle:slug}/reject', [AdminListingController::class, 'reject'])->name('listings.reject');
    Route::post('/listings/{vehicle:slug}/feature', [AdminListingController::class, 'toggleFeature'])->name('listings.feature');
    Route::delete('/listings/{vehicle:slug}', [AdminListingController::class, 'destroy'])->name('listings.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin-only
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [AdminUserController::class, 'index'])->name('users');
    Route::patch('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.role');
    Route::post('/users/{user}/toggle', [AdminUserController::class, 'toggleActive'])->name('users.toggle');

    Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories');
    Route::patch('/categories/{category:slug}', [AdminCategoryController::class, 'update'])->name('categories.update');

    Route::get('/translations', [AdminTranslationController::class, 'index'])->name('translations');
    Route::post('/translations', [AdminTranslationController::class, 'update'])->name('translations.update');
});

require __DIR__.'/auth.php';
