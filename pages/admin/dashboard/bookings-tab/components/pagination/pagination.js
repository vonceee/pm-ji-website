/**
 * Enhanced Payments Tab Management
 * Handles tab switching, pagination, and state management
 */

class PaymentsTabManager {
    constructor() {
        this.currentTab = 'outstanding';
        this.tabHistory = [];
        this.init();
    }

    init() {
        // Initialize tab state from URL
        this.initializeFromURL();
        
        // Set up event listeners
        this.setupEventListeners();
        
        // Initialize pagination handlers
        this.setupPaginationHandlers();
        
        // Setup keyboard shortcuts
        this.setupKeyboardShortcuts();
        
        console.log('PaymentsTabManager initialized');
    }

    initializeFromURL() {
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('active_tab') || 'outstanding';
        this.switchTab(activeTab, false); // Don't update URL on init
    }

    setupEventListeners() {
        // Tab button click handlers
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const tabName = button.getAttribute('data-tab');
                if (tabName) {
                    this.switchTab(tabName);
                }
            });
        });

        // Filter form submission
        const filterForm = document.getElementById('paymentFilterForm');
        if (filterForm) {
            filterForm.addEventListener('submit', (e) => {
                // Ensure active tab is preserved in the form
                this.updateFormActiveTab(filterForm);
            });
        }

        // Browser back/forward button handling
        window.addEventListener('popstate', (e) => {
            this.initializeFromURL();
        });
    }

    setupPaginationHandlers() {
        // Handle pagination clicks with delegation
        document.addEventListener('click', (e) => {
            if (e.target.closest('.pagination .page-link')) {
                const link = e.target.closest('.page-link');
                const href = link.getAttribute('href');
                
                // Add loading state
                this.showTabLoading(this.currentTab);
                
                // Small delay to show loading effect
                setTimeout(() => {
                    window.location.href = href;
                }, 100);
            }
        });
    }

    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Only handle shortcuts when not in input fields
            if (e.target.tagName === 'INPUT' || 
                e.target.tagName === 'TEXTAREA' || 
                e.target.tagName === 'SELECT') {
                return;
            }

            // Tab switching shortcuts (Ctrl + number)
            if (e.ctrlKey) {
                switch(e.key) {
                    case '1':
                        e.preventDefault();
                        this.switchTab('outstanding');
                        break;
                    case '2':
                        e.preventDefault();
                        this.switchTab('history');
                        break;
                    case '3':
                        e.preventDefault();
                        this.switchTab('refunds');
                        break;
                }
            }

            // Filter shortcut (Ctrl + F)
            if (e.ctrlKey && e.key === 'f') {
                e.preventDefault();
                this.openFilterModal();
            }

            // Print shortcut (Ctrl + P)
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                this.printCurrentView();
            }
        });
    }

    switchTab(tabName, updateURL = true) {
        if (this.currentTab === tabName) {
            return; // Already on this tab
        }

        // Add current tab to history
        if (this.currentTab) {
            this.tabHistory.push(this.currentTab);
        }

        // Remove active class from all tabs and buttons
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelectorAll('.tab-button').forEach(button => {
            button.classList.remove('active');
        });

        // Add active class to selected tab and button
        const targetTab = document.getElementById(tabName + '-tab');
        const targetButton = document.querySelector(`[data-tab="${tabName}"]`);

        if (targetTab && targetButton) {
            targetTab.classList.add('active');
            targetButton.classList.add('active');
            this.currentTab = tabName;

            // Update URL if requested
            if (updateURL) {
                this.updateURL(tabName);
            }

            // Update form hidden input for active tab
            this.updateFormActiveTab();

            // Show loading effect
            this.showTabLoading(tabName);

            // Hide loading after animation
            setTimeout(() => {
                this.hideTabLoading(tabName);
            }, 300);

            // Fire custom event
            this.fireTabChangeEvent(tabName);

            console.log(`Switched to ${tabName} tab`);
        }
    }

    updateURL(tabName) {
        const url = new URL(window.location);
        url.searchParams.set('active_tab', tabName);
        
        // Reset pagination for the new tab to avoid confusion
        url.searchParams.delete('outstanding_page');
        url.searchParams.delete('history_page');
        url.searchParams.delete('refunds_page');
        
        window.history.pushState({ tab: tabName }, '', url);
    }

    updateFormActiveTab(form = null) {
        const filterForm = form || document.getElementById('paymentFilterForm');
        if (filterForm) {
            let activeTabInput = filterForm.querySelector('input[name="active_tab"]');
            if (!activeTabInput) {
                activeTabInput = document.createElement('input');
                activeTabInput.type = 'hidden';
                activeTabInput.name = 'active_tab';
                filterForm.appendChild(activeTabInput);
            }
            activeTabInput.value = this.currentTab;
        }
    }

    showTabLoading(tabName) {
        const loadingElement = document.getElementById(`${tabName}-loading`);
        if (loadingElement) {
            loadingElement.style.display = 'block';
        }

        // Add loading class to tab content
        const tabContent = document.getElementById(`${tabName}-tab`);
        if (tabContent) {
            tabContent.classList.add('loading');
        }
    }

    hideTabLoading(tabName) {
        const loadingElement = document.getElementById(`${tabName}-loading`);
        if (loadingElement) {
            loadingElement.style.display = 'none';
        }

        // Remove loading class from tab content
        const tabContent = document.getElementById(`${tabName}-tab`);
        if (tabContent) {
            tabContent.classList.remove('loading');
        }
    }

    fireTabChangeEvent(tabName) {
        const event = new CustomEvent('tabChanged', {
            detail: {
                tab: tabName,
                previousTab: this.tabHistory[this.tabHistory.length - 1] || null
            }
        });
        document.dispatchEvent(event);
    }

    openFilterModal() {
        const filterModal = document.getElementById('paymentFilterModal');
        if (filterModal) {
            const modal = new bootstrap.Modal(filterModal);
            modal.show();
        }
    }

    printCurrentView() {
        if (typeof printCurrentPaymentPage === 'function') {
            printCurrentPaymentPage();
        } else {
            window.print();
        }
    }

    // Get current tab data
    getCurrentTabData() {
        const activeTab = document.querySelector('.tab-content.active');
        if (!activeTab) return null;

        const tabData = {
            name: this.currentTab,
            element: activeTab,
            hasData: false,
            totalItems: 0
        };

        // Check if tab has data
        const emptyState = activeTab.querySelector('.empty-state');
        const dataContainer = activeTab.querySelector('table tbody');
        
        if (!emptyState && dataContainer && dataContainer.children.length > 0) {
            tabData.hasData = true;
            tabData.totalItems = dataContainer.children.length;
        }

        // Get pagination info
        const paginationInfo = activeTab.querySelector('.pagination-info');
        if (paginationInfo) {
            tabData.paginationInfo = paginationInfo.textContent.trim();
        }

        return tabData;
    }

    // Refresh current tab
    refreshCurrentTab() {
        const url = new URL(window.location);
        url.searchParams.set('active_tab', this.currentTab);
        window.location.href = url.toString();
    }

    // Go back to previous tab
    goBackToPreviousTab() {
        if (this.tabHistory.length > 0) {
            const previousTab = this.tabHistory.pop();
            this.switchTab(previousTab);
        }
    }
}

// Enhanced pagination utilities
class PaginationManager {
    static changePageSize(newSize, pageParam, tabName) {
        const url = new URL(window.location);
        url.searchParams.set('items_per_page', newSize);
        url.searchParams.set(pageParam, 1); // Reset to first page
        if (tabName) {
            url.searchParams.set('active_tab', tabName);
        }
        
        // Show loading
        if (window.paymentsTabManager) {
            window.paymentsTabManager.showTabLoading(tabName);
        }
        
        window.location.href = url.toString();
    }

    static goToPage(pageNumber, pageParam, tabName) {
        const url = new URL(window.location);
        url.searchParams.set(pageParam, pageNumber);
        if (tabName) {
            url.searchParams.set('active_tab', tabName);
        }
        
        // Show loading
        if (window.paymentsTabManager) {
            window.paymentsTabManager.showTabLoading(tabName);
        }
        
        window.location.href = url.toString();
    }

    static setupQuickJump() {
        // Add quick jump functionality to pagination
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('quick-jump-btn')) {
                const pageInput = e.target.previousElementSibling;
                const pageNumber = parseInt(pageInput.value);
                const maxPage = parseInt(pageInput.getAttribute('max'));
                
                if (pageNumber >= 1 && pageNumber <= maxPage) {
                    const pageParam = e.target.getAttribute('data-page-param');
                    const tabName = e.target.getAttribute('data-tab-name');
                    PaginationManager.goToPage(pageNumber, pageParam, tabName);
                } else {
                    alert(`Please enter a page number between 1 and ${maxPage}`);
                }
            }
        });
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tab manager
    window.paymentsTabManager = new PaymentsTabManager();
    
    // Initialize pagination manager
    PaginationManager.setupQuickJump();
    
    // Setup global functions for backward compatibility
    window.switchTab = function(tabName) {
        window.paymentsTabManager.switchTab(tabName);
    };
    
    window.changePageSize = function(newSize, pageParam, tabName) {
        PaginationManager.changePageSize(newSize, pageParam, tabName);
    };

});