<?php

use DBorsatto\ObjectXml\Node;
use DBorsatto\ObjectXml\Manager;

require __DIR__.'/../vendor/autoload.php';

$manager = new Manager();

$node = $manager->parseFile('file.xml');
