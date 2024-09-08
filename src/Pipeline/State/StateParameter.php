<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline\State;

/**
 * Список параметров, которые может хранить объект состояния.
 */
enum StateParameter: string
{
    case FIAS_VERSION_NUMBER = 'fias_version_number';
    case FIAS_NEXT_VERSION_NUMBER = 'fias_next_version_number';
    case FIAS_VERSION_ARCHIVE_URL = 'fias_version_archive_url';

    case PATH_TO_DOWNLOAD_FILE = 'path_to_download_file';
    case PATH_TO_EXTRACT_FOLDER = 'path_to_extract_folder';

    case FILES_TO_PROCEED = 'files_to_proceed';
    case FIAS_INFO = 'fias_info';

    case TEST = 'test';
}
