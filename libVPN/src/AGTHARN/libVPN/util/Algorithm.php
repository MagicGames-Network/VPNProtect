<?php

declare(strict_types=1);

namespace AGTHARN\libVPN\util;

use AGTHARN\libVPN\API;

class Algorithm
{
    public static function getIncludes(int $minimumChecks = 2): array
    {
        $timings = array_flip(API::$timingCache);
        ksort($timings, SORT_NUMERIC);
        $detection = array_flip(API::$detectionCount);
        krsort($detection, SORT_NUMERIC);

        $responseTop = array_slice($timings, 0, $minimumChecks + 2, true);
        $detectionTop = array_slice($detection, 0, $minimumChecks + 2, true);

        $intersect = array_intersect($responseTop, $detectionTop);
        if (count($intersect) < $minimumChecks) {
            // TODO: Better logic for this
            $intersect = $responseTop;
        }
        return $intersect;
    }
}
