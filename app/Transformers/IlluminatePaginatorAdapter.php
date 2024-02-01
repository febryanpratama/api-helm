<?php
 
/**
 * This is a custom implementation of League\Fractal\Pagination\IlluminatePaginatorAdapter
 *
 * As of March 2019 Fractal doesn't have a paginator for Laravel simplePaginate()
 */
 
namespace App\Transformers;
 
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use League\Fractal\Pagination\PaginatorInterface;
 
/**
 * A paginator adapter for illuminate/pagination.
 *
 * @author Danny Herran <me@dannyherran.com>
 */
class IlluminatePaginatorAdapter implements PaginatorInterface
{
    // protected LengthAwarePaginator $paginator;

    /**
     * Create a new illuminate pagination adapter.
     */
    public function __construct(LengthAwarePaginator $paginator)
    {
        $this->paginator = $paginator;
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentPage(): int
    {
        return $this->paginator->currentPage();
    }

    /**
     * {@inheritDoc}
     */
    public function getLastPage(): int
    {
        return $this->paginator->lastPage();
    }

    /**
     * {@inheritDoc}
     */
    public function getTotal(): int
    {
        return $this->paginator->total();
    }

    /**
     * {@inheritDoc}
     */
    public function getCount(): int
    {
        return $this->paginator->count();
    }

    /**
     * {@inheritDoc}
     */
    public function getPerPage(): int
    {
        return $this->paginator->perPage();
    }

    /**
     * {@inheritDoc}
     */
    public function getUrl(int $page): string
    {
        return $this->paginator->url($page);
    }

    /**
     * Get the paginator instance.
     */
    public function getPaginator(): LengthAwarePaginator
    {
        return $this->paginator;
    }
}