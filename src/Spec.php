<?php

namespace Zenstruck\Porpaginas;

use Zenstruck\Porpaginas\Specification\Filter\Equal;
use Zenstruck\Porpaginas\Specification\Filter\GreaterThan;
use Zenstruck\Porpaginas\Specification\Filter\GreaterThanOrEqual;
use Zenstruck\Porpaginas\Specification\Filter\In;
use Zenstruck\Porpaginas\Specification\Filter\IsNotNull;
use Zenstruck\Porpaginas\Specification\Filter\IsNull;
use Zenstruck\Porpaginas\Specification\Filter\LessThan;
use Zenstruck\Porpaginas\Specification\Filter\LessThanOrEqual;
use Zenstruck\Porpaginas\Specification\Filter\Like;
use Zenstruck\Porpaginas\Specification\Filter\NotEqual;
use Zenstruck\Porpaginas\Specification\Filter\NotIn;
use Zenstruck\Porpaginas\Specification\Filter\NotLike;
use Zenstruck\Porpaginas\Specification\Logic\AndX;
use Zenstruck\Porpaginas\Specification\Logic\OrX;
use Zenstruck\Porpaginas\Specification\OrderBy;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Spec
{
    public static function andX(...$children): AndX
    {
        return new AndX(...$children);
    }

    public static function orX(...$children): OrX
    {
        return new OrX(...$children);
    }

    public static function like(string $field, $value): Like
    {
        return new Like($field, $value);
    }

    public static function notLike(string $field, $value): NotLike
    {
        return new NotLike($field, $value);
    }

    public static function contains(string $field, $value): Like
    {
        return Like::contains($field, $value);
    }

    public static function notContains(string $field, $value): NotLike
    {
        return NotLike::contains($field, $value);
    }

    public static function beginsWith(string $field, $value): Like
    {
        return Like::beginsWith($field, $value);
    }

    public static function notBeginningWith(string $field, $value): NotLike
    {
        return NotLike::beginsWith($field, $value);
    }

    public static function endsWith(string $field, $value): Like
    {
        return Like::endsWith($field, $value);
    }

    public static function notEndingWith(string $field, $value): NotLike
    {
        return NotLike::endsWith($field, $value);
    }

    public static function eq(string $field, $value): Equal
    {
        return new Equal($field, $value);
    }

    public static function neq(string $field, $value): NotEqual
    {
        return new NotEqual($field, $value);
    }

    public static function isNull(string $field): IsNull
    {
        return new IsNull($field);
    }

    public static function isNotNull(string $field): IsNotNull
    {
        return new IsNotNull($field);
    }

    public static function in(string $field, array $value): In
    {
        return new In($field, $value);
    }

    public static function notIn(string $field, array $value): NotIn
    {
        return new NotIn($field, $value);
    }

    public static function lt(string $field, $value): LessThan
    {
        return new LessThan($field, $value);
    }

    public static function lte(string $field, $value): LessThanOrEqual
    {
        return new LessThanOrEqual($field, $value);
    }

    public static function gt(string $field, $value): GreaterThan
    {
        return new GreaterThan($field, $value);
    }

    public static function gte(string $field, $value): GreaterThanOrEqual
    {
        return new GreaterThanOrEqual($field, $value);
    }

    public static function sortAsc(string $field): OrderBy
    {
        return OrderBy::asc($field);
    }

    public static function sortDesc(string $field): OrderBy
    {
        return OrderBy::desc($field);
    }
}
