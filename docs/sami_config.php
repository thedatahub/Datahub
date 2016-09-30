<?php

use Sami\Sami;
use Symfony\Component\Finder\Finder;

require_once __DIR__.'/../app/autoload.php';

$sep = DIRECTORY_SEPARATOR;
$rootPath = __DIR__.$sep.'..'.$sep;
$srcPath = $rootPath.'src';


$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->exclude('Resources')
    ->exclude('Tests')
    ->in($srcPath)
;

$sami = new Sami($iterator, array(
    // 'theme'                => 'symfony',
    // 'title'                => 'Symfony2 API',
    'build_dir'            => __DIR__.$sep.'build'.$sep.'code',
    'cache_dir'            => __DIR__.$sep.'cache'.$sep.'code',
    // 'default_opened_level' => 2,
));

return $sami;
