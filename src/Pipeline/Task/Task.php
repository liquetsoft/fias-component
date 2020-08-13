<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\Task;

use Exception;
use Liquetsoft\Fias\Component\Pipeline\State\State;

/**
 * Интерфейс для объекта, который производит одну атомарную операцию,
 * необходимую для загрузки данных ФИАС из файлов в базу данных.
 */
interface Task
{
    const FIAS_VERSION_PARAM = 'fias_version';

    const FIAS_INFO_PARAM = 'fias_info';

    const FIAS_SIZE = 'fias_size';

    const DOWNLOAD_TO_FILE_PARAM = 'download_to';

    const EXTRACT_TO_FOLDER_PARAM = 'extract_to';

    const FILES_TO_INSERT_PARAM = 'files_to_insert';

    const FILES_TO_DELETE_PARAM = 'files_to_delete';

    const DOWNLOAD_FILE_TYPE = 'download_file_type';

    /**
     * Запускает задачу на исполнение.
     *
     * @param State $state
     *
     * @throws Exception
     */
    public function run(State $state): void;
}
