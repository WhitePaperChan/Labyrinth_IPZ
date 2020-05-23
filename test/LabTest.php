<?php

require("libLabth.php");
require("libLabPlayer.php");

require_once("vendor/phpunit/php-code-coverage/tests/TestCase.php");
class LabTest extends \PHPUnit\Framework\TestCase
{
	protected $lab;
	protected $player;
	protected $labMySQL; 
	protected function setUp(): void{
		$this->lab = new Labth();
		$this->player = new LabPlayer("NyanCat");
	}

	public function testIsRiverMinLessMax(){
		$lab = $this->lab;
		$min = $lab->minRiverLength;
		$max = $lab->maxRiverLength;
		
		$this->assertTrue($min < $max);
	}

	public function testIsRiverMaxGreaterZero(){
		$this->assertTrue($this->lab->maxRiverLength > 0);
	}
	
	public function testIsMapNotShown(){
		$this->assertFalse($this->lab->showMap);
	}
	
	public function testIsZeroHoles(){
		$this->assertSame(0, sizeof($this->lab->holes));
	}

	public function testIsZeroBooms(){
		$this->assertSame(0, sizeof($this->lab->booms));
	}
	
	public function testHasPlayerName(){
		$this->assertTrue(strlen($this->player->name) > 0);
	}
	
	public function testHasPlayer3Tnt(){
		$this->assertSame($this->player->tnt, 3);
	}
	
	public function testHasPlayer2Cement(){
		$this->assertSame($this->player->cement, 2);
	}
	
	public function testHasPlayer1Bullet(){
		$this->assertSame($this->player->bullet, 1);
	}
	
	public function testHasPlayerNoTreasure(){
		$this->assertSame(sizeof($this->player->treasures), 0);
	}
	
	public function testHasPlayerNoForfeit(){
		$this->assertSame($this->player->forfeit, 0);
	}
	
	public function testIsArsenalGives3Tnt(){
		$player = $this->player;
		$tnt = $player->tnt;
		$player->getArsenal();
		
		$this->assertSame($player->tnt, $tnt + 3);
	}
	
	public function testIsArsenalGives3Cement(){
		$player = $this->player;
		$cement = $player->cement;
		$player->getArsenal();
		
		$this->assertSame($player->cement, $cement + 2);
	}
	
	public function testIsArsenalGives1Bullet(){
		$player = $this->player;
		$bullet = $player->bullet;
		$player->getArsenal();
		
		$this->assertSame($player->bullet, $bullet + 1);
	}
	
	public function testDoesPlayerGetForfeit(){
		$player = $this->player;		
		$forfeit = $player->forfeit;
		$player->incForfeit();
		$this->assertSame($player->forfeit, $forfeit + 1);
	}
	
	public function testDoesMovingWork(){
		$player = $this->player;
		$x = 4;
		$y = 2;
		$player->moveTo($x, $y);
		$this->assertSame(array($player->x, $player->y), array($x, $y));
	}
	
	public function testDoesInventoryContainTnt(){
		$player = $this->player;
		$inventory = $player->inventoryToJSON();
		$this->assertArrayHasKey('tnt', $inventory);
	}
	
	public function testDoesInventoryContainCement(){
		$player = $this->player;
		$inventory = $player->inventoryToJSON();
		$this->assertArrayHasKey('cement', $inventory);
	}
	
	public function testDoesInventoryContainBullet(){
		$player = $this->player;
		$inventory = $player->inventoryToJSON();
		$this->assertArrayHasKey('bullet', $inventory);
	}
	
	public function testDoesInventoryContainTreasuares(){
		$player = $this->player;
		$inventory = $player->inventoryToJSON();
		$this->assertArrayHasKey('treasures', $inventory);
	}
}