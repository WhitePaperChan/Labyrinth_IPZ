<html>
<head>
	<meta http-equiv=content-type content="text/html; charset=utf-8"> 
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<link href="Style.css" rel=stylesheet type=text/css>
	<title> Lab Training </title>
</head>
<body>
<?php
/**
* Training singleplayer game
*/	
$script="http://".$_SERVER["HTTP_HOST"].$_SERVER["SCRIPT_NAME"];
$form="form_lab_parameters.php";
$form_turn_buttons = "form_turn_buttons.php";
require("libLabth.php");
require("libLabPlayer.php");
	
if($_SERVER['REQUEST_METHOD']=="GET" || isset($_REQUEST['Training'])){ // no POST or started from other script, show form with defaults
	$Xmax = 5;
	$Ymax = 5;
	$minRiverLengthIndex = 5; // min river length 2do
	$maxRiverLengthIndex = 3; // max river length 2do
	$allThingsTogether = false;
	$showMap = false;
	require($form);
} else { // $_SERVER['REQUEST_METHOD']=="POST"
	$Xmax = $_REQUEST['Xmax'];
	$Ymax = $_REQUEST['Ymax'];
	if($_POST["Generate"]=="Generate"){	//	Generate Lab
		$nickname = $_REQUEST['nickname'];
		$minRiverLengthIndex = $_REQUEST['minRiverLengthIndex']; // min river length 2do
		$maxRiverLengthIndex = $_REQUEST['maxRiverLengthIndex']; // max river length 2do
		$allThingsTogether = isset($_REQUEST['allThingsTogether']);
		$showMap = isset($_REQUEST['showMap']);
		
		$lab = new Labth($Xmax, $Ymax, $minRiverLengthIndex, $maxRiverLengthIndex, $allThingsTogether, $showMap);
		$lab->labGenerate();
		
		echo "<br>\n";
		echo "Lab generated.<br>\n";
		if ($lab->showMap){
			echo "&#128176;&#9876;&#10010;&#9960;<br>\n";
			$lab->labPrint();
		}
		$lab_json = str_replace('"', '~', serialize($lab));
		echo "<br>\n";
		
		$land = $lab->landFill();
		$landSelected = array();
		if ($lab->randomCellGet($land, $landSelected)){
			$player = new LabPlayer($nickname);
			$player->moveTo($landSelected["x"], $landSelected["y"]);
			
			$player_serialize = str_replace('"', '~', serialize($player));
			$prevTurn = new Turn;
			$prevTurn_serialize = str_replace('"', '~', serialize($prevTurn));
			$currTurn = new Turn;
			$currTurn->no = 0;
			$currTurn->playerName = $player->name;
			array_push($currTurn->subturns, 
				array("noSubturn" => 0, // 0 only for landing
					"reaction" => "start: Land",
					"answer" => "..."
				)
			);
			$currTurn->finished = true;
			$currTurn_serialize = str_replace('"', '~', serialize($currTurn));
			require($form_turn_buttons);
			if ($lab->showMap){
				echo "Player position: X=".(($player->x+1)/2)." Y=".(($player->y+1)/2)."<br>\n";
			}
			$player->inventoryPrint();
			echo "<br />".$currTurn->toStr(); 
		} else {
			echo "No land to put the player.<br>\n";
		}
	}else{
		$lab_json = $_REQUEST['lab_json']; // needed for form
		$lab = unserialize(str_replace('~', '"', $lab_json)); 
		$player_serialize = $_REQUEST['player_serialize']; 
		$player = unserialize(str_replace('~', '"', $player_serialize));
		$prevTurn_serialize = $_REQUEST['prevTurn_serialize']; 
		$prevTurn = unserialize(str_replace('~', '"', $prevTurn_serialize)); 
		$currTurn_serialize = $_REQUEST['currTurn_serialize']; 
		$currTurn = unserialize(str_replace('~', '"', $currTurn_serialize)); 
		if ($currTurn->finished){
			$prevTurn = clone $currTurn;
			$currTurn->no++;
			$currTurn->playerName = $player->name;
			$currTurn->noSubturn = 1;
			$currTurn->subturns = array();
		} else {
			$currTurn->noSubturn++;
		}
		$turnActin = $_REQUEST['action'];
		
		$answer = "";
		
		if (isset($_REQUEST['North'])){
			$lab->turnLabReaction($turnActin, 'North', $player, $currTurn);
		} elseif (isset($_REQUEST['South'])){
			$lab->turnLabReaction($turnActin, 'South', $player, $currTurn);
		} elseif (isset($_REQUEST['West'])){
			$lab->turnLabReaction($turnActin, 'West', $player, $currTurn);
		} elseif (isset($_REQUEST['East'])){
			$lab->turnLabReaction($turnActin, 'East', $player, $currTurn);
		} elseif (isset($_REQUEST['Center'])){
			$lab->turnLabReaction($turnActin, 'Center', $player, $currTurn);
		}
		if ($lab->showMap){
			$lab->labPrint();
		}
		
		$lab_json = str_replace('"', '~', serialize($lab));
		
		$player_serialize = str_replace('"', '~', serialize($player));
		$prevTurn_serialize = str_replace('"', '~', serialize($prevTurn));
		$currTurn_serialize = str_replace('"', '~', serialize($currTurn));
		require($form_turn_buttons);
		if ($lab->showMap){
			echo "Player position: X=".(($player->x+1)/2)." Y=".(($player->y+1)/2)."<br>\n";
		}
		$player->inventoryPrint();
		echo "<br>\n";
		echo $currTurn->no.". ".$currTurn->playerName."<br>\n";
		if (count($currTurn->subturns) > 0){
			foreach ($currTurn->subturns as $value) {
				echo $currTurn->no.".".$value["noSubturn"].". ".$value["reaction"]."<br>\n";
				echo $value["answer"]."<br>\n";
			}
		}
		echo "<br>\n";
		echo $prevTurn->no.". ".$prevTurn->playerName."<br>\n";
		if (count($prevTurn->subturns) > 0){
			foreach ($prevTurn->subturns as $value) {
				echo $prevTurn->no.".".$value["noSubturn"].". ".$value["reaction"]."<br>\n";
			}
		}
	}
}
	//var_dump($lab->holes);
	//phpinfo();

?>

</body>
</html>
