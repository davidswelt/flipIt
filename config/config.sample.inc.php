<?php

//Uncomment to turn on demo mode
//define('DEMO','demo');             

if(defined('DEMO')) {
	define('MAX_RUNS_PER_SESSION', 600);
	define('NUM_PRACTICE_RUNS',-1);

}
else { //not demo
	define('MAX_RUNS_PER_SESSION', 6);
	define('NUM_PRACTICE_RUNS', 1);
}
    
define('COOKIE_NUM', 1);

//Monetary value of 1 point
//Currently set at 1/100 of a cent, or 100 points = 1 cent
define('POINTS_EXCHANGE_RATE', '0.0001');  

define('DB_NAME','flipIt');
define('DB_USERNAME','sample');
define('DB_PASSWORD','pass');



