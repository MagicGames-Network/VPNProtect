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
        
        $this->saveDefaultConfig();
        if (!$this->runChecks()) {
            return;
        }

        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
    }

    public function getEnabledAPIs(): array
    {
        $enabled = [];
        foreach ($this->getConfig()->get('checks', []) as $api => $data) {
            if ($data['enabled']) {
                $enabled[] = $api;
            }
        }
        var_dump($enabled);
        return $enabled;
    }

    private function runChecks(): bool
    {
        $minimumAPIs = $this->getConfig()->get('minimum-checks', 2) + 2;
        if (count($this->getEnabledAPIs()) <= $minimumAPIs) {
            $this->getLogger()->warning('Not enough APIs enabled to run checks! Please enable more than ' . $minimumAPIs. ' APIs.');
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return false;
        }
        return true;
    }
}
