<?php

declare(strict_types=1);

namespace iMD14\ServerSwitcher;

use pocketmine\plugin\PluginBase;
use pocketmine\player\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use iMD14\ServerSwitcher\VersionInfo;
use pocketmine\Server;

class Main extends PluginBase{
  public $versionInfo = VersionInfo::class;
  public $serversConfig;
  public $version;
  public $data;
public function reloadConfig(): void{
  $this->serversConfig->reload();
  }
public function onEnable(): void{
    $url = "https://poggit.pmmp.io/plugins.json?name=ServerSwitcher";
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    $latestVersion = $data[0]['version'];
  
    if ($this->versionInfo::VERSION !== $latestVersion) {
        $message = "Your plugin is not updated! Latest version: $latestVersion";
        $this->getServer()->getLogger()->warning($message);
    }
    @mkdir($this->getDataFolder());
    $this->saveResource("servers.yml");
    #$this->saveDefaultConfig();
    $this->getResource("servers.yml");
    $this->servers = new Config($this->getDataFolder() . "servers.yml", Config::YAML);
  }

public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
    switch($command->getName()) {
        case "server":
            if(count($args) !== 1) {
                $sender->sendMessage("/server <name>");
                return false;
            }
            $config = new Config($this->getDataFolder() . "servers.yml", Config::YAML);
            $servers = $config->get("servers");

            $name = $args[0];
            foreach($servers as $server) {
                if($server['name'] == $name) {
                    $sender->sendMessage("Taking you to " . $server['ip'] . ":" . $server['port']);
                    $sender->sendMessage($server['ip'] . ":" . $server['port']);
                    if ($sender instanceof Player) {
                        $sender->transfer($server['ip'], $server['port']);
                    } else {
                        $sender->sendMessage("Cannot transfer you to " . $server['name']);
                    }
                    return true;
                }
            }
            $sender->sendMessage("Server not found: $name");
            return false;
        default:
            return false;
        case "servers":
                $this->serversConfig = new Config($this->getDataFolder() . "servers.yml", Config::YAML);

                $serverData = $this->serversConfig->getAll()["servers"] ?? [];

                $serverNames = [];
                foreach ($serverData as $index => $server) {
                    $name = $server["name"] ?? "Unknown";
                    $serverNames[] = "Server" . ($index + 1);
                }

                $sender->sendMessage(implode(", ", $serverNames));

                return true;
        case "swrconfig":
            $this->reloadConfig();
            $sender->sendMessage("Config Reloaded successfully");
            return true;
        }
    }
}
