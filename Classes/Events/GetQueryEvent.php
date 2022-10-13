<?php

declare(strict_types=1);

namespace Sng\Recordsmanager\Events;

final class GetQueryEvent
{
    private array $query;

    public function __construct(array $query)
    {
        $this->query = $query;
    }

    public function getQuery(): array
    {
        return $this->query;
    }

    public function setQuery(array $query): void
    {
        $this->query = $query;
    }
}
