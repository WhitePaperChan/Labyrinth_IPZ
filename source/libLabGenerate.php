<?php
/*
labGenerate
*/
function noCellTypeFill($Xmax, $Ymax, $map, $cellType) {
	$land = array();
	for ($i=1; $i<=$Xmax; $i++){
		for ($j=1; $j<=$Ymax; $j++){
			$x = $i*2-1;
			$y = $j*2-1;
			if ($map[$y][$x][$cellType] == 0){
				array_push($land, array("x" => $x, "y" => $y));
			}
		}
	}
	return $land;
}
function landFill($Xmax, $Ymax, $map) {
	$land = array();
	for ($i=1; $i<=$Xmax; $i++){
		for ($j=1; $j<=$Ymax; $j++){
			$x = $i*2-1;
			$y = $j*2-1;
			if ($map[$y][$x] == array("hole" => 0, "river" => 0, "bum" => 0)){
				array_push($land, array("x" => $x, "y" => $y));
			}
		}
	}
	return $land;
}
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
function wallLineGenerate($Xmax) {
	$wallLine = array(0);
	for ($i = 1; $i < $Xmax*2+1; $i += 2) {
		array_push($wallLine, 0, 0);
	}
	return $wallLine;
}
function cellLineGenerate($Xmax) {
	$cellLine = array(0);
	for ($i = 1; $i < $Xmax*2+1; $i += 2) {
		array_push($cellLine, array("hole" => 0, "river" => 0, "bum" => 0), 0);
	}
	return $cellLine;
}
function lab0Generate($Xmax, $Ymax, array &$map) {
	array_push($map, wallLineGenerate($Xmax));
	for ($i = 1; $i < $Ymax*2+1; $i += 2) {
		array_push($map, cellLineGenerate($Xmax));
		array_push($map, wallLineGenerate($Xmax));
	}
}
function markExternalWalls($Xmax, $Ymax, array &$map) {
	for ($i = 0; $i < $Xmax*2+1; $i++) {
	 	$map[0][$i] = 999;
	}
	for ($i = 1; $i < $Ymax*2+1; $i += 2) {
	 	$map[$i][0] = 999; $map[$i][$Xmax*2] = 999;
		for ($j = 0; $j < $Xmax*2+1; $j += 2) {
		 	$map[$i+1][$j] = 999;
		}
	}
	for ($i = 0; $i < $Xmax*2+1; $i++) {
		$y = $Ymax*2;
	 	$map[$y][$i] = 999;
	}
}
function labExitGenerate($Xmax, $Ymax, array &$map) {
	$side = random_int(1, 4);
	if ($side == 1) {
		$map[0][random_int(1, $Xmax)*2-1] =0;
	} elseif ($side == 2) {
		$map[$Ymax*2][random_int(1, $Xmax)*2-1] =0;
	} elseif ($side == 3) {
		$map[random_int(1, $Ymax)*2-1][0] =0;
	} elseif ($side == 4) {
		$map[random_int(1, $Ymax)*2-1][$Xmax*2] =0;
	}
}

function labHoleBumGenerate(&$map, $Xmax, $Ymax, $allThingsTogether, $cellType /* "hole" or "bum" */) {
	if ($allThingsTogether){
		$land = noCellTypeFill($Xmax, $Ymax, $map, $cellType);
	} else {
		$land = landFill($Xmax, $Ymax, $map);
	}
	for ($i = 1; $i <=4; $i++){
		$landSelected = array();
		if (randomCellGet($land, $landSelected)){
				$map[$landSelected["y"]][$landSelected["x"]][$cellType] = $i;
		} else {
			echo "No Cell to place object ".$cellType."<br />"; // 2do 2 error log
		}
	}
}

function riverSource($Xmax, $Ymax, &$sourceX, &$sourceY) {
	$side = random_int(1, 4);
	if ($side == 1) {
		$sourceY = 1; $sourceX = random_int(1, $Xmax);
	} elseif ($side == 2) {
		$sourceY = $Ymax; $sourceX = random_int(1, $Xmax);
	} elseif ($side == 3) {
		$sourceY = random_int(1, $Ymax); $sourceX = 1;
	} elseif ($side == 4) {
		$sourceY = random_int(1, $Ymax); $sourceX = $Xmax;
	}
}

function findAvailableRiverDirections(&$map, $Xmax, $Ymax, $sourceX, $sourceY) {
			$availableDirections = array();
			if ($sourceX > 1 && $map[$sourceY*2-1][$sourceX*2-3]["river"] == 0) {
				array_push($availableDirections, 1);
			} elseif ($sourceY > 1 && $map[$sourceY*2-3][$sourceX*2-1]["river"] == 0) {
				array_push($availableDirections, 2);
			} elseif ($sourceX < $Xmax && $map[$sourceY*2-1][$sourceX*2+1]["river"] == 0) {
				array_push($availableDirections, 3);
			} elseif ($sourceY == $Ymax && $map[$sourceY*2+1][$sourceX*2-1]["river"] == 0) {
				array_push($availableDirections, 4);
			}
			return $availableDirections;
}

function riverDirectionGenerate(&$map, $Xmax, $Ymax, $sourceX, $sourceY) {
			$availableDirections = findAvailableRiverDirections($map, $Xmax, $Ymax, $sourceX, $sourceY);
			$numberOfAvailableDirection = count($availableDirections);
			if ($numberOfAvailableDirection == 0){
				// Lake
				$map[$sourceY*2-1][$sourceX*2-1]["river"] = 5;
			} else {
				$map[$sourceY*2-1][$sourceX*2-1]["river"] = $availableDirections[random_int(1, $numberOfAvailableDirection)];
			}
}

function labRiverGenerate(&$map, $Xmax, $Ymax, $minRiverLength, $maxRiverLength) {
	$sourceY = 1; $sourceX = 1;
	riverSource($Xmax, $Ymax, $sourceX, $sourceY);
	$direction = random_int(1, 4);
	$riverCellNumber = 1; // river lenth counter
	// if river is not so long not direct it to out
	for ($inf = 1; $inf <= 99999; $inf++) {  // while (true) is a not good idea for web server
		if ($riverCellNumber >= $maxRiverLength){
			$map[$sourceY*2-1][$sourceX*2-1]["river"] = 5; //Lake
			break;
		}
		if ($direction == 1 && $sourceX == 1) {
			if ($riverCellNumber >= $minRiverLength){
				$map[$sourceY*2-1][$sourceX*2-1]["river"] = $direction;
				break;
			} else {
				$direction = random_int(1, 4);
				continue;
			}
		} elseif ($direction == 2 && $sourceY == 1) {
			if ($riverCellNumber >= $minRiverLength){
				$map[$sourceY*2-1][$sourceX*2-1]["river"] = $direction;
				break;
			} else {
				$direction = random_int(1, 4);
				continue;
			}
		} elseif ($direction == 3 && $sourceX == $Xmax) {
			if ($riverCellNumber >= $minRiverLength){
				$map[$sourceY*2-1][$sourceX*2-1]["river"] = $direction;
				break;
			} else {
				$direction = random_int(1, 4);
				continue;
			}
		} elseif ($direction == 4 && $sourceY == $Ymax) {
			if ($riverCellNumber >= $minRiverLength){
				$map[$sourceY*2-1][$sourceX*2-1]["river"] = $direction;
				break;
			} else {
				$direction = random_int(1, 4);
				continue;
			}
		}
		if ($direction == 1 && $map[$sourceY*2-1][$sourceX*2-3]["river"] >0) {
			riverDirectionGenerate($map, $Xmax, $Ymax, $sourceX, $sourceY);
		} elseif ($direction == 2 && $map[$sourceY*2-3][$sourceX*2-1]["river"] >0) {
			riverDirectionGenerate($map, $Xmax, $Ymax, $sourceX, $sourceY);
		} elseif ($direction == 3 && $map[$sourceY*2-1][$sourceX*2+1]["river"] >0) {
			riverDirectionGenerate($map, $Xmax, $Ymax, $sourceX, $sourceY);
		} elseif ($direction == 4 && $map[$sourceY*2+1][$sourceX*2-1]["river"] >0) {
			riverDirectionGenerate($map, $Xmax, $Ymax, $sourceX, $sourceY);
		} else {
			$map[$sourceY*2-1][$sourceX*2-1]["river"] = $direction;
		$riverCellNumber++;
		}
		$direction = $map[$sourceY*2-1][$sourceX*2-1]["river"];
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
	if ($map[$sourceY*2-1][$sourceX*2-1]["river"] == 0){
		$map[$sourceY*2-1][$sourceX*2-1]["river"] = 5; // Lake // We need it for the case when the cycle ended and did not find the direction of the river
	}
}

function labGenerate($Xmax, $Ymax, $minRiverLength, $maxRiverLength, $allThingsTogether, array &$map) {
	lab0Generate($Xmax, $Ymax, $map);
	markExternalWalls($Xmax, $Ymax, $map);
	labExitGenerate($Xmax, $Ymax, $map);
	labRiverGenerate($map, $Xmax, $Ymax, $minRiverLength, $maxRiverLength);
	labHoleBumGenerate($map, $Xmax, $Ymax, $allThingsTogether, "hole");
	labHoleBumGenerate($map, $Xmax, $Ymax, $allThingsTogether, "bum");
}

/*
labPrint
*/
function writeWallTD($wallStrength) {
	 	if ($wallStrength >0) {
	 		echo "<td class=wall>&nbsp;&nbsp;&nbsp;</td>";
	 	}else{
	 		echo "<td>&nbsp;&nbsp;&nbsp;</td>";
	 	}
}
function writeCellTD($cellContent) {
	$tdClass = "cell";
	$hole = "&nbsp;";
	if ($cellContent["hole"] >= 1 && $cellContent["hole"] <= 4){
		$hole = "&#".(9311 + $cellContent["hole"]).";";
	}
	$river = "&nbsp;";
	if ($cellContent["river"] >= 1 && $cellContent["river"] <= 5){ // 5 - Lake
		$river = "&#".(8591 + $cellContent["river"]).";";
		$tdClass = "river";
	}
	$bum = "&nbsp;";
	if ($cellContent["bum"] == 1){
		$bum = "&#9960;";
	} elseif ($cellContent["bum"] == 2) {
		$bum = "&#9876;";
	} elseif ($cellContent["bum"] > 2) {
		$bum = "&#128176;";
	}
	echo "<td class='".$tdClass."'>".$hole.$river.$bum."</td>";
}
function wallLine($yLine, $Xmax, $map) {
	echo "<tr>";
	echo "<td></td><td>".$yLine."</td>";
	for ($i = 0; $i < $Xmax*2+1; $i++) {
	 	writeWallTD($map[$yLine][$i]);
	}
	echo "</tr>\n";
}
function cellLine($yLine, $Xmax, $map) {
	echo "<tr>";
	echo "<td><strong>".(($yLine+1)/2)."</strong></td><td>".$yLine."</td>";
	for ($i = 0; $i < $Xmax*2+1; $i++) {
		if (gettype($map[$yLine][$i]) === "integer"){
			writeWallTD($map[$yLine][$i]);
		}elseif(gettype($map[$yLine][$i]) === "array"){
			writeCellTD($map[$yLine][$i]);
		}
	}
	echo "</tr>\n";
}
function labPrint($Xmax, $Ymax, $map) {
	echo "<pre><table id='labMap' border=1 cellspacing=0>\n";
	echo "<tr>";
	echo "<td>&nbsp;</td><td><strong>X</strong></td>";
	echo "<td>&nbsp;</td>";
	for ($i = 1; $i <= $Xmax; $i++) {
		echo "<td><strong>".$i."</strong></td><td>&nbsp;</td>";
	}
	echo "</tr>\n";
	echo "<tr>";
	echo "<td><strong>Y</strong></td><td>xy</td>";
	for ($i = 0; $i < $Xmax*2+1; $i++) {
		echo "<td>".$i."</td>";
	}
	echo "</tr>\n";
	wallLine(0, $Xmax, $map);
	for ($i = 1; $i < $Ymax*2+1; $i += 2) {
		cellLine($i, $Xmax, $map);
		wallLine($i+1, $Xmax, $map);
	}
	echo "</table></pre>\n";
}
