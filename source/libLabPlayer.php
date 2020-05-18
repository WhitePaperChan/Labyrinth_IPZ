<?php
class LabPlayer {
	public $x = 0;
	public $y = 0;
	public $tnt = 3;
	public $cement = 2;
	public $bullet = 1;
	public $forfeit = 0;
	public $name = "Player";
	public $treasures = array();
	
    public function __construct($name) {
        $this->name = $name;
    }
    
	public function getArsenal(){
		$this->tnt += 3;
		$this->cement += 2;
		$this->bullet += 1;
	}
	public function getTreasure($id){
		$this->treasures[] = $id;
	}
	public function incForfeit(){
		$this->forfeit++;
	}
	public function moveTo($x, $y){
		$this->x = $x;
		$this->y = $y;
	}
	public function inventoryPrint(){
		echo "Player inventory: TNT=".$this->tnt
			." Cement=".$this->cement
			." Bullets=".$this->bullet
			." Treasures: ".sizeof($this->treasures)
			."<br />";
	}
	public function positionPrint(){
		echo "Player position: X=".(($this->x+1)/2)." Y=".(($this->y+1)/2)."<br><br>\n";
	}
	/* API */
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

class Turn {
	public $no = 0;
	public $noSubturn = 0;
	public $finished = false;
	public $playerName = "";
	public $subturns = array(); /* "noSubturn", "reaction", "answer" */

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
	public function toArray(){
		//$str = '';
		//$str .= $this->no.". ".$this->playerName."<br>\n";
		$turn = array("turn_no" => $this->no, "player_name" => $this->playerName);
		$subturns = array();
		if (count($this->subturns) > 0){
			foreach ($this->subturns as $value) {
				//$str .=  $this->no.".".$value["noSubturn"].". ".$value["reaction"]."<br>\n";
				//$str .=  $value["answer"]."<br>\n";
				$subturns[] = array("subturn_no" => $value["noSubturn"], "reaction" => $value["reaction"], "answer" => $value["answer"]);
			}
		}
		//$str .=  "<br>\n";
		$answer = array("turn" => $turn, "subturns" => $subturns);
		return $answer;
	}
}
