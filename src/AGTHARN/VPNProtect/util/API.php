<?php

declare(strict_types=1);

namespace AGTHARN\VPNProtect\util;

use pocketmine\utils\Internet;
use pocketmine\utils\InternetRequestResult;

class API
{
    public const REQUEST_ERROR = 0;
    public const PARSE_ERROR = 1;

    public static function checkAll(string $ip, array $configs = null): array
    {
        // This code originates from VPNProtect.
        $results = [];
        foreach (Parser::parseMapping($ip, $configs ?? self::getDefaults()) as $key => $data) {
            $dataLabel = str_replace('api', 'check', $key);

            $internetResult = Internet::getURL($data['url'], 5, $data['header'] ?? []);
            if (!$internetResult instanceof InternetRequestResult) {
                $results[$dataLabel] = self::REQUEST_ERROR;
                continue;
            }

            $results[$dataLabel] = Parser::parseResult(json_decode($internetResult->getBody(), true), $ip);
        }
        return $results;
    }

    public static function getDefaults(): array
    {
        return [
            'check2.key' => '',
            'check4.key' => '',
            'check5.key' => 'demo',
            'check7.key' => 'demo',
            'check7.mobile' => true,
            'check7.fast' => false,
            'check7.strictness' => 0,
            'check7.lighter_penalties' => true,
            'check8.key' => '',
            'check9.key' => '',
            'check10.key' => '',
            'check11.key' => ''
        ];
    }
}
