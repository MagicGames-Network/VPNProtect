<?php

declare(strict_types=1);

namespace AGTHARN\VPNProtect;

use pocketmine\plugin\PluginBase;
use AGTHARN\VPNProtect\EventListener;

class Main extends PluginBase
{
    private static Main $instance;

    public static function getInstance(): Main
    {
        return self::$instance;
    }

    public function onEnable(): void
    {
        self::$instance = $this;
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
    }
}