<?php

require_once(dirname(__FILE__).'/config/config.php');

function init() {
	sanitizeParams();
   
	$db = db_connect();
	$action = $_REQUEST['action'];

	switch($action) {
		case 'startGameSession':
			startGameSession($db, getKeyIfPresent('mturk_id'));
		break;

		case 'startGameRun':
			startGameRun($db, getKeyIfPresent('session_id'),getKeyIfPresent('tick'), getKeyIfPresent('anchor'));
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

		case 'connect':

	}
}

function getOldTreatment($db, $session_id) {
	$q = "SELECT treatment_id as id, opponent_description as message FROM gameSession, treatment WHERE treatment.id = treatment_id AND gameSession.id = $session_id";

	$data = runQuery($db, $q);

	return $data[0];
}

function getNewTreatment($db, $balanced = true) {
	$q = "SELECT treatment.id, count(treatment_id) as count, opponent_description as message FROM treatment LEFT JOIN gameSession on treatment.id = treatment_id GROUP by treatment.id ORDER BY count(treatment_id) ASC";
	$data = runQuery($db, $q);

	if($balanced) {
		$min_treatments = array();
		$min_count = $data[0]['count'];

		foreach($data as $row) {
			if($row['count'] != $min_count) {
				break;
			}
			$min_treatments[] = $row;
		}
		$data = $min_treatments;
	}
	$rand_index = array_rand($data);
	return $data[$rand_index];
}

function getKeyIfPresent($key) {
	if(!array_key_exists($key, $_REQUEST) || !$_REQUEST[$key]) {
   	logMessageAndDie("No $key provided!");
	}
	else {
   	return $_REQUEST[$key];
	}
}

function getSessionStats($db, $session_id) {
	$stats = array();
	
	$q = "SELECT count(id) FROM gameRun WHERE session_id=$session_id";
	$data = runQuery($db, $q);
	$count = $data[0]['count(id)'];

	$stats['num_runs_played'] = intval($count);

  	if($stats['num_runs_played']) {
   	$stats['bonus'] = getBonus($db, $session_id);
	}
	
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

function sanitizeParams($allInts = false) {
	$ints = array('session_id', 'run_id', 'tick', 'anchor'); 
   $alphanums = array('mturk_id', 'action');

	foreach($_REQUEST as $k=>$v) {
   	$_REQUEST[$k] = htmlentities($v, ENT_QUOTES);

      if($allInts) {
			if(is_array($allInts)) {
				if(!in_array($k, $allInts)) {
					$_REQUEST[$k] = intval($v);
				}
			}
			else {
				$id = preg_replace('/[^a-zA-Z0-9 \s]/','', $id);
			}
		}

		if(in_array($k, $ints)) {
			$_REQUEST[$k] = intval($v);
		}

		if(in_array($k, $alphanums)) {
			$REQUEST[$k] = preg_replace('/[^a-zA-Z0-9 \s]/','', $_REQUEST[$k]);
		}
	}
}

function startGameSession($db, $mturk_id, $survey_blob, $treatment_id, $die = true) {
	$q = "INSERT INTO gameSession (mturk_id, started, survey_blob, treatment_id) VALUES ('$mturk_id', now(), '$survey_blob', $treatment_id)";
	runQuery($db, $q, false);
	$session_id = getMostRecentSessionId($db, $mturk_id);

	if($die) {
		logMessageAndDie($session_id);
	}
	return $session_id;
}

function startGameRun($db, $session_id, $tick, $anchor) {
	$q = "INSERT INTO gameRun (session_id, started, tick, anchor) VALUES ('$session_id', now(), $tick, $anchor)";
	runQuery($db, $q, false);
	$game_id = getRunId($db, $session_id);

	logMessageAndDie($game_id);
}

function getBonus($db, $session_id) {
	$q = "SELECT amount FROM bonus WHERE session_id=$session_id";
   $data = runQuery($db, $q);
	if(count($data) > 0) {
   	return $data[0]['amount'];
	}

	$q = "SELECT session_id, run_id, blue_score, treatment_id, mturk_id FROM gameResult, gameRun, gameSession WHERE gameRun.id = gameResult.run_id AND gameRun.session_id = gameSession.id AND session_id=$session_id";	
	$data = runQuery($db, $q);

	$total_delta = 0;
	$games_won = 0;

   for($i=0;$i<count($data);$i++) {
		//don't include score from practice rounds
		if($i<NUM_PRACTICE_RUNS) {
      	continue;
		}

		$row = $data[$i];

		$bs = json_decode($row['blue_score']);
		$bs = $bs[count($bs)-1];

		$rs = json_decode($row['red_score']);
		$rs = $bs[count($rs)-1];

		$total_delta += $bs-$rs;
		if($bs > $rs) {
      	$games_won++;
		}
	}
	$bonus = $total_delta * POINTS_EXCHANGE_RATE; 
	$bonus = max(0, $bonus);
	$bonus = sprintf("$%0.2f", $bonus);

	recordbonus($db, $bonus, $mturk_id, $session_id, $data[0]['treatment_id']);

	return $bonus;
}

function recordbonus($db, $bonus, $mturk_id, $session_id) {
	$q = "INSERT INTO bonus (mturk_id, amount, session_id, finished) VALUES ('$mturk_id', '$bonus', $session_id, now())";

	runQuery($db, $q, false);
}

function postFlip($db, $run_id) {
	$bs = 0;
	$rs = 0;                  
	$flips = 'Invalid';
	if(array_key_exists('bs', $_REQUEST)) {
		$bs = $_REQUEST['bs'];
	}
	if(array_key_exists('rs', $_REQUEST)) {
		$rs = $_REQUEST['rs'];
	}
	if(array_key_exists('flips', $_REQUEST)) {
		$flips = $_REQUEST['flips'];
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
