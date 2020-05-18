<?php

class LabMySQL {
	public $table_games = "games";
	public $table_game_players = "game_players";
	public $table_game_turns = "game_turns";
	public $last_turns_no = 5;

	protected $host="localhost";
	protected $data_base="labyrinth";
	protected $user="root";  // "root" for development on local cerver only
	protected $password=""; // "" for development on local cerver only
	
	function connect_my_db($host,$user,$password,$data_base){
		$mysqli = new mysqli($host,$user,$password,$data_base);
		if ($mysqli->connect_errno){
			echo "Error: Failed to make a MySQL connection, here is why: \n";
			echo "Errno: " . $mysqli->connect_errno . "\n";
			echo "Error: " . $mysqli->connect_error . "\n";
		}
		return $mysqli;
	}
	
	function lastTurnsPrint($turnResult){ // $turnResult - db result table
		while ($row = $turnResult->fetch_assoc()) {
			$currTurn_serialize = $row["obj"];
			$currTurn = unserialize(str_replace('~', '"', $currTurn_serialize));
			echo $currTurn->toStr(); 
		}
	}
	
/* games */
	function games_to_join(){
		$mysqli = $this->connect_my_db($this->host, $this->user, $this->password, $this->data_base);
		$query = "SELECT id, initiator, started FROM ".$this->table_games." WHERE `new_game` = true ORDER BY started DESC";
		if (!$result = $mysqli->query($query)) {echo "Error $table ".$mysqli->errno." ".$mysqli->error;}
		return $result;
	}

	/* insert to DB and return game id */
	public function mysql_insert_game($lab, $initiator){ 
		$lab_serialized = str_replace('"', '~', serialize($lab));
		$mysqli = $this->connect_my_db($this->host, $this->user, $this->password, $this->data_base);
		$query  = "INSERT ".$this->table_games
			." SET `new_game` = true, `finished_game` = false, `labth` = '".$lab_serialized
			."', `max_player_no` = '1', `initiator` = '".$initiator
			."'";
		if (!$result = $mysqli->query($query)) {echo "Error $table ".$mysqli->errno." ".$mysqli->error; /*exit;*/}
		return $mysqli->insert_id;
	}
	
	function mysql_get_game($game_id, &$new_game, &$max_player_no, &$curr_player){
		$mysqli = $this->connect_my_db($this->host, $this->user, $this->password, $this->data_base);
		$query = "SELECT labth, new_game, curr_player, max_player_no FROM ".$this->table_games." WHERE `id`='".$game_id."'";
		if (!$result = $mysqli->query($query)) {echo "Error $table ".$mysqli->errno." ".$mysqli->error;}
		$row=$result->fetch_assoc();
		$new_game = $row["new_game"]; 
		$lab_json = $row["labth"]; 
		$max_player_no = $row["max_player_no"]; 
		$curr_player = $row["curr_player"]; 
		$lab = unserialize(str_replace('~', '"', $lab_json)); 
		return $lab;
	}

	function mysql_update_game_on_start($game_id, $player_no){
		$mysqli = $this->connect_my_db($this->host, $this->user, $this->password, $this->data_base);
		$query  = "UPDATE ".$this->table_games
			." SET `new_game` = false, `curr_player` = '".$player_no
			."' WHERE `id`='".$game_id."'";
		if (!$result = $mysqli->query($query)) {echo "Error $table ".$mysqli->errno." ".$mysqli->error;}
	}

	function mysql_update_game_curr_player($lab, $game_id, $player_no, $max_player_no, $curr_player, $finished){
		$mysqli = $this->connect_my_db($this->host, $this->user, $this->password, $this->data_base);
		if ($player_no == $max_player_no){
			$curr_player = 1;
		} else {
			$curr_player++;
		}
		if ($finished){ // $currTurn->finished
			$curr_player_str = "', `curr_player`='".$curr_player;
		} else {
			$curr_player_str = "";
		}
		$lab_json = str_replace('"', '~', serialize($lab));
		$query  = "UPDATE ".$this->table_games
			." SET `labth` = '".$lab_json
			.$curr_player_str
			."' WHERE `id`='".$game_id
			."'";
		if (!$result = $mysqli->query($query)) {echo "Error $table ".$mysqli->errno." ".$mysqli->error; /*exit;*/}
	}

	function mysql_get_game_meta_str($game_id){
		$mysqli = $this->connect_my_db($this->host, $this->user, $this->password, $this->data_base);
		$query = "SELECT id, initiator, started FROM ".$this->table_games." WHERE `id`='".$game_id."'";
		if (!$result = $mysqli->query($query)) {echo "Error $table ".$mysqli->errno." ".$mysqli->error; /*exit;*/}
		$row=$result->fetch_assoc();
		return "<div>Game <strong>".$row["id"]."</strong> started by <strong>".$row["initiator"]."</strong> at <strong>".date('d.m.Y H:i', strtotime($row["started"]))."</strong></div><br />";
	}

	function mysql_update_game_player_no_max_no($game_id, $curr_player, $max_player_no){
		$mysqli = $this->connect_my_db($this->host, $this->user, $this->password, $this->data_base);
		$query  = "UPDATE ".$this->table_games
			." SET `curr_player` = '".$curr_player    // ($player_no + 1) when Join player
			."', `max_player_no` = '".$max_player_no // $player_no when Join player
			."' WHERE `id`='".$game_id
			."'";
		if (!$result = $mysqli->query($query)) {echo "Error $table ".$mysqli->errno." ".$mysqli->error; /*exit;*/}
	}

/* players */
	function mysql_insert_player($player, $player_no, $game_id){
		$player_serialized = str_replace('"', '~', serialize($player));
		$mysqli = $this->connect_my_db($this->host, $this->user, $this->password, $this->data_base);
		$query  = "INSERT ".$this->table_game_players
			." SET `game` = '".$game_id
			."', `no` = '".$player_no
			//."', `nickname` = '".$player->nickname
			."', `obj` = '".$player_serialized."'";
		if (!$result = $mysqli->query($query)) {echo "Error $table ".$mysqli->errno." ".$mysqli->error; /*exit;*/}
	}

	function mysql_update_player($player, $player_no, $game_id){
		$mysqli = $this->connect_my_db($this->host, $this->user, $this->password, $this->data_base);
		$player_serialized = str_replace('"', '~', serialize($player));
		$query  = "UPDATE ".$this->table_game_players
			." SET `obj` = '".$player_serialized
			."' WHERE `game`='".$game_id
			."' AND `no` = '".$player_no
			."'";
		if (!$result = $mysqli->query($query)) {echo "Error $table ".$mysqli->errno." ".$mysqli->error; /*exit;*/}
	}

	function mysql_get_player($game_id, $player_no){
		$mysqli = $this->connect_my_db($this->host, $this->user, $this->password, $this->data_base);
		$query = "SELECT obj FROM ".$this->table_game_players." WHERE `game`='".$game_id."' AND `no`='".$player_no."'";
		if (!$result = $mysqli->query($query)) {echo "Error $table ".$mysqli->errno." ".$mysqli->error; /*exit;*/}
		$row=$result->fetch_assoc();
		$player_serialize = $row["obj"]; 
		$player = unserialize(str_replace('~', '"', $player_serialize));
		return $player;
	}

	function mysql_get_players_max_no($game_id){
		$mysqli = $this->connect_my_db($this->host, $this->user, $this->password, $this->data_base);
		$query = "SELECT MAX(no) AS 'maxno' FROM ".$this->table_game_players." WHERE `game`='".$game_id."' ";
		if (!$result = $mysqli->query($query)) {echo "Error $table ".$mysqli->errno." ".$mysqli->error; /*exit;*/}
		$row=$result->fetch_assoc();
		return $row["maxno"];
	}

/* turns */
	function mysql_insert_turn($currTurn, $game_id){
		$currTurn_serialized = str_replace('"', '~', serialize($currTurn));
		$mysqli = $this->connect_my_db($this->host, $this->user, $this->password, $this->data_base);
		$query  = "INSERT ".$this->table_game_turns
			." SET `game` = '".$game_id
			."', `no` = '".$currTurn->no
			."', `obj` = '".$currTurn_serialized."'";
		if (!$result = $mysqli->query($query)) {echo "Error $table ".$mysqli->errno." ".$mysqli->error;}
	}

	function mysql_insert_update_turn($currTurn, $newTurn, $game_id){
		$mysqli = $this->connect_my_db($this->host, $this->user, $this->password, $this->data_base);
		$currTurn_serialized = str_replace('"', '~', serialize($currTurn));
		if ($newTurn){
			$query  = "INSERT ".$this->table_game_turns
				." SET `game` = '".$game_id
				."', `no` = '".$currTurn->no
				."', `obj` = '".$currTurn_serialized
				."'";
		} else {
			$query  = "UPDATE ".$this->table_game_turns
				." SET `obj` = '".$currTurn_serialized
				."' WHERE `game`='".$game_id
				."' AND `no` = '".$currTurn->no
				."'";
		}
		if (!$result = $mysqli->query($query)) {echo "Error $table ".$mysqli->errno." ".$mysqli->error; /*exit;*/}
	}

	function mysql_get_last_turns_table($game_id){
		$mysqli = $this->connect_my_db($this->host, $this->user, $this->password, $this->data_base);
		$query = "SELECT obj FROM ".$this->table_game_turns." WHERE `game`='".$game_id."' ORDER BY no DESC LIMIT ".$this->last_turns_no; 
		if (!$result = $mysqli->query($query)) {echo "Error $table ".$mysqli->errno." ".$mysqli->error; /*exit;*/}
		return $result;
	}

	function mysql_get_turns_max_no($game_id){
		$mysqli = $this->connect_my_db($this->host, $this->user, $this->password, $this->data_base);
		$query = "SELECT MAX(no) AS 'maxno' FROM ".$this->table_game_turns." WHERE `game`='".$game_id."' ";
		if (!$result = $mysqli->query($query)) {echo "Error $table ".$mysqli->errno." ".$mysqli->error; /*exit;*/}
		$row=$result->fetch_assoc();
		return $row["maxno"];
	}

	function status_refresh($game_id, $player_no, $request_answer){
		global $form_lab_start_game, $form_lab_refresh, $form_turn_buttons;
		$answer = array();
		$lab = $this->mysql_get_game($game_id, $new_game, $max_player_no, $curr_player);
		$player = $this->mysql_get_player($game_id, $player_no);
		$turnResult = $this->mysql_get_last_turns_table($game_id);

		if ($lab->showMap){ $lab->labPrint(); }
		if ($new_game){ // must be commented! use only for debugging
			if ($player_no == 1){
				require($form_lab_start_game);
			} else {
				require($form_lab_refresh);
			}
		} else {
			require($form_turn_buttons);
		}
		if ($lab->showMap){ $player->positionPrint(); }
		echo $request_answer."<br />";
		$player->inventoryPrint();
		$this->lastTurnsPrint($turnResult);
	}
	
/* API games */
	function games_api_to_join(){
		$games = array();
		$mysqli = $this->connect_my_db($this->host, $this->user, $this->password, $this->data_base);
		$query = "SELECT id, initiator, started FROM ".$this->table_games." WHERE `new_game` = true ORDER BY started DESC";
		if (!$result = $mysqli->query($query)) {echo "Error $table ".$mysqli->errno." ".$mysqli->error;}
		while($row=$result->fetch_assoc()){
			$games[] = array(
				"id" => $row["id"]
				, "initiator" => $row["initiator"]
				, "started" => date('d.m.Y H:i', strtotime($row["started"]))
			);
		}
		echo json_encode($games);
	}

	function lastTurnsArray($turnResult){ // $turnResult - db result table
		$turns = array();
		while ($row = $turnResult->fetch_assoc()) {
			$currTurn_serialize = $row["obj"];
			$currTurn = unserialize(str_replace('~', '"', $currTurn_serialize));
			$turns[] = $currTurn->toArray(); 
		}
		return $turns;
	}

	function status_api_refresh($game_id, $player_no, $request_answer){
		global $form_lab_start_game, $form_lab_refresh, $form_turn_buttons;
		$answer = array();
		$lab = $this->mysql_get_game($game_id, $new_game, $max_player_no, $curr_player);
		$player = $this->mysql_get_player($game_id, $player_no);
		$turnResult = $this->mysql_get_last_turns_table($game_id);

		// if ($lab->showMap){ $lab->labPrint(); }
		/*
		if ($new_game){ // must be commented! use only for debugging
			if ($player_no == 1){
				require($form_lab_start_game);
			} else {
				require($form_lab_refresh);
			}
		} else {
			require($form_turn_buttons);
		}*/
		if ($new_game){ 
			if ($player_no == 1){
				$form = "start";
			} else {
				$form = "refresh";
			}
		} else {
			$form = "turn";
		}
		// if ($lab->showMap){ $player->positionPrint(); }
		// $player->inventoryPrint();
		$answer[] = array("answer" => $request_answer, "form" => $form);
		$answer[] = array("inventory" => $player->inventoryToJSON());
		//$this->lastTurnsPrint($turnResult);
		$answer[] = array("turns" => $this->lastTurnsArray($turnResult));
		echo json_encode($answer);
	}
	
}
