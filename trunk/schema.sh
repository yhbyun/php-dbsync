#!/usr/bin/php
<?php

require_once 'config.php';

require_once 'DbSync/Table/SchemaTable.php';
require_once 'DbSync/Controller/SchemaController.php';

$console = new DbSync_Console();
$console->parse();


$controller = new DbSync_Controller_SchemaController($config);
$controller->dispatch($console);