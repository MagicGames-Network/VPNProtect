<?php

declare(strict_types=1);

namespace AGTHARN\libVPN;

use pocketmine\utils\Internet;
use AGTHARN\libVPN\util\Parser;
use pocketmine\utils\InternetRequestResult;

class API
{
    public static function checkAll(string $ip, array $configs = null): array
    {
        // This code originates from VPNProtect.
        $results = [];
        foreach (Parser::parseMapping($ip, $configs ?? self::getDefaults()) as $key => $data) {
            $internetResult = Internet::getURL($data['url'], 5, $data['header'] ?? []);
            if (!$internetResult instanceof InternetRequestResult) {
                $results[$key] = 'Request error';
                continue;
            }

            $results[$key] = Parser::parseResult(json_decode($internetResult->getBody(), true) ?? $internetResult->getBody(), $ip);
        }
        return $results;
    }

    public static function getDefaults(): array
    {
        return [
            'api2.key' => '',
            'api4.key' => '',
            'api5.key' => 'demo',
            'api7.key' => 'demo',
            'api7.mobile' => true,
            'api7.fast' => false,
            'api7.strictness' => 0,
            'api7.lighter_penalties' => true,
            'api8.key' => '',
            'api9.key' => '',
            'api10.key' => '',
            'api11.key' => ''
        ];
    }
}
