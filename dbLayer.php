<?php

require_once(dirname(__FILE__).'/config/config.php');

function init() {
	sanitizeParams();
   
	$db = db_connect();
	$action = $_REQUEST['action'];

	switch($action) {
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
           
function getOldTreatment($db, $treatment_type, $session_id) {
	$tt = $treatment_type . "_treatment";
	$field = '';
   if($treatment_type == 'info') {
   	$field = 'opponent_description';
	}
	else if($treatment_type == 'visual') {
   	$field = 'feedback_type';
	}
 
	$q = "SELECT $tt"."_id as id, $field FROM gameSession, $tt WHERE $tt.id = $tt"."_id AND gameSession.id = $session_id";

	$data = runQuery($db, $q);

	return $data[0];
}           

function getMTurkIdFromDb($db, $session_id) {
	$q = "SELECT mturk_id FROM gameSession WHERE gameSession.id = $session_id";

	$data = runQuery($db, $q);
   $mturk_id = $data[0]['mturk_id'];

	return $mturk_id;
}

function getMTurkIdFromBlob($blob) {
	$blob = json_decode($blob, true);

	return $blob['mturk_id'];
}

function getNewTreatment($db, $treatment_type, $balanced = true) {
	$tt = $treatment_type . "_treatment";
	$field = '';
   if($treatment_type == 'info') {
   	$field = 'opponent_description';
	}
	else if($treatment_type == 'visual') {
   	$field = 'feedback_type';
	}

	$q = "SELECT $tt.id, $field, count($tt"."_id) as count FROM $tt LEFT JOIN gameSession ON $tt.id = $tt"."_id WHERE $tt.active=1 GROUP by $tt.id ORDER BY count($tt"."_id) ASC";
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

	
	$stats['session_id'] = intval($session_id);
	$stats['num_runs_remaining'] = MAX_RUNS_PER_SESSION - $count;
	$stats['num_practice_runs_remaining'] = NUM_PRACTICE_RUNS - $count;

  	if($stats['num_runs_remaining'] == 0) {
   	$stats['bonus'] = getBonus($db, $session_id);
	}                                   

	$stats['NUM_PRACTICE_RUNS'] = NUM_PRACTICE_RUNS;

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
	$data = @runQuery($db, $q);
	if(!$data) return 0;
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
				$id = preg_replace('/[^a-zA-Z0-9 ]/','', $id);
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

function startGameSession($db, $mturk_id, $survey_blob, $info_treatment_id, $visual_treatment_id, $die = true) {
	$q = "INSERT INTO gameSession (mturk_id, started, survey_blob, info_treatment_id, visual_treatment_id) VALUES ('$mturk_id', now(), '$survey_blob', $info_treatment_id, $visual_treatment_id)";
	runQuery($db, $q, false);
	$session_id = getMostRecentSessionId($db, $mturk_id);

	if($die) {
		logMessageAndDie($session_id);
	}
	return $session_id;
}

function isValidSession($db, $session_id) {
	$q = "SELECT * FROM gameSession WHERE id=$session_id";
	$data = runQuery($db, $q);
	return count($data) > 0;
}

function startGameRun($db, $session_id, $tick, $anchor) {
	$q = "INSERT INTO gameRun (session_id, started, tick, anchor) VALUES ('$session_id', now(), $tick, $anchor)";
	runQuery($db, $q, false);
	$game_id = getRunId($db, $session_id);

	logMessageAndDie($game_id);
}

function getBonus($db, $session_id) {
	$q = "SELECT blue_score, red_score, info_treatment_id, visual_treatment_id, survey_blob FROM gameResult, gameRun, gameSession WHERE gameRun.id = gameResult.run_id AND gameRun.session_id = gameSession.id AND session_id=$session_id";	
	$data = runQuery($db, $q);

	if(!$data) {
   	return 'NULL';
	}

	$total_delta = 0;
	$deltas = array();
	
   for($i=0;$i<count($data);$i++) {
		//don't include score from practice rounds
		if($i<NUM_PRACTICE_RUNS) {
      	continue;
		}

		$row = $data[$i];

		$bs = json_decode($row['blue_score']);
		$bs = $bs[count($bs)-1];

		$rs = json_decode($row['red_score']);
		$rs = $rs[count($rs)-1];

		array_push($deltas, $bs-$rs);
	}
   
	$deltas = array_map(function($a) {
		return max(0, 1000+$a);
	}, $deltas);

   

	foreach($deltas as $delta) {
   	$total_delta += $delta;
	}

	$bonus = $total_delta * POINTS_EXCHANGE_RATE; 
	$bonus = max(0, $bonus);
	$bonus = sprintf("$%0.2f", $bonus);

	$json_blob = $data[0]['survey_blob'];
	$json_blob = json_decode($json_blob, true);

	$hit_id = $json_blob['hit_id'];
	$mturk_id = $json_blob['mturk_id'];

	recordbonus($db, $mturk_id, $hit_id, $bonus, $session_id, $data[0]['info_treatment_id'], $data[0]['visual_treatment_id']);

	return $bonus;
}

function recordbonus($db, $mturk_id, $hit_id, $bonus, $session_id, $info_treatment_id, $visual_treatment_id) {
	$q = "DELETE FROM bonus WHERE session_id=$session_id";
   $data = runQuery($db, $q, false);

	$q = "INSERT INTO bonus (mturk_id, hit_id, amount, session_id, info_treatment_id, visual_treatment_id, finished, paid) VALUES ('$mturk_id', '$hit_id', '$bonus', $session_id, $info_treatment_id, $visual_treatment_id, now(), 0)";

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
		$dbname = 'flipIt';
		if(defined('DEMO')) {
			$dbname = 'flipIt_demo';
		}
		$db = new PDO("mysql:host=localhost;dbname=$dbname;", DB_USERNAME, DB_PASSWORD );
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
