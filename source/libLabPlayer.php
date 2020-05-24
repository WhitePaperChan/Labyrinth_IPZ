<?php
/**
* This file contains classes of players and turns. 
*/

/** 
* Player class
* @property int $x x coordinate of the player
* @property int $y y coordinate of the player
* @property int $tnt number of player's tnt
* @property int $cement number of player's cement
* @property int $bullet number of player's bullets
* @property int $forfeit forfeit turns
* @property string $name the name of the player
* @property array $treasures real and fake player's treasures
*/
class LabPlayer {
	public $x = 0;
	public $y = 0;
	public $tnt = 3;
	public $cement = 2;
	public $bullet = 1;
	public $forfeit = 0;
	public $name = "Player";
	public $treasures = array();
	
	/**
	* Creates the player
	* @param string $name the name of the player
	*/
    public function __construct($name) {
        $this->name = $name;
    }
    /**
	* Gives the player an arsenal
	*/
	public function getArsenal(){
		$this->tnt += 3;
		$this->cement += 2;
		$this->bullet += 1;
	}
	/**
	* Gives the player the treasure
	* @param int $id id of the treasure
	*/
	public function getTreasure($id){
		$this->treasures[] = $id;
	}
	/**
	* Gives player one forfeit turn
	*/
	public function incForfeit(){
		$this->forfeit++;
	}
	/**
	* Moves the player to a specific coordinate
	* @param int $x x coordinate move to
	* @param int $y y coordinate move to
	*/
	public function moveTo($x, $y){
		$this->x = $x;
		$this->y = $y;
	}
	/**
	* Show inventory of the player (tnt, cement, bullets, treasures)
	*/
	public function inventoryPrint(){
		echo "Player inventory: TNT=".$this->tnt
			." Cement=".$this->cement
			." Bullets=".$this->bullet
			." Treasures: ".sizeof($this->treasures)
			."<br />";
	}
	/**
	* shows position of the player
	*/
	public function positionPrint(){
		echo "Player position: X=".(($this->x+1)/2)." Y=".(($this->y+1)/2)."<br><br>\n";
	}
	/* API */
	/**
	* converts inventory to JSON
	* @return array object of inventory
	*/
	public function inventoryToJSON(){
		$inventory = array(
			"tnt" => $this->tnt
			, "cement" => $this->cement
			, "bullet" => $this->bullet
			, "treasures" => sizeof($this->treasures)
		);
		return $inventory;
	}
}

/**
* Turn class
* @property int $no number of the turn.
* @property int $noSubturn number of subturns.
* @property boolean $finished is turn finished?
* @property string $playerName the name of the player.
* @property array $subturns list of subturns.
*/
class Turn {
	public $no = 0;
	public $noSubturn = 0;
	public $finished = false;
	public $playerName = "";
	public $subturns = array(); /* "noSubturn", "reaction", "answer" */
	/**
	* Converts turn object to string
	* @return string string with player turn
	*/
	public function toStr(){
		$str = '';
		$str .= $this->no.". ".$this->playerName."<br>\n";
		if (count($this->subturns) > 0){
			foreach ($this->subturns as $value) {
				$str .=  $this->no.".".$value["noSubturn"].". ".$value["reaction"]."<br>\n";
				$str .=  $value["answer"]."<br>\n";
			}
		}
		$str .=  "<br>\n";
		return $str;
	}
	/**
	* Starting the game for the player
	* @param string $name the name of the player
	* @param int $turnNo number of the turn
	*/
	public function landing($name, $turnNo){
		$this->no = $turnNo;
		$this->finished = true;
		$this->playerName = $name;
		array_push($this->subturns, 
			array("noSubturn" => 0, // 0 only for landing
				"reaction" => " start: Land",
				"answer" => "..."
			)
		);
	}
	/* API */
	/**
	* Converts turn object to array
	* @return array array with player turn
	*/
	public function toArray(){
		$turn = array("turn_no" => $this->no, "player_name" => $this->playerName);
		$subturns = array();
		if (count($this->subturns) > 0){
			foreach ($this->subturns as $value) {
				$subturns[] = array("subturn_no" => $value["noSubturn"], "reaction" => $value["reaction"], "answer" => $value["answer"]);
			}
		}
		$answer = array("turn" => $turn, "subturns" => $subturns);
		return $answer;
	}
}
