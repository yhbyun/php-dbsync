<?php

$phar = new Phar('dbsync.phar');
$phar->buildFromDirectory('phar');

$phar->setDefaultStub('index.php');

$signatures=Phar::getSupportedSignatures();

$phar->setSignatureAlgorithm(PHAR::SHA512);

$phar->extractTo('pharTest');