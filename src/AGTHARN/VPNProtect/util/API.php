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
        if ($configs === null) $configs = self::getDefaults();
        $APIs = [
            'api1' => 'https://check.getipintel.net/check.php?ip=' . $ip . '&format=json&contact=' . self::generateRandom(mt_rand(0, 10)) . '@outlook.de&oflags=b',
            'api2' => 'https://proxycheck.io/v2/' . $ip . '?key=' . $configs['check2.key'],
            'api3' => 'https://api.iptrooper.net/check/' . $ip,
            'api4' => 'http://api.vpnblocker.net/v2/json/' . $ip . $configs['check4.key'],
            'api5' => 'https://api.ip2proxy.com/?ip=' . $ip . '&format=json&key=' . $configs['check5.key'],
            'api6' => 'https://vpnapi.io/api/' . $ip,
            'api7' => 'https://ipqualityscore.com/api/json/ip/' . $configs['check7.key'] . '/' . $ip . '?strictness=' . $configs['check7.strictness'] . '&allow_public_access_points=true&fast=' . $configs['check7.fast'] . '&lighter_penalties=' . $configs['check7.lighter_penalties'] . '&mobile=' . $configs['check7.mobile'],
            'api8' => 'http://v2.api.iphub.info/ip/' . $ip,
            'api9' => 'https://www.iphunter.info:8082/v1/ip/' . $ip,
            'api10' => 'https://ipinfo.io/' . $ip . '/json?token=' . $configs['check10.key'],
            'api11' => 'https://funkemunky.cc/vpn?ip=' . $ip . '&license=' . $configs['check11.key'],
        ];
        $apiHeaders = [
            'api8_header' => ['X-Key: ' . $configs['check8.key']],
            'api9_header' => ['X-Key: ' . $configs['check9.key']]
        ];

        $results = [];
        foreach ($APIs as $key => $value) {
            $dataLabel = str_replace('api', 'check', $key);

            $internetResult = Internet::getURL($value, 10, $apiHeaders[$key . '_header'] ?? []);
            if (!$internetResult instanceof InternetRequestResult) {
                $results[$dataLabel] = self::REQUEST_ERROR;
                continue;
            }

            $results[$dataLabel] = self::parseResult(json_decode($internetResult->getBody(), true), $ip);
        }
        return $results;
    }

    private static function parseResult(mixed $result, string $ip): bool|int
    {
        if (is_array($result)) {
            return match (true) {
                isset($result['BadIP']) => $result['BadIP'] >= 1 ? true : false, // 1
                isset($result[$ip]['proxy']) => $result[$ip]['proxy'] === 'yes' ? true : false, // 2
                isset($result['host-ip']) => $result['host-ip'], // 4
                isset($result['isProxy']) => $result['isProxy'] === 'YES' ? true : false, // 5
                isset($result['security']['vpn']) => $result['security']['vpn'], // 6
                isset($result['security']['proxy']) => $result['security']['proxy'], // 6
                isset($result['security']['tor']) => $result['security']['tor'], // 6
                isset($result['security']['relay']) => $result['security']['relay'], // 6
                isset($result['vpn']) => $result['vpn'], // 7
                isset($result['proxy']) => $result['proxy'], // 7 and 11
                isset($result['tor']) => $result['tor'], // 7
                isset($result['block']) => $result['block'] === 1 ? true : false, // 8
                isset($result['data']['block']) => $result['data']['block'] === 1 ? true : false, // 9
                isset($result['privacy']['vpn']) => $result['privacy']['vpn'], // 10
                isset($result['privacy']['proxy']) => $result['privacy']['proxy'], // 10
                isset($result['privacy']['tor']) => $result['privacy']['tor'], // 10
                isset($result['privacy']['hosting']) => $result['privacy']['hosting'], // 10
                default => self::PARSE_ERROR
            };
        }
        if (is_int($result)) {
            // right now, there's only 1 check using this so we can just return the result directly (3)
            return $result === 1 ? true : false;
        }
        return self::PARSE_ERROR;
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

    private static function generateRandom(int $charLimit): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $charLimit; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }
        return $randomString;
    }
}
