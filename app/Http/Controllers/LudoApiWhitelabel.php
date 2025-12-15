<?php

namespace App\Http\Controllers;

use App\Models\LudoApiWhitelabel as LudoApiWhitelabels;
use App\Models\LudoApiHit;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LudoApiWhitelabel extends Controller
{
    public function RoomCodeCheckerLudo($RoomCode,Request $request){
        $clientIP = request()->ip();
        $domain = ($request->headers->get('origin'));
        $exist = LudoApiWhitelabels::where('ip',$clientIP)->where('status',1)->first();
        
        if (!$exist) {
            return response()->json(['error' => 'Unauthorised: IP not allowed','YourIP'=>$clientIP], 401);
        }
        // return $exist->valid_at;
        $validAt = Carbon::parse($exist->valid_at);
        $currentDateTime = Carbon::now();
        if ($validAt->isPast()) {
            return response()->json(['error' => 'Forbidden: Your Subscription Expired'], 403);
        }
        $HowMuchRoomCodeGneretaed = LudoApiHit::where('hitIP',$exist->id)->where('created_at','>',date('Y-m-d')." 00:00:00")->count();
        if($exist->limits < $HowMuchRoomCodeGneretaed){
            return response()->json(['error' => 'Forbidden: Daily roomcode quota is expired.','YourIP'=>$clientIP], 401);
        }
        // $ExistRoomCode = LudoApiHit::where('roomcode',$RoomCode)->orderBy('id','desc')->first();
        // if($ExistRoomCode){
        //     $arrr = array(
        //         'status'=> 1,
        //         'creator_id'=>$ExistRoomCode->creatorid,
        //         'type'=>strtolower($ExistRoomCode->roomcodeType)
        //     );
        //     return \Response::json($arrr);
        // }
        $curl = curl_init();
        // if($exist->id == 10){
        //     curl_setopt_array($curl, array(
        //       CURLOPT_URL => 'https://api.rajasthaniludo.com/checkroomcode?roomcode='.$RoomCode.'&gameid=66e693fac61dd635d93ab972',
        //       CURLOPT_RETURNTRANSFER => true,
        //       CURLOPT_ENCODING => '',
        //       CURLOPT_MAXREDIRS => 10,
        //       CURLOPT_TIMEOUT => 0,
        //       CURLOPT_FOLLOWLOCATION => true,
        //       CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //       CURLOPT_CUSTOMREQUEST => 'GET',
        //     ));
            
        //     $response = curl_exec($curl);
        //     $err = curl_error($curl);
            
        //     curl_close($curl);
            
        //     if ($err) {
        //       echo "cURL Error #:" . $err;
        //     } else {
        //         $Hits = new LudoApiHit;
        //       $data= json_decode($response);
        //     //   return $data;
        //       if(isset($data->type)){
        //           $MyApiData = $data;
        //           $roomType = $data->type;
        //           $creatorName = "";
        //           $tableId = isset($MyApiData->ludoKing->_tableId) ? $MyApiData->ludoKing->_tableId : "";
        //           $creatorId = isset($MyApiData->ludoKing->creatorId) ? $MyApiData->ludoKing->creatorId : "";
        //           $Hits->roomcode = $RoomCode;
        //           $Hits->roomcodeType = $roomType;
        //           $Hits->creatorid = $creatorId;
        //           $Hits->creatorName = $creatorName;
        //           $Hits->tableId = $tableId;
        //           $Hits->table_name = 'Was';
        //           $Hits->result = "waiting";
        //           $Hits->hitIP = $exist->id;
        //           $Hits->hitURL = null;
        //           $Hits->status = 0;
        //           if($Hits->save()){
        //              $data->status = 1;
        //              $data->creator_id = $creatorId;
        //              $data->type = isset($roomType) ? strtolower($roomType) : null;
        //           }else{
        //              $data->status = 0;
        //           }
        //       }else{
        //           $data->status = 0;
        //           $data->message = $data->message;
        //       }
        //       return $data;
        //     }
        // }else{
        // $RandNum = rand(1,2);
        $RandNum = 3;
        if($exist->id == 12){
            $RandNum = 3;
        }
        if($RandNum == 1){
            $rand = "https://ludokingbot.gotiking.com/api/game/roomCode-Check";
        }elseif($RandNum == 2){
            $rand = "https://akadda.com/api/cashfree-callback1";
        }else{
            $rand = "https://mkadda.com/api/cashfree-callback1";
            // $rand = "https://game.qtechgames.in/api/accounts/LudoAPI";
        }
        curl_setopt_array($curl, [
          CURLOPT_URL => $rand,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => '{
    "roomCode":"'.$RoomCode.'",
    "purpose":"Check"
}',
          CURLOPT_HTTPHEADER => [
            "Accept: */*",
            "Content-Type: application/json",
          ],
        ]);
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);
        
        if ($err) {
          echo "cURL Error #:" . $err;
        } else {
            $Hits = new LudoApiHit;
          $data= json_decode($response);
        //   return $data;
          if(isset($data->success) && $data->success){
              $MyApiData = $data->data;
              $roomType = $MyApiData->type;
              $creatorName = $MyApiData->ludoName;
              $tableId = isset($MyApiData->ludoKing->_tableId) ? $MyApiData->ludoKing->_tableId : "";
              $creatorId = isset($MyApiData->ludoKing->creatorId) ? $MyApiData->ludoKing->creatorId : "";
              ///StoreData
              $Hits->roomcode = $RoomCode;
              $Hits->roomcodeType = $roomType;
              $Hits->creatorid = $creatorId;
              $Hits->creatorName = $creatorName;
              $Hits->tableId = $tableId;
              $Hits->result = "waiting";
              $Hits->table_name = $RandNum;
              $Hits->hitIP = $exist->id;
              $Hits->hitURL = null;
              $Hits->status = 0;
              if($Hits->save()){
                 $data->status = 1;
                 $data->creator_id = $creatorId;
                 $data->type = isset($roomType) ? strtolower($roomType) : null;
              }else{
                 $data->status = 0;
              }
          }else{
              $data->status = 0;
              $data->message = $data->message;
          }
          return $data;
        }
        // }
    }
    public function RoomCodeResultLudo($RoomCode,Request $request){
        $clientIP = request()->ip();
        $exist = LudoApiWhitelabels::where('ip',$clientIP)->where('status',1)->first();
        
        if (!$exist) {
            return response()->json(['error' => 'Unauthorised: IP not allowed','YourIP'=>$clientIP], 401);
        }
        $validAt = Carbon::parse($exist->valid_at);
        $currentDateTime = Carbon::now();
        if ($validAt->isPast()) {
            return response()->json(['error' => 'Forbidden: Your Subscription Expireds'], 403);
        }
        $ExistRoomCode = LudoApiHit::where('roomcode',$RoomCode)->orWhere('roomcode',intval($RoomCode))->first();
        if(!$ExistRoomCode){
            $arrr = array(
                'status'=> 0,
                // 'data'=>"Pushpendra Technology Private Limited (+917060471592)",
                'message'=> "Roomcode is not verified"
            );
            return \Response::json($arrr);
        }
        $curl = curl_init();
        
        $rand = "https://ludokingbot.gotiking.com/api/game/roomCode-result";
        if($ExistRoomCode->table_name == 2){
            $rand = "https://akadda.com/api/cashfree-callback1";
        }elseif($ExistRoomCode->table_name == 3){
            $rand = "https://mkadda.com/api/cashfree-callback1";
            // $rand = "https://game.qtechgames.in/api/accounts/LudoAPI";
        }
        curl_setopt_array($curl, [
          CURLOPT_URL => $rand,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => '{
    "roomCode":"'.$RoomCode.'",
    "purpose":"result"
}',
          CURLOPT_HTTPHEADER => [
            "Accept: */*",
            "Content-Type: application/json",
          ],
        ]);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);
        if ($err) {
          echo "cURL Error #:" . $err;
        } else {
            // return $response;
          $data = json_decode($response);
        //   $data->aOwner = "Pushpendra Technology Private Limited (+917060471591)";
          $data->YourIp = \Request::getClientIp(false);
          $data->ownerId = $ExistRoomCode->creatorid;
          return $data;
        }
    }
    public function RoomCodeResultLudoUser($RoomCode,Request $request){
        $clientIP = request()->ip();
        // $domain = ($request->headers->get('origin'));
        if(!isset($request->apikey) || $request->apikey == "")
        dd("Authorisation failed");
        // return;
        
        $exist = LudoApiWhitelabels::where('key',$request->apikey)->where('status',1)->first();
        if (!$exist) {
            dd("Authorisation failed");
            // return;
        }
        $validAt = Carbon::parse($exist->valid_at);
        $currentDateTime = Carbon::now();
        if ($validAt->isPast()) {
            dd("Forbidden: Your Subscription Expired, Contact Pushpendra Technology Private Limited (+917060471592)");
            // return;
        }
        $ExistRoomCode = LudoApiHit::where('roomcode',$RoomCode)->orWhere('roomcode',intval($RoomCode))->first();
        if(!$ExistRoomCode){
            $arrr = array(
                'status'=> 0,
                // 'data'=>"Pushpendra Technology Private Limited (+917060471592)",
                'message'=> "Roomcode is not verified"
            );
            dd($arrr);
            // return;
            // return \Response::json($arrr);
        }
        $curl = curl_init();

        curl_setopt_array($curl, [
          CURLOPT_URL => "https://ludokingbot.gotiking.com/api/game/roomCode-result",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => "{\n  \"roomCode\": \"$RoomCode\"\n}",
          CURLOPT_HTTPHEADER => [
            "Accept: */*",
            "Content-Type: application/json",
          ],
        ]);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);
        if ($err) {
          echo "cURL Error #:" . $err;
        } else {
            // return $response;
          $data = json_decode($response);
        //   $data->aOwner = "Pushpendra Technology Private Limited (+917060471592)";
          $data->YourIp = \Request::getClientIp(false);
          $data->ownerId = $ExistRoomCode->creatorid;
          dd($data);
        //   return;
        //   return $data;
        }
    }
}
