<?php


namespace AdvanceWarn\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\Player;

use AdvanceWarn\AdvanceWarn;

class DeletePoliceCommand extends Command
{
	
	protected $plugin;
	
	public const PERMISSION = "op";
	
	
	public function __construct (AdvanceWarn $plugin)
	{
		$this->plugin = $plugin;
		parent::__construct ("관리자 제거", "관리자 제거 명령어 입니다.");
		$this->setPermission (self::PERMISSION);
	}
	
	public function execute (CommandSender $player, string $label, array $args): bool
	{
		if ($player->hasPermission (self::PERMISSION)) {
			if (isset ($args [0])) {
				if ($this->plugin->isPolice ($args [0])) {
					$this->plugin->deletePolice ($args [0]);
					AdvanceWarn::message ($player, "§a{$args [0]}§7 님을 관리자에서 제거했습니다.");
				} else {
					AdvanceWarn::message ($player, "이미 존재하지 않는 관리자 입니다.");
				}
			} else {
				AdvanceWarn::message ($player, "/관리자 제거 (닉네임)");
			}
		} else {
			AdvanceWarn::message ($player, "당신은 이 명령어를 사용할 권한이 없습니다.");
		}
		return true;
	}
	
}