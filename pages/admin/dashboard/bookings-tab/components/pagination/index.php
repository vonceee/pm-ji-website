<?php

// components/pagination.php

// controls pagination rendering (if total pages is less than or equal to 1, do not render pagination)
if (!isset($paginationData) || $paginationData['total_pages'] <= 1) {
    return;
}

$currentPage = $paginationData['current_page'];
$totalPages = $paginationData['total_pages'];
$baseUrl = $_SERVER['REQUEST_URI'];

// parse current URL to maintain existing parameters
$urlParts = parse_url($baseUrl);
parse_str($urlParts['query'] ?? '', $queryParams);

function buildPaginationUrl($page, $queryParams) {
    $queryParams['page'] = $page;
    return '?' . http_build_query($queryParams);
}

// calculate page range to show
$range = 2; // show 2 pages before and after current page
$start = max(1, $currentPage - $range);
$end = min($totalPages, $currentPage + $range);
?>

<div class="pagination-container d-flex justify-content-between align-items-center mt-4">
    <div class="pagination-info">
        <small class="text-muted">
            showing page <?= $currentPage ?> of <?= $totalPages ?> 
            (<?= $paginationData['total_items'] ?> total items)
        </small>
    </div>
    
    <nav aria-label="Page navigation">
        <ul class="pagination mb-0">
            <!-- First Page -->
            <?php if ($currentPage > 3): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= buildPaginationUrl(1, $queryParams) ?>">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                </li>
            <?php endif; ?>
            
            <!-- Previous Page -->
            <?php if ($paginationData['has_previous']): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= buildPaginationUrl($paginationData['previous_page'], $queryParams) ?>">
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
                        <a class="page-link" href="<?= buildPaginationUrl($i, $queryParams) ?>"><?= $i ?></a>
                    </li>
                <?php endif; ?>
            <?php endfor; ?>
            
            <!-- Next Page -->
            <?php if ($paginationData['has_next']): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= buildPaginationUrl($paginationData['next_page'], $queryParams) ?>">
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
                    <a class="page-link" href="<?= buildPaginationUrl($totalPages, $queryParams) ?>">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>