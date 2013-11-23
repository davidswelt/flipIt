
<!DOCTYPE html>
<html>
  <head>  
  
<?php 

	define('INSTRUCTIONS', 'instructions_LM.php'); //this should be updated programatically but is not since sessions are assigned after the point of instructions. This is a problem for future Alan.

	include_once(dirname(__FILE__).'/die_if_ie_under_9.php'); 
	include_once(dirname(__FILE__).'/config/config.php');

	global $sid_string;
   $sid_string = 'session_id'.COOKIE_NUM;

	rejectIfRepeat();

	if(array_key_exists('mturk_id', $_REQUEST)) {
		$mturk_id = $_REQUEST['mturk_id'];
		$mturk_id = preg_replace('/[^a-zA-Z0-9]/','', $mturk_id);
		setcookie('mturk_id', $mturk_id);
	}      

	if(array_key_exists('hit_id', $_REQUEST)) {
		$hit_id = $_REQUEST['hit_id'];
		$hit_id = preg_replace('/[^a-zA-Z0-9 ]/','', $hit_id);
		setcookie('hit_id', $hit_id);
	}                              

	$prevPage = '';
	if(array_key_exists('prevPage', $_REQUEST)) {
		$prevPage = $_REQUEST['prevPage'];
	}

	$page = '';
	if(array_key_exists('page', $_REQUEST)) {
		$prevPage = $_REQUEST['prevPage'];
	}   

	if(array_key_exists($sid_string, $_COOKIE) ||
	   $prevPage == 'survey' || defined('DEMO')) {
		processSurveyAndCreateSession();
	}
	else {
		if(!$prevPage) {
			echo '</head>';
			displayConsent();
		}
		elseif($prevPage == 'consentForm') {
			echo '</head><body>';
			displayInstructions();
		}
		elseif($prevPage == 'instructions') {
			displaySurvey();
		}
	}

	function displayConsent() {
		//maybe here set a cookie for hit id and grab it back later to put into the survey, since clearly people do not understand what that is. same with mturk id?

		include('consentForm.php');
		die;
	}

	function displayInstructions() {
		include(INSTRUCTIONS);
		include('confirm_instructions.php');
		die;
	}
	function displaySurvey() {
   	include('survey/survey.php');
		die;
	}
	function processSurveyAndCreateSession() {  
		$_REQUEST['action'] = 'connect';
		require_once(dirname(__FILE__).'/dbLayer.php');
		unset($_REQUEST['action']);
		unset($_REQUEST['prevPage']);
          
		sanitizeParams(array('mturk_id','forceNewSession', 'prevPage','action', 'hit_id'));
		integrityCheck();

		collapseScale('rps', 9);
		collapseScale('nfc', 9);
		ksort($_REQUEST);
		
		$survey_blob = json_encode($_REQUEST);

		$db = db_connect();
		createNewSession($db, $survey_blob);
	}

	function rejectIfRepeat() {
		if(array_key_exists('mturk_id', $_REQUEST)) {
			rejectIfRepeatMturkID($_REQUEST['mturk_id']);
		}

		global $sid_string;

		if(array_key_exists($sid_string, $_COOKIE)) {
			$_REQUEST['action'] = 'connect';
			include_once(dirname(__FILE__).'/dbLayer.php');
			$db = db_connect();
			
			$mturk_id = getMTurkIdFromDb($db, $_COOKIE[$sid_string]);
			rejectIfRepeatMturkID($mturk_id);
		}
	}


	function rejectIfRepeatMturkID($mturk_id) {
		$record_file = dirname(__FILE__).'/review/affected.csv';
		$repeat_mturk_ids = file_get_contents($record_file);
		$repeat_mturk_ids = explode(',', $repeat_mturk_ids);
		$repeat_mturk_ids = array_unique($repeat_mturk_ids);
               
		if($mturk_id!='' && in_array($mturk_id, $repeat_mturk_ids)) {
			?>
			<p>
			Our records indicate that a user with the entered Mechanical Turk ID has already completed this experiment. Users cannot complete the experiment more than once. We are sorry for the inconvenience.
         </p>
			<?php

         die;
		}
	}
	
	function createNewSession($db, $survey_blob) {
		$info_treatment = getNewTreatment($db, 'info', true);
		$visual_treatment = getNewTreatment($db, 'visual', true);
		global $info_treatment_message, $sid_string, $visual_treatment_feedback_type;

		$valid_session = false;
		if(array_key_exists($sid_string, $_COOKIE)) {
			$valid_session = isValidSession($db, $_COOKIE[$sid_string]);
		}


		if($valid_session) {
			$info_treatment = getOldTreatment($db, 'info', intval($_COOKIE[$sid_string]));
			$visual_treatment = getOldTreatment($db, 'visual', intval($_COOKIE[$sid_string]));
		}

		$info_treatment_id = $info_treatment['id'];
		$info_treatment_message = $info_treatment['opponent_description'];

		$visual_treatment_id = $visual_treatment['id'];
		$visual_treatment_feedback_type = $visual_treatment['feedback_type'];

		if(array_key_exists('feedback', $_GET)) {
			$visual_treatment_feedback_type = $_GET['feedback'];
		}                      
			
		if(!$valid_session) {
			$db = db_connect();
			$mturk_id = getMTurkIdFromBlob($survey_blob);
			$session_id = startGameSession($db, $mturk_id, $survey_blob, $info_treatment_id, $visual_treatment_id, false);
			setcookie($sid_string, $session_id);
			return $session_id;  
		}
	}

	function integrityCheck() {
		foreach($_REQUEST as $k => $v) {
			if(preg_match('/^check(\d)$/', $k, $matches)) {
				$correct = intval($matches[1]);
				$check = $v;

				if($check != $correct) {
					?>
					<script>
						alert("You have answered an integrity question incorrectly. Please go back to the survey and read the directions carefully. Then, check your answers and re-submit.");
						window.history.back(-1);
					</script>
					<?php
					die;
				}
				unset($_REQUEST[$k]);
			}
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
	<script type="text/javascript" src="js/flipit.js"></script>
	<script type="text/javascript" src="js/drawflipit.js"></script>
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

		function getSession() {
			var session_id = $.cookie('<?php global $sid_string;echo $sid_string ?>');

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
			testCookie();
			if(!testCookie.enabled) {
				document.write("Please enable cookies to perform this task.");
				return;
			}
			var session_id = getSession();
			handleSessionChange(session_id);

			var msPerTickSlow = 10;
			var numTicksLong = 2000;

			var config = new RenderSettings( $("#gameBoard") ); 

			config.fogOfWar = true;
			config.numTicks = numTicksLong;
			config.feedback_type = "<?php echo $visual_treatment_feedback_type; ?>";
			window.feedback_type = config.feedback_type;

			var gDraw = new FlipItRenderEngine( config );
			var sb = new ScoreBoard( $("#scoreBoard"), config.xColor, config.yColor, config.feedback_type );        

			var game = new FlipItGame( gDraw, Players["humanPlayer"], Players["periodicPlayer"], sb.update );
			window.game = game;

			if(window.feedback_type != 'all' && window.feedback_type != 'LM') {
				$('#gameBoard_LM').hide();
			}
			if(window.feedback_type != 'all' && window.feedback_type != 'FH') {
				$('#gameBoard_FH').hide();
			}          
			game.newGame();
			sb.update(0, 0);
			var run_id = null;
			var started = false;
			var blueScores = [];
			var redScores = [];
			var endMsgDisplayed = 'No';
			var firstTime = true;
			window.game = game;

			setInterval(function() {
				if(game.running) {
					$('#startBtn').removeAttr('disabled')
					$('#startBtnAndText').hide();
					$('#flipBtnAndText').show();
					endMsgDisplayed = 'No';
				}
				else {
					if($('#countdown').html() == '<h2 style="display:inline">The game is now running.</h2>')
						$('#countdown').html('');

					$('#startBtnAndText').show();
					$('#flipBtnAndText').hide();

					if(endMsgDisplayed != 'Yes') {
					 if(game.xScore - game.yScore > 0) {
						 endMsg = "Good job! You won!";
					 }
					 else {
						 endMsg = "You lost! Better luck next time!";
					 }
					 game.resetAnchorAndPPT();

					 flips = getCleanFlipString(game.flips);
					 

					 if(flips != '') {
						 replaceOppParams();


						 blueScores.push(game.xScore);
						 redScores.push(game.yScore);
						 
						 var myData = {action:'postFlip','run_id':run_id,'flips':flips,'bs':JSON.stringify(blueScores), 'rs':JSON.stringify(redScores)}; 
						 run_id = $.ajax({type:'GET', url:'dbLayer.php', data:myData, async:false}).responseText;
						 $('#flash').css('visibility', 'visible');
						 $('#flash').html(endMsg);
						 $('#flash').css('text-align','center');
						 endMsgDisplayed = 'Yes';
						 handleSessionChange(session_id, true);
					 }
					 else {
						 replaceOppParams();
						 endMsgDisplayed = 'Yes';
					 }
                
					 if(firstTime) {
                	handleSessionChange(session_id, true);
						firstTime = false;
					 }  
					 blueScores = [];
					 redScores = [];
				  }

			  }
		  }, msPerTickSlow);

		//setup buttons
		$("#startBtn").click( function() {
				if(!game.running) {
					clearLMBoard();
					setTimeout(function() { 
						$('#countdown').append('<h4 style="display:inline">Game will start in 3</h4>...'); 
						setTimeout(function() { 
							$('#countdown').append('<h3 style="display:inline">2</h3>...');
							setTimeout(function() { 
								$('#countdown').append('<h2 style="display:inline">1</h2>...');
								setTimeout(function() { 
									startGameAfterCount();
								}, 1000);  
							}, 1000);
						}, 1000);
					}, 1);

					function startGameAfterCount() {

						started = true;
						sb.update(0, 0);
						var myData = {action:'startGameRun','session_id':session_id, 'tick':Players.periodicPlayerTick, 'anchor': Players.anchor};
						run_id = $.ajax({type:'GET', url:'dbLayer.php', data:myData, async:false}).responseText;

						$('#flash').css('visibility', 'hidden');

						if(window.feedback_type != 'all' && window.feedback_type != 'LM') {
							$('#gameBoard_LM').hide();
						}
						if(window.feedback_type != 'all' && window.feedback_type != 'FH') {
							$('#gameBoard_FH').hide();
						}           

						game.start( msPerTickSlow, numTicksLong );
					}
				}
        });


		  $("#flipBtn").click( function() {
			  if(game.running) {
				  game.defenderFlip();
				  flips = getCleanFlipString(game.flips);
				  sb.update( game.xScore, game.yScore );

				  blueScores.push(game.xScore);
				  redScores.push(game.yScore);
				  
			  //   var myData = {action:'postFlip','run_id':run_id,'flips':flips,'bs':JSON.stringify(blueScores), 'rs':JSON.stringify(redScores)};
			  //   $.ajax({type:'GET', url:'dbLayer.php', data:myData, async:false}).responseText;
			  }
		  });

		  game.resetAnchorAndPPT();

		  replaceOppParams.msPerTickSlow = msPerTickSlow;
      });
		  
		function replaceOppParams() {
			var msPerTickSlow = replaceOppParams.msPerTickSlow;
			var desc = $('#opponent_description').html();
			periodicPlayerTick = parseInt(Players['periodicPlayerTick']);
			periodicPlayerTick = periodicPlayerTick*msPerTickSlow/1000;
			periodicPlayerTick = '<b>1 flip every '+periodicPlayerTick+' seconds</b>';

			anchor = parseInt(Players['anchor'])*msPerTickSlow/1000;
			anchor = '<b>'+anchor+' seconds</b>';

			desc = desc.replace(/\d flip every \d(\.\d?\d?) seconds?/g, '%{alpha}');
			desc = desc.replace(/<b>\d(\.\d?\d?) seconds?/g, '<b>%{anchor}');

			desc = desc.replace(/%{alpha}/g,periodicPlayerTick);
			desc = desc.replace(/%{anchor}/g,anchor);

			$('#opponent_description').html(desc); 
		} 

		function pluralize_stupid(num, word) {
			return (num != 1) ? word+'s': word;
		}

		function value_and_plural(num, word) {
			return num+" "+pluralize_stupid(num, word);
		}

		//handles changing from practice to normal
		//and normal game state to session end
		function handleSessionChange(session_id, showAlert) {
			var myData = {action:'getSessionStats','session_id':session_id}; 
			var session_stats = $.ajax({type:'GET', url:'dbLayer.php', data:myData, async:false});
			session_stats = JSON.parse(session_stats.responseText);
			var num_runs_remaining = session_stats['num_runs_remaining'];
			var num_practice_runs_remaining = session_stats['num_practice_runs_remaining'];

			if(num_runs_remaining > 0) {
				$('#startBtn').html('Start game');
				var infoString = '';

				if(num_practice_runs_remaining > 0) {
					infoString = value_and_plural(num_practice_runs_remaining, 'practice round')+' left';
					window.is_practice = true;

					$('h1#title').html('Round for Practice');
					$('h1#title').css('color', '#CC1100');

 				}
				else {
					window.is_practice = false;

					$('h1#title').html('Round with Bonus Payment');
					$('h1#title').css('color', '#000000');
					if(num_practice_runs_remaining == 0) {
						var extra = '';
						if(window.feedback_type == 'LM') {
							//extra = ' From now on, you will not see the game board. However, when you play \'flip\' you will be told if your flip is effective or not, when your opponent last moved, and what the current time is.';
						}


						if(showAlert) {
							alert('Each round you play from now on will be counted. Results of these rounds will affect your bonus payment. You must play '+value_and_plural(num_runs_remaining, 'more round')+' in order to be paid.'+extra);
						}
					}
					infoString = value_and_plural(num_runs_remaining, 'round')+' left, '+value_and_plural(session_stats['num_runs_played']-session_stats['NUM_PRACTICE_RUNS'],'round')+' played';
				}

				$('#statsBox').html(' ('+infoString+')');
			}
			else {
				handleSessionEnd(session_stats['bonus']);
			} 
		}

		function handleSessionEnd(bonus) {
		        var msg = '<div style="border-style:solid;background-color:grey;position:absolute;left:20px;top:300px;z-index:100"><h1 style="text-align:center;margin: 50px 50px 50px 50px">Thank you for finishing all rounds. Your participation has been recorded (no receipt code is necessary).  You will be paid the amount specified in the accepted HIT,';

                        var paystring = 'but your performance did not warrant any bonus payments.';

                        if(bonus && bonus != '$0.00') {
                                paystring = ', and we\'ll send you an additional '+bonus+' based on your performance.';
                        }
                        msg += ' '+paystring;

                        msg += ' You may copy message for your records and close the window.</h1></div>';

			elem = '<div style="z-index:0;position:absolute;left:-100px;top:-100px;width:10000px;height:10000px;background-color:gray;opacity:0.8;filter:alpha(opacity=80);"></div>';
			$('body').css('z-index', '-100');

			$('body').prepend($(msg));
			$('body').prepend($(elem));
			window.open('', '_self', '');
//			window.close(); 
		}
    </script>
  </head>

  <title>FlipIt - The Game of Stealthy Takeover</title>
  <body>
	 <div id='above_buttons' style='min-height:300px;height:auto !important; height:300px;'>
	 <div id='not_rules_panel'>
    <div id="top_panel">
      <h1 id="title">FlipIt - The Game of Stealthy Takeover</h1>
    </div>
	  <div id='flash'></div>

	 <div id="scoreBoard"></div>
	 <br>
	 <div id='gameBoard_LM'>
		 <canvas id="gameBoard_LM_canvas" width="800" height="100"></canvas>
	 </div>    
	 <div id='gameBoard_FH'>
		 <canvas id="gameBoard" width="800" height="150"></canvas>
	 </div>
	 </div>
	 </div><br>


    <span id='startBtnAndText'><button id="startBtn" style='width:75px;height:50px;' >Start</button> to play as the blue player<span id='statsBox'></span></span>
    <span id='flipBtnAndText' style='display:none'><button id="flipBtn" style='width:75px;height:50px;'>Flip</button> to flip.</span>

    <br><br>

	 <h3>Important information about your opponent:</h3>
	 <p id='opponent_description'>
	 <?php
	 echo $info_treatment_message;
	 ?>
	 </p>


	<script>
	$('#rules_panel').css('position', 'relative');
	$('#rules_panel').css('float', 'right');
	</script>

	 <button onclick='$("#rules_panel").fadeToggle("fast","linear")'>Show/hide rules</button>
	 </div>

	 <?php include(INSTRUCTIONS); ?>
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
  For the source code and further documentation please visit <a href="https://github.com/anochens/flipIt">flipIt on github</a>.
  </p>
  </div>
  </body>
</html>
