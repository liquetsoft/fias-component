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
class EntitiesArrayFromXSDGenerator
{
    /**
     * Создает файл с описаниями сущностей ФИАС на основании данных собранных
     * из XSD файлов.
     *
     * @param string $xsdDir
     * @param string $resultFile
     * @param string $defaultEntitiesFile
     */
    public function generate(string $xsdDir, string $resultFile, string $defaultEntitiesFile): void
    {
        $files = $this->getXSDFilesFromDir($xsdDir);
        $entities = $this->parseEntitiesFromFiles($files);
        $defaultEntities = $this->loadDefaultEntities($defaultEntitiesFile);
        $resultEntities = $this->mergeEntities($entities, $defaultEntities);

        $fileText = "<?php\n\n";
        $fileText .= 'return ' . var_export($resultEntities, true) . ';';

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
    private function parseEntitiesFromFiles(array $files): array
    {
        $xsdEntities = [];

        foreach ($files as $file) {
            $entities = $this->parseEntitiesFormFile($file);
            foreach ($entities as $entity) {
                $entityName = $entity['entity_name'] ?? null;
                if ($entityName === null) {
                    throw new RuntimeException("Can't find entity name.");
                }
                unset($entity['entity_name']);
                $xsdEntities[$entityName] = $entity;
            }
        }

        return $xsdEntities;
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
        $entities = [];

        $schema = new DOMDocument();
        $schema->loadXML(file_get_contents($filePath));

        $xpath = new DOMXpath($schema);

        $elements = $xpath->query('//xs:schema/xs:element');
        foreach ($elements as $element) {
            $innerElement = $xpath->query('.//xs:complexType/xs:sequence/xs:element', $element)->item(0);
            $innerElementName = $innerElement->getAttribute('name');

            $entity = [
                'entity_name' => $innerElementName === 'Object' ? 'AddressObject' : $innerElementName,
                'description' => $xpath->query('.//xs:annotation/xs:documentation', $innerElement)->item(0)->nodeValue,
                'xmlPath' => '/' . $element->getAttribute('name') . '/' . $innerElementName,
                'fields' => $this->extractFieldsDescription($innerElement, $xpath),
            ];

            $entities[] = $entity;
        }

        return $entities;
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
    private function extractFieldsDescription(DOMNode $innerElement, DOMXpath $xpath): array
    {
        $fieldsList = [];

        $fields = $xpath->query('.//xs:complexType/xs:attribute', $innerElement);
        foreach ($fields as $field) {
            $fieldName = $field->getAttribute('name');
            $fieldsList[$fieldName] = $this->extractFieldDescription($field, $xpath);
        }

        return $fieldsList;
    }

    /**
     * Получает все данные поля из описания.
     *
     * @param DOMNode  $field
     * @param DOMXpath $xpath
     *
     * @return array
     *
     * @psalm-suppress UndefinedMethod
     */
    private function extractFieldDescription(DOMNode $field, DOMXpath $xpath): array
    {
        $typeArray = $this->extractTypeArray($field, $xpath);

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

        return $fieldArray;
    }

    /**
     * Получает тип поля из описания.
     *
     * @param DOMNode  $field
     * @param DOMXpath $xpath
     *
     * @return array
     *
     * @psalm-suppress UndefinedMethod
     */
    private function extractTypeArray(DOMNode $field, DOMXpath $xpath): array
    {
        $type = $field->getAttribute('type');
        if (empty($type)) {
            $type = $xpath->query('.//xs:simpleType/xs:restriction', $field)->item(0)->getAttribute('base');
        }

        return $this->convertType($type);
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
            'xs:integer' => ['type' => 'int', 'subType' => ''],
            'xs:int' => ['type' => 'int', 'subType' => ''],
            'xs:byte' => ['type' => 'int', 'subType' => ''],
        ];

        return $convertMap[$type] ?? ['type' => 'string', 'subType' => ''];
    }

    /**
     * Загружает массив с описанием сущностей по умолчанию.
     *
     * @param string $defaultEntitiesFile
     *
     * @return array
     *
     * @psalm-suppress UnresolvableInclude
     */
    private function loadDefaultEntities(string $defaultEntitiesFile): array
    {
        return include $defaultEntitiesFile;
    }

    /**
     * Объединяет массив с описанием текущих сущностей и сущностей по умолчанию.
     *
     * @param array $entities
     * @param array $defaultEntities
     *
     * @return array
     */
    private function mergeEntities(array $entities, array $defaultEntities): array
    {
        $resultEntities = [];

        foreach ($entities as $entityName => $entityDescription) {
            $defaultData = $defaultEntities[$entityName] ?? null;
            if ($defaultData !== null) {
                $entityDescription = array_replace_recursive($defaultData, $entityDescription);
            }
            $resultEntities[$entityName] = $entityDescription;
        }

        return $resultEntities;
    }
}
