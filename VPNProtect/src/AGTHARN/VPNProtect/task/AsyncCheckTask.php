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
use AGTHARN\libVPN\util\Algorithm;
use pocketmine\scheduler\AsyncTask;

class AsyncCheckTask extends AsyncTask
{
    private string $options;

    public function __construct(
        private Logger $logger,
        private string $playerIP,
        private string $playerName,
        array $options
    ) {
        $this->options = serialize($options);
    }

    public function onRun(): void
    {
        $options = unserialize($this->options);
        if ($options['smart-queries'] ?? true) {
            $this->setResult(API::getSmartResults($this->playerIP, $options));
            return;
        }
        $this->setResult(API::getVPNResults($this->playerIP, $options));
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

        if ($failedChecks > 0) {
            if (Main::getInstance()->getConfig()->get('enable-kick', true) && $failedChecks >= Main::getInstance()->getConfig()->get('minimum-checks', 2)) {
                $player->kick(TextFormat::colorize(Main::getInstance()->getConfig()->get('kick-message')));
                $this->addCache(false);
            }
            $this->logger->debug($this->playerName . ' VPN Checks have been completed and player has failed! (' . $failedChecks . ')');
            $this->addCache(true);
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
