<?php
/**
 * This file contains class of labyrinth. 
 */

/**
 * Labth (labyrinth) class.
 * Generation of labyrinth. 
 * Reaction of labyrinth on player's actions
 * @property int $Xmax width of labyrinth
 * @property int $Ymax height of labyrinth
 * @property int $minRiverLengthIndex divider for minimum length of river
 * @property int $maxRiverLengthIndex divider for maximum length of river
 * @property int $minRiverLength minimum length of river
 * @property int $maxRiverLength maximum length of river
 * @property boolean $allThingsTogether do holes and booms can be on one spot? (booms are still separate from each other)
 * @property boolean $showMap are the map and player coordinates shown on the page?
 * @property array $map all cells of labyrinth
 * @property array $holes all holes of labyrinth
 * @property array $booms all boomes of labyrinth (including treasures, arsenal and hospital)
*/
class Labth {
	public $Xmax = 5;
	public $Ymax = 5;
	public $minRiverLengthIndex = 5;
	public $maxRiverLengthIndex = 3;
	public $minRiverLength;
	public $maxRiverLength;
	public $allThingsTogether = false;
	public $showMap = false;
	public $map = array();
	var $holes = array(); // var due tu problem with protected during oject serilizing
	var $booms = array(); // var due tu problem with protected during oject serilizing
/**
 * Creates labyrinth (but don't generate it)
 * @param int $Xmax width of labyrinth
 * @param int $Ymax height of labyrinth
 * @param int $minRiverLengthIndex divider for minimum length of river
 * @param int $maxRiverLengthIndex divider for maximum length of river
 * @param int $allThingsTogether do holes and booms can be on one spot? (booms are still separate from each other)
 * @param int $showMap are the map and player coordinates shown on the page?
 */
    public function __construct(
    					$Xmax = 5, 
    					$Ymax = 5,
    					$minRiverLengthIndex = 5,
    					$maxRiverLengthIndex = 3,
    					$allThingsTogether = false,
    					$showMap = false
    		) {
		$this->Xmax = $Xmax;
		$this->Ymax = $Ymax;
		$this->minRiverLengthIndex = $minRiverLengthIndex;
		$this->maxRiverLengthIndex = $maxRiverLengthIndex;
		$this->allThingsTogether = $allThingsTogether;
		$this->showMap = $showMap;
		$this->minRiverLength = intdiv($this->Xmax * $this->Ymax, $this->minRiverLengthIndex); 
		$this->maxRiverLength = intdiv($this->Xmax * $this->Ymax, $this->maxRiverLengthIndex); 
		$holes = array_fill(1, 4, array('x' => 0, 'y' => 0)); // 2do const 4
		$booms = array_fill(1, 4, array('x' => 0, 'y' => 0)); // 2do const 4
    }
    /**
	* Returns all cells without holes/booms
	* @param string $cellType type of cell to ignore ("hole" or "boom")
	* @return array all cells without holes/booms
	*/
	function noCellTypeFill($cellType) {
		$land = array();
		for ($i=1; $i<=$this->Xmax; $i++){
			for ($j=1; $j<=$this->Ymax; $j++){
				$x = $i*2-1;
				$y = $j*2-1;
				if ($this->map[$y][$x][$cellType] == 0){
					array_push($land, array("x" => $x, "y" => $y));
				}
			}
		}
		return $land;
	}
	/**
	* Returns all cells without EITHER holes OR booms
	* @return array all cells without EITHER holes OR booms
	*/
	function landFill() {
		$land = array();
		for ($i=1; $i<=$this->Xmax; $i++){
			for ($j=1; $j<=$this->Ymax; $j++){
				$x = $i*2-1;
				$y = $j*2-1;
				if ($this->map[$y][$x] == array("hole" => 0, "river" => 0, "bum" => 0)){
					array_push($land, array("x" => $x, "y" => $y));
				}
			}
		}
		return $land;
	}
	/**
	* Pushes random cell from one array to another array
	* @param array $land array of cells to choose from
	* @param array $landSelected array to push random cell
	* @return boolean is getting a random cell successful
	*/
	function randomCellGet(&$land, &$landSelected) {
		if (count($land) > 0){
			$landNo = random_int(0, count($land)-1);
			$landSelected = $land[$landNo];
			array_splice($land, $landNo, 1);
			return true;
		} else {
			return false;
		}
	}
	/**
	* Prepares a wall line to push in map
	* @return array array of cells in wall line
	*/
	function wallLineGenerate() {
		$wallLine = array(0);
		for ($i = 1; $i < $this->Xmax*2+1; $i += 2) {
			array_push($wallLine, 0, 0);
		}
		return $wallLine;
	}
	/**
	* Prepares a walkable line to push in map
	* @return array array of cells in walkable line
	*/
	function cellLineGenerate() {
		$cellLine = array(0);
		for ($i = 1; $i < $this->Xmax*2+1; $i += 2) {
			array_push($cellLine, array("hole" => 0, "river" => 0, "bum" => 0), 0);
		}
		return $cellLine;
	}
	/**
	* Pushes in map wall lines and walkable lines
	*/
	function lab0Generate() {
		array_push($this->map, $this->wallLineGenerate());
		for ($i = 1; $i < $this->Ymax*2+1; $i += 2) {
			array_push($this->map, $this->cellLineGenerate());
			array_push($this->map, $this->wallLineGenerate());
		}
	}
	/**
	* Makes all external walls very-very hard to break 
	*/
	function markExternalWalls() {
		for ($i = 0; $i < $this->Xmax*2+1; $i++) {
		 	$this->map[0][$i] = 999;
		}
		for ($i = 1; $i < $this->Ymax*2+1; $i += 2) {
		 	$this->map[$i][0] = 999; 
		 	$this->map[$i][$this->Xmax*2] = 999;
			for ($j = 0; $j < $this->Xmax*2+1; $j += 2) {
			 	$this->map[$i+1][$j] = 999;
			}
		}
		for ($i = 0; $i < $this->Xmax*2+1; $i++) {
			$y = $this->Ymax*2;
		 	$this->map[$y][$i] = 999;
		}
	}
	/**
	* Generates exit in random place
	*/
	function labExitGenerate() {
		$side = random_int(1, 4);
		if ($side == 1) {
			$this->map[0][random_int(1, $this->Xmax)*2-1] = 0;
		} elseif ($side == 2) {
			$this->map[$this->Ymax*2][random_int(1, $this->Xmax)*2-1] = 0;
		} elseif ($side == 3) {
			$this->map[random_int(1, $this->Ymax)*2-1][0] = 0;
		} elseif ($side == 4) {
			$this->map[random_int(1, $this->Ymax)*2-1][$this->Xmax*2] = 0;
		}
	}
	/**
	* Generates holes or booms if it's possible
	* @param str $cellType type of cell ("hole" or "boom")
	*/
	function labHoleBumGenerate($cellType) { /* cellType = "hole" or "bum" */
		if ($this->allThingsTogether){
			$land = $this->noCellTypeFill($cellType);
		} else {
			$land = $this->landFill();
		}
		for ($i = 1; $i <=4; $i++){ // 2do // constant 4 - no of hols in a cycle or bum objects
			$landSelected = array();
			if ($this->randomCellGet($land, $landSelected)){
				$this->map[$landSelected["y"]][$landSelected["x"]][$cellType] = $i;
				if ($cellType == "hole"){
					//array_push($this->holes, "".$i => array("x" => $landSelected["x"], "y" => $landSelected["y"]));
					$this->holes[$i] = array("x" => $landSelected["x"], "y" => $landSelected["y"]);
				} else { // "bum"
					//array_push($this->booms, array("x" => $landSelected["x"], "y" => $landSelected["y"]));
					$this->booms[$i] = array("x" => $landSelected["x"], "y" => $landSelected["y"]);
				}
			} else {
				echo "No Cell to place object ".$cellType."<br />"; // 2do 2 error log
			}
		}
	}
	/**
	* Creates a river source and writes it's coordinates in given variables
	* @param int $sourceX variable to write in x coordinate
	* @param int $sourceY variable to write in y coordinate
	*/
	function riverSource(&$sourceX, &$sourceY) {
		$side = random_int(1, 4);
		if ($side == 1) {
			$sourceY = 1; $sourceX = random_int(1, $this->Xmax);
		} elseif ($side == 2) {
			$sourceY = $this->Ymax; $sourceX = random_int(1, $this->Xmax);
		} elseif ($side == 3) {
			$sourceY = random_int(1, $this->Ymax); $sourceX = 1;
		} elseif ($side == 4) {
			$sourceY = random_int(1, $this->Ymax); $sourceX = $this->Xmax;
		}
	}
	/**
	* Finds avaliable river directions
	* @param int $sourceX x coordinate of river source
	* @param int $sourceY y coordinate of river source
	* @return array array of avaliable directions
	*/
	function findAvailableRiverDirections($sourceX, $sourceY) {
				$availableDirections = array();
				if ($sourceX > 1 && $this->map[$sourceY*2-1][$sourceX*2-3]["river"] == 0) {
					array_push($availableDirections, 1);
				} elseif ($sourceY > 1 && $this->map[$sourceY*2-3][$sourceX*2-1]["river"] == 0) {
					array_push($availableDirections, 2);
				} elseif ($sourceX < $this->Xmax && $this->map[$sourceY*2-1][$sourceX*2+1]["river"] == 0) {
					array_push($availableDirections, 3);
				} elseif ($sourceY == $this->Ymax && $this->map[$sourceY*2+1][$sourceX*2-1]["river"] == 0) {
					array_push($availableDirections, 4);
				}
				return $availableDirections;
	}
	/**
	* Generates river direction or creates a lake
	* @param int $sourceX x coordinate of river source
	* @param int $sourceY y coordinate of river source
	*/
	function riverDirectionGenerate($sourceX, $sourceY) {
				$availableDirections = $this->findAvailableRiverDirections($sourceX, $sourceY);
				$numberOfAvailableDirection = count($availableDirections);
				if ($numberOfAvailableDirection == 0){
					// Lake
					$this->map[$sourceY*2-1][$sourceX*2-1]["river"] = 5; //2do 5 is a constant for Lake
				} else {
					$this->map[$sourceY*2-1][$sourceX*2-1]["river"] = 
						$availableDirections[random_int(1, $numberOfAvailableDirection)];
				}
	}
	/**
	* Generates river
	*/
	function labRiverGenerate() {
		$sourceY = 1; $sourceX = 1;
		$this->riverSource($sourceX, $sourceY);
		$direction = random_int(1, 4);
		$riverCellNumber = 1; // river lenth counter
		// if river is not so long not direct it to out
		for ($inf = 1; $inf <= 99999; $inf++) {  // 2do may be better way // while (true) is a not good idea for web server
			if ($riverCellNumber >= $this->maxRiverLength){
				$this->map[$sourceY*2-1][$sourceX*2-1]["river"] = 5; //Lake // 2do const Lake
				break;
			}
			if ($direction == 1 && $sourceX == 1) {
				if ($riverCellNumber >= $this->minRiverLength){
					$this->map[$sourceY*2-1][$sourceX*2-1]["river"] = $direction;
					break;
				} else {
					$direction = random_int(1, 4);
					continue;
				}
			} elseif ($direction == 2 && $sourceY == 1) {
				if ($riverCellNumber >= $this->minRiverLength){
					$this->map[$sourceY*2-1][$sourceX*2-1]["river"] = $direction;
					break;
				} else {
					$direction = random_int(1, 4);
					continue;
				}
			} elseif ($direction == 3 && $sourceX == $this->Xmax) {
				if ($riverCellNumber >= $minRiverLength){
					$this->map[$sourceY*2-1][$sourceX*2-1]["river"] = $direction;
					break;
				} else {
					$direction = random_int(1, 4);
					continue;
				}
			} elseif ($direction == 4 && $sourceY == $this->Ymax) {
				if ($riverCellNumber >= $this->minRiverLength){
					$this->map[$sourceY*2-1][$sourceX*2-1]["river"] = $direction;
					break;
				} else {
					$direction = random_int(1, 4);
					continue;
				}
			}
			if ($direction == 1 && $this->map[$sourceY*2-1][$sourceX*2-3]["river"] >0) {
				$this->riverDirectionGenerate($sourceX, $sourceY);
			} elseif ($direction == 2 && $this->map[$sourceY*2-3][$sourceX*2-1]["river"] >0) {
				$this->riverDirectionGenerate($sourceX, $sourceY);
			} elseif ($direction == 3 && $this->map[$sourceY*2-1][$sourceX*2+1]["river"] >0) {
				$this->riverDirectionGenerate($sourceX, $sourceY);
			} elseif ($direction == 4 && $this->map[$sourceY*2+1][$sourceX*2-1]["river"] >0) {
				$this->riverDirectionGenerate($sourceX, $sourceY);
			} else {
				$this->map[$sourceY*2-1][$sourceX*2-1]["river"] = $direction;
			$riverCellNumber++;
			}
			$direction = $this->map[$sourceY*2-1][$sourceX*2-1]["river"];
			if ($direction == 1) {
				$sourceX--;
			} elseif ($direction == 2) {
				$sourceY--;
			} elseif ($direction == 3) {
				$sourceX++;
			} elseif ($direction == 4) {
				$sourceY++;
			}
			$direction = random_int(1, 4);
		}
		if ($this->map[$sourceY*2-1][$sourceX*2-1]["river"] == 0){
			$this->map[$sourceY*2-1][$sourceX*2-1]["river"] = 5; // Lake // We need it for the case when the cycle ended and did not find the direction of the river
		}
	}
	/**
	* Generates labyrinth
	*/
	public function labGenerate() {
		$this->lab0Generate();
		$this->markExternalWalls();
		$this->labExitGenerate();
		$this->labRiverGenerate();
		$this->labHoleBumGenerate("hole");
		$this->labHoleBumGenerate("bum");/**/
	}
	
	/*
	labPrint
	*/
	/**
	* Prints a wall cell
	* @param int $wallStrength strength of the wall
	*/
	function writeWallTD($wallStrength) {
		 	if ($wallStrength >0) {
		 		echo "<td class=wall>&nbsp;&nbsp;&nbsp;</td>";
		 	}else{
		 		echo "<td>&nbsp;&nbsp;&nbsp;</td>";
		 	}
	}
	/**
	* Prints a walkable cell
	* @param array $cellContent array of things on this cell (holes, booms, rivers)
	*/
	function writeCellTD($cellContent) {
		$tdClass = "cell";
		$hole = "&nbsp;";
		if ($cellContent["hole"] >= 1 && $cellContent["hole"] <= 4){ // 2d 1 & 4 const
			$hole = "&#".(9311 + $cellContent["hole"]).";"; // 9311 offset for number in a cycle
		}
		$river = "&nbsp;";
		if ($cellContent["river"] >= 1 && $cellContent["river"] <= 5){ // 5 - Lake // 2do const
			$river = "&#".(8591 + $cellContent["river"]).";"; // 8591 offset for arrow
			$tdClass = "river";
		}
		$bum = "&nbsp;";
		if ($cellContent["bum"] == 1){
			$bum = "&#9960;"; // hospital ⛨
		} elseif ($cellContent["bum"] == 2) {
			$bum = "&#9876;"; // arsenal sign ⚔
		} elseif ($cellContent["bum"] > 2) {
			$bum = "&#128176;"; // money bag 💰
		}
		echo "<td class='".$tdClass."'>".$hole.$river.$bum."</td>";
	}
	/**
	* Prints a wall line
	* @param int $yLine y coordinate of line
	*/
	function wallLine($yLine) {
		echo "<tr>";
		echo "<td></td><td>".$yLine."</td>";
		for ($i = 0; $i < $this->Xmax*2+1; $i++) {
		 	$this->writeWallTD($this->map[$yLine][$i]);
		}
		echo "</tr>\n";
	}
	/**
	* Prints a walkable line
	* @param int $yLine y coordinate of line
	*/
	function cellLine($yLine) {
		echo "<tr>";
		echo "<td><strong>".(($yLine+1)/2)."</strong></td><td>".$yLine."</td>";
		for ($i = 0; $i < $this->Xmax*2+1; $i++) {
			if (gettype($this->map[$yLine][$i]) === "integer"){
				$this->writeWallTD($this->map[$yLine][$i]);
			}elseif(gettype($this->map[$yLine][$i]) === "array"){
				$this->writeCellTD($this->map[$yLine][$i]);
			}
		}
		echo "</tr>\n";
	}
	/**
	* Prints entire labyrinth
	*/
	public function labPrint() {
		echo "<pre><table id='labMap' border=1 cellspacing=0>\n";
		echo "<tr>";
		echo "<td>&nbsp;</td><td><strong>X</strong></td>";
		echo "<td>&nbsp;</td>";
		for ($i = 1; $i <= $this->Xmax; $i++) {
			echo "<td><strong>".$i."</strong></td><td>&nbsp;</td>";
		}
		echo "</tr>\n";
		echo "<tr>";
		echo "<td><strong>Y</strong></td><td>xy</td>";
		for ($i = 0; $i < $this->Xmax*2+1; $i++) {
			echo "<td>".$i."</td>";
		}
		echo "</tr>\n";
		$this->wallLine(0);
		for ($i = 1; $i < $this->Ymax*2+1; $i += 2) {
			$this->cellLine($i);
			$this->wallLine($i+1);
		}
		echo "</table></pre>\n";
	}

		/*
		labReaction
		*/
	/**
	* Checks if there is a wall
	* @param string $direction direction of the player
	* @param LabPlayer $player the active player
	*/
	function isWall($direction, $player) {
		if ($direction == 'North'){
			if ($this->map[$player->y-1][$player->x] == 0){
				return false;
			}
		} elseif ($direction == 'South'){
			if ($this->map[$player->y+1][$player->x] == 0){
				return false;
			}
		} elseif ($direction == 'West'){
			if ($this->map[$player->y][$player->x-1] == 0){
				return false;
			}
		} elseif ($direction == 'East'){
			if ($this->map[$player->y][$player->x+1] == 0){
				return false;
			}
		}
		return true;
	}
	/**
	* Creates a wall for the player
	* @param string $direction direction of the player
	* @param LabPlayer $player the active player
	*/
	function makeWall($direction, $player) {
		if ($direction == 'North'){
			$this->map[$player->y-1][$player->x]++;
		} elseif ($direction == 'South'){
			$this->map[$player->y+1][$player->x]++;
		} elseif ($direction == 'West'){
			$this->map[$player->y][$player->x-1]++;
		} elseif ($direction == 'East'){
			$this->map[$player->y][$player->x+1]++;
		}
		return true;
	}
	/**
	* Expodes a wall
	* @param string $direction direction of the player
	* @param LabPlayer $player the active player
	*/
	function explodeWall($direction, $player) {
		if ($direction == 'North'){
			if ($this->map[$player->y-1][$player->x] > 0){
				$this->map[$player->y-1][$player->x]--;
				return true;
			} else {
				return false;
			}
		} elseif ($direction == 'South'){
			if ($this->map[$player->y+1][$player->x] > 0){
				$this->map[$player->y+1][$player->x]--;
				return true;
			} else {
				return false;
			}
		} elseif ($direction == 'West'){
			if ($this->map[$player->y][$player->x-1] > 0){
				$this->map[$player->y][$player->x-1]--;
				return true;
			} else {
				return false;
			}
		} elseif ($direction == 'East'){
			if ($this->map[$player->y][$player->x+1] > 0){
				$this->map[$player->y][$player->x+1]--;
				return true;
			} else {
				return false;
			}
		}
		return true;
	}
	/**
	* Moves from one hole to another hole (1-2-3-4-1...)
	* @param LabPlayer $player the player to move
	* @param Turn $turn current turn to process
	*/
	function turnHoleMoveReaction($player, $turn) {
		$turn->noSubturn++;
		$answer = "";
		$holeNo = $this->map[$player->y][$player->x]["hole"];
		if ($holeNo >= 4){ // 2do const holes in a cycle
			$player->moveTo($this->holes[1]['x'], $this->holes[1]['y']);
		} else{
			$player->moveTo($this->holes[$holeNo+1]['x'], $this->holes[$holeNo+1]['y']);
		}
		if ($this->map[$player->y][$player->x]["bum"] > 0){
			$cellBum = "-Boom";
			$answer = "* You can blow it up *";
		} else {
			$cellBum = "";
		}
		if ($this->map[$player->y][$player->x]["river"] > 0){
			$cellType = "River";
			$answer = "~ ~ ~";
		} else {
			$cellType = "Land";
			$answer = "...";
		}
		if ($this->map[$player->y][$player->x]["hole"] > 0){
			$cellHole = "-Hole";
			$answer = ". O .";
		} else {
			$cellHole = "";
		}
		array_push($turn->subturns, 
			array("noSubturn" => $turn->noSubturn, 
				"reaction" => "hole move".": ".$cellType.$cellHole.$cellBum."", // 2do "hole move" to translate constant
				"answer" => $answer
			)
		);
	}
	/**
	* Moves the player in the river direction
	* @param LabPlayer $player the player to move
	* @param Turn $turn current turn to process
	*/
	function turnRiverMoveReaction($player, $turn) {
		$turn->noSubturn++;
		$answer = "";
		$direction = $this->map[$player->y][$player->x]["river"];
		if ($direction == 1) {
			if ($this->map[$player->y][$player->x-1] == 0){
				$player->x -= 2;
			}
		} elseif ($direction == 2) {
			if ($this->map[$player->y-1][$player->x] == 0){
				$player->y -= 2;
			}
		} elseif ($direction == 3) {
			if ($this->map[$player->y][$player->x+1] == 0){
				$player->x += 2;
			}
		} elseif ($direction == 4) {
			if ($this->map[$player->y+1][$player->x] == 0){
				$player->y += 2;
			}
		}
		/* 2do to functions */
		if ($this->map[$player->y][$player->x]["bum"] > 0){
			$cellBum = "-Bum";
			$answer = "* You can blow it up *";
		} else {
			$cellBum = "";
		}
		if ($this->map[$player->y][$player->x]["river"] > 0){
			$cellType = "River";
			$answer = "~ ~ ~";
		} else {
			$cellType = "Land";
			$answer = "...";
		}
		if ($this->map[$player->y][$player->x]["hole"] > 0){
			$cellHole = "-Hole";
			$answer = ". O .";
		} else {
			$cellHole = "";
		}
		array_push($turn->subturns, 
			array("noSubturn" => $turn->noSubturn, 
				"reaction" => "river move".": ".$cellType.$cellHole.$cellBum."", // 2do "hole move" to translate constant
				"answer" => $answer
			)
		);
		if ($this->map[$player->y][$player->x]["hole"] > 0){
			$this->turnHoleMoveReaction($player, $turn);
		}
	}
	/**
	* Moves the player when they want to.
	* @param string $turnActin current action ("move")
	* @param int $direction direction of the player
	* @param LabPlayer $player the player to move
	* @param Turn $turn current turn to process
	*/
	function turnMoveReaction($turnActin, $direction, $player, $turn) {
		if ($direction == 'Center'){
			$answer .= "Running on the spot is good idea, but not in this case. Try again.";
			$turn->finished = false;
			array_push($turn->subturns, 
				array("noSubturn" => $turn->noSubturn, 
					"reaction" => $turnActin." ".$direction.": Invalid move",
					"answer" => $answer
				)
			);
		} elseif ($this->isWall($direction, $player)){
			$answer .= "Wall, try again.";
			$turn->finished = false;
			array_push($turn->subturns, 
				array("noSubturn" => $turn->noSubturn, 
					"reaction" => $turnActin." ".$direction.": Wall",
					"answer" => $answer
				)
			);
		} else {
			if ($direction == 'North'){
				$player->y -= 2;
			} elseif ($direction == 'South'){
				$player->y += 2;
			} elseif ($direction == 'West'){
				$player->x -= 2;
			} elseif ($direction == 'East'){
				$player->x += 2;
			}
			$answer = "";
			if ($this->map[$player->y][$player->x]["bum"] > 0){
				$cellBum = "-Boom";
				$answer = "* You can blow it up *";
			} else {
				$cellBum = "";
			}
			if ($this->map[$player->y][$player->x]["river"] > 0){
				$cellType = "River";
				$answer = "~ River moves you ~";
			} else {
				$cellType = "Land";
				$answer = "...";
			}
			if ($this->map[$player->y][$player->x]["hole"] > 0){
				$cellHole = "-Hole";
				$answer = "~ Hole moves you. ~";
			} else {
				$cellHole = "";
			}
			if ($player->y == -1 || $player->x == -1 || $player->y == $this->Ymax*2+1|| $player->x == $this->Xmax*2+1){
				$cellType = "Exit";
				$answer = "";
				foreach($player->treasures as $value){
					if ($value == 3){ // 3 is Real treasure // may b 2do
						$answer .= "<br />You have got Real treasure! <br />YOU WIN!!!";
					} else {
						$answer .= "<br />You have got Fake treasure.";
					}
				}
			}
			array_push($turn->subturns, 
				array("noSubturn" => $turn->noSubturn, 
					"reaction" => $turnActin." ".$direction.": ".$cellType.$cellHole.$cellBum."",
					"answer" => $answer
				)
			);
			if ($this->map[$player->y][$player->x]["hole"] > 0){
				$this->turnHoleMoveReaction($player, $turn);
			} elseif ($this->map[$player->y][$player->x]["river"] > 0){
				$this->turnRiverMoveReaction($player, $turn);
			}
			$turn->finished = true;
		}
	}
	/**
	* Processes blowing booms (blowing up nothing, hospital, arsenal, treasures
	* @param string $turnActin current action ("bum")
	* @param int $direction direction of the player
	* @param LabPlayer $player the player-exploder
	* @param Turn $turn current turn to process
	*/
	function turnExplodeCell($turnActin, $direction, $player, $turn) {
		if ($this->map[$player->y][$player->x]["bum"] == 0){
			array_push($turn->subturns, 
				array("noSubturn" => $turn->noSubturn, 
					"reaction" => $turnActin." ".$direction.": Invalid move",
					"answer" => "Blow yourself up is good idea, but not in this case. Try again."
				)
			);
			$turn->finished = false;
		} elseif ($this->map[$player->y][$player->x]["bum"] == 1){ // hospital
			$player->tnt--;
			$player->incForfeit();
			foreach($player->treasures as $key => $value){
				$x = $this->booms[$value]["x"];
				$y = $this->booms[$value]["y"];
				$this->map[$y][$x]["bum"] = $value;
				unset($player->treasures[$key]);
			}
			array_push($turn->subturns, 
				array("noSubturn" => $turn->noSubturn, 
					"reaction" => $turnActin." ".$direction.": Hospital",
					"answer" => "Do not blow up hospitals! Lose a turn."
				)
			);
			$turn->finished = true;
		} elseif ($this->map[$player->y][$player->x]["bum"] == 2){ //arsenal
			$player->tnt--;
			$player->getArsenal();
			array_push($turn->subturns, 
				array("noSubturn" => $turn->noSubturn, 
					"reaction" => $turnActin." ".$direction.": Arsenal",
					"answer" => "You gain 3 tnt, 2 cement, 1 bullet"
				)
			);
			$turn->finished = true;
		} elseif ($this->map[$player->y][$player->x]["bum"] > 2){ //treasure
			$player->tnt--;
			$id = $this->map[$player->y][$player->x]["bum"];
			$this->map[$player->y][$player->x]["bum"] = 0;
			$player->getTreasure($id);
			array_push($turn->subturns, 
				array("noSubturn" => $turn->noSubturn, 
					"reaction" => $turnActin." ".$direction.": Treasure",
					"answer" => "TREASURE!	"
				)
			);
			$turn->finished = true;
		}
	}
	/**
	* Process the explode reaction (blowing without tnt, blowing booms, blowing walls)
	* @param string $turnActin current action ("bum")
	* @param int $direction direction of the player
	* @param LabPlayer $player the player-exploder
	* @param Turn $turn current turn to process
	*/
	function turnBumReaction($turnActin, $direction, $player, $turn) {
		if ($player->tnt <= 0){
			$answer .= "You have no enough TNT. Try again.";
			$turn->finished = false;
			array_push($turn->subturns, 
				array("noSubturn" => $turn->noSubturn, 
					"reaction" => $turnActin." ".$direction.": Invalid move",
					"answer" => $answer
				)
			);
		} elseif ($direction == 'Center'){
			$this->turnExplodeCell($turnActin, $direction, $player, $turn);
		} else{
			if ($this->explodeWall($direction, $player)){
				$player->tnt--;
				$turn->finished = true;
				array_push($turn->subturns, 
					array("noSubturn" => $turn->noSubturn, 
						"reaction" => $turnActin." ".$direction.": Broken wall.",
						"answer" => "The wall is blown up. You can go through if it was only one."
					)
				);
			} else {
				$turn->finished = false;
				array_push($turn->subturns, 
					array("noSubturn" => $turn->noSubturn, 
						"reaction" => $turnActin." ".$direction.": Invalid move",
						"answer" => "No wall to blow up. Try again."
					)
				);
			}
		}
	}
	/**
	* Proccesses reaction to player actions (move, boom, cement, shoot (2do))
	* @param string $turnActin current action ("move", "bum", "cement", "shoot")
	* @param int $direction direction of the player
	* @param LabPlayer $player the player-exploder
	* @param Turn $turn current turn to process
	*/
	function turnLabReaction($turnActin, $direction, $player, &$turn) {
		$answer = "";
		if ($turnActin == 'move'){
			$answer = $this->turnMoveReaction($turnActin, $direction, $player, $turn);
		} elseif ($turnActin == 'bum'){
			$answer = $this->turnBumReaction($turnActin, $direction, $player, $turn);
		} elseif ($turnActin == 'cement'){
			if ($player->cement <= 0){
				$answer .= "You have no enough cement. Try again.";
				$turn->finished = false;
				array_push($turn->subturns, 
					array("noSubturn" => $turn->noSubturn, 
						"reaction" => $turnActin." ".$direction.": Invalid move",
						"answer" => $answer
					)
				);
			} elseif ($direction == 'Center'){
				$answer .= "Cement yourself is good idea, but not in this case. Try again.";
				$turn->finished = false;
				array_push($turn->subturns, 
					array("noSubturn" => $turn->noSubturn, 
						"reaction" => $turnActin." ".$direction.": Invalid move",
						"answer" => $answer
					)
				);
			} elseif ($this->makeWall($direction, $player)){
				$answer .= "Wall is added.";
				$player->cement--;
				$turn->finished = true;
				array_push($turn->subturns, 
					array("noSubturn" => $turn->noSubturn, 
						"reaction" => $turnActin." ".$direction.": new Wall",
						"answer" => $answer
					)
				);
				// 2do if river move, if hole not
			}
		} elseif ($turnActin == 'shoot'){
			if ($player->bullet <= 0){
				$answer .= "You have no enough bullets. Try again.";
				$turn->finished = false;
				array_push($turn->subturns, 
					array("noSubturn" => $turn->noSubturn, 
						"reaction" => $turnActin." ".$direction.": Invalid move",
						"answer" => $answer
					)
				);
			} elseif ($direction == 'Center'){
				$answer .= "Shoot yourself is good idea, but not in this case. Try again.";
				$turn->finished = false;
				array_push($turn->subturns, 
					array("noSubturn" => $turn->noSubturn, 
						"reaction" => $turnActin." ".$direction.": Invalid move",
						"answer" => $answer
					)
				);
			} else {
				//2do
				$turn->finished = true;
				array_push($turn->subturns, 
					array("noSubturn" => $turn->noSubturn, 
						"reaction" => $turnActin." ".$direction.": 2do",
						"answer" => "2do"
					)
				);
			}
		}
		//return $answer;
		
	}
}
