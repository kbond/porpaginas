<?php

namespace Zenstruck\Porpaginas\Doctrine\ORM;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Zenstruck\Porpaginas\Callback\CallbackPage;
use Zenstruck\Porpaginas\Result;

/**
 * @author Florian Klein <florian.klein@free.fr>
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class RSMQueryResult implements Result
{
    private $em;
    private $rsm;
    private $qb;
    private $countQueryBuilderModifier;
    private $count;

    /**
     * @param EntityManagerInterface  $em
     * @param ResultSetMappingBuilder $rsm
     * @param QueryBuilder            $qb
     * @param callable|null           $countQueryBuilderModifier
     */
    public function __construct(EntityManagerInterface $em, ResultSetMappingBuilder $rsm, QueryBuilder $qb, callable $countQueryBuilderModifier = null)
    {
        $this->em = $em;
        $this->rsm = $rsm;
        $this->qb = $qb;
        $this->countQueryBuilderModifier = $countQueryBuilderModifier ?: function (QueryBuilder $qb) {
            return $qb->select('COUNT(*)');
        };
    }

    /**
     * {@inheritdoc}
     */
    public function take($offset, $limit)
    {
        $qb = clone $this->qb;
        $results = function ($offset, $limit) use ($qb) {
            $qb->setFirstResult($offset)
                ->setMaxResults($limit);

            $query = $this->em->createNativeQuery($qb->getSql(), $this->rsm);
            $query->setParameters($qb->getParameters());

            return $query->execute();
        };

        return new CallbackPage($results, [$this, 'count'], $offset, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        if ($this->count !== null) {
            return $this->count;
        }

        $qb = clone $this->qb;

        call_user_func($this->countQueryBuilderModifier, $qb);

        return $this->count = (int) $qb->execute()->fetchColumn();
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        $query = $this->em->createNativeQuery($this->qb->getSql(), $this->rsm);
        $query->setParameters($this->qb->getParameters());

        return $query->iterate();
    }
}
