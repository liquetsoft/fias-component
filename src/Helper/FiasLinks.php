<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Helper;

/**
 * Список ссылок на основные части ФИАС.
 */
enum FiasLinks: string
{
    case GAR_SCHEMAS = 'https://fias.nalog.ru/docs/gar_schemas.zip';
    case INFORMER_ALL = 'https://fias.nalog.ru/WebServices/Public/GetAllDownloadFileInfo';
    case INFORMER_LAST = 'https://fias.nalog.ru/WebServices/Public/GetLastDownloadFileInfo';
}
