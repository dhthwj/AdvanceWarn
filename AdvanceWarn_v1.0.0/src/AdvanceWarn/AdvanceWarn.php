<?php


namespace AdvanceWarn;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

use pocketmine\utils\Internet;
use pocketmine\Player;

use AdvanceWarn\command\{
	AddWarnCommand,
	AddPoliceCommand,
	ReduceWarnCommand,
	DeletePoliceCommand
};

class AdvanceWarn extends PluginBase
{
	
	private static $instance = null;
	
	public static $prefix = "§l§6[알림]§r§7 ";
	
	protected $config;
	
	protected $db;
	
	
	
	public static function runFunction (): AdvanceWarn
	{
		return self::$instance;
	}
	
	public function onLoad (): void
	{
		if (self::$instance === null) {
			self::$instance = $this;
		}
		if (!file_exists ($this->getDataFolder ())) {
			@mkdir ($this->getDataFolder ());
		}
		$this->config = new Config ($this->getDataFolder () . "config.yml", Config::YAML, [
			"warn-count" => 5,
			"police" => [],
			"player" => []
		]);
		$this->db = $this->config->getAll ();
	}
	
	public function onEnable (): void
	{
		$this->getServer ()->getCommandMap ()->registerAll ("avas", [
			new AddWarnCommand ($this),
			new ReduceWarnCommand ($this),
			new AddPoliceCommand ($this),
			new DeletePoliceCommand ($this)
		]);
	}
	
	public function onDisable (): void
	{
		$this->config->setAll ($this->db);
		$this->config->save ();
	}
	
	public function addWarn (string $name, string $reason, int $count, string $manager): void
	{
		if (!isset ($this->db ["player"] [$name])) {
			$this->db ["player"] [$name] = [
				"count" => 0,
				"reason" => []
			];
		}
		$this->db ["player"] [$name] ["count"] += $count;
		$this->db ["player"] [$name] ["reason"] [date ("Y년 m월 d일 h시 i분 s초")] = [
			"reason" => $reason,
			"manager" => $manager,
			"type" => "add"
		];
		$text = "[경고 처리기록] #경고처리";
		$text .= "\n\n{$name} 님에게 경고 {$count}가 부여되었습니다.";
		$text .= "\n총 경고수: " . $this->db ["player"] [$name] ["count"] . "";
		$text .= "\n경고 사유: " . $reason . "";
		
		$text .= "처리자: {$manager}\n";
		$this->getServer ()->broadcastMessage (self::$prefix . "§a{$name}§7 님께서 §a' {$reason} '§7 라는 이유로 경고 §a{$count}회§7를 부여받았습니다. [처리자: §a{$manager}§7]");
		if (!isset ($this->db ["player"] [$name] ["ban"])) {
			if ($this->db ["player"] [$name] ["count"] >= $this->db ["warn-count"]) {
				$this->getServer ()->getNameBans ()->addBan (strtolower ($name));
				$this->db ["player"] [$name] ["ban"] = date ("Y년 m월 d일 h시 i분 s초");
				$text .= "\n경고수 5회가 넘어서 서버 이용이 제한되었습니다.";
				$this->getServer ()->broadcastMessage (self::$prefix . "§a{$name}§7 님께서 경고수 5회가 넘어서 서버 이용이 제한되셨습니다.");
				if ($this->getServer ()->getPlayer ($name) !== null)
					$this->getServer ()->getPlayer ($name)->kick ("§b밴이 되셨습니다! 밴드에 문의하주세요!");
			}
		}
		$text .= "\n처리 일자: " . date ("Y년 m월 d일 h시 i분 s초") . "";
		Internet::postURL ("https://openapi.band.us/v2.2/band/post/create?access_token=ZQAAAWmw8BXAJdoBX-bFTx7BjiYSkqdtk0bix4exbLRBCndazHMYerG7TfN2iKAZLeq42ZMn9noZak8mXReyuwuR5Uxj_eUYYZNygorKaArpNYMp", [
			"band_key" => "AAC1cubkXGtplRcbc0EUjRqW",
			"content" => $text
		]);
	}
	
	public function reduceWarn (string $name, string $reason, int $count, string $manager): void
	{
		if (!isset ($this->db ["player"] [$name])) {
			return;
		}
		$this->db ["player"] [$name] ["count"] -= $count;
		$this->db ["player"] [$name] ["reason"] [date ("Y년 m월 d일 h시 i분 s초")] = [
			"reason" => $reason,
			"manager" => $manager,
			"type" => "reduce"
		];
		$text = "[경고 처리기록] #경고처리";
		$text .= "\n\n{$name} 님의 경고가 {$count} 만큼 차감되었습니다.";
		$text .= "\n총 경고수: " . $this->db ["player"] [$name] ["count"] . "";
		$text .= "\n차감 사유: " . $reason . "";
		
		$text .= "처리자: {$manager}\n";
		$this->getServer ()->broadcastMessage (self::$prefix . "§a{$name}§7 님께서 §a' {$reason} '§7 라는 이유로 경고 §a{$count}회§7를 차감받았습니다. [처리자: §a{$manager}§7]");
		if (isset ($this->db ["player"] [$name] ["ban"])) {
			if ($this->db ["player"] [$name] ["count"] < $this->db ["warn-count"]) {
				$this->getServer ()->getNameBans ()->remove (strtolower ($name));
				$text .= "\n경고수가 낮아서 이용제한이 해제되었습니다";
				$this->getServer ()->broadcastMessage (self::$prefix . "§a{$name}§7 님께서 경고수가 낮아서 이용제한이 풀렸습니다.");
				unset ($this->db ["player"] [$name] ["ban"]);
			}
		}
		$text .= "\n처리 일자: " . date ("Y년 m월 d일 h시 i분 s초") . "";
		Internet::postURL ("https://openapi.band.us/v2.2/band/post/create?access_token=ZQAAAWmw8BXAJdoBX-bFTx7BjiYSkqdtk0bix4exbLRBCndazHMYerG7TfN2iKAZLeq42ZMn9noZak8mXReyuwuR5Uxj_eUYYZNygorKaArpNYMp", [
			"band_key" => "AAC1cubkXGtplRcbc0EUjRqW",
			"content" => $text
		]);
	}
	
	public function isPolice (string $name): bool
	{
		return isset ($this->db ["police"] [$name]);
	}
	
	public function addPolice (string $name): void
	{
		$this->db ["police"] [$name] = true;
		if (($player = $this->getServer ()->getPlayer ($name)) instanceof Player) {
			$player->sendMessage (self::$prefix . "관리자 권한을 부여받으셨습니다.");
			$perm = $player->addAttachment ($this);

			$perm->setPermission ("advance.warn.command", true);
		}
	}
	
	public function deletePolice (string $name): void
	{
		unset ($this->db ["police"] [$name]);
		if (($player = $this->getServer ()->getPlayer ($name)) instanceof Player) {
			$player->kick ("smile");
		}
	}
	
	public static function message ($player, string $msg): void
	{
		$player->sendMessage (self::$prefix . $msg);
	}
}