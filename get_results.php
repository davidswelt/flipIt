<?php
date_default_timezone_set('America/New_York');
$date = date('m-d-Y', time());

require_once(dirname(__FILE__).'/config/config.php');

header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=data$date.csv");
header("Pragma: no-cache");
header("Expires: 0");

function init() {
	$db = db_connect();
	get_entry_data($db);
}

function db_connect() {
	try {
                $dbn = 'mysql:host=localhost;dbname=' . DB_NAME;
                $db = new PDO($dbn . ';', DB_USERNAME, DB_PASSWORD);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}
	catch(PDOException $e) {
          	print $e->getMessage();
	}
	return $db;
}

function get_entry_data($db) {
	$q = "SELECT bonus.amount as total_bonus, bonus.session_id, gameRun.id as run_id, bonus.info_treatment_id, gameRun.tick, gameRun.anchor, flips, blue_score, red_score, survey_blob, bonus.visual_treatment_id, paid FROM bonus, gameRun, gameSession, gameResult WHERE gameRun.session_id = bonus.session_id AND gameSession.id = bonus.session_id AND gameResult.run_id = gameRun.id";

	try {
		$results = $db->query($q);
		$data = $results->fetchAll(PDO::FETCH_ASSOC);
	}
	catch(PDOException $e) {
   	print $e->getMessage();
	}
 	
	//write the headers
	
	$datastr = implode(',', array_keys($data[0]));
	$datastr .= "\n";

	//now write to the csv
	foreach($data as $row) {
		foreach(array_values($row) as $val) {
			if($val[0] != '"')  
				$datastr .= '"';    
			$datastr .= htmlentities($val);
			$datastr .= '"';
			$datastr .= ',';
		}
		$datastr .= "\n";
	}
	print $datastr;
}

init();
