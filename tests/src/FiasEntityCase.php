<?php

declare(strict_types=1);

namespace Liquetsoft\Fias\Component\Tests;

use Liquetsoft\Fias\Component\FiasEntity\FiasEntity;
use Liquetsoft\Fias\Component\FiasEntity\FiasEntityBinder;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Трэйт, содержащий моки для сущностей и связанных объектов.
 */
trait FiasEntityCase
{
    /**
     * Создает мок для FiasEntityBinder со списком связанных сущностей.
     *
     * @return FiasEntity&MockObject
     */
    public function createEntityMock(): FiasEntity
    {
        /** @var FiasEntity&MockObject */
        $entity = $this->getMockBuilder(FiasEntity::class)->getMock();

        return $entity;
    }

    /**
     * Создает мок для FiasEntityBinder со списком связанных сущностей.
     *
     * @return FiasEntityBinder&MockObject
     */
    public function createFiasEntityBinderMockWithList(array $entites = []): FiasEntityBinder
    {
        $binder = $this->createFiasEntityBinderMock();

        $binder->method('getBoundEntities')->willReturn($entites);

        return $binder;
    }

    /**
     * Создает мок для FiasEntityBinder.
     *
     * @return FiasEntityBinder&MockObject
     */
    public function createFiasEntityBinderMock(): FiasEntityBinder
    {
        /** @var FiasEntityBinder&MockObject */
        $binder = $this->getMockBuilder(FiasEntityBinder::class)->getMock();

        return $binder;
    }
}
