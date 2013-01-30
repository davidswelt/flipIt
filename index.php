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
	if(!array_key_exists('mturk_id', $_GET) || !$_GET['mturk_id']) {

		if(!array_key_exists('prevPage', $_GET)) {
			displayConsent();
		}
		elseif($_GET['prevPage'] == 'consentForm') {
			echo '</head><body>';
			displayInstructions();
		}
		elseif($_GET['prevPage'] == 'instructions') {
			displaySurvey();
		}
		elseif($_GET['prevPage'] == 'survey') {
      	processSurvey();
		}
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
   	include('survey.php');
		die;
	}
	function processSurvey() {
		//survey info entered into db here

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

		function getOrCreateSession(mturk_id) {
			var session_id = $.cookie('session_id');
			var url = location.toString();
			var forceNewSession = $.url(url).param('forceNewSession');

			if(!session_id || forceNewSession) {
				 session_id = $.ajax({type:'GET', url:'dbLayer.php', data:{action:'startGameSession','mturk_id':mturk_id}, async:false}).responseText;
				 $.cookie('session_id', session_id);
			}
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
			var session_id = getOrCreateSession(mturk_id);
       
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
					 $('#flash').fadeToggle('fast','linear');
					 $('#flash').html(endMsg);
					 //$('#flash').fadeToggle('slow','linear');
					 //$('#flash').html('');
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
				  
				  //For now, don't post on every flip, to be more efficient
				  var myData = {action:'postFlip','run_id':run_id,'flips':flips,'bs':JSON.stringify(blueScores), 'rs':JSON.stringify(redScores)};
				  $.ajax({type:'GET', url:'dbLayer.php', data:myData, async:false}).responseText;
			  }
		  });
      });
    </script>

	 <style>
	 div#flash {
    	background: gray;
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
