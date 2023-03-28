<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Pipeline;

/**
 * Список параметров, которые могут храниться в состоянии, передаваемом между задачами.
 */
enum PipelineStateParam: string
{
    case INTERRUPT_PIPELINE = 'interrupt_pipeline';
    case FILES_TO_PROCEED = 'files_to_proceed';
    case FIAS_VERSION = 'fias_version';
    case EXTRACT_TO_FOLDER = 'extract_to';
    case FIAS_INFO = 'fias_info';
    case DOWNLOAD_TO_FILE = 'download_to';
}