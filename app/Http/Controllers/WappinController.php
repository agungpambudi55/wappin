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
        // Request dengan http-client ke endpoint API Wappin
        $response = Http::get(env('WAPPIN_URL'));

        if($response->successful()){
            return response()->json(['success'=>true, 'message'=>'API Wappin (Whatsapp Integrator) works!'], 200);
        }else{
            return response()->json(['success'=>false], $response->status());
        }
    }

    // Fungsi untuk mendapatkan IP eksternal dari PC atau server yg dipakai, IP ini dimasukkan ke dalam pengaturan integrasi portal Wappin
    public function getExternalIP()
    {
        // Request dengan http-client ke endpoint Ipecho untuk mendapatkan IP ekternal
        $response = Http::get('http://ipecho.net/plain');

        if($response->successful()){
            $ip = $response->body();

            return response()->json(['success'=>true, 'message'=>'External IP address is '.$ip, 'data'=>$ip], 200);
        }else{
            return response()->json(['success'=>false], $response->status());
        }
    }

    // Fungsi untuk mendapatkan token, token tersebut digunakan sebagai Auth Bearer pada API lainnya
    public function getToken(Request $request)
    {
        $username = $request->client_id;    // client_id sebagai username dari Auht Basic
        $password = $request->secret_key;   // secret_key sebagai password dari Auth Basic

        // Request dengan http-client ke Wappin untuk mendapatkan token dan tanggal kadaluarsa
        $response = Http::withBasicAuth($username, $password)->post(env('WAPPIN_URL').'/v1/token/get');

        if($response->successful()){
            $arrRes = json_decode($response->body(), true);             // Konversi response http-client string ke array
            $expiredDate = $arrRes['data']['expired_datetime'];         // Ambil data tanggal kadaluarsa
            $expired = (date('Y-m-d H:m:s')>=$expiredDate)?true:false;  // Membandingkan tgl & mengembalikan status kadaluarsa
    
            $data = array(
                    'token' => $arrRes['data']['access_token'],
                    'expired_date' => $expiredDate,
                    'expired' => $expired
                );
    
            if($arrRes['status'] == 200){
                return response()->json(['success'=>true, 'data'=>$data], 200);
            }else{
                return response()->json(['success'=>false, 'message'=>$arrRes['message']], $arrRes['status']);
            }
        }else{
            return response()->json(['success'=>false], $response->status());
        }
    }

    // Fungsi untuk mendapatkan informasi token saja dimana parameter username & password berasal dari file .env
    private function getTokenOnly(){
        // Membuat request body, client_id sebagai username & secret_key sebagai password
        $requestBody = new Request(['client_id'=>env('CLIENT_ID'), 'secret_key'=>env('SECRET_KEY')]);

        return $this->getToken($requestBody)->getData()->data->token;
    }    

    // Fungsi untuk mengirim notifikasi atau blast berisi hanya teks, menggunakan template yang sudah disetujui Facebook
    public function sendNotification(Request $request)
    {
        // Mendapatkan token sebagai parameter Auth Bearer API Wappin
        $token = $this->getTokenOnly();

        // Request dengan http-client untuk mengirim notifikasi
        $request->request->add(['client_id'=>env('CLIENT_ID'),'project_id'=>env('PROJECT_ID')]);
        $response = Http::withToken($token)->post(env('WAPPIN_URL').'/v1/message/do-send-hsm', $request->all());
        
        if($response->successful()){
            $arrRes = json_decode($response->body(), true); // Konversi response http-client string ke array

             // Status berasal dari respon kode tersendiri dari Wappin
            if($arrRes['status'] == 200){
                $msg = 'Notification has been sent successfully';
                $data = array('message_id'=>$arrRes['message_id']);

                return response()->json(['success'=>true, 'message'=>$msg, 'data'=>$data], 200);
            }else if($arrRes['status'] == 601){
                return response()->json(['success'=>false, 'message'=>$arrRes['message']], 400);
            }else{
                return response()->json(['success'=>false, 'message'=>$arrRes['message']], $arrRes['status']);
            }
        }else{
            return response()->json(['success'=>false], $response->status());
        }
    }

    // Fungsi untuk mengirim notifikasi atau blast dengan media, menggunakan template yang sudah disetujui Facebook
    public function sendNotificationMedia(Request $request)
    {
        // Mendapatkan token sebagai parameter Auth Bearer API Wappin
        $token = $this->getTokenOnly();

        // Request dengan http-client untuk mengirim notifikasi dengan media
        $request->request->add(['client_id'=>env('CLIENT_ID'),'project_id'=>env('PROJECT_ID')]);
        $response = Http::withToken($token)
            // Media yang dikirim berupa base64, jadi ketika request ke Wappin di-decode terlebih dahulu
            ->attach('media', base64_decode($request->media), 'image.png')
            ->post(env('WAPPIN_URL').'/v1/message/do-send-hsm-with-media', $request->all());

        if($response->successful()){
            $arrRes = json_decode($response->body(), true); // Konversi response http-client string ke array

            // Status berasal dari respon kode tersendiri dari Wappin
            if($arrRes['status'] == 200){
                $msg = 'Notification with media has been sent successfully';
                $data = array('message_id'=>$arrRes['message_id']);

                return response()->json(['success'=>true, 'message'=>$msg, 'data'=>$data], 200);
            }else if($arrRes['status'] == 601){
                return response()->json(['success'=>false, 'message'=>$arrRes['message']], 400);
            }else{
                return response()->json(['success'=>false, 'message'=>$arrRes['message']], $arrRes['status']);
            }
        }else{
            return response()->json(['success'=>false], $response->status());
        }
    }

    // Fungsi untuk mengirim pesan berisi hanya teks, perlu trigger terlebih dahulu
    public function sendMessage(Request $request)
    {
        // Mendapatkan token sebagai parameter Auth Bearer API Wappin
        $token = $this->getTokenOnly();

        // Request dengan http-client untuk mengirim pesan
        $request->request->add(['client_id'=>env('CLIENT_ID'),'project_id'=>env('PROJECT_ID')]);
        $response = Http::withToken($token)->post(env('WAPPIN_URL').'/v1/message/do-send', $request->all());

        if($response->successful()){
            $arrRes = json_decode($response->body(), true); // Konversi response http-client string ke array

            // Status berasal dari respon kode tersendiri dari Wappin
            if($arrRes['status'] == 200){
                $msg = 'Message has been sent successfully';
                $data = array('message_id'=>$arrRes['message_id']);

                return response()->json(['success'=>true, 'message'=>$msg, 'data'=>$data], 200);
            }else if($arrRes['status'] == 601){
                return response()->json(['success'=>false, 'message'=>$arrRes['message']], 400);
            }else{
                return response()->json(['success'=>false, 'message'=>$arrRes['message']], $arrRes['status']);
            }
        }else{
            return response()->json(['success'=>false], $response->status());
        }
    }

    // Fungsi untuk mengirim pesan dengan media, perlu trigger terlebih dahulu
    public function sendMessageMedia(Request $request)
    {
        // Mendapatkan token sebagai parameter Auth Bearer API Wappin
        $token = $this->getTokenOnly();

        // Request dengan http-client untuk mengirim pesan dengan media
        $request->request->add(['client_id'=>env('CLIENT_ID'),'project_id'=>env('PROJECT_ID')]);
        $response = Http::withToken($token)
            ->attach('media', base64_decode($request->media), 'image.png')
            ->post(env('WAPPIN_URL').'/v1/message/do-send-media', $request->all());

        if($response->successful()){
            $arrRes = json_decode($response->body(), true); // Konversi response http-client string ke array

            // Status berasal dari respon kode tersendiri dari Wappin        
            if($arrRes['status'] == 200){
                $msg = 'Notification with media has been sent successfully';
                $data = array('message_id'=>$arrRes['message_id']);
                return response()->json(['success'=>true, 'message'=>$msg, 'data'=>$data], 200);
            }else if($arrRes['status'] == 601){
                return response()->json(['success'=>false, 'message'=>$arrRes['message']], 400);
            }else{
                return response()->json(['success'=>false, 'message'=>$arrRes['message']], $arrRes['status']);
            }
        }else{
            return response()->json(['success'=>false], $response->status());
        }
    }

    // Fungsi cek status dari sebuah pesan, berdasarkan id pesan tersebut
    public function inquiry(Request $request)
    {
        // Mendapatkan token sebagai parameter Auth Bearer API Wappin
        $token = $this->getTokenOnly();

        // Request dengan http-client untuk mengecek status pesan
        $response = Http::withToken($token)->post(env('WAPPIN_URL').'/v1/message/inquiry', $request->all());

        if($response->successful()){
            $arrRes = json_decode($response->body(), true); // Konversi response http-client string ke array

            if($arrRes['status'] == 200){
                $msg = 'Check the status of messages';
                $data = array('message_id'=>$arrRes['message_id'], 'message_status'=>$arrRes['data']);
                return response()->json(['success'=>true, 'message'=>$msg, 'data'=>$data], 200);
            }else{
                return response()->json(['success'=>false, 'message'=>$arrRes['message']], $arrRes['status']);
            }
        }else{
            return response()->json(['success'=>false], $response->status());
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

    // SWC Chatbot

    // Fungsi login untuk mendapatkan token
    public function getTokenSWC(Request $request)
    {
        $response = Http::withBasicAuth($request->username, $request->password)->post(env('SWC_URL').'/v1/users/login');

        $arrRes = json_decode($response->body(), true);

        if($response->status() == 200){
            $expiredDate = $arrRes['users']['expires_after'];
            $expired = (date('Y-m-d H:m:s')>=$expiredDate)?true:false; // return status expired date dari token
    
            $data = array(
                    'expired' => $expired,
                    'expired_date' => $expiredDate,
                    'token' => $arrRes['users']['token']
                );
    
            return response()->json(['success'=>true, 'data'=>$data], $response->status());
        }else{
            return response()->json(['success'=>false, 'message'=>$arrRes['errors']['title']], $response->status());
        }
    }

    // Fungsi mengecek apakah kontak valid atau tidak valid
    public function checkContact(Request $request)
    {
        
        return '';
    }

    // Fungsi callback fitur chatbot
    public function callbackChatbot(Request $request)
    {
        $arrRequest = json_decode(json_encode($request->all()), true);
        $messageContent = (($arrRequest['messages'][0])['text'])['body'];

        $message = new WhatsappChatbot();
        $message->message_id = ($arrRequest['messages'][0])['id'];
        $message->wa_id = ($arrRequest['contacts'][0])['wa_id'];
        $message->wa_name = (($arrRequest['contacts'][0])['profile'])['name'];
        $message->content = $messageContent;
        $message->save();

        $message01 = 'Anda adalah *'.(($arrRequest['contacts'][0])['profile'])['name'].'*, mbok tulung moso lali jeneng wkwk.';

        if($messageContent == 'Siapa saya?'){
            $reqBody = new Request();
            $reqBody->setMethod('POST');
            $reqBody->request->add([            
                    'client_id' => '0317',
                    'secret_key' => 'dbd9c735281a4a617084795bf5ca8c4b506aa741',
                    'project_id' => '2036',
                    'recipient_number' => ($arrRequest['contacts'][0])['wa_id'],
                    'message_content' => $message01
                ]);

            $this->sendMessage($reqBody);
        }else if(strpos($messageContent,'INV') !== false){
            $reqBody = new Request();
            $reqBody->setMethod('POST');
            $reqBody->request->add([            
                    'client_id' => '0317',
                    'secret_key' => 'dbd9c735281a4a617084795bf5ca8c4b506aa741',
                    'project_id' => '2036',
                    'recipient_number' => ($arrRequest['contacts'][0])['wa_id'],
                    'message_content' => 'Tagihan dengan nomor *'.$messageContent.'* sudah lunas.'
                ]);

            $this->sendMessage($reqBody);
        }
    }
}