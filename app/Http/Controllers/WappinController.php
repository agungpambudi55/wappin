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

        if($resBody->status() == 200){
            return response()->json(['success'=>true, 'message'=>'API Wappin (Whatsapp Integrator) works!'], $resBody->status());
        }else{
            return response()->json(['success'=>false], $resBody->status());
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
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic '.base64_encode($request->client_id.':'.$request->secret_key),
        ])
        ->post(env('WAPPIN_URL').'/v1/token/get');

        $arrResBody = json_decode($resBody->body(), true);

        $data = array(
            'token'=>$arrResBody['data']['access_token'],
            'expired'=>$arrResBody['data']['expired_datetime']
        );

        if($resBody->status() == 200){
            return response()->json(['success'=>true, 'data'=>$data], $resBody->status());
        }else{
            return response()->json(['success'=>false], $resBody->status());
        }
    }

    public function sendNotification(Request $request)
    {
    }
}