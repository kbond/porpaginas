<?php

namespace Zenstruck\Porpaginas\Specification\Filter;

use Zenstruck\Porpaginas\Specification\Field;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class Comparison extends Field
{
    private $value;

    /**
     * @param mixed $value
     */
    public function __construct(string $field, $value)
    {
        parent::__construct($field);

        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function value()
    {
        return $this->value;
    }
}
