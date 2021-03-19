<?php
include_once("utils.php")

defined('KB_URL') or define('KB_URL', "https://kb_coke.lamp2020.local/jsonrpc.php");
defined('KB_TOKEN') or define('KB_TOKEN', "a0a78eae875cdc6a3235072dfta6f64d06f56dff225c1");


importFileToKBTask('../data/sampleProject_export.csv');
getKBStructure();


?>