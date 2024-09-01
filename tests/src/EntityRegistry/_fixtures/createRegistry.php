<?php

declare(strict_types=1);

return [
    'IntervalStatus' => [
        'description' => 'Статусы',
        'partitionsCount' => 1,
        'xmlPath' => '/IntervalStatuses/IntervalStatus',
        'xmlInsertFileMask' => 'AS_INTVSTAT_*.XML',
        'xmlDeleteFileMask' => 'AS_DEL_INTVSTAT_*.XML',
        'fields' => [
            'INTVSTATID' => [
                'type' => 'int',
                'subType' => '',
                'length' => 10,
                'nullable' => false,
                'isPrimary' => true,
                'isIndex' => false,
                'isPartition' => false,
            ],
            'NAME' => [
                'type' => 'string',
                'subType' => '',
                'length' => 255,
                'nullable' => false,
                'isPrimary' => false,
                'isIndex' => false,
                'isPartition' => false,
            ],
        ],
    ],
    'NormativeDocumentType' => [
        'description' => 'Типы нормативных документов',
        'partitionsCount' => 1,
        'xmlPath' => '/NormativeDocumentTypes/NormativeDocumentType',
        'xmlInsertFileMask' => 'AS_NDOCTYPE_*.XML',
        'xmlDeleteFileMask' => 'AS_DEL_NDOCTYPE_*.XML',
        'fields' => [
            'NDTYPEID' => [
                'type' => 'int',
                'subType' => '',
                'length' => 10,
                'nullable' => false,
                'isPrimary' => true,
                'isIndex' => false,
                'isPartition' => false,
            ],
            'NAME' => [
                'type' => 'string',
                'subType' => '',
                'length' => 255,
                'nullable' => false,
                'isPrimary' => false,
                'isIndex' => false,
                'isPartition' => false,
            ],
        ],
    ],
];
