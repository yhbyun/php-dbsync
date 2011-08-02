#!/usr/bin/php
<?php

require_once 'config.php';

require_once 'DbSync/Table/Schema.php';
require_once 'DbSync/Controller/Schema.php';

$console = new DbSync_Console();
$console->parse();


$controller = new DbSync_Controller_Schema($config);
$controller->dispatch($console);