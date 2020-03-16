<?php


namespace AdvanceWarn\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\Player;

use AdvanceWarn\AdvanceWarn;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

class AddPoliceCommand extends Command implements Listener
{
	
	protected $plugin;
	
	public const PERMISSION = "op";
	
	
	public function __construct (AdvanceWarn $plugin)
	{
		$this->plugin = $plugin;
		parent::__construct ("관리자 추가", "관리자 추가 명령어 입니다.");
		$this->setPermission (self::PERMISSION);
		$this->plugin->getServer ()->getPluginManager ()->registerEvents ($this, $plugin);
	}
	
	public function execute (CommandSender $player, string $label, array $args): bool
	{
		if ($player->hasPermission (self::PERMISSION)) {
			if (isset ($args [0])) {
				if (!$this->plugin->isPolice ($args [0])) {
					$this->plugin->addPolice ($args [0]);
					AdvanceWarn::message ($player, "§a{$args [0]}§7 님을 관리자로 추가하셨습니다.");
				} else {
					AdvanceWarn::message ($player, "이미 존재하는 관리자 입니다.");
				}
			} else {
				AdvanceWarn::message ($player, "/관리자 추가 (닉네임)");
			}
		} else {
			AdvanceWarn::message ($player, "당신은 이 명령어를 사용할 권한이 없습니다.");
		}
		return true;
	}
	
	public function onJoin (PlayerJoinEvent $event): void
	{
		$player = $event->getPlayer ();
		$name = strtolower ($player->getName ());
		if ($this->plugin->isPolice ($name)) {
			$player->sendMessage (AdvanceWarn::$prefix . "관리자 권한을 부여받으셨습니다.");
			$perm = $player->addAttachment ($this);
			$perm->setPermission ("advance.warn.command", true);
		}
	}
}