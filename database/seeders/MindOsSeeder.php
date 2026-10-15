<?php

namespace Database\Seeders;

use App\Models\MindOs\AudioTrack;
use App\Models\MindOs\BugQuestion;
use App\Models\MindOs\CertificationProgram;
use App\Models\MindOs\FoundationModule;
use App\Models\MindOs\MindBug;
use App\Models\MindOs\MindService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Default Mind OS content. Idempotent — keyed on slug/code, so it can be
 * re-run safely after editing texts here. Accounts live in AccountSeeder.
 */
class MindOsSeeder extends Seeder
{
    public function run(): void
    {
        $this->bugs();
        $this->modules();
        $this->audio();
        $this->services();
        $this->certifications();
    }

    private function bugs(): void
    {
        $bugs = [
            ['P1', 'Takut Gagal', 'Saya harus aman dulu baru bergerak', 'danger',
                'Pola yang membuat pikiran menganggap kegagalan sebagai ancaman bagi harga diri, sehingga Anda menunda, ragu, atau berhenti sebelum mencoba.',
                'Menunda-nunda, sulit memulai, cemas berlebihan sebelum presentasi atau ujian, menghindari tantangan baru.',
                'Gagal adalah data, bukan identitas. Setiap percobaan adalah bagian dari bertumbuh.',
                ['Saya sering menunda pekerjaan penting karena takut hasilnya tidak sempurna.', 'Saya menghindari kesempatan baru karena khawatir gagal.', 'Tubuh saya tegang atau berdebar saat harus tampil atau dinilai.', 'Saya lebih memilih tidak mencoba daripada mencoba lalu gagal.', 'Kritik kecil terasa seperti bukti bahwa saya tidak mampu.']],
            ['P2', 'Merasa Tidak Layak', 'Saya tidak cukup baik', 'warning',
                'Keyakinan inti bahwa diri tidak pantas dicintai, dihargai, atau sukses. Biasanya terbentuk dari pengalaman ditolak atau dibandingkan.',
                'Sulit menerima pujian, merasa minder, sabotase diri saat hampir berhasil, membandingkan diri terus-menerus.',
                'Nilai diri Anda bukan hasil kinerja — ia sudah terpasang sejak lahir.',
                ['Saya merasa canggung atau curiga saat dipuji.', 'Saya sering membandingkan diri dengan orang lain dan merasa kalah.', 'Saya merasa tidak pantas mendapatkan hal-hal baik.', 'Saat hampir berhasil, saya justru melakukan hal yang merusaknya.', 'Ada suara dalam diri yang bilang "kamu tidak cukup".']],
            ['P3', 'People Pleaser', 'Saya harus membuat semua orang senang', 'info',
                'Pola bertahan diri yang menukar kebutuhan diri demi penerimaan orang lain. Batasan diri menjadi kabur.',
                'Sulit berkata tidak, merasa bersalah saat menolak, lelah secara emosional, kehilangan arah keinginan sendiri.',
                'Menjaga batas adalah bentuk cinta — untuk diri dan orang lain.',
                ['Saya sulit menolak permintaan orang meskipun saya keberatan.', 'Saya merasa bersalah jika orang lain kecewa pada saya.', 'Saya sering mengutamakan kebutuhan orang lain hingga kelelahan.', 'Saya bingung apa yang sebenarnya saya inginkan.', 'Saya cemas jika ada orang yang tidak menyukai saya.']],
            ['P4', 'Overthinking', 'Pikiran saya tidak bisa berhenti', 'primary',
                'Pikiran terus memutar skenario terburuk berulang-ulang hingga sulit beristirahat dan merasa tenang.',
                'Sulit tidur, memutar ulang percakapan, cemas akan masa depan, sulit mengambil keputusan.',
                'Pikiran adalah alat, bukan penguasa. Anda boleh berhenti dan bernapas.',
                ['Saya sulit tidur karena pikiran terus berputar.', 'Saya memutar ulang percakapan lama dan menyesalinya.', 'Saya membayangkan skenario terburuk dari hal kecil.', 'Saya butuh waktu sangat lama untuk mengambil keputusan.', 'Kepala saya terasa penuh dan lelah meski tidak bekerja fisik.']],
            ['P5', 'Luka Masa Lalu', 'Yang dulu masih terasa sekarang', 'danger',
                'Memori emosional yang belum terproses (trauma) sehingga peristiwa masa kini memicu reaksi yang sama kuatnya dengan dulu.',
                'Reaksi berlebihan terhadap pemicu tertentu, mimpi buruk, menghindari tempat/orang tertentu, mati rasa emosional.',
                'Kejadiannya sudah selesai. Tubuh Anda boleh belajar bahwa sekarang Anda aman.',
                ['Ada kejadian masa lalu yang masih membuat saya sesak saat diingat.', 'Saya bereaksi sangat kuat pada pemicu kecil tertentu.', 'Saya menghindari tempat, orang, atau topik tertentu.', 'Saya kadang merasa mati rasa atau terputus dari perasaan.', 'Saya mengalami mimpi buruk atau ingatan yang tiba-tiba muncul.']],
            ['P6', 'Kontrol & Perfeksionis', 'Semua harus sesuai rencana', 'success',
                'Pola yang mencari rasa aman dengan mengendalikan segalanya. Ketidakpastian dianggap bahaya.',
                'Mudah marah saat rencana berubah, sulit mendelegasikan, standar tinggi yang melelahkan, sulit santai.',
                'Rasa aman sejati datang dari kepercayaan, bukan dari kontrol.',
                ['Saya gelisah jika sesuatu tidak berjalan sesuai rencana.', 'Saya sulit mempercayakan pekerjaan kepada orang lain.', 'Saya menuntut diri sendiri dan orang lain dengan standar sangat tinggi.', 'Saya sulit benar-benar beristirahat atau bersantai.', 'Kesalahan kecil membuat saya sangat terganggu.']],
            ['P7', 'Merasa Selalu Kurang', 'Tidak pernah cukup', 'warning',
                'Rasa selalu kurang terkait uang, waktu, dan cinta. Membuat Anda menggenggam terlalu erat atau justru menolak kelimpahan.',
                'Cemas soal uang meski cukup, sulit menikmati, iri pada keberhasilan orang lain, sulit berbagi atau menerima.',
                'Kelimpahan dimulai dari rasa cukup hari ini.',
                ['Saya cemas soal uang meskipun kebutuhan saya tercukupi.', 'Saya merasa waktu saya selalu kurang.', 'Saya sulit menikmati hal baik karena takut akan hilang.', 'Keberhasilan orang lain membuat saya merasa tertinggal.', 'Saya sulit menerima bantuan atau hadiah dari orang lain.']],
        ];

        // Earlier seeds used codes B1–B7; rename in place so existing
        // assessments and journals keep pointing at the same rows.
        foreach (range(1, 7) as $n) {
            if (! MindBug::where('code', 'P' . $n)->exists()) {
                MindBug::where('code', 'B' . $n)->update(['code' => 'P' . $n]);
            }
        }
        MindBug::flushCache();

        foreach ($bugs as $i => [$code, $name, $tagline, $color, $desc, $symptoms, $reframe, $questions]) {
            $bug = MindBug::updateOrCreate(['code' => $code], [
                'name' => $name,
                'tagline' => $tagline,
                'color' => $color,
                'description' => $desc,
                'symptoms' => $symptoms,
                'reframe_hint' => $reframe,
                'sort_order' => $i + 1,
            ]);

            foreach ($questions as $q => $question) {
                BugQuestion::updateOrCreate(
                    ['mind_bug_id' => $bug->id, 'sort_order' => $q + 1],
                    ['question' => $question, 'is_active' => true]
                );
            }
        }
    }

    private function modules(): void
    {
        $modules = [
            ['filosofi', 'Pikiran yang Sehat Bisa Dilatih', 'Kesehatan mental bukan bawaan lahir — pikiran bisa dipulihkan dan dilatih, salah satunya lewat hipnoterapi.',
                "Cara kita berpikir, merasa, dan bereaksi terbentuk sejak kecil lewat pengalaman, pola asuh, budaya, dan peristiwa emosional.\n\nSebagian besar pola itu membantu kita. Namun ada pola yang dulu berguna untuk bertahan, kini justru melukai: membuat Anda cemas, menunda, takut, sulit tidur, atau terjebak pada masalah yang sama.\n\nKabar baiknya: pikiran bisa pulih. Lewat hipnoterapi dan latihan sederhana, Mind OS membantu Anda menyadari pola tersebut, menemukan akarnya, lalu menanamkan cara pandang baru yang lebih sehat dan menenangkan."],
            ['filosofi', 'Kesadaran adalah Awal Pemulihan', 'Tanpa kesadaran, kita bereaksi otomatis. Dengan kesadaran, kita bisa memilih.',
                "Langkah pertama dari semua perubahan adalah Recognize — menyadari. Saat Anda bisa mengamati pikiran tanpa larut di dalamnya, Anda mulai memegang kendali atas reaksi Anda sendiri.\n\nLatihan: selama 3 hari, setiap kali emosi kuat muncul, berhenti 10 detik dan katakan dalam hati \"Saya sedang merasakan ___\". Catat di Jurnal Recognize."],
            ['lapisan', 'Lapisan 1 — Tubuh & Sistem Saraf', 'Tubuh menyimpan jejak stres dan emosi yang belum terselesaikan.',
                "Napas, detak jantung, ketegangan otot, dan kualitas tidur adalah sinyal paling jujur dari sistem Anda. Luka emosional hampir selalu meninggalkan jejak di tubuh.\n\nLatihan: body scan 5 menit setiap pagi. Perhatikan bagian tubuh yang tegang tanpa berusaha mengubahnya."],
            ['lapisan', 'Lapisan 2 — Pikiran Bawah Sadar', 'Tempat memori emosional dan kebiasaan otomatis tersimpan.',
                "Bawah sadar bekerja 24 jam, mengatur 90% perilaku Anda secara otomatis. Di sinilah hipnoterapi bekerja — membuka akses ke kernel untuk memperbarui program yang usang."],
            ['lapisan', 'Lapisan 3 — Keyakinan Inti', 'Keyakinan inti menentukan bagaimana setiap data kehidupan diproses.',
                "\"Saya tidak cukup baik\", \"Dunia ini berbahaya\", \"Uang itu sulit\" — keyakinan seperti ini menjadi filter yang mewarnai semua pengalaman. Mengubah keyakinan berarti mengubah cara seluruh sistem memproses realitas."],
            ['lapisan', 'Lapisan 4 — Kebiasaan & Perilaku', 'Program yang terlihat dari luar: rutinitas, respon, dan kebiasaan.',
                "Kebiasaan adalah aplikasi yang dijalankan oleh OS keyakinan. Itu sebabnya memaksakan kebiasaan baru tanpa mengubah keyakinan sering gagal. Action Plan 30 Hari membantu memasang aplikasi baru setelah OS diperbarui."],
            ['lapisan', 'Lapisan 5 — Emosi & Relasi', 'Cara diri Anda berinteraksi dengan dunia dan orang lain.',
                "Emosi adalah notifikasi sistem. Bukan musuh — melainkan pesan bahwa ada sesuatu yang perlu diperhatikan. Relasi adalah jaringan tempat sistem Anda terhubung dan saling memengaruhi."],
        ];

        foreach (MindBug::orderBy('sort_order')->get() as $bug) {
            $modules[] = ['bug', $bug->name, $bug->tagline . ' — kenali tanda, akar, dan cara memperbaikinya.',
                $bug->description . "\n\nTanda-tanda: " . $bug->symptoms . "\n\nKunci reframe: " . $bug->reframe_hint
                . "\n\nLangkah selanjutnya: isi Jurnal Recognize setiap kali pola ini muncul, dengarkan audio Reinforce, dan catat progres Anda di 6R Tracker."];
        }

        foreach ($modules as $i => [$category, $title, $summary, $content]) {
            FoundationModule::updateOrCreate(['slug' => Str::slug($title)], [
                'category' => $category,
                'title' => $title,
                'summary' => $summary,
                'content' => $content,
                'duration_minutes' => $category === 'bug' ? 15 : 10,
                'sort_order' => $i + 1,
                'is_published' => true,
            ]);
        }

        // Retitled default modules get a new slug — drop the old copies so
        // the Foundation list is not duplicated.
        $slugs = array_map(fn ($m) => Str::slug($m[1]), $modules);
        FoundationModule::whereIn('category', ['filosofi', 'lapisan', 'bug'])->whereNotIn('slug', $slugs)->delete();
    }

    private function audio(): void
    {
        $tracks = [
            ['R1', 'Body Scan Kesadaran', 'Latihan Recognize: memindai tubuh untuk menemukan jejak emosi yang tersimpan.', 600],
            ['R1', 'Napas 4-7-8 Menenangkan Sistem', 'Teknik napas untuk menurunkan respon stres dalam 5 menit.', 300],
            ['R2', 'Menelusuri Akar Bug', 'Guided reveal untuk menemukan kapan program lama pertama kali terpasang.', 900],
            ['R6', 'Afirmasi Pagi Mind OS', 'Reinforce program baru setiap pagi sebelum memulai aktivitas.', 420],
            ['R6', 'Tidur Nyenyak & Reprogram Malam', 'Self-hypnosis menjelang tidur untuk memperkuat program baru.', 1200],
            ['R6', 'Safe Place — Ruang Aman Batin', 'Membangun ruang aman di dalam pikiran yang bisa Anda kunjungi kapan saja.', 780],
        ];

        foreach ($tracks as $i => [$stage, $title, $desc, $seconds]) {
            AudioTrack::updateOrCreate(['slug' => Str::slug($title)], [
                'stage' => $stage,
                'title' => $title,
                'description' => $desc,
                'duration_seconds' => $seconds,
                'script' => "Duduk atau berbaring dengan nyaman. Pejamkan mata perlahan.\nTarik napas dalam... tahan... hembuskan.\nIzinkan tubuh Anda menjadi lebih rileks di setiap hembusan napas.\n\n" . $desc,
                'sort_order' => $i + 1,
                'is_published' => true,
            ]);
        }
    }

    private function services(): void
    {
        // "Diskusi 7 Bug" was renamed; carry the old row (and its bookings) over.
        $old = MindService::where('slug', 'diskusi-7-bug-online')->first();
        if ($old) {
            $new = MindService::where('slug', 'diskusi-7-pola-pikiran-online')->first();
            if (! $new) {
                $old->update(['slug' => 'diskusi-7-pola-pikiran-online']);
            } else {
                \App\Models\MindOs\Booking::where('mind_service_id', $old->id)->update(['mind_service_id' => $new->id]);
                $old->delete();
            }
        }

        $services = [
            ['online', 'Workshop Foundation', null, 'Workshop interaktif memahami 5 Lapisan Diri dan 7 Pola Pikiran bersama trainer Mind OS.', 150000, 180, 50, true],
            ['online', 'Diskusi 7 Pola Pikiran', 'R2', 'Sesi diskusi kelompok membedah pola pikiran dominan dan cara mengenalinya sehari-hari.', 75000, 90, 20, true],
            ['online', '6R — Reframe', 'R4', 'Sesi privat online untuk membingkai ulang peristiwa yang memicu cemas atau luka.', 350000, 60, 1, false],
            ['online', '6R — Reprogram Guided', 'R5A', 'Sesi hipnoterapi terpandu online untuk menanam program baru.', 500000, 90, 1, false],
            ['online', 'Group Coaching', 'R6', 'Coaching kelompok mingguan untuk menjaga konsistensi Action Plan 30 Hari.', 100000, 90, 15, true],
            ['online', 'Supervisi Practitioner', null, 'Supervisi kasus untuk peserta program sertifikasi Practitioner & Master.', 250000, 120, 10, true],
            ['offline', 'Personal Session', 'R3', 'Sesi hipnoterapi privat tatap muka bersama Practitioner bersertifikat.', 750000, 120, 1, false],
            ['offline', '6R — Release', 'R3', 'Melepas muatan emosi tertahan secara aman dan terpandu.', 850000, 120, 1, false],
            ['offline', '6R — Reprogram Deep', 'R5B', 'Reprogram mendalam untuk pola kuat dan trauma.', 1200000, 150, 1, false],
            ['offline', 'Recovery Trauma', 'R5B', 'Program pemulihan trauma bertahap bersama Master Practitioner.', 1500000, 150, 1, false],
            ['offline', 'Relationship Couple', 'R4', 'Sesi pasangan untuk memperbaiki pola komunikasi dan luka relasi.', 1500000, 150, 2, false],
        ];

        foreach ($services as $i => [$modality, $name, $stage, $desc, $price, $duration, $capacity, $group]) {
            MindService::updateOrCreate(['slug' => Str::slug($name . '-' . $modality)], [
                'modality' => $modality,
                'name' => $name,
                'stage' => $stage,
                'description' => $desc,
                'price' => $price,
                'duration_minutes' => $duration,
                'capacity' => $capacity,
                'is_group' => $group,
                'is_active' => true,
                'sort_order' => $i + 1,
            ]);
        }
    }

    private function certifications(): void
    {
        $programs = [
            ['practitioner', 'Mind OS Practitioner Program', 'Alumni Foundation', 24, 3,
                ['24 Jam Teori & Praktik', 'Praktik 3 Klien', 'Laporan Kasus', 'Ujian & Sertifikasi'],
                'Menjadi praktisi Mind OS yang mampu mendampingi klien melalui tahapan 6R dengan etika dan teknik hipnoterapi yang aman.', 7500000],
            ['master', 'Mind OS Master Practitioner Program', 'Mind OS Practitioner', 54, 10,
                ['54 Jam Lanjutan', 'Praktik 10 Klien', 'Supervisi', 'Ujian & Sertifikasi'],
                'Pendalaman teknik Release dan Reprogram Deep untuk menangani trauma, relasi, dan kasus kompleks.', 15000000],
            ['trainer', 'Mind OS Trainer & Lisensi', 'Mind OS Master Practitioner', 24, 0,
                ['24 Jam TOT (Training of Trainers)', 'Mengajar 3 Workshop', 'Evaluasi'],
                'Lisensi resmi untuk mengajar Workshop Foundation dan membuka kelas Mind OS di kota Anda.', 25000000],
        ];

        foreach ($programs as $i => [$level, $name, $prereq, $hours, $clients, $curriculum, $desc, $price]) {
            CertificationProgram::updateOrCreate(['slug' => $level], [
                'level' => $level,
                'name' => $name,
                'prerequisite' => $prereq,
                'hours' => $hours,
                'practice_clients' => $clients,
                'curriculum' => $curriculum,
                'description' => $desc,
                'price' => $price,
                'sort_order' => $i + 1,
            ]);
        }
    }
}
