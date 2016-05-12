<?php

namespace Zenstruck\Porpaginas\Doctrine;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Zenstruck\Porpaginas\Callback\CallbackPage;
use Zenstruck\Porpaginas\Result;

final class ORMQueryResult implements Result
{
    private $query;
    private $fetchCollection;
    private $count;

    /**
     * @param Query|QueryBuilder $query
     * @param bool               $fetchCollection
     */
    public function __construct($query, $fetchCollection = true)
    {
        if ($query instanceof QueryBuilder) {
            $query = $query->getQuery();
        }

        $this->query = $query;
        $this->fetchCollection = $fetchCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function take($offset, $limit)
    {
        $results = function ($offset, $limit) {
            return iterator_to_array($this->createPaginator($offset, $limit));
        };

        return new CallbackPage($results, [$this, 'count'], $offset, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        if (null !== $this->count) {
            return $this->count;
        }

        return $this->count = count($this->createPaginator(0, 1));
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        foreach ($this->query->iterate() as $row) {
            yield $row[0];
        }
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return Paginator
     */
    private function createPaginator($offset, $limit)
    {
        $query = clone $this->query;
        $query->setParameters($this->query->getParameters());

        foreach ($this->query->getHints() as $name => $value) {
            $query->setHint($name, $value);
        }

        $query->setFirstResult($offset)->setMaxResults($limit);

        return new Paginator($query, $this->fetchCollection);
    }
}