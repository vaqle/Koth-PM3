<?php
namespace vale\koth\user\task;
use pocketmine\block\Sand;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use vale\koth\Main;
use vale\koth\user\KUManager;

class KothHeartBeatTask extends Task
{
	public $plugin;
	public $task = null;
	public int $time = 15;

	/**
	 * @param Main $plugin
	 */
	public function __construct(Main $plugin)
	{
		$this->plugin = $plugin;
	}

	public function onRun(int $currentTick): void
	{
		$task = new ClosureTask(function (): void {
			--$this->time;
			Server::getInstance()->broadcastMessage(KUManager::PREFIX . " §r§eA Koth Game will start in " . $this->time);
			if ($this->time === 0) {
				KUManager::setEnabled(true);
				Server::getInstance()->broadcastMessage("Koth has started");
				$this->plugin->getScheduler()->scheduleDelayedTask(new KothHeartBeatTask($this->plugin), (20 * 60 * $this->plugin->getConfig()->get("time-till-new-game")));
				Main::getInstance()->getScheduler()->cancelTask($this->task->getTaskId());
			}
		});
		$this->plugin->getScheduler()->scheduleRepeatingTask($task, 20);
		$this->task = $task;
	}
}
