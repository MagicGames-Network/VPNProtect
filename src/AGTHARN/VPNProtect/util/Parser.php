<?php

declare(strict_types=1);

namespace AGTHARN\VPNProtect\util;

class Parser
{
    public static function parseResult(mixed $result, string $ip): bool|int
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
                default => API::PARSE_ERROR
            };
        }
        if (is_int($result)) {
            // right now, there's only 1 check using this so we can just return the result directly (3)
            return $result === 1 ? true : false;
        }
        return API::PARSE_ERROR;
    }

    public static function parseMapping(string $ip, array $configs): array
    {
        return [
            'api1' => [
                'https://check.getipintel.net/check.php?ip=' . $ip . '&format=json&contact=' . self::generateRandom(mt_rand(0, 10)) . '@outlook.de&oflags=b',
                []
            ],
            'api2' => [
                'https://proxycheck.io/v2/' . $ip . '?key=' . $configs['check2.key'],
                []
            ],
            'api3' => [
                'https://api.iptrooper.net/check/' . $ip,
                []
            ],
            'api4' => [
                'http://api.vpnblocker.net/v2/json/' . $ip . $configs['check4.key'],
                []
            ],
            'api5' => [
                'https://api.ip2proxy.com/?ip=' . $ip . '&format=json&key=' . $configs['check5.key'],
                []
            ],
            'api6' => [
                'https://vpnapi.io/api/' . $ip,
                []
            ],
            'api7' => [
                'https://ipqualityscore.com/api/json/ip/' . $configs['check7.key'] . '/' . $ip . '?strictness=' . $configs['check7.strictness'] . '&allow_public_access_points=true&fast=' . $configs['check7.fast'] . '&lighter_penalties=' . $configs['check7.lighter_penalties'] . '&mobile=' . $configs['check7.mobile'],
                []
            ],
            'api8' => [
                'http://v2.api.iphub.info/ip/' . $ip,
                ['X-Key: ' . $configs['check8.key']]
            ],
            'api9' => [
                'https://www.iphunter.info:8082/v1/ip/' . $ip,
                ['X-Key: ' . $configs['check9.key']]
            ],
            'api10' => [
                'https://ipinfo.io/' . $ip . '/json?token=' . $configs['check10.key'],
                []
            ],
            'api11' => [
                'https://funkemunky.cc/vpn?ip=' . $ip . '&license=' . $configs['check11.key'],
                []
            ]
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
