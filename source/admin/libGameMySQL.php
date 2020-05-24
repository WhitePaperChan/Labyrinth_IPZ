<?php
/**
* Class for work with MySQL
* @property string $table_games name of the games table
* @property string $table_game_players name of the players table
* @property string $table_game_turns name of the turns table
* @property int $last_turns_no number of last turns to show
* @property string $host name of the host
* @property string $data_base name of the database
* @property string $user username (!!!only for development on local server!!!)
* @property string $password password (!!!only for development on local server!!!)
*/
class LabMySQL {
	public $table_games = "games";
	public $table_game_players = "game_players";
	public $table_game_turns = "game_turns";
	public $last_turns_no = 5;

	protected $host="localhost";
	protected $data_base="labyrinth";
	protected $user="root";  // "root" for development on local server only
	protected $password=""; // "" for development on local server only
	/**
	* Connects to the database
	* @param string $host name of the host
	* @param string $data_base name of the database
	* @param string $user username (!!!only for development on local server!!!)
	* @param string $password password (!!!only for development on local server!!!)
	* @return mysqli result of the connection
	*/
	function connect_my_db($host,$user,$password,$data_base){
		$mysqli = new mysqli($host,$user,$password,$data_base);
		if ($mysqli->connect_errno){
			echo "Error: Failed to make a MySQL connection, here is why: \n";
			echo "Errno: " . $mysqli->connect_errno . "\n";
			echo "Error: " . $mysqli->connect_error . "\n";
		}
		return $mysqli;
	}
	/**
	* Prints last turns
	* @param object $turnResult table with last turns
	*/
	function lastTurnsPrint($turnResult){ // $turnResult - db result table
		while ($row = $turnResult->fetch_assoc()) {
			$currTurn_serialize = $row["obj"];
			$currTurn = unserialize(str_replace('~', '"', $currTurn_serialize));
			echo $currTurn->toStr(); 
		}
	}
	
/* games */
	/**
	* Gets games to join
	* @result object tables of games to join
	*/
	function games_to_join(){
		$mysqli = $this->connect_my_db($this->host, $this->user, $this->password, $this->data_base);
		$query = "SELECT id, initiator, started FROM ".$this->table_games." WHERE `new_game` = true ORDER BY started DESC";
		if (!$result = $mysqli->query($query)) {echo "Error $table ".$mysqli->errno." ".$mysqli->error;}
		return $result;
	}

	/** 
	* Inserts game to database and return game id 
	* @param Labth $lab game labyrinth
	* @param string $initiator nickname of the initiator
	* @return int id of the game
	*/
	public function mysql_insert_game($lab, $initiator){ 
		$lab_serialized = str_replace('"', '~', serialize($lab));
		$mysqli = $this->connect_my_db($this->host, $this->user, $this->password, $this->data_base);
		$query  = "INSERT ".$this->table_games
			." SET `new_game` = true, `finished_game` = false, `labth` = '".$lab_serialized
			."', `curr_player` = '1', `max_player_no` = '1', `initiator` = '".$initiator
			."'";
		if (!$result = $mysqli->query($query)) {echo "Error $table ".$mysqli->errno." ".$mysqli->error; /*exit;*/}
		return $mysqli->insert_id;
	}
	/**
	* Gets game by its id
	* @param int $game_id id of the game
	* @param boolean $new_game is this game new
	* @param int $max_player_no number of players
	* @param int $curr_player number of current player
	* @result Labth labyrinth of the game
	*/
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
	/**
	* Updates game when it starts
	* @param int $game_id id of the game
	* @param int $player_no number of the current player
	*/
	function mysql_update_game_on_start($game_id, $player_no){
		$mysqli = $this->connect_my_db($this->host, $this->user, $this->password, $this->data_base);
		$query  = "UPDATE ".$this->table_games
			." SET `new_game` = false, `curr_player` = '".$player_no
			."' WHERE `id`='".$game_id."'";
		if (!$result = $mysqli->query($query)) {echo "Error $table ".$mysqli->errno." ".$mysqli->error;}
	}
	/**
	* Updates current player
	* @param Labth labyrinth of the game
	* @param int $game_id id of the game
	* @param int $player_no number of the current player
	* @param int $max_player_no number of players
	* @param int $curr_player number of current player
	* @param boolean $finished is turn finished
	*/
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
	/**
	* Gets info about the game (game id, iniator, date) in string form
	* @param int $game_id id of the game
	* @return string info about the game
	*/
	function mysql_get_game_meta_str($game_id){
		$mysqli = $this->connect_my_db($this->host, $this->user, $this->password, $this->data_base);
		$query = "SELECT id, initiator, started FROM ".$this->table_games." WHERE `id`='".$game_id."'";
		if (!$result = $mysqli->query($query)) {echo "Error $table ".$mysqli->errno." ".$mysqli->error; /*exit;*/}
		$row=$result->fetch_assoc();
		return "<div>Game <strong>".$row["id"]."</strong> started by <strong>".$row["initiator"]."</strong> at <strong>".date('d.m.Y H:i', strtotime($row["started"]))."</strong></div><br />";
	}
	/**
	* Updates max number of players
	* @param int $game_id id of the game
	* @param int $curr_player number of current player
	* @param int $max_player_no number of players
	*/
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

	/**
	* Inserts player in database
	* @param LabPlayer $player player to insert
	* @param int $player_no number of the player
	* @param int $game_id number of the game
	*/
	function mysql_insert_player($player, $player_no, $game_id){
		$player_serialized = str_replace('"', '~', serialize($player));
		$mysqli = $this->connect_my_db($this->host, $this->user, $this->password, $this->data_base);
		$query  = "INSERT ".$this->table_game_players
			." SET `game` = '".$game_id
			."', `no` = '".$player_no
			."', `nickname` = '".$player->nickname
			."', `obj` = '".$player_serialized."'";
		if (!$result = $mysqli->query($query)) {echo "Error $table ".$mysqli->errno." ".$mysqli->error; /*exit;*/}
	}

	/**
	* Updates player
	* @param LabPlayer $player player to insert
	* @param int $player_no number of the player
	* @param int $game_id number of the game
	*/
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
	/**
	* Gets player object
	* @param int $game_id number of the game
	* @param int $player_no number of the player
	* @return LabPlayer object of player
	*/
	function mysql_get_player($game_id, $player_no){
		$mysqli = $this->connect_my_db($this->host, $this->user, $this->password, $this->data_base);
		$query = "SELECT obj FROM ".$this->table_game_players." WHERE `game`='".$game_id."' AND `no`='".$player_no."'";
		if (!$result = $mysqli->query($query)) {echo "Error $table ".$mysqli->errno." ".$mysqli->error; /*exit;*/}
		$row=$result->fetch_assoc();
		$player_serialize = $row["obj"]; 
		$player = unserialize(str_replace('~', '"', $player_serialize));
		return $player;
	}
	/**
	* Gets number of players of the game
	* @param int $game_id number of the game
	* @return object number of players
	*/
	function mysql_get_players_max_no($game_id){
		$mysqli = $this->connect_my_db($this->host, $this->user, $this->password, $this->data_base);
		$query = "SELECT MAX(no) AS 'maxno' FROM ".$this->table_game_players." WHERE `game`='".$game_id."' ";
		if (!$result = $mysqli->query($query)) {echo "Error $table ".$mysqli->errno." ".$mysqli->error; /*exit;*/}
		$row=$result->fetch_assoc();
		return $row["maxno"];
	}

/* turns */
	/**
	* Inserts turn in database
	* @param Turn $currTurn turn to add
	* @param int $game_id number of the game
	*/
	function mysql_insert_turn($currTurn, $game_id){
		$currTurn_serialized = str_replace('"', '~', serialize($currTurn));
		$mysqli = $this->connect_my_db($this->host, $this->user, $this->password, $this->data_base);
		$query  = "INSERT ".$this->table_game_turns
			." SET `game` = '".$game_id
			."', `no` = '".$currTurn->no
			."', `obj` = '".$currTurn_serialized."'";
		if (!$result = $mysqli->query($query)) {echo "Error $table ".$mysqli->errno." ".$mysqli->error;}
	}
	/**
	* Updates turn
	* @param Turn $currTurn turn to update
	* @param boolean $newTurn is turn new
	* @param int $game_id number of the game
	*/
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
	/**
	* Gets table of last turns
	* @param int $game_id number of the game
	* @result object table of last turns
	*/
	function mysql_get_last_turns_table($game_id){
		$mysqli = $this->connect_my_db($this->host, $this->user, $this->password, $this->data_base);
		$query = "SELECT obj FROM ".$this->table_game_turns." WHERE `game`='".$game_id."' ORDER BY no DESC LIMIT ".$this->last_turns_no; 
		if (!$result = $mysqli->query($query)) {echo "Error $table ".$mysqli->errno." ".$mysqli->error; /*exit;*/}
		return $result;
	}
	/**
	* Gets number of turns
	* @param int $game_id number of the game
	* @result object number of turns
	*/
	function mysql_get_turns_max_no($game_id){
		$mysqli = $this->connect_my_db($this->host, $this->user, $this->password, $this->data_base);
		$query = "SELECT MAX(no) AS 'maxno' FROM ".$this->table_game_turns." WHERE `game`='".$game_id."' ";
		if (!$result = $mysqli->query($query)) {echo "Error $table ".$mysqli->errno." ".$mysqli->error; /*exit;*/}
		$row=$result->fetch_assoc();
		return $row["maxno"];
	}
	/**
	* Refreshes inventory and last turns
	* @param int $game_id number of the game
	* @param int $player_no number of the player
	* @param string $request_answer string of refresh request
	*/
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
	/**
	* Gets games to join in JSON
	*/
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
	
}
