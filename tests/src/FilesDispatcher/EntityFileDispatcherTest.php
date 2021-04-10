<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FilesDispatcher;

use Liquetsoft\Fias\Component\EntityDescriptor\EntityDescriptor;
use Liquetsoft\Fias\Component\EntityManager\EntityManager;
use Liquetsoft\Fias\Component\FilesDispatcher\EntityFileDispatcher;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для объекта, который разбивает файлы на потоки по именам сущностей, к которым файлы относятся.
 */
class EntityFileDispatcherTest extends BaseCase
{
    /**
     * Проверяет, что объект правильно разбивает на потоки файлы для вставки.
     */
    public function testDispatchInsert(): void
    {
        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getName')->willReturn('entity');
        $descriptor1 = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor1->method('getName')->willReturn('entity_1');
        $descriptor2 = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor2->method('getName')->willReturn('entity_2');
        $descriptor3 = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor3->method('getName')->willReturn('entity_3');

        $entityManager = $this->getMockBuilder(EntityManager::class)->getMock();
        $entityManager->method('getDescriptorByInsertFile')->will(
            $this->returnCallback(
                function ($fileName) use ($descriptor, $descriptor1, $descriptor2, $descriptor3) {
                    switch ($fileName) {
                        case 'test.xml':
                            $descriptorToReturn = $descriptor;
                            break;
                        case 'test_1.xml':
                            $descriptorToReturn = $descriptor1;
                            break;
                        case 'test_2.xml':
                            $descriptorToReturn = $descriptor2;
                            break;
                        case 'test_3.xml':
                            $descriptorToReturn = $descriptor3;
                            break;
                        default:
                            $descriptorToReturn = null;
                            break;
                    }

                    return $descriptorToReturn;
                }
            )
        );

        $dispatcher = new EntityFileDispatcher(
            $entityManager,
            [
                'entity',
                'entity_1',
            ]
        );
        $dispatchedFiles = $dispatcher->dispatchInsert(
            [
                '/var/test.xml',
                '/var/test/test_1.xml',
                'test_2.xml',
                'test_3.xml',
            ],
            2
        );

        $this->assertSame(
            [
                [
                    '/var/test.xml',
                    'test_2.xml',
                ],
                [
                    '/var/test/test_1.xml',
                    'test_3.xml',
                ],
            ],
            $dispatchedFiles
        );
    }

    /**
     * Проверяет, что объект правильно разбивает на потоки файлы для удаления.
     */
    public function testDispatchDelete(): void
    {
        $descriptor = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor->method('getName')->will($this->returnValue('entity'));
        $descriptor1 = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor1->method('getName')->will($this->returnValue('entity_1'));
        $descriptor2 = $this->getMockBuilder(EntityDescriptor::class)->getMock();
        $descriptor2->method('getName')->will($this->returnValue('entity_2'));

        $entityManager = $this->getMockBuilder(EntityManager::class)->getMock();
        $entityManager->method('getDescriptorByDeleteFile')->will($this->returnCallback(function ($fileName) use ($descriptor, $descriptor1, $descriptor2) {
            switch ($fileName) {
                case 'test.xml':
                    $descriptorToReturn = $descriptor;
                    break;
                case 'test_1.xml':
                    $descriptorToReturn = $descriptor1;
                    break;
                case 'test_2.xml':
                    $descriptorToReturn = $descriptor2;
                    break;
                default:
                    $descriptorToReturn = null;
                    break;
            }

            return $descriptorToReturn;
        }));

        $dispatcher = new EntityFileDispatcher($entityManager, ['entity', 'entity_1']);
        $dispatchedFiles = $dispatcher->dispatchDelete(['/var/test.xml', '/var/test/test_1.xml', 'test_2.xml'], 2);

        $this->assertSame([['/var/test.xml', 'test_2.xml'], ['/var/test/test_1.xml']], $dispatchedFiles);
    }
}
