<?php

namespace Zenstruck\Porpaginas\Specification;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class OrderBy extends Field
{
    public const ASC = 'ASC';
    public const DESC = 'DESC';

    private string $direction;

    private function __construct(string $field, string $direction)
    {
        parent::__construct($field);

        $this->direction = $direction;
    }

    public static function asc(string $field): self
    {
        return new self($field, self::ASC);
    }

    public static function desc(string $field): self
    {
        return new self($field, self::DESC);
    }

    public function direction(): string
    {
        return $this->direction;
    }
}
