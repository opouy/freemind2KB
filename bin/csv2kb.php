<?php
include_once("utils.php");

defined('KB_URL') or define('KB_URL', "https://kb_demo.lamp2020.local/kb_XP5/jsonrpc.php");
defined('KB_TOKEN') or define('KB_TOKEN', "15fad02248d82456d5abb47f11036910d64ccb705e6dedbdc9038eb4dccc");


importFileToKBTask('../data/sampleProject_export.csv');
//getKBStructure();


?>