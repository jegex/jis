<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Post;
use App\Models\Product;
use App\Models\Tag;
use App\Models\User;
use Exception;
use Illuminate\Database\Seeder;

final class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->createTags();
        $this->createProductCategories();
        $this->createPostCategories();
        $this->createUsers();
        $this->createProducts();
        $this->createPosts();
        $this->createProjects();
    }

    private function createTags(): void
    {
        $tags = [
            ['name' => 'Naval Architecture', 'slug' => 'naval-architecture'],
            ['name' => 'Hull Design', 'slug' => 'hull-design'],
            ['name' => 'Ship Stability', 'slug' => 'ship-stability'],
            ['name' => 'RO-RO Ferry', 'slug' => 'ro-ro-ferry'],
            ['name' => 'General Cargo', 'slug' => 'general-cargo'],
            ['name' => 'Tug Boat', 'slug' => 'tug-boat'],
            ['name' => 'Oil Tanker', 'slug' => 'oil-tanker'],
            ['name' => 'Barge', 'slug' => 'barge'],
            ['name' => 'SPOB', 'slug' => 'spob'],
            ['name' => 'Ship Building', 'slug' => 'ship-building'],
            ['name' => 'Marine Engineering', 'slug' => 'marine-engineering'],
            ['name' => 'Featured Project', 'slug' => 'featured-project'],
        ];

        foreach ($tags as $tag) {
            Tag::firstOrCreate(['slug' => $tag['slug']], $tag);
        }
    }

    private function createProductCategories(): void
    {
        $categories = [
            [
                'name' => ['en' => 'Ship Design Services', 'id' => 'Jasa Desain Kapal'],
                'description' => ['en' => 'Complete ship design packages from concept to production', 'id' => 'Paket desain kapal lengkap dari konsep hingga produksi'],
                'slug' => 'ship-design-services',
                'type' => CategoryType::Product,
                'is_published' => true,
            ],
            [
                'name' => ['en' => 'Technical Drawings', 'id' => 'Gambar Teknis'],
                'description' => ['en' => 'Detailed engineering drawings and technical documentation', 'id' => 'Gambar teknik detail dan dokumentasi teknis'],
                'slug' => 'technical-drawings',
                'type' => CategoryType::Product,
                'is_published' => true,
            ],
            [
                'name' => ['en' => 'Engineering Analysis', 'id' => 'Analisis Teknik'],
                'description' => ['en' => 'Structural, stability, and hydrodynamic analysis services', 'id' => 'Layanan analisis struktur, stabilitas, dan hidrodinamika'],
                'slug' => 'engineering-analysis',
                'type' => CategoryType::Product,
                'is_published' => true,
            ],
            [
                'name' => ['en' => 'Consultation & Advisory', 'id' => 'Konsultasi & Advisory'],
                'description' => ['en' => 'Expert consultation for ship design and marine projects', 'id' => 'Konsultasi ahli untuk desain kapal dan proyek kelautan'],
                'slug' => 'consultation-advisory',
                'type' => CategoryType::Product,
                'is_published' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => $category['slug'], 'type' => $category['type']],
                $category
            );
        }
    }

    private function createPostCategories(): void
    {
        $categories = [
            [
                'name' => ['en' => 'Naval Architecture', 'id' => 'Arsitektur Kapal'],
                'description' => ['en' => 'Fundamentals and advanced topics in naval architecture and ship design', 'id' => 'Dasar dan topik lanjutan dalam arsitektur kapal dan desain kapal'],
                'slug' => 'naval-architecture',
                'type' => CategoryType::Post,
                'is_published' => true,
            ],
            [
                'name' => ['en' => 'Ship Building', 'id' => 'Pembangunan Kapal'],
                'description' => ['en' => 'Ship construction techniques, yard operations, and project management', 'id' => 'Teknik konstruksi kapal, operasi galangan, dan manajemen proyek'],
                'slug' => 'ship-building',
                'type' => CategoryType::Post,
                'is_published' => true,
            ],
            [
                'name' => ['en' => 'Marine Engineering', 'id' => 'Teknik Kelautan'],
                'description' => ['en' => 'Marine propulsion, systems engineering, and offshore technology', 'id' => 'Propulsi kelautan, rekayasa sistem, dan teknologi lepas pantai'],
                'slug' => 'marine-engineering',
                'type' => CategoryType::Post,
                'is_published' => true,
            ],
            [
                'name' => ['en' => 'Industry Insights', 'id' => 'Wawasan Industri'],
                'description' => ['en' => 'Maritime industry trends, regulations, and market analysis', 'id' => 'Tren industri maritim, regulasi, dan analisis pasar'],
                'slug' => 'industry-insights',
                'type' => CategoryType::Post,
                'is_published' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => $category['slug'], 'type' => $category['type']],
                $category
            );
        }
    }

    private function createUsers(): void
    {
        $users = [
            ['name' => 'Admin', 'email' => 'admin@jis.test', 'is_admin' => true],
            ['name' => 'Rina Wijaya', 'email' => 'rina@jis.test', 'is_admin' => false],
            ['name' => 'Budi Santoso', 'email' => 'budi@jis.test', 'is_admin' => false],
        ];

        foreach ($users as $data) {
            User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => bcrypt('password'),
                    'is_admin' => $data['is_admin'],
                    'email_verified_at' => now(),
                ]
            );
        }
    }

    private function createProducts(): void
    {
        $productCategories = Category::where('type', CategoryType::Product)->get();
        $tags = Tag::all();

        $productImages = [
            'https://images.unsplash.com/photo-1585713181935-d5f622cc2415?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1605745341112-85968b19335b?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1583157048761-ac1dba033233?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1580166463495-ab4d21922c22?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1613690399151-65ea69478674?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1606185540834-d6e7483ee1a4?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1511316695145-4992006ffddb?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1542986386-660ccbbedaf8?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1554254648-2d58a1bc3fd5?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1576423596782-8c5478efd11f?w=800&h=600&fit=crop',
        ];

        $products = [
            [
                'title' => ['en' => 'Full Ship Design Package', 'id' => 'Paket Desain Kapal Lengkap'],
                'description' => [
                    'en' => '<p>A complete ship design service from concept development through to production-ready drawings. This comprehensive package covers all aspects of naval architecture and marine engineering required to bring your vessel from idea to reality.</p><p><strong>Deliverables include:</strong></p><ul><li>Preliminary design and feasibility study</li><li>General arrangement drawings</li><li>Lines plan and hull form optimization</li><li>Structural design and scantling calculation</li><li>Stability analysis (intact and damage)</li><li>Propulsion system design and power estimation</li><li>Electrical system design</li><li>Outfitting and equipment specification</li><li>Detailed production drawings</li></ul><p>Suitable for all vessel types including passenger ferries, cargo ships, tankers, tugs, barges, and special purpose vessels.</p>',
                    'id' => '<p>Layanan desain kapal lengkap dari pengembangan konsep hingga gambar siap produksi. Paket komprehensif ini mencakup semua aspek arsitektur kapal dan teknik kelautan yang diperlukan untuk mewujudkan kapal Anda dari ide menjadi kenyataan.</p><p><strong>Hasil kerja meliputi:</strong></p><ul><li>Desain awal dan studi kelayakan</li><li>Gambar rencana umum</li><li>Rencana garis dan optimasi bentuk lambung</li><li>Desain struktur dan perhitungan scantling</li><li>Analisis stabilitas (utuh dan rusak)</li><li>Desain sistem propulsi dan estimasi daya</li><li>Desain sistem kelistrikan</li><li>Spesifikasi outfitting dan peralatan</li><li>Gambar produksi detail</li></ul><p>Cocok untuk semua jenis kapal termasuk ferry penumpang, kapal kargo, tanker, tug, tongkang, dan kapal tujuan khusus.</p>',
                ],
                'short_description' => ['en' => 'End-to-end ship design from concept to production drawings',
                    'id' => 'Desain kapal end-to-end dari konsep hingga gambar produksi',
                ],
                'price' => rand(9, 99),
                'category_id' => $productCategories[0]->id,
            ],
            [
                'title' => ['en' => 'Hull Line Design', 'id' => 'Desain Garis Lambung'],
                'description' => [
                    'en' => '<p>Optimized hull form design tailored to your vessel\'s operational profile. Using advanced computational fluid dynamics (CFD) tools, we develop hull lines that minimize resistance while maximizing cargo capacity and stability.</p><p><strong>Deliverables include:</strong></p><ul><li>Lines plan with offset table</li><li>Hydrostatic curves and stability data</li><li>Resistance and power prediction using Holtrop-Mennen method</li><li>CFD analysis for hull form optimization</li><li>Trim and stability booklet</li><li>3D hull surface model (IGES/STEP format)</li></ul><p>Our hull design expertise covers displacement hulls, semi-planing hulls, and multi-hull configurations.</p>',
                    'id' => '<p>Desain bentuk lambung yang dioptimalkan sesuai dengan profil operasional kapal Anda. Menggunakan alat computational fluid dynamics (CFD) canggih, kami mengembangkan garis lambung yang meminimalkan hambatan sambil memaksimalkan kapasitas kargo dan stabilitas.</p><p><strong>Hasil kerja meliputi:</strong></p><ul><li>Rencana garis dengan tabel offset</li><li>Kurva hidrostatis dan data stabilitas</li><li>Prediksi hambatan dan daya menggunakan metode Holtrop-Mennen</li><li>Analisis CFD untuk optimasi bentuk lambung</li><li>Buku trim dan stabilitas</li><li>Model permukaan lambung 3D (format IGES/STEP)</li></ul><p>Keahlian desain lambung kami mencakup lambung displacement, semi-planing, dan konfigurasi multi-lambung.</p>',
                ],
                'short_description' => ['en' => 'CFD-optimized hull form design for minimum resistance',
                    'id' => 'Desain bentuk lambung yang dioptimalkan CFD untuk hambatan minimal',
                ],
                'price' => rand(9, 99),
                'category_id' => $productCategories[0]->id,
            ],
            [
                'title' => ['en' => 'Ship Stability Analysis', 'id' => 'Analisis Stabilitas Kapal'],
                'description' => [
                    'en' => '<p>Comprehensive stability analysis ensuring your vessel meets all applicable stability criteria including IMO intact stability code, damage stability requirements, and classification society rules.</p><p><strong>Deliverables include:</strong></p><ul><li>Intact stability analysis for all loading conditions</li><li>Damage stability analysis (probabilistic and deterministic)</li><li>Cross curves of stability (KN curves)</li><li>Weather criterion evaluation</li><li>Grain loading stability assessment</li><li>Stability booklet and loading manual</li><li>Lightship survey and inclining experiment support</li></ul><p>We work with all major classification societies including BKI, Lloyd\'s Register, DNV, ABS, and Bureau Veritas.</p>',
                    'id' => '<p>Analisis stabilitas komprehensif yang memastikan kapal Anda memenuhi semua kriteria stabilitas yang berlaku termasuk IMO intact stability code, persyaratan damage stability, dan peraturan badan klasifikasi.</p><p><strong>Hasil kerja meliputi:</strong></p><ul><li>Analisis stabilitas utuh untuk semua kondisi muatan</li><li>Analisis stabilitas rusak (probabilistik dan deterministik)</li><li>Kurva silang stabilitas (kurva KN)</li><li>Evaluasi kriteria cuaca</li><li>Penilaian stabilitas muatan biji-bijian</li><li>Buku stabilitas dan manual pemuatan</li><li>Survei lightsip dan dukungan percobaan inclining</li></ul><p>Kami bekerja dengan semua badan klasifikasi utama termasuk BKI, Lloyd\'s Register, DNV, ABS, dan Bureau Veritas.</p>',
                ],
                'short_description' => ['en' => 'IMO-compliant intact and damage stability calculations',
                    'id' => 'Perhitungan stabilitas utuh dan rusak sesuai IMO',
                ],
                'price' => rand(9, 99),
                'category_id' => $productCategories[2]->id,
            ],
            [
                'title' => ['en' => 'Technical Drawing Set', 'id' => 'Set Gambar Teknis'],
                'description' => [
                    'en' => '<p>A complete set of production-ready technical drawings for your vessel. Our detailed engineering drawings follow classification society standards and are suitable for use in shipyard fabrication.</p><p><strong>Deliverable drawings include:</strong></p><ul><li>General arrangement plan</li><li>Lines plan and offsets</li><li>Shell expansion plan</li><li>Midship section and structural details</li><li>Tank and piping arrangement</li><li>Electrical wiring diagram</li><li>Engine room arrangement</li><li>Deck equipment arrangement</li><li>Outfitting details</li><li>Construction profile and deck plans</li></ul><p>Drawings are provided in both digital (DWG/PDF) and hard copy formats.</p>',
                    'id' => '<p>Set lengkap gambar teknis siap produksi untuk kapal Anda. Gambar teknik detail kami mengikuti standar badan klasifikasi dan cocok digunakan untuk fabrikasi galangan kapal.</p><p><strong>Gambar yang dihasilkan meliputi:</strong></p><ul><li>Rencana umum</li><li>Rencana garis dan offset</li><li>Rencana pengembangan kulit</li><li>Potongan tengah kapal dan detail struktur</li><li>Susunan tangki dan pipa</li><li>Diagram kelistrikan</li><li>Susunan kamar mesin</li><li>Susunan peralatan dek</li><li>Detail outfitting</li><li>Profil konstruksi dan rencana geladak</li></ul><p>Gambar disediakan dalam format digital (DWG/PDF) dan cetak.</p>',
                ],
                'short_description' => ['en' => 'Production-ready engineering drawings for shipyard fabrication',
                    'id' => 'Gambar teknik siap produksi untuk fabrikasi galangan',
                ],
                'price' => rand(9, 99),
                'category_id' => $productCategories[1]->id,
            ],
            [
                'title' => ['en' => 'Design Feasibility Study', 'id' => 'Studi Kelayakan Desain'],
                'description' => [
                    'en' => '<p>A thorough feasibility assessment conducted before committing to full design and construction. We evaluate technical, economic, and regulatory aspects to ensure your project is viable and compliant.</p><p><strong>Study covers:</strong></p><ul><li>Vessel type selection and concept development</li><li>Route analysis and operational requirements</li><li>Preliminary sizing and principal dimensions</li><li>Speed-power trade-off analysis</li><li>Comparative cost estimation (CAPEX and OPEX)</li><li>Regulatory compliance review</li><li>Risk assessment and mitigation strategies</li><li>Project timeline and milestone planning</li></ul><p>Ideal for ship owners and investors evaluating new vessel construction or fleet expansion projects.</p>',
                    'id' => '<p>Penilaian kelayakan menyeluruh yang dilakukan sebelum berkomitmen pada desain dan konstruksi penuh. Kami mengevaluasi aspek teknis, ekonomi, dan peraturan untuk memastikan proyek Anda layak dan sesuai ketentuan.</p><p><strong>Cakupan studi:</strong></p><ul><li>Pemilihan jenis kapal dan pengembangan konsep</li><li>Analisis rute dan persyaratan operasional</li><li>Penentuan ukuran awal dan dimensi utama</li><li>Analisis trade-off kecepatan-daya</li><li>Estimasi biaya komparatif (CAPEX dan OPEX)</li><li>Tinjauan kepatuhan regulasi</li><li>Penilaian risiko dan strategi mitigasi</li><li>Jadwal proyek dan perencanaan milestone</li></ul><p>Ideal untuk pemilik kapal dan investor yang mengevaluasi proyek pembangunan kapal baru atau ekspansi armada.</p>',
                ],
                'short_description' => ['en' => 'Technical and economic feasibility assessment for vessel projects',
                    'id' => 'Penilaian kelayakan teknis dan ekonomi untuk proyek kapal',
                ],
                'price' => rand(9, 99),
                'category_id' => $productCategories[3]->id,
            ],
            [
                'title' => ['en' => 'Retrofit & Conversion Design', 'id' => 'Desain Retrofit & Konversi'],
                'description' => [
                    'en' => '<p>Expert design services for vessel retrofitting, conversion, and life extension projects. Whether you need to change a vessel\'s function, upgrade its systems, or extend its operational life, we provide comprehensive engineering solutions.</p><p><strong>Services include:</strong></p><ul><li>Feasibility study for conversion projects</li><li>Structural modification and reinforcement design</li><li>New system integration design</li><li>Weight control and stability impact assessment</li><li>Regulatory compliance for converted vessels</li><li>Scantling and strength reassessment</li><li>Production drawings for conversion work</li></ul><p>Past projects include barge-to-barge conversion, passenger ferry refurbishment, and tanker life extension programs.</p>',
                    'id' => '<p>Layanan desain ahli untuk proyek retrofit kapal, konversi, dan perpanjangan umur. Apakah Anda perlu mengubah fungsi kapal, meningkatkan sistemnya, atau memperpanjang umur operasionalnya, kami menyediakan solusi teknik yang komprehensif.</p><p><strong>Layanan meliputi:</strong></p><ul><li>Studi kelayakan proyek konversi</li><li>Modifikasi struktur dan desain perkuatan</li><li>Desain integrasi sistem baru</li><li>Kontrol berat dan penilaian dampak stabilitas</li><li>Kepatuhan regulasi untuk kapal konversi</li><li>Penilaian ulang scantling dan kekuatan</li><li>Gambar produksi untuk pekerjaan konversi</li></ul><p>Proyek sebelumnya termasuk konversi tongkang, refurbishment ferry penumpang, dan program perpanjangan umur tanker.</p>',
                ],
                'short_description' => ['en' => 'Vessel conversion, retrofit, and life extension engineering',
                    'id' => 'Rekayasa konversi, retrofit, dan perpanjangan umur kapal',
                ],
                'price' => rand(9, 99),
                'category_id' => $productCategories[0]->id,
            ],
            [
                'title' => ['en' => 'Structural Calculation', 'id' => 'Perhitungan Struktur'],
                'description' => [
                    'en' => '<p>Detailed structural analysis and finite element analysis (FEA) for ship hull structures. We ensure your vessel meets classification society strength requirements while optimizing steel weight for cost efficiency.</p><p><strong>Deliverables include:</strong></p><ul><li>3D FEA model of hull structure</li><li>Global strength analysis (longitudinal and torsional)</li><li>Local strength analysis for critical areas</li><li>Scantling calculation per class rules</li><li>Fatigue life assessment</li><li>Buckling strength analysis</li><li>Weight optimization recommendations</li><li>Structural design report</li></ul><p>Our analysis covers all structural elements including bottom, side, deck, bulkheads, and superstructure.</p>',
                    'id' => '<p>Analisis struktur detail dan finite element analysis (FEA) untuk struktur lambung kapal. Kami memastikan kapal Anda memenuhi persyaratan kekuatan badan klasifikasi sambil mengoptimalkan berat baja untuk efisiensi biaya.</p><p><strong>Hasil kerja meliputi:</strong></p><ul><li>Model FEA 3D struktur lambung</li><li>Analisis kekuatan global (longitudinal dan torsional)</li><li>Analisis kekuatan lokal untuk area kritis</li><li>Perhitungan scantling sesuai aturan kelas</li><li>Penilaian umur fatik</li><li>Analisis kekuatan tekuk</li><li>Rekomendasi optimasi berat</li><li>Laporan desain struktur</li></ul><p>Analisis kami mencakup semua elemen struktur termasuk alas, sisi, geladak, sekat, dan bangunan atas.</p>',
                ],
                'short_description' => ['en' => 'FEA-based structural strength analysis and scantling calculation',
                    'id' => 'Analisis kekuatan struktur berbasis FEA dan perhitungan scantling',
                ],
                'price' => rand(9, 99),
                'category_id' => $productCategories[2]->id,
            ],
            [
                'title' => ['en' => 'Shipyard Support Service', 'id' => 'Layanan Dukungan Galangan'],
                'description' => [
                    'en' => '<p>On-site technical support during the vessel construction phase. Our experienced naval architects and marine engineers work directly with your shipyard team to ensure the design is implemented correctly and efficiently.</p><p><strong>Support includes:</strong></p><ul><li>On-site engineering representation during construction</li><li>Technical clarification and drawing interpretation</li><li>Construction quality inspection and approval</li><li>Resolution of design issues during fabrication</li><li>Fit-up and alignment supervision</li><li>Welding procedure specification review</li><li>Sea trial planning and execution support</li><li>Final documentation and as-built drawing preparation</li></ul><p>Available for both domestic and international projects with flexible engagement terms.</p>',
                    'id' => '<p>Dukungan teknis di lokasi selama fase konstruksi kapal. Arsitek kapal dan insinyur kelautan berpengalaman kami bekerja langsung dengan tim galangan Anda untuk memastikan desain diimplementasikan dengan benar dan efisien.</p><p><strong>Dukungan meliputi:</strong></p><ul><li>Perwakilan teknik di lokasi selama konstruksi</li><li>Klarifikasi teknis dan interpretasi gambar</li><li>Inspeksi dan persetujuan kualitas konstruksi</li><li>Penyelesaian masalah desain selama fabrikasi</li><li>Supervisi fit-up dan alignment</li><li>Tinjauan spesifikasi prosedur pengelasan</li><li>Perencanaan dan dukungan pelaksanaan sea trial</li><li>Dokumentasi akhir dan persiapan gambar as-built</li></ul><p>Tersedia untuk proyek dalam dan luar negeri dengan ketentuan keterlibatan yang fleksibel.</p>',
                ],
                'short_description' => ['en' => 'On-site technical support during vessel construction',
                    'id' => 'Dukungan teknis di lokasi selama konstruksi kapal',
                ],
                'price' => rand(9, 99),
                'category_id' => $productCategories[0]->id,
            ],
            [
                'title' => ['en' => 'Outfitting Design Package', 'id' => 'Paket Desain Outfitting'],
                'description' => [
                    'en' => '<p>Comprehensive outfitting design covering all interior and exterior fitments of your vessel. Our designs balance functionality, comfort, and regulatory compliance while optimizing space utilization.</p><p><strong>Design scope includes:</strong></p><ul><li>Accommodation layout and interior design</li><li>Navigation bridge arrangement</li><li>Galley and mess room design</li><li>HVAC system design and duct routing</li><li>Piping system design (fuel, water, bilge, ballast)</li><li>Fire protection and safety equipment layout</li><li>Deck equipment specification and arrangement</li><li>Life-saving appliance arrangement</li><li>Lighting and electrical outlet layout</li><li>Furniture specification and arrangement</li></ul><p>Compliant with SOLAS, MLC, and relevant classification society requirements.</p>',
                    'id' => '<p>Desain outfitting komprehensif yang mencakup semua perlengkapan interior dan eksterior kapal Anda. Desain kami menyeimbangkan fungsionalitas, kenyamanan, dan kepatuhan regulasi sambil mengoptimalkan penggunaan ruang.</p><p><strong>Lingkup desain meliputi:</strong></p><ul><li>Tata letak akomodasi dan desain interior</li><li>Susunan anjungan navigasi</li><li>Desain dapur dan ruang makan</li><li>Desain sistem HVAC dan routing duct</li><li>Desain sistem perpipaan (bahan bakar, air, bilge, ballast)</li><li>Tata letak perlindungan kebakaran dan peralatan keselamatan</li><li>Spesifikasi dan susunan peralatan dek</li><li>Susunan alat penolong</li><li>Tata letak penerangan dan stop kontak</li><li>Spesifikasi dan susunan furnitur</li></ul><p>Sesuai dengan persyaratan SOLAS, MLC, dan badan klasifikasi terkait.</p>',
                ],
                'short_description' => ['en' => 'Complete interior and exterior outfitting design for vessels',
                    'id' => 'Desain outfitting interior dan eksterior lengkap untuk kapal',
                ],
                'price' => rand(9, 99),
                'category_id' => $productCategories[1]->id,
            ],
            [
                'title' => ['en' => 'Weight & Trim Calculation', 'id' => 'Perhitungan Berat & Trim'],
                'description' => [
                    'en' => '<p>Precise weight estimation and trim analysis essential for vessel safety and performance. Our calculations ensure your vessel maintains proper trim and stability throughout all operating conditions.</p><p><strong>Deliverables include:</strong></p><ul><li>Lightweight estimation by weight group</li><li>Deadweight calculation and capacity plan</li><li>Trim analysis for all loading conditions</li><li>Longitudinal strength assessment</li><li>Loading manual and loading instrument data</li><li>Draft survey support</li><li>Intact stability for all conditions</li></ul><p>Critical for new designs, conversions, and any modification that affects vessel weight distribution.</p>',
                    'id' => '<p>Estimasi berat dan analisis trim yang presisi, penting untuk keselamatan dan kinerja kapal. Perhitungan kami memastikan kapal Anda mempertahankan trim dan stabilitas yang tepat di semua kondisi operasi.</p><p><strong>Hasil kerja meliputi:</strong></p><ul><li>Estimasi lightweight per kelompok berat</li><li>Perhitungan deadweight dan rencana kapasitas</li><li>Analisis trim untuk semua kondisi muatan</li><li>Penilaian kekuatan longitudinal</li><li>Manual pemuatan dan data loading instrument</li><li>Dukungan survei draft</li><li>Stabilitas utuh untuk semua kondisi</li></ul><p>Penting untuk desain baru, konversi, dan modifikasi yang mempengaruhi distribusi berat kapal.</p>',
                ],
                'short_description' => ['en' => 'Lightweight, deadweight, and trim analysis for vessel safety',
                    'id' => 'Analisis lightweight, deadweight, dan trim untuk keselamatan kapal',
                ],
                'price' => rand(9, 99),
                'is_published' => true,
                'category_id' => $productCategories[2]->id,
            ],
        ];

        foreach ($products as $index => $data) {
            $isPublished = $data['is_published'] ?? true;

            $product = Product::firstOrCreate(
                ['title->en' => $data['title']['en']],
                [
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'short_description' => $data['short_description'],
                    'price' => $data['price'],
                    'is_published' => $isPublished,
                    'category_id' => $data['category_id'],
                    'currency_id' => Currency::getDefault()->id,
                ]
            );

            if (! $product->getFirstMediaUrl('cover')) {
                try {
                    $product->addMediaFromUrl($productImages[$index % count($productImages)])
                        ->toMediaCollection('cover');
                } catch (Exception $e) {
                    // skip if image download fails
                }
            }

            $product->tags()->syncWithoutDetaching(
                $tags->random(rand(1, 3))->pluck('id')
            );
        }
    }

    private function createPosts(): void
    {
        $postCategories = Category::where('type', CategoryType::Post)->get();
        $users = User::all();
        $tags = Tag::all();

        $postImages = [
            'https://images.unsplash.com/photo-1585713181935-d5f622cc2415?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1605745341112-85968b19335b?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1583157048761-ac1dba033233?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1580166463495-ab4d21922c22?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1613690399151-65ea69478674?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1606185540834-d6e7483ee1a4?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1511316695145-4992006ffddb?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1542986386-660ccbbedaf8?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1554254648-2d58a1bc3fd5?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1576423596782-8c5478efd11f?w=800&h=600&fit=crop',
        ];

        $posts = [
            [
                'title' => ['en' => 'Basic Principles of Ship Design', 'id' => 'Prinsip Dasar Desain Kapal'],
                'content' => ['en' => '<p>Ship design is a complex engineering discipline that combines naval architecture, marine engineering, and regulatory compliance. Whether you are building a small fishing boat or a massive oil tanker, the fundamental principles remain the same.</p><p><strong>Key Design Phases:</strong></p><ol><li><strong>Concept Design</strong> - Defining the vessel\'s purpose, principal dimensions, and initial layout based on owner requirements.</li><li><strong>Preliminary Design</strong> - Developing hull form, general arrangement, and basic structural design. Hydrostatic calculations and initial stability checks are performed.</li><li><strong>Contract Design</strong> - Detailed specifications and drawings sufficient for shipyard bidding and contract signing.</li><li><strong>Detailed Design</strong> - Production-ready drawings, structural calculations, system designs, and outfitting details.</li></ol><p>The design spiral is an iterative process where each cycle refines the design based on calculations from the previous cycle. Key parameters include displacement, length, beam, depth, and draft — each affecting vessel performance, stability, and cost.</p><p>Modern ship design increasingly relies on computational tools like CFD for hydrodynamic optimization and FEA for structural analysis, enabling more efficient and safer vessels.</p>',
                    'id' => '<p>Desain kapal adalah disiplin teknik yang kompleks yang menggabungkan arsitektur kapal, teknik kelautan, dan kepatuhan regulasi. Apakah Anda membangun kapal nelayan kecil atau kapal tanker minyak raksasa, prinsip fundamentalnya tetap sama.</p><p><strong>Fase Desain Utama:</strong></p><ol><li><strong>Desain Konsep</strong> - Mendefinisikan tujuan kapal, dimensi utama, dan tata letak awal berdasarkan kebutuhan pemilik.</li><li><strong>Desain Awal</strong> - Mengembangkan bentuk lambung, rencana umum, dan desain struktur dasar. Perhitungan hidrostatis dan pemeriksaan stabilitas awal dilakukan.</li><li><strong>Desain Kontrak</strong> - Spesifikasi detail dan gambar yang cukup untuk proses tender galangan dan penandatanganan kontrak.</li><li><strong>Desain Detail</strong> - Gambar siap produksi, perhitungan struktur, desain sistem, dan detail outfitting.</li></ol><p>Spiral desain adalah proses iteratif di mana setiap siklus menyempurnakan desain berdasarkan perhitungan dari siklus sebelumnya. Parameter kunci meliputi displacement, panjang, lebar, tinggi, dan sarat — masing-masing mempengaruhi kinerja kapal, stabilitas, dan biaya.</p><p>Desain kapal modern semakin mengandalkan alat komputasi seperti CFD untuk optimasi hidrodinamika dan FEA untuk analisis struktur, memungkinkan kapal yang lebih efisien dan aman.</p>',
                ],
                'excerpt' => ['en' => 'An introduction to the fundamental principles and phases of ship design.',
                    'id' => 'Pengantar prinsip dasar dan fase desain kapal.',
                ],
                'category_id' => $postCategories[0]->id,
                'published_at' => now()->subDays(2),
            ],
            [
                'title' => ['en' => 'RO-RO Ferry Design: A Case Study', 'id' => 'Desain Ferry RO-RO: Sebuah Studi Kasus'],
                'content' => ['en' => '<p>Roll-on/Roll-off (RO-RO) ferries are the backbone of maritime transportation in Indonesia\'s archipelagic region. This case study examines the design process of a 72-meter RO-RO ferry for inter-island service.</p><p><strong>Vessel Requirements:</strong></p><ul><li>Capacity: 350 passengers + 25 vehicles</li><li>Service speed: 14 knots</li><li>Route: Surabaya-Makassar (2-day voyage)</li><li>Class: BKI (Biro Klasifikasi Indonesia)</li></ul><p><strong>Design Challenges:</strong></p><p>The primary challenge was balancing cargo capacity with stability requirements. The wide stern ramp required for vehicle loading created potential stability issues that needed careful analysis. We solved this through optimized hull form design and strategic placement of ballast tanks.</p><p>Another critical aspect was passenger comfort. We designed the accommodation layout to minimize motion sickness effects by locating passenger cabins near the vessel\'s center of motion. The HVAC system was sized to maintain comfort in tropical conditions.</p><p>The vessel was delivered on schedule and has been operating successfully for 5 years with excellent fuel efficiency and passenger satisfaction ratings.</p>',
                    'id' => '<p>Ferry Roll-on/Roll-off (RO-RO) adalah tulang punggung transportasi maritim di wilayah kepulauan Indonesia. Studi kasus ini mengkaji proses desain ferry RO-RO sepanjang 72 meter untuk layanan antar pulau.</p><p><strong>Persyaratan Kapal:</strong></p><ul><li>Kapasitas: 350 penumpang + 25 kendaraan</li><li>Kecepatan operasi: 14 knot</li><li>Rute: Surabaya-Makassar (pelayaran 2 hari)</li><li>Kelas: BKI (Biro Klasifikasi Indonesia)</li></ul><p><strong>Tantangan Desain:</strong></p><p>Tantangan utama adalah menyeimbangkan kapasitas kargo dengan persyaratan stabilitas. Ramp buritan lebar yang diperlukan untuk bongkar muat kendaraan menciptakan potensi masalah stabilitas yang memerlukan analisis cermat. Kami memecahkannya melalui desain bentuk lambung yang dioptimalkan dan penempatan tangki ballast yang strategis.</p><p>Aspek kritis lainnya adalah kenyamanan penumpang. Kami mendesain tata letak akomodasi untuk meminimalkan efek mabuk laut dengan menempatkan kabin penumpang di dekat pusat gerakan kapal. Sistem HVAC dirancang untuk menjaga kenyamanan dalam kondisi tropis.</p><p>Kapal selesai tepat waktu dan telah beroperasi dengan sukses selama 5 tahun dengan efisiensi bahan bakar yang sangat baik dan tingkat kepuasan penumpang yang tinggi.</p>',
                ],
                'excerpt' => ['en' => 'A detailed case study of designing a 72-meter RO-RO ferry for Indonesian inter-island service.',
                    'id' => 'Studi kasus detail tentang desain ferry RO-RO 72 meter untuk layanan antar pulau Indonesia.',
                ],
                'category_id' => $postCategories[0]->id,
                'published_at' => now()->subDays(5),
            ],
            [
                'title' => ['en' => 'Understanding Ship Stability', 'id' => 'Memahami Stabilitas Kapal'],
                'content' => ['en' => '<p>Ship stability is arguably the most critical aspect of naval architecture. A vessel that fails to meet stability criteria poses a direct risk to life, cargo, and the environment.</p><p><strong>Types of Stability:</strong></p><ul><li><strong>Intact Stability</strong> - The vessel\'s ability to return to an upright position after being heeled by external forces (wind, waves, cargo shift). Governed by IMO Intact Stability Code (2008 IS Code).</li><li><strong>Damage Stability</strong> - The vessel\'s ability to survive flooding of one or more compartments after hull breach. Determined through probabilistic or deterministic methods.</li></ul><p><strong>Key Stability Parameters:</strong></p><ol><li><strong>GM (Metacentric Height)</strong> - The initial stability indicator. A positive GM indicates initial stability.</li><li><strong>GZ Curve (Righting Lever)</strong> - The curve showing righting moment at various angles of heel. Area under the curve represents energy available to resist capsizing.</li><li><strong>Free Surface Effect</strong> - The reduction in stability caused by liquids sloshing in partially filled tanks.</li></ol><p>Regular stability assessments throughout a vessel\'s life are essential, especially after modifications, changes in operation, or as part of routine safety management under the ISM Code.</p>',
                    'id' => '<p>Stabilitas kapal adalah aspek paling kritis dalam arsitektur kapal. Kapal yang gagal memenuhi kriteria stabilitas menimbulkan risiko langsung terhadap keselamatan jiwa, kargo, dan lingkungan.</p><p><strong>Jenis Stabilitas:</strong></p><ul><li><strong>Stabilitas Utuh</strong> - Kemampuan kapal untuk kembali ke posisi tegak setelah dimiringkan oleh gaya eksternal (angin, gelombang, pergeseran kargo). Diatur oleh IMO Intact Stability Code (IS Code 2008).</li><li><strong>Stabilitas Rusak</strong> - Kemampuan kapal untuk bertahan setelah kebocoran pada satu atau lebih kompartemen. Ditentukan melalui metode probabilistik atau deterministik.</li></ul><p><strong>Parameter Stabilitas Utama:</strong></p><ol><li><strong>GM (Metacentric Height)</strong> - Indikator stabilitas awal. GM positif menunjukkan stabilitas awal yang baik.</li><li><strong>Kurva GZ (Righting Lever)</strong> - Kurva yang menunjukkan momen penegak pada berbagai sudut miring. Luas area di bawah kurva mewakili energi yang tersedia untuk menahan terbalik.</li><li><strong>Free Surface Effect</strong> - Pengurangan stabilitas yang disebabkan oleh pergerakan cairan dalam tangki yang terisi sebagian.</li></ol><p>Penilaian stabilitas secara teratur sepanjang umur kapal sangat penting, terutama setelah modifikasi, perubahan operasi, atau sebagai bagian dari manajemen keselamatan rutin berdasarkan ISM Code.</p>',
                ],
                'excerpt' => ['en' => 'A comprehensive overview of ship stability principles, including intact and damage stability.',
                    'id' => 'Gambaran komprehensif tentang prinsip stabilitas kapal, termasuk stabilitas utuh dan rusak.',
                ],
                'category_id' => $postCategories[0]->id,
                'published_at' => now()->subDays(7),
            ],
            [
                'title' => ['en' => 'Tug Boat Design Considerations', 'id' => 'Pertimbangan Desain Kapal Tug'],
                'content' => ['en' => '<p>Tug boats are the workhorses of the maritime industry, responsible for maneuvering larger vessels in ports, towing barges, and performing escort duties. Their design differs significantly from other vessel types due to their unique operational requirements.</p><p><strong>Key Design Factors:</strong></p><ul><li><strong>Bollard Pull</strong> - The primary performance metric. A tug\'s bollard pull depends on engine power, propeller design, and hull form. Typical harbor tugs range from 30 to 80 tonnes bollard pull.</li><li><strong>Maneuverability</strong> - Modern tugs use azimuth thrusters (Z-drives) or cycloidal propellers for exceptional maneuverability. Some designs incorporate Voith-Schneider propellers for precise positioning.</li><li><strong>Stability</strong> - Tugs must maintain stability under extreme loads during towing operations. The tow hook or winch placement must be carefully designed to avoid capsizing risks.</li></ul><p><strong>Indonesian Context:</strong></p><p>Indonesia\'s thriving port development and coal/gas shipping industries have driven demand for tugs in the 20-35 meter range. Local shipyards have developed considerable expertise in building these vessels using steel and aluminum construction.</p><p>Key considerations for Indonesian tug designs include tropical climate adaptation, ease of maintenance in remote locations, and compliance with BKI class rules.</p>',
                    'id' => '<p>Kapal tug adalah pekerja keras industri maritim, bertanggung jawab untuk bermanuver kapal besar di pelabuhan, menarik tongkang, dan melakukan tugas eskort. Desainnya berbeda secara signifikan dari jenis kapal lain karena persyaratan operasionalnya yang unik.</p><p><strong>Faktor Desain Utama:</strong></p><ul><li><strong>Bollard Pull</strong> - Metrik kinerja utama. Bollard pull kapal tug tergantung pada tenaga mesin, desain propeller, dan bentuk lambung. Typical harbor tugs berkisar 30 hingga 80 ton bollard pull.</li><li><strong>Maneuverability</strong> - Kapal tug modern menggunakan azimuth thruster (Z-drive) atau propeller sikloidal untuk manuver yang luar biasa. Beberapa desain menggabungkan propeller Voith-Schneider untuk posisi yang presisi.</li><li><strong>Stabilitas</strong> - Kapal tug harus mempertahankan stabilitas di bawah beban ekstrem selama operasi penarik. Penempatan tow hook atau winch harus dirancang dengan hati-hati untuk menghindari risiko terbalik.</li></ul><p><strong>Konteks Indonesia:</strong></p><p>Pembangunan pelabuhan yang pesat dan industri pengiriman batubara/gas di Indonesia telah mendorong permintaan kapal tug dalam rentang 20-35 meter. Galangan kapal lokal telah mengembangkan keahlian yang cukup besar dalam membangun kapal-kapal ini menggunakan konstruksi baja dan aluminium.</p><p>Pertimbangan utama untuk desain tug Indonesia meliputi adaptasi iklim tropis, kemudahan perawatan di lokasi terpencil, dan kepatuhan terhadap aturan kelas BKI.</p>',
                ],
                'excerpt' => ['en' => 'Key design considerations for tug boats including bollard pull, maneuverability, and stability.',
                    'id' => 'Pertimbangan desain utama untuk kapal tug termasuk bollard pull, maneuverability, dan stabilitas.',
                ],
                'category_id' => $postCategories[0]->id,
                'published_at' => now()->subDays(10),
            ],
            [
                'title' => ['en' => 'Oil Tanker Construction Trends 2026', 'id' => 'Tren Konstruksi Kapal Tanker Minyak 2026'],
                'content' => ['en' => '<p>The oil tanker market continues to evolve with new regulations, environmental concerns, and technological advancements shaping vessel design and construction.</p><p><strong>Key Trends:</strong></p><ol><li><strong>Double Hull Mandate</strong> - All new tankers must feature double hull construction as mandated by MARPOL Annex I. This provides enhanced environmental protection against oil spills in case of grounding or collision.</li><li><strong>Energy Efficiency Design Index (EEDI)</strong> - New tankers must comply with increasingly stringent EEDI requirements. Design optimization focuses on hull form, propeller efficiency, and waste heat recovery systems.</li><li><strong>LNG-Ready Designs</strong> - While most tankers still operate on heavy fuel oil, new designs incorporate LNG-ready features for future fuel switching. Some owners are opting for dual-fuel engines from the outset.</li><li><strong>Digitalization</strong> - Modern tankers feature advanced automation, remote monitoring, and digital twin technology for predictive maintenance.</li></ol><p><strong>Indonesian Market:</strong></p><p>Indonesia\'s oil and gas shipping sector has seen growth in the SPOB (Self-Propelled Oil Barge) and coastal tanker segments. These vessels are specifically designed for Indonesia\'s shallow-draft ports and inter-island routes, typically ranging from 30 to 90 meters in length with deadweight capacities of 500-6,000 DWT.</p>',
                    'id' => '<p>Pasar kapal tanker minyak terus berkembang dengan regulasi baru, masalah lingkungan, dan kemajuan teknologi yang membentuk desain dan konstruksi kapal.</p><p><strong>Tren Utama:</strong></p><ol><li><strong>Mandat Double Hull</strong> - Semua kapal tanker baru harus memiliki konstruksi double hull sesuai mandat MARPOL Annex I. Ini memberikan perlindungan lingkungan yang lebih baik terhadap tumpahan minyak jika terjadi grounding atau tabrakan.</li><li><strong>Energy Efficiency Design Index (EEDI)</strong> - Kapal tanker baru harus memenuhi persyaratan EEDI yang semakin ketat. Optimasi desain berfokus pada bentuk lambung, efisiensi propeller, dan sistem pemulihan panas buang.</li><li><strong>Desain LNG-Ready</strong> - Meskipun sebagian besar tanker masih menggunakan bahan bakar minyak berat, desain baru menggabungkan fitur LNG-ready untuk peralihan bahan bakar di masa depan. Beberapa pemilik memilih mesin dual-fuel sejak awal.</li><li><strong>Digitalisasi</strong> - Kapal tanker modern menampilkan otomatisasi canggih, pemantauan jarak jauh, dan teknologi digital twin untuk pemeliharaan prediktif.</li></ol><p><strong>Pasar Indonesia:</strong></p><p>Sektor pengiriman minyak dan gas Indonesia telah melihat pertumbuhan di segmen SPOB (Self-Propelled Oil Barge) dan kapal tanker pantai. Kapal-kapal ini dirancang khusus untuk pelabuhan draft dangkal dan rute antar pulau Indonesia, biasanya dengan panjang 30-90 meter dan kapasitas deadweight 500-6.000 DWT.</p>',
                ],
                'excerpt' => ['en' => 'Latest trends in oil tanker design and construction including EEDI compliance and LNG-ready designs.',
                    'id' => 'Tren terbaru dalam desain dan konstruksi kapal tanker minyak termasuk kepatuhan EEDI dan desain LNG-ready.',
                ],
                'category_id' => $postCategories[3]->id,
                'published_at' => now()->subDays(3),
            ],
            [
                'title' => ['en' => 'Fuel Efficiency in Modern Ship Design', 'id' => 'Efisiensi Bahan Bakar dalam Desain Kapal Modern'],
                'content' => ['en' => '<p>Fuel costs represent a significant portion of vessel operating expenses. Modern ship design prioritizes fuel efficiency through multiple interconnected strategies.</p><p><strong>Hull Form Optimization:</strong></p><p>Computational Fluid Dynamics (CFD) allows designers to analyze and optimize hull forms for minimal resistance. Key techniques include bulbous bow optimization, wake-adapted propeller design, and air lubrication systems that reduce frictional resistance by injecting air bubbles along the hull surface. Fuel savings of 5-15% are achievable through hull optimization alone.</p><p><strong>Propulsion Efficiency:</strong></p><ul><li>Large-diameter, slow-turning propellers with optimized blade geometry</li><li>Propeller-rudder-bulb systems that recover rotational energy</li><li>Diesel-electric and hybrid propulsion for variable load profiles</li><li>Waste heat recovery systems for auxiliary power generation</li></ul><p><strong>Operational Optimization:</strong></p><p>Weather routing systems, hull cleaning schedules, and engine health monitoring contribute to maintaining design efficiency throughout the vessel\'s life. The combination of good design and smart operations can reduce total fuel consumption by 20-30% compared to conventional approaches.</p>',
                    'id' => '<p>Biaya bahan bakar merupakan bagian signifikan dari biaya operasional kapal. Desain kapal modern memprioritaskan efisiensi bahan bakar melalui berbagai strategi yang saling terkait.</p><p><strong>Optimasi Bentuk Lambung:</strong></p><p>Computational Fluid Dynamics (CFD) memungkinkan desainer menganalisis dan mengoptimalkan bentuk lambung untuk hambatan minimal. Teknik utama meliputi optimasi bulbous bow, desain propeller yang disesuaikan dengan wake, dan sistem air lubrication yang mengurangi hambatan gesek dengan menyuntikkan gelembung udara di sepanjang permukaan lambung. Penghematan bahan bakar 5-15% dapat dicapai melalui optimasi lambung saja.</p><p><strong>Efisiensi Propulsi:</strong></p><ul><li>Propeller berdiameter besar dan putaran lambat dengan geometri blade yang dioptimalkan</li><li>Sistem propeller-rudder-bulb yang memulihkan energi rotasional</li><li>Propulsi diesel-electric dan hybrid untuk profil beban variabel</li><li>Sistem pemulihan panas buang untuk pembangkit listrik tambahan</li></ul><p><strong>Optimasi Operasional:</strong></p><p>Sistem weather routing, jadwal pembersihan lambung, dan pemantauan kesehatan mesin berkontribusi mempertahankan efisiensi desain sepanjang umur kapal. Kombinasi desain yang baik dan operasi cerdas dapat mengurangi total konsumsi bahan bakar sebesar 20-30% dibandingkan pendekatan konvensional.</p>',
                ],
                'excerpt' => ['en' => 'Strategies for improving fuel efficiency through hull optimization, propulsion design, and smart operations.',
                    'id' => 'Strategi meningkatkan efisiensi bahan bakar melalui optimasi lambung, desain propulsi, dan operasi cerdas.',
                ],
                'category_id' => $postCategories[2]->id,
                'published_at' => now()->subDays(14),
            ],
            [
                'title' => ['en' => 'Barge Selection Guide', 'id' => 'Panduan Memilih Tongkang'],
                'content' => ['en' => '<p>Barges play a vital role in Indonesia\'s maritime logistics, transporting bulk commodities like coal, nickel ore, palm oil, and construction materials across the archipelago. Selecting the right barge type is crucial for operational efficiency and profitability.</p><p><strong>Barge Types:</strong></p><ul><li><strong>Deck Cargo Barge</strong> - For heavy-lift and project cargo. Typical sizes range from 100 to 300 feet. Ideal for transporting construction equipment and modules.</li><li><strong>Hopper Barge</strong> - Self-unloading barges with bottom doors for discharging dredged materials or aggregates. Common in dredging and reclamation projects.</li><li><strong>Tanker Barge</strong> - For liquid cargoes including oil, chemicals, and palm oil. Double hull construction is standard for environmental compliance.</li><li><strong>Flat Top Barge</strong> - General-purpose barge with unobstructed deck area. Highly versatile for various cargo types.</li></ul><p><strong>Selection Criteria:</strong></p><ol><li><strong>Cargo type and density</strong> - Determines required deck area and load capacity</li><li><strong>Route and water depth</strong> - Draft restrictions in shallow ports may limit barge size</li><li><strong>Tug boat compatibility</strong> - Bollard pull requirements for intended routes</li><li><strong>Regulatory compliance</strong> - BKI class, ISM Code, and applicable Indonesian regulations</li></ol>',
                    'id' => '<p>Tongkang memainkan peran penting dalam logistik maritim Indonesia, mengangkut komoditas curah seperti batubara, bijih nikel, minyak sawit, dan bahan konstruksi di seluruh nusantara. Memilih jenis tongkang yang tepat sangat penting untuk efisiensi operasional dan profitabilitas.</p><p><strong>Jenis Tongkang:</strong></p><ul><li><strong>Deck Cargo Barge</strong> - Untuk kargo heavy-lift dan proyek. Ukuran tipikal berkisar 100-300 kaki. Ideal untuk mengangkut peralatan konstruksi dan modul.</li><li><strong>Hopper Barge</strong> - Tongkang self-unloading dengan pintu bawah untuk membuang material kerukan atau agregat. Umum dalam proyek pengerukan dan reklamasi.</li><li><strong>Tanker Barge</strong> - Untuk kargo cair termasuk minyak, bahan kimia, dan minyak sawit. Konstruksi double hull adalah standar untuk kepatuhan lingkungan.</li><li><strong>Flat Top Barge</strong> - Tongkang tujuan umum dengan area dek tanpa halangan. Sangat serbaguna untuk berbagai jenis kargo.</li></ul><p><strong>Kriteria Pemilihan:</strong></p><ol><li><strong>Jenis dan densitas kargo</strong> - Menentukan luas dek yang diperlukan dan kapasitas muatan</li><li><strong>Rute dan kedalaman air</strong> - Batasan draft di pelabuhan dangkal dapat membatasi ukuran tongkang</li><li><strong>Kompatibilitas kapal tug</strong> - Persyaratan bollard pull untuk rute yang dimaksud</li><li><strong>Kepatuhan regulasi</strong> - Kelas BKI, ISM Code, dan peraturan Indonesia yang berlaku</li></ol>',
                ],
                'excerpt' => ['en' => 'A practical guide to selecting the right barge type for various cargoes and operating conditions.',
                    'id' => 'Panduan praktis memilih jenis tongkang yang tepat untuk berbagai kargo dan kondisi operasi.',
                ],
                'category_id' => $postCategories[1]->id,
                'published_at' => now()->subDays(6),
            ],
            [
                'title' => ['en' => 'Material Selection for Shipbuilding', 'id' => 'Pemilihan Material untuk Pembuatan Kapal'],
                'content' => ['en' => '<p>Material selection is a critical decision in ship design that affects structural strength, weight, cost, and vessel lifespan. Each material offers unique advantages and trade-offs.</p><p><strong>Steel Grades:</strong></p><ul><li><strong>Mild Steel (Grade A)</strong> - The standard material for most vessel structures. Good weldability and cost-effective. Used for hull plating, decks, and bulkheads.</li><li><strong>High-Tensile Steel (AH/DH/EH)</strong> - Higher strength-to-weight ratio, reducing steel weight by 15-25%. Used in high-stress areas like topsides of large vessels. Requires careful welding procedure control.</li><li><strong>Corrosion-Resistant Steel</strong> - Specialized grades for cargo tanks in chemical tankers. Higher initial cost but reduced coating maintenance.</li></ul><p><strong>Aluminum Alloys:</strong></p><p>Increasingly popular for superstructures, high-speed craft, and passenger vessels. Aluminum offers 60% weight reduction compared to steel but comes with higher material cost and specialized welding requirements. Benefits include improved stability (lower center of gravity), higher payload capacity, and reduced fuel consumption.</p><p><strong>Composite Materials:</strong></p><p>GFRP (Glass Fiber Reinforced Plastic) is common in small craft under 40 meters. Advantages include corrosion resistance, design flexibility, and low maintenance. CFRP (Carbon Fiber) is used in high-performance vessels but remains cost-prohibitive for most commercial applications.</p>',
                    'id' => '<p>Pemilihan material adalah keputusan kritis dalam desain kapal yang mempengaruhi kekuatan struktur, berat, biaya, dan umur kapal. Setiap material menawarkan keunggulan dan trade-off yang unik.</p><p><strong>Grade Baja:</strong></p><ul><li><strong>Mild Steel (Grade A)</strong> - Material standar untuk sebagian besar struktur kapal. Kemampuan las yang baik dan hemat biaya. Digunakan untuk pelat lambung, geladak, dan sekat.</li><li><strong>High-Tensile Steel (AH/DH/EH)</strong> - Rasio kekuatan-terhadap-berat yang lebih tinggi, mengurangi berat baja 15-25%. Digunakan di area tegangan tinggi seperti topsides kapal besar. Memerlukan kontrol prosedur las yang hati-hati.</li><li><strong>Corrosion-Resistant Steel</strong> - Grade khusus untuk tangki kargo di kapal tanker kimia. Biaya awal lebih tinggi tetapi perawatan coating berkurang.</li></ul><p><strong>Paduan Aluminium:</strong></p><p>Semakin populer untuk bangunan atas, kapal berkecepatan tinggi, dan kapal penumpang. Aluminium menawarkan pengurangan berat 60% dibandingkan baja tetapi dengan biaya material lebih tinggi dan persyaratan pengelasan khusus. Manfaatnya termasuk stabilitas yang lebih baik (pusat gravitasi lebih rendah), kapasitas muatan lebih tinggi, dan konsumsi bahan bakar berkurang.</p><p><strong>Material Komposit:</strong></p><p>GFRP (Glass Fiber Reinforced Plastic) umum di kapal kecil di bawah 40 meter. Keunggulannya meliputi ketahanan korosi, fleksibilitas desain, dan perawatan rendah. CFRP (Carbon Fiber) digunakan di kapal berkinerja tinggi tetapi masih mahal untuk sebagian besar aplikasi komersial.</p>',
                ],
                'excerpt' => ['en' => 'A comprehensive comparison of steel, aluminum, and composites for shipbuilding applications.',
                    'id' => 'Perbandingan komprehensif baja, aluminium, dan komposit untuk aplikasi pembuatan kapal.',
                ],
                'category_id' => $postCategories[1]->id,
                'published_at' => now()->subDays(8),
            ],
            [
                'title' => ['en' => 'Port Infrastructure Development in Indonesia', 'id' => 'Pengembangan Infrastruktur Pelabuhan di Indonesia'],
                'content' => ['en' => '<p>Indonesia\'s port infrastructure development is a key driver of economic growth, supporting the country\'s maritime connectivity and trade competitiveness. As a maritime nation with over 17,000 islands, efficient ports are essential for logistics and regional development.</p><p><strong>Current Developments:</strong></p><ul><li><strong>New Priok Terminal (Jakarta)</strong> - Expanding container handling capacity by 8 million TEUs annually. Features deep-water berths capable of accommodating the largest container vessels.</li><li><strong>Patimban Deep Seaport (West Java)</strong> - A new international hub port with 7.5 million TEU capacity, designed to relieve congestion at Tanjung Priok. Includes a dedicated car terminal for Indonesia\'s automotive exports.</li><li><strong>Kuala Tanjung (North Sumatra)</strong> - Multi-purpose port development serving the Sumatra region, with special focus on palm oil and rubber exports.</li></ul><p><strong>Design Considerations:</strong></p><p>Port design for Indonesia must account for tropical weather patterns, seismic activity, and varying seabed conditions. Our firm has been involved in designing specialized barge ramps, Ro-Ro facilities, and small craft harbors that support the inter-island shipping network.</p>',
                    'id' => '<p>Pengembangan infrastruktur pelabuhan Indonesia adalah pendorong utama pertumbuhan ekonomi, mendukung konektivitas maritim dan daya saing perdagangan negara. Sebagai negara maritim dengan lebih dari 17.000 pulau, pelabuhan yang efisien sangat penting untuk logistik dan pembangunan daerah.</p><p><strong>Perkembangan Saat Ini:</strong></p><ul><li><strong>Terminal Baru Priok (Jakarta)</strong> - Memperluas kapasitas penanganan kontainer sebesar 8 juta TEUs per tahun. Dilengkapi dermaga air dalam yang mampu menampung kapal kontainer terbesar.</li><li><strong>Patimban Deep Seaport (Jawa Barat)</strong> - Pelabuhan hub internasional baru dengan kapasitas 7,5 juta TEU, dirancang untuk mengurangi kemacetan di Tanjung Priok. Termasuk terminal mobil khusus untuk ekspor otomotif Indonesia.</li><li><strong>Kuala Tanjung (Sumatera Utara)</strong> - Pengembangan pelabuhan multi-fungsi yang melayani wilayah Sumatera, dengan fokus khusus pada ekspor minyak sawit dan karet.</li></ul><p><strong>Pertimbangan Desain:</strong></p><p>Desain pelabuhan untuk Indonesia harus memperhitungkan pola cuaca tropis, aktivitas seismik, dan kondisi dasar laut yang bervariasi. Perusahaan kami telah terlibat dalam mendesain barge ramp khusus, fasilitas Ro-Ro, dan pelabuhan kapal kecil yang mendukung jaringan pelayaran antar pulau.</p>',
                ],
                'excerpt' => ['en' => 'Overview of major port infrastructure projects driving Indonesia\'s maritime connectivity.',
                    'id' => 'Gambaran umum proyek infrastruktur pelabuhan utama yang mendorong konektivitas maritim Indonesia.',
                ],
                'category_id' => $postCategories[3]->id,
                'published_at' => now()->subDays(4),
            ],
            [
                'title' => ['en' => 'Classification Society Rules Update 2026', 'id' => 'Pembaruan Aturan Badan Klasifikasi 2026'],
                'content' => ['en' => '<p>Classification societies play a crucial role in ensuring vessel safety and regulatory compliance. The year 2026 brings several important rule changes that ship designers, owners, and yards need to understand.</p><p><strong>BKI (Biro Klasifikasi Indonesia) Updates:</strong></p><ul><li>Revised rules for inland waterway vessels operating on Indonesian rivers and lakes</li><li>Updated requirements for alternative fuel systems including LNG and methanol</li><li>Enhanced structural requirements for vessels operating in Indonesian archipelagic waters with specific wave loading criteria</li></ul><p><strong>IMO Regulatory Milestones 2026:</strong></p><ul><li>EEXI (Energy Efficiency Existing Ship Index) implementation enters final phase</li><li>CII (Carbon Intensity Indicator) rating requirements tighten for existing vessels</li><li>Ballast Water Management Convention compliance deadline for all vessels</li><li>Revised SOLAS Chapter II-1 on subdivision and damage stability</li></ul><p><strong>Impact on New Designs:</strong></p><p>These rule changes affect materials selection, structural design, system configuration, and operational planning. Early engagement with classification societies during the design phase is essential to avoid costly redesigns and delays. Our firm maintains close working relationships with BKI, Lloyd\'s Register, DNV, and ABS to ensure seamless class approval for our designs.</p>',
                    'id' => '<p>Badan klasifikasi memainkan peran penting dalam memastikan keselamatan kapal dan kepatuhan regulasi. Tahun 2026 membawa beberapa perubahan aturan penting yang perlu dipahami oleh desainer kapal, pemilik, dan galangan.</p><p><strong>Pembaruan BKI (Biro Klasifikasi Indonesia):</strong></p><ul><li>Aturan yang direvisi untuk kapal perairan pedalaman yang beroperasi di sungai dan danau Indonesia</li><li>Persyaratan yang diperbarui untuk sistem bahan bakar alternatif termasuk LNG dan metanol</li><li>Persyaratan struktur yang ditingkatkan untuk kapal yang beroperasi di perairan kepulauan Indonesia dengan kriteria pembebanan gelombang spesifik</li></ul><p><strong>Tonggak Regulasi IMO 2026:</strong></p><ul><li>EEXI (Energy Efficiency Existing Ship Index) memasuki fase akhir implementasi</li><li>Persyaratan peringkat CII (Carbon Intensity Indicator) diperketat untuk kapal yang ada</li><li>Tenggat kepatuhan Ballast Water Management Convention untuk semua kapal</li><li>Revisi SOLAS Chapter II-1 tentang subdivisi dan stabilitas rusak</li></ul><p><strong>Dampak pada Desain Baru:</strong></p><p>Perubahan aturan ini mempengaruhi pemilihan material, desain struktur, konfigurasi sistem, dan perencanaan operasional. Keterlibatan awal dengan badan klasifikasi selama fase desain sangat penting untuk menghindari desain ulang dan keterlambatan yang mahal. Perusahaan kami memelihara hubungan kerja yang erat dengan BKI, Lloyd\'s Register, DNV, dan ABS untuk memastikan persetujuan kelas yang lancar untuk desain kami.</p>',
                ],
                'excerpt' => ['en' => 'Key classification society rule changes and IMO regulatory milestones affecting ship design in 2026.',
                    'id' => 'Perubahan aturan badan klasifikasi utama dan tonggak regulasi IMO yang mempengaruhi desain kapal di tahun 2026.',
                ],
                'category_id' => $postCategories[3]->id,
                'published_at' => now()->subDays(1),
            ],
        ];

        foreach ($posts as $index => $data) {
            $isPublished = $data['is_published'] ?? true;

            $author = $users->random();

            $post = Post::firstOrCreate(
                ['title->en' => $data['title']['en']],
                [
                    'title' => $data['title'],
                    'content' => $data['content'],
                    'excerpt' => $data['excerpt'],
                    'is_published' => $isPublished,
                    'category_id' => $data['category_id'],
                    'author_id' => $author->id,
                    'published_at' => $data['published_at'],
                ]
            );

            if (! $post->getFirstMediaUrl('featured_image')) {
                $imageUrl = $postImages[$index % count($postImages)];

                try {
                    $post->addMediaFromUrl($imageUrl)
                        ->toMediaCollection('featured_image');
                } catch (Exception $e) {
                    // skip if image download fails
                }
            }

            $post->tags()->syncWithoutDetaching(
                $tags->random(rand(1, 3))->pluck('id')
            );
        }
    }

    private function createProjects(): void
    {
        $this->call(ProjectSeeder::class);
    }
}
