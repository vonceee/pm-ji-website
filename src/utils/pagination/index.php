<?php
// src/utils/pagination/index.php

namespace Utils;

class Pagination
{
    /**
     * Render pagination HTML
     * 
     * @param int $totalItems Total number of items
     * @param int $itemsPerPage Items per page
     * @param int $currentPage Current page number
     * @param string $baseUrl Optional base URL (defaults to current page)
     * @param array $additionalParams Additional query parameters to maintain
     * @return string HTML pagination
     */
    public static function render($totalItems, $itemsPerPage, $currentPage, $baseUrl = null, $additionalParams = [])
    {
        // Calculate pagination data
        $totalPages = ceil($totalItems / $itemsPerPage);
        
        // Don't render if only one page or less
        if ($totalPages <= 1) {
            return '';
        }
        
        // Get current URL and parse query parameters
        if ($baseUrl === null) {
            $baseUrl = $_SERVER['REQUEST_URI'];
        }
        
        $urlParts = parse_url($baseUrl);
        parse_str($urlParts['query'] ?? '', $queryParams);
        
        // Merge with additional parameters
        $queryParams = array_merge($queryParams, $additionalParams);
        
        // Build pagination data
        $paginationData = [
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'total_items' => $totalItems,
            'has_previous' => $currentPage > 1,
            'has_next' => $currentPage < $totalPages,
            'previous_page' => max(1, $currentPage - 1),
            'next_page' => min($totalPages, $currentPage + 1)
        ];
        
        // Generate pagination HTML
        ob_start();
        self::renderPaginationHTML($paginationData, $queryParams);
        return ob_get_clean();
    }
    
    /**
     * Build pagination URL with query parameters
     */
    private static function buildPaginationUrl($page, $queryParams)
    {
        $queryParams['page'] = $page;
        return '?' . http_build_query($queryParams);
    }
    
    /**
     * Render the actual pagination HTML
     */
    private static function renderPaginationHTML($paginationData, $queryParams)
    {
        $currentPage = $paginationData['current_page'];
        $totalPages = $paginationData['total_pages'];
        
        // Calculate page range to show
        $range = 2; // show 2 pages before and after current page
        $start = max(1, $currentPage - $range);
        $end = min($totalPages, $currentPage + $range);
        ?>
        
        <div class="pagination-container d-flex justify-content-between align-items-center mt-4">
            <div class="pagination-info">
                <small class="text-muted">
                    Page <?= $currentPage ?> of <?= $totalPages ?>
                    (<?= $paginationData['total_items'] ?> total items)
                </small>
            </div>

            <nav aria-label="Page navigation">
                <ul class="pagination mb-0">
                    <!-- First Page -->
                    <?php if ($currentPage > 3): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= self::buildPaginationUrl(1, $queryParams) ?>" title="First page">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- Previous Page -->
                    <?php if ($paginationData['has_previous']): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= self::buildPaginationUrl($paginationData['previous_page'], $queryParams) ?>" title="Previous page">
                                <i class="fas fa-angle-left"></i>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link">
                                <i class="fas fa-angle-left"></i>
                            </span>
                        </li>
                    <?php endif; ?>

                    <!-- Page Numbers -->
                    <?php for ($i = $start; $i <= $end; $i++): ?>
                        <?php if ($i == $currentPage): ?>
                            <li class="page-item active">
                                <span class="page-link"><?= $i ?></span>
                            </li>
                        <?php else: ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= self::buildPaginationUrl($i, $queryParams) ?>"><?= $i ?></a>
                            </li>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <!-- Next Page -->
                    <?php if ($paginationData['has_next']): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= self::buildPaginationUrl($paginationData['next_page'], $queryParams) ?>" title="Next page">
                                <i class="fas fa-angle-right"></i>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link">
                                <i class="fas fa-angle-right"></i>
                            </span>
                        </li>
                    <?php endif; ?>

                    <!-- Last Page -->
                    <?php if ($currentPage < $totalPages - 2): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= self::buildPaginationUrl($totalPages, $queryParams) ?>" title="Last page">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        
        <?php
    }
    
    /**
     * Create pagination data array for use in templates
     * 
     * @param int $totalItems Total number of items
     * @param int $itemsPerPage Items per page
     * @param int $currentPage Current page number
     * @return array Pagination data
     */
    public static function createPaginationData($totalItems, $itemsPerPage, $currentPage)
    {
        $totalPages = ceil($totalItems / $itemsPerPage);
        $offset = ($currentPage - 1) * $itemsPerPage;
        
        return [
            'current_page' => $currentPage,
            'page' => $currentPage, // alias for compatibility
            'total_pages' => $totalPages,
            'total_items' => $totalItems,
            'total' => $totalItems, // alias for compatibility
            'limit' => $itemsPerPage,
            'offset' => $offset,
            'has_previous' => $currentPage > 1,
            'has_next' => $currentPage < $totalPages,
            'previous_page' => max(1, $currentPage - 1),
            'next_page' => min($totalPages, $currentPage + 1)
        ];
    }
}