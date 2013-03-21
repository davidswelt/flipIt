<?php

$prefix = dirname(__FILE__);

if(file_exists("$prefix/config.inc.php")) {
	require_once("$prefix/config.inc.php");
}
else {
	require_once("$prefix/config.sample.inc.php");
}
