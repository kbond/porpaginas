# zenstruck/porpaginas

[![Build Status](http://img.shields.io/travis/kbond/porpaginas.svg?style=flat-square)](https://travis-ci.org/kbond/porpaginas)
[![Scrutinizer Code Quality](http://img.shields.io/scrutinizer/g/kbond/porpaginas.svg?style=flat-square)](https://scrutinizer-ci.com/g/kbond/porpaginas/)
[![Code Coverage](http://img.shields.io/scrutinizer/coverage/g/kbond/porpaginas.svg?style=flat-square)](https://scrutinizer-ci.com/g/kbond/porpaginas/)
[![StyleCI](https://styleci.io/repos/42656988/shield)](https://styleci.io/repos/42656988)
[![Latest Stable Version](http://img.shields.io/packagist/v/zenstruck/porpaginas.svg?style=flat-square)](https://packagist.org/packages/zenstruck/porpaginas)
[![License](http://img.shields.io/packagist/l/zenstruck/porpaginas.svg?style=flat-square)](https://packagist.org/packages/zenstruck/porpaginas)

**NOTE**: This library is a fork of [beberlei/porpaginas](https://github.com/beberlei/porpaginas).

This library solves a bunch of issues that comes up with APIs of Repository
classes alot:

- You need different methods for paginatable and non-paginatable finder
  methods.
- You need to expose the underlying data-source and return query objects from
  your repository.
- Serialization of paginators should be easily possible for REST APIs

Both Pagerfanta and KnpLabs Pager don't solve this issue and their APIs are
really problematic in this regard. You need to return the query objects or
adapters for paginators from your repositories to get it working and then use
an API on the controller side to turn them into a paginated result set.

Porpaginas solves this by introducing a sane abstraction for paginated results.
For rendering purposes you can integrate with either Pagerfanta or KnpLabs
Pager, this library is not about reimplementating the view part of pagination.

Central part of this library is the interface `Result`:

```php
namespace Zenstruck\Porpaginas;

interface Result extends \Countable, \IteratorAggregate, \JsonSerializable
{
    public function take(int $offset, int $limit): Page;

    /**
     * Return the number of all results in the paginatable.
     */
    public function count(): int;

    /**
     * Return an iterator over all results of the paginatable.
     */
    public function getIterator(): \Iterator;
}
```

This API offers you two ways to iterate over the paginatable result,
either the full result or a paginated window of the result using `take()`.
One drawback is that the query is always lazily executed inside
the `Result` and not directly in the repository.

The `Page` interface returned from `Result::take()` looks like this:

```php
namespace Zenstruck\Porpaginas;

interface Page extends \Countable, \IteratorAggregate, \JsonSerializable
{
    /**
     * Return the number of results on the currrent page.
     */
    public function count(): int;

    /**
     * Return the number of ALL results in the paginatable..
     */
    public function totalCount(): int;

    /**
     * Return an iterator over selected windows of results of the paginatable.
     */
    public function getIterator(): \Iterator;
}
```

## Supported Backends

- Array
- Doctrine ORM
- Doctrine DBAL

## Example

Take the following example using Doctrine ORM:

```php
use Zenstruck\Porpaginas\Result;

class UserRepository extends EntityRepository
{
    public function findAllUsers(): Result
    {
        $qb = $this->createQueryBuilder('u')->orderBy('u.username');

        return new ORMQueryResult($qb);
    }
}

class UserController
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function listAction(Request $request)
    {
        $result = $this->userRepository->findAllUsers();
        // no filtering by page, iterate full result

        return array('users' => $result);
    }

    public function porpaginasListAction(Request $request)
    {
        $result = $this->userRepository->findAllUsers();

        $paginator = $result->take(($request->get('page', 1)-1) * 20, 20);

        return array('users' => $paginator);
    }
}
```

This library also comes with a pagination helper. Using the `Pager`, the above controller's
`porpaginasListAction()` could be re-written as follows:

```php
use Zenstruck\Porpaginas\Pager;

class UserController
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function porpaginasListAction(Request $request)
    {
        $result = $this->userRepository->findAllUsers();

        return array('users' => $result->paginate($request->get('page', 1)));
    }
}
```
