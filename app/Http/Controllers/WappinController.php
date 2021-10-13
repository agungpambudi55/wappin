<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WappinController extends Controller
{
    public function ping()
    {
        $resBody = Http::get(env('WAPPIN_URL'));

        $arrResBody = json_decode($resBody->body(), true);

        if($arrResBody['status'] == 000){
            return response()->json(['success'=>true, 'message'=>'API Wappin (Whatsapp Integrator) works!'], $resBody->status());
        }else{
            return response()->json(['success'=>false], $arrResBody['status']);
        }
    }

    public function getExternalIP()
    {
        $externalIP = file_get_contents('http://ipecho.net/plain');

        return response()->json(['success'=>true, 'message'=>'External IP address is '.$externalIP, 'data'=>$externalIP], 200);
    }

    public function getToken(Request $request)
    {
        $username = $request->client_id;
        $password = $request->secret_key;

        $resBody = Http::withBasicAuth($username, $password)->post(env('WAPPIN_URL').'/v1/token/get');

        $arrResBody = json_decode($resBody->body(), true);
        $expiredDate = $arrResBody['data']['expired_datetime'];
        $expired = (date('Y-m-d H:m:s')>=$expiredDate)?true:false;

        $data = array(
                'expired' => $expired,
                'expired_date' => $expiredDate,
                'token' => $arrResBody['data']['access_token']
            );

        if($arrResBody['status'] == 200){
            return response()->json(['success'=>true, 'data'=>$data], $arrResBody['status']);
        }else{
            return response()->json(['success'=>false, 'message'=>$arrResBody['message']], $arrResBody['status']);
        }
    }

    public function sendNotification(Request $request)
    {
        $token = $this->getToken($request)->getData()->data->token;

        $resBody = Http::withToken($token)->post(env('WAPPIN_URL').'/v1/message/do-send-hsm', $request->all());
        
        $arrResBody = json_decode($resBody->body(), true);

        if($arrResBody['status'] == 200){
            $msg = 'Notification has been sent successfully';
            return response()->json(['success'=>true, 'message'=>$msg], $arrResBody['status']);
        }else{
            return response()->json(['success'=>false, 'message'=>$arrResBody['message']], $arrResBody['status']);
        }
    }

    public function sendNotificationWithMedia(Request $request)
    {
        // $token = $this->getToken($request)->getData()->data->token;

        // $resBody = Http::withToken($token)
        //     ->attach('media', base64_decode($request->media), 'image.png')
        //     ->post(env('WAPPIN_URL').'/v1/message/do-send-hsm-with-media', $request->all());
        
        // $arrResBody = json_decode($resBody->body(), true);

        // if($arrResBody['status'] == 200){
        //     $msg = 'Notification with media has been sent successfully';
        //     return response()->json(['success'=>true, 'message'=>$msg], $arrResBody['status']);
        // }else{
        //     return response()->json(['success'=>false, 'message'=>$arrResBody['message']], $arrResBody['status']);
        // }
    }
}