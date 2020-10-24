<?php

use Liquetsoft\Fias\Component\Generator\EntitiesArrayFromXSDGenerator;
use Liquetsoft\Fias\Component\Helper\PathHelper;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$entitiesArrayGenerator = new EntitiesArrayFromXSDGenerator();
$entitiesArrayGenerator->generate(
    PathHelper::resource('xsd'),
    PathHelper::resource('fias_entities.php'),
    PathHelper::resource('fias_entities_default.php')
);
