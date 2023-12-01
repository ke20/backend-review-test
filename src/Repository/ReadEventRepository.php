<?php

namespace App\Repository;

use App\Dto\SearchInput;

interface ReadEventRepository
{
    public function countAll(SearchInput $searchInput): int;

    /**
     * @return array<string>
     */
    public function countByType(SearchInput $searchInput): array;

    /**
     * @return array<int, array<string, int>>
     */
    public function statsByTypePerHour(SearchInput $searchInput): array;

    /**
     * @return array<int, array<string, string>>
     */
    public function getLatest(SearchInput $searchInput): array;

    public function exist(int $id): bool;
}
