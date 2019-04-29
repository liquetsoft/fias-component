<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Liquetsoft\Fias\Component\Downloader\Downloader;
use Liquetsoft\Fias\Component\FiasInformer\InformerResponse;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Exception\TaskException;
use SplFileInfo;

/**
 * Задача, которая скачивает архив из текущего состояния по ссылке
 * в указанный в состоянии локальный файл.
 */
class DownloadTask implements Task
{
    /**
     * @var Downloader
     */
    protected $downloader;

    /**
     * @param Downloader $downloader
     */
    public function __construct(Downloader $downloader)
    {
        $this->downloader = $downloader;
    }

    /**
     * @inheritdoc
     */
    public function run(State $state): void
    {
        $info = $state->getParameter('fiasInfo');
        if (!($info instanceof InformerResponse)) {
            throw new TaskException(
                "State parameter 'fiasInfo' must be an '" . InformerResponse::class . "' instance for '" . self::class . "'."
            );
        }

        $localFile = $state->getParameter('downloadTo');
        if (!($localFile instanceof SplFileInfo)) {
            throw new TaskException(
                "State parameter 'downloadTo' must be an '" . SplFileInfo::class . "' instance for '" . self::class . "'."
            );
        }

        $this->downloader->download($info->getUrl(), $localFile);
    }
}
