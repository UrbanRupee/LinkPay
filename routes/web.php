<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Logics_building;
use App\Http\Controllers\pages;

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

Route::get('/', function () {
    try {
        if (session()->has('userlogin')) {
            return redirect('/dashboard');
        } elseif (session()->has('adminlogin')) {
            return redirect('/admin/dashboard');
        } else {
            return redirect('/login');
        }
    } catch (\Exception $e) {
        // Fallback to login if there's any session error
        return redirect('/login');
    }
});

Route::get('/login', [pages::class, "login"]);
Route::get('/register', [pages::class, "register"]);
Route::post('/login', [pages::class, "login_submit"]);
Route::post('/register', [pages::class, "register_submit"]);
Route::get('/logout', [pages::class, "logout"]);

Route::get('/forgot', [pages::class, "forgot"]);
Route::post('/forgot', [pages::class, "forgot_submit"]);

Route::get('/reset/{token}', [pages::class, "reset"]);
Route::post('/reset', [pages::class, "reset_submit"]);

Route::get('/verify/{token}', [pages::class, "verify"]);

Route::get('/admin', [pages::class, "admin_login"]);
Route::post('/admin/login', [pages::class, "admin_login_submit"]);
Route::get('/admin/logout', [pages::class, "admin_logout"]);

Route::get('paydeer/redirect/{token}', [Logics_building::class,"paydeerRedirect"])->name('paydeer.redirect');
Route::get('/payment-success', [pages::class, "payment_success"]);
Route::get('/payment-failed', [pages::class, "payment_failed"]);
Route::get('/retry-payment', [pages::class, "retry_payment"]);
Route::get('/maintainPayinBalanace', [pages::class, "maintainPayinBalanace"]);
Route::get('/cleanupOrphanedWallets', [pages::class, "cleanupOrphanedWallets"]);

Route::get('/test-payin-total', function() {
    $total = \App\Models\Payment_request::where('status', 1)->where('data3', 1)->sum(DB::raw('amount - tax'));
    return "Total Payin Transactions: ₹" . number_format($total, 2);
});

Route::get('/test-dashboard-data', function () {
    $userWallet = \App\Models\Wallet::where('userid', 'UR1128')->first();
    return response()->json([
        'userid' => 'UR1128',
        'wallet' => $userWallet ? $userWallet->toArray() : 'Not found',
        'payin_balance' => $userWallet ? $userWallet->payin : 0,
        'payout_balance' => $userWallet ? $userWallet->payout : 0
    ]);
});

// UrbanPay Frontend Example - Serve HTML file
Route::get('/urbanpay-frontend-example.html', function () {
    $filePath = public_path('urbanpay-frontend-example.html');
    if (file_exists($filePath)) {
        return response()->file($filePath, ['Content-Type' => 'text/html']);
    }
    // Fallback: read and return HTML content if file doesn't exist yet
    $htmlContent = file_get_contents(base_path('public/urbanpay-frontend-example.html'));
    if ($htmlContent) {
        return response($htmlContent, 200)->header('Content-Type', 'text/html');
    }
    return response('File not found. Please deploy urbanpay-frontend-example.html to public directory.', 404);
});

Route::middleware(['isUser'])->group(function () {
    Route::get('/dashboard', [pages::class, "user_dashboard"]);
    Route::get('/profile', [pages::class, "user_profile"]);
    Route::post('/profile', [pages::class, "user_profile_submit"]);
    
    Route::get('/user/add-fund-history', [pages::class, "user_add_fund_history"]);
    Route::get('/user/payin_report', [pages::class, "payin_report"]);
    Route::get('/user/payout_report', [pages::class, "payout_report"]);
    // Route::get('/user/card_income', [pages::class, "card_income"]); // REMOVED: Card transactions not needed
    Route::get('/user/settlement_report', [pages::class, "settlement_report"]);
    Route::get('/user/settlement/invoice/{id}', [pages::class, "settlement_invoice"])->name('user.settlement.invoice');
    Route::get('/user/payment-link', [pages::class, "payment_link_page"]);
    Route::post('/user/payment-link/generate', [pages::class, "generate_payment_link"])->name('user.payment_link.generate');
    Route::get('/user/pgdocs', [pages::class, "pgdocs"]);
    Route::get('/user/edit-profile', [pages::class, "user_edit_profile"]);
    Route::get('/user/reset-password', [pages::class, "user_reset_password"]);
    Route::get('/user/support/chat-support', [pages::class, "chat_support"]);
    Route::get('/user/wallet', [pages::class, "main_wallet"]);
    Route::get('/user/fund-area', [pages::class, "user_add_fund"]);
    Route::get('/user/export_payout_report', [pages::class, "export_payout_report"]);
});

Route::middleware(['isAdmin'])->group(function () {
    Route::get('/admin/dashboard', [pages::class, "admin_dashboard"]);
    Route::get('/admin/users', [pages::class, "admin_userlist"]);
    Route::get('/admin/userlist', [pages::class, "admin_userlist"]);
    Route::get('/admin/user/{id}', [pages::class, "admin_user_edit"]);
    Route::post('/admin/user/{id}', [pages::class, "admin_user_edit"]);
    Route::get('/admin/userlist/edit/{id}', [pages::class, "admin_user_edit"]);
    
    Route::get('/admin/useradd', [pages::class, "admin_useradd"]);
    Route::get('/admin/user_request', [pages::class, "admin_user_request"]);
    Route::get('/admin/userprofile/{userid}', [pages::class, "admin_userprofile"]);
    Route::get('/admin/user_ledger/{id?}', [pages::class, "admin_user_ledger"]);
    Route::get('/admin/payout_ledger/{id?}', [pages::class, "admin_payout_ledger"]);
    Route::get('/admin/product_ledger/{id?}', [pages::class, "admin_product_ledger"]);
    // Route::get('/admin/card_ledger', [pages::class, "admin_card_ledger"]); // REMOVED: Card ledger not needed
    // Route::get('/admin/user_card_ledger/{userid}', [pages::class, "admin_user_card_ledger"]); // REMOVED: Card ledger not needed
    Route::get('/admin/settlement', [pages::class, "admin_settlement"]);
    Route::get('/admin/settlement_list', [pages::class, "admin_settlementlist"]);
    Route::get('/admin/hold_ledger', [pages::class, "admin_hold_ledger"]);
    Route::get('/admin/verify_transfer', [pages::class, "admin_verify_transafer"]);
    Route::get('/admin/mannual_payment', [pages::class, "admin_mannual_payment"]);
    Route::get('/admin/amount_transfer', [pages::class, "admin_amount_transfer"]);
    Route::get('/admin/withdrawal_request', [pages::class, "admin_withdrawal_request"]);
    Route::get('/admin/setting/{id?}', [pages::class, "admin_setting"]);
    Route::get('/admin/change_password', [pages::class, "admin_change_password"]);
    Route::get('/admin/change-password', [pages::class, "admin_change_password"]); // Alias with hyphen
    Route::get('/admin/logs', [pages::class, "admin_logs"]);
    Route::get('/admin/add_product', [pages::class, "admin_add_product"]);
    Route::get('/admin/category/{id?}', [pages::class, "admin_category"]);
    Route::get('/admin/2_club', [pages::class, "admin_2_club"]);
    Route::get('/admin/3_club', [pages::class, "admin_3_club"]);
    Route::get('/admin/club_report', [pages::class, "admin_club_report"]);
    Route::get('/admin/providers', [pages::class, "admin_providers"]);
    Route::get('/admin/settlement/export', [pages::class, "admin_export_settlements"])->name('admin.export.settlements');
    Route::get('/admin/settlement_list/export', [pages::class, "admin_export_settlement_list"])->name('admin.export.settlement_list');
    
    // Provider API Routes
    Route::get('/admin/api/providers/list', [pages::class, "admin_providers_list"]);
    Route::post('/admin/api/provider/add', [pages::class, "admin_provider_add"]);
    
    Route::get('/admin/Ludouserlist/{id?}', [pages::class, "admin_Ludouserlist"]);
    
    // User-specific admin routes
    Route::get('/admin/user/login/{id}', [pages::class, "admin_redirect_to_user_dashboard"]);
    Route::get('/admin/user/user-ledger/{id}', [pages::class, "admin_user_ledger"]);
    Route::get('/admin/user/payout-ledger/{id}', [pages::class, "admin_payout_ledger"]);
    
    // Additional admin routes
    Route::get('/admin/payout_recon', [\App\Http\Controllers\AutoReconciliation::class, "showReconciliationPage"]);
    Route::get('/admin/payout_recon_old', [Logics_building::class, "rudraxpaypayoutreconcile"]); // Legacy route
    Route::post('/admin/payout_recon/trigger', [\App\Http\Controllers\AutoReconciliation::class, "reconcileAllPendingPayouts"]);
    Route::get('/admin/payout_recon/stats', [\App\Http\Controllers\AutoReconciliation::class, "getReconciliationStats"]);
    Route::post('/admin/payout_recon/bulk-cancel', [\App\Http\Controllers\AutoReconciliation::class, "bulkCancelTransactions"]);
    Route::post('/admin/payout_recon/cancel/{id}', [\App\Http\Controllers\AutoReconciliation::class, "cancelTransaction"]);
    
    // Wallet Reconciliation Routes
    Route::get('/maintainPayinBalanace', [\App\Http\Controllers\WalletReconciliation::class, "maintainPayinBalance"]);
    Route::post('/admin/wallet/fix/{userid}', [\App\Http\Controllers\WalletReconciliation::class, "autoFixWallet"]);
    Route::post('/admin/wallet/bulk-fix', [\App\Http\Controllers\WalletReconciliation::class, "bulkReconcile"]);
    Route::get('/admin/wallet/check-pending/{userid}', [\App\Http\Controllers\WalletReconciliation::class, "checkPending"]);
    Route::get('/admin/wallet/nso-pending/{userid}', [\App\Http\Controllers\WalletReconciliation::class, "checkNSOPending"]);
    Route::get('/admin/wallet/nso-monitor', [\App\Http\Controllers\WalletReconciliation::class, "monitorNSOCallbacks"]);
    Route::get('/admin/wallet/report', [\App\Http\Controllers\WalletReconciliation::class, "generateReport"]);
    Route::get('/admin/wallet/diagnostic/{userid}', [\App\Http\Controllers\WalletReconciliation::class, "getDiagnostic"]);
    
    Route::get('/admin/restart_system', [pages::class, "admin_restart_system"]);
});

// Export routes with named routes
Route::get('/export_payout_report', [pages::class, "export_payout_report"])->name('user.export.payout_report');
Route::get('/export_payin_report', [pages::class, "export_payin_report"])->name('user.export.payin_report');
Route::get('/export_settlement_report', [pages::class, "export_settlement_report"])->name('user.export.settlement_report');

// Admin API Routes
Route::post('admin/api/user/becomefranchise/{id}', [Logics_building::class,"becomefranchise"]);
Route::post('admin/api/transactionVerify', [Logics_building::class,"admin_transactionVerify"]);
Route::post('admin/api/amounttransfer', [Logics_building::class,"admin_amounttransfer"]);
Route::post('admin/api/amounttransferSelf', [Logics_building::class,"admin_amounttransferSelf"]);
Route::post('admin/api/payout_mannual', [Logics_building::class,"admin_payout_mannual"]);
Route::post('admin/api/add_user', [Logics_building::class,"add_user"]);
Route::post('admin/api/user_edit/{id}', [pages::class,"admin_user_edit"]);
Route::post('admin/api/sell_now_product', [Logics_building::class,"sell_now_product"]);
Route::post('admin/api/settlement/approve', [Logics_building::class,"admin_settlement_aprove"]);
Route::post('admin/api/e_pin/expire', [Logics_building::class,"admin_epin_expire"]);
Route::post('admin/api/update_setting', [Logics_building::class,"update_setting"]);
Route::post('admin/api/reset_password', [Logics_building::class,"admin_reset_password"]);
Route::post('admin/api/approve/withdrawal', [Logics_building::class,"admin_approve_withdrawal"]);
Route::post('admin/api/delete_user', [Logics_building::class,"delete_user"]);
Route::post('admin/api/e_pin/expire', [Logics_building::class,"admin_epin_expire"]);
Route::post('admin/api/update_setting', [Logics_building::class,"update_setting"]);
Route::post('admin/api/reset_password', [Logics_building::class,"admin_reset_password"]);
Route::post('admin/api/approve/withdrawal', [Logics_building::class,"admin_approve_withdrawal"]);
Route::post('admin/api/delete_user', [Logics_building::class,"delete_user"]);