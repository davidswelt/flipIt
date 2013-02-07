<!-- From http://24ways.org/2009/have-a-field-day-with-html5-forms/ for css -->
<link rel="stylesheet" type="text/css" href="css/surveyStyle.css">
<script src="//code.jquery.com/jquery-latest.js"></script>
<script type="text/javascript" src="js/jquery.validate.js"></script>

<script>
$(document).ready(function(){
	$('form#survey input, form textarea').not([type="submit"]).addClass('required');

	$("form#survey").validate({
		errorPlacement: function(error, element) {
			element.closest('fieldset > ul > li').css('color', '#AA0000');
			var errorMsgTop = $('.errorMsgTop');
			errorMsgTop.html('<br><span style="color:#AA0000">Questions in red are required and have not been filled out. Please fill in these questions.</span>');
		}
	});
});
</script>               
</head>

<body>

<form name='survey' id='survey' target='_self' method='POST'>

<?php
if(array_key_exists('lab', $_GET)) {
	echo "<input type='hidden' id='lab' name='lab' value='lab'>\n";
}

class SurveyParts {
	public $sections;

	function fillArrays() {
		$demoPre = 'Please answer the following demographic questions.';
		$demo = array('pre' => $demoPre, 'title' => 'Survey',
		'data' => array(
			'mturk_id' => array('label' => "What is your $id?"),
			'age' => array('label' => 'What is your age (in years)?'),
			'gender' => array('type' => 'radio', 'options' => array('Male', 'Female', 'Decline to answer')),
			'education' => array('label' => 'What is the highest level of education that you have completed?', 'type' => 'radio', 'options' => array('Some high school', 'High school', 'Some college', 'Two year college degree', 'Four year college degree', 'Graduate or professional degree')),
			'country' => array('label' => 'What is your country of origin?', 'type' => 'radio', 'options' => array('United States', 'India', 'Canada', 'None of the above'))
			));

		$rpsPre = "Please indicate the extent to which you agree or disagree with the following statement by putting a circle around the option you prefer. Please do not think too long before answering; usually your first inclination is also the best one.";
		$rps = array('pre' => $rpsPre, 'title' => 'Self-assessment', 
		 'data' => array(
		 'rps1' => array('label' => 'Safety First.', 'type' => 'likert', 'size' => '9'),
		 'rps2' => array('label' => 'I do not take risks with my health.', 'type' => 'likert', 'size' => '9'),
		 'rps3' => array('label' => 'I prefer to avoid risks.', 'type' => 'likert', 'size' => '9'),
		 'rps4' => array('label' => 'I take risks regularly.', 'type' => 'likert', 'size' => '9'),
		 'rps5' => array('label' => 'I really dislike not knowing what is going to happen.', 'type' => 'likert', 'size' => '9'),
		 'rps6' => array('label' => 'I usually view risks as a challenge.', 'type' => 'likert', 'size' => '9'),
		 'rps7' => array('label' => 'I view myself as a...', 'left' => 'risk avoider', 'right' => 'risk seeker', 'type' => 'likert', 'size' => '9'),
		));

		$nfcPre = "Please indicate the extent to which you agree or disagree with the following statement by putting a circle around the option you prefer.";
		$nfc = array('pre' => $nfcPre, 'title' => 'Self-assessment', 
		'data' => array(
		 'nfc1' => array('label' => 'I would rather do something that requires little thought than something that is sure to challenge my thinking abilities.', 'type' => 'likert', 'size' => '9'),
		 'nfc2' => array('label' => 'I try to anticipate situations where there is a likely chance I\'ll have to think in depth about something.', 'type' => 'likert', 'size' => '9'),
		 'nfc3' => array('label' => 'I only think as hard as I have to.', 'type' => 'likert', 'size' => '9'),
		 'nfc4' => array('label' => 'The idea of relying on thought to get my way to the top does not appeal to me.', 'type' => 'likert', 'size' => '9'),
		 'nfc5' => array('label' => 'The notion of thinking abstractly is not appleaing to me.', 'type' => 'likert', 'size' => '9'),
		));

		$this->addComponents($demo, $rps, $nfc);
	}

	function __construct($randomize = true) {
		$this->fillArrays();
	
		for($i=0;$i<count($this->sections);$i++) {
			$sect = $this->sections[$i];
			$this->sections[$i] = $this->getHtmlFromSurvey($sect['data'], $sect['pre'], $sect['title']); 
		}

		if($randomize) {
			shuffle($this->sections);
		}
	}

	function addComponents() {
		$components = func_get_args();

		foreach($components as $component) {
			$this->sections[] = $component;
		}
		
		if($randomize) {
			shuffle($this->sections);
		}
	}

	function getHtmlFromSurvey($survey, $preMsg = '', $title = 'Survey') {
		$result = '';
		$result .= "<legend>$title</legend><fieldset>";
		$result .= "<p class='preMsg'>$preMsg<span class='errorMsgTop'</p><ul>";
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

			$result .= "<li><label for='$param'>".$details['label']."</label>";

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
				$result .= '<br><ul><li><span style="float:left;min-width:150px">'.$details['left'].'&nbsp;</span>';

				for($i=1;$i<=$size;$i++) {
					$result .= "<input type='radio' name='$param' id='$param' value='$i'>\n";
				}
				$result .= $details['right'].'&nbsp;</li></ul>';

			}
			elseif($details['type'] == 'radio') {
				$result .= "<br><ul>";
				$i = 0;
				foreach($details['options'] as $option) {
					$result .= "<li>$option<input align='left' name='$param' id='$param' type='".$details['type']."' value='".$i."'></li>\n";
					$i++;
				}
				$result .= '</ul></li>';
			}
			else {
				$result .= "<input name='$param' id='$param' type='".$details['type']."' value='".$details['value']."'></li>\n";
			}
			
		}
		$result .= '</ul></fieldset><hr>';

		return $result;
	}
}

$s = new SurveyParts();

foreach($s->sections as $section) {
	print $section;
}

?>

<fieldset>
<input name='forceNewSession' value='true' type='hidden'>
<input type='hidden' name='prevPage' value='survey' />
<button type='submit' value='Submit'>Submit</button>
</fieldset>
</form>
