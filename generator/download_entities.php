<?php

declare(strict_types=1);

use Liquetsoft\Fias\Component\Downloader\CurlDownloader;
use Liquetsoft\Fias\Component\FiasInformer\SoapFiasInformer;
use Liquetsoft\Fias\Component\Helper\PathHelper;
use Liquetsoft\Fias\Component\Unpacker\ZipUnpacker;
use Marvin255\FileSystemHelper\FileSystemFactory;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$fs = FileSystemFactory::create();
$informer = new SoapFiasInformer();
$downloader = new CurlDownloader();
$unpack = new ZipUnpacker();
$sysTmp = __DIR__;
$tmpFile = new SplFileInfo("{$sysTmp}/archive");
$tmpDir = new SplFileInfo("{$sysTmp}/extracted");
$xsdFolder = new SplFileInfo(PathHelper::resource('xsd'));

$fs->removeIfExists($tmpFile);
$fs->mkdirIfNotExist($tmpDir);
$fs->emptyDir($tmpDir);

$deltas = $informer->getDeltaList();
$version = reset($deltas);
if (empty($version) || !$version->hasResult()) {
    throw new RuntimeException("Can't find any version of FIAS.");
}

$downloader->download($version->getUrl(), $tmpFile);
$unpack->unpack($tmpFile, $tmpDir);

$fs->removeIfExists($xsdFolder);
$fs->rename($tmpDir->getPathname() . '/Schemas', $xsdFolder);
$fs->removeIfExists($tmpDir);
$fs->removeIfExists($tmpFile);
