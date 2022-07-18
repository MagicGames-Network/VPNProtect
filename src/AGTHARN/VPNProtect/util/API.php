<?php
declare(strict_types=1); 

namespace AGTHARN\VPNProtect\util;

use pocketmine\utils\Internet;
use pocketmine\utils\InternetRequestResult;

class API
{          
    public const CURL_ERROR = 0;

    public static function checkAll(string $ip, array $configs = null): array
    {   
        // This code originates from VPNProtect.
        if ($configs === null) $configs = self::getDefaults();
        $APIs = [
            'api1' => 'https://check.getipintel.net/check.php?ip=' . $ip . '&format=json&contact=idonthavetook@outlook.de&oflags=b',
            'api2' => 'https://proxycheck.io/v2/' . $ip . '?key=' . $configs['check2.key'],
            'api3' => 'https://api.iptrooper.net/check/' . $ip,
            'api4' => 'http://api.vpnblocker.net/v2/json/' . $ip . $configs['check4.key'],
            'api5' => 'https://api.ip2proxy.com/?ip=' . $ip . '&format=json&key=' . $configs['check5.key'],
            'api6' => 'https://vpnapi.io/api/' . $ip,
            // TODO: API7 HAS MADE API KEYS MANDATORY! THIS WILL NOT WORK UNTIL I FIX IT.
            'api7' => 'https://ipqualityscore.com/api/json/ip/' . $configs['check7.key'] . '/' . $ip . '?strictness=' . $configs['check7.strictness'] . '&allow_public_access_points=true&fast=' . $configs['check7.fast'] . '&lighter_penalties=' . $configs['check7.lighter_penalties'] . '&mobile=' . $configs['check7.mobile'],
            'api8' => 'http://v2.api.iphub.info/ip/' . $ip,
            'api9' => 'https://www.iphunter.info:8082/v1/ip/' . $ip,
            'api10' => 'https://ipinfo.io/' . $ip . '/json?token=' . $configs['check10.key']
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
                $results[$dataLabel] = self::CURL_ERROR;
                continue;
            }

            $decoded = json_decode($internetResult->getBody(), true);
            if (is_int($decoded)) {
                $results[$dataLabel] = self::parseIntResult($decoded);
                continue;
            }
            if ($decoded === null) {
                $results[$dataLabel] = self::CURL_ERROR;
                continue;
            }
            
            $results[$dataLabel] = self::parseArrayResult($decoded, $ip);
        }
        return $results;
    }

    private static function parseArrayResult(array $result, string $ip): bool|int
    {
        return match (true) {
            isset($result['BadIP']) => $result['BadIP'] >= 1 ? true : false,
            isset($result[$ip]['proxy']) => $result[$ip]['proxy'] === 'yes' ? true : false,
            isset($result['host-ip']) => $result['host-ip'],
            isset($result['isProxy']) => $result['isProxy'] === 'YES' ? true : false,
            isset($result['security']['vpn']) => $result['security']['vpn'],
            isset($result['security']['proxy']) => $result['security']['proxy'],
            isset($result['security']['tor']) => $result['security']['tor'],
            isset($result['vpn']) => $result['vpn'],
            isset($result['proxy']) => $result['proxy'],
            isset($result['tor']) => $result['tor'],
            isset($result['block']) => $result['block'] === 1 ? true : false,
            isset($result['data']['block']) => $result['data']['block'] === 1 ? true : false,
            isset($result['privacy']['vpn']) => $result['privacy']['vpn'],
            isset($result['privacy']['proxy']) => $result['privacy']['proxy'],
            isset($result['privacy']['tor']) => $result['privacy']['tor'],
            isset($result['privacy']['hosting']) => $result['privacy']['hosting'],
            default => self::CURL_ERROR
        };
    }

    private static function parseIntResult(int $result): bool
    {
        // right now, there's only 1 check using this so we can just return the result directly
        return $result === 1 ? true : false;
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
            'check10.key' => ''
        ];
    }
}