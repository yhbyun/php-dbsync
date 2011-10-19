#!/usr/bin/php
<?php

require_once 'config.php';

require_once 'DbSync/Table/Trigger.php';
require_once 'DbSync/Controller/TriggerController.php';

$console = new DbSync_Console();
$console->parse();

$controller = new DbSync_Controller_TriggerController($config);
$controller->dispatch($console);