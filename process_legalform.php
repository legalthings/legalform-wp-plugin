<?php

require_once('../../../wp-load.php');
require_once('legalforms.php');

$data = json_decode(file_get_contents('php://input'), true);
$legalforms->process_legalform($data);
