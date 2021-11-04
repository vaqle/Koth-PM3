<?php
namespace vale\koth\user;
use pocketmine\block\Sand;
use pocketmine\Player;
use pocketmine\Server;
use vale\koth\Main;
use vale\koth\user\task\KothHeartBeatTask;
use vale\koth\user\task\KothTask;

class KUManager
{
    /** @var array $kothUser*/
    public static array $kothUser = [];

	/** @var bool $enabled */
	public static $enabled = false;

	/** @var string */
	public const PREFIX = "§r§d§l[§6§lK§7§l.§6§lO§7.§e§lT§7.§a§lH§d]";

     /** @var Main|null $plugin */
    public Main $plugin;

    /**
     * @param Main $plugin
     */
    public function __construct(Main $plugin){
        $this->plugin = $plugin;
		$this->plugin->getScheduler()->scheduleRepeatingTask(new KothTask($this->plugin),20);
    }

	/**
	 * @return bool
	 */
	public function isEnabled(): bool{
		return self::$enabled;
	}

	public static function setEnabled(bool $value){
		self::$enabled = $value;
	}

    /**
     * @return Player|null
     */
    public function getCurrentKothCaptuerer(): ?Player
    {
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if (!$player->isOnline()) {
                return null;
            }
            if ($player->isOnline()) {
                if ($this->plugin->getKuManager()->isCurrentKothCaputer($player)) {
                    return $player;
                }
            }
        }
        return null;
    }

    /**
     * @return KothUser|null
     */
    public function getCurrentKothUserSession(): ?KothUser
    {
		foreach (Server::getInstance()->getOnlinePlayers() as $player) {
			if(!$player->isOnline()){
				return null;
			}
			if ($this->plugin->getKuManager()->isCurrentKothCaputer($player)) {
				return self::$kothUser[$player->getName()];
			}
		}
		return new KothUser(null, []);
	}
	/**
	 * @param Player|null $player
	 * @param array|null $rewards
	 * @return mixed|KothUser
	 */
    public function setCapturing(Player $player = null, array $rewards = null)
    {
        if (!isset(self::$kothUser[$player->getName()])) {
			Server::getInstance()->broadcastMessage(self::PREFIX . " §r§e{$player->getName()} is now capturing.");
            self::$kothUser[$player->getName()] = new KothUser($player, []);
        }
        return self::$kothUser[$player->getName()];
    }

    public function removeCapturing(Player $player): void
    {
        if (!isset(self::$kothUser[$player->getName()])) {
            return;
        }
		Server::getInstance()->broadcastMessage(self::PREFIX . " §r§e{$player->getName()} is no longer capturing.");
        unset(self::$kothUser[$player->getName()]);
    }

    public function getData(): array
    {
        return self::$kothUser;
    }

    public function isCurrentKothCaputer(Player $player): bool
    {
        return isset(self::$kothUser[$player->getName()]);
    }
}