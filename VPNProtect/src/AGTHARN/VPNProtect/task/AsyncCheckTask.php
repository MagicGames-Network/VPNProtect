<?php

declare(strict_types=1);

namespace AGTHARN\VPNProtect\task;

use Logger;
use pocketmine\Server;
use AGTHARN\libVPN\API;
use AGTHARN\VPNProtect\Main;
use pocketmine\player\Player;
use AGTHARN\libVPN\util\Cache;
use pocketmine\utils\TextFormat;
use pocketmine\scheduler\AsyncTask;

class AsyncCheckTask extends AsyncTask
{
    private string $configs;

    public function __construct(
        private Logger $logger,
        private string $playerIP,
        private string $playerName,
        array $configs
    ) {
        $this->configs = serialize($configs);
    }

    public function onRun(): void
    {
        $this->setResult(API::checkAll($this->playerIP, unserialize($this->configs)));
    }

    public function onCompletion(): void
    {
        $taskResult = $this->getResult();
        $player = Server::getInstance()->getPlayerExact($this->playerName) ?? null;
        if (!$player instanceof Player) {
            $this->logger->debug('The player, ' . $this->playerName . ' does not exist! Skipping...');
            return;
        }

        $failedChecks = 0;
        foreach ($taskResult as $key => $data) {
            $vpnResult = $data[0];
            $responseMs = round($data[1] * 0.001);

            $exclusive = ['.vpn', '.proxy', '.tor', '.hosting', '.relay'];
            if (Main::getInstance()->getConfig()->getNested(str_replace($exclusive, '', $key . '.enabled'), true)) {
                if (!Main::getInstance()->getConfig()->getNested($key)) {
                    continue;
                }

                // NOTE: do not remove this strict check
                if ($vpnResult === true) {
                    $failedChecks++;
                    $this->logger->debug($this->playerName . ' has failed ' . $key . '! (' . $failedChecks . ') ' . $responseMs . 'ms');
                } elseif ($vpnResult === false) {
                    $this->logger->debug($this->playerName . ' has passed ' . $key . '! ' . $responseMs . 'ms');
                } elseif (is_string($vpnResult)) {
                    $this->logger->debug('An error has occurred on ' . $key . '! This can be ignored if other checks are not affected. Error: "' . $vpnResult . '" ' . $responseMs . 'ms');
                }
            }
        }

        if ($failedChecks > 0) {
            if (Main::getInstance()->getConfig()->get('enable-kick', true) && $failedChecks >= Main::getInstance()->getConfig()->get('minimum-checks', 2)) {
                $player->kick(TextFormat::colorize(Main::getInstance()->getConfig()->get('kick-message')));
            }
            $this->logger->debug($this->playerName . ' VPN Checks have been completed and player has failed! (' . $failedChecks . ')');
            $this->addCache(false);
            return;
        }
        $this->logger->debug($this->playerName . ' VPN Checks have been completed and player has passed! (' . $failedChecks . ')');
        $this->addCache(true);
    }

    private function addCache(bool $passed): void
    {
        if (Main::getInstance()->getConfig()->get('enable-cache', true)) {
            Cache::add($this->playerIP, $passed, Main::getInstance()->getConfig()->get('cache-limit', 50));
        }
    }
}
