<?php

declare(strict_types=1);

namespace AGTHARN\libVPN;

use CurlHandle;
use pocketmine\utils\Internet;
use AGTHARN\libVPN\util\Parser;
use AGTHARN\libVPN\util\Algorithm;
use pocketmine\utils\InternetException;

class API
{
    protected static array $tempCache = [];

    public static array $timingCache = [];
    public static array $detectionCount = [];

    public static function getVPNResults(string $ip, array $options = []): array
    {
        // This code originates from VPNProtect.
        $results = [];
        foreach (Parser::parseMapping($ip, $options["config"] ?? self::getDefaults()) as $key => $data) {
            if (in_array($key, $options["exclude"] ?? [])) {
                continue;
            }
            if (!empty($options["include"] ?? []) && !in_array($key, $options["include"] ?? [])) {
                continue;
            }

            try {
                $internetResult = Internet::simpleCurl($data['url'], 5, $data['header'] ?? [], [], function (CurlHandle $ch) use ($ip, $key) {
                    self::$tempCache[$ip] = curl_getinfo($ch, CURLINFO_TOTAL_TIME_T);
                    self::$timingCache[$key] = curl_getinfo($ch, CURLINFO_TOTAL_TIME_T);
                });
            } catch (InternetException $ex) {
                $results[$key] = ['Request error', 0];
                continue;
            }

            $parsedResult = Parser::parseResult(json_decode($internetResult->getBody(), true) ?? $internetResult->getBody(), $ip);
            // NOTE: do not remove this strict check
            if ($parsedResult === true) {
                if (!isset(self::$detectionCount[$key])) {
                    self::$detectionCount[$key] = 0;
                }
                self::$detectionCount[$key]++;
            }

            $results[$key] = [
                $parsedResult,
                self::$tempCache[$ip] ?? 0
            ];
        }

        if (isset(self::$tempCache[$ip])) {
            unset(self::$tempCache[$ip]);
        }
        return $results;
    }

    /**
     * @see This will not return all results!
     */
    public static function getSmartResults(string $ip, array $options = []): array
    {
        if (!empty(self::$timingCache)) {
            return self::getVPNResults($ip, array_merge($options, [
                'include' => Algorithm::getIncludes($options['minimum-checks'] ?? 2)
            ]));
        }
        return self::getVPNResults($ip, $options);
    }

    public static function getDefaults(): array
    {
        return [
            'api2.key' => '',
            'api4.key' => '',
            'api5.key' => '',
            'api7.key' => '',
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
