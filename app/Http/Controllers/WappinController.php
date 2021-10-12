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

        return response()->json(['success'=>true, 'message'=>'Your external IP address is '.$externalIP, 'data'=>$externalIP], 200);
    }

    public function getToken(Request $request)
    {
        $resBody = Http::withHeaders([
                'Authorization' => 'Basic '.base64_encode($request->client_id.':'.$request->secret_key),
                'Content-Type' => 'application/json'
            ])
            ->post(env('WAPPIN_URL').'/v1/token/get');

        $arrResBody = json_decode($resBody->body(), true);
        $expiredDate = $arrResBody['data']['expired_datetime'];
        $expired = (date('Y-m-d H:m:s')>=$expiredDate)?true:false;

        $data = array(
                'expired'=>$expired,
                'expired_date'=>$expiredDate,
                'token'=>$arrResBody['data']['access_token']
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

        $reqBody = array(
                'client_id' => $request->client_id,
                'project_id' => $request->project_id,
                'type' => $request->type,
                'recipient_number' => $request->recipient_number,
                'params' => $request->params
            );

        $resBody = Http::withHeaders([
                'Authorization' => 'Bearer '.$token,
                'Content-Type' => 'application/json'
            ])
            ->post(env('WAPPIN_URL').'/v1/message/do-send-hsm', $reqBody);
        
        $arrResBody = json_decode($resBody->body(), true);

        if($arrResBody['status'] == 200){
            $msg = 'Notification has been sent successfully';
            return response()->json(['success'=>true, 'message'=>$msg], $arrResBody['status']);
        }else{
            return response()->json(['success'=>false, 'message'=>$arrResBody['message']], $arrResBody['status']);
        }
    }
}