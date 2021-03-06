<?php
error_reporting(E_ALL);
ini_set('display_errors', TRUE);

function show_status() {
	
	global $conn;
	check_abort();
	$conn = dbconnect();
	$sql = 'select * from game_status';
	$st = $conn->prepare($sql);
	$st->execute();
	$res = $st->get_result();
	header('Content-type: application/json');
	print json_encode($res->fetch_all(MYSQLI_ASSOC), JSON_PRETTY_PRINT);

}

function check_abort() {
	global $conn;
	$conn = dbconnect();	
	$sql = "update game_status set status='aborded', result=if(p_turn='R','Y','R'),p_turn=null where p_turn is not null and last_change<(now()-INTERVAL 5 MINUTE) and status='started'";
	$st = $conn->prepare($sql);
	$r = $st->execute();
}

function read_status() {
	global $conn;
	$conn = dbconnect();
	$sql = 'select * from game_status';
	$st = $conn->prepare($sql);
	$st->execute();
	$res = $st->get_result();
	$status = $res->fetch_assoc();
	return($status);
}

function update_game_status() {
	global $conn;
	$conn = dbconnect();	
	$sql = 'select * from game_status';
	$st = $conn->prepare($sql);
	$st->execute();
	$res = $st->get_result();
	$status = $res->fetch_assoc();
	
	
	$new_status=null;
	$new_turn=null;
	
	$st3=$conn->prepare('select count(*) as aborted from players WHERE last_action< (NOW() - INTERVAL 5 MINUTE)');
	$st3->execute();
	$res3 = $st3->get_result();
	$aborted = $res3->fetch_assoc()['aborted'];
	if($aborted>0) {
		$sql = "UPDATE players SET username=NULL, token=NULL WHERE last_action< (NOW() - INTERVAL 5 MINUTE)";
		$st2 = $conn->prepare($sql);
		$st2->execute();
		if($status['status']=='started') {
			$new_status='aborded';
		}
	}

	
	$sql = 'select count(*) as c from players where username is not null';
	$st = $conn->prepare($sql);
	$st->execute();
	$res = $st->get_result();
	$active_players = $res->fetch_assoc()['c'];
	
	
	switch($active_players) {
		case 0: $new_status='not active'; break;
		case 1: $new_status='initialized'; break;
		case 2: $new_status='started'; 
				if($status['p_turn']==null) {
					$new_turn='R'; // It was not started before...
				}
				break;
	}

	$sql = 'update game_status set status=?, p_turn=?';
	$st = $conn->prepare($sql);
	$st->bind_param('ss',$new_status,$new_turn);
	$st->execute();	
}
?>