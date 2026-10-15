<?php

namespace App\Support;

/**
 * The Mind OS 6R method and which modality delivers each stage:
 * Web App (R1, R2, R6), Online Live (R4, R5A), Offline (R3, R5B).
 */
class SixR
{
    public const STAGES = [
        'R1' => ['name' => 'Recognize', 'modality' => 'web', 'desc' => 'Menyadari pola pikiran, emosi, dan reaksi tubuh yang muncul berulang.'],
        'R2' => ['name' => 'Reveal', 'modality' => 'web', 'desc' => 'Mengungkap akar masalah: keyakinan, pengalaman, dan luka di bawah sadar.'],
        'R3' => ['name' => 'Release', 'modality' => 'offline', 'desc' => 'Melepas muatan emosi yang tertahan bersama praktisi secara tatap muka.'],
        'R4' => ['name' => 'Reframe', 'modality' => 'online', 'desc' => 'Membingkai ulang makna peristiwa agar tidak lagi memicu cemas atau luka.'],
        'R5A' => ['name' => 'Reprogram — Guided', 'modality' => 'online', 'desc' => 'Menanam program baru lewat sesi hipnoterapi terpandu secara online.'],
        'R5B' => ['name' => 'Reprogram — Deep', 'modality' => 'offline', 'desc' => 'Reprogram mendalam untuk trauma & pola kuat secara tatap muka.'],
        'R6' => ['name' => 'Reinforce', 'modality' => 'web', 'desc' => 'Menguatkan program baru lewat audio harian, jurnal, dan action plan.'],
    ];

    public const MODALITIES = [
        'web' => ['label' => 'Web App', 'color' => 'primary'],
        'online' => ['label' => 'Online Live', 'color' => 'info'],
        'offline' => ['label' => 'Tatap Muka', 'color' => 'warning'],
    ];

    /** @return array{code:string,name:string,modality:string,desc:string,modality_label:string,color:string} */
    public static function stage(?string $code): array
    {
        $stage = self::STAGES[$code] ?? ['name' => (string) $code, 'modality' => 'web', 'desc' => ''];
        $modality = self::MODALITIES[$stage['modality']];

        return $stage + [
            'code' => (string) $code,
            'modality_label' => $modality['label'],
            'color' => $modality['color'],
        ];
    }

    public static function codes(): array
    {
        return array_keys(self::STAGES);
    }
}
