<?php

namespace App\Http\Controllers;

use App\Models\user;
use App\Models\User_Bank;
use App\Models\Wallet;
use Hash;
use Illuminate\Http\Request;

class Authentication extends Controller
{
    public function Login(Request $r)
    {
        $response = array('status' => 0, 'title' => "Oops!!", 'message' => "Invalid Action!!");
        $validated = $r->validate([
            'username' => 'required',
            'password' => 'required',
        ]);
        $query = user::where('userid', $r->username)->orWhere('email', $r->username)->orWhere('mobile', $r->username)->first();
        if ($query) {
            if ($query->status == 1) {
                if (Hash::check($r->password, $query->password)) {
                    if ($query->role == 'user') {
                        $r->session()->put('userlogin', $query);
                    } else {
                        $r->session()->put('adminlogin', $query);
                    }
                    $response = array('status' => 1, 'title' => $query->name . " you are Log In successfully!", 'message' => "");
                } else {
                    $response = array('status' => 0, 'title' => "Oops, Password Incorrect!!", 'message' => "");
                }
            } else {
                $response = array('status' => 0, 'title' => "Oops, Your Id is Block!!", 'message' => "");
            }
        } else {
            $response = array('status' => 0, 'title' => "Oops, Username Incorrect!!", 'message' => "");
        }
        return response()->json($response);
    }
    public function admin_userlogin(Request $r, $id)
    {
        $query = user::where('userid', $id)->where('status', 1)->first();
        if ($query) {
            if ($query->status == 1) {
                if ($query->isadmin == null) {
                    $r->session()->put('userlogin', $query);
                    return redirect('/dashboard');
                }else{
                    $r->session()->put('adminlogin', $query);
                    return redirect('/admin/dashboard');
                }
            } else {
                $response = "Oops, Your Id is Block!!";
            }
        } else {
            $response = "Oops, Username Incorrect!!";
        }
        return $response;
    }
    public function makeuserid(){
        $randomuserid = 'UPT'.rand(0,9).''.rand(0,9).''.rand(0,9).''.rand(0,9).''.rand(0,9).''.rand(0,9).''.rand(0,9).''.rand(0,9).''.rand(0,9);
        $checkexistuserid = user::where('userid',$randomuserid)->first();
        if($checkexistuserid){
            $this->makeuserid();
        }else{
           return strtoupper($randomuserid);
        }
    }
    public function register(Request $r)
    {
        $response = array('status' => 0, 'title' => "Oops, Invalid Action!!", 'message' => "");
        $r->validate([
            "sponserid" => 'required',
            "name" => 'required',
            "email" => 'required',
            "mobile" => 'required',
            "address" => 'required',
            "password" => 'required',
            "repassword" => 'required',
            "image" => 'required',
        ]);
        $existsponser = user::where('userid', $r->sponserid)->where('status', '1')->first();
        if ($existsponser) {
            $checkemail = user::where('mobile', $r->mobile)->first();
            if ($checkemail) {
                $response = array('status' => 0, 'title' => "Oops, Mobile already exist, Try different!!", 'message' => "");
            } else {
                if ($r->password == $r->repassword) {
                    $newuserid = $r->mobile;
                    $user = new user;
                    $user->userid = $newuserid;
                    $user->sponserid = $r->sponserid;
                    $user->name = $r->name;
                    $user->email = $r->email;
                    $user->mobile = $r->mobile;
                    $user->address = $r->address;
                    $user->data2 = $r->password;
                    $user->password = Hash::make($r->password);
                    $user->image = imageupload($r->file('image'), time(), 'user/' . $newuserid . '/')['filePath'];
                    if ($user->save()) {
                        $wallet = new Wallet;
                        $wallet->userid = $newuserid;
                        $wallet->wallet = 0;
                        if ($wallet->save()) {
                            $bank = new User_Bank;
                            $bank->userid = $newuserid;
                            if ($bank->save()) {
                                $r->session()->put('userid', $newuserid);
                                $body = '<h1>Dear, ' . $r->name . '</h1><br/>';
                                $body .= '<p>Welcome to E-Village family,';
                                $body .= '<br/> Your Name is:'.$r->name;
                                $body .= '<br/> Your Sponser id is:'.$r->sponserid;
                                $body .= '<br/> Your Userid is:'.$newuserid;
                                $body .= '<br/> Your Mobile No. is:'.$r->mobile;
                                $body .= '<br/> Your Email Id is:'.$r->email;
                                $body .= '<br/> Your Password Id is:'.$r->password;
                                $body .= '<br/> Your Login URL is:'.url('/login');
                                $body .= '<br/><br/>Thanks, <br>';
                                $body .= 'Team E-Village PVT LTD';
                                composeEmail($r->email, "New Registration - E-Village PVT LTD", $body);
                                $response = array('status' => 1, 'title' => "Success, Registration Successfully!!", 'message' => "");
                            }
                        }
                    }
                } else {
                    $response = array('status' => 0, 'title' => "Oops, Password Not Match!!", 'message' => "");
                }
            }
        } else {
            $response = array('status' => 0, 'title' => "Oops, Sponser Id Not Found!!", 'message' => "");
        }
        return response()->json($response);
    }
    public function forget_password(Request $r)
    {
        $response = array('status' => 0, 'title' => "Oops!! Invalid Action!!", 'message' => "");
        $r->validate([
            "username" => "required",
        ]);
        $existuser = user::where('userid', $r->username)->orWhere('mobile', $r->username)->where('status', 1)->first();
        if ($existuser) {
            $newpassword = rand(400, 500) . "$%^" . date("his");
            user::where('userid', $r->username)->update([
                "password" => Hash::make($newpassword),
                "data2" => $newpassword,
            ]);
            $body = '<h1>Dear, ' . $existuser->name . '</h1>';
            $body .= '<p>Your Login password is changed successfully, <br/> Your password is <b>' . $newpassword . '</b></p>';
            $body .= 'Thanks, <br>';
            $body .= 'Team TiMEUP MARKETING PVT LTD';
            $r->session()->put('userid', $existuser->userid);
            $response = array('status' => 1, 'title' => "<b>'" . $newpassword . "'</b> New Passowrd send successfully in your registered Email");
            if (isset($existuser->email) && $existuser->email != '') {
                composeEmail($existuser->email, "Forget Password - TimeUp", $body);
            }
        } else {
            $response = array('status' => 0, 'title' => "User not found!!");
        }
        return response()->json($response);
    }
}
