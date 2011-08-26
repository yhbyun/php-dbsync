#!/usr/bin/php
<?php

require_once 'config.php';

require_once 'DbSync/Table/DataTable.php';
require_once 'DbSync/Controller/DataController.php';

$console = new DbSync_Console();
$console->parse();

$controller = new DbSync_Controller_DataController($config);
$controller->dispatch($console);