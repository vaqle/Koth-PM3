<?php
namespace vale\koth\user\task;

use pocketmine\scheduler\Task;
use vale\koth\user\scoreboard\ScoreboardManager;
use vale\koth\Main;
class KothTask extends Task
{
	public $plugin;

	public function __construct(
		 Main $plugin
	)
	{
		$this->plugin = $plugin;
	}

	public function onRun(int $currentTick): void
	{
		$kmanager = Main::getInstance()->getKuManager();
		foreach (Main::getInstance()->getServer()->getOnlinePlayers() as $player) {
			if ($kmanager->isEnabled()) {
				if (Main::getInstance()->isPositionInside($player->getPosition())
					&& empty($kmanager->getData()) &&
					!$kmanager->isCurrentKothCaputer($player)) {
					Main::getInstance()->getKuManager()->setCapturing($player, []);
				}
				if (
					Main::getInstance()->getKuManager()->
					isCurrentKothCaputer($player)
				) {
					$session = Main::getInstance()->getKuManager()->setCapturing($player);
					$session->update();
				}
			}
		}
	}
}