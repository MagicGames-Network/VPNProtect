<?php

declare(strict_types=1);

namespace AGTHARN\VPNProtect\task;

use Logger;
use pocketmine\Server;
use AGTHARN\VPNProtect\Main;
use pocketmine\player\Player;
use AGTHARN\VPNProtect\util\API;
use AGTHARN\VPNProtect\util\Cache;
use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\TextFormat;

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
        $result = $this->getResult();
        $player = Server::getInstance()->getPlayerExact($this->playerName) ?? null;
        if (!$player instanceof Player) {
            $this->logger->debug('The player, ' . $this->playerName . ' does not exist! Skipping...');
            return;
        }

        $failedChecks = 0;
        foreach ($result as $key => $value) {
            $exclusive = ['.vpn', '.proxy', '.tor', '.hosting', '.relay'];
            if (Main::getInstance()->getConfig()->getNested(str_replace($exclusive, '', $key . '.enabled'), true)) {
                if (!Main::getInstance()->getConfig()->getNested($key)) {
                    return;
                }

                // NOTE: do not remove this strict check
                if ($value === true) {
                    $failedChecks++;
                    $this->logger->debug($this->playerName . ' has failed VPN ' . $key . '! (' . $failedChecks . ')');
                } elseif (is_string($value)) {
                    $this->logger->debug('An error has occurred on VPN ' . $key . '! This can be ignored if other checks are not affected. Error: "' . $value . '"');
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
