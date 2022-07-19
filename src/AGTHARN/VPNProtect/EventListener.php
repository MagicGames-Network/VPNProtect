<?php

declare(strict_types=1);

namespace AGTHARN\VPNProtect;

use AGTHARN\VPNProtect\Main;
use pocketmine\event\Listener;
use AGTHARN\VPNProtect\task\AsyncCheckTask;
use pocketmine\event\player\PlayerJoinEvent;

class EventListener implements Listener
{
    private Main $plugin;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onPlayerJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();
        if (!$player->hasPermission("vpnprotect.bypass")) {
            // TODO: IF LARGE NUMBER OF PLAYERS JOIN LIKE A BOT ATTACK, THE ASYNCPOOL WILL BE FLOODED!
            $this->plugin->getServer()->getAsyncPool()->submitTask(new AsyncCheckTask($this->plugin->getLogger(), $player->getNetworkSession()->getIp(), $player->getName(), [
                'check2.key' => $this->plugin->getConfig()->getNested('check2.key', ''),
                'check4.key' => $this->plugin->getConfig()->getNested('check4.key', ''),
                'check5.key' => $this->plugin->getConfig()->getNested('check5.key', 'demo'),
                'check7.key' => $this->plugin->getConfig()->getNested('check7.key', 'demo'),
                'check7.mobile' => $this->plugin->getConfig()->getNested('check7.mobile', true),
                'check7.fast' => $this->plugin->getConfig()->getNested('check7.fast', false),
                'check7.strictness' => $this->plugin->getConfig()->getNested('check7.strictness', 0),
                'check7.lighter_penalties' => $this->plugin->getConfig()->getNested('check7.lighter_penalties', true),
                'check8.key' => $this->plugin->getConfig()->getNested('check8.key', ''),
                'check9.key' => $this->plugin->getConfig()->getNested('check9.key', ''),
                'check10.key' => $this->plugin->getConfig()->getNested('check10.key', ''),
                'check11.key' => $this->plugin->getConfig()->getNested('check11.key', '')
            ]));
        }
    }
}
