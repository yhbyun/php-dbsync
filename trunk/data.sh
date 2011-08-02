#!/usr/bin/php
<?php

require_once 'config.php';

require_once 'DbSync/Table/Data.php';
require_once 'DbSync/Controller/Data.php';

$console = new DbSync_Console();
$console->parse();

$controller = new DbSync_Controller_Data($config);
$controller->dispatch($console);