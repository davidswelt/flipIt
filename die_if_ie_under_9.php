<?php

preg_match('/MSIE (.*?);/', $_SERVER['HTTP_USER_AGENT'], $matches);

if (count($matches)>1){
  //Then we're using IE
  $version = intval($matches[1]);

  switch(true){
    case ($version<=8):
die("You are using Internet Explorer 8 or below, which is not compatible with this site. Please download a modern browser (such as <a href='https://www.google.com/intl/en/chrome/browser/'>Google Chrome</a>) and try again.");

      break;

    case ($version==9):
      //IE9!
      break;

    default:
      //You get the idea
  }
}
