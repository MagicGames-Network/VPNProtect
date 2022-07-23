<?php

declare(strict_types=1);

namespace AGTHARN\VPNProtect;

use AGTHARN\VPNProtect\Main;
use AGTHARN\libVPN\util\Cache;
use pocketmine\event\Listener;
use AGTHARN\VPNProtect\task\AsyncCheckTask;
use pocketmine\event\player\PlayerLoginEvent;

class EventListener implements Listener
{
    public function __construct(
        private Main $plugin
    ) {
    }

    public function onPlayerLogin(PlayerLoginEvent $event): void
    {
        $player = $event->getPlayer();
        if (!$player->hasPermission('vpnprotect.bypass')) {
            if (Main::getInstance()->getConfig()->get('enable-cache', true)) {
                $result = Cache::get($player->getNetworkSession()->getIp());
                if ($result !== null) {
                    if ($result) {
                        $this->plugin->getLogger()->debug($player->getName() . ' has results cached and had passed! Skipping...');
                        return;
                    }
                    return;
                }
            }

            // TODO: IF LARGE NUMBER OF PLAYERS JOIN LIKE A BOT ATTACK, THE ASYNCPOOL WILL BE FLOODED!
            $this->plugin->getServer()->getAsyncPool()->submitTask(new AsyncCheckTask($this->plugin->getLogger(), $player->getNetworkSession()->getIp(), $player->getName(), [
                'include' => $this->plugin->getEnabledAPIs(),
                'config' => [
                    'api2.key' => $this->plugin->getConfig()->getNested('checks.api2.key', ''),
                    'api4.key' => $this->plugin->getConfig()->getNested('checks.api4.key', ''),
                    'api5.key' => $this->plugin->getConfig()->getNested('checks.api5.key', ''),
                    'api7.key' => $this->plugin->getConfig()->getNested('checks.api7.key', ''),
                    'api7.mobile' => $this->plugin->getConfig()->getNested('checks.api7.mobile', true),
                    'api7.fast' => $this->plugin->getConfig()->getNested('checks.api7.fast', false),
                    'api7.strictness' => $this->plugin->getConfig()->getNested('checks.api7.strictness', 0),
                    'api7.lighter_penalties' => $this->plugin->getConfig()->getNested('checks.api7.lighter_penalties', true),
                    'api8.key' => $this->plugin->getConfig()->getNested('checks.api8.key', ''),
                    'api9.key' => $this->plugin->getConfig()->getNested('checks.api9.key', ''),
                    'api10.key' => $this->plugin->getConfig()->getNested('checks.api10.key', ''),
                    'api11.key' => $this->plugin->getConfig()->getNested('checks.api11.key', '')
                ],
                'minimum-checks' => $this->plugin->getConfig()->get('minimum-checks', 2),
                'smart-queries' => $this->plugin->getConfig()->get('smart-queries', true)
            ]));
        }
    }
}
