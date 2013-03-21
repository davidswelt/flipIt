<div id="rules_panel">
 <h2>Basic Rules</h2>
<ul style='list-style-type: none'><li>
You will be playing multiple rounds of a two-player game called FlipIt. The objective of FlipIt is to gain and maintain possession of the game board. 

Until you take an action, the state of possession of the game board is hidden from your view. In this state, the board is shown in gray color. 
                                                                   <br>
The only action you have available is to 'flip' the game board. When you flip the board, it will be shown to you who had possession of the game board until this very moment. This information will only be shown to you and not your opponent. At the same time, you also gain possession of the board, or maintain possession if you already owned the board.
                                                                   <br>
The same rules apply to your opponent. That is, you cannot observe if and when the opponent flipped the board in the past, until you take the action to flip the board yourself.
                                                                   <br>
Below, we break down the rules in more detail.
</li></ul>

 <h2>Detailed Rules</h2>
 <ul>
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
	  <br>
	  <br>
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
	  The board displays the current known information about the game, including your points, the points of the red player, and the difference between your points and the points of the red player.
	  Each 'flip' played is marked with a dot.
	  You can only see information that was revealed by your flips.
	  Blue rectangles represent periods of time in which you, the blue player, had control. 
	  Red rectangles represent periods of time in which the red player was in control.
	  </p>

	  <h2 style='position:relative;left:-40px'>An example game:</h2>
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
			<i>12th second:</i> The red player plays 'flip' and regains control. He maintains control for the rest of the game. He makes a number of flips in which he maintains control.
		 </li>                                                          
		 <li>
			<i>20th second:</i> The game ends.
		 </li>
	  </ul>
	  <p> 
	  The blue player was in control for <b>8.05</b> seconds earning <b>805</b> points, and played 'flip' 3 times, costing <b>300</b> points. This gives him a total score of <b>505</b> points.
	  </p>
	  <p>
	  The red player was in control for <b>11.95</b> seconds earning <b>1195</b> points, and played 'flip' 9 times, costing <b>900</b> points. This gives him a total score of <b>295</b> points.
	  </p>
	  <p>
	  The blue player has more points than the red player and thus wins.
	  </p>
	</li>

	<li>
	  <h3>Payment</h3> 
	  <p>
You will be compensated according to your performance in this study. For completing the study, you are guaranteed the amount listed on the Mechanical Turk HIT that you have accepted, and you will be paid an additional sum based on your performance.<br> 
You will participate in multiple rounds of the game. At first, you will participate in a practice round without a bonus payment to familiarize yourself with the interface. Then, you will participate in several additional rounds. You can receive a bonus payment for your performance in each of those rounds.<br>
You can increase your bonus payment in a given round by performing well compared to the red player. If you lose by more than 1000 points, however, you will receive no bonus payment for that round.<br> 
The exchange rate for points into the bonus payment is 1 cent for 100 points. For example, you would earn a bonus payment of 10 cents by gaining exactly as many points as the red player. If you outperform your opponent by 500 points you would earn 15 cents. If you underperform you opponent by 500 points you would earn 5 cents.<br> 
	  </p>
 </ul>
</div> 

