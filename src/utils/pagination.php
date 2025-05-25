<?php
// utils/pagination.php

namespace Utils;

class Pagination
{
    public static function calculateOffset(int $page, int $limit): int
    {
        return ($page - 1) * $limit;
    }

    public static function getCurrentPage(): int
    {
        return isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
    }

    public static function render(int $totalItems, int $limit, int $currentPage, string $baseUrl): string
    {
        $totalPages = (int) ceil($totalItems / $limit);
        if ($totalPages <= 1) {
            return '';
        }

        $html = '<nav aria-label="Pagination" class="mt-3">';
        $html .= '<ul class="pagination justify-content-center">';

        // Previous button
        $prevPage = $currentPage - 1;
        $html .= '<li class="page-item' . ($currentPage <= 1 ? ' disabled' : '') . '">';
        $html .= '<a class="page-link" href="' . htmlspecialchars($baseUrl) . '?page=' . $prevPage . '">Previous</a>';
        $html .= '</li>';

        // Page numbers
        for ($i = 1; $i <= $totalPages; $i++) {
            $active = $i == $currentPage ? ' active' : '';
            $html .= '<li class="page-item' . $active . '"><a class="page-link" href="' . htmlspecialchars($baseUrl) . '?page=' . $i . '">' . $i . '</a></li>';
        }

        // Next button
        $nextPage = $currentPage + 1;
        $html .= '<li class="page-item' . ($currentPage >= $totalPages ? ' disabled' : '') . '">';
        $html .= '<a class="page-link" href="' . htmlspecialchars($baseUrl) . '?page=' . $nextPage . '">Next</a>';
        $html .= '</li>';

        $html .= '</ul></nav>';

        return $html;
    }
}
