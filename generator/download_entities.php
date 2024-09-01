<?php

declare(strict_types=1);

use Liquetsoft\Fias\Component\Downloader\CurlDownloader;
use Liquetsoft\Fias\Component\Downloader\DownloaderImpl;
use Liquetsoft\Fias\Component\Helper\PathHelper;
use Liquetsoft\Fias\Component\HttpTransport\HttpTransportCurl;
use Liquetsoft\Fias\Component\Unpacker\ZipUnpacker;
use Marvin255\FileSystemHelper\FileSystemFactory;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$xsdUrl = 'https://fias.nalog.ru/docs/gar_schemas.zip';

$fs = FileSystemFactory::create();
$httpTransport = new HttpTransportCurl();
$downloader = new DownloaderImpl($httpTransport);
$unpack = new ZipUnpacker();
$sysTmp = __DIR__;
$tmpFile = new SplFileInfo("{$sysTmp}/archive");
$tmpDir = new SplFileInfo("{$sysTmp}/extracted");
$xsdFolder = new SplFileInfo(PathHelper::resource('xsd'));

$fs->removeIfExists($tmpFile);
$fs->mkdirIfNotExist($tmpDir);
$fs->emptyDir($tmpDir);

$downloader->download($xsdUrl, $tmpFile);
$unpack->unpack($tmpFile, $tmpDir);

$fs->removeIfExists($xsdFolder);
$fs->rename($tmpDir->getPathname(), $xsdFolder);
$fs->removeIfExists($tmpDir);
$fs->removeIfExists($tmpFile);
