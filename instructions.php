<div id="rules_panel">
 <h2>Basic Rules</h2>
<ul style='list-style-type: none'><li>
You will be playing multiple rounds of a two-player game called FlipIt. The objective of FlipIt is to gain and maintain possession of the game board. 

Until you take an action, the state of possession of the game board is hidden from your view. In this state, the board is shown in gray color. 

The only action you have available is to 'flip' the game board. When you flip the board, it will be shown to you who had possession of the game board until this very moment. This information will only be shown to you and not your opponent. At the same time, you also gain possession of the board, or maintain possession if you already owned the board.

The same rules apply to your opponent. That is, you cannot observe if and when the opponent flipped the board in the past, until you take the action to flip the board yourself.

Below, we break down the rules in more detail.
</li></ul>

 <h2>Detailed Rules</h2>
 <ul>
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
	  You earn <b>0</b> points while your opponent is in control.
	  <br>
	  You pay <b>100</b> points when you play 'flip'.
	  <br><br>
	  You begin with <b>0</b> points. Scores are updated when you play a 'flip' and at the end of the game.
	  </p>
	</li>
	
	<li>  
	  <h3>Moves</h3>
	  <p>
	  Your only move is to play 'flip'.
	  If you are in control and you play 'flip' you remain in control.
	  If you are not in control and you play 'flip' you regain control.
	  Only one player can be in control at a time.
	  </p>
	</li>
 
	<li>
	  <h3>The Board</h3>
	  <p>
	  The board displays the current known information about the game.
	  Each 'flip' played is marked with a dot.
	  You can only see information that was revealed by your flips.
	  Blue rectangles represent periods of time in which you, the blue player, had control. 
	  Red rectangles represent periods of time in which the red player was in control.
	  The score is given in the upper left hand corner.
	  </p>

	  <h2>An example game:</h2>
	  <h4>The game in progress:</h4>
	  <img src='images/midgame.png' width='800' />

	  <h4>The game when finished:</h4>
	  <img src='images/example_game.png' width='800' />
	  <!-- <iframe src="/flipIt/drawgame.html?flips=100:X,200:Y,900:X" width="850" height="200" frameborder="0"></iframe> -->
	  <p>   
	  Let's examine the moves made in the game given above.
	  </p>
	  <ul>
		 <li>
			<i>1st second</i>: The blue player starts in control.
		 </li>
		 <li>
			<i>2nd second:</i> The red player plays 'flip' and gains control. The red player plays 'flip' again less than a second later and remains in control.
		 </li>
		 <li> 
			<i>3rd second:</i> The blue player plays 'flip' and regains control. He maintains control for a bit over 2 seconds.
		 </li>
		 <li>
			<i>5th second:</i> The red player plays 'flip' and regains control. He keeps control for less than a second.
		 </li>
		 <li>
			<i>6th second:</i> The blue player plays 'flip' and regains control. He keeps control for about 2 seconds.
		 </li>                                                         
		 <li>
			<i>7th second:</i> The red player plays 'flip' and regains control. He keeps control for about 1 second.
		 </li>                                                         
		 <li>
			<i>9th second:</i> The blue player plays 'flip' and regains control. He maintains control for 4 seconds.
		 </li>                                                          
		 <li>
			<i>12th second:</i> The red player plays 'flip' and regains control. He maintains control for the rest of the game.
		 </li>                                                          
		 <li>
			<i>20th second:</i> The game ends.
		 </li>
	  </ul>
	  <p> 
	  The blue player was in control for <b>8.05</b> seconds earning <b>805</b> points, and played 'flip' 3 times, costing <b>300</b> points. This gives him a total score of <b>500</b> points.
	  </p>
	  <p>
	  The blue player was in control for <b>6.95</b> seconds earning <b>695</b> points, and played 'flip' 4 times, costing <b>400</b> points. This gives him a total score of <b>295</b> points.
	  </p>
	  <p>
	  The blue player has more points than the red player and thus wins.
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
