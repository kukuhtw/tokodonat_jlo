 **README.md**  repository GitHub [`tokodonat_jlo`](https://github.com/kukuhtw/tokodonat_jlo), chatbot AI untuk toko donat:

---

```markdown
# 🍩 tokodonat_jlo — Chatbot AI Toko Donat Otomatis

Selamat datang di proyek open-source **Chatbot AI untuk Toko Donat JLO**!  
Chatbot ini didesain untuk melayani pelanggan layaknya manusia — dari menerima pesanan, menghitung PPN dan ongkir, memberikan diskon, hingga rekap pembelian.  
Cocok untuk toko donat, toko makanan ringan, atau bisnis kuliner berbasis pre-order & delivery.

---

## ✨ Fitur Utama

- 💬 **Chatbot seperti manusia** (menggunakan OpenAI GPT)
- 🛒 **Rekap pesanan otomatis** dalam format JSON
- 🧾 **Hitung PPN otomatis (11%)**
- 🛵 **Hitung biaya pengiriman berdasarkan kecamatan/kelurahan**
- 🎁 **Diskon otomatis** (contoh: beli 3 gratis 1)
- 📍 **Kumpulkan alamat & kontak pelanggan**
- 🧠 **Memory chat history** & quota per jam
- 📱 **Siap diintegrasikan ke WhatsApp Gateway (Fonnte)**
- 💾 **Penyimpanan chat, cart, dan order di MySQL**

---

## 🚀 Teknologi yang Digunakan

- **PHP Native 8.x**
- **MySQL / MariaDB**
- **OpenAI GPT-4 API (Chat Completion with Function Call)**
- **Logger bawaan (dengan timestamp dan struktur json)**
- **HTML Admin Panel untuk melihat order dan history**

---

## 📂 Struktur Folder

```

├── public/               # Frontend / chatbot entry point (Telegram/Web/WhatsApp)
├── src/                  # Logika utama chatbot (ChatHandler, Logger, helper)
├── sql/                  # Struktur database (users, chat\_history, orders)
├── bootstrap.php         # Koneksi DB + autoload
├── README.md

````

---

## ⚙️ Cara Install

1. **Clone repo ini**

```bash
git clone https://github.com/kukuhtw/tokodonat_jlo.git
cd tokodonat_jlo
````

2. **Setup file konfigurasi**

jalankan public/create_admin.php
set file config.php
```

3. **Import struktur database**

Import file `sql/tokodonat_structure.sql` ke MySQL Anda:

```bash
mysql -u root -p tokodonat < sql/tokodonat_structure.sql
```

4. **Install dependencies (jika ada via Composer)**
   Saat ini menggunakan PHP Native, tidak membutuhkan Composer.

5. **Jalankan dari browser atau terminal**
   Akses via `public/index.php` atau integrasikan ke endpoint WhatsApp/Telegram Anda.

---

## 🧪 Contoh Percakapan

```
👤: Saya mau pesan donat Red Velvet 1 pcs
🤖: Red Velvet Premium Donut (1 pcs): Rp15.000
PPN 11%: Rp1.650
Total: Rp16.650
Ada tambahan pesanan?

👤: Tambahkan Lemon Tea
🤖: Lemon Tea (Rp15.000) ditambahkan.
Total baru: Rp31.650 (sudah termasuk PPN)

Silakan isi alamat pengiriman, email, dan nomor WhatsApp Anda.
```

---

## 🧩 Integrasi WhatsApp Gateway

Chatbot ini mendukung pengiriman balasan via [Fonnte] https://md.fonnte.com/new/register.php?ref=9 API (atau bisa disesuaikan).
Pastikan Anda setup webhook dan format data sesuai dengan `ChatHandler`.

---

## 🤝 Kontribusi

Silakan fork dan kembangkan!
Bila Anda membuat versi khusus (misalnya untuk toko kue, makanan, pre-order catering), mention repo ini ya 🙌

---

## 🛠️ Modifikasi untuk Toko Lain (Pizza, Kopi, Roti, dll.)

Chatbot ini sangat fleksibel dan **mudah dimodifikasi untuk berbagai jenis usaha kuliner**, seperti:

### 🍕 Toko Pizza
- Ganti daftar menu dengan varian pizza (ukuran, topping, crust)
- Tambahkan opsi custom pizza (extra keju, saus, dll)
- Hitung harga berdasarkan ukuran dan tambahan topping
- Atur delivery fee sesuai zona

### ☕ Coffee Shop
- Ganti menu dengan minuman panas/dingin: espresso, latte, matcha, dll
- Tambah varian ukuran (Tall, Grande, Venti)
- Tambahkan promo: beli 2 gratis 1 atau diskon jam tertentu (happy hour)
- Simpan preferensi pelanggan (manis, tanpa gula, extra shot)

### 🍞 Toko Roti & Kue
- Sesuaikan menu dengan paket roti, kue ulang tahun, dan hampers
- Tambahkan fitur request tulisan di atas kue
- Tentukan batas waktu pre-order (misal H-1)
- Hitung ongkir berdasarkan bobot/volume pesanan

### 🍱 Usaha Kuliner Lain (Catering, Food Truck, Bento, dll)
- Chatbot dapat menjawab FAQ (jadwal buka, lokasi foodtruck, dll)
- Sistem pemesanan pre-order / langganan mingguan
- Hitung total sesuai paket makanan + biaya kirim
- Integrasi pembayaran otomatis (manual, VA, QRIS)

🧩 Anda hanya perlu mengubah:
- Menu dan harga di bagian JSON produk
- Aturan diskon dan promo di `ChatHandler.php`
- Daftar ongkir di helper atau DB
- Prompt dan instruksi di `defaultPrompt` atau di database


---



## 📞 Kontak & Bantuan

📧 Email: [kukuhtw@gmail.com](mailto:kukuhtw@gmail.com)
📱 WhatsApp: [https://wa.me/628129893706](https://wa.me/628129893706)

---

## 🪪 Lisensi

Proyek ini menggunakan lisensi **MIT** — silakan gunakan, ubah, dan distribusikan untuk kebutuhan bisnis Anda!

---

## ⭐ Dukungan

Jika repo ini bermanfaat, mohon bantu:

* 🌟 Beri bintang (Star)
* 🔄 Fork & share
* 💬 Beri feedback atau buka issue

---

