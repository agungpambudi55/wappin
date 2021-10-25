<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\WhatsappNotification;
use App\Models\WhatsappChatbot;
use Illuminate\Support\Str;

class WappinController extends Controller
{
    // Fungsi untuk melakukan ping ke API Wappin, mengecek apakah berfungsi atau tidak
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

    // Fungsi untuk mendapatkan alamat IP eksternal dari PC atau server yang kita pakai
    public function getExternalIP()
    {
        $externalIP = file_get_contents('http://ipecho.net/plain');

        return response()->json(['success'=>true, 'message'=>'External IP address is '.$externalIP, 'data'=>$externalIP], 200);
    }

    // Fungsi untuk mendapatkan token, token tersebut digunakan sebagai Auth Bearer pada API lainnya
    public function getToken(Request $request)
    {
        $username = $request->client_id;    // sebagai username dari Auht Basic
        $password = $request->secret_key;   // sebagai password dari Auth Basic

        $resBody = Http::withBasicAuth($username, $password)->post(env('WAPPIN_URL').'/v1/token/get');

        $arrResBody = json_decode($resBody->body(), true);
        $expiredDate = $arrResBody['data']['expired_datetime'];
        $expired = (date('Y-m-d H:m:s')>=$expiredDate)?true:false; // return status expired date dari token

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

    // Fungsi untuk mengirim notifikasi atau blast berisi hanya teks, menggunakan template yang sudah disetujui Facebook
    public function sendNotification(Request $request)
    {
        // Mendapatkan token menggunakan fungsi getToken() sebagai parameter API Wappin
        $token = $this->getToken($request)->getData()->data->token;

        $resBody = Http::withToken($token)->post(env('WAPPIN_URL').'/v1/message/do-send-hsm', $request->all());
        
        $arrResBody = json_decode($resBody->body(), true);

        if($arrResBody['status'] == 200){
            $msg = 'Notification has been sent successfully';
            $data = array('message_id'=>$arrResBody['message_id']);
            return response()->json(['success'=>true, 'message'=>$msg, 'data'=>$data], $arrResBody['status']);
        }else{
            return response()->json(['success'=>false, 'message'=>$arrResBody['message']], $arrResBody['status']);
        }
    }

    // Fungsi untuk mengirim notifikasi atau blast dengan media, menggunakan template yang sudah disetujui Facebook
    public function sendNotificationMedia(Request $request)
    {
        // Mendapatkan token menggunakan fungsi getToken() sebagai parameter API Wappin
        $token = $this->getToken($request)->getData()->data->token;

        $resBody = Http::withToken($token)
            ->attach('media', base64_decode($request->media), 'image.png')
            ->post(env('WAPPIN_URL').'/v1/message/do-send-hsm-with-media', $request->all());
        
        $arrResBody = json_decode($resBody->body(), true);

        if($arrResBody['status'] == 200){
            $msg = 'Notification with media has been sent successfully';
            $data = array('message_id'=>$arrResBody['message_id']);
            return response()->json(['success'=>true, 'message'=>$msg, 'data'=>$data], $arrResBody['status']);
        }else{
            return response()->json(['success'=>false, 'message'=>$arrResBody['message']], $arrResBody['status']);
        }
    }

    // Fungsi untuk mengirim pesan berisi hanya teks, perlu trigger terlebih dahulu
    public function sendMessage(Request $request)
    {
        // Mendapatkan token menggunakan fungsi getToken() sebagai parameter API Wappin
        $token = $this->getToken($request)->getData()->data->token;

        $resBody = Http::withToken($token)->post(env('WAPPIN_URL').'/v1/message/do-send', $request->all());
        
        $arrResBody = json_decode($resBody->body(), true);

        if($arrResBody['status'] == 200){
            $msg = 'Message has been sent successfully';
            $data = array('message_id'=>$arrResBody['message_id']);
            return response()->json(['success'=>true, 'message'=>$msg, 'data'=>$data], $arrResBody['status']);
        }else{
            return response()->json(['success'=>false, 'message'=>$arrResBody['message']], $arrResBody['status']);
        }
    }

    // Fungsi untuk mengirim pesan dengan media, perlu trigger terlebih dahulu
    public function sendMessageMedia(Request $request)
    {
        // Mendapatkan token menggunakan fungsi getToken() sebagai parameter API Wappin
        $token = $this->getToken($request)->getData()->data->token;

        $resBody = Http::withToken($token)
            ->attach('media', base64_decode($request->media), 'image.png')
            ->post(env('WAPPIN_URL').'/v1/message/do-send-media', $request->all());

        $arrResBody = json_decode($resBody->body(), true);

        if($arrResBody['status'] == 200){
            $msg = 'Notification with media has been sent successfully';
            $data = array('message_id'=>$arrResBody['message_id']);
            return response()->json(['success'=>true, 'message'=>$msg, 'data'=>$data], $arrResBody['status']);
        }else{
            return response()->json(['success'=>false, 'message'=>$arrResBody['message']], $arrResBody['status']);
        }
    }

    // Fungsi cek status dari sebuah pesan, berdasarkan id pesan tersebut
    public function inquiry(Request $request)
    {
        // Mendapatkan token menggunakan fungsi getToken() sebagai parameter API Wappin
        $token = $this->getToken($request)->getData()->data->token;

        $resBody = Http::withToken($token)->post(env('WAPPIN_URL').'/v1/message/inquiry', $request->all());

        $arrResBody = json_decode($resBody->body(), true);

        if($arrResBody['status'] == 200){
            $msg = 'Check the status of messages';
            $data = array('message_id'=>$arrResBody['message_id'], 'message_status'=>$arrResBody['data']);
            return response()->json(['success'=>true, 'message'=>$msg, 'data'=>$data], $arrResBody['status']);
        }else{
            return response()->json(['success'=>false, 'message'=>$arrResBody['message']], $arrResBody['status']);
        }
    }

    // Fungsi callback fitur notifikasi, berisi konten dan status (sent, delivered, read) dari pesan blast notifikasi
    public function callbackNotification(Request $request)
    {
        if($request->has('message_id')){
            // Jika pesan sudah ada maka muat dari DB untuk perbarui status, jika belum ada maka tambahkan ke DB
            if(WhatsappNotification::where('message_id','=',$request->message_id)->exists()){ 
                $message = WhatsappNotification::where('message_id','=',$request->message_id)->first();
            }else{
                $message = new WhatsappNotification();
                $message->message_id = $request->message_id;
                $message->client_id = $request->client_id;
                $message->project_id = $request->project_id;
                $message->telephone = $request->sender_number;
                $message->content = $request->message_content;
            }

            // Status diisi sesuai callback dari Wappin, timestamp dikonversi ke datetime
            if($request->status_messages == 'sent'){
                $message->sent_at = date('Y-m-d H:i:s', $request->timestamp);
            }else if($request->status_messages == 'delivered'){
                $message->delivered_at = date('Y-m-d H:i:s', $request->timestamp);
            }else if($request->status_messages == 'read'){
                $message->read_at = date('Y-m-d H:i:s', $request->timestamp);
            }

            $message->save();
        }
    }

    // Fungsi callback fitur chatbot
    public function callbackChatbot(Request $request){
        $arrRequest = json_decode(json_encode($request->all()), true);
        $messageContent = (($arrRequest['messages'][0])['text'])['body'];

        $message = new WhatsappChatbot();
        $message->message_id = ($arrRequest['messages'][0])['id'];
        $message->wa_id = ($arrRequest['contacts'][0])['wa_id'];
        $message->wa_name = (($arrRequest['contacts'][0])['profile'])['name'];
        $message->content = $messageContent;
        $message->save();

        $messageShipper01 = `
                Halo Kerabat Shipper, apa yang anda perlukan?
                Silahkan ketik sesuai kebutuhan.%0a
                *Cek muatan*%0a
                *Tagihan*%0a
            `;

        if($messageContent == 'Shipper'){
            $reqBody = new Request();
            $reqBody->setMethod('POST');
            $reqBody->request->add([            
                    'client_id' => '0317',
                    'secret_key' => 'dbd9c735281a4a617084795bf5ca8c4b506aa741',
                    'project_id' => '2036',
                    'recipient_number' => '6285853352902',
                    'message_content' => $messageShipper01
                ]);

            $this->sendMessage($reqBody);
        }
    }
}