<?php

declare(strict_types=1);

namespace AGTHARN\VPNProtect\task;

use Logger;
use pocketmine\Server;
use AGTHARN\VPNProtect\Main;
use AGTHARN\VPNProtect\util\API;
use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\TextFormat as C;

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

        $failedChecks = 0;
        if (count($result) === 0) {
            $this->logger->debug('An error has occurred on VPN IPs! Please ensure others are affected before reporting!');
            return;
        }

        foreach ($result as $key => $value) {
            $exclusive = ['.vpn', '.proxy', '.tor', '.hosting'];
            if (Main::getInstance()->getConfig()->getNested(str_replace($exclusive, '', $key . '.enabled'))) {
                // just a quick hack to check if string contains $exclusive
                if (str_replace($exclusive, '', $key) !== $key) {
                    if (!Main::getInstance()->getConfig()->getNested($key)) {
                        return;
                    }
                }
                
                // NOTE: do not remove this strict check
                if ($value === true) {
                    $failedChecks++;
                    $this->logger->debug($this->playerName . ' has failed VPN ' . $key . '! (' . (string) $failedChecks . ')');
                } elseif (is_int($value)) {
                    $this->logger->debug('An error has occurred on VPN ' . $key . '! This can be ignored if other checks are not affected. Error ' . $value);
                }
            }
        }

        if ($failedChecks > 0) {
            if (Main::getInstance()->getConfig()->get('enable-kick', true) && $failedChecks >= Main::getInstance()->getConfig()->get('minimum-checks', 2)) {
                $player === null ? $this->logger->debug('An error has occurred when kicking a player! ') : $player->kick(C::colorize(Main::getInstance()->getConfig()->get('kick-message')));
            }
            $this->logger->debug($this->playerName . ' VPN Checks have been completed and player has failed! (' . (string) $failedChecks . ')');
            return;
        }
        $this->logger->debug($this->playerName . ' VPN Checks have been completed and player has passed! (' . (string) $failedChecks . ')');
    }
}
