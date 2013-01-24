<?php

require_once(dirname(__FILE__).'/config/config.php');

function init() {
	sanitizeParams();
   
	$db = db_connect();
	$action = readAction();

	if($action == 'startGameSession') {
		$mturk_id = $_GET['mturk_id'] or die("No mturk_id provided!");
   	startGameSession($db, $mturk_id);
	}

	if($action == 'startGameRun') {
		$session_id = $_GET['session_id'] or die("No session_id provided!");
		startGameRun($db, $session_id);

	}

	if($action == 'postFlip') {
		$run_id = $_GET['run_id'] or die("No run_id provided!");
   	postFlip($db, $run_id);
	}
}

function getSessionId($db, $mturk_id) {
	//this should really get his cookie, not just the most recent one
	$q = "SELECT id FROM gameSession WHERE mturk_id='$mturk_id' ORDER BY started DESC LIMIT 1";
	$data = runQuery($db, $q);
	$id = $data[0]['id'];
	return intval($id);
}

function getRunId($db, $session_id) {
	//this should really get his cookie, not just the most recent one
	$q = "SELECT id FROM gameRun WHERE session_id='$session_id' ORDER BY started DESC LIMIT 1";
	$data = runQuery($db, $q);
	$id = $data[0]['id'];
	return intval($id);
}

function sanitizeParams() {
	$ints = array('session_id', 'run_id'); 
	foreach($GET as $k=>$v) {
   	$_GET[$k] = htmlentities($v, ENT_QUOTES);
		if(in_array($k, $ints)) {
			$_GET[$k] = intval($v);
		}
		if($k == 'mturk_id') {
			$id = preg_replace('/[^a-zA-Z0-9]/','', $id);
		}
	}
}

function startGameSession($db, $mturk_id) {
	$q = "INSERT INTO gameSession (mturk_id, started) VALUES ('$mturk_id', now())";
	runQuery($db, $q, false);
	$session_id = getSessionId($db, $mturk_id);

	logMsg($session_id);
}

function startGameRun($db, $session_id) {
	$q = "INSERT INTO gameRun (session_id, started) VALUES ('$session_id', now())";
	runQuery($db, $q, false);
	$game_id = getRunId($db, $session_id);

	logMsg($game_id);
}

function postFlip($db, $run_id) {
	$bs = 0;
	$rs = 0;                  
	$flips = 'Invalid';
	if(array_key_exists('bs', $_GET)) {
		$bs = $_GET['bs'];
	}
	if(array_key_exists('rs', $_GET)) {
		$rs = $_GET['rs'];
	}
	if(array_key_exists('flips', $_GET)) {
		$flips = $_GET['flips'];
	}
	$q = "DELETE FROM gameResult WHERE run_id = $run_id";
	runQuery($db, $q, false);

	$q = "INSERT INTO gameResult (run_id, flips, blue_score, red_score, updated) VALUES ($run_id, '$flips', '$bs', '$rs', now())";

	runQuery($db, $q, false);
	logMsg("Inserted a flip row with bs=$bs, rs=$rs");
}

function logMsg($msg) {
	die("$msg");

}

function readAction() {
	$goodAction = array('startGameSession', 'startGameRun', 'postFlip');
	$action = $_GET['action'];
	if(!in_array($action, $goodAction)) {
   	die("Invalid action!");
	}
	return $action;
}

function db_connect() {
	try {
		$db = new PDO('mysql:host=localhost;dbname=flipIt;', DB_USERNAME, DB_PASSWORD );
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}
	catch(PDOException $e) {
   	print $e->getMessage();
	}
	return $db;
}

function runQuery($db, $q, $return = true) {
	try {
		$results = $db->query($q);         
		if($return) {
			return $results->fetchAll(PDO::FETCH_ASSOC);
		}
	}
	catch(PDOException $e) {
		print "$q<br>\n";
   	print $e->getMessage();
		die;
	}
}

init();
