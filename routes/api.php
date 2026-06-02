<?php

use App\Http\Controllers\API\Admin\AdminAbandonedCartController;
use App\Http\Controllers\API\Admin\AdminCategoryController;
use App\Http\Controllers\API\Admin\AdminDashboardController;
use App\Http\Controllers\API\Admin\AdminImportController;
use App\Http\Controllers\API\Admin\AdminNewsletterController;
use App\Http\Controllers\API\Admin\AdminOrderController;
use App\Http\Controllers\API\Admin\AdminProductController;
use App\Http\Controllers\API\Admin\AdminReturnController;
use App\Http\Controllers\API\Admin\AdminUploadController;
use App\Http\Controllers\API\Admin\AdminLeadsController;
use App\Http\Controllers\API\Admin\AdminVisitorController;
use App\Http\Controllers\API\Admin\AdminWalletController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\InvoiceController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\WalletController;
use App\Http\Controllers\API\PushNotificationController;
use App\Http\Controllers\API\BundleController;
use App\Http\Controllers\API\ComparisonController;
use App\Http\Controllers\API\CouponController;
use App\Http\Controllers\API\EmailLeadController;
use App\Http\Controllers\API\FlashSaleController;
use App\Http\Controllers\API\LoyaltyController;
use App\Http\Controllers\API\OrderAckController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\OrderTrackingController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ProductQuestionController;
use App\Http\Controllers\API\ProductViewController;
use App\Http\Controllers\API\RecentlyViewedController;
use App\Http\Controllers\API\ReferralController;
use App\Http\Controllers\API\ReturnRequestController;
use App\Http\Controllers\API\ReviewController;
use App\Http\Controllers\API\StockAlertController;
use App\Http\Controllers\API\WishlistController;
use Illuminate\Support\Facades\Route;

// ── Public routes ──────────────────────────────────────────────
Route::get('banners', fn() => response()->json(\App\Models\Banner::where('is_active', true)->orderBy('sort_order')->get()));
Route::get('brands',  fn() => response()->json(\App\Models\Brand::where('is_active', true)->get()));

Route::middleware('throttle:10,1')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login',    [AuthController::class, 'login']);
});

Route::get('categories',       [CategoryController::class, 'index']);
Route::get('categories/{slug}', [CategoryController::class, 'show']);

Route::get('products',               [ProductController::class, 'index']);
Route::get('products/featured',      [ProductController::class, 'featured']);
Route::get('products/{slug}',        [ProductController::class, 'show']);
Route::get('products/{slug}/related',[ProductController::class, 'related']);

Route::get('flash-sales', [FlashSaleController::class, 'index']);

Route::post('compare', [ComparisonController::class, 'compare']);

Route::get('products/{slug}/questions',  [ProductQuestionController::class, 'index']);
Route::post('products/{slug}/questions', [ProductQuestionController::class, 'store'])->middleware('throttle:20,1');

Route::get('bundles',              [BundleController::class, 'index']);
Route::get('bundles/{slug}',       [BundleController::class, 'show']);

Route::post('products/{productId}/view',       [ProductViewController::class, 'log'])->middleware('throttle:30,1');
Route::post('stock-alerts/{productId}',        [StockAlertController::class, 'subscribe'])->middleware('throttle:5,1');
Route::delete('stock-alerts/{productId}',      [StockAlertController::class, 'unsubscribe']);
Route::get('recently-viewed',                  [RecentlyViewedController::class, 'index']);
Route::post('recently-viewed/{productId}',     [RecentlyViewedController::class, 'store'])->middleware('throttle:30,1');

Route::get('orders/track/{uuid}', [OrderTrackingController::class, 'index']);

Route::post('leads',       [EmailLeadController::class, 'store'])->middleware('throttle:5,1');
Route::post('coupons/validate', [CouponController::class, 'validate']);

Route::get('ack/{token}',  [OrderAckController::class, 'show']);
Route::post('ack/{token}', [OrderAckController::class, 'confirm'])->middleware('throttle:5,1');

Route::get('cart',             [CartController::class, 'index']);
Route::post('cart',            [CartController::class, 'add'])->middleware('throttle:30,1');
Route::put('cart/{cart}',      [CartController::class, 'update']);
Route::delete('cart/{cart}',   [CartController::class, 'remove']);
Route::delete('cart',          [CartController::class, 'clear']);

// Push notification subscriptions (public — browser calls before auth flow)
Route::post('push/subscribe',    [PushNotificationController::class, 'subscribe']);
Route::delete('push/subscribe',  [PushNotificationController::class, 'unsubscribe']);

// Stripe webhook — public, no CSRF, verified via signature
Route::post('payments/stripe-webhook', [PaymentController::class, 'stripeWebhook']);

// JazzCash & EasyPaisa callbacks — public (called by payment gateways)
Route::post('payments/jazzcash/callback',  [\App\Http\Controllers\API\JazzCashController::class, 'callback']);
Route::get('payments/easypaisa/callback',  [\App\Http\Controllers\API\EasyPaisaController::class, 'callback']);
Route::post('payments/easypaisa/callback', [\App\Http\Controllers\API\EasyPaisaController::class, 'callback']);

// ── Authenticated routes ────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout',             [AuthController::class, 'logout']);
    Route::get('profile',             [AuthController::class, 'profile']);
    Route::put('profile',             [AuthController::class, 'updateProfile']);
    Route::put('profile/password',    [AuthController::class, 'changePassword']);

    Route::get('addresses',                    [AuthController::class, 'addresses']);
    Route::post('addresses',                   [AuthController::class, 'storeAddress']);
    Route::put('addresses/{address}',          [AuthController::class, 'updateAddress']);
    Route::delete('addresses/{address}',       [AuthController::class, 'destroyAddress']);

    Route::get('orders',                       [OrderController::class, 'index']);
    Route::post('orders',                      [OrderController::class, 'store']);
    Route::get('orders/{order}',               [OrderController::class, 'show']);
    Route::put('orders/{order}/cancel',        [OrderController::class, 'cancel']);
    Route::get('orders/{order}/returns',       [ReturnRequestController::class, 'index']);
    Route::post('orders/{order}/returns',      [ReturnRequestController::class, 'store']);

    Route::get('returns/{returnRequest}',      [ReturnRequestController::class, 'show']);

    Route::get('wishlist',  [WishlistController::class, 'index']);
    Route::post('wishlist', [WishlistController::class, 'toggle']);

    Route::post('products/{productId}/reviews', [ReviewController::class, 'store'])->middleware('throttle:5,1');

    Route::post('bundles/{bundle}/add-to-cart', [BundleController::class, 'addToCart']);

    Route::get('loyalty/balance',  [LoyaltyController::class, 'balance']);
    Route::get('loyalty/history',  [LoyaltyController::class, 'history']);
    Route::post('loyalty/redeem',  [LoyaltyController::class, 'redeem']);

    Route::get('referral/my-code', [ReferralController::class, 'myCode']);
    Route::post('referral/apply',  [ReferralController::class, 'apply']);

    // JazzCash & EasyPaisa payment initiation (authenticated)
    Route::post('payments/jazzcash',  [\App\Http\Controllers\API\JazzCashController::class, 'initiate']);
    Route::post('payments/easypaisa', [\App\Http\Controllers\API\EasyPaisaController::class, 'initiate']);

    Route::get('wallet/balance',   [WalletController::class, 'balance']);
    Route::get('wallet/history',   [WalletController::class, 'history']);

    Route::get('orders/{order}/invoice', [InvoiceController::class, 'download']);

    // Stripe payment intent (authenticated — tied to the logged-in user's order)
    Route::post('payments/create-intent', [PaymentController::class, 'createIntent']);
});

// ── Admin routes ────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('dashboard', [AdminDashboardController::class, 'index']);

    Route::apiResource('products',   AdminProductController::class);
    Route::apiResource('categories', AdminCategoryController::class);

    Route::get('orders',                       [AdminOrderController::class, 'index']);
    Route::get('orders/{order}',               [AdminOrderController::class, 'show']);
    Route::put('orders/{order}/status',        [AdminOrderController::class, 'updateStatus']);

    Route::get('returns',                      [AdminReturnController::class, 'index']);
    Route::put('returns/{returnRequest}',      [AdminReturnController::class, 'update']);

    Route::post('upload',    [AdminUploadController::class, 'upload']);
    Route::delete('upload',  [AdminUploadController::class, 'delete']);

    Route::get('visitors',         [AdminVisitorController::class, 'index']);
    Route::get('visitors/summary', [AdminVisitorController::class, 'summary']);

    Route::get('leads',                        [AdminLeadsController::class, 'index']);
    Route::get('leads/stats',                  [AdminLeadsController::class, 'stats']);
    Route::put('leads/{lead}/contacted',       [AdminLeadsController::class, 'markContacted']);
    Route::delete('leads/{lead}',              [AdminLeadsController::class, 'destroy']);

    Route::get('coupons',            [CouponController::class, 'index']);
    Route::post('coupons',           [CouponController::class, 'store']);
    Route::put('coupons/{coupon}',   [CouponController::class, 'update']);
    Route::delete('coupons/{coupon}',[CouponController::class, 'destroy']);

    Route::get('flash-sales',                  [FlashSaleController::class, 'adminIndex']);
    Route::post('flash-sales',                 [FlashSaleController::class, 'store']);
    Route::put('flash-sales/{flashSale}',      [FlashSaleController::class, 'update']);
    Route::delete('flash-sales/{flashSale}',   [FlashSaleController::class, 'destroy']);

    Route::get('questions',                    [ProductQuestionController::class, 'adminIndex']);
    Route::put('questions/{question}/answer',  [ProductQuestionController::class, 'answer']);

    Route::post('bundles',            [BundleController::class, 'store']);
    Route::put('bundles/{bundle}',    [BundleController::class, 'update']);
    Route::delete('bundles/{bundle}', [BundleController::class, 'destroy']);

    // Abandoned cart
    Route::get('abandoned-carts',                          [AdminAbandonedCartController::class, 'index']);
    Route::post('abandoned-carts/{userId}/send-recovery',  [AdminAbandonedCartController::class, 'sendRecovery']);

    // Bulk import
    Route::post('import',           [AdminImportController::class, 'import']);
    Route::get('import/template',   [AdminImportController::class, 'template']);

    // Newsletter
    Route::post('newsletter/send',  [AdminNewsletterController::class, 'send']);

    // Wallet admin credit
    Route::post('users/{user}/wallet/credit', [AdminWalletController::class, 'credit']);
});
