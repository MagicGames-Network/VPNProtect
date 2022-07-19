<?php

declare(strict_types=1);

namespace AGTHARN\VPNProtect\util;

class Parser
{
    public static function parseResult(mixed $result, string $ip): bool|string
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
                isset($result['proxy']) => $result['proxy'], // 7, 11, 12
                isset($result['tor']) => $result['tor'], // 7
                isset($result['block']) => $result['block'] === 1 ? true : false, // 8
                isset($result['data']['block']) => $result['data']['block'] === 1 ? true : false, // 9
                isset($result['privacy']['vpn']) => $result['privacy']['vpn'], // 10
                isset($result['privacy']['proxy']) => $result['privacy']['proxy'], // 10
                isset($result['privacy']['tor']) => $result['privacy']['tor'], // 10
                isset($result['privacy']['hosting']) => $result['privacy']['hosting'], // 10
                default => self::parseError($result)
            };
        }
        if (is_int($result)) {
            // right now, there's only 1 check using this so we can just return the result directly (3)
            return $result === 1 ? true : false;
        }
        return self::parseError($result);
    }

    public static function parseError(mixed $result): string
    {
        return match (true) {
            isset($result["message"]) => $result["message"], // 1, 2, 6, 7
            isset($result["msg"]) => $result["msg"], // 4, 9
            isset($result["response"]) => $result["response"], // 5
            isset($result["error"]) => $result["error"], // 8
            isset($result["error"]["message"]) => $result["error"]["message"], // 10
            default => "Unknown error" // 12
        };
    }

    public static function parseMapping(string $ip, array $configs): array
    {
        return [
            'api1' => [
                'url' => 'https://check.getipintel.net/check.php?ip=' . $ip . '&format=json&contact=' . self::generateRandom(mt_rand(6, 10)) . '@outlook.de&oflags=b'
            ],
            'api2' => [
                'url' => 'https://proxycheck.io/v2/' . $ip . '?key=' . $configs['check2.key']
            ],
            'api3' => [
                'url' => 'https://api.iptrooper.net/check/' . $ip
            ],
            'api4' => [
                'url' => 'http://api.vpnblocker.net/v2/json/' . $ip . $configs['check4.key']
            ],
            'api5' => [
                'url' => 'https://api.ip2proxy.com/?ip=' . $ip . '&format=json&key=' . $configs['check5.key']
            ],
            'api6' => [
                'url' => 'https://vpnapi.io/api/' . $ip
            ],
            'api7' => [
                'url' => 'https://ipqualityscore.com/api/json/ip/' . $configs['check7.key'] . '/' . $ip . '?strictness=' . $configs['check7.strictness'] . '&allow_public_access_points=true&fast=' . $configs['check7.fast'] . '&lighter_penalties=' . $configs['check7.lighter_penalties'] . '&mobile=' . $configs['check7.mobile']
            ],
            'api8' => [
                'url' => 'http://v2.api.iphub.info/ip/' . $ip,
                'header' => ['X-Key: ' . $configs['check8.key']]
            ],
            'api9' => [
                'url' => 'https://www.iphunter.info:8082/v1/ip/' . $ip,
                'header' => ['X-Key: ' . $configs['check9.key']]
            ],
            'api10' => [
                'url' => 'https://ipinfo.io/' . $ip . '/json?token=' . $configs['check10.key']
            ],
            'api11' => [
                'url' => 'https://funkemunky.cc/vpn?ip=' . $ip . '&license=' . $configs['check11.key']
            ],
            'api12' => [
                'url' => 'http://ip-api.com/json/' . $ip . '?fields=proxy'
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
