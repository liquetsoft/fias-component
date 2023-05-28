<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests\FiasEntity;

use Liquetsoft\Fias\Component\Exception\FiasEntityException;
use Liquetsoft\Fias\Component\FiasEntity\FiasEntityRepositoryPhpFile;
use Liquetsoft\Fias\Component\Tests\BaseCase;

/**
 * Тест для объекта, который хранит список всех сущностей во внутреннем массиве.
 *
 * @internal
 */
class FiasEntityRepositoryPhpFileTest extends BaseCase
{
    /**
     * Проверяет, что объект выбросит исключение, если указанный файл не существует.
     */
    public function testGetAllEntitiesNonExistedFileException(): void
    {
        $path = __DIR__ . '/_fixtures/FiasEntityRepositoryPhpFileTest/non_existed.php';

        $repository = new FiasEntityRepositoryPhpFile($path);

        $this->expectException(FiasEntityException::class);
        $this->expectExceptionMessage('must exists and be readable');
        $repository->getAllEntities();
    }

    /**
     * Проверяет, что объект выбросит исключение, если указанный файл не является php файлом.
     */
    public function testGetAllEntitiesNonPhpFileException(): void
    {
        $path = __DIR__ . '/_fixtures/FiasEntityRepositoryPhpFileTest/testGetAllEntitiesNonPhpFileException.txt';

        $repository = new FiasEntityRepositoryPhpFile($path);

        $this->expectException(FiasEntityException::class);
        $this->expectExceptionMessage("must has 'php' extension");
        $repository->getAllEntities();
    }

    /**
     * Проверяет, что объект выбросит исключение, если файл не вернет массив.
     */
    public function testGetAllEntitiesNonArrayException(): void
    {
        $path = __DIR__ . '/_fixtures/FiasEntityRepositoryPhpFileTest/testGetAllEntitiesNonArrayException.php';

        $repository = new FiasEntityRepositoryPhpFile($path);

        $this->expectException(FiasEntityException::class);
        $this->expectExceptionMessage('file must return an array');
        $repository->getAllEntities();
    }

    /**
     * Проверяет, что объект выбросит исключение, если файл вернет массив,
     * в котором есть вложенне элементы - не массивы.
     */
    public function testGetAllEntitiesNonArrayItemException(): void
    {
        $path = __DIR__ . '/_fixtures/FiasEntityRepositoryPhpFileTest/testGetAllEntitiesNonArrayItemException.php';

        $repository = new FiasEntityRepositoryPhpFile($path);

        $this->expectException(FiasEntityException::class);
        $this->expectExceptionMessage('All items in the php array must also be arrays');
        $repository->getAllEntities();
    }

    /**
     * Проверяет, что объект перехватит исключение от php файла с сущностями.
     */
    public function testGetAllEntitiesPhpFileException(): void
    {
        $path = __DIR__ . '/_fixtures/FiasEntityRepositoryPhpFileTest/testGetAllEntitiesPhpFileException.php';

        $repository = new FiasEntityRepositoryPhpFile($path);

        $this->expectException(FiasEntityException::class);
        $this->expectExceptionMessage('test');
        $repository->getAllEntities();
    }

    /**
     * Проверяет, что объект вернет список всех сущностей.
     */
    public function testGetAllEntities(): void
    {
        $path = __DIR__ . '/_fixtures/FiasEntityRepositoryPhpFileTest/testGetAllEntities.php';

        $repository = new FiasEntityRepositoryPhpFile($path);
        $array = [];
        foreach ($repository->getAllEntities() as $item) {
            $array[] = $item;
        }

        $this->assertCount(2, $array);
        $this->assertSame('test', $array[0]->getName());
        $this->assertSame('test1', $array[1]->getName());
    }

    /**
     * Проверяет, что объект вернет правду, если содержит указанную сущность.
     *
     * @dataProvider provideHasEntity
     */
    public function testHasEntity(string $searchName, bool $awaits): void
    {
        $path = __DIR__ . '/_fixtures/FiasEntityRepositoryPhpFileTest/testHasEntity.php';

        $repository = new FiasEntityRepositoryPhpFile($path);
        $hasEntity = $repository->hasEntity($searchName);

        $this->assertSame($awaits, $hasEntity);
    }

    public function provideHasEntity(): array
    {
        return [
            'has entity' => [
                'test',
                true,
            ],
            "doesn't have entity" => [
                'test_1',
                false,
            ],
            'wrong case' => [
                'TeSt',
                true,
            ],
            'whitespaces' => [
                '  test  ',
                true,
            ],
        ];
    }

    /**
     * Проверяет, что объект вернет сущность по ее имени.
     *
     * @dataProvider provideGetEntity
     */
    public function testGetEntity(string $searchName, \Exception $exception = null): void
    {
        $path = __DIR__ . '/_fixtures/FiasEntityRepositoryPhpFileTest/testGetEntity.php';

        $repository = new FiasEntityRepositoryPhpFile($path);

        if ($exception) {
            $this->expectExceptionObject($exception);
        }

        $gotEntity = $repository->getEntity($searchName);

        if ($exception === null) {
            $this->assertSame(strtolower(trim($searchName)), $gotEntity->getName());
        }
    }

    public function provideGetEntity(): array
    {
        return [
            'has entity' => [
                'test',
            ],
            "doesn't have entity" => [
                'test_1',
                new FiasEntityException("Can't find entity with name 'test_1'"),
            ],
            'wrong case' => [
                'TeSt',
            ],
            'whitespaces' => [
                '  test  ',
            ],
        ];
    }
}
