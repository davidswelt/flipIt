<?php

require_once(dirname(__FILE__).'/config/config.php');

function init() {
	sanitizeParams();
   
	$db = db_connect();
	$action = $_GET['action'];

	switch($action) {
		case 'startGameSession':
			startGameSession($db, getKeyIfPresent('mturk_id'));
		break;

		case 'startGameRun':
			startGameRun($db, getKeyIfPresent('session_id'));
		break;

		case 'postFlip':
			postFlip($db, getKeyIfPresent('run_id'));
		break;

		case 'getSessionStats':
			getSessionStats($db, getKeyIfPresent('session_id'));
		break;

		case '':
			logMessageAndDie("No action provided!");

		default:
			logMessageAndDie("Invalid action!");

	}
}

function getKeyIfPresent($key) {
	if(!array_key_exists($key, $_GET) || !$_GET[$key]) {
   	logMessageAndDie("No $key provided!");
	}
	else {
   	return $_GET[$key];
	}
}

function getSessionStats($db, $session_id) {
	$stats = array();
	
	$q = "SELECT count(id) FROM gameRun WHERE session_id=$session_id";
	$data = runQuery($db, $q);
	$count = $data[0]['count(id)'];

	$stats['num_runs_played'] = intval($count);
	
	$stats['session_id'] = intval($session_id);
	$stats['num_runs_remaining'] = MAX_RUNS_PER_SESSION - $count;
	logMessageAndDie(json_encode($stats));
}

function getMostRecentSessionId($db, $mturk_id) {
	$q = "SELECT id FROM gameSession WHERE mturk_id='$mturk_id' ORDER BY started DESC LIMIT 1";
	$data = runQuery($db, $q);
	$id = $data[0]['id'];
	return intval($id);
}

function getRunId($db, $session_id) {
	$q = "SELECT id FROM gameRun WHERE session_id='$session_id' ORDER BY started DESC LIMIT 1";
	$data = runQuery($db, $q);
	$id = $data[0]['id'];
	return intval($id);
}

function sanitizeParams() {
	$ints = array('session_id', 'run_id'); 
   $alphanums = array('mturk_id', 'action');

	foreach($GET as $k=>$v) {
   	$_GET[$k] = htmlentities($v, ENT_QUOTES);
		if(in_array($k, $ints)) {
			$_GET[$k] = intval($v);
		}

		if(in_array($k, $alphanums)) {
			$id = preg_replace('/[^a-zA-Z0-9 \s]/','', $id);
		}
	}
}

function startGameSession($db, $mturk_id) {
	$q = "INSERT INTO gameSession (mturk_id, started) VALUES ('$mturk_id', now())";
	runQuery($db, $q, false);
	$session_id = getMostRecentSessionId($db, $mturk_id);

	logMessageAndDie($session_id);
}

function startGameRun($db, $session_id) {
	$q = "INSERT INTO gameRun (session_id, started) VALUES ('$session_id', now())";
	runQuery($db, $q, false);
	$game_id = getRunId($db, $session_id);

	logMessageAndDie($game_id);
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
	logMessageAndDie("Inserted a flip row with bs=$bs, rs=$rs");
}

function logMessageAndDie($msg) {
	die("$msg");

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
