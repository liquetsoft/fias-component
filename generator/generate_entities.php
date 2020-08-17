<?php

use Liquetsoft\Fias\Component\Generator\EntitesArrayFromXSDGenerator;
use Liquetsoft\Fias\Component\Helper\PathHelper;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$entitiesArrayGenerator = new EntitesArrayFromXSDGenerator();
$entitiesArrayGenerator->generate(
    PathHelper::resource('xsd'),
    PathHelper::resource('fias_entites.php'),
    PathHelper::resource('fias_entites_default.php'),
);
