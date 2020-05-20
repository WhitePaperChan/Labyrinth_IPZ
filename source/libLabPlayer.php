<?php
/**
* Player and Turn
*/

/** 
* Player class
* @property int $x x position.
* @property int $y y position.
* @property int $tnt number of tnt.
* @property int $cement number of cement.
* @property int $bullet number of bullets.
* @property int $forfeit forfeit turns.
* @property str $name name of player.
* @property array $treasures real and fake treasures.
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
	* creates player
	* @param str $name
	*/
    public function __construct($name) {
        $this->name = $name;
    }
    /**
	* gives player an arsenal
	*/
	public function getArsenal(){
		$this->tnt += 3;
		$this->cement += 2;
		$this->bullet += 1;
	}
	/**
	* gives player a treasure
	* @param int $id
	*/
	public function getTreasure($id){
		$this->treasures[] = $id;
	}
	/**
	* gives player a forfeit
	*/
	public function incForfeit(){
		$this->forfeit++;
	}
	/**
	* moves player
	* @param int $x
	* @param int $y
	*/
	public function moveTo($x, $y){
		$this->x = $x;
		$this->y = $y;
	}
	/**
	* show inventory
	*/
	public function inventoryPrint(){
		echo "Player inventory: TNT=".$this->tnt
			." Cement=".$this->cement
			." Bullets=".$this->bullet
			." Treasures: ".sizeof($this->treasures)
			."<br />";
	}
	/**
	* show a position
	*/
	public function positionPrint(){
		echo "Player position: X=".(($this->x+1)/2)." Y=".(($this->y+1)/2)."<br><br>\n";
	}
	/* API */
	/**
	* inventory json
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
* @property int $no number of turn.
* @property int $noSubturn number of subturns.
* @property boolean $finished is finished.
* @property str $playerName name of the player.
* @property array $subturns list of subturns.
*/
class Turn {
	public $no = 0;
	public $noSubturn = 0;
	public $finished = false;
	public $playerName = "";
	public $subturns = array(); /* "noSubturn", "reaction", "answer" */
	/**
	* creates a string
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
	* starting
	* @param str $name
	* @param int $turnNo
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
	* creates array for turn
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
