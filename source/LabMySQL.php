<html>
<head>
	<meta http-equiv=content-type content="text/html; charset=utf-8"> 
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<link href="Style.css" rel=stylesheet type=text/css>
	<title> Lab MySQL </title>
</head>
<body>
<?php
	
$script="http://".$_SERVER["HTTP_HOST"].$_SERVER["SCRIPT_NAME"];
/* 2do - constants to config file */
$form_start_join="form_lab_start-join.php";
$form_new="form_lab_parameters.php";
$form_turn_buttons = "form_turn_buttons_MySQL.php";
$form_join_player = "form_join_player.php";
$form_lab_start_game = "form_lab_start_game.php";
$form_lab_refresh = "form_lab_refresh.php";

require("libLabth.php");
require("libLabPlayer.php");
require("admin/libGameMySQL.php");
	
if($_SERVER['REQUEST_METHOD']=="GET"){ // not POSTs
	require($form_start_join);
} else { // $_SERVER['REQUEST_METHOD']=="POST"
	if(isset($_REQUEST['New'])){	//	Generate Lab
		require($form_new);
	} elseif(isset($_REQUEST['Generate'])){	//	Generate Lab
		$Xmax = $_REQUEST['Xmax'];
		$Ymax = $_REQUEST['Ymax'];
		$nickname = $_REQUEST['nickname'];
		//if (trim($nickname) == ""){; }// 2do
		$minRiverLengthIndex = $_REQUEST['minRiverLengthIndex']; // min river length 2do
		$maxRiverLengthIndex = $_REQUEST['maxRiverLengthIndex']; // max river length 2do
		$allThingsTogether = isset($_REQUEST['allThingsTogether']);
		$showMap = isset($_REQUEST['showMap']);
		
		$lab = new Labth($Xmax, $Ymax, $minRiverLengthIndex, $maxRiverLengthIndex, $allThingsTogether, $showMap);
		$lab->labGenerate();
		
		echo "&#128176;&#9876;&#9960;<br>\n";
		if ($lab->showMap){ $lab->labPrint(); }
		
		$land = $lab->landFill();
		$landSelected = array();
		if ($lab->randomCellGet($land, $landSelected)){
			$player = new LabPlayer($nickname);
			$player->moveTo($landSelected["x"], $landSelected["y"]);
			
			$db = new LabMySQL;
			$game_id = $db->mysql_insert_game($lab, $nickname);
			$player_no = 1; // game creator is no 1 // this var needed for form
			$db->mysql_insert_player($player, $player_no, $game_id);
			
			$currTurn = new Turn;
			$currTurn->landing($player->name, 0); // 0 only for creator
			$db->mysql_insert_turn($currTurn, $game_id);
			echo "<br>\nLab is generated.<br>\n<br>\n";
			if ($lab->showMap){ $player->positionPrint(); }
			$player->inventoryPrint();
			require($form_lab_start_game);
			echo "<br />".$currTurn->toStr(); 
		} else {
			echo "No land to put the player.<br>\n";
		}
	} elseif(isset($_REQUEST['Refresh'])){	
		$game_id = $_REQUEST['game_id'];
		$player_no = $_REQUEST['player_no'];
		$db = new LabMySQL;
		$db->status_refresh($game_id, $player_no, 'Refresh');
	} elseif(isset($_REQUEST['Start'])){	
		$game_id = $_REQUEST['game_id'];
		$player_no = $_REQUEST['player_no'];
		$db = new LabMySQL;
		$db->mysql_update_game_on_start($game_id, $player_no);
		
		$lab = $db->mysql_get_game($game_id, $new_game, $max_player_no, $curr_player);
		$player = $db->mysql_get_player($game_id, $player_no);
		$turnResult = $db->mysql_get_last_turns_table($game_id);

		if ($lab->showMap){ $lab->labPrint(); }
		require($form_turn_buttons);
		if ($lab->showMap){ $player->positionPrint(); }
		$player->inventoryPrint();
		$db->lastTurnsPrint($turnResult);
		
	} elseif(isset($_REQUEST['Join'])){	//	Join existing game
		$game_id = array_pop(explode(' ', $_REQUEST['Join']));
		$db = new LabMySQL;
		echo $db->mysql_get_game_meta_str($game_id);
		require($form_join_player);
	} elseif(isset($_REQUEST['NewPlayer'])){	//	after Join form
		$nickname = $_REQUEST['nickname'];
		$game_id = $_REQUEST['game_id'];
		$db = new LabMySQL;
		
		$lab = $db->mysql_get_game($game_id, $new_game, $max_player_no, $curr_player);
		echo "&#128176;&#9876;&#9960;<br>\n";
		if ($lab->showMap){ $lab->labPrint(); }

		$players_max_no = $db->mysql_get_players_max_no($game_id);
		$land = $lab->landFill();
		$landSelected = array();
		if ($lab->randomCellGet($land, $landSelected)){
			$player = new LabPlayer($nickname);
			$player->moveTo($landSelected["x"], $landSelected["y"]);
		}
		
		$player_no = $players_max_no + 1;
		$db->mysql_insert_player($player, $player_no, $game_id);
		$db->mysql_update_game_player_no_max_no($game_id, ($player_no + 1), $player_no);
		$turns_max_no = $db->mysql_get_turns_max_no($game_id);
		$turnResult = $db->mysql_get_last_turns_table($game_id);
		
		$currTurn = new Turn;
		$currTurn->landing($player->name, $turns_max_no + 1);
		$db->mysql_insert_turn($currTurn, $game_id);
		?>You are in the game!<br /><?
		if ($lab->showMap){ $player->positionPrint(); }
		$player->inventoryPrint();
		if ($new_game){
			require($form_lab_refresh);
		} else {
			require($form_turn_buttons);
		}
		echo "<br />".$currTurn->toStr(); 
		$db->lastTurnsPrint($turnResult);
	}else{
		$game_id = $_REQUEST['game_id'];
		$player_no = $_REQUEST['player_no'];
		$db = new LabMySQL;
		$lab = $db->mysql_get_game($game_id, $new_game, $max_player_no, $curr_player);
		if ($curr_player == $player_no){
			$player = $db->mysql_get_player($game_id, $player_no);
			
			$turnResult = $db->mysql_get_last_turns_table($game_id);
			$row=$turnResult->fetch_assoc();
			$currTurn_serialize = $row["obj"]; //2do
			$currTurn = unserialize(str_replace('~', '"', $currTurn_serialize)); 
			if ($currTurn->finished){
				$lastTurnStr = $currTurn->toStr();
				$newTurn = true;
				$currTurn->no++;
				$currTurn->playerName = $player->name;
				$currTurn->noSubturn = 1;
				$currTurn->subturns = array();
			} else {
				$lastTurnStr = "";
				$newTurn = false;
				$currTurn->noSubturn++;
			}
			$turnActin = $_REQUEST['action'];
			
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
			if ($lab->showMap){ $lab->labPrint(); }
			
			$db->mysql_update_game_curr_player($lab, $game_id, $player_no, $max_player_no, $curr_player, $currTurn->finished);
			$db->mysql_update_player($player, $player_no, $game_id);
			$db->mysql_insert_update_turn($currTurn, $newTurn, $game_id);
			
			require($form_turn_buttons);
			if ($lab->showMap){ $player->positionPrint(); }
			$player->inventoryPrint();
			echo "<br>\n".$currTurn->toStr(); 
			echo $lastTurnStr;
			$db->lastTurnsPrint($turnResult);
		} else {
		$game_id = $_REQUEST['game_id'];
		$player_no = $_REQUEST['player_no'];
		$db = new LabMySQL;
		$db->status_refresh($game_id, $player_no, 'Not your turn');
		}
	}
}
?>

</body>
</html>
