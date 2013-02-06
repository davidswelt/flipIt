<!-- From http://24ways.org/2009/have-a-field-day-with-html5-forms/ for css -->
<style>
html, body, h1, form, fieldset, legend, ol, li {
	margin: 0;
	padding: 0;
}
body {
	background: #ffffff;
	color: #111111;
	font-family: Georgia, "Times New Roman", Times, serif;
	padding: 20px;
}
form {
	background: #9cbc2c;
	-moz-border-radius: 5px;
	-webkit-border-radius: 5px;
	border-radius: 5px;
	padding: 20px;
	width: 750px;
}
form ul li {
	background: #b9cf6a;
	background: rgba(255,255,255,.3);
	border-color: #e3ebc3;
	border-color: rgba(255,255,255,.6);
	border-style: solid;
	border-width: 2px;
	-moz-border-radius: 5px;
	-webkit-border-radius: 5px;
	border-radius: 5px;
	line-height: 30px;
	list-style: none;
	padding: 5px 10px;
	margin-bottom: 2px;
	width:90%
}
form fieldset {
	border: none;
	margin-bottom: 10px;
}
form fieldset:last-of-type {
	margin-bottom: 0;
}
form legend {
	color: #384313;
	font-size: 16px;
	font-weight: bold;
	padding-bottom: 10px;
	text-shadow: 0 1px 1px #c0d576;
}
form > fieldset > legend:before {
	content: "Step " counter(fieldsets) ": ";
	counter-increment: fieldsets;
}
form ul ul li {
	background: none;
	border: none;
}
form label {
	font-size: 15px;
	min-width: 300px;
	float:left;
}
form input:not([type=radio]),
form textarea {
	background: #ffffff;
	border: none;
	-moz-border-radius: 3px;
	-webkit-border-radius: 3px;
	-khtml-border-radius: 3px;
	border-radius: 3px;
	font: italic 15px Georgia, "Times New Roman", Times, serif;
	outline: none;
	padding: 5px;
	width: 200px;
}
form input:not([type=submit]):focus,
form textarea:focus {
	background: #eaeaea;
}
form input[type=radio] {
	float: left;
	margin-right: 5px;
}
form button {
	background: #384313;
	border: none;
	-moz-border-radius: 20px;
	-webkit-border-radius: 20px;
	-khtml-border-radius: 20px;
	border-radius: 20px;
	color: #ffffff;
	display: block;
	font: 18px Georgia, "Times New Roman", Times, serif;
	letter-spacing: 1px;
	margin: auto;
	padding: 7px 25px;
	text-shadow: 0 1px 1px #000000;
	text-transform: uppercase;
}
form button:hover {
	background: #1e2506;
	cursor: pointer;
}
</style>
</head><body>
<form target='_self' method='GET'>
<?php

$id = 'MTurk Id';
if(array_key_exists('lab', $_GET)) {
	$id = 'full name (first and last)';

	echo "<input type='hidden' id='lab' name='lab' value='lab'>\n";
}

$survey = array(
	'mturk_id' => array('label'=> "What is your $id?"),
	'age' => array('label'=>'What is your age (in years)?'),
	'gender' => array('type'=>'radio', 'options' => array('Male', 'Female', 'Decline to answer')),
	'education' => array('label'=>'What is the highest level of education that you have completed?', 'type'=>'radio', 'options' => array('Some high school', 'High school', 'Some college', 'Two year college degree', 'Four year college degree', 'Graduate or professional degree')),
	'country' => array('label' => 'What is your country of origin?', 'type'=>'radio', 'options'=>array('United States', 'India', 'Canada', 'None of the above'))
	);

$rps = array(
  'rps1' => array('label'=>'Safety First', 'type'=>'likert', 'size'=>'9'),
  'rps2' => array('label'=>'I do not take risks with my health', 'type'=>'likert', 'size'=>'9'),
  'rps3' => array('label'=>'I prefer to avoid risks', 'type'=>'likert', 'size'=>'9'),
  'rps4' => array('label'=>'I take risks regularly', 'type'=>'likert', 'size'=>'9'),
  'rps5' => array('label'=>'I really dislike not knowing what is going to happen.', 'type'=>'likert', 'size'=>'9'),
  'rps6' => array('label'=>'I usually view risks as a challenge', 'type'=>'likert', 'size'=>'9'),
  'rps7' => array('label'=>'I view myself as a...', 'left'=>'risk avoider', 'right'=>'risk seeker', 'type'=>'likert', 'size'=>'9'),
);

?>
<style>
label.error { float: none; color: red; padding-left: .5em; vertical-align: top; }

</style>

<script src="//code.jquery.com/jquery-latest.js"></script>
<script type="text/javascript" src="//jzaefferer.github.com/jquery-validation/jquery.validate.js"></script>
<script>

$(document).ready(function(){
	$('form input, form textarea').not([type="submit"]).addClass('required');

	$("form").validate({
		errorPlacement: function(error, element) {
			element.closest('fieldset > ul > li').css('color', '#AA0000');
			var errorMsgTop = $('.errorMsgTop').first();
			errorMsgTop.html('<br><span style="color:#AA0000">Questions in red are required and have not been filled out. Please fill in these questions.</span>');
		}
	});
});

</script>

<?php

makeHtmlFromSurvey($survey, "Please answer the following questions before playing the game.");

$rpsPre = "Please indicate the extent to which you agree or disagree with the following statement by putting a circle around the option you prefer. Please do not think too long before answering; usually your first inclination is also the best one.";
makeHtmlFromSurvey($rps, $rpsPre, 'Self-assesment');


function makeHtmlFromSurvey($survey, $preMsg = '', $title='Survey') {
	echo "<legend>$title</legend><fieldset>";
	echo "<p class='preMsg'>$preMsg<span class='errorMsgTop'</p><ul>";
	foreach($survey as $param => $details) {
		if(!array_key_exists('value', $details)) {
			$details['value'] = '';
		}
		if(!array_key_exists('type', $details)) {
			$details['type'] = 'text';
		}
		if(!array_key_exists('label', $details)) {
			$details['label'] = "What is your $param?";
		}

		echo "<li><label for='$param'>".$details['label']."</label>";

		if($details['type'] == 'likert') {
			if(!array_key_exists('left', $details)) {
				$details['left'] = 'totally disagree';
			}
			if(!array_key_exists('right', $details)) {
				$details['right'] = 'totally agree';
			} 

			$size = 5;
			if(array_key_exists('size', $details)) {
         	$size = intval($details['size']);
			}
			echo '<br><ul><li><span style="float:left;min-width:150px">'.$details['left'].'&nbsp;</span>';

			for($i=1;$i<=$size;$i++) {
         	echo "<input type='radio' name='$param' id='$param' value='$i'>\n";
			}
			echo $details['right'].'&nbsp;</li></ul>';

		}
		elseif($details['type'] == 'radio') {
			echo "<br><ul>";
			$i = 0;
			foreach($details['options'] as $option) {
				echo "<li>$option<input align='left' name='$param' id='$param' type='".$details['type']."' value='".$i."'></li>\n";
				$i++;
			}
			echo '</ul></li>';
		}
		else {
			echo "<input name='$param' id='$param' type='".$details['type']."' value='".$details['value']."'></li>\n";
		}
		
	}
	echo '</ul></fieldset><hr>';
}

?>

<fieldset>
<input name='forceNewSession' value='true' type='hidden'>
<input type='hidden' name='prevPage' value='survey' />
<button type='submit' value='Submit'>Submit</button>
</fieldset>
</form>         
