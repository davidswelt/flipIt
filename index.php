<?php
	if(!array_key_exists('mturk_id', $_GET)) {
   	?>
		<form target='_self' method='GET'>
		What is you MTurk Id? <input name='mturk_id' id='mturk_id' type='text'>
		<input type='submit' value='Submit'>
		</form>
		<?php
		die;
	}
?>
<html>
  <head>
    <meta http-equiv="X-UA-Compatible" content="chrome=1, IE=edge">

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

			if(!session_id == null || forceNewSession) {
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
			console.log("session_id:"+session_id);

       
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
					 alert(endMsg);
					 endMsgDisplayed = 'Yes';

					 var myData = {action:'getSessionStats','session_id':session_id}; 
					 var session_stats = JSON.parse($.ajax({type:'GET', url:'dbLayer.php', data:myData, async:false}).responseText);
					 var num_runs_remaining = session_stats['num_runs_remaining'];

		  			 if(num_runs_remaining > 0) {
						 $('#startBtn').html('Start next game');
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

  </head>

  <title>FlipIt - The Game of Stealthy Takeover</title>
  <body>
    <div id="top_panel">
      <h1 id="title">FlipIt - The Game of Stealthy Takeover</h1>
    </div>

    <div id="scoreBoard"></div>
    
    <canvas id="gameBoard" width="800" height="150"><h1>Canvas element not supported by your browser. Use an HTML5 compatible browser like Chrome or Firefox.</h1></canvas>
	 <br>

    <button id="startBtn">Start</button> to play as the blue player
    <br><button id="flipBtn">Flip</button> to flip.

    <br><br>

    <p>Click start to start the game. You are playing the blue player. The board will appear grey until you flip to learn the state of the board.</p>

  <div id="rules_panel">

    <h2>Rules</h2>
    <ul>
      
      <li>
        <h3>Basic</h3>
        <p>
        You are playing as the blue player.
        While you, the blue player, always start in control the red player can play a flip and gain control at any time.
        The state of the board is obscured in grey.
        You and the red player only learn the state of the game by playing 'flip'.
        You can gain control by playing flip.
        The game ends after 10 seconds.
        </p>
      </li>
    
      <li>
        <h3>How to Win</h3>
        <p>
        The object of the game is to win as many points as possible.
        To win you want to be in control for as long as possible using as few flips as possible.
        </p>
      </li>
      
      <li>
        <h3>Points</h3>
        <p>
        You gain <b>100</b> points per second that you are in control.
        <br>
        You lose <b>100</b> points when you play 'flip'.
        </p>
      </li>
      
      <li>  
        <h3>Moves</h3>
        <p>
        Your only move is to play 'flip'
        If you are in control and you play 'flip' you remain in control.
        If you are not in control and you play 'flip' you regain control.
        Only one player can be in control at a time.
        </p>
      </li>
    
      <li>
        <h3>The Board</h3>
        <p>
        The board displays the current known information about the game.
        Each 'flip' played is marked with a circle.
        You can only see information that was revealed by your flips.
        The scores are updated when you play a 'flip' and reveal the current state of the game.
        Blue rectangles represent periods of time in which you, the blue player, had control. 
        Red rectangles represent periods of time in which the red player was in control.
        The score is given in the upper left hand corner.
        </p>

        An example game:
        <iframe src="/flipIt/drawgame.html?flips=100:X,200:Y,900:X" width="850" height="200" frameborder="0"></iframe>
        <p>   
        Lets examine the moves made in the game given above.
        </p>
        <ul>
          <li>
            0 second: The blue player starts in control.
          </li>
          <li>
            1 second: the blue player plays 'flip' and reminds in control.
          </li>
          <li> 
            2 seconds: the red player plays 'flip' and seizes control. The red player reminds in control for 7 seconds.
          </li>
          <li>
            9 seconds: the blue player plays 'flip' and takes control back. 
          </li>
          <li>
            10 seconds: the game ends.
          </li>
        </ul>
        <p> 
        The blue player was in control for <b>2 + 1 = 3</b> seconds gaining <b>300</b> points, playing flip twice costed the blue player <b>2 * -100 = -200</b> points, for a total score of <b>100</b> points.
        </p>
        <p>
        The red player was in control for <b>7</b> seconds gaining <b>700</b> points, playing flip only once at a cost of <b>-100</b> points, for a total score of <b>600</b> points.
        </p>
        <p>
        The red player has more points than the blue player and thus wins.
        </p>
      </li>
    </ul>
  </div>

  <div id="about_panel">
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
