<?php
namespace vale\koth;
use ErrorException;
use FactionsPro\FactionMain;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use vale\koth\user\KUManager;
use vale\koth\user\task\KothHeartBeatTask;
use vale\koth\user\task\SBTask;

final class Main extends PluginBase implements Listener
{
	/** @var KUManager $kuManager */
	public KUManager $kuManager;

	/** @var Main|null $instance */
	public static ?Main $instance = null;

	public $factionMain;

	public function onEnable()
	{
		self::$instance = $this;
		$this->getScheduler()->scheduleDelayedTask(new KothHeartBeatTask($this), (20 * 60 * $this->getConfig()->get("time-till-new-game")));
		$this->factionMain = Server::getInstance()->getPluginManager()->getPlugin("FactionsPro");
		if(!$this->factionMain instanceof FactionMain){
			$this->getServer()->shutdown();
			throw new ErrorException("Factions Pro is not Loaded Please install it");
		}
		$this->kuManager = new KUManager($this);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	/**
	 * @param PlayerJoinEvent $event
	 */
	public function onJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$this->getScheduler()->scheduleRepeatingTask(new SBTask($player,$this),20);
	}

	/**
	 * @return KUManager|null
	 */
	public function getKuManager(): ?KUManager
	{
		return $this->kuManager;
	}

	/**
	 * @return Main|null
	 */
	public static function getInstance(): ?Main
	{
		return self::$instance;
	}

	/**
	 * @param Position $position
	 * @return bool
	 */
	public function isPositionInside(Position $position): bool
	{
		$level = $position->getLevel();
		$config = Main::getInstance()->getConfig();
		if ($level->getName() !== $config->get("Level")) return false;
		$data1 = $this->getConfig()->get("firstposition");
		$fp = explode(":", $data1);
		$data2 = $this->getConfig()->get("secondposition");
		$sp = explode(":", $data2);
		$firstPosition = new Position($fp[0], $fp[1], $fp[2], Server::getInstance()->getLevelByName($this->getConfig()->get("Level")));
		$secondPosition = new Position($sp[0], $sp[1], $sp[2], Server::getInstance()->getLevelByName($this->getConfig()->get("Level")));
		$minX = min($firstPosition->getX(), $secondPosition->getX());
		$maxX = max($firstPosition->getX(), $secondPosition->getX());
		$minZ = min($firstPosition->getZ(), $secondPosition->getZ());
		$maxZ = max($firstPosition->getZ(), $secondPosition->getZ());
		return $minX <= $position->getX() and $maxX >= $position->getFloorX() and
			$minZ <= $position->getZ() and $maxZ >= $position->getFloorZ() and
			$firstPosition->getLevel()->getName() === Server::getInstance()->getLevelByName($this->getConfig()->get("Level"))->getName();
	}

 	public function getPlayerFaction(Player $player): string{
		$factionsPro = $this->factionMain;
		$factionName = $factionsPro->getPlayerFaction($player->getName());

		if($factionName === null){
			return "No Faction";
		}

		return $factionName;
	}

	/**
	 * @param int $int
	 * @return string
	 */
    public static function intToString(int $int): string
    {
        $m = floor($int / 60);
        $s = floor($int % 60);
        return (($m < 10 ? "0" : "") . $m . ":" . ($s < 10 ? "0" : "") . $s);
    }
}