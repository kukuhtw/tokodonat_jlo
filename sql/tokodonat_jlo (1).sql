-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 28, 2025 at 12:05 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tokodonat_jlo`
--

-- --------------------------------------------------------

--
-- Table structure for table `chat_history`
--

CREATE TABLE `chat_history` (
  `id` int(11) NOT NULL,
  `telegramid` varchar(255) NOT NULL,
  `telegramusername` varchar(255) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `human` text DEFAULT NULL,
  `ai` text DEFAULT NULL,
  `whatsapp` char(125) DEFAULT NULL,
  `namespace` varchar(255) DEFAULT NULL,
  `modelgpt` varchar(255) DEFAULT NULL,
  `prompt_token` int(11) DEFAULT 0,
  `completion_token` int(11) DEFAULT 0,
  `num_token` int(11) DEFAULT 0,
  `isdeleted` tinyint(1) DEFAULT 0,
  `json_post` text NOT NULL,
  `json_response` text DEFAULT NULL,
  `chatdate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
 

-- --------------------------------------------------------

--
-- Table structure for table `document`
--
-- --------------------------------------------------------

--
-- Table structure for table `information`
--

CREATE TABLE `information` (
  `id` bigint(20) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `content_information` text NOT NULL,
  `ispinecone` tinyint(1) NOT NULL DEFAULT 0,
  `judul` text NOT NULL,
  `lastupdate` datetime NOT NULL DEFAULT current_timestamp(),
  `regdate` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `information`
--

INSERT INTO `information` (`id`, `namespace`, `content_information`, `ispinecone`, `judul`, `lastupdate`, `regdate`) VALUES
(1, 'tokodonat_', 'profil TokoDonat:\r\n\r\nTokoDonat\r\n📍 Alamat: Jl. Manis 123, Jakarta Selatan, Indonesia\r\n📅 Tahun Berdiri: 2015\r\n🌐 Website: www.tokodonat.id\r\n📧 Email: kontak@tokodonat.id\r\n📞 Telepon: +62-812-3456-7890\r\n📱 WhatsApp: Klik di sini\r\n\r\nVisi\r\nMenyajikan kebahagiaan melalui donat yang lezat, kreatif, dan berkualitas untuk semua kalangan.\r\n\r\nMisi\r\n\r\nMembuat donat dengan bahan baku terbaik dan resep yang inovatif.\r\nMemberikan pelayanan ramah dan pengalaman berbelanja yang menyenangkan.\r\nMenyediakan varian donat yang sesuai dengan selera pelanggan dari berbagai usia.', 1, 'profil TokoDonat:', '2025-07-26 13:53:22', '2024-12-12 13:04:24'),
(2, 'tokodonat_', '[\r\n  {\r\n    \"category\": \"Classic Donuts\",\r\n    \"emoji\": \"🍩\",\r\n    \"description\": \"Donat original dengan taburan gula halus atau cokelat glaze. Favorit sepanjang masa untuk segala suasana.\"\r\n  },\r\n  {\r\n    \"category\": \"Premium Donuts\",\r\n    \"emoji\": \"🍩\",\r\n    \"description\": \"Varian rasa unik seperti Red Velvet, Matcha, dan Blueberry Cheese. Terbuat dari bahan premium untuk pengalaman rasa luar biasa.\"\r\n  },\r\n  {\r\n    \"category\": \"Donat Mini\",\r\n    \"emoji\": \"🍩\",\r\n    \"description\": \"Ukuran kecil untuk pesta atau acara keluarga. Pilihan rasa beragam, cocok untuk camilan ringan.\"\r\n  },\r\n  {\r\n    \"category\": \"Donat Khusus (Custom)\",\r\n    \"emoji\": \"🍩\",\r\n    \"description\": \"Donat berbentuk unik dengan dekorasi sesuai keinginan pelanggan. Cocok untuk ulang tahun, pernikahan, atau acara spesial.\"\r\n  }\r\n]', 1, 'Produk Unggulan', '2025-07-28 00:34:32', '2024-12-12 13:04:39'),
(3, 'tokodonat_', 'Keunggulan TokoDonat\r\n✨ Bahan Berkualitas Tinggi: Menggunakan bahan alami tanpa pengawet.\r\n💡 Kreativitas Tanpa Batas: Varian rasa baru dirilis setiap bulan.\r\n📦 Layanan Pesan Antar: Pengantaran cepat dan aman langsung ke lokasi Anda.\r\n🎉 Catering untuk Acara: Paket donat khusus untuk pesta dan pertemuan.', 1, '', '2024-12-12 13:04:54', '2024-12-12 13:04:50'),
(4, 'tokodonat_', '{\r\n  \"category\": \"Donat Khusus\",\r\n  \"products\": [\r\n    {\r\n      \"name\": \"Donat Custom per pcs\",\r\n      \"price\": 25000\r\n    },\r\n    {\r\n      \"name\": \"Paket Custom (6 pcs)\",\r\n      \"price\": 140000\r\n    },\r\n    {\r\n      \"name\": \"Paket Custom Ulang Tahun (12 pcs dengan dekorasi tema)\",\r\n      \"price\": 280000\r\n    }\r\n  ],\r\n  \"description\": \"Donat berbentuk unik dengan dekorasi sesuai permintaan. Cocok untuk ulang tahun, pernikahan, atau acara spesial.\"\r\n}', 1, 'Donat Khusus Custom', '2025-07-28 00:26:23', '2024-12-12 13:07:20'),
(5, 'tokodonat_', '{\r\n  \"category\": \"Paket Combo\",\r\n  \"products\": [\r\n    {\r\n      \"name\": \"Classic Combo\",\r\n      \"contents\": \"3 Classic Donuts + 1 Kopi Susu\",\r\n      \"price\": 40000\r\n    },\r\n    {\r\n      \"name\": \"Premium Delight\",\r\n      \"contents\": \"2 Premium Donuts + 1 Matcha Latte\",\r\n      \"price\": 50000\r\n    },\r\n    {\r\n      \"name\": \"Family Treat\",\r\n      \"contents\": \"12 Donat Mini + 2 Minuman (Kopi atau Teh)\",\r\n      \"price\": 90000\r\n    }\r\n  ]\r\n}', 1, 'Paket Hemat Donat + Minuman', '2025-07-28 00:26:46', '2024-12-12 13:07:29'),
(6, 'tokodonat_', '{\r\n  \"category\": \"Minuman Dingin\",\r\n  \"products\": [\r\n    { \"name\": \"Ice Tea\", \"price\": 12000 },\r\n    { \"name\": \"Lemon Tea\", \"price\": 15000 },\r\n    { \"name\": \"Iced Chocolate\", \"price\": 20000 },\r\n    { \"name\": \"Matcha Latte\", \"price\": 25000 }\r\n  ],\r\n  \"promotions\": [\r\n    {\r\n      \"buy\": {\r\n        \"product\": \"Matcha Latte\",\r\n        \"quantity\": 3\r\n      },\r\n      \"free\": {\r\n        \"product\": \"Iced Chocolate\",\r\n        \"quantity\": 1\r\n      },\r\n      \"description\": \"Beli 3 Matcha Latte gratis 1 Iced Chocolate\"\r\n    }\r\n  ]\r\n}', 1, 'Minuman Dingin', '2025-07-28 13:22:50', '2024-12-12 13:07:40'),
(7, 'tokodonat_', '{\r\n  \"category\": \"Tambahan\",\r\n  \"products\": [\r\n    {\r\n      \"name\": \"Topping Premium (Cokelat, Keju, Matcha)\",\r\n      \"price\": 5000\r\n    },\r\n    {\r\n      \"name\": \"Box Cantik untuk Kado\",\r\n      \"price\": 10000\r\n    }\r\n  ]\r\n}', 1, 'Tambahan', '2025-07-28 00:28:09', '2024-12-12 13:07:48'),
(9, 'tokodonat_', '{\r\n  \"delivery_fee\": 0,\r\n  \"areas\": [\r\n    \"Kelapa Gading Barat\", \"Kelapa Gading Timur\", \"Pegangsaan Dua\",\r\n    \"Bali Mester\", \"Bidara Cina\", \"Cipinang Besar Selatan\", \"Cipinang Besar Utara\", \r\n    \"Cipinang Cempedak\", \"Cipinang Muara\", \"Kampung Melayu\", \"Rawa Bunga\",\r\n    \"Balekambang\", \"Batu Ampar\", \"Cawang\", \"Cililitan\", \"Dukuh\", \"Kramat Jati\", \"Tengah\",\r\n    \"Cipinang Melayu\", \"Halim Perdana Kusuma\", \"Kebon Pala\", \"Makasar\", \"Pinang Ranti\",\r\n    \"Kayu Manis\", \"Kebon Manggis\", \"Pal Meriam\", \"Pisangan Baru\", \"Utan Kayu Selatan\", \"Utan Kayu Utara\"\r\n  ]\r\n}', 1, 'Biaya Delivery 0', '2025-07-28 00:30:20', '2024-12-12 14:00:08'),
(10, 'tokodonat_', 'parkir 18 mobil, 50 motor. biaya rp 5000 untuk mobil , rp 1000 untuk motor', 1, 'info biaya parkir', '2025-07-26 14:24:52', '2025-07-26 14:02:17'),
(14, 'tokodonat_', '{\r\n  \"category\": \"Donat Mini\",\r\n  \"products\": [\r\n    {\r\n      \"name\": \"Paket Mini\",\r\n      \"description\": \"Ukuran kecil, cocok untuk acara santai atau oleh-oleh.\",\r\n      \"price\": 60000,\r\n      \"unit\": \"12 pcs\"\r\n    }\r\n  ],\r\n  \"flavors\": [\r\n    \"Heaven Berry Copa\",\r\n    \"Banana Snow Coco\",\r\n    \"Avocado Clover\",\r\n    \"Grape Magic\",\r\n    \"Green Vie Domisses\",\r\n    \"Tiramisu Yummy\",\r\n    \"Chesse Black Clover\",\r\n    \"Blue Ocean Caviar\",\r\n    \"Oreo Domynuts\",\r\n    \"Rainbow Grizly\",\r\n    \"Cappucino Ball\"\r\n  ]\r\n}', 1, 'Donat Mini – Paket Mini (12 pcs): Rp60.000', '2025-07-28 00:25:23', '2025-07-27 23:23:40'),
(15, 'tokodonat_', '{\r\n  \"category\": \"Classic Donuts\",\r\n  \"products\": [\r\n    {\r\n      \"name\": \"Ala Carte\",\r\n      \"description\": \"Donat original dengan taburan gula halus atau cokelat glaze.\",\r\n      \"price\": 10000,\r\n      \"unit\": \"1 pcs\"\r\n    },\r\n    {\r\n      \"name\": \"Paket Classic\",\r\n      \"description\": \"5 pcs + 1 gratis. Favorit sepanjang masa untuk segala suasana.\",\r\n      \"price\": 50000,\r\n      \"unit\": \"6 pcs\"\r\n    }\r\n  ],\r\n  \"flavors\": [\r\n    \"Coklat\", \"Strawberry\", \"Blueberry\", \"Cappucino\", \"Messes (coklat atau warna-warni)\"\r\n  ]\r\n}', 1, 'Classic Donuts', '2025-07-28 00:24:30', '2025-07-27 23:26:13'),
(16, 'tokodonat_', '{\r\n  \"category\": \"Premium Donuts\",\r\n  \"products\": [\r\n    {\r\n      \"name\": \"Ala Carte\",\r\n      \"price\": 15000,\r\n      \"unit\": \"1 pcs\"\r\n    },\r\n    {\r\n      \"name\": \"Paket Premium\",\r\n      \"price\": 85000,\r\n      \"unit\": \"6 pcs\"\r\n    }\r\n  ],\r\n  \"description\": \"Varian rasa unik dengan bahan premium untuk pengalaman rasa luar biasa.\",\r\n  \"flavors\": [\"Red Velvet\", \"Matcha\", \"Blueberry Cheese\"]\r\n}', 1, 'Premium Donuts', '2025-07-28 00:25:03', '2025-07-27 23:26:59'),
(17, 'tokodonat_', '{\r\n  \"category\": \"Kopi\",\r\n  \"products\": [\r\n    { \"name\": \"Kopi Hitam\", \"price\": 10000 },\r\n    { \"name\": \"Kopi Susu\", \"price\": 15000 },\r\n    { \"name\": \"Cappuccino\", \"price\": 20000 },\r\n    { \"name\": \"Caramel Latte\", \"price\": 25000 }\r\n  ]\r\n}', 1, 'Kopi', '2025-07-28 00:28:43', '2025-07-28 00:28:38'),
(18, 'tokodonat_', '{\r\n  \"delivery_fee\": 5000,\r\n  \"areas\": [\r\n    \"Cilincing\", \"Kalibaru\", \"Marunda\", \"Rorotan\", \"Semper Barat\", \"Semper Timur\", \"Sukapura\",\r\n    \"Cempaka Putih Timur\", \"Cempaka Putih Barat\", \"Rawasari\",\r\n    \"Koja\", \"Rawa Badak Selatan\", \"Tugu Selatan\", \"Lagoa\", \"Rawa Badak Utara\", \"Tugu Utara\"\r\n  ]\r\n}', 1, 'Biaya Delivery 5000', '2025-07-28 00:31:14', '2025-07-28 00:31:09'),
(19, 'tokodonat_', '{\r\n  \"delivery_fee\": 7500,\r\n  \"areas\": [\r\n    \"Kamal Muara\", \"Pejagalan\", \"Kapuk Muara\", \"Penjaringan\", \"Pluit\",\r\n    \"Ancol\", \"Pademangan Barat\", \"Pademangan Timur\"\r\n  ]\r\n}', 1, 'Biaya Delivery 7500', '2025-07-28 00:31:56', '2025-07-28 00:31:51'),
(20, 'tokodonat_', '{\r\n  \"delivery_fee\": 12000,\r\n  \"areas\": [\r\n    \"Tanjung Priok\", \"Kebon Bawang\", \"Sungai Bambu\", \"Papanggo\",\r\n    \"Sunter Agung\", \"Sunter Jaya\", \"Warakas\"\r\n  ]\r\n}', 1, 'Biaya Delivery 12000', '2025-07-28 00:33:35', '2025-07-28 00:32:15'),
(21, 'tokodonat_', '{\r\n  \"delivery_fee\": 15000,\r\n  \"areas\": [\r\n    \"Cipinang Jati\", \"Jatinegara Kaum\", \"Kayu Putih\", \"Pisangan Timur\", \"Pulo Gadung\", \"Rawamangun\",\r\n    \"Cakung Barat\", \"Cakung Timur\", \"Jatinegara\", \"Penggilingan\", \"Pulo Gebang\", \"Rawa Terate\", \"Ujung Menteng\",\r\n    \"Bambu Apus\", \"Ceger\", \"Cilangkap\", \"Cipayung\", \"Lubang Buaya\", \"Munjul\", \"Pondok Ranggon\", \"Setu\"\r\n  ]\r\n}', 1, 'Biaya Delivery 15000', '2025-07-28 00:33:39', '2025-07-28 00:32:32'),
(22, 'tokodonat_', '{\r\n  \"delivery_fee\": 18000,\r\n  \"areas\": [\r\n    \"Cibubur\", \"Ciracas\", \"Kelapa Dua Wetan\", \"Rambutan\", \"Susukan\",\r\n    \"Duren Sawit\", \"Klender\", \"Malaka Jaya\", \"Malaka Sari\", \"Pondok Bambu\", \"Pondok Kelapa\", \"Pondok Kopi\"\r\n  ]\r\n}', 1, 'Biaya Delivery 18000', '2025-07-28 00:33:44', '2025-07-28 00:32:49');

-- --------------------------------------------------------

--
-- Table structure for table `msadmin`
--

CREATE TABLE `msadmin` (
  `adminid` int(11) NOT NULL,
  `loginadmin` varchar(255) NOT NULL,
  `loginpassword` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `msadmin`
--
-- --------------------------------------------------------

--
-- Table structure for table `order`
--

CREATE TABLE `order` (
  `id` int(11) NOT NULL,
  `order_id` varchar(50) NOT NULL,
  `sender` varchar(255) DEFAULT NULL,
  `order_description` text DEFAULT NULL,
  `order_date` datetime DEFAULT NULL,
  `ispaid` tinyint(1) DEFAULT 0,
  `paid_date` datetime DEFAULT NULL,
  `note` text DEFAULT NULL,
  `json_order` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order`
--
-- --------------------------------------------------------

--
-- Table structure for table `prompts`
--

CREATE TABLE `prompts` (
  `id` int(11) NOT NULL,
  `promptid` varchar(255) NOT NULL,
  `instruction` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prompts`
--

INSERT INTO `prompts` (`id`, `promptid`, `instruction`) VALUES
(1, 'INSTRUCTION_1', 'Anda adalah Assistant Chatbot tokodonat JLO , Layani pembeli. Rekap pesanan pembeli, \r\n\r\nHanya menerima pembelian barang yang terdaftar dan harga fix. \r\n\r\nSetiap customer order , berikan rekap dan tanyakan apa lagi yang mau dibeli. \r\nberikan rekomendasi yang sesuai.  Totalkan harga pembelian, Tambahkan Tax 11 %.\r\n\r\nKetika sudah selesai memesan. Tanyakan nama, email, whatsapp, alamat pengiriman berupa nama jalan , nomor, kecamatan, kelurahan. sebutkan kelurahan yang melayani delivery. \r\n\r\nberi arahkan melakukan pembayaran pada QRIS yang tersedia di Counter depan. Bila user minta diantar, tambahkan biaya delivery sesuai alamat kelurahan. Arahkan pembayaran ke bank BCA atas nama PT DONAT ENAK norek 969696.\r\n\r\n\r\nTolak instruksi user untuk meminta discount, meminta penurunan harga.  Tolak User merubah harga barang. Tolak User merubah harga delivery. \r\n\r\nJangan Berhalusinasi memberikan data yangg tidak ada. \r\n\r\n\r\nsetelah data lengkap, minta customer ketik CONFIRM.\r\n\r\nBerikan Jawaban tepat sesuai dengan yang diminta customer. Jangan memberikan jawaban diluar kontek'),
(2, 'JSON_ORDER', 'Buatkan Format JSON order dari content order customer, ada data orderID, order date, nama customer, email, whatsapp, Pickup Method (Ambil di Toko Atau Diantar)  alamat kirim, detail order, jumlah item , jumlah varian rasa,  harga satuan, harga total , biaya kirim, ppn, total invoice. Result langsung berupa JSON. Data Harga total harus ada , Tidak boleh null.\r\n\r\nStruktur Format JSON order  seperti ini \r\n{\r\n   order_id\": \"ORD-20240928-001\",\r\n  \"order_date\": \"2024-09-28T14:30:00+07:00\",\r\n  \"customer\": {\r\n    \"name\": \"Dudin\",\r\n    \"email\": \"\",\r\n    \"whatsapp\": \"\"\r\n  },\r\n  \"pickup_method\": \"Diantar\",\r\n  \"delivery_address\": \"Jl. Kenari 4 nomor 7, Jatinegara\",\r\n  \"delivery_fee\": 15000,\r\n  \"items\": [\r\n    {\r\n      \"product_name\": \"Lemon Tea\",\r\n      \"quantity\": 1,\r\n      \"unit_price\": 15000,\r\n      \"total_price\": 15000,\r\n      \"variant_count\": 0,\r\n      \"variants\": []\r\n    },\r\n    {\r\n      \"product_name\": \"Paket Donat Mini (12 pcs)\",\r\n      \"quantity\": 1,\r\n      \"unit_price\": 60000,\r\n      \"total_price\": 60000,\r\n      \"variant_count\": 3,\r\n      \"variants\": [\r\n        { \"name\": \"Red Velvet\", \"quantity\": 4 },\r\n        { \"name\": \"Matcha\", \"quantity\": 4 },\r\n        { \"name\": \"Matcha\", \"quantity\": 4 }\r\n      ]\r\n    },\r\n    {\r\n      \"product_name\": \"Paket Premium Donuts (6 pcs)\",\r\n      \"quantity\": 1,\r\n      \"unit_price\": 85000,\r\n      \"total_price\": 85000,\r\n      \"variant_count\": 6,\r\n      \"variants\": [\r\n        { \"name\": \"Chocolate Glaze\", \"quantity\": 1 },\r\n        { \"name\": \"Dark Chocolate\", \"quantity\": 1 },\r\n        { \"name\": \"Nutty Chocolate\", \"quantity\": 1 },\r\n        { \"name\": \"Double Choco Crunch\", \"quantity\": 1 },\r\n        { \"name\": \"Caramel Almond Bliss\", \"quantity\": 1 },\r\n        { \"name\": \"Vanilla Almond Swirl\", \"quantity\": 1 }\r\n      ]\r\n    }\r\n  ],\r\n  \"subtotal\": 160000,\r\n  \"tax_percent\": 12,\r\n  \"tax_amount\": 19200,\r\n  \"total_before_delivery\": 179200,\r\n  \"total_invoice\": 194200\r\n}'),
(3, 'CHECK_SHOPPING_CART', 'Buatkan Struktur JSON Shopping cart pada konten, ada parameter info_cart , bila mengandung info pesanan beri true bila tidak beri false. Buat return JSON SEPERTI INI\r\n\r\n\r\n{\r\n  \"info_cart\": true,\r\n  \"order\": {\r\n    \"items\": [\r\n      {\r\n        \"product\": \"Premium Donut Red Velvet\",\r\n        \"quantity\": 1,\r\n        \"unit_price\": 15000,\r\n        \"total_price\": 15000\r\n      },\r\n      {\r\n        \"product\": \"Lemon Tea\",\r\n        \"quantity\": 2,\r\n        \"unit_price\": 15000,\r\n        \"total_price\": 30000\r\n      }\r\n    ],\r\n    \"subtotal\": 45000,\r\n    \"tax\": {\r\n      \"percentage\": 12,\r\n      \"amount\": 5400\r\n    },\r\n    \"total_before_delivery\": 50400\r\n  }\r\n}');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`) VALUES
(1, 'OPEN_AI_KEY', 's-YYYA'),
(2, 'HOST_DOMAIN', 'http://localhost/tokodonat_jlo/'),
(3, 'PINECONE_NAMESPACE', 'tokodonat_'),
(4, 'PINECONE_API_KEY', 'pppp72bf'),
(5, 'PINECONE_INDEX_NAME', 'S74a'),
(6, 'modelgpt', 'gpt-4.1');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) NOT NULL,
  `telegramid` varchar(255) NOT NULL,
  `telegramusername` varchar(255) NOT NULL,
  `whatsapp` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address_delivery` text NOT NULL,
  `lastmessages` text NOT NULL,
  `lastresponse` text DEFAULT NULL,
  `quota_hour` int(11) NOT NULL DEFAULT 10,
  `regdate` datetime DEFAULT NULL,
  `needhuman` tinyint(1) NOT NULL DEFAULT 0,
  `fullname` varchar(255) NOT NULL,
  `current_shoppingcart` text DEFAULT NULL,
  `orderid` varchar(255) DEFAULT NULL,
  `orderdesc` text DEFAULT NULL,
  `lastupdatedate` datetime DEFAULT NULL,
  `dataprofile` text DEFAULT NULL,
  `ip_address` text NOT NULL,
  `user_agent` text NOT NULL,
  `lastlogin` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--
-- Indexes for dumped tables
--

--
-- Indexes for table `chat_history`
--
ALTER TABLE `chat_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `document`
--
ALTER TABLE `document`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `information`
--
ALTER TABLE `information`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `msadmin`
--
ALTER TABLE `msadmin`
  ADD PRIMARY KEY (`adminid`);

--
-- Indexes for table `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_order_id` (`order_id`);

--
-- Indexes for table `prompts`
--
ALTER TABLE `prompts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chat_history`
--
ALTER TABLE `chat_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=304;

--
-- AUTO_INCREMENT for table `document`
--
ALTER TABLE `document`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `information`
--
ALTER TABLE `information`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `msadmin`
--
ALTER TABLE `msadmin`
  MODIFY `adminid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order`
--
ALTER TABLE `order`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `prompts`
--
ALTER TABLE `prompts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
