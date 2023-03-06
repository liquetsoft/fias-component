<?php

declare(strict_types=1);

use Liquetsoft\Fias\Component\Downloader\BaseDownloader;
use Liquetsoft\Fias\Component\Helper\FiasLinks;
use Liquetsoft\Fias\Component\Helper\PathHelper;
use Liquetsoft\Fias\Component\HttpTransport\CurlHttpTransport;
use Liquetsoft\Fias\Component\Unpacker\ZipUnpacker;
use Marvin255\FileSystemHelper\FileSystemFactory;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$fs = FileSystemFactory::create();
$downloader = new BaseDownloader(new CurlHttpTransport());
$unpacker = new ZipUnpacker();

$sysTmp = $fs->getTmpDir()->getRealPath();
$tmpFile = new SplFileInfo("{$sysTmp}/xsd_archive");
$tmpDir = new SplFileInfo("{$sysTmp}/xsd_extracted");
$xsdFolder = new SplFileInfo(PathHelper::resource('xsd'));

$fs->removeIfExists($tmpFile);
$fs->mkdirIfNotExist($tmpDir);
$fs->emptyDir($tmpDir);

$downloader->download(FiasLinks::GAR_SCHEMAS->value, $tmpFile);
$unpacker->unpack($tmpFile, $tmpDir);

$fs->removeIfExists($xsdFolder);
$fs->copy($tmpDir, $xsdFolder);
$fs->removeIfExists($tmpDir);
$fs->removeIfExists($tmpFile);
