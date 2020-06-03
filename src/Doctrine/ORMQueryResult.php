<?php

namespace Zenstruck\Porpaginas\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Zenstruck\Porpaginas\Callback\CallbackPage;
use Zenstruck\Porpaginas\Page;
use Zenstruck\Porpaginas\Result;

final class ORMQueryResult implements Result
{
    private Query $query;
    private bool $fetchCollection;
    private ?bool $useOutputWalkers;
    private ?int $count = null;

    /**
     * @param Query|QueryBuilder $query
     * @param bool|null          $useOutputWalkers Set to false if query contains only columns
     */
    public function __construct($query, bool $fetchCollection = true, ?bool $useOutputWalkers = null)
    {
        if ($query instanceof QueryBuilder) {
            $query = $query->getQuery();
        }

        $this->query = $query;
        $this->fetchCollection = $fetchCollection;
        $this->useOutputWalkers = $useOutputWalkers;
    }

    public function take(int $offset, int $limit): Page
    {
        return new CallbackPage(
            function ($offset, $limit) {
                return \iterator_to_array($this->paginatorFor(
                    $this->cloneQuery()->setFirstResult($offset)->setMaxResults($limit)
                ));
            },
            [$this, 'count'],
            $offset,
            $limit
        );
    }

    public function count(): int
    {
        if (null !== $this->count) {
            return $this->count;
        }

        return $this->count = \count($this->paginatorFor($this->cloneQuery()));
    }

    public function getIterator(): \Traversable
    {
        $logger = $this->em()->getConfiguration()->getSQLLogger();
        $this->em()->getConfiguration()->setSQLLogger(null);

        foreach (new ORMIterableResultDecorator($this->cloneQuery()->iterate()) as $key => $value) {
            yield $key => $value;

            $this->em()->clear();
        }

        $this->em()->getConfiguration()->setSQLLogger($logger);
    }

    public function batchIterator(int $batchSize = 100): ORMCountableBatchProcessor
    {
        return new ORMCountableBatchProcessor(
            new class($this->cloneQuery(), $this) implements \IteratorAggregate, \Countable {
                private Query $query;
                private Result $result;

                public function __construct(Query $query, Result $result)
                {
                    $this->query = $query;
                    $this->result = $result;
                }

                public function getIterator(): \Traversable
                {
                    return new ORMIterableResultDecorator($this->query->iterate());
                }

                public function count(): int
                {
                    return $this->result->count();
                }
            },
            $this->em(),
            $batchSize
        );
    }

    private function em(): EntityManager
    {
        return $this->query->getEntityManager();
    }

    private function paginatorFor(Query $query): Paginator
    {
        return (new Paginator($query, $this->fetchCollection))->setUseOutputWalkers($this->useOutputWalkers);
    }

    private function cloneQuery(): Query
    {
        $query = clone $this->query;
        $query->setParameters($this->query->getParameters());

        foreach ($this->query->getHints() as $name => $value) {
            $query->setHint($name, $value);
        }

        return $query;
    }
}
