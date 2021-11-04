<?php
namespace vale\koth\user\task;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use vale\koth\Main;
use vale\koth\user\scoreboard\ScoreboardManager;

class SBTask extends Task{

	public ?Player $player = null;
	public ?Main $plugin;

	public const TITLE = "&r&7&lAncient&9&lMC &r&e&lSeason I";

	public function __construct(Player $player, Main $plugin){
		$this->player = $player;
		$this->plugin = $plugin;
	}

	public function getScoreboards(): ScoreboardManager{
		return new ScoreboardManager();
	}

	public function translate(string $string): string{
		return str_replace("&", TextFormat::ESCAPE, $string);
	}

	public function onRun(int $currentTick): void
	{

		if(!$this->player->isOnline()){
			Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
			return;
		}
		$player = $this->player;
		if($this->player->isOnline()) {
			if ($player->getLevel()->getName() === Main::getInstance()->getConfig()->get("Level")) {
				$kothM = Main::getInstance()->getKuManager();
				$session = $kothM->getCurrentKothUserSession();
				$name = $session->getUserName();
				$time = Main::intToString($session->getElapsedTime());
				$api = $this->getScoreboards();
				$date = date('d-m-y');
				$faction = $session->getFaction();
				$scoreboard = [
					" ",
					" ",
					"&r&c&lK&r&7.&6&lO&r&7.&r&e&lT&r&7.&a&lH &r&7&lInfo &r&7$date",
					" ",
					"&r&7&lFACTION&r&8:",
					"&r&c{$faction}",
					"&r&7&lCAPPING&r&8:",
					"&r&c$name",
					"&r&7&lTIMER&r&8:",
					"&r&c$time",
					"   ",
					"&r&c&lGeneral Info",
					"&r&7TPS:&r&6 " . $this->plugin->getServer()->getTicksPerSecond(),
					"&r&7Online:&r&6 " . count($this->plugin->getServer()->getOnlinePlayers()) . "&r&7/&6" . $this->plugin->getServer()->getMaxPlayers(),
					"&r&7Ping: &r&6{$player->getPing()}",
				];
				$api->newScoreboard($player, $player->getName(),
					$this->translate(self::TITLE));
				if (
					$api->getObjectiveName($player) !== null) {
					foreach (
						$scoreboard as $line => $key
					) {
						$api->remove($player, $scoreboard);
						$api->newScoreboard($player, $player->getName(),
							$this->translate(self::TITLE));
					}
				}
				foreach ($scoreboard as $line => $key) {
					$api->setLine($player, $line + 1, $this->translate($key));
				}
			}
		}
	}
}
