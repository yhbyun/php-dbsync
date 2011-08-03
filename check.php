<?php

if (version_compare(PHP_VERSION, '5.0.0', '<')) {
    echo "PHP 5.0 or newer is required", PHP_EOL;exit;
}

if (!extension_loaded('pdo_mysql')) {
    echo "PDO extension is required", PHP_EOL;exit;
}