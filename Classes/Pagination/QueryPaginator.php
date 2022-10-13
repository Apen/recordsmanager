<?php

declare(strict_types=1);

namespace Sng\Recordsmanager\Pagination;

use Sng\Recordsmanager\Utility\Query;
use TYPO3\CMS\Core\Pagination\AbstractPaginator;

final class QueryPaginator extends AbstractPaginator
{
    private Query $query;

    private array $paginatedItems = [];

    public function __construct(
        Query $query,
        int $currentPageNumber = 1,
        int $itemsPerPage = 10
    ) {
        $this->query = $query;
        $this->setCurrentPageNumber($currentPageNumber);
        $this->setItemsPerPage($itemsPerPage);
        $this->updateInternalState();
    }

    /**
     * @return iterable|array
     */
    public function getPaginatedItems(): iterable
    {
        return $this->paginatedItems;
    }

    protected function updatePaginatedItems(int $itemsPerPage, int $offset): void
    {
        $this->query->setLimit($offset . ',' . $itemsPerPage);
        $this->query->execQuery();

        $this->paginatedItems = $this->query->getRows();
    }

    protected function getTotalAmountOfItems(): int
    {
        return $this->query->getNbRowsFromStatement();
    }

    protected function getAmountOfItemsOnCurrentPage(): int
    {
        return count($this->paginatedItems);
    }
}
