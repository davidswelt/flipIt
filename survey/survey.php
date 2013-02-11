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

<form name='survey' id='survey' method='GET'>

<?php
if(array_key_exists('lab', $_GET)) {
	echo "<input type='hidden' id='lab' name='lab' value='lab'>\n";
}

require_once(dirname(__FILE__).'/SurveyParts.class.php');

$s = new SurveyParts();
$s->printSections();


?>

<fieldset>
<input name='forceNewSession' value='true' type='hidden'>
<input type='hidden' name='prevPage' value='survey' />
<button type='submit' value='Submit'>Submit</button>
</fieldset>
</form>
