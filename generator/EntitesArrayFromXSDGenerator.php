<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Generator;

use DOMDocument;
use DOMNode;
use DOMXpath;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

/**
 * Объект, который генерирует файл с описаниями сущностей из xsd файлов,
 * поставляемых с ФИАС.
 */
class EntitesArrayFromXSDGenerator
{
    /**
     * Создает файл с описаниемя сущностей ФИАС на основании данных собранных
     * из XSD файлов.
     *
     * @param string $xsdDir
     * @param string $resultFile
     * @param string $defaultEntitesFile
     */
    public function generate(string $xsdDir, string $resultFile, string $defaultEntitesFile): void
    {
        $files = $this->getXSDFilesFromDir($xsdDir);
        $entites = $this->parseEntitesFromFiles($files);
        $defaultEntites = $this->loadDefaultEntites($defaultEntitesFile);
        $resultEntites = $this->mergeEntites($entites, $defaultEntites);

        $fileText = "<?php\n\n";
        $fileText .= 'return ' . var_export($resultEntites, true) . ';';

        file_put_contents($resultFile, $fileText);
    }

    /**
     * Получает массив с файлами XSD из указанной папки.
     *
     * @param string $xsdDir
     *
     * @return string[]
     */
    private function getXSDFilesFromDir(string $xsdDir): array
    {
        $files = [];

        $directoryIterator = new RecursiveDirectoryIterator(
            $xsdDir,
            RecursiveDirectoryIterator::SKIP_DOTS
        );
        $iterator = new RecursiveIteratorIterator($directoryIterator);

        foreach ($iterator as $fileInfo) {
            if (strtolower($fileInfo->getExtension()) === 'xsd') {
                $files[] = (string) $fileInfo->getRealPath();
            }
        }

        return $files;
    }

    /**
     * Получает описания сущностей из XSD файлов.
     *
     * @param string[] $files
     *
     * @return array
     */
    private function parseEntitesFromFiles(array $files): array
    {
        $xsdEntites = [];

        foreach ($files as $file) {
            $entites = $this->parseEntitiesFormFile($file);
            foreach ($entites as $entity) {
                $entityName = $entity['entity_name'] ?? null;
                if ($entityName === null) {
                    throw new RuntimeException("Can't find entity name.");
                }
                unset($entity['entity_name']);
                $xsdEntites[$entityName] = $entity;
            }
        }

        return $xsdEntites;
    }

    /**
     * Получает описание сущности из XSD файла.
     *
     * @param string $filePath
     *
     * @return array
     *
     * @psalm-suppress UndefinedMethod
     */
    private function parseEntitiesFormFile(string $filePath): array
    {
        $entites = [];

        $schema = new DOMDocument();
        $schema->loadXML(file_get_contents($filePath));

        $xpath = new DOMXpath($schema);

        $elements = $xpath->query('//xs:schema/xs:element');
        foreach ($elements as $element) {
            $innerElement = $xpath->query('.//xs:complexType/xs:sequence/xs:element', $element)->item(0);
            $innerElementName = $innerElement->getAttribute('name');

            $entity = [
                'entity_name' => $innerElementName,
                'description' => $xpath->query('.//xs:annotation/xs:documentation', $innerElement)->item(0)->nodeValue,
                'xmlPath' => '/' . $element->getAttribute('name') . '/' . $innerElementName,
                'fields' => $this->extractFieldsDecription($innerElement, $xpath),
            ];

            $entites[] = $entity;
        }

        return $entites;
    }

    /**
     * Создает описания полей по XSD схеме.
     *
     * @param DOMNode  $innerElement
     * @param DOMXpath $xpath
     *
     * @return array
     *
     * @psalm-suppress UndefinedMethod
     */
    private function extractFieldsDecription(DOMNode $innerElement, DOMXpath $xpath): array
    {
        $fieldsList = [];

        $fields = $xpath->query('.//xs:complexType/xs:attribute', $innerElement);
        foreach ($fields as $field) {
            $fieldName = $field->getAttribute('name');

            $type = $field->getAttribute('type');
            if (empty($type)) {
                $type = $xpath->query('.//xs:simpleType/xs:restriction', $field)->item(0)->getAttribute('base');
            }
            $typeArray = $this->convertType($type);

            $fieldArray = [
                'type' => $typeArray['type'] ?? '',
                'subType' => $typeArray['subType'] ?? '',
                'isNullable' => $field->getAttribute('use') !== 'required',
                'description' => $xpath->query('.//xs:annotation/xs:documentation', $field)->item(0)->nodeValue,
            ];

            if ($fieldArray['type'] === 'string') {
                $length = $xpath->query('.//xs:simpleType/xs:restriction/xs:length', $field)->item(0);
                $maxLength = $xpath->query('.//xs:simpleType/xs:restriction/xs:maxLength', $field)->item(0);
                if ($length) {
                    $fieldArray['length'] = (int) $length->getAttribute('value');
                    if ($fieldArray['length'] === 36) {
                        $fieldArray['subType'] = 'uuid';
                    }
                } elseif ($maxLength) {
                    $fieldArray['length'] = (int) $maxLength->getAttribute('value');
                }
            }

            if ($fieldArray['type'] === 'int') {
                $length = $xpath->query('.//xs:simpleType/xs:restriction/xs:totalDigits', $field)->item(0);
                if ($length) {
                    $fieldArray['length'] = (int) $length->getAttribute('value');
                }
            }

            $fieldsList[$fieldName] = $fieldArray;
        }

        return $fieldsList;
    }

    /**
     * Конвертирует XSD тип в тип пригодный для описания сущностей.
     *
     * @param string $type
     *
     * @return array
     */
    private function convertType(string $type): array
    {
        $convertMap = [
            'xs:date' => ['type' => 'string', 'subType' => 'date'],
            'xs:integer' => ['type' => 'int', 'subType' => 'date'],
        ];

        return $convertMap[$type] ?? ['type' => 'string', 'subType' => ''];
    }

    /**
     * Загружает массив с описанием сущностей по умолчанию.
     *
     * @param string $defaultEntitesFile
     *
     * @return array
     *
     * @psalm-suppress UnresolvableInclude
     */
    private function loadDefaultEntites(string $defaultEntitesFile): array
    {
        return include $defaultEntitesFile;
    }

    /**
     * Мержит массив с описанием текущих сущностей и сущностей по умолчанию.
     *
     * @param array $entites
     * @param array $defaultEntites
     *
     * @return array
     */
    private function mergeEntites(array $entites, array $defaultEntites): array
    {
        $resultEntities = [];

        foreach ($entites as $entityName => $entityDescription) {
            $defaultData = $defaultEntites[$entityName] ?? null;
            if ($defaultData !== null) {
                $entityDescription = array_replace_recursive($defaultData, $entityDescription);
            }
            $resultEntities[$entityName] = $entityDescription;
        }

        return $resultEntities;
    }
}
