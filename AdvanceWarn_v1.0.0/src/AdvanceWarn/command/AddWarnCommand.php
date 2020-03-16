<?php


namespace AdvanceWarn\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\Player;

use AdvanceWarn\AdvanceWarn;

class AddWarnCommand extends Command
{
	
	protected $plugin;
	
	public const PERMISSION = "advance.warn.command";
	
	
	public function __construct (AdvanceWarn $plugin)
	{
		$this->plugin = $plugin;
		parent::__construct ("경고 추가", "경고 추가 명령어 입니다.");
		$this->setPermission (self::PERMISSION);
	}
	
	public function execute (CommandSender $player, string $label, array $args): bool
	{
		if ($player->hasPermission (self::PERMISSION)) {
			if (isset ($args [0]) and isset ($args [1]) and isset ($args [2]) and is_numeric ($args [2])) {
				$this->plugin->addWarn ($args [0], $args [1], $args [2], $player->getName ());
			} else {
				AdvanceWarn::message ($player, "/경고 추가 (닉네임) (사유) (경고 수)");
			}
		} else {
			AdvanceWarn::message ($player, "당신은 이 명령어를 사용할 권한이 없습니다.");
		}
		return true;
	}
	
}