<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

final class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $keys = [
            'site_title' => [
                'en' => 'DKI Konsultan - Trusted Ship Design and Construction Consultant',
                'id' => 'DKI Konsultan - Konsultan Desain dan Pembangunan Kapal Terpercaya',
            ],
            'site_description' => [
                'en' => 'Trusted ship design and construction consulting services in Indonesia with years of experience in the maritime industry.',
                'id' => 'Jasa konsultan desain dan pembangunan kapal terpercaya di Indonesia dengan pengalaman bertahun-tahun dalam industri maritim.',
            ],
            'favicon' => 'favicon.png',
            'logo_dark' => 'logo_dark.svg',
            'logo_light' => 'logo_white.svg',
            'supported_locales' => [
                'en',
                'id',
            ],
            'default_locale' => 'id',
            'hide_default_locale' => true,
            'redirect_enabled' => true,
            'persist_locale' => [
                'cookie' => true,
                'session' => true,
            ],
            'date_format' => 'F j, Y',
            'time_format' => 'g:i a',
            'products_per_page' => 9,
            'posts_per_page' => 9,
            'footer_description' => [
                'en' => 'Trusted ship design and construction consultant in Indonesia with years of experience in the maritime industry.',
                'id' => 'Konsultan desain dan pembangunan kapal terpercaya di Indonesia dengan pengalaman bertahun-tahun dalam industri maritim.',
            ],
            'footer_copyright' => [
                'en' => '&copy; :year DKI Konsultan. All rights reserved.',
                'id' => '&copy; :year DKI Konsultan. Hak cipta dilindungi undang-undang.',
            ],
            'social' => [
                [
                    'url' => '#',
                    'name' => 'Instagram',
                    'icon_svg' => '<svg viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"></path>
</svg>',
                ],
            ],
            'contact_address' => 'Gold Coast Office, Eifel Tower Lantai 12-E. Jl. Pantai Indah Kapuk, Jakarta Utara, DKI Jakarta.',
            'contact_phone' => '+62 812 1314 1751',
            'contact_email' => 'info@dkikonsultan.com',
            'before_head' => null,
            'before_body' => null,
            'seo' => [
                'page_image' => null,
                'page_title' => [
                    'en' => '%title% | %site_title%',
                    'id' => '%title% | %site_title%',
                ],
                'post_image' => null,
                'post_title' => [
                    'en' => '%title% | %site_title%',
                    'id' => null,
                ],
                'product_image' => null,
                'product_title' => [
                    'en' => '%title% | %site_title%',
                    'id' => '%title% | %site_title%',
                ],
                'category_title' => [
                    'en' => '%name% | %site_title%',
                    'id' => '%name% | %site_title%',
                ],
                'homepage_image' => null,
                'homepage_title' => [
                    'en' => '%site_title%',
                    'id' => null,
                ],
                'page_description' => [
                    'en' => '%description%',
                    'id' => '%description%',
                ],
                'post_description' => [
                    'en' => '%excerpt%',
                    'id' => null,
                ],
                'product_description' => [
                    'en' => '%short_description%',
                    'id' => '%short_description%',
                ],
                'category_description' => [
                    'en' => '%description%',
                    'id' => '%description%',
                ],
                'homepage_description' => [
                    'en' => '%site_description%',
                    'id' => null,
                ],
            ],

            // ─── Frontend ──────────────────────────────────────────
            'homepage_blocks' => [
                ['type' => 'hero', 'data' => [
                    'badge_enabled' => true,
                    'badge' => ['en' => 'Specialist in Ship Design & Naval Architecture', 'id' => 'Spesialis Desain Kapal & Arsitektur Perkapalan'],
                    'title' => ['en' => 'Ship Design & Construction Consultant', 'id' => 'Konsultan Desain dan Pembangunan Kapal'],
                    'subtitle' => [
                        'en' => 'To become a leader in the ship design industry by providing innovative and reliable design solutions that comply with national and international standards.',
                        'id' => 'Menjadi pemimpin dalam industri desain kapal dengan menyediakan solusi desain yang inovatif, terpercaya, dan sesuai standar nasional dan internasional.'],
                    'primary_button_label' => ['en' => 'Discuss Your Design', 'id' => 'Konsultasi Desain'],
                    'primary_button_url' => '#contact',
                    'secondary_button_label' => ['en' => 'Our Services', 'id' => 'Layanan Kami'],
                    'secondary_button_url' => '#services',
                    'image' => 'images/hero.png',
                ]],
                ['type' => 'stats', 'data' => ['items' => [
                    ['icon' => 'shield-check', 'value' => 200, 'suffix' => '+', 'label' => ['en' => 'Ship Designs Completed', 'id' => 'Desain Kapal Selesai']],
                    ['icon' => 'shield-check', 'value' => 500, 'suffix' => '+', 'label' => ['en' => 'Approval Drawings Delivered', 'id' => 'Gambar Approval Dikirim']],
                    ['icon' => 'shield-check', 'value' => 98, 'suffix' => '%', 'label' => ['en' => 'Class Approval Rate', 'id' => 'Tingkat Persetujuan Class']],
                    ['icon' => 'users', 'value' => 15, 'suffix' => '+', 'label' => ['en' => 'Naval Architects & Engineers', 'id' => 'Arsitek & Insinyur Kapal']],
                ]]],
                ['type' => 'featured', 'data' => [
                    'label' => ['en' => 'Why Trust Us', 'id' => 'Kenapa Percaya Kami'],
                    'title' => ['en' => 'Ship Design Backed by Real Technical Experience', 'id' => 'Desain Kapal yang Didukung Pengalaman Teknis Nyata'],
                    'description' => ['en' => 'Every design we deliver goes through proper technical calculations — stability, strength, hydrostatics — so your vessel is safe, efficient, and ready for class approval.', 'id' => 'Setiap desain yang kami hasilkan melalui perhitungan teknis yang benar — stabilitas, kekuatan, hidrostatik — sehingga kapal Anda aman, efisien, dan siap approval class.'],
                    'services' => [
                        ['icon' => 'bolt', 'title' => ['en' => 'Years of Ship Design Experience', 'id' => 'Bertahun-Tahun Pengalaman Desain Kapal'], 'description' => ['en' => 'We have been designing ships for over a decade — from fishing vessels to cargo ships, tankers to specialized workboats.', 'id' => 'Kami sudah mendesain kapal selama lebih dari satu dekade — dari kapal ikan hingga kapal kargo, tanker hingga kapal kerja khusus.']],
                        ['icon' => 'shield-check', 'title' => ['en' => 'Class-Compliant Designs', 'id' => 'Desain Sesuai Standar Class'], 'description' => ['en' => 'All our designs follow BKI, Lloyd\'s, DNV, and ABS rules. No last-minute revisions from the classification surveyor.', 'id' => 'Semua desain kami ikuti aturan BKI, Lloyd\'s, DNV, dan ABS. Tidak ada revisi mendadak dari surveyor class.']],
                        ['icon' => 'shield-check', 'title' => ['en' => 'Complete Technical Calculations', 'id' => 'Perhitungan Teknis Lengkap'], 'description' => ['en' => 'Hydrostatic, stability, longitudinal strength, and structural analysis — we run all the numbers so you do not have to guess.', 'id' => 'Hidrostatik, stabilitas, kekuatan longitudinal, dan analisis struktur — kami hitung semua angka biar Anda tidak perlu menerka.']],
                        ['icon' => 'cube', 'title' => ['en' => 'Detailed 3D Modeling & Drawings', 'id' => 'Pemodelan 3D & Gambar Detail'], 'description' => ['en' => 'From lines plan to construction profiles, we provide clear, detailed drawings that your workshop can work with directly.', 'id' => 'Dari lines plan hingga profil konstruksi, kami sediakan gambar detail yang langsung bisa dipakai bengkel kerja Anda.']],
                        ['icon' => 'clipboard-document', 'title' => ['en' => 'End-to-End Class Approval Support', 'id' => 'Pendampingan Approval Class Penuh'], 'description' => ['en' => 'We help you through the entire class approval process — from submission to revision — so your design gets approved faster.', 'id' => 'Kami bantu Anda dari pengajuan hingga revisi — biar desain Anda cepat disetujui class.']],
                        ['icon' => 'chart-bar', 'title' => ['en' => 'Cost-Efficient Design Optimization', 'id' => 'Optimasi Desain Hemat Biaya'], 'description' => ['en' => 'We optimize structural designs to reduce material costs without compromising safety or class compliance — saving you money from the drawing board.', 'id' => 'Kami optimasi desain struktur untuk mengurangi biaya material tanpa mengorbankan keselamatan atau kepatuhan class — hemat biaya sejak dari meja gambar.']],
                    ],
                ]],
                ['type' => 'services', 'data' => [
                    'label' => ['en' => 'Our Services', 'id' => 'Layanan Kami'],
                    'title' => ['en' => 'Complete Ship Design Services — From A to Z', 'id' => 'Layanan Desain Kapal Lengkap — Dari A sampai Z'],
                    'description' => ['en' => 'We cover every stage of ship design, from initial concept to detailed engineering drawings ready for class submission.', 'id' => 'Kami mencakup semua tahap desain kapal, dari konsep awal hingga gambar rekayasa detail yang siap diajukan ke class.'],
                    'items' => [
                        ['icon' => 'pencil-square', 'title' => ['en' => 'Concept Design & Approval Package', 'id' => 'Desain Konsep & Paket Approval'], 'description' => ['en' => 'Complete set of approval drawings including Key Plan, General Arrangement, Midship Section, Lines Plan, Construction Profiles, Machinery Arrangement, and Electrical Diagrams.', 'id' => 'Paket lengkap gambar approval meliputi Key Plan, General Arrangement, Midship Section, Lines Plan, Profil Konstruksi, Tata Letak Mesin, dan Diagram Kelistrikan.']],
                        ['icon' => 'cube', 'title' => ['en' => 'Structural Analysis & FEM', 'id' => 'Analisis Struktur & FEM'], 'description' => ['en' => 'Longitudinal strength assessment, fatigue analysis, and Finite Element Method analysis for critical areas like cargo holds, engine room, and superstructure.', 'id' => 'Penilaian kekuatan longitudinal, analisis kelelahan, dan analisis Metode Elemen Hingga untuk area kritis seperti palka, kamar mesin, dan superstruktur.']],
                        ['icon' => 'arrows-right-left', 'title' => ['en' => 'Stability Analysis — Intact & Damage', 'id' => 'Analisis Stabilitas — Utuh & Kerusakan'], 'description' => ['en' => 'Full stability analysis for all loading conditions per IMO criteria, including damage stability for subdivision and floodability. Complete stability booklet included.', 'id' => 'Analisis stabilitas penuh untuk semua kondisi muatan sesuai kriteria IMO, termasuk stabilitas kerusakan. Stability booklet lengkap disertakan.']],
                        ['icon' => 'beaker', 'title' => ['en' => 'Hydrodynamic & CFD Analysis', 'id' => 'Analisis Hidrodinamik & CFD'], 'description' => ['en' => 'Hull form optimization, resistance prediction, wake field analysis, and propeller design — helping you achieve better fuel efficiency and seakeeping.', 'id' => 'Optimasi bentuk lambung, prediksi hambatan, analisis medan arus, dan desain propeller — membantu Anda dapatkan efisiensi bahan bakar lebih baik.']],
                        ['icon' => 'link', 'title' => ['en' => 'Mooring, Anchoring & Outfitting', 'id' => 'Mooring, Jangkar & Perlengkapan'], 'description' => ['en' => 'Mooring arrangement, anchoring system assessment, towing calculations, and outfitting drawings — all included in your construction profile package.', 'id' => 'Pengaturan mooring, penilaian sistem jangkar, perhitungan towing, dan gambar perlengkapan — semua termasuk dalam paket profil konstruksi Anda.']],
                    ]]],
                ['type' => 'projects', 'data' => [
                    'label' => ['en' => 'Portfolio', 'id' => 'Portofolio'],
                    'title' => ['en' => 'Ships We Have Designed', 'id' => 'Kapal yang Kami Desain'],
                    'description' => ['en' => 'From fishing vessels to cargo ships — here are some of the ship designs we have completed and delivered to clients.', 'id' => 'Dari kapal ikan hingga kapal kargo — berikut beberapa desain kapal yang sudah kami selesaikan untuk klien.'],
                    'count' => null,
                    'sort_by' => 'latest',
                ]],
                ['type' => 'featured-products', 'data' => [
                    'label' => ['en' => 'Design Packages', 'id' => 'Paket Desain'],
                    'title' => ['en' => 'Ready-to-Use Ship Design Products', 'id' => 'Produk Desain Kapal Siap Pakai'],
                    'description' => ['en' => 'Browse our collection of ship design products — purchase and download ready-to-build design packages.', 'id' => 'Jelajahi koleksi produk desain kapal kami — beli dan unduh paket desain siap bangun.'],
                    'count' => 6,
                    'sort_by' => 'latest',
                    'show_view_all' => true,
                ]],
                ['type' => 'latest-posts', 'data' => [
                    'label' => ['en' => 'Insights', 'id' => 'Wawasan'],
                    'title' => ['en' => 'Latest from Our Naval Architects', 'id' => 'Terbaru dari Arsitek Kapal Kami'],
                    'description' => ['en' => 'Articles and insights about ship design, naval architecture, and the maritime industry.', 'id' => 'Artikel dan wawasan seputar desain kapal, arsitektur perkapalan, dan industri maritim.'],
                    'count' => 3,
                    'sort_by' => 'latest',
                    'show_view_all' => true,
                ]],
                ['type' => 'testimonials', 'data' => [
                    'label' => ['en' => 'Client Stories', 'id' => 'Cerita Klien'],
                    'title' => ['en' => 'What Ship Owners Say About Our Designs', 'id' => 'Kata Pemilik Kapal tentang Desain Kami'],
                    'description' => ['en' => '', 'id' => ''],
                    'items' => [
                        ['name' => 'Capt. Andi Wijaya', 'company' => 'PT Pelayaran Samudra', 'role' => ['en' => 'Fleet Director', 'id' => 'Direktur Armada'], 'quote' => ['en' => 'The approval process with BKI went smoothly thanks to DKI\'s complete and accurate design package. No back-and-forth revisions like we had with previous consultants.', 'id' => 'Proses approval di BKI berjalan lancar berkat paket desain DKI yang lengkap dan akurat. Tidak ada bolak-balik revisi seperti konsultan sebelumnya.']],
                        ['name' => 'Budi Santoso', 'company' => 'PT Perikanan Nusantara', 'role' => ['en' => 'Technical Manager', 'id' => 'Manajer Teknis'], 'quote' => ['en' => 'The stability analysis and structural optimization helped us reduce material costs while still meeting all safety requirements. Great value.', 'id' => 'Analisis stabilitas dan optimasi struktur membantu kami hemat biaya material tapi tetap memenuhi semua persyaratan keselamatan. Nilai tambah banget.']],
                        ['name' => 'Hendra Gunawan', 'company' => 'PT Multi Sarana Shipping', 'role' => ['en' => 'Owner', 'id' => 'Pemilik'], 'quote' => ['en' => 'Had our tanker design approved by DNV on the first submission. DKI knows what class surveyors look for — that saved us months.', 'id' => 'Desain tanker kami disetujui DNV di pengajuan pertama. DKI paham yang dicari surveyor class — itu menghemat kami berbulan-bulan.']],
                    ],
                ]],
                ['type' => 'partners', 'data' => [
                    'label' => ['en' => 'Affiliations', 'id' => 'Afiliasi'],
                    'title' => ['en' => 'Our Designs Meet International Standards', 'id' => 'Desain Kami Memenuhi Standar Internasional'],
                    'description' => ['en' => 'We work with the world\'s leading classification societies to ensure every design meets the required safety and quality standards.', 'id' => 'Kami bekerja sama dengan lembaga klasifikasi terkemuka dunia untuk memastikan setiap desain memenuhi standar keselamatan dan kualitas yang dipersyaratkan.'],
                    'items' => [
                        ['name' => 'BKI', 'logo' => '', 'url' => '#'],
                        ['name' => 'Lloyd\'s Register', 'logo' => '', 'url' => '#'],
                        ['name' => 'DNV', 'logo' => '', 'url' => '#'],
                        ['name' => 'ABS', 'logo' => '', 'url' => '#'],
                    ],
                ]],
                ['type' => 'cta', 'data' => [
                    'label' => ['en' => 'Start Your Ship Design Project', 'id' => 'Mulai Proyek Desain Kapal Anda'],
                    'title' => ['en' => 'Have a Vessel in Mind? Let\'s Discuss the Design.', 'id' => 'Ada Kapal yang Mau Didesain? Mari Kita Bahas.'],
                    'button_label' => ['en' => 'Schedule a Design Consultation', 'id' => 'Jadwalkan Konsultasi Desain'],
                    'button_url' => '#contact',
                ]],
            ],
        ];

        Setting::set($keys);
    }
}
