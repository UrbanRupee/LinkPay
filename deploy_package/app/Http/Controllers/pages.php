<?php
namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Packages;
use App\Models\Products;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\Admin_Wallet;
use App\Models\Wallet;
use App\Models\Investment;
use App\Models\Order;
use App\Models\Logs;
use App\Models\Donations;
use App\Models\User_request;
use App\Models\Payment_request;
use App\Models\PayoutRequest;
use App\Models\user;
use App\Models\club_user;
use App\Models\User_Wishlist;
use App\Models\User_Bank;
use Illuminate\Support\Facades\Cache;
use App\Models\LudoApiWhitelabel as LudoApiWhitelabels;
use App\Models\LudoApiHit;
use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Http\Controllers\Logics_building;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\Gateway\AuroPay as AuroPayGateway;

class pages extends Controller
{
    protected $LogicsApi;

    public function __construct(Logics_building $LogicsApi)
    {
        $this->LogicsApi = $LogicsApi;
    }
    public function testing()
    {
        // $path = public_path('report.csv');   // <-- same file but saved/exported as CSV
        // $rows = [];

        // if (($h = fopen($path, 'r')) !== false) {
        //     while (($data = fgetcsv($h, 0, ',')) !== false) {
        //         $rows[] = $data;             // each row is a plain PHP array
        //     }
        //     fclose($h);
        // }
        // $i = 0;
        // foreach ($rows as $r => $row){
        //     if($i>0){
        //         $utr= $row[0];
        //         $orderid = $row[2];
        //         $StatOfHere= $row[3];
        //         if($StatOfHere=="#N/A"){
        //             $payinData = Payment_request::where('status',0)->where('transaction_id',$orderid)->first();
        //             if($payinData){
        //                 $payinData->status = 1;
        //                 $payinData->data1 = $utr;
        //                 $payinData->data2 = $utr;
        //                 $payinData->save();
        //                 $finalamount = $payinData->amount - $payinData->tax;
        //                 addtransaction($payinData->userid, 'payin', 'credit', $finalamount, '', 1, $orderid);
        //                 addwallet($payinData->userid, $finalamount, '+', 'payin');
        //                 $callbackdata = array("status" => "success", "client_txn_id" => $payinData->transaction_id, "amount" => $payinData->amount, "utr" => $payinData->data1);
        //                 $callback = user('callback', $payinData->userid);
        //                 $this->LogicsApi->CallbacksendToClient($callback, json_encode($callbackdata));
        //                 echo "SUCCESS <br>";
        //             }else{
        //                 echo "<b style='color:red'>NOT FOUND</b><br>";
        //             }
        //         }
                
        //     }
        //     $i++;
        // }
        
        // return ;
        $alluser = user::where('status',1)->where('userid','!=','RXP10374')->get();
        $total =0;
        foreach($alluser as $r){
            $name = $r->name;
            $wallets = Wallet::where('userid',$r->userid)->first();
            $wallet = $wallets->payout??0;
            $walletHold = $wallets->hold??0;
            $TAddFund = Transaction::where('userid',$r->userid)->where('category','add_fund')->where('data3','payout')->sum('amount');
            // $TAddFundHold = Transaction::where('userid',$r->userid)->where('category','add_fund')->where('data2','payout')->where('data3','hold')->sum('amount');
            $TAddFundHold = $walletHold;
            $TPayout = PayoutRequest::where('userid', $r->userid) ->where('status','!=',2) ->selectRaw('SUM(amount + tax) as total')->value('total');
            $Fff = $TAddFund-$TAddFundHold-$TPayout;
            if($Fff<$wallet){
            // return $TPayout;
            $total +=$Fff;
                echo "Userid: ".$r->userid." Name: $name | Payout wallet: $wallet | TotalFundAdd: $TAddFund | MinusInHold: $TAddFundHold | TotalPayout: $TPayout || FinalByTrans.: $Fff"."<br/>";
            // $wallets->payout = $Fff;
            // $wallets->save();
            }
            
        }
        return "End".$total;
    }
    
    
    public function maintainPayinBalanace(){
        // Get all users, not just those with non-zero payin balances
        $allUsers = user::where('status', 1)->get();
        foreach($allUsers as $user){
            $userid = $user->userid;
            
            // Calculate payin balance from transactions
            $calculatedPayin = wallet($userid,'int','payin');
            
            // Get current wallet record
            $walletRecord = Wallet::where('userid', $userid)->first();
            if($walletRecord){
                $OLD = $walletRecord->payin;
                
                // Check if there are any settlements for this user
                $settlementAmount = DB::select("SELECT SUM(amount) as total_settlement FROM transactions WHERE userid = ? AND category = 'settlement' AND type = 'credit' AND status = 1", [$userid]);
                $settledAmount = $settlementAmount[0]->total_settlement ?? 0;
                
                // Calculate actual payin balance (calculated - settled)
                $actualPayinBalance = $calculatedPayin - $settledAmount;
                
                // Ensure balance doesn't go below 0
                $actualPayinBalance = max(0, $actualPayinBalance);
                
                $walletRecord->payin = $actualPayinBalance;
                $walletRecord->save();
                
                echo "<b>USERID:$userid</b> - OLD: $OLD | CALCULATED: $calculatedPayin | SETTLED: $settledAmount | NEW: $actualPayinBalance <br>";
            } else {
                // Create new wallet record if doesn't exist
                $newWallet = new Wallet();
                $newWallet->userid = $userid;
                $newWallet->payin = $calculatedPayin;
                $newWallet->save();
                echo "<b>USERID:$userid</b> - CREATED NEW WALLET | PAYIN: $calculatedPayin <br>";
            }
        }
    }
    
    public function cleanupOrphanedWallets(){
        // Remove wallet records for users that don't exist
        $orphanedWallets = DB::select('SELECT w.userid FROM wallets w LEFT JOIN users u ON w.userid = u.userid WHERE u.userid IS NULL');
        $deletedCount = 0;
        
        foreach($orphanedWallets as $orphan){
            $deleted = Wallet::where('userid', $orphan->userid)->delete();
            $deletedCount += $deleted;
            echo "<b>REMOVED:</b> Orphaned wallet for userid: $orphan->userid <br>";
        }
        
        echo "<b>CLEANUP COMPLETE:</b> Removed $deletedCount orphaned wallet record(s) <br>";
        return "Cleanup completed. Removed $deletedCount orphaned wallet record(s).";
    }
    
    // public function testing()
    // {
    //     $total =0;
    //     $alluser = user::get();
    //     $dtim = '2025-06-20 00:00:00';
    //     $tdddate = '2025-05-28 00:00:00';
    //     // return count($alluser);
    //     foreach($alluser as $r){
    //         $Amount = Payment_request::where('userid',$r->userid)->where('status',1)->where('created_at','>',$dtim)->where('created_at','<',$tdddate)->sum('amount');
    //         // return $Amount;
    //         $Tax = Payment_request::where('userid',$r->userid)->where('status',1)->where('created_at','>',$dtim)->where('created_at','<',$tdddate)->sum('tax');
    //         $Taxs = Transaction::where('userid',$r->userid)->where('category','add_fund')->where('created_at','>',$dtim)->sum('amount');
    //         $mm= $Amount-$Tax;
    //         $tt = $Amount-($Tax+$Taxs);
    //         if($tt!=0)
    //         echo "Total remaining for settlement:"."Userid: ".$r->userid."| ".$tt." | $Taxs | $mm<br>";
    //     }
    //     return "End".$total;
    // }
    public function sitemap() {
        $posts = array();
        array_push($posts,[
            "url" => '',
            "created_at" => '2023-02-02'
            ]);
        array_push($posts,[
            "url" => 'index',
            "created_at" => '2023-02-02'
            ]);
        array_push($posts,[
            "url" => 'login',
            "created_at" => '2023-02-02'
            ]);
        array_push($posts,[
            "url" => 'register',
            "created_at" => '2023-02-02'
            ]);
        array_push($posts,[
            "url" => 'about-us',
            "created_at" => '2023-02-02'
            ]);
        array_push($posts,[
            "url" => 'contact',
            "created_at" => '2023-02-02'
            ]);
        array_push($posts,[
            "url" => 'wishlist',
            "created_at" => '2023-02-02'
            ]);
        array_push($posts,[
            "url" => 'shop',
            "created_at" => '2023-02-02'
            ]);
        return response()->view('sitemap', [
            'data' => $posts
        ])->header('Content-Type', 'text/xml');
      }

    //frontend Pages---------------------------------------------------------------------------------
    public function index()
    {
        // $category = Category::orderBy('id', 'desc')->get();
        // $newarrival = Products::orderBy('id', 'desc')->limit(5)->get();
        // $products = Products::orderBy('id', 'desc')->limit(12)->get();
        return view('frontend.index');
    }
    public function refund_policy()
    {
        return view('frontend.refund_policy');
    }
    public function privacy_policy()
    {
        return view('frontend.privacy_policy');
    }
    public function cart()
    {
        return view('frontend.cart');
    }
    public function checkout($userid)
    {
        $exist = $UserData = user::where('userid',$userid)->first();
        if(!$exist){
            return "Access Denied!";
        }
        $token = $exist->token;
        $name = $exist->name;
        return view('frontend.checkout',compact('token','userid','name'));
    }
    public function shop()
    {
        $products = Products::orderBy('id', 'desc')->get();
        $category = Category::orderBy('id', 'desc')->get();
        return view('frontend.shop', compact('category', 'products'));
    }
    public function wishlist()
    {
        $data = User_Wishlist::where('userid', user('userid'))->get();
        return view('frontend.wishlist', compact('data'));
    }
    public function project_details()
    {
        return view('frontend.project_details');
    }
    public function news_details()
    {
        return view('frontend.news_details');
    }
    public function single_product($id)
    {
        $data = Products::where('id', $id)->first();
        $similar_products = Products::where('cid', $data->cid)->where('id', '!=', $data->id)->orderBy('id', 'desc')->limit(10)->get();
        if ($data) {
            return view('frontend.single-product', compact('data', 'similar_products'));
        }
        return redirect('/');
    }
    public function about_us()
    {
        return view('frontend.about_us');
    }
    public function blogs()
    {
        return view('frontend.blog');
    }
    public function pricing()
    {
        return view('frontend.pricing');
    }
    public function terms_policy()
    {
        return view('frontend.terms_policy');
    }
    public function shipping_policy()
    {
        return view('frontend.shipping_policy');
    }
    public function return_policy()
    {
        return view('frontend.return_policy');
    }
    public function services()
    {
        return view('frontend.services');
    }
    public function services_details()
    {
        return view('frontend.services_details');
    }
    public function contact()
    {
        return view('frontend.contact_us');
    }
    public function bank_detail()
    {
        return view('frontend.bank-detail-company');
    }
    public function forget_password()
    {
        return view('frontend.forget_password');
    }

    //Users Pages---------------------------------------------------------------------------------
    public function success_register()
    {

        if (session()->has('userid') && session()->get('userid') != null && session()->get('userid') != '') {
            $data = user::where('userid',session()->get('userid'))->first();
            if ($data) {
                return view('frontend.successregister',compact('data'));
            }
        }
        return redirect('/');
    }
    // public function user_dashboard()
    // {
    //     $Sdate = date('Y-m-d').' 00:00:00';
    //     $Tdate = date('Y-m-d').' 23:59:59';
    //     $lists = Payment_request::where('userid', user('userid'))->where('data3',1)->whereBetween('created_at', [$Sdate, $Tdate])->where('status',1);
    //     $tamount = $lists->get()->sum(function ($item) {
    //         return $item->amount - $item->tax;
    //     });
    //     $tpendingcount = Payment_request::where('userid', user('userid'))->where('data3',1)->whereBetween('created_at', [$Sdate, $Tdate])->where('status',0)->count();
    //     $tfailedcount = Payment_request::where('userid', user('userid'))->where('data3',1)->whereBetween('created_at', [$Sdate, $Tdate])->where('status',2)->count();
    //     $tsuccesscount = $lists->count();
    //     $userId = user('userid');
    //     // Fetch last 5 Payment Requests (PayIn)
    //     $payins = Payment_request::where('userid', $userId)
    //                             ->select(
    //                                 'transaction_id',
    //                                 'amount',
    //                                 'status',
    //                                 'created_at'
    //                             )
    //                             ->latest() // Order by created_at DESC
    //                             ->limit(5)
    //                             ->get()
    //                             ->map(function ($item) {
    //                                 $item->type = 'PayIn'; // Add type for merging
    //                                 $item->formatted_status = $this->formatStatus($item->status); // Format status for display
    //                                 return $item;
    //                             });

    //     // Fetch last 5 Payout Requests
    //     $payouts = PayoutRequest::where('userid', $userId)
    //                           ->select(
    //                               'transaction_id',
    //                               'amount',
    //                               'status',
    //                               'created_at'
    //                           )
    //                           ->latest() // Order by created_at DESC
    //                           ->limit(5)
    //                           ->get()
    //                           ->map(function ($item) {
    //                               $item->type = 'PayOut'; // Add type for merging
    //                               $item->formatted_status = $this->formatStatus($item->status); // Format status for display
    //                               return $item;
    //                           });

    //     // Merge the collections
    //     $mergedTransactions = $payins->merge($payouts);

    //     // Sort the merged collection by 'created_at' in descending order
    //     $recentTransactions = $mergedTransactions->sortByDesc('created_at')->take(5); // Take top 5 after sorting

        
    //     return view('user.dashboard', compact('tamount','recentTransactions','tpendingcount','tsuccesscount','tfailedcount'));
    // }
    // Your existing controller code
public function user_dashboard()
{
    $Sdate = date('Y-m-d').' 00:00:00';
    $Tdate = date('Y-m-d').' 23:59:59';
    
    // In settlement mode, show current wallet balance instead of today's raw transactions
    $currentUserId = user('userid');
    $userWallet = Wallet::where('userid', $currentUserId)->first();
    $tamount = $userWallet ? $userWallet->payin : 0;
    
    // Debug logging
    \Log::info("Dashboard Debug - User ID: {$currentUserId}, Wallet: " . json_encode($userWallet ? $userWallet->toArray() : 'Not found'));
    
    // Keep the original logic for counting today's transactions
    $lists = Payment_request::where('userid', user('userid'))->where('data3',1)->whereBetween('created_at', [$Sdate, $Tdate])->where('status',1);
    $tpendingcount = Payment_request::where('userid', user('userid'))->where('data3',1)->whereBetween('created_at', [$Sdate, $Tdate])->where('status',0)->count();
    $tfailedcount = Payment_request::where('userid', user('userid'))->where('data3',1)->whereBetween('created_at', [$Sdate, $Tdate])->where('status',2)->count();
    $tsuccesscount = $lists->count();
    $userId = user('userid');

    // Fetch last 10 Payment Requests (PayIn) for better analytics
    $payins = Payment_request::where('userid', $userId)
                                ->select(
                                    'transaction_id',
                                    'amount',
                                    'status',
                                    'created_at'
                                )
                                ->latest()
                                ->limit(10)
                                ->get()
                                ->map(function ($item) {
                                    $item->type = 'PayIn';
                                    $item->formatted_status = $this->formatStatus($item->status);
                                    return $item;
                                });

    // Fetch last 10 Payout Requests
    $payouts = PayoutRequest::where('userid', $userId)
                           ->select(
                               'transaction_id',
                               'amount',
                               'status',
                               'created_at'
                           )
                           ->latest()
                           ->limit(10)
                           ->get()
                           ->map(function ($item) {
                               $item->type = 'PayOut';
                               $item->formatted_status = $this->formatStatus($item->status);
                               return $item;
                           });

    // Merge the collections
    $mergedTransactions = $payins->merge($payouts);

    // Sort the merged collection by 'created_at' in descending order
    $recentTransactions = $mergedTransactions->sortByDesc('created_at')->take(10);

    // *************** ENHANCED DATA FOR CHARTS & ANALYTICS *******************

    // Data for Last 7 Days Transaction Trends
    $last7DaysData = [
        'dates' => [],
        'payinSuccess' => [],
        'payinFailed' => [],
        'payoutSuccess' => [],
        'payoutFailed' => []
    ];

    for ($i = 6; $i >= 0; $i--) {
        $date = Carbon::now()->subDays($i)->toDateString();
        $last7DaysData['dates'][] = Carbon::parse($date)->format('D, M d');
        
        $dayStart = $date . ' 00:00:00';
        $dayEnd = $date . ' 23:59:59';
        
        $last7DaysData['payinSuccess'][] = Payment_request::where('userid', $userId)
            ->where('status', 1)->whereBetween('created_at', [$dayStart, $dayEnd])->sum('amount');
        $last7DaysData['payinFailed'][] = Payment_request::where('userid', $userId)
            ->where('status', 2)->whereBetween('created_at', [$dayStart, $dayEnd])->sum('amount');
        $last7DaysData['payoutSuccess'][] = PayoutRequest::where('userid', $userId)
            ->where('status', 1)->whereBetween('created_at', [$dayStart, $dayEnd])->sum('amount');
        $last7DaysData['payoutFailed'][] = PayoutRequest::where('userid', $userId)
            ->where('status', 2)->whereBetween('created_at', [$dayStart, $dayEnd])->sum('amount');
    }

    // Data for Monthly Transaction Volume (Last 6 months)
    $monthlyVolumeData = [];
    $months = [];
    for ($i = 5; $i >= 0; $i--) {
        $month = Carbon::now()->subMonths($i);
        $startOfMonth = $month->copy()->startOfMonth()->toDateTimeString();
        $endOfMonth = $month->copy()->endOfMonth()->toDateTimeString();

        $payinVolume = Payment_request::where('userid', $userId)
                                ->where('status', 1)
                                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                                ->sum('amount');
        
        $payoutVolume = PayoutRequest::where('userid', $userId)
                                ->where('status', 1)
                                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                                ->sum('amount');

        $monthlyVolumeData[] = ['payin' => $payinVolume, 'payout' => $payoutVolume];
        $months[] = $month->format('M Y');
    }

    // Total lifetime statistics
    $totalPayinSuccess = Payment_request::where('userid', $userId)->where('status', 1)->sum('amount');
    $totalPayinFailed = Payment_request::where('userid', $userId)->where('status', 2)->sum('amount');
    $totalPayoutSuccess = PayoutRequest::where('userid', $userId)->where('status', 1)->sum('amount');
    $totalPayoutFailed = PayoutRequest::where('userid', $userId)->where('status', 2)->sum('amount');

    // Payin Analytics - Today, Yesterday, Weekly, Monthly
    $todayPayin = Payment_request::where('userid', $userId)
        ->where('status', 1)
        ->whereDate('created_at', Carbon::today())
        ->sum('amount');
    
    $yesterdayPayin = Payment_request::where('userid', $userId)
        ->where('status', 1)
        ->whereDate('created_at', Carbon::yesterday())
        ->sum('amount');
    
    $weeklyPayin = Payment_request::where('userid', $userId)
        ->where('status', 1)
        ->whereBetween('created_at', [Carbon::now()->subDays(6)->startOfDay(), Carbon::now()->endOfDay()])
        ->sum('amount');
    
    $monthlyPayin = Payment_request::where('userid', $userId)
        ->where('status', 1)
        ->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfDay()])
        ->sum('amount');

    // Get current wallet balances for display
    $currentPayinWallet = $userWallet ? $userWallet->payin : 0;
    $currentPayoutWallet = $userWallet ? $userWallet->payout : 0;
    $currentHoldWallet = $userWallet ? $userWallet->hold : 0;

    $response = response()->view('user.dashboard', compact(
        'tamount',
        'recentTransactions',
        'tpendingcount',
        'tsuccesscount',
        'tfailedcount',
        'monthlyVolumeData',
        'months',
        'currentPayinWallet',
        'currentPayoutWallet',
        'currentHoldWallet',
        'last7DaysData',
        'totalPayinSuccess',
        'totalPayinFailed',
        'totalPayoutSuccess',
        'totalPayoutFailed',
        'todayPayin',
        'yesterdayPayin',
        'weeklyPayin',
        'monthlyPayin'
    ));
    
    // Add cache-busting headers
    $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
    $response->headers->set('Pragma', 'no-cache');
    $response->headers->set('Expires', '0');
    
    return $response;
}

// Ensure you have this helper function
private function formatStatus($status) {
    switch ($status) {
        case 0: return 'Pending';
        case 1: return 'Completed';
        case 2: return 'Failed';
        default: return 'Unknown';
    }
}
    
    // private function formatStatus(int $status): string
    // {
    //     switch ($status) {
    //         case 1:
    //             return 'Completed';
    //         case 2:
    //             return 'Failed';
    //         case 0:
    //         default: // Treat any other value as pending
    //             return 'Pending';
    //     }
    // }
    public function user_edit_profile()
    {
        return view('user.edit_profile');
    }

    public function user_reset_password()
    {
        $title = "Reset Password";
        return view('user.reset_password', compact('title'));
    }
    public function user_change_password()
    {
        return view('user.change_password');
    }
    public function user_trans_password()
    {
        return view('user.trans_password');
    }
    public function user_add_fund()
    {
        return view('user.add_fund');
    }
    public function user_add_fund_history()
    {
        $title = "Fund history";
        // Show all add_fund transactions including:
        // 1. Hold amounts from settlement
        // 2. Admin amount transfers (add_fund credits)
        // 3. Admin deductions (admin_deduction debits)
        // 4. Other add_fund transactions
        $transaction = Transaction::where('userid', user('userid'))
            ->where(function($q) {
                $q->where('category', 'add_fund')
                  ->orWhere('category', 'admin_deduction'); // Include admin deductions
            })
            ->orderBy('id', 'desc')
            ->get();
        return view('user.add_fund_history', compact('transaction', 'title'));
    }
    public function user_activation()
    {
        $package = Packages::orderBy('id', 'ASC')->get();
        return view('user.activation', compact('package'));
    }
    public function e_pin_history()
    {
        $title = "E-Pin history";
        // Since orders table doesn't exist, use transactions as placeholder
        $transaction = Transaction::where('userid', user('userid'))->where('category', 'epin')->orderBy('id', 'desc')->get();
        return view('user.epin_history', compact('transaction', 'title'));
    }
    public function activation_history()
    {
        $title = "Activation history";
        $transaction = Transaction::where('userid', user('userid'))->where('category', 'activation')->orderBy('id', 'desc')->get();
        return view('user.activation_history', compact('transaction', 'title'));
    }
    public function refferal_team()
    {
        $title = "Direct Team";
        $list = user::where('sponserid', user('userid'))->where('status', '1')->orderBy('id', 'desc')->get();
        return view('user.direct_team', compact('list', 'title'));
    }
    
    public function extra_user()
    {
        $title = "Extra Users";
        $list = user::where('sponserid', user('userid'))->where('status', '1')->skip(4)->take(20)->orderBy('id', 'desc')->get();
        return view('user.extra_user', compact('list', 'title'));
    }
    public function level_view($level = null)
    {
        $title = "Level View";
        $list = user::where('sponserid', user('userid'))->where('status', '1')->get();
        $data = leveldetail(user('userid'));
        $ldata = array();
        if ($level != null && $level >= 0 && $level < 6) {
            $ldata = leveldetail(user('userid'))[$level - 1]['data'];
        }
        return view('user.level_view_list', compact('ldata', 'list', 'title', 'data', 'level'));
        
    }
    public function tree_view($userid=null){
        $ranks = leveldetail(user('userid'));
        // return $ranks;
        $title = "Tree hierarchy";
        $ldata = array();
        if($userid != null){
            $main = user::where('id',$userid)->first();
            $team = user::where('sponserid',$main->userid)->get();
        }else{
            $main = user::where('userid',user('userid'))->first();
            $team = user::where('sponserid',user('userid'))->get();
        }
        return view('user.treeview',compact('ranks','title','main','team'));
    }
    public function level_rank($level = null)
    {
        $title = "Rank View";
        $data = rankdetail(user('userid'));
        $ranks = array('SILVER','GOLD','PLATINUM','DIAMOND','CROWN');
        $ldata = array();
        if ($level != null && $level >= 0 && $level <= 12) {
            $ldata = rankdetail(user('userid'))[$level];
        }
        // return $ldata[0][0];
        return view('user.level_rank_view', compact('title', 'data','ldata','level','ranks'));
    }
    public function club_income_2()
    {
        $title = "Self Income";
        $list = Transaction::where('userid', user('userid'))->where('category', 'club_2')->orderBy('id', 'desc')->get();
        return view('user.club_income', compact('list', 'title'));
    }
    public function club_income_3()
    {
        $title = "Level Income";
        $list = Transaction::where('userid', user('userid'))->where('category', 'club_3')->orderBy('id', 'desc')->get();
        return view('user.club_income', compact('list', 'title'));
    }
    public function level_income($package=null)
    {
        $title = null;
        $list = array();
        $pack = array(1,2,3,4,5,6,7);
        if($package != null){
            $title = " Level ".$package." Income";
            $list = Transaction::where('userid', user('userid'))->where('category', 'level_income')->where('data3', $package)->orderBy('id', 'desc')->get();
        }
        return view('user.level_income', compact('list', 'title','package','pack'));
    }
    public function aadhar_report()
    {
        $title = "Aadhar Report";
        $list = Transaction::where('userid', user('userid'))->where('category', 'aadhar_uses')->orderBy('id', 'desc')->get();
        // $list = Investment::where('userid',user('userid'))->where('category','aadhar_uses')->orderBy('id', 'desc')->get();
        return view('user.sponser_income', compact('list', 'title'));
    }
    public function payout_report(Request $request)
    {
        $title = "PayOut Report";
        $userId = user('userid'); // Assuming user() helper is available and returns the authenticated user's ID

        // Check if user is authenticated
        if (!$userId) {
            return redirect('/')->with('error', 'Please login to view payout report');
        }

        // Start with the base query
        $query = PayoutRequest::where('userid', $userId);
                               // Removed ->where('byApi',1) based on your provided code, re-add if needed

        // Date Filtering
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');

        if ($from_date) {
            $query->whereDate('created_at', '>=', Carbon::parse($from_date)->startOfDay());
        }
        if ($to_date) {
            $query->whereDate('created_at', '<=', Carbon::parse($to_date)->endOfDay());
        }

        // Status Filtering
        $status_filter = $request->input('status_filter');
        if ($status_filter === 'success') {
            $query->where('status', 1); // Only successful payouts
        } elseif ($status_filter === 'failed') {
            $query->where('status', '!=', 1)->where('status', '!=', 0); // Only failed payouts
        }
        // If no status_filter or 'all', show all payouts

        // Search Filtering (for transaction_id or utr)
        $search = $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', '%' . $search . '%')
                  ->orWhere('utr', 'like', '%' . $search . '%') // Assuming 'utr' column for UTR No.
                  ->orWhere('holder_name', 'like', '%' . $search . '%') // Can search by holder name too
                  ->orWhere('account_no', 'like', '%' . $search . '%'); // Can search by account number
            });
        }

        // Order and Paginate the results
        $list = $query->orderBy('id', 'desc')->paginate(10780); // Changed paginate count to 100

        $response = response()->view('user.payout_income', compact('list', 'title'));
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');
        $response->headers->set('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');
        return $response;
    }
   public function payin_report(Request $request)
    {
        $title = "PayIn Report";
        $userId = user('userid'); // Assuming user() helper is available and returns the authenticated user's ID

        // Check if user is authenticated
        if (!$userId) {
            return redirect('/')->with('error', 'Please login to view payin report');
        }

        $period = $request->input('period', 'all');

        // Start with the base query - now includes ALL transactions (pending, success, failed)
        $query = Payment_request::where('userid', $userId)
                                ->where(function($q) {
                                    $q->where('data3', 1) // Regular PayIn transactions
                                      ->orWhere('data3', 'like', 'intent_%') // NSO intent IDs
                                      ->orWhere('data3', 'like', '%-%-%-%-%') // NSO UUIDs
                                      ->orWhere('data6', 29) // AuroPay gateway (flag 29)
                                      ->orWhere('data6', '29'); // AuroPay gateway (string)
                                });

        switch ($period) {
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'yesterday':
                $query->whereDate('created_at', Carbon::yesterday());
                break;
            case 'last7':
                $query->whereBetween('created_at', [Carbon::now()->subDays(6)->startOfDay(), Carbon::now()->endOfDay()]);
                break;
            case 'all':
            default:
                // no additional filter
                break;
        }

        // Status Filtering
        $status_filter = $request->input('status_filter');
        if ($status_filter === 'success') {
            $query->where('status', 1); // Only successful transactions
        } elseif ($status_filter === 'failed') {
            $query->where('status', '!=', 1)->where('status', '!=', 0); // Only failed transactions
        } elseif ($status_filter === 'pending') {
            $query->where('status', 0); // Only pending transactions
        }
        // If no status_filter or 'all', show all transactions (pending, success, failed)

        // Date Filtering
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');

        if ($from_date) {
            $query->whereDate('created_at', '>=', Carbon::parse($from_date)->startOfDay());
        }
        if ($to_date) {
            $query->whereDate('created_at', '<=', Carbon::parse($to_date)->endOfDay());
        }

        // Search Filtering (for transaction_id or data1 - UTR No.)
        $searchTerm = $request->input('search', $request->input('search_query'));
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('transaction_id', 'like', '%' . $searchTerm . '%')
                  ->orWhere('data1', 'like', '%' . $searchTerm . '%');
            });
        }

        $summaryStats = (clone $query)->selectRaw("
            SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as success_count,
            COALESCE(SUM(CASE WHEN status = 1 THEN amount ELSE 0 END), 0) as success_amount,
            SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as pending_count,
            COALESCE(SUM(CASE WHEN status = 0 THEN amount ELSE 0 END), 0) as pending_amount,
            SUM(CASE WHEN status NOT IN (0,1) THEN 1 ELSE 0 END) as failed_count,
            COALESCE(SUM(CASE WHEN status NOT IN (0,1) THEN amount ELSE 0 END), 0) as failed_amount
        ")->first();

        $statusSummary = [
            'success' => [
                'count' => (int) ($summaryStats->success_count ?? 0),
                'amount' => (float) ($summaryStats->success_amount ?? 0),
            ],
            'pending' => [
                'count' => (int) ($summaryStats->pending_count ?? 0),
                'amount' => (float) ($summaryStats->pending_amount ?? 0),
            ],
            'failed' => [
                'count' => (int) ($summaryStats->failed_count ?? 0),
                'amount' => (float) ($summaryStats->failed_amount ?? 0),
            ],
        ];

        // Order and Paginate the results - Show latest transactions first
        // Use a reasonable page size to avoid exhausting PHP memory on large datasets
        $list = $query->orderBy('created_at', 'desc')->paginate(100);

        $list->getCollection()->transform(function ($item) {
            return $this->appendGatewayMeta($item);
        });

        // Analytics Data for Charts - Use same base query as main query
        $analyticsQuery = Payment_request::where('userid', $userId)
            ->where(function($q) {
                $q->where('data3', 1)
                  ->orWhere('data3', 'like', 'intent_%')
                  ->orWhere('data3', 'like', '%-%-%-%-%')
                  ->orWhere('data6', 29)
                  ->orWhere('data6', '29');
            });

        // Apply same filters for analytics as main query
        switch ($period) {
            case 'today':
                $analyticsQuery->whereDate('created_at', Carbon::today());
                break;
            case 'yesterday':
                $analyticsQuery->whereDate('created_at', Carbon::yesterday());
                break;
            case 'last7':
                $analyticsQuery->whereBetween('created_at', [Carbon::now()->subDays(6)->startOfDay(), Carbon::now()->endOfDay()]);
                break;
        }

        if ($from_date) {
            $analyticsQuery->whereDate('created_at', '>=', Carbon::parse($from_date)->startOfDay());
        }
        if ($to_date) {
            $analyticsQuery->whereDate('created_at', '<=', Carbon::parse($to_date)->endOfDay());
        }

        // Daily trend data (last 30 days or selected period)
        $daysToShow = $period === 'last7' ? 7 : ($period === 'today' || $period === 'yesterday' ? 1 : 30);
        $startDate = $from_date ? Carbon::parse($from_date) : Carbon::now()->subDays($daysToShow - 1);
        $endDate = $to_date ? Carbon::parse($to_date) : Carbon::now();
        
        $dailyTrends = [];
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dayData = (clone $analyticsQuery)
                ->whereDate('created_at', $currentDate->format('Y-m-d'))
                ->selectRaw("
                    COUNT(*) as total_count,
                    SUM(CASE WHEN status = 1 THEN amount ELSE 0 END) as success_amount,
                    SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as success_count
                ")
                ->first();
            
            $dailyTrends[] = [
                'date' => $currentDate->format('M d'),
                'amount' => (float) ($dayData->success_amount ?? 0),
                'count' => (int) ($dayData->total_count ?? 0),
            ];
            $currentDate->addDay();
        }

        // Payment mode distribution
        $modeDistributionData = (clone $analyticsQuery)
            ->where('status', 1)
            ->get();
        
        // Transform each item to get gateway_meta
        $modeDistributionData->transform(function ($item) {
            return $this->appendGatewayMeta($item);
        });
        
        $modeDistribution = $modeDistributionData
            ->groupBy(function($item) {
                $meta = $item->gateway_meta ?? [];
                $mode = strtolower($meta['mode'] ?? '');
                $paymentDetails = $meta['payment_details'] ?? [];
                $type = strtolower($meta['type'] ?? '');
                
                // Check for UPI
                if (stripos($mode, 'upi') !== false || stripos($type, 'upi') !== false || 
                    isset($paymentDetails['upi_id']) || isset($paymentDetails['vpa'])) {
                    return 'UPI';
                }
                
                // Check for Card (Credit or Debit)
                if (stripos($mode, 'card') !== false || stripos($mode, 'credit') !== false || 
                    stripos($mode, 'debit') !== false || stripos($type, 'card') !== false ||
                    stripos($type, 'credit') !== false || stripos($type, 'debit') !== false ||
                    isset($paymentDetails['card_number']) || isset($paymentDetails['card_type'])) {
                    return 'Card';
                }
                
                // Check for Net Banking
                if (stripos($mode, 'netbanking') !== false || stripos($mode, 'nb') !== false ||
                    stripos($mode, 'net banking') !== false || stripos($type, 'netbanking') !== false ||
                    stripos($type, 'nb') !== false || stripos($type, 'net banking') !== false) {
                    return 'Net Banking';
                }
                
                // Default to Unknown if no match
                return 'Other';
            })
            ->map(function($group) {
                return [
                    'count' => $group->count(),
                    'amount' => $group->sum('amount'),
                ];
            })
            ->toArray();
        
        // Ensure UPI, Card, Net Banking are always present (even if 0)
        if (!isset($modeDistribution['UPI'])) {
            $modeDistribution['UPI'] = ['count' => 0, 'amount' => 0];
        }
        if (!isset($modeDistribution['Card'])) {
            $modeDistribution['Card'] = ['count' => 0, 'amount' => 0];
        }
        if (!isset($modeDistribution['Net Banking'])) {
            $modeDistribution['Net Banking'] = ['count' => 0, 'amount' => 0];
        }

        // Success rate calculation
        $totalTransactions = (clone $analyticsQuery)->count();
        $successTransactions = (clone $analyticsQuery)->where('status', 1)->count();
        $successRate = $totalTransactions > 0 ? round(($successTransactions / $totalTransactions) * 100, 2) : 0;

        // Average transaction amount
        $avgAmount = (clone $analyticsQuery)
            ->where('status', 1)
            ->selectRaw('AVG(amount) as avg_amount')
            ->first();
        $avgTransactionAmount = (float) ($avgAmount->avg_amount ?? 0);

        // Peak hours analysis (hourly distribution)
        $hourlyData = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $hourStats = (clone $analyticsQuery)
                ->whereRaw('HOUR(created_at) = ?', [$hour])
                ->where('status', 1)
                ->selectRaw('COUNT(*) as count, SUM(amount) as amount')
                ->first();
            $hourlyData[] = [
                'hour' => $hour,
                'count' => (int) ($hourStats->count ?? 0),
                'amount' => (float) ($hourStats->amount ?? 0),
            ];
        }

        // Period-based analytics
        $periodAnalytics = [
            'today' => [],
            'yesterday' => [],
            'weekly' => [],
            'monthly' => [],
        ];
        
        // Today
        $todayData = (clone $analyticsQuery)
            ->whereDate('created_at', Carbon::today())
            ->where('status', 1)
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count, SUM(amount) as amount')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
        $periodAnalytics['today'] = $todayData->map(function($item) {
            return [
                'hour' => (int) $item->hour,
                'count' => (int) $item->count,
                'amount' => (float) $item->amount,
            ];
        })->toArray();
        
        // Yesterday
        $yesterdayData = (clone $analyticsQuery)
            ->whereDate('created_at', Carbon::yesterday())
            ->where('status', 1)
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count, SUM(amount) as amount')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
        $periodAnalytics['yesterday'] = $yesterdayData->map(function($item) {
            return [
                'hour' => (int) $item->hour,
                'count' => (int) $item->count,
                'amount' => (float) $item->amount,
            ];
        })->toArray();
        
        // Weekly (last 7 days)
        $weeklyData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dayData = (clone $analyticsQuery)
                ->whereDate('created_at', $date->format('Y-m-d'))
                ->where('status', 1)
                ->selectRaw('COUNT(*) as count, SUM(amount) as amount')
                ->first();
            $weeklyData[] = [
                'date' => $date->format('M d'),
                'count' => (int) ($dayData->count ?? 0),
                'amount' => (float) ($dayData->amount ?? 0),
            ];
        }
        $periodAnalytics['weekly'] = $weeklyData;
        
        // Monthly (last 30 days)
        $monthlyData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dayData = (clone $analyticsQuery)
                ->whereDate('created_at', $date->format('Y-m-d'))
                ->where('status', 1)
                ->selectRaw('COUNT(*) as count, SUM(amount) as amount')
                ->first();
            $monthlyData[] = [
                'date' => $date->format('M d'),
                'count' => (int) ($dayData->count ?? 0),
                'amount' => (float) ($dayData->amount ?? 0),
            ];
        }
        $periodAnalytics['monthly'] = $monthlyData;

        $analytics = [
            'dailyTrends' => $dailyTrends,
            'modeDistribution' => $modeDistribution,
            'successRate' => $successRate,
            'avgTransactionAmount' => $avgTransactionAmount,
            'hourlyData' => $hourlyData,
            'totalTransactions' => $totalTransactions,
            'successTransactions' => $successTransactions,
            'periodAnalytics' => $periodAnalytics,
        ];

        // If you need the counts for dashboard cards, fetch them separately
        // $tpendingcount = Payment_request::where('userid', user('userid'))->where('data3',1)->whereBetween('created_at', [$Sdate, $Tdate])->where('status',0)->count();
        // $tpayout = PayoutRequest::where('userid', user('userid'))->get();

        // Add timestamp to prevent caching
        $timestamp = time();
        $response = response()->view('user.extra_income', compact('list', 'title', 'timestamp', 'period', 'statusSummary', 'analytics'));
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');
        $response->headers->set('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');
        return $response;
    }
    public function settlement_report(Request $request)
    {
        $title = "Settlement Report";
        $userId = user('userid');

        $baseQuery = Transaction::where('userid', $userId)
                             ->where('category', 'settlement');

        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');

        if ($from_date) {
            $baseQuery->whereDate('created_at', '>=', Carbon::parse($from_date)->format('Y-m-d'));
        }
        if ($to_date) {
            $baseQuery->whereDate('created_at', '<=', Carbon::parse($to_date)->format('Y-m-d'));
        }

        $search = $request->input('search');
        if ($search) {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('data2', 'like', '%' . $search . '%');
            });
        }

        // Overall totals (for header summary)
        $totalsRow = (clone $baseQuery)
            ->selectRaw('COALESCE(SUM(COALESCE(amount, 0)), 0) as net_total')
            ->selectRaw('COALESCE(SUM(COALESCE(data4, 0)), 0) as tax_total')
            ->selectRaw('COALESCE(SUM(COALESCE(amount, 0) + COALESCE(data4, 0)), 0) as gross_total')
            ->first();

        $totals = [
            'gross' => round((float) ($totalsRow->gross_total ?? 0), 2),
            'tax' => round((float) ($totalsRow->tax_total ?? 0), 2),
            'settled' => round((float) ($totalsRow->net_total ?? 0), 2),
        ];

        // Last settlement only (for header summary)
        $lastSettlement = (clone $baseQuery)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastSettlement) {
            $lastTax = (float) ($lastSettlement->data4 ?? 0);
            $lastHold = (float) ($lastSettlement->data5 ?? 0);
            $lastSettled = (float) ($lastSettlement->amount ?? 0);
            $lastTotals = [
                'settled' => round($lastSettled, 2),
                'tax' => round($lastTax, 2),
                'hold' => round($lastHold, 2),
            ];
        } else {
            $lastTotals = [
                'settled' => 0,
                'tax' => 0,
                'hold' => 0,
            ];
        }

        $list = (clone $baseQuery)
            ->orderBy('id', 'desc')
            ->paginate(100);

        // Get hold amounts from settlements
        $holdQuery = Transaction::where('userid', $userId)
                             ->where('category', 'settlement')
                             ->whereNotNull('data5')
                             ->where('data5', '>', 0);
        
        if ($from_date) {
            $holdQuery->whereDate('created_at', '>=', Carbon::parse($from_date)->startOfDay());
        }
        if ($to_date) {
            $holdQuery->whereDate('created_at', '<=', Carbon::parse($to_date)->endOfDay());
        }
        
        $holdList = $holdQuery->orderBy('id', 'desc')->get();
        $totalHold = $holdList->sum(function($item) {
            return (float) ($item->data5 ?? 0);
        });

        $filters = array_filter($request->only(['from_date', 'to_date', 'search']));
        $exportUrl = route('user.export.settlement_report', $filters);

        return view('user.settlement_report', compact('list', 'title', 'exportUrl', 'holdList', 'totalHold', 'totals', 'lastTotals'));
    }
    
    public function settlement_invoice($id)
    {
        $userId = user('userid');
        
        // Get the settlement transaction
        $settlement = Transaction::where('id', $id)
            ->where('userid', $userId)
            ->where('category', 'settlement')
            ->first();
            
        if (!$settlement) {
            abort(404, 'Settlement not found');
        }
        
        // Get user details
        $user = user::where('userid', $userId)->first();
        
        // Calculate amounts
        // amount = settled amount (net - hold), data3 = gross amount WITHOUT tax, data4 = tax, data5 = hold amount
        $settledAmount = (float) ($settlement->amount ?? 0); // Amount paid to payout (net - hold)
        $taxAmount = (float) ($settlement->data4 ?? 0);
        $holdAmount = (float) ($settlement->data5 ?? 0);
        $grossAmountWithoutTax = (float) ($settlement->data3 ?? 0);
        
        // Calculate gross amount correctly
        if ($grossAmountWithoutTax > 0) {
            // New format: data3 contains gross without tax
            $grossAmount = $grossAmountWithoutTax + $taxAmount; // Gross = gross without tax + tax
        } else {
            // Backward compatibility: calculate from settled + tax + hold
            $grossAmount = $settledAmount + $taxAmount + $holdAmount;
        }
        
        // Get company/site settings
        $siteName = Setting::where('name', 'app_name')->value('value') ?? 'Dhankubera';
        $siteEmail = Setting::where('name', 'app_email')->value('value') ?? '';
        $sitePhone = Setting::where('name', 'app_phone')->value('value') ?? '';
        $siteAddress = Setting::where('name', 'app_address')->value('value') ?? '';
        
        // Get bank details
        $bankDetails = \App\Models\User_Bank::where('userid', $userId)->first();
        $bankName = $bankDetails ? ($bankDetails->bank_name ?? '') : '';
        $accountNo = $bankDetails ? ($bankDetails->account_no ?? '') : '';
        $ifscCode = $bankDetails ? ($bankDetails->ifsc_code ?? '') : '';
        $accountHolderName = $user ? ($user->name ?? '') : '';
        
        return view('user.settlement_invoice', compact(
            'settlement',
            'user',
            'settledAmount',
            'taxAmount',
            'holdAmount',
            'grossAmount',
            'grossAmountWithoutTax',
            'siteName',
            'siteEmail',
            'sitePhone',
            'siteAddress',
            'bankName',
            'accountNo',
            'ifscCode',
            'accountHolderName'
        ));
    }
    
    public function payment_link_page()
    {
        $title = "Payment Link Generator";
        $userId = user('userid');
        
        return view('user.payment_link', compact('title', 'userId'));
    }
    
    public function generate_payment_link(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'mobile' => 'required|string|min:10|max:10',
            'name' => 'required|string|max:255',
            'orderid' => 'required|string|min:15|max:30|regex:/^[a-zA-Z0-9]+$/',
            'qr_required' => 'nullable|boolean',
        ]);
        
        $userId = user('userid');
        $user = user::where('userid', $userId)->first();
        
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ]);
        }
        
        // Get callback URL from user's callback field
        // This is the merchant's callback URL where they want to receive payment status updates
        $callbackUrl = $user->callback;
        
        if (empty($callbackUrl)) {
            return response()->json([
                'status' => false,
                'message' => 'Callback URL not configured. Please contact admin to set up your callback URL in your profile settings.'
            ]);
        }
        
        // Prepare API request data
        $apiData = [
            'token' => $user->token,
            'userid' => $userId,
            'amount' => (string) $request->amount,
            'mobile' => $request->mobile,
            'name' => $request->name,
            'orderid' => $request->orderid,
            'callback_url' => $callbackUrl,
        ];
        
        // Add qr_required if provided
        if ($request->has('qr_required') && $request->qr_required) {
            $apiData['qr_required'] = true;
        }
        
        // Call the API endpoint
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(30)
                ->post(url('/api/pg/urbanpay/initiate'), $apiData);
            
            $responseData = $response->json();
            
            return response()->json($responseData);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error generating payment link: ' . $e->getMessage()
            ]);
        }
    }
    
    public function retyerment_income()
    {
        $title = "Retyerment Income";
        $list = Transaction::where('userid', user('userid'))->where('category', 'retyerment_income')->orderBy('id', 'desc')->get();
        return view('user.extra_income', compact('list', 'title'));
    }
      // Export methods for reports
    public function export_payin_report(Request $request)
    {
        $userId = user('userid');
        $query = Payment_request::where('userid', $userId)
                                ->where(function($q) {
                                    $q->where('data3', 1)
                                      ->orWhere('data3', 'like', 'intent_%')
                                      ->orWhere('data3', 'like', '%-%-%-%-%')
                                      ->orWhere('data6', 29)
                                      ->orWhere('data6', '29');
                                });

        $period = $request->input('period', 'all');
        switch ($period) {
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'yesterday':
                $query->whereDate('created_at', Carbon::yesterday());
                break;
            case 'last7':
                $query->whereBetween('created_at', [Carbon::now()->subDays(6)->startOfDay(), Carbon::now()->endOfDay()]);
                break;
            case 'all':
            default:
                break;
        }

        $status_filter = $request->input('status_filter');
        if ($status_filter === 'success') {
            $query->where('status', 1);
        } elseif ($status_filter === 'failed') {
            $query->where('status', '!=', 1)->where('status', '!=', 0);
        } elseif ($status_filter === 'pending') {
            $query->where('status', 0);
        }

        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');
        if ($from_date) {
            $query->whereDate('created_at', '>=', Carbon::parse($from_date)->startOfDay());
        }
        if ($to_date) {
            $query->whereDate('created_at', '<=', Carbon::parse($to_date)->endOfDay());
        }

        $searchTerm = $request->input('search', $request->input('search_query'));
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('transaction_id', 'like', '%' . $searchTerm . '%')
                  ->orWhere('data1', 'like', '%' . $searchTerm . '%');
            });
        }

        $list = $query->orderBy('created_at', 'desc')->get()->map(function ($item) {
            return $this->appendGatewayMeta($item);
        });
        
        return $this->exportToExcel($list, 'PayIn Report', [
            'Transaction ID', 'Reference', 'Gateway Txn', 'Mode', 'Amount', 'Fees', 'Settled', 'UTR No.', 'Status', 'Date & Time'
        ], function ($item) {
            $meta = $item->gateway_meta ?? [];
            $status = $this->formatStatusLabel($item->status ?? null);
            
            // Clean and format values
            $transactionId = trim($item->transaction_id ?? '');
            $reference = trim($meta['reference'] ?? $item->transaction_id ?? '');
            $gatewayTxn = trim($meta['gateway_txn'] ?? '');
            $mode = trim($meta['mode'] ?? 'N/A');
            $utr = trim($meta['utr'] ?? $item->data4 ?? $item->data1 ?? '');
            
            // Format amounts - ensure proper decimal formatting
            $amount = number_format((float) ($item->amount ?? 0), 2, '.', '');
            $fees = number_format((float) ($meta['fees'] ?? $item->tax ?? 0), 2, '.', '');
            $settled = number_format((float) ($meta['settled'] ?? max(($item->amount ?? 0) - ($item->tax ?? 0), 0)), 2, '.', '');
            
            // Format date consistently
            $dateTime = '';
            if ($item->created_at) {
                try {
                    $dateTime = Carbon::parse($item->created_at)->format('d-m-Y H:i:s');
                } catch (\Exception $e) {
                    $dateTime = dformat($item->created_at, 'd-m-Y H:i:s');
                }
            }
            
            return [
                $transactionId,
                $reference,
                $gatewayTxn,
                $mode,
                $amount,
                $fees,
                $settled,
                $utr,
                $status,
                $dateTime,
            ];
        });
    }
    
    public function export_payout_report(Request $request)
    {
        $userId = user('userid');
        $query = PayoutRequest::where('userid', $userId);

        // Apply filters
        $status_filter = $request->input('status_filter');
        if ($status_filter === 'success') {
            $query->where('status', 1);
        } elseif ($status_filter === 'failed') {
            $query->where('status', '!=', 1)->where('status', '!=', 0);
        }

        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');
        if ($from_date) {
            $query->whereDate('created_at', '>=', Carbon::parse($from_date)->startOfDay());
        }
        if ($to_date) {
            $query->whereDate('created_at', '<=', Carbon::parse($to_date)->endOfDay());
        }

        $search = $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', '%' . $search . '%')
                  ->orWhere('utr', 'like', '%' . $search . '%')
                  ->orWhere('holder_name', 'like', '%' . $search . '%')
                  ->orWhere('account_no', 'like', '%' . $search . '%');
            });
        }

        $list = $query->orderBy('id', 'desc')->get();
        
        return $this->exportToExcel($list, 'Payout Report', [
            'Transaction ID', 'Amount', 'Tax', 'Settle Amount', 'UTR No.', 'Status', 'Date & Time'
        ]);
    }
    
    public function export_settlement_report(Request $request)
    {
        $userId = user('userid');
        $query = Transaction::where('userid', $userId)
                             ->where('category', 'settlement');

        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');
        if ($from_date) {
            $query->whereDate('created_at', '>=', Carbon::parse($from_date)->startOfDay());
        }
        if ($to_date) {
            $query->whereDate('created_at', '<=', Carbon::parse($to_date)->endOfDay());
        }

        $search = $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('data2', 'like', '%' . $search . '%');
            });
        }

        $list = $query->orderBy('id', 'desc')->get();
        
        // Fetch bank details once (all transactions belong to same user)
        $bankDetails = User_Bank::where('userid', $userId)->first();
        $bankName = $bankDetails ? ($bankDetails->bank_name ?? '') : '';
        $accountNo = $bankDetails ? ($bankDetails->account_no ?? '') : '';
        $ifscCode = $bankDetails ? ($bankDetails->ifsc_code ?? '') : '';
        
        return $this->exportToExcel($list, 'Settlement Report', [
            'Transaction ID', 'Total Amount', 'Service Charge', 'Settled Amount', 'UTR No.', 'Status', 'Date & Time', 'Bank Name', 'Account Number', 'IFSC Code'
        ], function ($item) use ($bankName, $accountNo, $ifscCode) {
            // amount = settled amount (net - hold), data3 = gross amount WITHOUT tax, data4 = tax, data5 = hold amount
            $settledAmount = (float) ($item->amount ?? 0);
            $taxAmount = (float) ($item->data4 ?? 0);
            $holdAmount = (float) ($item->data5 ?? 0);
            $grossAmountWithoutTax = (float) ($item->data3 ?? 0);
            
            // Calculate gross amount correctly
            if ($grossAmountWithoutTax > 0) {
                // New format: data3 contains gross without tax
                $grossAmount = $grossAmountWithoutTax + $taxAmount; // Gross = gross without tax + tax
            } else {
                // Backward compatibility: calculate from settled + tax + hold
                $grossAmount = $settledAmount + $taxAmount + $holdAmount;
            }

            return [
                $item->transaction_id ?? $item->id ?? '',
                number_format($grossAmount, 2, '.', ''),
                number_format($taxAmount, 2, '.', ''),
                number_format($settledAmount, 2, '.', ''),
                $item->data2 ?? '',
                $this->formatStatusLabel($item->status ?? null),
                $item->created_at ? dformat($item->created_at, 'd-m-Y H:i:s') : '',
                $bankName,
                $accountNo,
                $ifscCode,
            ];
        });
    }
    
    public function export_extra_income(Request $request)
    {
        $userId = user('userid');
        $query = Transaction::where('userid', $userId)
                             ->where('category', 'extra_income');

        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');
        if ($from_date) {
            $query->whereDate('created_at', '>=', Carbon::parse($from_date)->startOfDay());
        }
        if ($to_date) {
            $query->whereDate('created_at', '<=', Carbon::parse($to_date)->endOfDay());
        }

        $search = $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', '%' . $search . '%')
                  ->orWhere('data1', 'like', '%' . $search . '%');
            });
        }

        $list = $query->orderBy('id', 'desc')->get();
        
        return $this->exportToExcel($list, 'Payin Ledger', [
            'Transaction ID', 'Amount', 'Tax', 'Settle Amount', 'UTR No.', 'Status', 'Date & Time'
        ]);
    }
    
    private function exportToExcel($data, $filename, $headers, callable $rowBuilder = null)
    {
        $filename = Str::slug($filename, '_') . '_' . date('Y-m-d_H-i-s') . '.csv';
        
        return response()->streamDownload(function () use ($data, $headers, $rowBuilder) {
            $output = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel compatibility
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Write headers with proper formatting
            fputcsv($output, $headers, ',', '"');
            
            // Write data rows
            foreach ($data as $item) {
                $row = $rowBuilder ? $rowBuilder($item) : $this->buildDefaultExportRow($item);
                // Clean and format each cell value
                $cleanedRow = array_map(function($cell) {
                    // Remove any special characters that might break Excel
                    $cell = str_replace(["\r\n", "\r", "\n"], " ", $cell);
                    $cell = trim($cell);
                    // Ensure proper number formatting (remove any formatting issues)
                    if (is_numeric($cell)) {
                        return $cell;
                    }
                    return $cell;
                }, $row);
                fputcsv($output, $cleanedRow, ',', '"');
            }
            
            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Pragma' => 'public',
        ]);
    }

    private function buildDefaultExportRow($item): array
    {
        $amount = number_format((float) ($item->amount ?? 0), 2, '.', '');
        $tax = number_format((float) ($item->tax ?? 0), 2, '.', '');
        $settled = number_format(max((float) ($item->amount ?? 0) - (float) ($item->tax ?? 0), 0), 2, '.', '');
        $utr = $item->data1 ?? $item->data4 ?? $item->utr ?? '';
        $status = $this->formatStatusLabel($item->status ?? null);

        return [
            $item->transaction_id ?? $item->utr ?? ($item->id ?? ''),
            $amount,
            $tax,
            $settled,
            $utr,
            $status,
            $item->created_at ? dformat($item->created_at, 'd-m-Y H:i:s') : '',
        ];
    }

    private function formatStatusLabel($status): string
    {
        return match ((int) $status) {
            1 => 'Success',
            0 => 'Pending',
            default => 'Failed',
        };
    }
    public function main_wallet()
    {
        $title = "Main Wallet";
        return view('user.main_wallet', compact('title'));
    }
    public function p2p_transfer()
    {
        $title = "P2P Transfer";
        return view('user.p2p_transfer', compact('title'));
    }
    public function transfer_history()
    {
        $title = "Transfer History";
        $list = Transaction::where('userid', user('userid'))->where('category', 'p2ptransfer')->orderBy('id', 'desc')->get();
        return view('user.transfer_history', compact('title', 'list'));
    }
    public function withdrawal_request()
    {
        $title = "Withdraw Request";
        return view('user.withdrawal_request', compact('title'));
    }
    public function withdrawal_history()
    {
        $title = "Withdrawal History";
        $list = Transaction::where('userid', user('userid'))->where('category', 'withdrawal')->orderBy('id', 'desc')->get();
        return view('user.withdrawal_history', compact('title', 'list'));
    }
    public function donation_kanya_vivah()
    {
        $title = "Kanya Vivah Donation";
        $type = 'kanya';
        return view('user.donation', compact('title', 'type'));
    }
    public function donation_education()
    {
        $title = "Education Donation";
        $type = 'education';
        return view('user.donation', compact('title', 'type'));
    }
    public function donation_handicapped()
    {
        $title = "Handicapped Donation";
        $type = 'handicapped';
        return view('user.donation', compact('title', 'type'));
    }
    public function pgdocs()
    {
        $title = "API Documentation";
        return view('user.pgdocs', compact('title'));
    }
    public function chat_support()
    {
        $title = "Chat Support";
        return view('user.chat_support', compact('title'));
    }

    // REMOVED: Card transactions not needed
    // public function card_income()
    // {
    //     $title = "Card Income";
    //     return view('user.card_income', compact('title'));
    // }
    
    public function user_logout()
    {
        if (session()->has('userlogin')) {
            session()->forget('userlogin');
        }
        return redirect('/');
    }
    //Admin Pages----------------------------------------------------------------------------------
    public function admin_dashboard(Request $r)
    {
        $adminuserid = admin('userid');
        $successRatio = 0;
        if(admin('role') == 'admin'){
            $total_user = user::where('isadmin',null)->orderBy('id','desc')->count();
            $today_user = user::where('isadmin',null)->whereDate('created_at', Carbon::today())->count();
            $adminamount = Wallet::sum('wallet');
            
        }else{
            $adminamount = Wallet::where('userid',$adminuserid)->sum('wallet');
            $total_user = user::where('isadmin',null)->where('papa',$adminuserid)->orderBy('id','desc')->count();
            $today_user = user::where('isadmin',null)->whereDate('created_at', Carbon::today())->where('papa',$adminuserid)->count();
        }
        $padminamount = Wallet::sum('payin');
        $payoutadminamount = Wallet::sum('payout');
        $holdamount = Wallet::sum('hold');
        $mlmrevenue['todayrecharge'] = Transaction::where('category','add_fund')->whereDate('created_at', Carbon::today())->count();
        $mlmrevenue['todaypayin'] = Payment_request::where('status','1')->where('created_at','>=',date('Y-m-d').' 00:00:00')->sum('amount');
        
        $mlmrevenue['todaypayout'] = PayoutRequest::where('status','1')->where('created_at','>=',date('Y-m-d').' 00:00:00')->sum('amount');
        $mlmrevenue['todaypayouttax'] = PayoutRequest::where('status','1')->where('created_at','>=',date('Y-m-d').' 00:00:00')->sum('tax');
        
        $mlmrevenue['totalwithdrawal'] = Transaction::where('category','withdrawal')->count();
        $SummaryData = false;
        if(isset($r->from) && $r->from!="" && isset($r->to) && $r->to!=""){
            if(admin('role') == "agent"){
                $SummaryData = User::query()
                ->withLedgerStats($r->from, $r->to)   // <‑‑ supply the date range
                ->where('papa',admin('userid'))
                ->orderByDesc('userid')
                ->get();
            }else{
                $SummaryData = User::query()
                ->withLedgerStats($r->from, $r->to)   // <‑‑ supply the date range
                ->orderByDesc('userid')
                ->get();
            }
        }
        ///////////////////
        function ledgerChunk(string $table, Carbon $from, Carbon $to, bool $isPayout = false): array
{
    // for payouts we need amount + tax, otherwise just amount
    $money = $isPayout ? 'amount + tax' : 'amount';
    if(admin('role') == "agent"){
        $usid = admin('userid');
        return (array) DB::table("$table as t")                     //  ← alias "t" for clarity
    ->leftJoin('users as u', 'u.userid', '=', 't.userid')   //  users.userid = $table.userid
    ->where('u.papa', $usid)                                //  only rows owned by this agent
    ->whereBetween('t.created_at', [$from, $to])            //  date window
    ->selectRaw("
        SUM(CASE WHEN t.status = 0 THEN 1          END) AS pending_cnt,
        SUM(CASE WHEN t.status = 0 THEN $money      END) AS pending_sum,
        SUM(CASE WHEN t.status = 1 THEN 1          END) AS success_cnt,
        SUM(CASE WHEN t.status = 1 THEN $money      END) AS success_sum,
        SUM(CASE WHEN t.status = 2 THEN 1          END) AS failed_cnt,
        SUM(CASE WHEN t.status = 2 THEN $money      END) AS failed_sum
    ")
    ->first();
    }
    return (array) DB::table($table)
        ->whereBetween('created_at', [$from, $to])
        ->selectRaw("
            SUM(CASE WHEN status = 0 THEN 1 END)          AS pending_cnt,
            SUM(CASE WHEN status = 0 THEN $money END)      AS pending_sum,
            SUM(CASE WHEN status = 1 THEN 1 END)          AS success_cnt,
            SUM(CASE WHEN status = 1 THEN $money END)      AS success_sum,
            SUM(CASE WHEN status = 2 THEN 1 END)          AS failed_cnt,
            SUM(CASE WHEN status = 2 THEN $money END)      AS failed_sum
        ")->first();
        
    
}

/* ──────────────────────────────────────────────────────────────
 |  2. Build the five time windows
 *──────────────────────────────────────────────────────────────*/
$now = Carbon::now();

$windows = [
    'today'     => [ $now->copy()->startOfDay(),                          $now ],
    'yesterday' => [ $now->copy()->subDay()->startOfDay(),                $now->copy()->subDay()->endOfDay() ],
    'weekly'    => [ $now->copy()->subDays(6)->startOfDay(),              $now ],
    'monthly'   => [ $now->copy()->firstOfMonth()->startOfDay(),          $now ],
    'yearly'    => [ $now->copy()->firstOfYear()->startOfDay(),           $now ],
];

/* ──────────────────────────────────────────────────────────────
 |  3. Loop once per window, merge pay‑in + pay‑out stats
 *──────────────────────────────────────────────────────────────*/
$stats = [];
$i=0;
$TotalSuccessCount = 0;
$TotalPendingCount = 0;
$TotalFailedCount = 0;
foreach ($windows as $label => [$from, $to]) {
// return $label;
    $pin  = ledgerChunk('payment_requests', $from, $to);          // pay‑ins
    $pout = ledgerChunk('payout_requests',  $from, $to, true);    // pay‑outs (add tax)
    $stats[$label] = [
        // Pay‑ins
        'TotalPayinPending'   => $pin['pending_cnt']  ?? 0,
        'SumPayinPending'     => $pin['pending_sum']  ?? 0,
        'TotalPayinSuccess'   => $pin['success_cnt']  ?? 0,
        'SumPayinSuccess'     => $pin['success_sum']  ?? 0,
        'TotalPayinFailed'    => $pin['failed_cnt']   ?? 0,
        'SumPayinFailed'      => $pin['failed_sum']   ?? 0,

        // Pay‑outs
        'TotalPayoutPending'  => $pout['pending_cnt'] ?? 0,
        'SumPayoutPending'    => $pout['pending_sum'] ?? 0,
        'TotalPayoutSuccess'  => $pout['success_cnt'] ?? 0,
        'SumPayoutSuccess'    => $pout['success_sum'] ?? 0,
        'TotalPayoutFailed'   => $pout['failed_cnt']  ?? 0,
        'SumPayoutFailed'     => $pout['failed_sum']  ?? 0,
    ];
     
    if($label=="today"){
        $TotalSuccessCount = $pin['success_cnt'];
        $TotalPendingCount = $pin['pending_cnt'];
        $TotalFailedCount = $pin['failed_cnt'];
    }
}
$TotalTransactions = $TotalSuccessCount + $TotalPendingCount + $TotalFailedCount;
if ($TotalTransactions > 0) {
    $successRatio = ($TotalSuccessCount / $TotalTransactions) * 100;
} else {
    $successRatio = 0;
}
// return $stats;

        $row  = DB::selectOne("SHOW GLOBAL STATUS LIKE 'Queries'");
        $curr = (int) $row->Value;
        $now  = microtime(true);

        // ❷ Pull the previous sample (if any) from cache
        $prev      = Cache::get('qps_prev_total');
        $prevTime  = Cache::get('qps_prev_time');

        // ❸ Store the fresh values for the next request
        Cache::put('qps_prev_total', $curr, 60);   // keep for 1 min
        Cache::put('qps_prev_time',  $now,  60);

        // ❹ If this is the first hit → we can't compute Δ yet
        if ($prev === null || $prevTime === null) {
            $finalQPS= 0;
        }

        // ❺ Calculate QPS = ΔQueries ÷ Δseconds
        $elapsed = $now - $prevTime ?: 1;          // avoid division by zero
        $qps     = ($curr - $prev) / $elapsed;
        $finalQPS = round($qps, 2);
        return view('admin.dashboard',compact('finalQPS','successRatio','today_user','payoutadminamount','total_user','mlmrevenue','adminamount','padminamount','holdamount','SummaryData','stats'));
    }
    public function admin_category($id = null)
    {
        $category = Category::all();
        if ($id != null && $id != '') {
            $data = Category::where('id', $id)->first();
        } else {
            $data = null;
        }
        return view('admin.category', compact('category', 'data'));
    }
    public function admin_useradd()
    {
        $data = array(); 
        array_push($data,[
         "title" => "User Name",
         "name" => "username",
        ]);
        array_push($data,[
         "title" => "Mobile No.",
         "name" => "mobile",
        ]);
        array_push($data,[
         "title" => "Email Id",
         "name" => "email",
        ]);
        array_push($data,[
         "title" => "Password",
         "name" => "password",
        ]);
        array_push($data,[
         "title" => "Aadhar Number",
         "name" => "aadhar_no",
        ]);
        array_push($data,[
         "title" => "Pan Number",
         "name" => "pan_no",
        ]);
        array_push($data,[
         "title" => "Company Name",
         "name" => "company_name",
        ]);
        return view('admin.adduser',compact('data'));
    }
    public function admin_Ludouserlist($id = null)
    {
        $user =  DB::table('ludo_api_whitelabels')
    ->leftJoin('ludo_api_hits', 'ludo_api_hits.hitIP', '=', 'ludo_api_whitelabels.id')
    ->select('ludo_api_whitelabels.id', 'ludo_api_whitelabels.name','ludo_api_whitelabels.ip','ludo_api_whitelabels.valid_at','ludo_api_whitelabels.status','ludo_api_whitelabels.limits', DB::raw('COUNT(ludo_api_hits.id) as AllHitsToday'))  // Specify columns explicitly
    ->whereDate('ludo_api_hits.created_at', Carbon::today())  // Filters hits for today
    ->groupBy('ludo_api_whitelabels.id', 'ludo_api_whitelabels.name','ludo_api_whitelabels.ip','ludo_api_whitelabels.valid_at','ludo_api_whitelabels.status','ludo_api_whitelabels.limits')  // Group by all selected columns
    ->get();
        $TotalFirst = LudoApiHit::whereDate('created_at', Carbon::today())->where('table_name','1')->count();
        $TotalSecond = LudoApiHit::whereDate('created_at', Carbon::today())->where('table_name','2')->count();
        $TotalThird = LudoApiHit::whereDate('created_at', Carbon::today())->where('table_name','3')->count();
        $title = "All Ludo User";
        return view('admin.Ludouserlist', compact('user', 'title','TotalFirst','TotalSecond','TotalThird'));
    }
    public function admin_userlist($id = null)
    {
        if(admin('role') == 'agent'){
            $user = user::where('papa',admin('userid'))->get();
        }else{
            $user = user::with('wallet')->where('role','!=','admin')->get();
        }
        // return $user;
        $title = "All User";
        return view('admin.userlist', compact('user', 'title'));
    }
    public function admin_user_request()
    {
        $user = Transaction::where('category','add_fund')->where('data1','!=','by_admin')->orderBy('id','desc')->get();
        $title = "Recharge Request";
        return view('admin.userrequest', compact('user', 'title'));
    }
    public function admin_userprofile($userid)
    {
        if (isset($userid) && $userid != '' && $userid != null) {
            $title = "Users Profile";
            $user = user::where('userid',$userid)->first();
            $direct_team = user::where('sponserid',$userid)->get();
            return view('admin.user_profile', compact('user', 'title','direct_team'));
        }else{
            return redirect('/admin/dashboard');
        }
    }
    public function admin_payout_ledger(Request $request, $id=null)
    {
        $period = $request->get('period', 'today'); // Get time period filter
        
        if(admin('role') == "agent"){
            $agentId = admin('userid');
            if($id == null){
                $data = PayoutRequest::select('payout_requests.*')   // avoid "id" collisions
                ->leftJoin('users', 'users.userid', '=', 'payout_requests.userid')
                ->where('users.papa', $agentId)                     // only rows tied to this agent
                ->orderBy('payout_requests.id', 'desc')
                ->paginate(100);
            }else{
                $data = PayoutRequest::select('payout_requests.*')   // avoid "id" collisions
                ->leftJoin('users', 'users.userid', '=', 'payout_requests.userid')
                ->where('users.papa', $agentId)                     // only rows tied to this agent
                ->where('payout_requests.userid',$id)
                ->orderBy('payout_requests.id', 'desc')
                ->paginate(100);
            }
            // $data = PayoutRequest::where('userid',admin('userid'))->orderBy('id', 'desc')->paginate(15);
            $title = "Ledger";
        }else{
            if($id == null){
                // $data = Payment_request::orderBy('id', 'desc')->where('status',1)->paginate(15);
                $data = PayoutRequest::orderBy('id', 'desc')->paginate(15);
            }else{
                $data = PayoutRequest::where('userid',$id)->orderBy('id', 'desc')->paginate(15);
            }
            $title = "User Ledger";
        }

        // Analytics data with time period filter
        $analytics = $this->getPayoutLedgerAnalytics($id, $period);
        
        $response = response()->view('admin.PayoutLedger', compact('data', 'title', 'analytics', 'period'));
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');
        $response->headers->set('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');
        return $response;
    }
    
    public function admin_user_ledger(Request $request,$id=null)
    {
        try {
            return $this->admin_user_ledger_inner($request, $id);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('admin_user_ledger: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->view('admin.ledger_error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }

    private function admin_user_ledger_inner(Request $request, $id=null)
    {
        $period = $request->get('period', 'today'); // Get time period filter
        
        // Build base query with join to users table for name filtering
        $query = Payment_request::select('payment_requests.*')
            ->leftJoin('users', 'users.userid', '=', 'payment_requests.userid');
        
        // Agent role filter
        if(admin('role') == "agent"){
            $agentId = admin('userid');
            $query->where('users.papa', $agentId);
        }
        
        // User ID filter (from URL parameter or request)
        $filterUserId = $id ?? $request->input('userid');
        if($filterUserId){
            $query->where('payment_requests.userid', $filterUserId);
        }
        
        // Status filter
        $statusFilter = $request->input('status_filter');
        if($statusFilter !== null && $statusFilter !== ''){
            if($statusFilter == 'success'){
                $query->where('payment_requests.status', 1);
            } elseif($statusFilter == 'pending'){
                $query->where('payment_requests.status', 0);
            } elseif($statusFilter == 'failed'){
                $query->where('payment_requests.status', '!=', 1)
                      ->where('payment_requests.status', '!=', 0);
            }
        }
        
        // User Name filter
        $nameFilter = $request->input('name_filter');
        if($nameFilter){
            $query->where(function($q) use ($nameFilter) {
                $q->where('users.name', 'like', '%' . $nameFilter . '%')
                  ->orWhere('users.userid', 'like', '%' . $nameFilter . '%');
            });
        }
        
        // Reference ID / Order ID / Gateway TXN filter
        $referenceFilter = $request->input('reference_filter');
        if($referenceFilter){
            $query->where(function($q) use ($referenceFilter) {
                $q->where('payment_requests.transaction_id', 'like', '%' . $referenceFilter . '%')
                  ->orWhere('payment_requests.data1', 'like', '%' . $referenceFilter . '%') // UTR
                  ->orWhere('payment_requests.data2', 'like', '%' . $referenceFilter . '%') // Gateway TXN
                  ->orWhere('payment_requests.data5', 'like', '%' . $referenceFilter . '%'); // Other reference
            });
        }
        
        // Date range filter
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        if($fromDate){
            $query->whereDate('payment_requests.created_at', '>=', Carbon::parse($fromDate)->startOfDay());
        }
        if($toDate){
            $query->whereDate('payment_requests.created_at', '<=', Carbon::parse($toDate)->endOfDay());
        }
        
        // Payment Gateway filter
        $gatewayFilter = $request->input('gateway_filter');
        if($gatewayFilter !== null && $gatewayFilter !== ''){
            $query->where('payment_requests.data6', $gatewayFilter);
        }
        
        // Calculate summary totals based on filtered query (before pagination)
        // Build a separate query for summary without selecting individual columns
        $summaryQuery = Payment_request::leftJoin('users', 'users.userid', '=', 'payment_requests.userid');
        
        // Apply all the same filters to summary query
        if(admin('role') == "agent"){
            $agentId = admin('userid');
            $summaryQuery->where('users.papa', $agentId);
        }
        
        if($filterUserId){
            $summaryQuery->where('payment_requests.userid', $filterUserId);
        }
        
        if($statusFilter !== null && $statusFilter !== ''){
            if($statusFilter == 'success'){
                $summaryQuery->where('payment_requests.status', 1);
            } elseif($statusFilter == 'pending'){
                $summaryQuery->where('payment_requests.status', 0);
            } elseif($statusFilter == 'failed'){
                $summaryQuery->where('payment_requests.status', '!=', 1)
                      ->where('payment_requests.status', '!=', 0);
            }
        }
        
        if($nameFilter){
            $summaryQuery->where(function($q) use ($nameFilter) {
                $q->where('users.name', 'like', '%' . $nameFilter . '%')
                  ->orWhere('users.userid', 'like', '%' . $nameFilter . '%');
            });
        }
        
        if($referenceFilter){
            $summaryQuery->where(function($q) use ($referenceFilter) {
                $q->where('payment_requests.transaction_id', 'like', '%' . $referenceFilter . '%')
                  ->orWhere('payment_requests.data1', 'like', '%' . $referenceFilter . '%')
                  ->orWhere('payment_requests.data2', 'like', '%' . $referenceFilter . '%')
                  ->orWhere('payment_requests.data5', 'like', '%' . $referenceFilter . '%');
            });
        }
        
        if($fromDate){
            $summaryQuery->whereDate('payment_requests.created_at', '>=', Carbon::parse($fromDate)->startOfDay());
        }
        if($toDate){
            $summaryQuery->whereDate('payment_requests.created_at', '<=', Carbon::parse($toDate)->endOfDay());
        }
        
        if($gatewayFilter !== null && $gatewayFilter !== ''){
            $summaryQuery->where('payment_requests.data6', $gatewayFilter);
        }
        
        $summaryTotals = $summaryQuery->selectRaw("
            SUM(CASE WHEN payment_requests.status = 1 THEN payment_requests.amount ELSE 0 END) as success_amount,
            SUM(CASE WHEN payment_requests.status = 0 THEN payment_requests.amount ELSE 0 END) as pending_amount,
            SUM(CASE WHEN payment_requests.status != 1 AND payment_requests.status != 0 THEN payment_requests.amount ELSE 0 END) as failed_amount,
            COUNT(CASE WHEN payment_requests.status = 1 THEN 1 END) as success_count,
            COUNT(CASE WHEN payment_requests.status = 0 THEN 1 END) as pending_count,
            COUNT(CASE WHEN payment_requests.status != 1 AND payment_requests.status != 0 THEN 1 END) as failed_count
        ")->first();
        
        // Get payment requests
        $perPage = (admin('role') == "agent") ? 100 : 50;
        $paymentRequests = $query->orderBy('payment_requests.id', 'desc')->get();
        
        // Get admin transactions (admin_deduction and add_fund) from transactions table
        // Check if admin transactions should be included (default: yes)
        $includeAdminTransactions = $request->input('include_admin', '1') !== '0';
        
        $adminTransactions = collect([]);
        if ($includeAdminTransactions) {
            $transactionsQuery = DB::table('transactions')
                ->select('transactions.*')
                ->leftJoin('users', 'users.userid', '=', 'transactions.userid')
                ->whereIn('transactions.category', ['admin_deduction', 'add_fund'])
                ->where('transactions.status', 1); // Only successful admin transactions
            
            // Apply same filters to transactions
            if(admin('role') == "agent"){
                $agentId = admin('userid');
                $transactionsQuery->where('users.papa', $agentId);
            }
            
            if($filterUserId){
                $transactionsQuery->where('transactions.userid', $filterUserId);
            }
            
            if($nameFilter){
                $transactionsQuery->where(function($q) use ($nameFilter) {
                    $q->where('users.name', 'like', '%' . $nameFilter . '%')
                      ->orWhere('users.userid', 'like', '%' . $nameFilter . '%');
                });
            }
            
            if($referenceFilter){
                $transactionsQuery->where(function($q) use ($referenceFilter) {
                    $q->where('transactions.data1', 'like', '%' . $referenceFilter . '%')
                      ->orWhere('transactions.id', 'like', '%' . $referenceFilter . '%');
                });
            }
            
            if($fromDate){
                $transactionsQuery->whereDate('transactions.created_at', '>=', Carbon::parse($fromDate)->startOfDay());
            }
            if($toDate){
                $transactionsQuery->whereDate('transactions.created_at', '<=', Carbon::parse($toDate)->endOfDay());
            }
            
            $adminTransactions = $transactionsQuery->orderBy('transactions.id', 'desc')->get();
        }
        
        // Convert transactions to Payment_request-like objects for compatibility
        $convertedTransactions = $adminTransactions->map(function($txn) {
            // Create a Payment_request model instance and populate it
            $pr = new Payment_request();
            $pr->id = 999999999 + $txn->id; // Use high ID to distinguish from payment_requests
            $pr->userid = $txn->userid;
            $pr->transaction_id = 'ADMIN_' . $txn->id;
            $pr->amount = (float) $txn->amount;
            $pr->tax = 0;
            $pr->status = $txn->status;
            $pr->data1 = $txn->data1 ?? ''; // Description
            $pr->data2 = $txn->data2 ?? ''; // Wallet type
            $pr->data3 = $txn->data3 ?? '';
            $pr->data4 = $txn->data4 ?? '';
            $pr->data5 = $txn->data5 ?? '';
            $pr->data6 = null; // No gateway for admin transactions
            $pr->created_at = $txn->created_at;
            $pr->updated_at = $txn->created_at ?? $txn->created_at;
            $pr->callback_payload = null;
            $pr->is_admin_transaction = true; // Flag to identify admin transactions
            $pr->category = $txn->category;
            $pr->type = $txn->type;
            $pr->exists = true; // Mark as existing record
            return $pr;
        });
        
        // Merge payment requests and admin transactions
        $allTransactions = $paymentRequests->concat($convertedTransactions);
        
        // Sort by created_at descending
        $allTransactions = $allTransactions->sortByDesc(function($item) {
            return $item->created_at ? strtotime($item->created_at) : 0;
        })->values();
        
        // Manual pagination
        $currentPage = $request->get('page', 1);
        $currentPage = max(1, (int)$currentPage);
        $offset = ($currentPage - 1) * $perPage;
        $items = $allTransactions->slice($offset, $perPage)->all();
        
        // Create paginator manually
        $data = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $allTransactions->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        // Preserve filters in pagination links
        $data->appends($request->query());

        $title = "User Ledger";
        
        // Analytics data with time period filter (catch errors so page still loads)
        try {
            $analytics = $this->getUserLedgerAnalytics($filterUserId, $period);
        } catch (\Throwable $e) {
            \Log::error('User Ledger Analytics Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $analytics = [
                'overview' => ['total_transactions' => 0, 'successful_transactions' => 0, 'pending_transactions' => 0, 'failed_transactions' => 0, 'total_amount' => 0, 'successful_amount' => 0, 'total_tax' => 0, 'success_rate' => 0, 'avg_transaction_amount' => 0],
                'daily_stats' => [],
                'hourly_stats' => [],
                'top_users_by_count' => collect(),
                'top_users_by_amount' => collect(),
                'gateway_stats' => collect(),
                'status_distribution' => ['success' => 0, 'pending' => 0, 'failed' => 0],
                'recent_activity' => collect(),
            ];
        }
        
        $data->getCollection()->transform(function ($item) {
            try {
                if (!isset($item->is_admin_transaction) || !$item->is_admin_transaction) {
                    return $this->appendGatewayMeta($item);
                }
                $item->gateway_meta = [
                    'reference' => $item->transaction_id ?? '',
                    'gateway_txn' => $item->transaction_id ?? '',
                    'fees' => 0,
                    'settled' => $item->amount ?? 0,
                    'mode' => 'Admin ' . ucfirst((string) ($item->type ?? 'credit')),
                    'card_category' => null,
                    'payment_details' => [],
                    'utr' => null
                ];
                return $item;
            } catch (\Throwable $e) {
                \Log::warning('User Ledger transform: ' . $e->getMessage(), ['item_id' => $item->id ?? null]);
                $item->gateway_meta = ['reference' => '', 'gateway_txn' => '', 'fees' => 0, 'settled' => 0, 'mode' => 'Unknown', 'card_category' => null, 'payment_details' => [], 'utr' => null];
                return $item;
            }
        });
        
        // Get gateway list for filter dropdown
        $gateways = ALLgateway(1);
        
        return view('admin.ledger', compact('data', 'title', 'analytics', 'period', 'gateways', 'summaryTotals'));
    }
    
    /**
     * Get comprehensive analytics for user ledger
     */
    private function getUserLedgerAnalytics($userId = null, $period = 'today')
    {
        $baseQuery = Payment_request::query();
        
        // Apply user filter if specified
        if ($userId) {
            $baseQuery->where('userid', $userId);
        }
        
        // Apply agent filter if needed
        if (admin('role') == "agent") {
            $agentId = admin('userid');
            $baseQuery->leftJoin('users', 'users.userid', '=', 'payment_requests.userid')
                     ->where('users.papa', $agentId);
        }
        
        // Apply time period filter
        $this->applyTimePeriodFilter($baseQuery, $period);
        
        // Overall statistics
        $totalTransactions = $baseQuery->count();
        $successfulTransactions = (clone $baseQuery)->where('status', 1)->count();
        $pendingTransactions = (clone $baseQuery)->where('status', 0)->count();
        $failedTransactions = (clone $baseQuery)->where('status', '!=', 1)->where('status', '!=', 0)->count();
        
        // Amount statistics
        $totalAmount = $baseQuery->sum('amount');
        $successfulAmount = (clone $baseQuery)->where('status', 1)->sum('amount');
        $totalTax = $baseQuery->sum('tax');
        
        // Daily statistics for last 30 days
        $dailyStats = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dayQuery = clone $baseQuery;
            $dayStats = $dayQuery->whereDate('created_at', $date);
            
            $dailyStats[] = [
                'date' => $date,
                'transactions' => $dayStats->count(),
                'amount' => $dayStats->sum('amount'),
                'successful' => (clone $dayStats)->where('status', 1)->count(),
                'pending' => (clone $dayStats)->where('status', 0)->count(),
                'failed' => (clone $dayStats)->where('status', '!=', 1)->where('status', '!=', 0)->count()
            ];
        }
        
        // Hourly statistics for today
        $hourlyStats = [];
        for ($i = 0; $i < 24; $i++) {
            $hourQuery = clone $baseQuery;
            $hourStats = $hourQuery->whereDate('created_at', today())
                                  ->whereRaw('HOUR(created_at) = ?', [$i]);
            
            $hourlyStats[] = [
                'hour' => $i,
                'transactions' => $hourStats->count(),
                'amount' => $hourStats->sum('amount')
            ];
        }
        
        // Top users by transaction count
        $topUsersByCount = (clone $baseQuery)->selectRaw('userid, COUNT(*) as transaction_count, SUM(amount) as total_amount')
                                   ->groupBy('userid')
                                   ->orderBy('transaction_count', 'desc')
                                   ->limit(10)
                                   ->get();
        
        // Top users by amount
        $topUsersByAmount = (clone $baseQuery)->selectRaw('userid, SUM(amount) as total_amount, COUNT(*) as transaction_count')
                                    ->groupBy('userid')
                                    ->orderBy('total_amount', 'desc')
                                    ->limit(10)
                                    ->get();
        
        // Gateway statistics
        $gatewayStats = (clone $baseQuery)->selectRaw('data6 as gateway, COUNT(*) as count, SUM(amount) as amount')
                                ->whereNotNull('data6')
                                ->groupBy('data6')
                                ->orderBy('count', 'desc')
                                ->get();
        
        // Status distribution
        $statusDistribution = [
            'success' => $successfulTransactions,
            'pending' => $pendingTransactions,
            'failed' => $failedTransactions
        ];
        
        // Success rate calculation
        $successRate = $totalTransactions > 0 ? round(($successfulTransactions / $totalTransactions) * 100, 2) : 0;
        
        // Average transaction amount
        $avgTransactionAmount = $totalTransactions > 0 ? round($totalAmount / $totalTransactions, 2) : 0;
        
        // Recent activity (last 10 transactions)
        $recentActivity = (clone $baseQuery)->orderBy('created_at', 'desc')->limit(10)->get();
        
        return [
            'overview' => [
                'total_transactions' => $totalTransactions,
                'successful_transactions' => $successfulTransactions,
                'pending_transactions' => $pendingTransactions,
                'failed_transactions' => $failedTransactions,
                'total_amount' => $totalAmount,
                'successful_amount' => $successfulAmount,
                'total_tax' => $totalTax,
                'success_rate' => $successRate,
                'avg_transaction_amount' => $avgTransactionAmount
            ],
            'daily_stats' => $dailyStats,
            'hourly_stats' => $hourlyStats,
            'top_users_by_count' => $topUsersByCount,
            'top_users_by_amount' => $topUsersByAmount,
            'gateway_stats' => $gatewayStats,
            'status_distribution' => $statusDistribution,
            'recent_activity' => $recentActivity
        ];
    }
    
    /**
     * Get comprehensive analytics for payout ledger
     */
    private function getPayoutLedgerAnalytics($userId = null, $period = 'today')
    {
        $baseQuery = PayoutRequest::query();
        
        // Apply user filter if specified
        if ($userId) {
            $baseQuery->where('userid', $userId);
        }
        
        // Apply agent filter if needed
        if (admin('role') == "agent") {
            $agentId = admin('userid');
            $baseQuery->leftJoin('users', 'users.userid', '=', 'payout_requests.userid')
                     ->where('users.papa', $agentId);
        }
        
        // Apply time period filter
        $this->applyTimePeriodFilter($baseQuery, $period);
        
        // Overall statistics
        $totalPayouts = $baseQuery->count();
        $successfulPayouts = (clone $baseQuery)->where('status', 1)->count();
        $pendingPayouts = (clone $baseQuery)->where('status', 0)->count();
        $failedPayouts = (clone $baseQuery)->where('status', '!=', 1)->where('status', '!=', 0)->count();
        
        // Amount statistics
        $totalAmount = $baseQuery->sum('amount');
        $successfulAmount = (clone $baseQuery)->where('status', 1)->sum('amount');
        $totalTax = $baseQuery->sum('tax');
        
        // Daily statistics for last 30 days
        $dailyStats = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dayQuery = clone $baseQuery;
            $dayStats = $dayQuery->whereDate('created_at', $date);
            
            $dailyStats[] = [
                'date' => $date,
                'payouts' => $dayStats->count(),
                'amount' => $dayStats->sum('amount'),
                'successful' => (clone $dayStats)->where('status', 1)->count(),
                'pending' => (clone $dayStats)->where('status', 0)->count(),
                'failed' => (clone $dayStats)->where('status', '!=', 1)->where('status', '!=', 0)->count()
            ];
        }
        
        // Hourly statistics for today
        $hourlyStats = [];
        for ($i = 0; $i < 24; $i++) {
            $hourQuery = clone $baseQuery;
            $hourStats = $hourQuery->whereDate('created_at', today())
                                  ->whereRaw('HOUR(created_at) = ?', [$i]);
            
            $hourlyStats[] = [
                'hour' => $i,
                'payouts' => $hourStats->count(),
                'amount' => $hourStats->sum('amount')
            ];
        }
        
        // Top users by payout count
        $topUsersByCount = (clone $baseQuery)->selectRaw('userid, COUNT(*) as payout_count, SUM(amount) as total_amount')
                                   ->groupBy('userid')
                                   ->orderBy('payout_count', 'desc')
                                   ->limit(10)
                                   ->get();
        
        // Top users by amount
        $topUsersByAmount = (clone $baseQuery)->selectRaw('userid, SUM(amount) as total_amount, COUNT(*) as payout_count')
                                    ->groupBy('userid')
                                    ->orderBy('total_amount', 'desc')
                                    ->limit(10)
                                    ->get();
        
        // Gateway statistics (using mode field for payout gateways)
        $gatewayStats = (clone $baseQuery)->selectRaw('mode as gateway, COUNT(*) as count, SUM(amount) as amount')
                                ->whereNotNull('mode')
                                ->groupBy('mode')
                                ->orderBy('count', 'desc')
                                ->get();
        
        // Status distribution
        $statusDistribution = [
            'success' => $successfulPayouts,
            'pending' => $pendingPayouts,
            'failed' => $failedPayouts
        ];
        
        // Success rate calculation
        $successRate = $totalPayouts > 0 ? round(($successfulPayouts / $totalPayouts) * 100, 2) : 0;
        
        // Average payout amount
        $avgPayoutAmount = $totalPayouts > 0 ? round($totalAmount / $totalPayouts, 2) : 0;
        
        // Recent activity (last 10 payouts)
        $recentActivity = (clone $baseQuery)->orderBy('created_at', 'desc')->limit(10)->get();
        
        return [
            'overview' => [
                'total_payouts' => $totalPayouts,
                'successful_payouts' => $successfulPayouts,
                'pending_payouts' => $pendingPayouts,
                'failed_payouts' => $failedPayouts,
                'total_amount' => $totalAmount,
                'successful_amount' => $successfulAmount,
                'total_tax' => $totalTax,
                'success_rate' => $successRate,
                'avg_payout_amount' => $avgPayoutAmount
            ],
            'daily_stats' => $dailyStats,
            'hourly_stats' => $hourlyStats,
            'top_users_by_count' => $topUsersByCount,
            'top_users_by_amount' => $topUsersByAmount,
            'gateway_stats' => $gatewayStats,
            'status_distribution' => $statusDistribution,
            'recent_activity' => $recentActivity
        ];
    }
    
    /**
     * Apply time period filter to query
     */
    private function applyTimePeriodFilter($query, $period)
    {
        switch ($period) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case 'yesterday':
                $query->whereDate('created_at', today()->subDay());
                break;
            case 'weekly':
                $query->whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ]);
                break;
            case 'monthly':
                $query->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year);
                break;
            case 'yearly':
                $query->whereYear('created_at', now()->year);
                break;
            default:
                $query->whereDate('created_at', today());
        }
    }
    public function admin_user_edit(Request $request, $id)
    {
        $user = user::where('userid', $id)->first();
        
        if (!$user || !$id) {
            if ($request->isMethod('post')) {
                return response()->json([
                    'status' => 0,
                    'message' => 'User not found'
                ]);
            }
            return redirect('/admin/userlist');
        }

        // Handle POST request (API update)
        if ($request->isMethod('post')) {
            try {
                // Update basic user fields
                if ($request->has('name')) {
                    $user->name = $request->name;
                }
                if ($request->has('email')) {
                    $user->email = $request->email;
                }
                if ($request->has('mobile')) {
                    $user->mobile = $request->mobile;
                }
                if ($request->has('status')) {
                    $user->status = $request->status;
                }
                if ($request->has('role')) {
                    $user->role = $request->role;
                }
                
                // Update payin fields
                if ($request->has('percentage')) {
                    $user->percentage = $request->percentage;
                }
                if ($request->has('upi_percentage')) {
                    $user->upi_percentage = $request->upi_percentage;
                }
                if ($request->has('cc_percentage')) {
                    $user->cc_percentage = $request->cc_percentage;
                }
                if ($request->has('dc_percentage')) {
                    $user->dc_percentage = $request->dc_percentage;
                }
                if ($request->has('nb_percentage')) {
                    $user->nb_percentage = $request->nb_percentage;
                }
                // Credit Card type-specific percentages
                if ($request->has('cc_master_percentage')) {
                    $user->cc_master_percentage = $request->cc_master_percentage;
                }
                if ($request->has('cc_visa_percentage')) {
                    $user->cc_visa_percentage = $request->cc_visa_percentage;
                }
                if ($request->has('cc_rupay_percentage')) {
                    $user->cc_rupay_percentage = $request->cc_rupay_percentage;
                }
                if ($request->has('cc_maestro_percentage')) {
                    $user->cc_maestro_percentage = $request->cc_maestro_percentage;
                }
                if ($request->has('cc_amex_percentage')) {
                    $user->cc_amex_percentage = $request->cc_amex_percentage;
                }
                if ($request->has('cc_diners_percentage')) {
                    $user->cc_diners_percentage = $request->cc_diners_percentage;
                }
                if ($request->has('cc_others_percentage')) {
                    $user->cc_others_percentage = $request->cc_others_percentage;
                }
                // Debit Card type-specific percentages
                if ($request->has('dc_master_percentage')) {
                    $user->dc_master_percentage = $request->dc_master_percentage;
                }
                if ($request->has('dc_visa_percentage')) {
                    $user->dc_visa_percentage = $request->dc_visa_percentage;
                }
                if ($request->has('dc_rupay_percentage')) {
                    $user->dc_rupay_percentage = $request->dc_rupay_percentage;
                }
                if ($request->has('dc_maestro_percentage')) {
                    $user->dc_maestro_percentage = $request->dc_maestro_percentage;
                }
                if ($request->has('dc_amex_percentage')) {
                    $user->dc_amex_percentage = $request->dc_amex_percentage;
                }
                if ($request->has('dc_diners_percentage')) {
                    $user->dc_diners_percentage = $request->dc_diners_percentage;
                }
                if ($request->has('dc_others_percentage')) {
                    $user->dc_others_percentage = $request->dc_others_percentage;
                }
                if ($request->has('callback')) {
                    $user->callback = $request->callback;
                }
                if ($request->has('payin_success_redirect')) {
                    $user->payin_success_redirect = $request->payin_success_redirect;
                }
                if ($request->has('payingateway')) {
                    $user->payingateway = $request->payingateway;
                }
                
                // Update payout fields
                if ($request->has('out_percentage')) {
                    $user->out_percentage = $request->out_percentage;
                }
                if ($request->has('out_callback')) {
                    $user->out_callback = $request->out_callback;
                }
                if ($request->has('out_ip')) {
                    $user->out_ip = $request->out_ip;
                }
                if ($request->has('payoutgateway')) {
                    $user->payoutgateway = $request->payoutgateway;
                }
                
                // Update card fields
                if ($request->has('card_percentage')) {
                    $user->card_percentage = $request->card_percentage;
                }
                if ($request->has('card_fixed_fee')) {
                    $user->card_fixed_fee = $request->card_fixed_fee;
                }
                if ($request->has('card_callback')) {
                    $user->card_callback = $request->card_callback;
                }
                if ($request->has('cardgateway')) {
                    $user->cardgateway = $request->cardgateway;
                }
                // Note: card_processing_fee is calculated field, not stored in database
                if ($request->has('card_ip')) {
                    $user->card_ip = $request->card_ip;
                }
                if ($request->has('card_status')) {
                    $user->card_status = $request->card_status;
                }
                
                // Update KYC fields (only for admin)
                if (admin('role') == 'admin') {
                    if ($request->has('aadhar_card')) {
                        $user->aadhar_card = $request->aadhar_card;
                    }
                    if ($request->has('pan_card')) {
                        $user->pan_card = $request->pan_card;
                    }
                }
                
                $user->save();
                
                // Update bank details (always update if form fields are present)
                // Check if bank fields are in the request (even if empty)
                if ($request->has('bank_name') || $request->has('account_no') || $request->has('ifsc_code')) {
                    $bank = User_Bank::where('userid', $user->userid)->first();
                    
                    if ($bank) {
                        // Update existing bank record
                    if ($request->has('bank_name')) {
                            $bank->bank_name = $request->bank_name ?? '';
                    }
                    if ($request->has('account_no')) {
                            $bank->account_no = $request->account_no ?? '';
                    }
                    if ($request->has('ifsc_code')) {
                            $bank->ifsc_code = $request->ifsc_code ?? '';
                        }
                        $bank->save();
                    } else {
                        // Create new bank record
                        $bank = new User_Bank;
                        $bank->userid = $user->userid;
                        $bank->bank_name = $request->input('bank_name', '');
                        $bank->account_no = $request->input('account_no', '');
                        $bank->ifsc_code = $request->input('ifsc_code', '');
                        $bank->status = 1;
                        $bank->save();
                    }
                }
                
                return response()->json([
                    'status' => 1,
                    'message' => 'User updated successfully'
                ]);
                
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Failed to update user: ' . $e->getMessage()
                ]);
            }
        }

        // Handle GET request (show edit form)
            $title = "User edit";
            $role = admin('role');
            return view('admin.user_edit', compact('user', 'title','id','role'));
    }
    public function admin_logs()
    {
        $product = Logs::orderBy('id', 'desc')->paginate(100);
        $title = "Logs";
        return view('admin.all_logs', compact('product', 'title'));
    }
    public function admin_add_product()
    {
        $category = Category::orderBy('id', 'desc')->get();
        $title = "Add Product";
        return view('admin.add_product', compact('category', 'title'));
    }
    
    
//     public function admin_user_ledger(Request $request, $id = null)
// {
//     /* ---------- build ONE query that both paging + export can reuse ---------- */
//     $query = Payment_request::select('payment_requests.*');

//     if (admin('role') === 'agent') {
//         $query->leftJoin('users', 'users.userid', '=', 'payment_requests.userid')
//               ->where('users.papa', admin('userid'));
//     }

//     if ($id) {
//         $query->where('payment_requests.userid', $id);
//     }

//     /* ---------- export branch ---------- */
//     if ($fmt = $request->query('export')) {

//         $rows = $query->orderByDesc('payment_requests.id')->get();   // no paginate ‑ grab all

//         /* --- CSV --- */
//         if ($fmt === 'csv') {
//             $headers = [
//                 'Content-Type'        => 'text/csv',
//                 'Content-Disposition' => 'attachment; filename=user_ledger_'.now()->format('Ymd_His').'.csv',
//             ];

//             return new StreamedResponse(function () use ($rows) {
//                 $out = fopen('php://output', 'w');
//                 fputcsv($out, ['#','Userid','Username','TxnId','Amount','Status','Created at']); // headings

//                 foreach ($rows as $r) {
//                     fputcsv($out, [
//                         $r->id,
//                         $r->userid,
//                         userbyuserid($r->userid, 'name'),
//                         $r->transaction_id,
//                         balance($r->amount),
//                         $r->status == 1 ? 'Success' : ($r->status == 0 ? 'Pending' : 'Failed'),
//                         dformat($r->created_at, 'd-m-Y H:i:s'),
//                     ]);
//                 }
//                 fclose($out);
//             }, 200, $headers);
//         }

//         /* --- JSON --- */
//         if ($fmt === 'json') {
//             return response()->json($rows);
//         }

//         abort(404);
//     }

//     /* ---------- normal paginated branch ---------- */
//     $perPage = admin('role') === 'agent' ? 100 : 15;
//     $data    = $query->orderByDesc('payment_requests.id')->paginate($perPage);

//     return view('admin.ledger', [
//         'data'  => $data,
//         'title' => 'User Ledger',
//     ]);
// }
    public function admin_product_ledger($id=null)
    {
        $data = Transaction::orderBy('id', 'desc')->where('category','sell')->where('data2','!=','')->get();
        if($id != null){
            $data = Transaction::where('userid',$id)->orderBy('id', 'desc')->where('category','sell')->where('data2','!=','')->get();
        }
        $sell = true;
        $title = "Product Ledger";
        return view('admin.ledger', compact('data', 'title','sell'));
    }
public function admin_settlement(Request $request)
    {
        // Get default date range
       $lastDate = date('Y-m-d');
$todayDate = date('Y-m-d')." 00:00:00";
        $sub1todayDate = datealgebra($todayDate, "-", "1 days", "Y-m-d");
        
        // Check for custom date range from request
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');
        
        if ($from_date && $to_date) {
            $lastDate = $from_date;
            $sub1todayDate = $to_date;
            $todayDate = $to_date;
        } else {
            // Use last settlement date if available, otherwise use default range
        $lastTransaction = Setting::where('name','last_settlement')->first();
        if($lastTransaction){
            $lastDate = $lastTransaction->value;
                $todayDate = date('Y-m-d H:i:s');
                $sub1todayDate = $todayDate;
            } else {
                // Default to last 7 days if no settlement date is set
                $lastDate = date('Y-m-d H:i:s', strtotime('-7 days'));
                $todayDate = date('Y-m-d H:i:s');
                $sub1todayDate = $todayDate;
            }
        }
        
        // Build the query with proper date filtering
        // Settlement should show:
        // BTotal (Gross) = SUM of all successful transaction amounts (original amounts)
        // TaxTotal (Tax) = SUM of all taxes deducted
        // ATotal (Net) = Actual wallet balance available (already has settled amount deducted)
        // Exclude payment_requests that have already been settled for the same date range
        $data = DB::select("SELECT users.id, users.userid, users.name, 
            SUM(COALESCE(payment_requests.amount,0) - COALESCE(payment_requests.tax,0)) as ATotal,
            SUM(payment_requests.amount) as BTotal,
            SUM(payment_requests.tax) as TaxTotal
            FROM users 
            INNER JOIN wallets ON users.userid = wallets.userid 
            LEFT JOIN payment_requests ON users.userid = payment_requests.userid 
                AND payment_requests.status = 1 
                AND payment_requests.created_at >= ?
                AND payment_requests.created_at <= ?
                AND NOT EXISTS (
                    SELECT 1 FROM transactions 
                    WHERE transactions.userid = payment_requests.userid 
                    AND transactions.category = 'settlement'
                    AND transactions.data6 IS NOT NULL 
                    AND transactions.data7 IS NOT NULL
                    AND payment_requests.created_at >= transactions.data6
                    AND payment_requests.created_at <= transactions.data7
                )
            WHERE users.status = 1 AND wallets.payin > 0 
            GROUP BY users.id, users.userid, users.name, wallets.payin
            HAVING SUM(COALESCE(payment_requests.amount,0)) > 0
            ORDER BY wallets.payin DESC", [$lastDate, $todayDate]);
        
        // Calculate totals for summary
        $total1 = 0;
        $total2 = 0;
        $total3 = 0;
        
        foreach ($data as $item) {
            $total1 += $item->BTotal;
            $total2 += $item->TaxTotal;
            $total3 += $item->ATotal;
        }
        
        $title = "Settlements";
        return view('admin.settlement', compact('data', 'title', 'sub1todayDate', 'lastDate', 'todayDate', 'total1', 'total2', 'total3'));
    }
    public function admin_settlementlist(Request $request)
    {
        $query = Transaction::where('category','settlement');
        
        // Date filter - use whereDate for date-only comparison (timezone-safe)
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');
        
        if ($from_date) {
            $query->whereDate('created_at', '>=', Carbon::parse($from_date)->format('Y-m-d'));
        }
        if ($to_date) {
            $query->whereDate('created_at', '<=', Carbon::parse($to_date)->format('Y-m-d'));
        }
        
        // User ID filter
        $userid_filter = $request->input('userid_filter');
        if ($userid_filter) {
            $query->where('userid', 'like', '%' . $userid_filter . '%');
        }
        
        // UTR filter
        $utr_filter = $request->input('utr_filter');
        if ($utr_filter) {
            $query->where('data2', 'like', '%' . $utr_filter . '%');
        }
        
        // Get total count before filtering for debugging
        $totalCount = Transaction::where('category','settlement')->count();
        $todayStart = Carbon::today('Asia/Kolkata')->startOfDay();
        $todayEnd = Carbon::today('Asia/Kolkata')->endOfDay();
        $todayCount = Transaction::where('category','settlement')
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->count();
        
        // Get recent settlements for debugging (last 5)
        $recentSettlements = Transaction::where('category','settlement')
            ->orderBy('id','desc')
            ->limit(5)
            ->get(['id', 'userid', 'amount', 'data2', 'created_at', 'status'])
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'userid' => $item->userid,
                    'amount' => $item->amount,
                    'utr' => $item->data2,
                    'created_at' => $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : null,
                    'status' => $item->status
                ];
            });
        
        $data = $query->orderBy('id','desc')->get();
        $title = "All settlement";
        
        // Log for debugging if no data found
        if ($data->isEmpty() && !$from_date && !$to_date && !$userid_filter && !$utr_filter) {
            \Log::info('Settlement List: No settlements found', [
                'total_settlements' => $totalCount,
                'today_settlements' => $todayCount,
                'recent_settlements' => $recentSettlements->toArray()
            ]);
        }
        
        return view('admin.settlement_list', compact('data', 'title', 'from_date', 'to_date', 'userid_filter', 'utr_filter', 'totalCount', 'todayCount'));
    }
    
    public function admin_export_settlement_list(Request $request)
    {
        // Use the same query logic as admin_settlementlist
        $query = Transaction::where('category','settlement');
        
        // Date filter - use whereDate for date-only comparison (timezone-safe)
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');
        
        if ($from_date) {
            $query->whereDate('created_at', '>=', Carbon::parse($from_date)->format('Y-m-d'));
        }
        if ($to_date) {
            $query->whereDate('created_at', '<=', Carbon::parse($to_date)->format('Y-m-d'));
        }
        
        // User ID filter
        $userid_filter = $request->input('userid_filter');
        if ($userid_filter) {
            $query->where('userid', 'like', '%' . $userid_filter . '%');
        }
        
        // UTR filter
        $utr_filter = $request->input('utr_filter');
        if ($utr_filter) {
            $query->where('data2', 'like', '%' . $utr_filter . '%');
        }
        
        $data = $query->orderBy('id','desc')->get();
        
        $rowNumber = 0;
        return $this->exportToExcel($data, 'Settlement_List_Export_' . date('Y-m-d'), [
            '#',
            'User ID',
            'UTR No.',
            'Gross Amount',
            'Service Charge',
            'Paid Amount',
            'Hold Amount',
            'Description',
            'Created At',
            'Bank Name',
            'Account No',
            'IFSC Code'
        ], function ($item) use (&$rowNumber) {
            $rowNumber++;
            
            // Calculate amounts same way as in the view
            $taxAmount = (float) ($item->data4 ?? 0);
            $holdAmount = (float) ($item->data5 ?? 0);
            $settledAmount = (float) ($item->amount ?? 0);
            
            $grossAmountWithoutTax = (float) ($item->data3 ?? 0);
            if ($grossAmountWithoutTax == 0) {
                $grossAmountWithoutTax = $settledAmount + $taxAmount + $holdAmount;
            }
            
            $totalGross = $grossAmountWithoutTax + $taxAmount;
            
            // Get bank details
            $bankDetails = \App\Models\User_Bank::where('userid', $item->userid)->first();
            $bankName = $bankDetails ? ($bankDetails->bank_name ?? '') : '';
            $accountNo = $bankDetails ? ($bankDetails->account_no ?? '') : '';
            $ifscCode = $bankDetails ? ($bankDetails->ifsc_code ?? '') : '';
            
            return [
                $rowNumber,
                $item->userid ?? '',
                $item->data2 ?? '',
                number_format($totalGross, 2, '.', ''),
                number_format($taxAmount, 2, '.', ''),
                number_format($settledAmount, 2, '.', ''),
                number_format($holdAmount, 2, '.', ''),
                $item->data1 ?? '',
                $item->created_at ? $item->created_at->format('d-m-Y H:i:s') : '',
                $bankName,
                $accountNo,
                $ifscCode
            ];
        });
    }
    
    public function admin_hold_ledger()
    {
        // Get all hold transactions (from settlement with hold amount)
        $data = Transaction::where('category', 'settlement')
            ->whereNotNull('data5')
            ->where('data5', '>', 0)
            ->orderBy('id', 'desc')
            ->get();
        $title = "Hold Ledger";
        return view('admin.hold_ledger', compact('data', 'title'));
    }

    public function admin_export_settlements(Request $request)
    {
        // Get date range from request (same as admin_settlement)
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');
        
        if (!$from_date || !$to_date) {
            // Use last settlement date if available
            $lastTransaction = Setting::where('name','last_settlement')->first();
            if($lastTransaction){
                $from_date = $lastTransaction->value;
                $to_date = date('Y-m-d H:i:s');
            } else {
                // Default to last 7 days
                $from_date = date('Y-m-d H:i:s', strtotime('-7 days'));
                $to_date = date('Y-m-d H:i:s');
            }
        }
        
        // Get settlement data based on the same query as admin_settlement page
        // Exclude payment_requests that have already been settled for the same date range
        $data = DB::select("SELECT users.id, users.userid, users.name, 
            SUM(COALESCE(payment_requests.amount,0) - COALESCE(payment_requests.tax,0)) as ATotal,
            SUM(payment_requests.amount) as BTotal,
            SUM(payment_requests.tax) as TaxTotal
            FROM users 
            INNER JOIN wallets ON users.userid = wallets.userid 
            LEFT JOIN payment_requests ON users.userid = payment_requests.userid 
                AND payment_requests.status = 1 
                AND payment_requests.created_at >= ?
                AND payment_requests.created_at <= ?
                AND NOT EXISTS (
                    SELECT 1 FROM transactions 
                    WHERE transactions.userid = payment_requests.userid 
                    AND transactions.category = 'settlement'
                    AND transactions.data6 IS NOT NULL 
                    AND transactions.data7 IS NOT NULL
                    AND payment_requests.created_at >= transactions.data6
                    AND payment_requests.created_at <= transactions.data7
                )
            WHERE users.status = 1 AND wallets.payin > 0 
            GROUP BY users.id, users.userid, users.name, wallets.payin
            HAVING SUM(COALESCE(payment_requests.amount,0)) > 0
            ORDER BY wallets.payin DESC", [$from_date, $to_date]);

        return $this->exportToExcel($data, 'Settlement_Export_' . date('Y-m-d'), [
            'User ID',
            'User Name',
            'Gross Amount',
            'Service Charge',
            'Net Amount',
            'Bank Name',
            'Account No',
            'IFSC Code',
        ], function ($item) {
            // Fetch bank details for this user
            $bankDetails = User_Bank::where('userid', $item->userid)->first();
            $bankName = $bankDetails ? ($bankDetails->bank_name ?? '') : '';
            $accountNo = $bankDetails ? ($bankDetails->account_no ?? '') : '';
            $ifscCode = $bankDetails ? ($bankDetails->ifsc_code ?? '') : '';

            // Clean formatting - ensure proper decimal places (no commas, just numbers with 2 decimals)
            $grossAmount = number_format((float) ($item->BTotal ?? 0), 2, '.', '');
            $serviceCharge = number_format((float) ($item->TaxTotal ?? 0), 2, '.', '');
            $netAmount = number_format((float) ($item->ATotal ?? 0), 2, '.', '');

            return [
                $item->userid ?? '',
                trim($item->name ?? ''),
                $grossAmount,
                $serviceCharge,
                $netAmount,
                trim($bankName),
                trim($accountNo),
                trim($ifscCode),
            ];
        });
    }
    public function admin_verify_transafer()
    {
        return view('admin.verify_transafer');
    }
    public function admin_mannual_payment()
    {
        $user = user::where('status', 1)->whereIn('role', ['franchise','user'])->orderBy('id', 'desc')->get();
        $title = "Amount Share";
        return view('admin.mannual_payout', compact('user', 'title'));
    }
    public function admin_amount_transfer()
    {
        $user = user::where('status', 1)->whereIn('role', ['franchise','user'])->orderBy('id', 'desc')->get();
        $title = "Amount Share";
        return view('admin.wallet_transfer', compact('user', 'title'));
    }
    public function admin_add_e_pin()
    {
        $user = user::where('status', 1)->where('isadmin', null)->orderBy('id', 'desc')->get();
        $title = "Add E-Pin";
        return view('admin.add_epin', compact('user', 'title'));
    }
    public function products_sell()
    {
        $user = user::where('status', 1)->where('isadmin', null)->orderBy('id', 'desc')->get();
        if(admin('role') == 'admin'){
            $product = Products::orderBy('id', 'desc')->get();
        }else{
            $product = Products::where('uid',admin('userid'))->orderBy('id', 'desc')->get();
        }
        $title = "Sell Product";
        return view('admin.sell_product', compact('user', 'title','product'));
    }
    public function admin_withdrawal_request()
    {
        $user = PayoutRequest::where('byApi',0)->orderBy('id', 'desc')->get();
        $title = "Withdrawal Requests";
        return view('admin.user_withdrawal', compact('user', 'title'));
    }
    public function admin_setting($id = null)
    {
        $setting = Setting::orderBy('id', 'desc')->where('status',1)->get();
        $title = "Settings";
        $data = null;
        if ($id != null && $id != '') {
            $data = Setting::where('id', $id)->orderBy('id', 'ASC')->first();
        } else {
            $data = null;
        }
        return view('admin.setting', compact('setting', 'title', 'data'));
    }
    public function admin_2_club()
    {
        $data = Transaction::where('category','add_fund')->orderBy('id', 'desc')->get();
        $title = "Amount Transfer History";
        return view('admin.club_user', compact('data', 'title'));
    }
    public function admin_3_club()
    {
        $data = Transaction::where('category','level_income')->where('amount','>',0)->orderBy('id', 'desc')->get();
        $title = "Level Income";
        return view('admin.club_user', compact('data', 'title'));
    }
    public function admin_club_report()
    {
        $data = Transaction::where('category','extra_income')->where('amount','>',0)->orderBy('id', 'desc')->get();
        $title = "NON WORKING TEAM INCOME";
        return view('admin.club_user', compact('data', 'title'));
    }
    public function admin_providers()
    {
        $data = \App\Models\Provider::orderBy('id','desc')->get();
        $title = "Providers";
        return view('admin.admin_providers', compact('data', 'title'));
    }
    
    /**
     * Get all providers as JSON
     */
    public function admin_providers_list()
    {
        try {
            $providers = \App\Models\Provider::orderBy('id', 'desc')->get();
            
            return response()->json([
                'status' => true,
                'providers' => $providers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error loading providers: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Add new provider
     */
    public function admin_provider_add(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'provider_name' => 'required|string|max:255',
                'gateway_id' => 'required',
                'gateway_type' => 'required|in:payin,payout,both',
                'api_key' => 'required|string',
                'api_secret' => 'required|string',
                'environment' => 'required|in:test,prod',
                'status' => 'required|in:0,1'
            ]);
            
            // Create provider
            $provider = new \App\Models\Provider();
            $provider->provider_name = $request->provider_name;
            $provider->gateway_id = $request->gateway_id;
            $provider->gateway_type = $request->gateway_type;
            $provider->api_key = $request->api_key;
            $provider->api_secret = $request->api_secret;
            $provider->merchant_id = $request->merchant_id;
            $provider->environment = $request->environment;
            $provider->callback_url = $request->callback_url;
            $provider->webhook_url = $request->webhook_url;
            $provider->status = $request->status;
            $provider->priority = $request->priority ?? 1;
            $provider->notes = $request->notes;
            $provider->save();
            
            return response()->json([
                'status' => true,
                'message' => 'Provider added successfully! You can add more providers.',
                'provider' => $provider
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error adding provider: ' . $e->getMessage()
            ], 500);
        }
    }
    public function admin_change_password()
    {
        $title = "Change Password";
        return view('admin.admin_change_password', compact('title'));
    }
    public function admin_login()
    {
        return view('admin.login');
    }
    
    public function admin_login_submit(Request $request)
    {
        $username = $request->username;
        $password = $request->password;
        
        // Simple admin authentication - you can modify this as needed
        if ($username == 'admin' && $password == 'admin123') {
            // Find admin user from database
            $adminUser = user::where('role', 'admin')->first();
            if ($adminUser) {
                session(['adminlogin' => $adminUser]);
            return redirect('/admin/dashboard');
            } else {
                // Create a default admin user if none exists
                $adminUser = new user();
                $adminUser->userid = 'ADMIN001';
                $adminUser->name = 'Admin';
                $adminUser->email = 'admin@xpaisa.in';
                $adminUser->phone = '9999999999';
                $adminUser->role = 'admin';
                $adminUser->status = 1;
                $adminUser->save();
                
                session(['adminlogin' => $adminUser]);
                return redirect('/admin/dashboard');
            }
        } else {
            return redirect('/admin')->with('error', 'Invalid credentials');
        }
    }
    
    public function admin_logout()
    {
        if (session()->has('adminlogin')) {
            session()->forget('adminlogin');
        }
        return redirect('/');
    }

    // REMOVED: Card transactions not needed
    /**
     * User Card Transactions View
     */
    // public function user_card_transactions()
    // {
    //     $userid = user('userid');
    //     if (!$userid) {
    //         return redirect('/login');
    //     }

    //     $cardTransactions = DB::table('card_transactions')
    //         ->where('userid', $userid)
    //         ->orderBy('id', 'desc')
    //         ->paginate(20);

    //     $title = "My Card Transactions";
    //     return view('user.card_income', compact('cardTransactions', 'title'));
    // }

    // REMOVED: Card transactions not needed
    /**
     * Admin Card Transactions Ledger
     */
    // public function admin_card_ledger()
    // {
    //     $cardTransactions = DB::table('card_transactions')
    //         ->orderBy('id', 'desc')
    //         ->paginate(50);

    //     $title = "Card Transaction Ledger";
    //     return view('admin.cardledger', compact('cardTransactions', 'title'));
    // }

    // REMOVED: Card transactions not needed
    /**
     * Admin Card Transactions for Specific User
     */
    // public function admin_user_card_ledger($userid)
    // {
    //     $cardTransactions = DB::table('card_transactions')
    //         ->where('userid', $userid)
    //         ->orderBy('id', 'desc')
    //         ->paginate(50);

    //     $title = "Card Transactions - " . userbyuserid($userid, 'name');
    //     return view('admin.cardledger', compact('cardTransactions', 'title'));
    // }

    public function admin_restart_system()
    {
        try {
            // Clear all Laravel caches
            \Artisan::call('cache:clear');
            \Artisan::call('config:clear');
            \Artisan::call('view:clear');
            \Artisan::call('route:clear');
            
            return response()->json([
                'status' => true,
                'message' => 'System restarted successfully! All caches cleared.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to restart system: ' . $e->getMessage()
            ]);
        }
    }

    public function admin_redirect_to_user_dashboard($userid)
    {
        // Find the user and log in as them
            $user = user::where('userid', $userid)->first();
        if ($user) {
            // Store admin session before switching
            $adminSession = session()->get('adminlogin');
            session(['adminlogin' => $adminSession, 'admin_as_user' => true]);
            
            // Log in as the user
            session(['userlogin' => $user]);
            
            return redirect('/dashboard')->with('success', 'Logged in as user: ' . $user->name);
        } else {
                return redirect('/admin/userlist')->with('error', 'User not found');
        }
    }

    // User Authentication Methods
    public function login()
    {
        if (session()->has('userlogin')) {
            return redirect('/dashboard');
        } elseif (session()->has('adminlogin')) {
            return redirect('/admin/dashboard');
        }
        return view('user.login');
    }

    public function register()
    {
        if (session()->has('userlogin')) {
            return redirect('/dashboard');
        } elseif (session()->has('adminlogin')) {
            return redirect('/admin/dashboard');
        }
        return view('user.register');
    }

    public function login_submit(Request $request)
    {
        $username = $request->username;
        $password = $request->password;
        
        $user = user::where('userid', $username)
                   ->orWhere('email', $username)
                   ->orWhere('mobile', $username)
                   ->first();
        
        if ($user && $user->status == 1) {
            if (Hash::check($password, $user->password)) {
                if ($user->isadmin) {
                    session(['adminlogin' => $user]);
                    return response()->json([
                        'status' => 1,
                        'message' => 'Login successful',
                        'redirect' => '/admin/dashboard'
                    ]);
                } else {
                    session(['userlogin' => $user]);
                    return response()->json([
                        'status' => 1,
                        'message' => 'Login successful',
                        'redirect' => '/dashboard'
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 0,
                    'message' => 'Invalid password'
                ]);
            }
        } else {
            return response()->json([
                'status' => 0,
                'message' => 'Invalid username or account blocked'
            ]);
        }
    }

    public function register_submit(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'mobile' => 'required|string|unique:users,mobile',
            'password' => 'required|string|min:6|confirmed',
            'sponsor_id' => 'required|exists:users,userid'
        ]);

        $userid = $this->makeuserid();
        
        $user = new user();
        $user->userid = $userid;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->mobile = $request->mobile;
        $user->password = Hash::make($request->password);
        $user->sponsor_id = $request->sponsor_id;
        $user->status = 1;
        $user->role = 'user';
        $user->save();

        // Create wallet for user
        $wallet = new Wallet();
        $wallet->userid = $userid;
        $wallet->payin = 0;
        $wallet->payout = 0;
        $wallet->aeps = 0;
        $wallet->total_balance = 0;
        $wallet->save();

        return redirect('/login')->with('success', 'Registration successful! Please login.');
    }

    public function logout()
    {
        session()->forget(['userlogin', 'adminlogin']);
        return redirect('/');
    }

    public function forgot()
    {
        return view('user.forgot_password');
    }

    public function forgot_submit(Request $request)
    {
        // Implement forgot password logic
        return redirect('/login')->with('success', 'Password reset instructions sent to your email.');
    }

    public function reset($token)
    {
        // Implement reset password view
        return view('user.reset_password', compact('token'));
    }

    public function reset_submit(Request $request)
    {
        // Implement reset password logic
        return redirect('/login')->with('success', 'Password reset successfully!');
    }

    public function verify($token)
    {
        // Implement email verification logic
        return redirect('/login')->with('success', 'Email verified successfully!');
    }

    private function makeuserid()
    {
        $randomuserid = 'UPT'.rand(0,9).''.rand(0,9).''.rand(0,9).''.rand(0,9).''.rand(0,9).''.rand(0,9).''.rand(0,9).''.rand(0,9).''.rand(0,9);
        $checkexistuserid = user::where('userid',$randomuserid)->first();
        if($checkexistuserid){
            return $this->makeuserid();
        }else{
           return strtoupper($randomuserid);
        }
    }

    /**
     * Payment Success Page (Public - No Auth Required)
     */
    public function payment_success(Request $request)
    {
        $txnId = $request->get('txn');
        $status = $request->get('status');
        
        // If no transaction ID provided, show not found
        if (!$txnId) {
            return view('Gateway.PaymentSuccess', [
                'found' => false,
                'txnId' => null
            ]);
        }
        
        // Find transaction by transaction_id or data1 (NSO transaction ID)
        $transaction = Payment_request::where('transaction_id', $txnId)
            ->orWhere('data1', $txnId)
            ->first();
        
        if (!$transaction) {
            return view('Gateway.PaymentSuccess', [
                'found' => false,
                'txnId' => $txnId
            ]);
        }

        // Only show UTR if it's a valid reference number (not null, not empty, not "pending", not a flag like "1")
        $utr = $transaction->data4; // data4 holds the actual UTR
        $validUtr = null;
        if ($utr && $utr !== 'pending' && strlen($utr) > 5) {
            $validUtr = $utr;
        }

        $transaction = $this->appendGatewayMeta($transaction);

        $redirectTarget = trim((string) $request->get('redirect', ''));
        $user = user::where('userid', $transaction->userid)->first();
        $merchantRedirect = $user && !empty($user->payin_success_redirect)
            ? trim($user->payin_success_redirect)
            : '';

        if (!empty($merchantRedirect)) {
            $redirectTarget = $merchantRedirect;
        } elseif ($redirectTarget === '') {
            $redirectTarget = null;
        }

        $gatewayMeta = $transaction->gateway_meta ?? [];
        $fees = isset($gatewayMeta['fees']) ? (float) $gatewayMeta['fees'] : (float) ($transaction->tax ?? 0);
        $settled = isset($gatewayMeta['settled'])
            ? (float) $gatewayMeta['settled']
            : max(((float) ($transaction->amount ?? 0)) - $fees, 0);

        return view('Gateway.PaymentSuccess', [
            'found' => true,
            'transaction' => $transaction,
            'txnId' => $txnId,
            'amount' => $transaction->amount,
            'status' => $transaction->status,
            'utr' => $validUtr,
            'createdAt' => $transaction->created_at,
            'gatewayMeta' => $gatewayMeta,
            'fees' => round($fees, 2),
            'settled' => round($settled, 2),
            'paymentDetails' => $gatewayMeta['payment_details'] ?? [],
            'redirectUrl' => $redirectTarget,
        ]);
    }

    public function payment_failed(Request $request)
    {
        $txnId = $request->get('txn');
        $amount = $request->get('amount');
        $reason = $request->get('reason', 'Payment failed');
        
        // Try to find transaction to get user's redirect URL
        // Priority: User's configured redirect > Query param redirect > null
        // This matches the logic in payment_success()
        $redirectTarget = null;
        $transaction = null;
        $merchantRedirect = null;
        
        if ($txnId) {
            $transaction = Payment_request::where('transaction_id', $txnId)
                ->orWhere('data1', $txnId)
                ->first();
            
            if ($transaction) {
                $user = user::where('userid', $transaction->userid)->first();
                $merchantRedirect = $user && !empty($user->payin_success_redirect)
                    ? trim($user->payin_success_redirect)
                    : null;
                
                // Prioritize merchant's configured redirect over query param (same as payment_success)
                if (!empty($merchantRedirect)) {
                    $redirectTarget = $merchantRedirect;
                    \Log::info('Payment Failed: Using merchant redirect from database', [
                        'txnId' => $txnId,
                        'userid' => $transaction->userid,
                        'redirect' => $merchantRedirect
                    ]);
                } else {
                    // Fallback to query param if merchant redirect not configured
                    $redirectTarget = trim((string) $request->get('redirect', ''));
                    if ($redirectTarget === '') {
                        $redirectTarget = null;
                    }
                    \Log::info('Payment Failed: No merchant redirect, using query param', [
                        'txnId' => $txnId,
                        'userid' => $transaction->userid,
                        'user_found' => $user !== null,
                        'redirect_configured' => $user && !empty($user->payin_success_redirect),
                        'redirect_from_query' => $request->get('redirect'),
                        'redirect_target' => $redirectTarget
                    ]);
                }
            } else {
                // No transaction found, use query param
                $redirectTarget = trim((string) $request->get('redirect', ''));
                if ($redirectTarget === '') {
                    $redirectTarget = null;
                }
                \Log::info('Payment Failed: Transaction not found', [
                    'txnId' => $txnId,
                    'redirect_from_query' => $request->get('redirect'),
                    'redirect_target' => $redirectTarget
                ]);
            }
        } else {
            // No txnId, use query param
            $redirectTarget = trim((string) $request->get('redirect', ''));
            if ($redirectTarget === '') {
                $redirectTarget = null;
            }
            \Log::info('Payment Failed: No txnId provided', [
                'redirect_from_query' => $request->get('redirect'),
                'redirect_target' => $redirectTarget
            ]);
        }
        
        return view('Gateway.PaymentFailed', [
            'txnId' => $txnId,
            'amount' => $amount,
            'reason' => $reason,
            'redirectUrl' => $redirectTarget
        ]);
    }
    
    public function retry_payment(Request $request)
    {
        $txnId = $request->get('txn');
        
        // Show a message explaining that the payment session has expired
        // and they need to initiate a new payment
        return view('Gateway.PaymentRetry', [
            'txnId' => $txnId,
            'message' => 'Your previous payment session has expired. Please initiate a new payment transaction.'
        ]);
    }

    private function appendGatewayMeta(Payment_request $paymentRequest)
    {
        $paymentRequest->gateway_meta = $this->buildGatewayMeta($paymentRequest);
        return $paymentRequest;
    }

    private function buildGatewayMeta(Payment_request $paymentRequest): array
    {
        $payload = $paymentRequest->callback_payload;

        if (is_string($payload)) {
            $decoded = json_decode($payload, true);
            $payload = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
        } elseif (!is_array($payload)) {
            $payload = [];
        }

        $payload = $this->hydrateAuroPayPayload($paymentRequest, $payload);

        $tenderInfo = Arr::get($payload, 'tenderInfo', []);
        if (!is_array($tenderInfo)) {
            $tenderInfo = [];
        }

        $reference = $this->firstNonEmpty($payload, [
            'reference_no', 'reference', 'payment_reference', 'payment_reference_no', 'refNo', 'refno',
            'ReferenceNo', 'referenceNumber', 'merchant_reference', 'merchant_reference_no',
            'order_id', 'orderId', 'payment_reference_number', 'linkReferenceNumber', 'sourceEntityNo', 'invoiceNo'
        ]);

        $gatewayTxn = $this->firstNonEmpty($payload, [
            'easepayid', 'TransactionId', 'transactionId', 'transaction_id', 'paymentId', 'payment_id', 'id',
            'processorRefId', 'traceNumber'
        ]);

        $modeRaw = $this->firstNonEmpty($payload, [
            'mode', 'payment_source', 'payment_source_type', 'paymentMode', 'payment_mode'
        ]);

        $mode = $this->formatPaymentMode($modeRaw, $payload) ?? $this->inferModeFromTenderInfo($tenderInfo);

        $cardCategoryRaw = $this->firstNonEmpty($payload, [
            'cardCategory', 'card_category', 'card_type', 'cardType'
        ]);
        if (!$cardCategoryRaw && !empty($tenderInfo)) {
            $cardCategoryRaw = $this->firstNonEmpty($tenderInfo, [
                'cardCategory', 'cardBinCategory', 'cardType'
            ]);
        }
        $cardCategory = $cardCategoryRaw ? Str::title($cardCategoryRaw) : null;

        $paymentDetails = [];
        $cardNumber = $this->firstNonEmpty($payload, ['cardnum', 'card_number', 'masked_card', 'cardNumber']);
        if (!$cardNumber && !empty($tenderInfo)) {
            $cardNumber = $this->firstNonEmpty($tenderInfo, ['maskCardNumber', 'cardNumber', 'maskedCard']);
        }
        if ($cardNumber) {
            $prefix = 'Card ';
            if ($mode === 'Credit Card') {
                $prefix = 'Credit ';
            } elseif ($mode === 'Debit Card') {
                $prefix = 'Debit ';
            }
            $cardLabel = $this->firstNonEmpty($tenderInfo, ['cardType']) ?? '';
            $label = trim($prefix . ($cardLabel ? Str::upper($cardLabel) . ' ' : '') . $this->formatMaskedValue($cardNumber));
            $paymentDetails[] = trim($label);
        }

        $upiVpa = $this->firstNonEmpty($payload, ['upi_va', 'upi_id', 'vpa', 'upiId']);
        if (!$upiVpa && !empty($tenderInfo)) {
            $upiVpa = $this->firstNonEmpty($tenderInfo, ['upiId']);
        }
        if ($upiVpa) {
            $paymentDetails[] = 'UPI: ' . Str::lower($upiVpa);
        }

        $bankName = $this->firstNonEmpty($payload, ['bank_name', 'issuing_bank', 'bankName', 'processor_name']);
        if (!$bankName && !empty($tenderInfo)) {
            $bankName = $this->firstNonEmpty($tenderInfo, ['bankName', 'cardBinIssuer']);
        }
        if ($bankName) {
            $paymentDetails[] = Str::title($bankName);
        }

        $customer = $this->firstNonEmpty($payload, ['firstname', 'customer_name', 'name']);
        if ($customer) {
            $paymentDetails[] = Str::title($customer);
        }

        $phone = $this->firstNonEmpty($payload, ['phone', 'mobile', 'mobile_no']);
        if ($phone) {
            $paymentDetails[] = $phone;
        }

        $cardHolder = $this->firstNonEmpty($payload, ['card_holder', 'card_holder_name', 'cardholder']);
        if (!$cardHolder && !empty($tenderInfo)) {
            $cardHolder = $this->firstNonEmpty($tenderInfo, ['cardHolderName', 'nameOnCheck', 'accountHolderName']);
        }
        if ($cardHolder) {
            $paymentDetails[] = Str::title($cardHolder);
        }

        $paymentDetails = array_values(array_unique(array_filter($paymentDetails)));
        $paymentDetails = array_map(function ($line) {
            $trimmed = trim($line);
            if ($trimmed === '' || in_array(strtoupper($trimmed), ['NA', 'N/A', 'NONE', 'NULL'], true)) {
                return null;
            }
            if (Str::startsWith(Str::lower($trimmed), 'upi:')) {
                return Str::upper($trimmed);
            }
            if (preg_match('/^\d{10}$/', $trimmed)) {
                return $trimmed;
            }
            return Str::title($trimmed);
        }, $paymentDetails);
        $paymentDetails = array_values(array_filter($paymentDetails));

        $amount = (float) ($paymentRequest->amount ?? 0);
        $settled = $this->firstNonEmpty($payload, ['settlement_amount', 'settlementAmount', 'netSettlementAmount']);
        $settled = $settled !== null ? (float)$settled : max($amount - (float)($paymentRequest->tax ?? 0), 0);

        $gatewayFee = $this->firstNonEmpty($payload, ['gateway_fee', 'merchantFee', 'merchant_fee']);

        if ($gatewayFee !== null) {
            $fees = (float) $gatewayFee;
            $settled = max($amount - $fees, 0);
        } else {
            $fees = $amount - $settled;
        }
        if ($fees < 0) {
            $fees = 0;
        }
        $fees = round($fees, 2);
        $settled = round($settled, 2);

        $internalTax = round((float) ($paymentRequest->tax ?? 0), 2);
        if ($internalTax > 0) {
            $fees = $internalTax;
            $settled = round(max($amount - $internalTax, 0), 2);
        }

        if ($mode) {
            $mode = Str::title($mode);
        }

        if ($cardCategory) {
            $cardCategory = Str::title($cardCategory);
        }

        if (empty($paymentDetails)) {
            $paymentDetails = [];
        }

        $utrValue = $this->firstNonEmpty($payload, [
            'utr', 'UTR', 'traceNumber', 'processorRefId', 'reference_no'
        ]) ?: ($paymentRequest->data4 ?: $paymentRequest->data1);

        if ($this->looksLikeUrl($utrValue)) {
            $utrValue = null;
        }

        return [
            'reference' => $reference ?: $paymentRequest->transaction_id,
            'gateway_txn' => $gatewayTxn,
            'mode' => $mode ?? '—',
            'card_category' => $cardCategory ?? '—',
            'payment_details' => $paymentDetails,
            'fees' => $fees,
            'settled' => $settled,
            'utr' => $utrValue,
        ];
    }

    private function firstNonEmpty(array $payload, array $keys)
    {
        foreach ($keys as $key) {
            if (!Arr::exists($payload, $key)) {
                continue;
            }
            $value = Arr::get($payload, $key);
            if (is_string($value)) {
                $value = trim($value);
                if ($value === '' || in_array(strtoupper($value), ['NA', 'N/A', 'NULL', 'NONE'], true)) {
                    continue;
                }
            }
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function formatPaymentMode(?string $mode, array $payload): ?string
    {
        if (!$mode || in_array(strtoupper($mode), ['NA', 'N/A'], true)) {
            if (isset($payload['payment_source']) && !in_array(strtoupper($payload['payment_source']), ['NA', 'N/A'], true)) {
                $mode = $payload['payment_source'];
            } else {
                return null;
            }
        }

        $mode = strtoupper($mode);

        $map = [
            'NB' => 'Net Banking',
            'NETBANKING' => 'Net Banking',
            'UPI' => 'UPI',
            'CC' => 'Credit Card',
            'CREDIT' => 'Credit Card',
            'DC' => 'Debit Card',
            'DEBIT' => 'Debit Card',
            'CARD' => 'Card',
            'WALLET' => 'Wallet',
        ];

        return $map[$mode] ?? Str::title(strtolower($mode));
    }

    private function inferModeFromTenderInfo(array $tenderInfo): ?string
    {
        if (empty($tenderInfo)) {
            return null;
        }

        $upiId = $this->firstNonEmpty($tenderInfo, ['upiId']);
        if ($upiId) {
            return 'UPI';
        }

        $cardHints = $this->firstNonEmpty($tenderInfo, ['cardCategory', 'cardBinCategory', 'cardType']);
        if ($cardHints) {
            $hint = Str::lower($cardHints);
            if (Str::contains($hint, 'debit')) {
                return 'Debit Card';
            }
            if (Str::contains($hint, 'credit')) {
                return 'Credit Card';
            }
            return 'Card';
        }

        if ($this->firstNonEmpty($tenderInfo, ['maskCardNumber', 'cardNumber'])) {
            return 'Card';
        }

        if ($this->firstNonEmpty($tenderInfo, ['paymentMode', 'paymentType'])) {
            return Str::title($this->firstNonEmpty($tenderInfo, ['paymentMode', 'paymentType']));
        }

        return null;
    }

    private function formatMaskedValue(string $value): string
    {
        $clean = str_replace([' ', '-', '_'], '', $value);
        if (preg_match('/^[Xx*]+\d{2,}$/', $clean)) {
            return '••••' . substr($clean, -4);
        }

        return $value;
    }

    private function looksLikeUrl(?string $value): bool
    {
        if (!$value || !is_string($value)) {
            return false;
        }

        $value = trim($value);
        if ($value === '') {
            return false;
        }

        if (Str::contains(Str::lower($value), ['http://', 'https://'])) {
            return true;
        }

        if (preg_match('/^(https?:)?\\/\\//i', $value)) {
            return true;
        }

        return false;
    }

    private function hydrateAuroPayPayload(Payment_request $paymentRequest, array $payload): array
    {
        $isAuroPay = in_array($paymentRequest->data6, ['29', 29], true)
            || Str::startsWith($paymentRequest->transaction_id, 'AURO')
            || Str::contains(Str::lower((string) $paymentRequest->data3), 'auropay');

        $utrLooksLikeUrl = $this->looksLikeUrl($paymentRequest->data4);

        // Always hydrate AuroPay transactions that are successful and have a transaction ID
        $needsHydration = $isAuroPay
            && (int) $paymentRequest->status === 1
            && !empty($paymentRequest->data1) // Must have AuroPay transaction ID
            && (
                empty($payload['traceNumber'])
                || empty($payload['upi_va'])
                || empty($payload['processor_name'])
                || empty($paymentRequest->data4)
                || $utrLooksLikeUrl
                || $paymentRequest->transaction_id === 'AURO2025111205' // Force refresh for this specific transaction
            );

        if (!$needsHydration) {
            return $payload;
        }

        $transactionId = $paymentRequest->data1
            ?? Arr::get($payload, 'TransactionId')
            ?? Arr::get($payload, 'transactionId')
            ?? Arr::get($payload, 'processorRefId');

        if (!$transactionId) {
            return $payload;
        }

        try {
            $auropay = new AuroPayGateway();
            $statusData = $auropay->checkTransactionStatus($transactionId);

            if (!is_array($statusData) || empty($statusData)) {
                return $payload;
            }

            $updated = false;

            $payload = array_merge($payload, [
                'transactionStatus' => Arr::get($statusData, 'transactionStatus'),
                'traceNumber' => Arr::get($statusData, 'traceNumber'),
                'processorRefId' => Arr::get($statusData, 'processorRefId'),
                'transactionId' => Arr::get($statusData, 'transactionId'),
                'linkReferenceNumber' => Arr::get($statusData, 'linkReferenceNumber'),
                'invoiceNo' => Arr::get($statusData, 'invoiceNo'),
                'processor_name' => Arr::get($statusData, 'processorName'),
            ]);

            $tenderInfo = Arr::get($statusData, 'tenderInfo', []);
            if (!empty($tenderInfo)) {
                $payload['upi_va'] = $payload['upi_va'] ?? Arr::get($tenderInfo, 'upiId');
                if (!empty($payload['upi_va']) && empty($payload['mode'])) {
                    $payload['mode'] = 'UPI';
                }
                $payload['settlement_amount'] = $payload['settlement_amount']
                    ?? Arr::get($tenderInfo, 'netSettlementAmount');
                $payload['gateway_fee'] = $payload['gateway_fee']
                    ?? Arr::get($tenderInfo, 'merchantFee');
            }

            $billing = Arr::get($statusData, 'billingContact', []);
            if (!empty($billing)) {
                $name = Arr::get($billing, 'name', []);
                $firstName = Arr::get($name, 'firstName', '');
                $lastName = Arr::get($name, 'lastName', '');
                $fullName = trim($firstName . ' ' . $lastName);

                if ($fullName !== '') {
                    $payload['customer_name'] = $payload['customer_name'] ?? $fullName;
                    $payload['firstname'] = $payload['firstname'] ?? $firstName;
                    if (!empty($lastName) && empty($payload['lastname'])) {
                        $payload['lastname'] = $lastName;
                    }
                }

                if (!empty($billing['phone']) && empty($payload['phone'])) {
                    $payload['phone'] = $billing['phone'];
                }

                if (!empty($billing['email']) && empty($payload['email'])) {
                    $payload['email'] = $billing['email'];
                }
            }

            if (!empty($payload['processor_name']) && empty($payload['bank_name'])) {
                $payload['bank_name'] = $payload['processor_name'];
            }

             $traceNumber = Arr::get($statusData, 'traceNumber') ?: Arr::get($statusData, 'processorRefId');
             if (!empty($traceNumber) && $paymentRequest->data4 !== $traceNumber) {
                 $paymentRequest->data4 = $traceNumber;
                 $updated = true;
             }

             $processorName = Arr::get($statusData, 'processorName');
             if (!empty($processorName) && $paymentRequest->data2 !== $processorName) {
                 $paymentRequest->data2 = $processorName;
                 $updated = true;
             }

             if ($paymentRequest->isDirty('data1') === false && !empty($transactionId) && $paymentRequest->data1 !== $transactionId) {
                 $paymentRequest->data1 = $transactionId;
                 $updated = true;
             }

             // Save enriched payload back to callback_payload if column exists
             try {
                 if (Schema::hasColumn('payment_requests', 'callback_payload')) {
                     $paymentRequest->callback_payload = $payload;
                     $updated = true;
                 }
             } catch (\Exception $e) {
                 // Column doesn't exist, skip
             }

             if ($updated) {
                 $paymentRequest->save();
             }
        } catch (\Throwable $e) {
            Log::warning('AuroPay status hydration failed', [
                'transaction_id' => $paymentRequest->transaction_id,
                'error' => $e->getMessage(),
            ]);
        }

        return $payload;
    }
}
