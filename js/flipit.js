/**
 * Creates a new FlipItGame Object for playing 'flip it'.
 *
 * new FlipItGame( new FlipItRenderEngine, funct, funct, funct(num x, num y) )
 *
 * @param renderer  an object which draws the game.
 * @param playerX   a player function which decides when player X is to flip.
 * @param playerY   a player function which decides when player Y is to flip.
 * @param scoreBoardFunct function to draw scores, blank if no score board.
 *
 **/
function FlipItGame( renderer, playerX, playerY, scoreBoardFunct) {
	var xControlBenefit = 1;
	var yControlBenefit = 1;

	var xFlipCost = 100;
	var yFlipCost = 100;

	this.resetAnchorAndPPT = function() {
		Players.periodicPlayerTick = Math.floor((Math.random()*400)+100);
		Players.anchor = Math.floor((Math.random()*400)+10);
	}
	this.scoreBoardFunct = scoreBoardFunct;
	this.lastDiff = 0;
	this.thisDiff = 0;
	this.deltaOfDeltas = 0;

	/**
	 * Clears and refreshes all the varables to start a new game.
	 **/
	this.newGame = function(){
		clearInterval( this.clock );

		this.running = false;

		this.ticks = 0;
		this.msPerTick = 0;
		this.control = "X";
		this.flips = [];

		this.xScore = 0;
		this.yScore = 0;
		this.lastFlipGood = false;

		this.lastDiff = 0;
		this.thisDiff = 0;
		this.deltaOfDeltas = 0;

		this.lastDiffAdj = 0;
		this.thisDiffAdj = 0;
		this.deltaOfDeltasAdj = 0;
 

		renderer.drawBoard( 0, [] );
	}


	/** 
	 * Start a game.
	 *
	 * this.start( int, int )
	 *
	 * @param msPerTick number of milliseconds per tick or turn of the game.
	 * @param numTicks  length of game in ticks/turns.
	 *
	 **/
	this.start = function( msPerTick, numTicks ) {
		this.newGame();

		if (this.running == false ){
			this.running = true;

			renderer.newBoard();
			this.numTicks = numTicks;
			this.msPerTick = msPerTick;

			var self = this; //Save the current context
			this.clock = setInterval( function(){ self.tick( numTicks ); }, msPerTick);
			this.scoreBoardFunct(0, 0);
		}
	};

	/**
	 * Ends the current game.
	 **/
	this.endGame = function() {
		clearInterval( this.clock );
		this.running = false;
		this.resetAnchorAndPPT();

		renderer.drawEnd(this.numTicks, this.flips);
		//renderer.drawBoard(this.numTicks, this.flips);


		if ( scoreBoardFunct != null ) scoreBoardFunct( this.xScore, this.yScore );
	};

	/**
	 * The main game loop. End turn/tick this runs.
	 *
	 * this.tick( int )
	 *
	 * @param numTicks  length of game in ticks/turns.
	 *
	 **/
	this.tick = function( numTicks ) {
		if( this.ticks >= numTicks ) {
			this.endGame();
			return;
		} 

		this.ticks += 1;

		if ( this.control == "X" ) this.xScore += xControlBenefit;
		if ( this.control == "Y" ) this.yScore += yControlBenefit;

		//if a human is playing a player function is set to neverMove()
		if( playerX( this.ticks ) ){ this.defenderFlip() }; //player x makes their move
		if( playerY( this.ticks ) ){ this.attackerFlip() }; //player y makes their move

		//only draw every fifth frame
		if ( this.ticks % 5 == 0 ) renderer.drawBoard( this.ticks, this.flips );

		elapsed = (window.game.ticks*window.game.msPerTick);

		if(elapsed % 100 == 0) {
			str = elapsed/1000;
			if(elapsed % 1000 == 0) {
				str = elapsed/1000+'.0';
			}


			$('#clock').html(str);
		}
	};

	/**
	* When the defender, player x, flips call this function. 
	**/
	this.defenderFlip = function() {
		if (this.running == true) {
			this.flips[this.ticks] = "X";

			this.lastFlipGood = false;
			if(this.control == 'Y') {
				this.lastFlipGood = true;
			}

			this.control = "X";


			function previousXFlip() {
				this.flips = window.game.flips;
				
				prev = null;
				for(ftime in this.flips) {
					if(this.flips[ftime] == 'X')
						if(ftime != window.game.ticks)
							prev = ftime;
				}
				return prev;
			}
			function oppFlipsSinceLastX() {
				this.flips = window.game.flips;
				lastX = previousXFlip();

				num = 0;
				for(ftime in this.flips) {
					if(this.flips[ftime] == 'Y' &&
						ftime > lastX) {
						num++;
					}
				}
				return num;
			}

			
			yNumFlipsInArea = oppFlipsSinceLastX();
			this.yNumFlipsInArea = yNumFlipsInArea;
			console.log(yNumFlipsInArea);

			yAdj = this.yScore + (yFlipCost * yNumFlipsInArea);
			xAdj = this.xScore;

			this.xScore -= xFlipCost;

			this.thisDiff = this.xScore - this.yScore;
			this.thisDiffAdj = xAdj - yAdj;

			this.deltaOfDeltas = this.thisDiff - this.lastDiff;
			this.deltaOfDeltasAdj = this.thisDiffAdj - this.lastDiffAdj;

			this.lastDiff = this.thisDiff;
			this.lastDiffAdj = this.thisDiffAdj;
		}
	};

	/**
	 * When the attacker, player y, flips call this function. 
	 **/
	this.attackerFlip = function(){
		if (this.running == true) {
			this.flips[this.ticks] = "Y";
			this.control = "Y";

			this.yScore -= yFlipCost;
		}
	}
};


// Computer players
var Players = { 
	"humanPlayer":function( ticks ){ return false }, 
	"randomPlayer":function( ticks ){ if(ticks % 79 == 0) return Math.random(ticks) < 0.3; },

	"periodicPlayer":function( ticks ){ var anchor = Players['anchor']; var ppt = Players['periodicPlayerTick']; if(ticks >= anchor && (((anchor - ticks) % ppt) == 0)) {return true;}return false; }
};
