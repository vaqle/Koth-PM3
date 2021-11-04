<?php
namespace vale\koth\user;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;
use vale\koth\Main;

class KothUser
{
	/** @var Player $player */
	public ?Player $player;

	/** @var array $reward */
	public array $rewards = [];

	public int $time = 20;

	/**
	 * @param Player|null $player
	 * @param array $rewards
	 */
	public function __construct(?Player  $player, array $rewards)
	{
		$this->player = $player;
		$this->rewards = [];
	}

	/**
	 * @return array
	 */
	public function getRewards(): array
	{
		return [];
	}

	/**
	 * @return Player|null
	 */
	public function getPlayer(): ?Player
	{
		return Server::getInstance()->getPlayerExact($this->player->getName()) ?? null;
	}

	public function getUserName(): ?string{
		if($this->player === null) {
			return "No King";
		}
		return $this->player->getName();
	}

	public function getFaction(): string{
		if($this->player === null){
			return "X";
		}
		return Main::getInstance()->getPlayerFaction($this->player);
	}

	public function getElapsedTime(): int{
		return $this->time ?? 500;
	}

	public function update(): void
	{
		if ($this->player === null) {
			return;
		}
		if (!$this->getPlayer()->isOnline()) return;

		if (!Main::getInstance()->isPositionInside($this->player->getPosition())) {
			Main::getInstance()->getKuManager()->removeCapturing($this->getPlayer());
		}
		if (Main::getInstance()->isPositionInside($this->player->getPosition())) {
			--$this->time;
		}
		if ($this->time <= 0) {
			Server::getInstance()->broadcastMessage(KUManager::PREFIX . " §r§e{$this->getUserName()} is the " . KUManager::PREFIX . " §r§ewinner and has won a ". KUManager::PREFIX . " §r§dLootbag §r§aKill them before they escape");
			$rewards = Main::getInstance()->getConfig()->get("rewards");
			foreach ($rewards as $command) {
				$this->player->getServer()->dispatchCommand(new ConsoleCommandSender(), str_replace("{player}", $this->player->getName(), $command));
			}
			Main::getInstance()->getKuManager()->removeCapturing($this->getPlayer());
			KUManager::setEnabled(false);
		}
	}
}