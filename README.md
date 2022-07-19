# VPNProtect
![VPNProtect](https://media.discordapp.net/attachments/973097510748966982/980656191250247770/VPNProtect.gif?width=1416&height=663)

Are banned players joining back on different IPs each time and you have no idea how to stop them? Have you been tired of constantly needing to ban them? No worries, this plugin has got you covered!

The main purpose of this plugin is to block the usage of VPNs on PMMP servers. It has been proven to be effective with multiple free and paid VPNs like McAfee VPN, NordVPN, Cryptostorm, TunnelBear and more!

However, it may not always be reliable as it uses free API providers for this purpose.

Credits to SpigotMC's [AntiVPN](https://www.spigotmc.org/resources/anti-vpn.58291/) for the inspiration!

**NOTE: This plugin is still in its early stages of development so you may encounter issues using it. Feel free to test it for bugs!**

## FAQ
- [How does this plugin exactly work?](#how-does-this-plugin-exactly-work)
- [By any chance would this plugin fail?](#by-any-chance-would-this-plugin-fail)
- [How do I suggest a new API to be used?](#how-do-i-suggest-a-new-api-to-be-used)
- [Will this plugin cause my server to lag?](#will-this-plugin-cause-my-server-to-lag)
- [How do I integrate it into my own plugin?](#how-do-i-integrate-it-into-my-own-plugin)

### How does this plugin exactly work?
This plugin uses the APIs offered by different web services that make the checks possible. The ones used are free and I can't 100% guarantee that your IPs are in safe hands, but the ones used may be trusted!

When a player joins your server, it passes the player's IP to these APIs which would then check if its a VPN. The results would be collected by the plugin and be used to make necessary actions against the player.

### By any chance would this plugin fail?
Yes. This may or may not be bypassable and would probably be troublesome to attempt. Since this plugin uses free APIs, the APIs have limited the maximum usage every day/week. If you've noticed that some checks stop working, that may be the reason.

There may also be false flags. However, there is a system put in place to prevent this and it is located in the configuration where you can change the minimum detected checks before an action.

**IT IS HIGHLY RECOMMENDED THAT YOU SET UP ALL THE API KEYS AND USE ALL THAT'S PROVIDED!**

### How do I suggest a new API to be used?
First, you need to make sure it is a trusted source. If you're very sure it can be trusted and be used for the public, you can suggest it by opening an issue.

### Will this plugin cause my server to lag?
It mostly depends on your server resources. However, this plugin shouldn't cause any lag issues as the checks run on AsyncTask which will not slow down the main thread.

### How do I integrate it into my own plugin?
There are two ways you can do it and you must have basic knowledge.

```php
// Use the async task within the plugin. Recommended to do so as queries hog the main thread:
Server::getInstance()->getAsyncPool()->submitTask(new AsyncCheckTask(Main::getInstance()->getLogger(), $player->getNetworkSession()->getIp(), $player->getName(), API::getDefaults()));
```

```php
// Use without running it asyncly. Not recommended unless you know what you're doing:
API::checkAll($ip, API::getDefaults());
```