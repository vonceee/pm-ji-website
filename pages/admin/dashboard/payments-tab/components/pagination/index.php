<?php
// Updated pagination component that handles separate pagination for different tabs

// Extract pagination data
$currentPage = $paginationData['current_page'] ?? 1;
$totalPages = $paginationData['total_pages'] ?? 1;
$totalItems = $paginationData['total_items'] ?? 0;
$hasPrevious = $paginationData['has_previous'] ?? false;
$hasNext = $paginationData['has_next'] ?? false;
$previousPage = $paginationData['previous_page'] ?? 1;
$nextPage = $paginationData['next_page'] ?? 1;
$pageParam = $paginationData['page_param'] ?? 'page';
$tabName = $paginationData['tab_name'] ?? '';

// Function to build pagination URL
function buildPaginationUrl($pageNumber, $pageParam, $tabName) {
    $params = $_GET;
    $params[$pageParam] = $pageNumber;
    if (!empty($tabName)) {
        $params['active_tab'] = $tabName;
    }
    return '?' . http_build_query($params);
}

// Only show pagination if there are multiple pages
if ($totalPages > 1):
?>
<div class="pagination-container d-flex justify-content-between align-items-center mt-4">
    <div class="pagination-info">
        <small class="text-muted">
            Showing page <?= $currentPage ?> of <?= $totalPages ?> 
            (<?= $totalItems ?> total items)
        </small>
    </div>
    
    <nav aria-label="Pagination Navigation">
        <ul class="pagination pagination-sm mb-0">
            <!-- First Page -->
            <?php if ($currentPage > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= buildPaginationUrl(1, $pageParam, $tabName) ?>" 
                       title="First page" aria-label="Go to first page">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                </li>
            <?php endif; ?>
            
            <!-- Previous Page -->
            <?php if ($hasPrevious): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= buildPaginationUrl($previousPage, $pageParam, $tabName) ?>" 
                       title="Previous page" aria-label="Go to previous page">
                        <i class="fas fa-angle-left"></i> Previous
                    </a>
                </li>
            <?php else: ?>
                <li class="page-item disabled">
                    <span class="page-link">
                        <i class="fas fa-angle-left"></i> Previous
                    </span>
                </li>
            <?php endif; ?>
            
            <!-- Page Numbers -->
            <?php
            // Calculate the range of page numbers to show
            $startPage = max(1, $currentPage - 2);
            $endPage = min($totalPages, $currentPage + 2);
            
            // Adjust range if we're near the beginning or end
            if ($currentPage <= 3) {
                $endPage = min($totalPages, 5);
            }
            if ($currentPage > $totalPages - 3) {
                $startPage = max(1, $totalPages - 4);
            }
            
            // Show ellipsis at the beginning if needed
            if ($startPage > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= buildPaginationUrl(1, $pageParam, $tabName) ?>">1</a>
                </li>
                <?php if ($startPage > 2): ?>
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                <?php endif; ?>
            <?php endif; ?>
            
            <!-- Page number links -->
            <?php for ($page = $startPage; $page <= $endPage; $page++): ?>
                <?php if ($page == $currentPage): ?>
                    <li class="page-item active" aria-current="page">
                        <span class="page-link">
                            <?= $page ?>
                            <span class="sr-only">(current)</span>
                        </span>
                    </li>
                <?php else: ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= buildPaginationUrl($page, $pageParam, $tabName) ?>" 
                           title="Go to page <?= $page ?>">
                            <?= $page ?>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endfor; ?>
            
            <!-- Show ellipsis at the end if needed -->
            <?php if ($endPage < $totalPages): ?>
                <?php if ($endPage < $totalPages - 1): ?>
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                <?php endif; ?>
                <li class="page-item">
                    <a class="page-link" href="<?= buildPaginationUrl($totalPages, $pageParam, $tabName) ?>">
                        <?= $totalPages ?>
                    </a>
                </li>
            <?php endif; ?>
            
            <!-- Next Page -->
            <?php if ($hasNext): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= buildPaginationUrl($nextPage, $pageParam, $tabName) ?>" 
                       title="Next page" aria-label="Go to next page">
                        Next <i class="fas fa-angle-right"></i>
                    </a>
                </li>
            <?php else: ?>
                <li class="page-item disabled">
                    <span class="page-link">
                        Next <i class="fas fa-angle-right"></i>
                    </span>
                </li>
            <?php endif; ?>
            
            <!-- Last Page -->
            <?php if ($currentPage < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= buildPaginationUrl($totalPages, $pageParam, $tabName) ?>" 
                       title="Last page" aria-label="Go to last page">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
    
    <!-- Page Size Selector (Optional) -->
    <div class="page-size-selector">
        <select class="form-select form-select-sm" style="width: auto;" 
                onchange="changePageSize(this.value, '<?= $pageParam ?>', '<?= $tabName ?>')">
            <option value="5" <?= $itemsPerPage == 5 ? 'selected' : '' ?>>5 per page</option>
            <option value="10" <?= $itemsPerPage == 10 ? 'selected' : '' ?>>10 per page</option>
            <option value="25" <?= $itemsPerPage == 25 ? 'selected' : '' ?>>25 per page</option>
            <option value="50" <?= $itemsPerPage == 50 ? 'selected' : '' ?>>50 per page</option>
        </select>
    </div>
</div>

<script>
// Function to change page size
function changePageSize(newSize, pageParam, tabName) {
    const url = new URL(window.location);
    url.searchParams.set('items_per_page', newSize);
    url.searchParams.set(pageParam, 1); // Reset to first page
    if (tabName) {
        url.searchParams.set('active_tab', tabName);
    }
    window.location.href = url.toString();
}

// Add keyboard navigation for pagination
document.addEventListener('keydown', function(e) {
    // Only work if no input/textarea is focused
    if (document.activeElement.tagName !== 'INPUT' && 
        document.activeElement.tagName !== 'TEXTAREA' && 
        document.activeElement.tagName !== 'SELECT') {
        
        const activeTab = document.querySelector('.tab-content.active');
        if (activeTab) {
            const paginationContainer = activeTab.querySelector('.pagination-container');
            if (paginationContainer) {
                // Left arrow key - previous page
                if (e.key === 'ArrowLeft') {
                    const prevLink = paginationContainer.querySelector('.pagination .page-item:not(.disabled) .page-link[title*="Previous"]');
                    if (prevLink) {
                        e.preventDefault();
                        prevLink.click();
                    }
                }
                // Right arrow key - next page
                else if (e.key === 'ArrowRight') {
                    const nextLink = paginationContainer.querySelector('.pagination .page-item:not(.disabled) .page-link[title*="Next"]');
                    if (nextLink) {
                        e.preventDefault();
                        nextLink.click();
                    }
                }
            }
        }
    }
});
</script>

<?php endif; ?>