<?php

namespace vale\koth\user\scoreboard;

use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\Player;
use vale\koth\Main;

class ScoreboardManager {

	/** @var array $scoreboards[] */
	protected static $scoreboards = [];

	/**
	 * @param Player $player
	 * @param String $objectiveName
	 * @param String $displayName
	 * @return void
	 */
	public function newScoreboard(Player $player, String $objectiveName, String $displayName) : void {
		if(isset(self::$scoreboards[$player->getName()])){
			unset(self::$scoreboards[$player->getName()]);
		}
		if(!$player->isOnline()){
			return;
		}
		$pk = new SetDisplayObjectivePacket();
		$pk->displaySlot = "sidebar";
		$pk->objectiveName = $objectiveName;
		$pk->displayName = $displayName;
		$pk->criteriaName = "dummy";
		$pk->sortOrder = 0;
		$player->sendDataPacket($pk);
		self::$scoreboards[$player->getName()] = $objectiveName;
	}

	/**
	 * @param Player $player
	 * @return void
	 */
	public function removePrimary(Player $player) : void {
		if(isset(self::$scoreboards[$player->getName()])){
			$objectiveName = $this->getObjectiveName($player);
			$pk = new RemoveObjectivePacket();
			$pk->objectiveName = $objectiveName;
			$player->sendDataPacket($pk);
			unset(self::$scoreboards[$player->getName()]);
		}
	}

	/**
	 * @param Player $player
	 * @return void
	 */
	public function remove(Player $player, $key) : void {
		if(isset(self::$scoreboards[$player->getName()])){
			$objectiveName = $this->getObjectiveName($player);
			$pk = new RemoveObjectivePacket();
			$pk->objectiveName = $objectiveName;
			$player->sendDataPacket($pk);
			unset(self::$scoreboards[$player->getName()], $key);
		}
	}

	/**
	 * @param Player $player
	 * @param Int $score
	 * @param String $message
	 * @return void
	 */
	public function setLine(Player $player, Int $score, ?String $message) : void {
		if(!isset(self::$scoreboards[$player->getName()])){
			Main::getInstance()->getLogger()->info("Error");
			return;
		}
		if($score > 15){
			Main::getInstance()->getLogger()->info("Error, you exceeded the limit of parameters 1-15");
			return;
		}
		$objectiveName = $this->getObjectiveName($player);
		$entry = new ScorePacketEntry();
		$entry->objectiveName = $objectiveName;
		$entry->type = $entry::TYPE_FAKE_PLAYER;
		$entry->customName = $message;
		$entry->score = $score;
		$entry->scoreboardId = $score;
		$pk = new SetScorePacket();
		$pk->type = $pk::TYPE_CHANGE;
		$pk->entries[] = $entry;
		$player->sendDataPacket($pk);
	}

	/**
	 * @param Player $player
	 * @return String
	 */
	public function getObjectiveName(Player $player) : ?String {
		return isset(self::$scoreboards[$player->getName()]) ? self::$scoreboards[$player->getName()] : null;
	}
}