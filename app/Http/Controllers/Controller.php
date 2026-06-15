<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Build the page number window and return common pagination view data.
     *
     * @param  int  $currentPage  The active page number.
     * @param  int  $totalPages   Total pages returned by the API.
     * @param  int  $window       Number of pages to show either side of the current page.
     * @return array{currentPage: int, totalPages: int, pageNumbers: int[]}
     */
    protected function paginationData(int $currentPage, int $totalPages, int $window = 2): array
    {
        $pageNumbers = [];
        for ($i = 1; $i <= $totalPages; $i++) {
            if ($i === 1 || $i === $totalPages || ($i >= $currentPage - $window && $i <= $currentPage + $window)) {
                $pageNumbers[] = $i;
            }
        }

        return compact('currentPage', 'totalPages', 'pageNumbers');
    }
}
