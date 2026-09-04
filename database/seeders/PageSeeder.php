<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\Page;
use Illuminate\Database\Seeder;

final class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'title' => ['en' => 'About Us', 'id' => 'Tentang Kami'],
                'slug' => 'about',
                'content' => [
                    'en' => '<p>Welcome to JIS, the premier digital marketplace for developers, designers, and creatives. We connect talented creators with customers worldwide, offering a curated collection of high-quality digital products including themes, templates, plugins, graphics, and more.</p><p>Founded in 2024, our mission is to empower creators by providing a platform where they can showcase and sell their work, while giving buyers access to premium digital assets that accelerate their projects.</p><p>We carefully review every product submitted to our platform to ensure quality, originality, and value. Our team is dedicated to providing excellent support to both creators and buyers, making JIS the trusted choice for digital commerce.</p>',
                    'id' => '<p>Selamat datang di JIS, marketplace digital terdepan untuk pengembang, desainer, dan kreatif. Kami menghubungkan kreator berbakat dengan pelanggan di seluruh dunia, menawarkan koleksi terkurasi produk digital berkualitas tinggi termasuk tema, template, plugin, grafis, dan lainnya.</p><p>Didirikan pada tahun 2024, misi kami adalah memberdayakan kreator dengan menyediakan platform di mana mereka dapat menampilkan dan menjual karya mereka, sementara memberi pembeli akses ke aset digital premium yang mempercepat proyek mereka.</p><p>Kami meninjau dengan cermat setiap produk yang dikirimkan ke platform kami untuk memastikan kualitas, orisinalitas, dan nilai. Tim kami berdedikasi untuk memberikan dukungan yang sangat baik kepada kreator dan pembeli, menjadikan JIS pilihan tepercaya untuk perdagangan digital.</p>',
                ],
                'status' => ContentStatus::Publish->value,
            ],
            [
                'title' => ['en' => 'Contact', 'id' => 'Kontak'],
                'slug' => 'contact',
                'content' => [
                    'en' => '<p>We\'d love to hear from you! Whether you have a question about our products, need help with your purchase, or want to become a creator on our platform, our team is here to help.</p><h3>Get in Touch</h3><p><strong>Email:</strong> support@jis.test<br><strong>Business Inquiries:</strong> business@jis.test<br><strong>Creator Support:</strong> creators@jis.test</p><h3>Office</h3><p>Jl. Sudirman No. 123<br>Jakarta Pusat, DKI Jakarta 10220<br>Indonesia</p><h3>Hours</h3><p>Monday — Friday: 09:00 — 18:00 WIB<br>Saturday: 09:00 — 15:00 WIB<br>Sunday: Closed</p><p>We strive to respond to all inquiries within 24 hours during business days.</p>',
                    'id' => '<p>Kami senang mendengar dari Anda! Apakah Anda memiliki pertanyaan tentang produk kami, membutuhkan bantuan dengan pembelian Anda, atau ingin menjadi kreator di platform kami, tim kami siap membantu.</p><h3>Hubungi Kami</h3><p><strong>Email:</strong> support@jis.test<br><strong>Inquiry Bisnis:</strong> business@jis.test<br><strong>Dukungan Kreator:</strong> creators@jis.test</p><h3>Kantor</h3><p>Jl. Sudirman No. 123<br>Jakarta Pusat, DKI Jakarta 10220<br>Indonesia</p><h3>Jam Operasional</h3><p>Senin — Jumat: 09:00 — 18:00 WIB<br>Sabtu: 09:00 — 15:00 WIB<br>Minggu: Tutup</p><p>Kami berusaha merespon semua pertanyaan dalam waktu 24 jam selama hari kerja.</p>',
                ],
                'status' => ContentStatus::Publish->value,
            ],
            [
                'title' => ['en' => 'DMCA', 'id' => 'DMCA'],
                'slug' => 'dmca',
                'content' => [
                    'en' => '<p>JIS respects the intellectual property rights of others and expects its users to do the same. In accordance with the Digital Millennium Copyright Act (DMCA), we have adopted a policy that allows for the reporting of alleged copyright infringement.</p><h3>Filing a DMCA Notice</h3><p>If you believe that your copyrighted work has been copied in a way that constitutes copyright infringement, please provide our designated copyright agent with the following information:</p><ol><li>A physical or electronic signature of the copyright owner or authorized representative.</li><li>Identification of the copyrighted work claimed to have been infringed.</li><li>Identification of the material that is claimed to be infringing, including its location on our platform.</li><li>Your contact information, including address, telephone number, and email address.</li><li>A statement that you have a good faith belief that the disputed use is not authorized.</li><li>A statement, made under penalty of perjury, that the information in your notice is accurate.</li></ol><h3>Submit Notice To</h3><p>Email: dmca@jis.test<br>Subject: DMCA Notice</p><p>We will investigate and take appropriate action, which may include removing or disabling access to the allegedly infringing material.</p>',
                    'id' => '<p>JIS menghormati hak kekayaan intelektual orang lain dan mengharapkan penggunanya melakukan hal yang sama. Sesuai dengan Digital Millennium Copyright Act (DMCA), kami telah mengadopsi kebijakan yang memungkinkan pelaporan pelanggaran hak cipta.</p><h3>Mengajukan Pemberitahuan DMCA</h3><p>Jika Anda yakin bahwa karya berhak cipta Anda telah disalin dengan cara yang melanggar hak cipta, harap berikan agen hak cipta kami yang ditunjuk dengan informasi berikut:</p><ol><li>Tanda tangan fisik atau elektronik dari pemilik hak cipta atau perwakilan yang berwenang.</li><li>Identifikasi karya berhak cipta yang diduga dilanggar.</li><li>Identifikasi materi yang diduga melanggar, termasuk lokasinya di platform kami.</li><li>Informasi kontak Anda, termasuk alamat, nomor telepon, dan alamat email.</li><li>Pernyataan bahwa Anda memiliki keyakinan itikad baik bahwa penggunaan yang disengketakan tidak sah.</li><li>Pernyataan, dibuat di bawah sumpah palsu, bahwa informasi dalam pemberitahuan Anda akurat.</li></ol><h3>Kirim Pemberitahuan Ke</h3><p>Email: dmca@jis.test<br>Subjek: DMCA Notice</p><p>Kami akan menyelidiki dan mengambil tindakan yang sesuai, yang dapat mencakup menghapus atau menonaktifkan akses ke materi yang diduga melanggar.</p>',
                ],
                'status' => ContentStatus::Publish->value,
            ],
            [
                'title' => ['en' => 'Privacy Policy', 'id' => 'Kebijakan Privasi'],
                'slug' => 'privacy-policy',
                'content' => [
                    'en' => '<p>Your privacy is important to us. This Privacy Policy explains how JIS collects, uses, discloses, and safeguards your information when you visit our platform.</p><h3>Information We Collect</h3><p>We may collect personal information such as your name, email address, billing address, and payment information when you create an account, make a purchase, or contact our support team.</p><h3>How We Use Your Information</h3><p>We use your information to process transactions, provide customer support, improve our services, send important updates, and personalize your experience on our platform.</p><h3>Data Security</h3><p>We implement industry-standard security measures to protect your personal information, including encryption, secure servers, and regular security audits.</p><h3>Third-Party Services</h3><p>We may share your information with trusted third-party service providers who assist us in operating our platform, processing payments, and delivering products, subject to strict confidentiality agreements.</p><h3>Contact</h3><p>For questions about this Privacy Policy, please contact us at privacy@jis.test.</p>',
                    'id' => '<p>Privasi Anda penting bagi kami. Kebijakan Privasi ini menjelaskan bagaimana JIS mengumpulkan, menggunakan, mengungkapkan, dan melindungi informasi Anda saat Anda mengunjungi platform kami.</p><h3>Informasi yang Kami Kumpulkan</h3><p>Kami dapat mengumpulkan informasi pribadi seperti nama, alamat email, alamat penagihan, dan informasi pembayaran saat Anda membuat akun, melakukan pembelian, atau menghubungi tim dukungan kami.</p><h3>Bagaimana Kami Menggunakan Informasi Anda</h3><p>Kami menggunakan informasi Anda untuk memproses transaksi, memberikan dukungan pelanggan, meningkatkan layanan kami, mengirim pembaruan penting, dan mempersonalisasi pengalaman Anda di platform kami.</p><h3>Keamanan Data</h3><p>Kami menerapkan langkah-langkah keamanan standar industri untuk melindungi informasi pribadi Anda, termasuk enkripsi, server aman, dan audit keamanan rutin.</p><h3>Layanan Pihak Ketiga</h3><p>Kami dapat membagikan informasi Anda dengan penyedia layanan pihak ketiga tepercaya yang membantu kami mengoperasikan platform, memproses pembayaran, dan mengirimkan produk, dengan tunduk pada perjanjian kerahasiaan yang ketat.</p><h3>Kontak</h3><p>Untuk pertanyaan tentang Kebijakan Privasi ini, silakan hubungi kami di privacy@jis.test.</p>',
                ],
                'status' => ContentStatus::Publish->value,
            ],
            [
                'title' => ['en' => 'Terms of Service', 'id' => 'Syarat & Ketentuan'],
                'slug' => 'terms-of-service',
                'content' => [
                    'en' => '<p>These Terms of Service govern your use of the JIS platform. By accessing or using our platform, you agree to be bound by these terms.</p><h3>Account Registration</h3><p>You must create an account to purchase products or become a creator. You are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account.</p><h3>Purchases and Payments</h3><p>All prices are listed in the specified currency and are subject to applicable taxes. Payments are processed securely through our payment partners. Upon successful payment, you will receive immediate access to your purchased digital products.</p><h3>Creator Obligations</h3><p>Creators warrant that their products are original, do not infringe any third-party rights, and are properly described. Creators are responsible for providing support and updates as specified in their product listings.</p><h3>Refund Policy</h3><p>Refunds are handled in accordance with our Refund Policy. Please refer to the Refund Policy page for detailed information.</p><h3>Limitation of Liability</h3><p>JIS shall not be liable for any indirect, incidental, special, consequential, or punitive damages arising from your use of the platform.</p>',
                    'id' => '<p>Syarat & Ketentuan ini mengatur penggunaan Anda atas platform JIS. Dengan mengakses atau menggunakan platform kami, Anda setuju untuk terikat oleh ketentuan ini.</p><h3>Pendaftaran Akun</h3><p>Anda harus membuat akun untuk membeli produk atau menjadi kreator. Anda bertanggung jawab untuk menjaga kerahasiaan kredensial akun Anda dan untuk semua aktivitas yang terjadi di bawah akun Anda.</p><h3>Pembelian dan Pembayaran</h3><p>Semua harga tercantum dalam mata uang yang ditentukan dan dapat dikenakan pajak yang berlaku. Pembayaran diproses secara aman melalui mitra pembayaran kami. Setelah pembayaran berhasil, Anda akan menerima akses langsung ke produk digital yang Anda beli.</p><h3>Kewajiban Kreator</h3><p>Kreator menjamin bahwa produk mereka asli, tidak melanggar hak pihak ketiga, dan dideskripsikan dengan benar. Kreator bertanggung jawab untuk memberikan dukungan dan pembaruan sebagaimana ditentukan dalam daftar produk mereka.</p><h3>Kebijakan Pengembalian</h3><p>Pengembalian dana ditangani sesuai dengan Kebijakan Pengembalian kami. Silakan merujuk ke halaman Kebijakan Pengembalian untuk informasi terperinci.</p><h3>Batas Tanggung Jawab</h3><p>JIS tidak bertanggung jawab atas kerusakan tidak langsung, insidental, khusus, konsekuensial, atau punitif yang timbul dari penggunaan Anda atas platform.</p>',
                ],
                'status' => ContentStatus::Publish->value,
            ],
            [
                'title' => ['en' => 'Refund Policy', 'id' => 'Kebijakan Pengembalian'],
                'slug' => 'refund-policy',
                'content' => [
                    'en' => '<p>We want you to be satisfied with your purchase. This Refund Policy outlines the conditions under which refunds may be issued for digital products purchased on JIS.</p><h3>Eligibility</h3><p>Refund requests are evaluated on a case-by-case basis. Generally, refunds may be issued if:</p><ul><li>The product is significantly different from its description.</li><li>The product contains technical issues that prevent its intended use and the creator is unable to resolve them.</li><li>The product was purchased accidentally or fraudulently.</li></ul><h3>Non-Eligible Situations</h3><p>Refunds are generally not provided for:</p><ul><li>Change of mind after downloading the product.</li><li>Compatibility issues with your specific setup that were disclosed in the product description.</li><li>Products that have been used, modified, or resold.</li></ul><h3>Request Process</h3><p>To request a refund, please contact our support team at support@jis.test within 14 days of your purchase. Include your order number and a detailed explanation of the issue.</p><h3>Processing Time</h3><p>Refund requests are processed within 5-7 business days. Approved refunds will be credited to your original payment method.</p>',
                    'id' => '<p>Kami ingin Anda puas dengan pembelian Anda. Kebijakan Pengembalian ini menjelaskan kondisi di mana pengembalian dana dapat diberikan untuk produk digital yang dibeli di JIS.</p><h3>Kelayakan</h3><p>Permintaan pengembalian dana dievaluasi berdasarkan kasus per kasus. Umumnya, pengembalian dana dapat diberikan jika:</p><ul><li>Produk secara signifikan berbeda dari deskripsinya.</li><li>Produk mengandung masalah teknis yang mencegah penggunaan yang dimaksudkan dan kreator tidak dapat menyelesaikannya.</li><li>Produk dibeli secara tidak sengaja atau curang.</li></ul><h3>Situasi yang Tidak Memenuhi Syarat</h3><p>Pengembalian dana umumnya tidak diberikan untuk:</p><ul><li>Perubahan pikiran setelah mengunduh produk.</li><li>Masalah kompatibilitas dengan pengaturan spesifik Anda yang telah diungkapkan dalam deskripsi produk.</li><li>Produk yang telah digunakan, dimodifikasi, atau dijual kembali.</li></ul><h3>Prosedur Permintaan</h3><p>Untuk meminta pengembalian dana, silakan hubungi tim dukungan kami di support@jis.test dalam waktu 14 hari setelah pembelian Anda. Sertakan nomor pesanan Anda dan penjelasan rinci tentang masalah tersebut.</p><h3>Waktu Pemrosesan</h3><p>Permintaan pengembalian dana diproses dalam 5-7 hari kerja. Pengembalian dana yang disetujui akan dikreditkan ke metode pembayaran asli Anda.</p>',
                ],
                'status' => ContentStatus::Publish->value,
            ],
        ];

        foreach ($pages as $data) {
            Page::firstOrCreate(
                ['slug->en' => $data['slug']],
                [
                    'title' => $data['title'],
                    'slug' => $data['slug'],
                    'content' => $data['content'],
                    'status' => $data['status'],
                ]
            );
        }
    }
}
