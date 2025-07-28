<?php


date_default_timezone_set("Asia/Jakarta");
$tanggalhariini = date("Y-m-d");
$jamhariini = date("H:i:sa");
$saatini = $tanggalhariini. " ".$jamhariini;
$saatini =str_replace("am", "", $saatini);
$saatini =str_replace("pm", "", $saatini);


?>