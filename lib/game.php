<?php

function show_status() {
	
	global $conn;
	$conn = dbconnect();
	$sql = 'select * from game_status';
	$st = $conn->prepare($sql);

	$st->execute();
	$res = $st->get_result();

	header('Content-type: application/json');
	print json_encode($res->fetch_all(MYSQLI_ASSOC), JSON_PRETTY_PRINT);

}

?>