<?php

$phar=new Phar('dbsync.phar');
$phar->buildFromDirectory('DbSync');
$phar->buildFromDirectory('dependencies');
$phar->addFile('index.php');
$phar->addFile('init.php');
$phar->addFile('phpdbsync.ini.example');
$phar->addFile('LICENSE.txt');
$phar->addFile('README.txt');

$phar->setDefaultStub('index.php');

$signatures=Phar::getSupportedSignatures();

$phar->setSignatureAlgorithm(PHAR::SHA512);