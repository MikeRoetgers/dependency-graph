<?php

$file = __DIR__.'/../vendor/autoload.php';
if (!file_exists($file)) {
    throw new RuntimeException("Install dependencies using composer to run the test suite.");
}

$loader = require_once $file;
$loader->add('MikeRoetgers\\DependencyGraph\\', __DIR__);