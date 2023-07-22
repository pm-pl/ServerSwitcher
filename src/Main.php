<?php

declare(strict_types=1);

namespace iMD14\ServerSwitcher;

use pocketmine\{
    command\Command,
    command\CommandSender,
    plugin\PluginBase,
    player\Player,
    utils\Config,
    Server
};

use libpmquery\{
    PMQuery,
    PmQueryException
};

class Main extends PluginBase{
  public $serversConfig;
  public $servers;
  
  public function reloadConfig(): void{
    $this->serversConfig->reload();
  }
  
  public function onEnable(): void{
    $this->saveResource("servers.yml");
    $this->servers = new Config($this->getDataFolder() . "servers.yml", Config::YAML);
  }

  public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
    switch($command->getName()) {
        case "server":
            if(count($args) !== 1) {
                $sender->sendMessage("§4/server <name>");
                return false;
            }
            $config = new Config($this->getDataFolder() . "servers.yml", Config::YAML);
            $servers = $config->get("servers");

            $name = $args[0];
            foreach($servers as $server) {
                if($server['name'] == $name) {
                    // Exception
                    try{
                        $query = PMQuery::query($server['ip'], $server['port']);
                        
                        $sender->sendMessage("§6Taking you to " . $server['ip'] . ":" . $server['port']);
                        if ($sender instanceof Player) {
                        $sender->transfer($server['ip'], $server['port']);
                        }

                    } catch PmQueryException $e){
                        $this->sendMessage("§4An error occurred while trying to transfer you");
                    }}
                    return true;
                }
            }
            $sender->sendMessage("§4The server `$name` cannot be found");
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
