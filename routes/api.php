<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Logics_building;
use App\Http\Controllers\LudoApiWhitelabel;
// ✅ ONLY EASEBUZZ AND SWIPEPOINTE ACTIVE
use App\Http\Controllers\Gateway\Callback;
use App\Http\Controllers\Gateway\PayoutCallback;
use App\Http\Controllers\Gateway\SwipePointe;
use App\Http\Controllers\Gateway\Easebuzz;

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
//Aadhar KYC Routes
Route::post('wallet', [Logics_building::class,"walletbalance"]);
Route::any('aadhar_verify', [Logics_building::class,"aadhar_verify"]);
Route::any('aadhar_otp_verify', [Logics_building::class,"aadhar_otp_verify"]);
Route::post('usercheck', [Logics_building::class,"usercheck"]);
// LudoKingApi
Route::get('ludo/roomcode/checker/{RoomCode}', [LudoApiWhitelabel::class,"RoomCodeCheckerLudo"]);
Route::get('ludo/roomcode/result/{RoomCode}', [LudoApiWhitelabel::class,"RoomCodeResultLudo"]);
Route::get('ludo/public/roomcode/result/{RoomCode}', [LudoApiWhitelabel::class,"RoomCodeResultLudoUser"]);
// Rudraxpay Routes
Route::post('pg/rudraxpay/payincallback', [Logics_building::class,"payinCallbackRudraxpay"]);
// UpiTel Routes
Route::post('pg/upitel/recharge_successfully', [Logics_building::class,"UpiTeladd_fund_success"]);
Route::any('pg/upitel/redirect_recharge_successfully/{trn}', [Logics_building::class,"upitel_redirect_response"]);
// Phonepe Routes
Route::post('pg/phonepe/recharge_successfully', [Logics_building::class,"add_fund_success"]);
Route::post('pg/phonepe/redirect_recharge_successfully', [Logics_building::class,"phonepe_redirect_response"]);
Route::post('pg/urbanpay/initiate', [Logics_building::class,"phonepe_initiate"]);
Route::post('pg/phonepe/initiateNonseamless', [Logics_building::class,"phonepe_initiate_non_seamless"]);
Route::post('pg/urbanpay/checkstatus', [Logics_building::class,"phonepe_checkstatus"]);
Route::post('pg/urbanpay/check-pending', [Logics_building::class,"check_pending_transactions"]);
Route::get('pg/phonepe/token/{token}/{mode}', [Logics_building::class,"phonepe_initiate_token"]);
Route::post('/phonepeinitiate', [Logics_building::class,"phonepeinitiate_byUser"]);
// BharatPe Routes
Route::post('bharatpe/checkUTR', [Bharatpe::class,"BharatPe_checkUTR"]);
// CCAvenue Routes
Route::post('payin/ccavenue/callback', [Logics_building::class,"ccavenue_callback"]);
// Cashfree Routes
Route::post('payin/cashfree/callback', [Logics_building::class,"cashfree_callback"]);
// Razorpay Routes
Route::get('payin/razorpay/Initate/{trn}', [Razorpay::class,"RazorpayInitaiteCard"]);
Route::post('payin/razorpay/callback', [Razorpay::class,"Razorpay_callback"]);
// Sabpaisa Routes
Route::post('sabpaisastatusapi', [Sabpaisa::class,"sabpaisastatusapi"]);
Route::get('payin/sabpaisa/Initate/{trn}', [Sabpaisa::class,"SabpaisaInitaitePage"]);
Route::any('callback/update/subpaisacbk', [Sabpaisa::class,"callback"]);
Route::post('payin/nsdl/callback', [Sabpaisa::class,"Nsdl_callback"]);
// runpaisa Routes
Route::get('payin/runpaisa/Initate/{trn}', [RunPaisa::class,"PayomatixInitaitePage"]);
Route::post('payin/runpaisa/callback', [RunPaisa::class,"callback"]);
Route::get('payin/runpaisa/return', [RunPaisa::class,"redirect_response"]);
// Payomatix Routes
Route::get('payin/payomatix/Initate/{trn}', [Payomatix::class,"PayomatixInitaitePage"]);
Route::post('payin/payomatix/callback', [Payomatix::class,"callback"]);
Route::get('payin/payomatix/return', [Payomatix::class,"redirect_response"]);
// USDPAY Routes
Route::post('payin/usdpay/callback', [Callback::class,"usdpay_callback"]);
Route::get('payin/payomatix/return', [Payomatix::class,"redirect_response"]);
// Airpay Routes
Route::post('payin/airpay/callback', [Callback::class,"usdpay_callback"]);
// Airpay Routes
Route::post('payin/safexpay/callback', [Callback::class,"usdpay_callback"]);
// FinQunes Callbacks (New)
Route::post('payin/finqunes/callback', [\App\Http\Controllers\Gateway\FinQunes::class,"callback"]);
Route::any('payout/finqunes/callback', [\App\Http\Controllers\Gateway\FinQunes::class,"payoutCallback"]);
// Legacy FinUniq Callbacks (Backward Compatibility)
Route::post('payin/finuniq/callback', [Sabpaisa::class,"finuniq_callback"]);
Route::any('payout/finuniq/callback', [PayoutCallback::class,"finuniq_callback"]);
//Payout Routes
Route::post('/payout/initiate', [Logics_building::class,"PayoutInitiateByApi"]);
Route::post('/payout/checkstatus', [Logics_building::class,"PayoutCheckStatus"]);
Route::post('/payout/initiateUPI', [Logics_building::class,"PayoutInitiateByApiUPI"]);
Route::post('/payout/ibr/impscallback', [Logics_building::class,"ibrpayIMSP_callback"]);
Route::post('/payout/razorpy_callback', [Logics_building::class,"razorpy_callback"]);
Route::get('/payout/ibr/statuscron', [Logics_building::class,"PayoutStatusCron"]);
Route::get('/payout/ibr/statuscronTest', [Logics_building::class,"TestPayoutStatusCron"]);
//Payout WAOPAY
// Route::post('/payout/waopay/initiate', [RunPaisa::class,"PayoutInitiateByApi"]);
Route::post('/payout/waopay/callback', [RunPaisa::class,"PayoutCallback"]);
// Route::post('/payout/waopay/initiate', [RunPaisa::class,"PayoutInitiateByApi"]);
Route::post('/payout/uniqpay/callback', [PayoutCallback::class,"UniqPayCallback"]);
Route::post('/payout/benakpay/callback', [PayoutCallback::class,"UniqPayCallback"]);
Route::post('/payout/velozPay/callback', [PayoutCallback::class,"VeloZPayCallback"]);
Route::post('/payout/motherPayPayout/callback', [PayoutCallback::class,"MotherPayPayoutCallback"]);
//Payout Universal Payout
Route::post('/payout/universepay/callback', [PayoutCallback::class,"universepay"]);
//Payout Cashkavach Payout
Route::post('/payout/kavachpay/callback', [PayoutCallback::class,"CashKavchPayout"]);
//Payout usdpay
Route::post('/payout/usdpay/callback', [PayoutCallback::class,"usdpay"]);
//Payout safexpay
Route::post('/payout/safexpay/callback', [PayoutCallback::class,"safexpaypayout"]);
//Test Inititae
Route::post('pg/phonepe/initiateTest', [Logics_building::class,"phonepe_initiateTest"]);
// BingePay Routes
Route::get('payin/bingepay/initiate/{trn}', [BingePay::class, 'initiatePayin']);   // Generate QR / Intent
Route::get('callback/update/bingepay',       [BingePay::class, 'callback']);       // Unified GET callback (pay-in/payout)
Route::get('payout/bingepay/initiate',      [BingePay::class, 'initiatePayout']); // Payout initiate

Route::post('/gateway/qutepaisa/payin/{trn}', [QutePaisa::class, 'initiatePayin']);
Route::post('/gateway/qutepaisa/payout', [QutePaisa::class, 'initiatePayout']);
Route::post('/gateway/qutepaisa/callback', [QutePaisa::class, 'callback']);

// Card Transaction Routes
Route::post('/card/initiate', [Logics_building::class, 'card_transaction']);
Route::post('/gateway/swipepointe/process', [SwipePointe::class, 'processCardTransaction']);

// SwipePointe Callback and Webhook Routes
Route::post('/gateway/swipepointe/callback', [SwipePointe::class, 'handleCallback']);
Route::post('/gateway/swipepointe/webhook', [SwipePointe::class, 'handleWebhook']);

// Paydeer Routes
Route::post('/gateway/paydeer/payin/{trn}', [Paydeer::class, 'initiatePayment']);
Route::post('/gateway/paydeer/payout', [Paydeer::class, 'initiatePayout']);
Route::post('/gateway/paydeer/callback', [Paydeer::class, 'callback']);
Route::post('/gateway/paydeer/payout-callback', [Paydeer::class, 'payoutCallback']);
Route::post('/gateway/paydeer/checkstatus', [Paydeer::class, 'checkStatus']);
Route::get('/test/paydeer', [Paydeer::class, 'test']);
Route::get('/test/paydeer/debug/{trn}', [Paydeer::class, 'debugPayin']);

// HZTPay Routes (Now using Paydrion API)
Route::post('/gateway/hztpay/payin/{trn}', [HZTPay::class, 'initiatePayin']);
Route::post('/gateway/hztpay/payout', [HZTPay::class, 'initiatePayout']);
Route::post('/gateway/hztpay/callback', [HZTPay::class, 'callback']);
Route::post('/gateway/hztpay/payout-callback', [HZTPay::class, 'payoutCallback']);
Route::post('/gateway/hztpay/check-payin-status', [HZTPay::class, 'checkPayinStatus']);
Route::post('/gateway/hztpay/check-payout-status', [HZTPay::class, 'checkPayoutStatus']);


// PayVanta Routes
Route::post('/gateway/payvanta/payin/{trn}', [PayVanta::class, 'initiatePayin']);
Route::post('/gateway/payvanta/payout', [PayVanta::class, 'initiatePayout']);
Route::post('/gateway/payvanta/webhook', [PayVanta::class, 'webhook']);

// PayVanta Test Routes
Route::get('/test/payvanta', [PayVanta::class, 'test']);
Route::get('/test/payvanta/debug/{trn}', [PayVanta::class, 'debugPayin']);

// ASVB Routes
Route::post('/gateway/asvb/payin/{trn}', [ASVB::class, 'initiatePayin']);
Route::post('/gateway/asvb/payout', [ASVB::class, 'initiatePayout']);
Route::post('/gateway/asvb/callback', [ASVB::class, 'callback']); // Unified callback
Route::post('/gateway/asvb/payin-callback', [ASVB::class, 'payinCallback']); // Separate PayIn callback
Route::post('/gateway/asvb/payout-callback', [ASVB::class, 'payoutCallback']); // Separate Payout callback

// ASVB Test Routes
Route::get('/test/asvb', [ASVB::class, 'test']);
Route::get('/test/asvb/debug/payin/{trn}', [ASVB::class, 'debugPayin']);
Route::get('/test/asvb/debug/payout/{trn}', [ASVB::class, 'debugPayout']);

// PayPayout Routes
Route::post('/gateway/paypayout/payout', [PayPayout::class, 'initiatePayout']);
Route::get('/gateway/paypayout/callback', [PayPayout::class, 'callback']);

// PayPayout Test Routes
Route::get('/test/paypayout', [PayPayout::class, 'test']);
Route::get('/test/paypayout/debug/{trn}', [PayPayout::class, 'debugPayout']);
Route::get('/test/paypayout/callback/{requestid}', [PayPayout::class, 'testCallback']);

// PayU Routes (Payin)
Route::post('/gateway/payu/payin/{trn}', [PayU::class, 'initiatePayin']);
Route::any('/gateway/payu/callback', [PayU::class, 'callback']);
Route::get('/gateway/payu/test-token', [PayU::class, 'testToken']);

// PayU Routes (Payout)
Route::post('/gateway/payu/payout', [PayU::class, 'initiatePayout']);
Route::any('/gateway/payu/payout-callback', [PayU::class, 'payoutCallback']);

// UnitPayGo Routes (Payin)
Route::post('/gateway/unitpaygo/payin', [UnitPayGo::class, 'initiatePayin']);
Route::get('/gateway/unitpaygo/pay/{trn}', [UnitPayGo::class, 'showPaymentPage']);
Route::any('/gateway/unitpaygo/callback', [UnitPayGo::class, 'callback']);
Route::post('/check-payment-status', [UnitPayGo::class, 'checkPaymentStatus']);
// Admin: reconcile pending UnitPayGo payouts
Route::post('/admin/payout_recon', [UnitPayGo::class, 'reconcilePayouts']);

// UnitPayGo Routes (Payout)
Route::post('/gateway/unitpaygo/payout', [UnitPayGo::class, 'initiatePayout']);

// Solitpay Routes
Route::post('/gateway/solitpay/payin/{trn}', [\App\Http\Controllers\Gateway\Solitpay::class, 'initiatePayin']);
Route::post('/gateway/solitpay/payout', [\App\Http\Controllers\Gateway\Solitpay::class, 'initiatePayout']);
Route::any('/gateway/solitpay/webhook', [\App\Http\Controllers\Gateway\Solitpay::class, 'webhook']);
Route::post('/gateway/solitpay/check-status', [\App\Http\Controllers\Gateway\Solitpay::class, 'checkStatus']);

// NSO Routes (PayIn only)
Route::post('/gateway/nso/payin/{trn}', [NSO::class, 'initiatePayin']);
Route::post('/gateway/nso/callback', [NSO::class, 'callback']);
Route::post('/gateway/nso/check-payin-status', [NSO::class, 'checkPayinStatus']);
Route::get('/gateway/nso/redirect/{transactionId}', [NSO::class, 'showRedirectPage']);

// Spay Routes
Route::post('/gateway/spay/payin', [Spay::class, 'initiatePayin']);
Route::post('/gateway/spay/callback', [Spay::class, 'callback']);
Route::post('/gateway/spay/payout', [Spay::class, 'initiatePayout']);
Route::post('/gateway/spay/check-status', [Spay::class, 'checkStatus']);

// Easebuzz Routes (PayIn)
Route::post('/gateway/easebuzz/payin/{trn}', [Easebuzz::class, 'initiatePayin']);
Route::post('/gateway/easebuzz/callback', [Easebuzz::class, 'handleCallback']);
Route::post('/gateway/easebuzz/webhook', [Easebuzz::class, 'handleWebhook']);
Route::post('/gateway/easebuzz/status', [Easebuzz::class, 'checkStatus']);
Route::post('/gateway/easebuzz/refund', [Easebuzz::class, 'initiateRefund']);
Route::post('/gateway/easebuzz/access-key', [Easebuzz::class, 'generateAccessKey']);
Route::get('/gateway/easebuzz/return', [Easebuzz::class, 'handleCallback']);

// Easebuzz Card Routes (Support GET for 3D Secure redirects & POST for callbacks)
Route::any('/gateway/easebuzz/card/success', [Easebuzz::class, 'handleCardSuccess']);
Route::any('/gateway/easebuzz/card/failure', [Easebuzz::class, 'handleCardFailure']);
Route::any('/gateway/easebuzz/card/webhook', [Easebuzz::class, 'handleCardSuccess']);

// AuroPay Routes (PayIn with Payment Link + QR Code)
Route::post('/gateway/auropay/payin/{trn}', [\App\Http\Controllers\Gateway\AuroPay::class, 'initiatePayin']);
Route::any('/gateway/auropay/callback', [\App\Http\Controllers\Gateway\AuroPay::class, 'handleCallback']); // Supports GET & POST
Route::any('/gateway/auropay/webhook', [\App\Http\Controllers\Gateway\AuroPay::class, 'handleCallback']);