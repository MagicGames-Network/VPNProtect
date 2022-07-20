<?php

declare(strict_types=1);

namespace AGTHARN\VPNProtect;

use AGTHARN\VPNProtect\Main;
use AGTHARN\libVPN\util\Cache;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use AGTHARN\VPNProtect\task\AsyncCheckTask;
use pocketmine\event\player\PlayerJoinEvent;

class EventListener implements Listener
{
    public function __construct(
        private Main $plugin
    ) {
    }

    public function onPlayerJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();
        if (!$player->hasPermission('vpnprotect.bypass')) {
            if (Main::getInstance()->getConfig()->get('enable-cache', true)) {
                $result = Cache::$results[$player->getNetworkSession()->getIp()] ?? null;
                if ($result !== null) {
                    if ($result) {
                        $this->plugin->getLogger()->debug($player->getName() . ' has results cached and had passed! Skipping...');
                        return;
                    }
                    $this->plugin->getLogger()->debug($player->getName() . ' has results cached and had failed!');
                    $player->kick(TextFormat::colorize(Main::getInstance()->getConfig()->get('kick-message')));
                    return;
                }
            }

            // TODO: IF LARGE NUMBER OF PLAYERS JOIN LIKE A BOT ATTACK, THE ASYNCPOOL WILL BE FLOODED!
            $this->plugin->getServer()->getAsyncPool()->submitTask(new AsyncCheckTask($this->plugin->getLogger(), $player->getNetworkSession()->getIp(), $player->getName(), [
                'api2.key' => $this->plugin->getConfig()->getNested('api2.key', ''),
                'api4.key' => $this->plugin->getConfig()->getNested('api4.key', ''),
                'api5.key' => $this->plugin->getConfig()->getNested('api5.key', 'demo'),
                'api7.key' => $this->plugin->getConfig()->getNested('api7.key', 'demo'),
                'api7.mobile' => $this->plugin->getConfig()->getNested('api7.mobile', true),
                'api7.fast' => $this->plugin->getConfig()->getNested('api7.fast', false),
                'api7.strictness' => $this->plugin->getConfig()->getNested('api7.strictness', 0),
                'api7.lighter_penalties' => $this->plugin->getConfig()->getNested('api7.lighter_penalties', true),
                'api8.key' => $this->plugin->getConfig()->getNested('api8.key', ''),
                'api9.key' => $this->plugin->getConfig()->getNested('api9.key', ''),
                'api10.key' => $this->plugin->getConfig()->getNested('api10.key', ''),
                'api11.key' => $this->plugin->getConfig()->getNested('api11.key', '')
            ]));
        }
    }
}
