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
	width: 500px;
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
	float: left;
}
form label {
	float: left;
	font-size: 13px;
	width: 200px;
}
form input:not([type=radio]),
form textarea {
	background: #ffffff;
	border: none;
	-moz-border-radius: 3px;
	-webkit-border-radius: 3px;
	-khtml-border-radius: 3px;
	border-radius: 3px;
	font: italic 13px Georgia, "Times New Roman", Times, serif;
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
<fieldset>
		<legend>Survey</legend>
<?php

$id = 'MTurk Id';
if(array_key_exists('lab', $_GET)) {
	$id = 'full name (first and last)';

	echo "<input type='hidden' id='lab' name='lab' value='lab'>\n";
}

$survey = array(
	'mturk_id' => array('label'=> "What is your $id?")
);

?>

<p>Please answer the following survey questions before playing the game.</p>
<ul>

<?php

foreach($survey as $param => $details) {
	if(!array_key_exists('value', $details)) {
   	$details['value'] = '';
	}
	if(!array_key_exists('type', $details)) {
   	$details['type'] = 'text';
	}
	if(!array_key_exists('label', $details)) {
   	$details['label'] = "What is your $param";
	}

	echo "<li><label for='$param'>".$details['label']."</label>";
	echo "<input name='$param' id='$param' type='".$details['type']."' value='".$details['value']."'></li>\n";
	
}

?>
</ul>

<input name='forceNewSession' value='true' type='hidden'>
<input type='hidden' name='prevPage' value='survey' />
<button type='submit' value='Submit'>Submit</button>
</fieldset>
</form>         
