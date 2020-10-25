<?php

use Liquetsoft\Fias\Component\Downloader\CurlDownloader;
use Liquetsoft\Fias\Component\FiasInformer\SoapFiasInformer;
use Liquetsoft\Fias\Component\Helper\FileSystemHelper;
use Liquetsoft\Fias\Component\Helper\PathHelper;
use Liquetsoft\Fias\Component\Unpacker\ZipUnpacker;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$informer = new SoapFiasInformer();
$downloader = new CurlDownloader();
$unpack = new ZipUnpacker();
$sysTmp = __DIR__;
$tmpFile = new SplFileInfo("{$sysTmp}/archive");
$tmpDir = new SplFileInfo("{$sysTmp}/extracted");
$xsdFolder = new SplFileInfo(PathHelper::resource('xsd'));

if (!mkdir($tmpDir->getPathname(), 0777, true)) {
    throw new RuntimeException("Can't create temp dir.");
}

$deltas = $informer->getDeltaList();
$version = reset($deltas);
if (empty($version) || !$version->hasResult()) {
    throw new RuntimeException("Can't find any version of FIAS.");
}

$downloader->download($version->getUrl(), $tmpFile);
$unpack->unpack($tmpFile, $tmpDir);

FileSystemHelper::remove($xsdFolder);
FileSystemHelper::move(new SplFileInfo($tmpDir->getPathname() . '/Schemas'), $xsdFolder);
FileSystemHelper::remove($tmpDir);
FileSystemHelper::remove($tmpFile);
