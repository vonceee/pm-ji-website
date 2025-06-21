// Enhanced Pagination JavaScript
document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize pagination enhancements
    initializePagination();
    
    /**
     * Initialize pagination functionality
     */
    function initializePagination() {
        // Add loading states to pagination links
        addPaginationLoadingStates();
        
        // Add keyboard navigation
        addKeyboardNavigation();
        
        // Add smooth scrolling after pagination
        addSmoothScrolling();
        
        // Track active tab for pagination (if using tabs)
        trackActiveTab();
    }
    
    /**
     * Add loading states to pagination links
     */
    function addPaginationLoadingStates() {
        const paginationLinks = document.querySelectorAll('.pagination .page-link:not([disabled])');
        
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                // Don't add loading state if it's the current page
                if (this.closest('.page-item').classList.contains('active')) {
                    e.preventDefault();
                    return;
                }
                
                // Add loading state
                const paginationWrapper = document.querySelector('.pagination-wrapper');
                if (paginationWrapper) {
                    paginationWrapper.classList.add('loading');
                }
                
                // Show loading text
                const originalText = this.innerHTML;
                if (!this.querySelector('i.fa-angle')) {
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                }
                
                // Remove loading state after a delay (in case navigation fails)
                setTimeout(() => {
                    if (paginationWrapper) {
                        paginationWrapper.classList.remove('loading');
                    }
                    this.innerHTML = originalText;
                }, 5000);
            });
        });
    }
    
    /**
     * Add keyboard navigation support
     */
    function addKeyboardNavigation() {
        document.addEventListener('keydown', function(e) {
            // Only if no input is focused
            if (document.activeElement && 
                ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) {
                return;
            }
            
            const pagination = document.querySelector('.pagination');
            if (!pagination) return;
            
            let targetLink = null;
            
            // Left arrow or 'p' for previous
            if (e.key === 'ArrowLeft' || e.key.toLowerCase() === 'p') {
                targetLink = pagination.querySelector('.page-item:not(.disabled) .page-link[title*="Previous"]');
            }
            // Right arrow or 'n' for next  
            else if (e.key === 'ArrowRight' || e.key.toLowerCase() === 'n') {
                targetLink = pagination.querySelector('.page-item:not(.disabled) .page-link[title*="Next"]');
            }
            // Home key for first page
            else if (e.key === 'Home') {
                targetLink = pagination.querySelector('.page-item:not(.disabled) .page-link[title*="First"]');
            }
            // End key for last page
            else if (e.key === 'End') {
                targetLink = pagination.querySelector('.page-item:not(.disabled) .page-link[title*="Last"]');
            }
            
            if (targetLink) {
                e.preventDefault();
                targetLink.click();
            }
        });
    }
    
    /**
     * Add smooth scrolling after pagination navigation
     */
    function addSmoothScrolling() {
        const paginationLinks = document.querySelectorAll('.pagination .page-link');
        
        paginationLinks.forEach(link => {
            link.addEventListener('click', function() {
                // Scroll to top of content after a short delay
                setTimeout(() => {
                    const contentTop = document.querySelector('.bookings-table-container') || 
                                     document.querySelector('.empty-state') ||
                                     document.querySelector('section');
                    
                    if (contentTop) {
                        contentTop.scrollIntoView({ 
                            behavior: 'smooth', 
                            block: 'start' 
                        });
                    }
                }, 100);
            });
        });
    }
    
    /**
     * Track active tab for pagination (for tab-based interfaces)
     */
    function trackActiveTab() {
        const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
        
        tabButtons.forEach(button => {
            button.addEventListener('shown.bs.tab', function(e) {
                const tabId = e.target.getAttribute('aria-controls');
                setActiveTab(tabId);
            });
        });
    }
    
    /**
     * Set active tab and update URL
     */
    function setActiveTab(tab) {
        try {
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            url.searchParams.delete('page'); // Reset page when switching tabs
            window.history.replaceState({}, '', url);
        } catch (error) {
            console.warn('Unable to update URL:', error);
        }
    }
    
    /**
     * Update pagination URL parameters
     */
    function updatePaginationUrl(page, additionalParams = {}) {
        try {
            const url = new URL(window.location);
            url.searchParams.set('page', page);
            
            // Add additional parameters  
            Object.keys(additionalParams).forEach(key => {
                if (additionalParams[key]) {
                    url.searchParams.set(key, additionalParams[key]);
                } else {
                    url.searchParams.delete(key);
                }
            });
            
            return url.toString();
        } catch (error) {
            console.warn('Unable to build pagination URL:', error);
            return '#';
        }
    }
    
    /**
     * Show pagination info tooltip
     */
    function showPaginationInfo() {
        const paginationInfo = document.querySelector('.pagination-info');
        if (paginationInfo) {
            // Add tooltip functionality if needed
            paginationInfo.setAttribute('title', 'Navigate using arrow keys or clicking page numbers');
        }
    }
    
    // Initialize pagination info
    showPaginationInfo();
    
    // Add ARIA labels for accessibility
    addAccessibilityLabels();
    
    /**
     * Add accessibility labels
     */
    function addAccessibilityLabels() {
        const pagination = document.querySelector('.pagination');
        if (!pagination) return;
        
        // Add role and aria-label to pagination
        pagination.setAttribute('role', 'navigation');
        pagination.setAttribute('aria-label', 'Bookings pagination');
        
        // Add aria-labels to pagination links
        const pageLinks = pagination.querySelectorAll('.page-link');
        pageLinks.forEach(link => {
            const pageItem = link.closest('.page-item');
            
            if (pageItem.classList.contains('active')) {
                link.setAttribute('aria-current', 'page');
                link.setAttribute('aria-label', `Current page ${link.textContent}`);
            } else if (pageItem.classList.contains('disabled')) {
                link.setAttribute('aria-disabled', 'true');
            } else if (link.querySelector('.fa-angle-left')) {
                link.setAttribute('aria-label', 'Go to previous page');
            } else if (link.querySelector('.fa-angle-right')) {
                link.setAttribute('aria-label', 'Go to next page');
            } else if (link.querySelector('.fa-angle-double-left')) {
                link.setAttribute('aria-label', 'Go to first page');
            } else if (link.querySelector('.fa-angle-double-right')) {
                link.setAttribute('aria-label', 'Go to last page');
            } else {
                link.setAttribute('aria-label', `Go to page ${link.textContent}`);
            }
        });
    }
    
    // Handle form submission with pagination reset
    const filterForm = document.querySelector('.bookings-filter-form');
    if (filterForm) {
        filterForm.addEventListener('submit', function() {
            // Reset page to 1 when filtering
            const pageInput = this.querySelector('input[name="page"]');
            if (pageInput) {
                pageInput.value = '1';
            }
        });
    }
    
    // Handle filter changes
    const filterInputs = document.querySelectorAll('.bookings-filter-form input, .bookings-filter-form select');
    filterInputs.forEach(input => {
        input.addEventListener('input', debounce(function() {
            // Auto-submit after typing stops (for search)
            if (this.name === 'search' && this.value.length > 2) {
                // Optional: auto-submit search after typing stops
                // this.form.submit();
            }
        }, 500));
    });
    
    /**
     * Debounce function for search input
     */
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
});

// Export functions for external use
window.PaginationUtils = {
    setActiveTab: function(tab) {
        try {
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            url.searchParams.delete('page');
            window.history.replaceState({}, '', url);
        } catch (error) {
            console.warn('Unable to update URL:', error);
        }
    },
    
    goToPage: function(page) {
        try {
            const url = new URL(window.location);
            url.searchParams.set('page', page);
            window.location.href = url.toString();
        } catch (error) {
            console.warn('Unable to navigate to page:', error);
        }
    }
};