<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\Pipeline\State;

use Liquetsoft\Fias\Component\Pipeline\Task\DownloadTask;
use Liquetsoft\Fias\Component\Downloader\Downloader;
use Liquetsoft\Fias\Component\FiasInformer\InformerResponse;
use Liquetsoft\Fias\Component\Pipeline\State\State;
use Liquetsoft\Fias\Component\Exception\TaskException;
use Liquetsoft\Fias\Component\Tests\BaseCase;
use SplFileInfo;

/**
 * Тест для задачи, которая загружает архив ФИАС по ссылке.
 */
class DownloadTaskTest extends BaseCase
{
    /**
     * Проверяет, что объект верно загружает ссылку.
     */
    public function testRun()
    {
        $url = 'http://test.test/test';

        $informerResult = $this->getMockBuilder(InformerResponse::class)->getMock();
        $informerResult->method('hasResult')->will($this->returnValue(true));
        $informerResult->method('getUrl')->will($this->returnValue($url));

        $filePath = __DIR__ . '/test.file';
        $file = new SplFileInfo($filePath);

        $downloader = $this->getMockBuilder(Downloader::class)->getMock();
        $downloader->expects($this->once())->method('download')->with(
            $this->equalTo($url),
            $this->callback(function ($file) use ($filePath) {
                return $file->getPathname() === $filePath;
            })
        );

        $state = $this->getMockBuilder(State::class)->getMock();
        $state->method('getParameter')->will($this->returnCallback(function ($name) use ($informerResult, $file) {
            $return = null;
            if ($name === 'fiasInfo') {
                $return = $informerResult;
            } elseif ($name === 'downloadTo') {
                $return = $file;
            }

            return $return;
        }));

        $task = new DownloadTask($downloader);
        $task->run($state);
    }

    /**
     * Проверяет, что объект выбросит исключение, если в состоянии не указана ссылка на ФИАС.
     */
    public function testRunNoFiasInfoException()
    {
        $downloader = $this->getMockBuilder(Downloader::class)->getMock();

        $file = new SplFileInfo(__DIR__ . '/test.file');

        $state = $this->getMockBuilder(State::class)->getMock();
        $state->method('getParameter')->will($this->returnCallback(function ($name) use ($file) {
            return $name === 'downloadTo' ? $file : null;
        }));

        $task = new DownloadTask($downloader);

        $this->expectException(TaskException::class);
        $task->run($state);
    }

    /**
     * Проверяет, что объект выбросит исключение, если в состоянии не указан путь к локальному файлу.
     */
    public function testRunNoDownloadToInfoException()
    {
        $downloader = $this->getMockBuilder(Downloader::class)->getMock();

        $url = 'http://test.test/test';
        $informerResult = $this->getMockBuilder(InformerResponse::class)->getMock();
        $informerResult->method('hasResult')->will($this->returnValue(true));
        $informerResult->method('getUrl')->will($this->returnValue($url));

        $state = $this->getMockBuilder(State::class)->getMock();
        $state->method('getParameter')->will($this->returnCallback(function ($name) use ($informerResult) {
            return $name === 'fiasInfo' ? $informerResult : null;
        }));

        $task = new DownloadTask($downloader);

        $this->expectException(TaskException::class);
        $task->run($state);
    }
}
