# WAPPIN (Whatsapp Integrator)
Wappin sebagai salah satu provider WhatsApp Business API.

## Fitur Wappin
### Notifikasi
Contoh penggunaannya seperti Event Reminder, Notifikasi Invoice dan Pembayaran, Pemberitahuan Update, One Time Password (OTP), Update Tiket, dan lainnya.

### Chatbot
Chatbot digunakan untuk menangani percakapan dengan bantuan WhatsApp Virtual Assistant.

### Customer Service Platform
Diakses oleh banyak agen CS dengan 1 nomor yang sama, dilengkapi fitur routing agen yang memudahkan pendistribusian chat customer.

### Fitur Advanced
1. Dashboard report real-time
2. Report performa agen customer service
3. Integrasi API ke sistem perusahaan

## Riset API Wappin untuk Notifikasi dan Chatbot
### Cara Integrasi Notifikasi
Harus menggunakan template, template notifikasi tersebut harus didaftarkan di Wappin. Kemudian dari pihak Wappin akan mendaftarkan ke pihak Facebook dan menunggu untuk disetujui baru bisa digunakan.

### Cara Integrasi Chatbot
Ada dua cara integrasi :
#### Otomatis dari Wappin
- Menggunakan endpoint https://api.wappin.id
- Menggunakan template dan keyword yang didaftarkan sebagai trigger
- Tidak bisa custom chatbot sendiri disisi backend

#### SWC Session
- Menggunakan endpoint https://swc.wappin.id
- Perlu trigger untuk memulai chatbot
- Sesi berakhir 24 jam setelah chat terakhir
- Tanpa menggunakan template
