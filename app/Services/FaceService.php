<?php

namespace App\Services;

class FaceService
{
    /**
     * Ambang jarak kosinus untuk dianggap wajah yang sama (face-api.js convention).
     * Semakin kecil, semakin ketat.
     */
    public const MATCH_DISTANCE = 0.55;

    /**
     * Jarak kosinus antara dua vektor 128 dimensi (1 = jauh, 0 = identik).
     */
    public function cosineDistance(array $a, array $b): float
    {
        $a = array_values(array_map('floatval', $a));
        $b = array_values(array_map('floatval', $b));

        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        foreach ($a as $i => $value) {
            $dot += $value * $b[$i];
            $normA += $value ** 2;
            $normB += $b[$i] ** 2;
        }

        if ($normA == 0.0 || $normB == 0.0) {
            return 1.0;
        }

        return 1.0 - ($dot / (sqrt($normA) * sqrt($normB)));
    }

    /**
     * True jika dua vektor dianggap berasal dari wajah yang sama.
     */
    public function matches(array $a, array $b): bool
    {
        return $this->cosineDistance($a, $b) <= self::MATCH_DISTANCE;
    }
}
