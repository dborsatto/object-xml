<?php

use DBorsatto\ObjectXml\Manager;
use DBorsatto\ObjectXml\Node;

require __DIR__.'/../vendor/autoload.php';

$manager = new Manager();
$node = $manager->parseFile('file.xml');

$node = Node::create('root');
