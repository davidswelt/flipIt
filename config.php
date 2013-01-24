<?php

if(file_exists('config.inc.php')) {
	require_once('config.inc.php');
}
else {
	require_once('config.sample.inc.php');
}
