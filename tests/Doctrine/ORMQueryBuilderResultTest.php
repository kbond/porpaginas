<?php

namespace Zenstruck\Porpaginas\Tests\Doctrine;

use Zenstruck\Porpaginas\Doctrine\ORMQueryResult;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class ORMQueryBuilderResultTest extends DoctrineResultTestCase
{
    protected function createResultWithItems($count)
    {
        $entityManager = $this->setupEntityManager($count);
        $qb = $entityManager->createQueryBuilder()
            ->select('e')
            ->from(DoctrineOrmEntity::class, 'e');

        return new ORMQueryResult($qb);
    }
}