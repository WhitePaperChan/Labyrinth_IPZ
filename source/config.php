<?php
	/* Constants and most comon functions for "labth" project */ 
$host="localhost";
$data_base="labyrinth";
$user="root";  // for development on local cerver only
$password=""; // for development on local cerver only

function connect_my_db($host,$user,$password,$data_base){
	$mysqli = new mysqli($host,$user,$password,$data_base);
	if ($mysqli->connect_errno){
		echo "Error: Failed to make a MySQL connection, here is why: \n";
		echo "Errno: " . $mysqli->connect_errno . "\n";
		echo "Error: " . $mysqli->connect_error . "\n";
	}
	return $mysqli;
}

