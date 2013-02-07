<div id="rules_panel" style='postion:relative;float:right'>
 <p>You are playing a game called flipIt. You will play multiple rounds of this game. You must play all rounds. Click start to start the game. You are the blue player. The board will appear grey until you flip to learn the state of the board.</p>

 <h2>Rules</h2>
 <ul>
	
	<li>
	  <h3>Basic</h3>
	  <p>
	  You, the blue player, always start in control of the board. The red player can play a flip and gain control at any time.
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

<form action='index.php' method='GET' id='instructions_confirm_form'>
	<input type='submit' value='I understand the above rules' />
	<input type='hidden' name='prevPage' value='instructions' />
	<?php
	if(array_key_exists('lab', $_GET)) {
		echo "<input type='hidden' id='lab' name='lab' value='lab'>\n";
	}
	?> 
</form> 
