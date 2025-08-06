/*
 * 🔒 Spond Manager - Created by Belli Dev
 * © 2025 Belli Dev. All rights reserved.
 * You are not allowed to copy, modify, redistribute, or sell this software
 * without explicit written permission from the author.
 * Violators will be prosecuted under applicable laws.
 */


// Global functions and utilities
class SpondManager {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
        this.initTooltips();
    }

    bindEvents() {
        // Sync events button
        const syncBtn = document.querySelector('[onclick="syncSpondEvents()"]');
        if (syncBtn) {
            syncBtn.removeAttribute('onclick');
            syncBtn.addEventListener('click', () => this.syncSpondEvents());
        }
    }

    initTooltips() {
        // Initialize Bootstrap tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    async syncSpondEvents() {
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        
        // Show loading state
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Syncing...';

        try {
            const response = await fetch('api/sync.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ action: 'sync_events' })
            });

            const result = await response.json();

            if (result.success) {
                this.showToast('Success', result.message, 'success');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                this.showToast('Error', result.message || 'Sync failed', 'error');
            }
        } catch (error) {
            console.error('Sync error:', error);
            this.showToast('Error', 'Network error during sync', 'error');
        } finally {
            button.disabled = false;
            button.innerHTML = originalText;
        }
    }

    showToast(title, message, type = 'info') {
        const colors = {
            success: { bg: '#1cc88a', icon: 'check-circle' },
            error: { bg: '#e74a3b', icon: 'exclamation-triangle' },
            warning: { bg: '#f6c23e', icon: 'exclamation-circle' },
            info: { bg: '#36b9cc', icon: 'info-circle' }
        };

        const color = colors[type] || colors.info;
        
        // Create toast element
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${color.bg};
            color: white;
            padding: 15px 20px;
            border-radius: 5px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 9999;
            font-weight: 500;
            min-width: 300px;
            animation: slideIn 0.3s ease;
        `;
        
        toast.innerHTML = `
            <div style="display: flex; align-items: center;">
                <i class="fas fa-${color.icon}" style="margin-right: 10px; font-size: 18px;"></i>
                <div>
                    <div style="font-weight: 600; margin-bottom: 2px;">${title}</div>
                    <div style="font-size: 14px; opacity: 0.9;">${message}</div>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" 
                        style="background: none; border: none; color: white; margin-left: 15px; font-size: 18px; cursor: pointer;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (toast.parentElement) {
                toast.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }
        }, 5000);
    }

    // Utility function to format dates
    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }

    // Utility function to format time
    formatTime(timeString) {
        const time = new Date(`2000-01-01 ${timeString}`);
        return time.toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
    }

    // Confirmation dialog
    confirm(message, callback) {
        if (confirm(message)) {
            callback();
        }
    }

    // Loading overlay
    showLoading() {
        const overlay = document.createElement('div');
        overlay.id = 'loading-overlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
        `;
        
        overlay.innerHTML = `
            <div style="background: white; padding: 30px; border-radius: 10px; text-align: center;">
                <div class="spinner-border text-primary mb-3"></div>
                <div>Loading...</div>
            </div>
        `;
        
        document.body.appendChild(overlay);
        return overlay;
    }

    hideLoading(overlay) {
        if (overlay && overlay.parentElement) {
            overlay.remove();
        }
    }
}

// Global sync function for backward compatibility
function syncSpondEvents() {
    if (window.spondManager) {
        window.spondManager.syncSpondEvents();
    }
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .toast-notification:hover {
        transform: translateX(-5px);
        transition: transform 0.2s ease;
    }
`;
document.head.appendChild(style);

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.spondManager = new SpondManager();
});

// Table utilities
function sortTable(table, column, direction = 'asc') {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        const aVal = a.cells[column].textContent.trim();
        const bVal = b.cells[column].textContent.trim();
        
        // Check if values are numbers
        const aNum = parseFloat(aVal.replace(/[^0-9.-]/g, ''));
        const bNum = parseFloat(bVal.replace(/[^0-9.-]/g, ''));
        
        if (!isNaN(aNum) && !isNaN(bNum)) {
            return direction === 'asc' ? aNum - bNum : bNum - aNum;
        }
        
        return direction === 'asc' ? 
            aVal.localeCompare(bVal) : 
            bVal.localeCompare(aVal);
    });
    
    tbody.innerHTML = '';
    rows.forEach(row => tbody.appendChild(row));
}

// Search/filter utilities
function filterTable(table, searchTerm, columns = []) {
    const tbody = table.querySelector('tbody');
    const rows = tbody.querySelectorAll('tr');
    const term = searchTerm.toLowerCase();
    
    rows.forEach(row => {
        let visible = false;
        
        if (columns.length === 0) {
            // Search all columns
            const text = row.textContent.toLowerCase();
            visible = text.includes(term);
        } else {
            // Search specific columns
            columns.forEach(col => {
                if (row.cells[col] && row.cells[col].textContent.toLowerCase().includes(term)) {
                    visible = true;
                }
            });
        }
        
        row.style.display = visible ? '' : 'none';
    });
}

// Form validation utilities
function validateForm(formElement) {
    const inputs = formElement.querySelectorAll('input[required], select[required], textarea[required]');
    let valid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            valid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    return valid;
}

// Auto-save functionality for forms
function enableAutoSave(formElement, saveUrl, interval = 30000) {
    const formData = new FormData(formElement);
    let lastSave = JSON.stringify([...formData.entries()]);
    
    setInterval(async () => {
        const currentData = new FormData(formElement);
        const currentSave = JSON.stringify([...currentData.entries()]);
        
        if (currentSave !== lastSave) {
            try {
                await fetch(saveUrl, {
                    method: 'POST',
                    body: currentData
                });
                lastSave = currentSave;
                console.log('Form auto-saved');
            } catch (error) {
                console.error('Auto-save failed:', error);
            }
        }
    }, interval);
}

// Export utilities
function exportTableToCSV(table, filename = 'export.csv') {
    const rows = table.querySelectorAll('tr');
    const csvContent = Array.from(rows).map(row => {
        const cells = row.querySelectorAll('th, td');
        return Array.from(cells).map(cell => {
            const text = cell.textContent.replace(/"/g, '""');
            return `"${text}"`;
        }).join(',');
    }).join('\n');
    
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    window.URL.revokeObjectURL(url);
}

// Print utilities
function printElement(element) {
    const printContent = element.cloneNode(true);
    const printWindow = window.open('', '_blank');
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Print</title>
            <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body { margin: 20px; }
                @media print { 
                    .btn, .no-print { display: none !important; }
                }
            </style>
        </head>
        <body>
            ${printContent.outerHTML}
        </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.focus();
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 250);
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + S for save
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        const saveBtn = document.querySelector('[data-action="save"], .btn-primary[type="submit"]');
        if (saveBtn) saveBtn.click();
    }
    
    // Ctrl/Cmd + F for search
    if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
        e.preventDefault();
        const searchInput = document.querySelector('input[type="search"], input[placeholder*="search" i]');
        if (searchInput) {
            searchInput.focus();
            searchInput.select();
        }
    }
    
    // Escape to close modals
    if (e.key === 'Escape') {
        const openModal = document.querySelector('.modal.show');
        if (openModal) {
            const modal = bootstrap.Modal.getInstance(openModal);
            if (modal) modal.hide();
        }
    }
});

// Enhanced error handling
window.addEventListener('error', function(e) {
    console.error('JavaScript Error:', e.error);
    if (window.spondManager) {
        window.spondManager.showToast('Error', 'An unexpected error occurred', 'error');
    }
});

// Handle unhandled promise rejections
window.addEventListener('unhandledrejection', function(e) {
    console.error('Unhandled Promise Rejection:', e.reason);
    if (window.spondManager) {
        window.spondManager.showToast('Error', 'Network or server error', 'error');
    }
});

// Created by Belli Dev watermark enhancement
function enhanceWatermark() {
    const watermark = document.querySelector('.watermark');
    if (watermark) {
        watermark.addEventListener('click', () => {
            window.spondManager?.showToast('Info', 'Spond Manager v1.0 - Created by Belli Dev', 'info');
        });
        watermark.style.cursor = 'pointer';
        watermark.title = 'Click for more info';
    }
}

// Initialize watermark enhancement when DOM is loaded
document.addEventListener('DOMContentLoaded', enhanceWatermark);