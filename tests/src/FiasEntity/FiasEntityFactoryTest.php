<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasEntity;

use Liquetsoft\Fias\Component\Exception\FiasEntityException;
use Liquetsoft\Fias\Component\FiasEntity\FiasEntityFactory;
use Liquetsoft\Fias\Component\FiasEntity\FiasEntityField;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для фабрики, которая создает сущности.
 *
 * @internal
 */
class FiasEntityFactoryTest extends BaseCase
{
    /**
     * Проверяет, что фабрика выбросит исключение при попытке использовать не массив.
     */
    public function testCreateFromArrayNonArrayException(): void
    {
        $this->expectException(FiasEntityException::class);
        $this->expectExceptionMessage('Param must be an instance of array');
        FiasEntityFactory::createFromArray('test');
    }

    /**
     * Проверяет, что фабрика выбросит исключение при попытке задать неправильное описание поля.
     */
    public function testCreateMalformedFieldException(): void
    {
        $this->expectException(FiasEntityException::class);
        $this->expectExceptionMessage('Field must be an array or implements EntityField');
        FiasEntityFactory::createFromArray(
            [
                'name' => 'entity name',
                'xmlPath' => '/root/test',
                'fields' => ['test'],
            ]
        );
    }

    /**
     * Проверяет, что фабрика создает объект из массива опций.
     */
    public function testCreateFromArray(): void
    {
        $field = $this->getMockBuilder(FiasEntityField::class)->getMock();
        $field->method('getName')->willReturn('test_1');

        $options = [
            'name' => 'entity name',
            'xmlPath' => '/root/test',
            'description' => 'entity description',
            'partitionsCount' => 1,
            'insertFileMask' => '.*',
            'deleteFileMask' => '.*',
            'fields' => [
                $field,
                [
                    'type' => 'string',
                    'subType' => 'date',
                    'name' => 'test_2',
                ],
            ],
        ];

        $entity = FiasEntityFactory::createFromArray($options);

        $this->assertSame($options['name'], $entity->getName());
        $this->assertSame($options['xmlPath'], $entity->getXmlPath());
        $this->assertSame($options['description'], $entity->getDescription());
        $this->assertSame($options['partitionsCount'], $entity->getPartitionsCount());
        $this->assertSame($options['insertFileMask'], $entity->getXmlInsertFileMask());
        $this->assertSame($options['deleteFileMask'], $entity->getXmlDeleteFileMask());
        $this->assertSame($field, $entity->getField('test_1'));
        $this->assertNotNull($entity->getField('test_2'));
    }
}
