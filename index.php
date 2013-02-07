<html>
  <head>
	<style>
	body {
		position: absolute;
		left: 20%;
		top: 20%;
		padding-right:40%;
		margin: auto
	}
	ul, ol, li { 
		margin-top: 10px; 
	}
	</style>
 
<?php

	if(!array_key_exists('prevPage', $_REQUEST)) {
		displayConsent();
	}
	elseif($_REQUEST['prevPage'] == 'consentForm') {
		echo '</head><body>';
		displayInstructions();
	}
	elseif($_REQUEST['prevPage'] == 'instructions') {
		displaySurvey();
	}
	elseif($_REQUEST['prevPage'] == 'survey') {
		processSurvey();
	}

	function displayConsent() {
		include('consentForm.php');
		die;
	}

	function displayInstructions() {
   	include('instructions.php');
		die;
	}
	function displaySurvey() {
   	include('survey/survey.php');
		die;
	}
	function processSurvey() {
		$_REQUEST['action'] = 'connect';
		require_once(dirname(__FILE__).'/dbLayer.php');
		unset($_REQUEST['action']);
		unset($_REQUEST['prevPage']);

		sanitizeParams(array('mturk_id','forceNewSession', 'prevPage','action'));
		collapseScale('rps', 9);
		collapseScale('nfc', 9);
		ksort($_REQUEST);
		$survey_blob = json_encode($_REQUEST);

		//get treatment stuff here
		//save it and use it later
		createNewSession($survey_blob);

		//survey info entered into db here
	}
	
	function createNewSession($survey_blob) {
		if(array_key_exists('forceNewSession', $_REQUEST)) {
			$db = db_connect();
			$session_id = startGameSession($db, $mturk_id, $survey_blob, false);
			setcookie('session_id', $session_id);
		}
	}

	//this function should be moved somewhere better. too lazy...
	function collapseScale($type, $size) {
		if($type == 'rps' || $type == 'nfc') {
			
			if($type == 'rps') {
				$rev = array(1, 2, 3, 5);
			}
			else {
         	$rev = array(1, 3, 4, 5);
			}

			$rpstotal = 0;

      	foreach($_REQUEST as $k => $v) {
				if(preg_match('/^'.$type.'(\d)$/', $k, $matches)) {
					$rpsnum = intval($matches[1]);
					
					if(in_array($rpsnum, $rev)) {
               	$v = $size+1-$v;
					}

               $rpstotal += $v;
					unset($_REQUEST[$k]);
				}
			}
			$_REQUEST[$type.'total'] = $rpstotal;
		}
	}
?>
    <link rel="stylesheet" type="text/css" href="/flipIt/css/style.css" /> 

    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script type="text/javascript" src="/flipIt/js/flipit.js"></script>
    <script type="text/javascript" src="/flipIt/js/drawflipit.js"></script>
    <script type="text/javascript" src="/flipIt/js/jquery.cookie.js"></script>
    <script type="text/javascript" src="/flipIt/js/purl.js"></script>

    <!-- Start flipit.js once the document loads -->
    <script type="text/javascript">
	 	function testCookie() {
			testCookie.enabled = false;
			$.cookie('testCookie','1');
			var test = $.cookie('testCookie');
			if(test) {
         	testCookie.enabled = true;
			}
		}      

		function getSession(mturk_id) {
			var session_id = $.cookie('session_id');

			return session_id;
		}

	 	function getCleanFlipString(dirty) {
			clean = [];

         for(var i=0;i<dirty.length;i++) {
         	if(dirty[i] != null) {
					clean.push(i+":"+dirty[i]);
				}
			}

			return clean.join(",");
		}

		$(document).ready(function() {
			var url = location.toString();
			var mturk_id = $.url(url).param('mturk_id');
			testCookie();
			if(!testCookie.enabled) {
				document.write("Please enable cookies to perform this task.");
				return;
			}
			var session_id = getSession(mturk_id);
       
        var msPerTickSlow = 10;
        var numTicksLong = 2000;

        var config = new RenderSettings( $("#gameBoard") ); 

        config.fogOfWar = true;
        config.numTicks = numTicksLong;

        var gDraw = new FlipItRenderEngine( config );
        var sb = new ScoreBoard( $("#scoreBoard"), config.xColor, config.yColor );        

        var game = new FlipItGame( gDraw, 
          Players["humanPlayer"], Players["randomPlayer"], sb.update );

        game.newGame();
        sb.update(0, 0);
		  var run_id = null;
		  var started = false;
		  var blueScores = [];
		  var redScores = [];
		  var endMsgDisplayed = 'N/A';

		  setInterval(function() {
			  if(game.running) {
               $('#startBtn').attr('disabled','disabled')
					endMsgDisplayed = 'No';
			  }
			  else {
				  if(endMsgDisplayed == 'No') {
					 if(game.xScore - game.yScore > 0) {
						 endMsg = "Good job! You won!";
					 }
					 else {
						 endMsg = "You lost! Better luck next time!";
					 }

					 flips = getCleanFlipString(game.flips);

					 blueScores.push(game.xScore);
					 redScores.push(game.yScore);
					 
					 var myData = {action:'postFlip','run_id':run_id,'flips':flips,'bs':JSON.stringify(blueScores), 'rs':JSON.stringify(redScores)}; 
					  run_id = $.ajax({type:'GET', url:'dbLayer.php', data:myData, async:false}).responseText;
					  $('#flash').hide();
					 $('#flash').fadeIn('fast','linear');
					 $('#flash').html(endMsg);
					 $('#flash').css('text-align','center');
					 $('#flash').fadeOut(6000,'linear');
					 endMsgDisplayed = 'Yes';

					 var myData = {action:'getSessionStats','session_id':session_id}; 
					 var session_stats = $.ajax({type:'GET', url:'dbLayer.php', data:myData, async:false});
					 session_stats = JSON.parse(session_stats.responseText);
					 var num_runs_remaining = session_stats['num_runs_remaining'];
					 var num_runs_played = session_stats['num_runs_played'];

		  			 if(num_runs_remaining > 0) {
						 $('#startBtn').html('Start next game');
						 $('#statsBox').html(' ('+num_runs_remaining+' runs left, '+num_runs_played+' runs played)');
					 }
					 else {
                	$('body').hide();
						alert('You have played enough today. The window will now be closed');
						window.close();
					 }
				  }

				  $('#startBtn').removeAttr('disabled')
			  }
		  }, msPerTickSlow);

        //setup buttons
        $("#startBtn").click( function() {
				  if(!game.running) {
					  started = true;
					  sb.update(0, 0);
					  var myData = {action:'startGameRun','session_id':session_id};
					  run_id = $.ajax({type:'GET', url:'dbLayer.php', data:myData, async:false}).responseText;
					  game.start( msPerTickSlow, numTicksLong );
				}
        });

		  $("#flipBtn").click( function() {
			  if(game.running) {
				  game.defenderFlip();
				  flips = getCleanFlipString(game.flips);
				  sb.update( game.xScore, game.yScore );

				  blueScores.push(game.xScore);
				  redScores.push(game.yScore);
				  
				  var myData = {action:'postFlip','run_id':run_id,'flips':flips,'bs':JSON.stringify(blueScores), 'rs':JSON.stringify(redScores)};
				  $.ajax({type:'GET', url:'dbLayer.php', data:myData, async:false}).responseText;
			  }
		  });
      });
    </script>

	 <style>
	 div#flash {
   	background: #6d7b8d;
		border: 1px solid black;
		padding: 3px 3px 3px 3px;
	 }

	 </style>
  </head>

  <title>FlipIt - The Game of Stealthy Takeover</title>
  <body>
	 <div id='not_rules_panel'>
    <div id="top_panel">
      <h1 id="title">FlipIt - The Game of Stealthy Takeover</h1>
    </div>
	  <div id='flash' style='display:none'></div>

    <div id="scoreBoard"></div>
    
    <canvas id="gameBoard" width="800" height="150"><h1>Canvas element not supported by your browser. Please use an HTML5 compatible browser like Chrome or Firefox.</h1></canvas>
	 <br>

    <button id="startBtn">Start</button> to play as the blue player<span id='statsBox'></span>
    <br><button id="flipBtn">Flip</button> to flip.

    <br><br>

	 <button onclick='$("#rules_panel").fadeToggle("fast","linear")'>Show/hide rules</button>
	 </div>

	 <?php include('instructions.php') ?>
	 <script>
	 $('#rules_panel').hide();
	 $('#instructions_confirm_form').hide();

	 </script>




  <div id="about_panel" style='display:none'>
  <h2>About The Game</h2>
  <p>
  FlipIt was invented by Marten van Dijk, Ari Juels, Alina Oprea, and Ronald L. Rivest in the paper <a href="http://www.rsa.com/rsalabs/presentations/Flipit.pdf">FLIPIT: The Game of "Stealthy Takeover"</a>.
  </p>

  <p>
  This implementation of flipIt was written in javascript and HTML by <a href="http://github.com/EthanHeilman">Ethan Heilman</a>.
  This version was adapted by <a href='http://www.alannochenson.com'>Alan Nochenson.</a>
  For the source code and further documentation please visit <a href="https://github.com/EthanHeilman/flipIt">flipIt on github</a>.
  </p>
  </div>
  </body>
</html>
