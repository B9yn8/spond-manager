/*
 * 🔒 Spond Manager - Created by Belli Dev
 * © 2025 Belli Dev. All rights reserved.
 * You are not allowed to copy, modify, redistribute, or sell this software
 * without explicit written permission from the author.
 * Violators will be prosecuted under applicable laws.
 */


class AttendanceManager {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
        this.initializeTable();
    }

    bindEvents() {
        // Global functions for onclick handlers
        window.updateStatus = (attendanceId, status) => this.updateStatus(attendanceId, status);
        window.updateNotes = (attendanceId, notes) => this.updateNotes(attendanceId, notes);
        window.checkIn = (attendanceId) => this.checkIn(attendanceId);
        window.toggleSelectAll = () => this.toggleSelectAll();
        window.bulkCheckIn = () => this.bulkCheckIn();
    }

    initializeTable() {
        // Add search functionality
        this.addSearchFunctionality();
        
        // Add sorting to table headers
        this.addSortingFunctionality();
        
        // Initialize tooltips
        this.initTooltips();
    }

    addSearchFunctionality() {
        // Create search input if it doesn't exist
        let searchInput = document.getElementById('attendanceSearch');
        if (!searchInput) {
            const cardHeader = document.querySelector('.card-header h6').parentElement;
            const searchDiv = document.createElement('div');
            searchDiv.className = 'd-flex align-items-center';
            searchDiv.innerHTML = `
                <h6 class="m-0 font-weight-bold text-primary me-auto">Attendance List</h6>
                <input type="text" class="form-control form-control-sm" id="attendanceSearch" 
                       placeholder="Search members..." style="width: 200px;">
            `;
            cardHeader.innerHTML = '';
            cardHeader.appendChild(searchDiv);
            searchInput = document.getElementById('attendanceSearch');
        }

        searchInput.addEventListener('input', (e) => {
            this.filterTable(e.target.value);
        });
    }

    addSortingFunctionality() {
        const headers = document.querySelectorAll('#attendanceTable th');
        headers.forEach((header, index) => {
            if (index > 0 && index < headers.length - 1) { // Skip checkbox and actions columns
                header.style.cursor = 'pointer';
                header.style.userSelect = 'none';
                header.innerHTML += ' <i class="fas fa-sort text-muted"></i>';
                
                header.addEventListener('click', () => {
                    this.sortTable(index);
                });
            }
        });
    }

    initTooltips() {
        const tooltips = document.querySelectorAll('[title]');
        tooltips.forEach(element => {
            new bootstrap.Tooltip(element);
        });
    }

    filterTable(searchTerm) {
        const table = document.getElementById('attendanceTable');
        const rows = table.querySelectorAll('tbody tr');
        const term = searchTerm.toLowerCase();

        rows.forEach(row => {
            const memberName = row.cells[1].textContent.toLowerCase();
            const email = row.cells[2].textContent.toLowerCase();
            const visible = memberName.includes(term) || email.includes(term);
            row.style.display = visible ? '' : 'none';
        });

        this.updateVisibleCount();
    }

    sortTable(columnIndex) {
        const table = document.getElementById('attendanceTable');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        // Determine sort direction
        const header = table.querySelectorAll('th')[columnIndex];
        const currentDirection = header.dataset.sortDirection || 'asc';
        const newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
        
        // Update header icons
        table.querySelectorAll('th i.fa-sort, th i.fa-sort-up, th i.fa-sort-down').forEach(icon => {
            icon.className = 'fas fa-sort text-muted';
        });
        
        const icon = header.querySelector('i');
        if (icon) {
            icon.className = `fas fa-sort-${newDirection === 'asc' ? 'up' : 'down'} text-primary`;
        }
        
        header.dataset.sortDirection = newDirection;

        rows.sort((a, b) => {
            const aVal = a.cells[columnIndex].textContent.trim();
            const bVal = b.cells[columnIndex].textContent.trim();
            
            let comparison = aVal.localeCompare(bVal);
            return newDirection === 'asc' ? comparison : -comparison;
        });

        tbody.innerHTML = '';
        rows.forEach(row => tbody.appendChild(row));
    }

    updateVisibleCount() {
        const table = document.getElementById('attendanceTable');
        const visibleRows = table.querySelectorAll('tbody tr:not([style*="display: none"])').length;
        const totalRows = table.querySelectorAll('tbody tr').length;
        
        let countElement = document.getElementById('visibleCount');
        if (!countElement) {
            countElement = document.createElement('small');
            countElement.id = 'visibleCount';
            countElement.className = 'text-muted ms-2';
            document.querySelector('.card-header').appendChild(countElement);
        }
        
        countElement.textContent = visibleRows === totalRows ? 
            `(${totalRows} members)` : 
            `(${visibleRows} of ${totalRows} members)`;
    }

    async updateStatus(attendanceId, status) {
        try {
            const response = await fetch('attendance.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update_status&attendance_id=${attendanceId}&status=${status}`
            });

            if (response.ok) {
                this.showNotification('Status updated successfully', 'success');
                this.updateRowStatus(attendanceId, status);
            } else {
                throw new Error('Failed to update status');
            }
        } catch (error) {
            console.error('Error updating status:', error);
            this.showNotification('Failed to update status', 'error');
        }
    }

    async updateNotes(attendanceId, notes) {
        // Debounce the notes update
        if (this.notesTimeout) {
            clearTimeout(this.notesTimeout);
        }

        this.notesTimeout = setTimeout(async () => {
            try {
                const response = await fetch('attendance.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=update_status&attendance_id=${attendanceId}&notes=${encodeURIComponent(notes)}`
                });

                if (response.ok) {
                    this.showNotification('Notes saved', 'success', 2000);
                }
            } catch (error) {
                console.error('Error updating notes:', error);
                this.showNotification('Failed to save notes', 'error');
            }
        }, 1000);
    }

    async checkIn(attendanceId) {
        try {
            const response = await fetch('attendance.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=check_in&attendance_id=${attendanceId}`
            });

            if (response.ok) {
                this.showNotification('Member checked in successfully', 'success');
                this.updateCheckInStatus(attendanceId);
            } else {
                throw new Error('Failed to check in');
            }
        } catch (error) {
            console.error('Error checking in:', error);
            this.showNotification('Failed to check in member', 'error');
        }
    }

    toggleSelectAll() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.member-checkbox');
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });

        this.updateBulkActions();
    }

    updateBulkActions() {
        const checkedBoxes = document.querySelectorAll('.member-checkbox:checked');
        const bulkBtn = document.querySelector('[onclick="bulkCheckIn()"]');
        
        if (bulkBtn) {
            bulkBtn.disabled = checkedBoxes.length === 0;
            bulkBtn.innerHTML = checkedBoxes.length > 0 ? 
                `<i class="fas fa-check-double"></i> Bulk Check-in (${checkedBoxes.length})` :
                `<i class="fas fa-check-double"></i> Bulk Check-in`;
        }
    }

    async bulkCheckIn() {
        const checkedBoxes = document.querySelectorAll('.member-checkbox:checked');
        if (checkedBoxes.length === 0) {
            this.showNotification('No members selected', 'warning');
            return;
        }

        if (!confirm(`Check in ${checkedBoxes.length} selected members?`)) {
            return;
        }

        const loading = this.showLoading();
        let successCount = 0;
        let errorCount = 0;

        for (const checkbox of checkedBoxes) {
            try {
                const attendanceId = checkbox.value;
                const response = await fetch('attendance.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=check_in&attendance_id=${attendanceId}`
                });

                if (response.ok) {
                    successCount++;
                    this.updateCheckInStatus(attendanceId);
                } else {
                    errorCount++;
                }
            } catch (error) {
                console.error('Error in bulk check-in:', error);
                errorCount++;
            }
        }

        this.hideLoading(loading);

        if (successCount > 0) {
            this.showNotification(`Successfully checked in ${successCount} members`, 'success');
        }
        if (errorCount > 0) {
            this.showNotification(`Failed to check in ${errorCount} members`, 'error');
        }

        // Clear selections
        document.getElementById('selectAll').checked = false;
        checkedBoxes.forEach(cb => cb.checked = false);
        this.updateBulkActions();
    }

    updateRowStatus(attendanceId, status) {
        const row = document.querySelector(`tr[data-attendance-id="${attendanceId}"]`);
        if (row) {
            const statusBadge = row.querySelector('.badge');
            const statusInfo = this.getStatusInfo(status);
            
            statusBadge.className = `badge bg-${statusInfo.class}`;
            statusBadge.innerHTML = `<i class="fas fa-${statusInfo.icon}"></i> ${statusInfo.text}`;
            
            // Update the select dropdown
            const select = row.querySelector('select');
            if (select) {
                select.value = status;
            }
        }
    }

    updateCheckInStatus(attendanceId) {
        const row = document.querySelector(`tr[data-attendance-id="${attendanceId}"]`);
        if (row) {
            const checkInCell = row.cells[5]; // Check-in time column
            const actionCell = row.cells[7]; // Actions column
            
            checkInCell.innerHTML = `
                <small class="text-success">
                    <i class="fas fa-clock"></i>
                    ${new Date().toLocaleTimeString('en-US', { 
                        hour: 'numeric', 
                        minute: '2-digit',
                        hour12: false 
                    })}
                </small>
            `;
            
            actionCell.innerHTML = `
                <span class="text-success">
                    <i class="fas fa-check-circle"></i> Checked in
                </span>
            `;
        }
    }

    getStatusInfo(status) {
        const statuses = {
            'accepted': { class: 'success', icon: 'check', text: 'Accepted' },
            'declined': { class: 'danger', icon: 'times', text: 'Declined' },
            'unanswered': { class: 'warning', icon: 'question', text: 'Unanswered' },
            'present': { class: 'success', icon: 'check-circle', text: 'Present' },
            'absent': { class: 'danger', icon: 'times-circle', text: 'Absent' }
        };
        
        return statuses[status] || { class: 'secondary', icon: 'question', text: ucfirst(status) };
    }

    showNotification(message, type = 'info', duration = 4000) {
        if (window.spondManager) {
            window.spondManager.showToast('Attendance', message, type);
        } else {
            // Fallback notification
            console.log(`${type.toUpperCase()}: ${message}`);
        }
    }

    showLoading() {
        if (window.spondManager) {
            return window.spondManager.showLoading();
        }
        return null;
    }

    hideLoading(overlay) {
        if (window.spondManager) {
            window.spondManager.hideLoading(overlay);
        }
    }
}

// Initialize attendance manager when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.attendanceManager = new AttendanceManager();
    
    // Initialize checkbox change handlers
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('member-checkbox')) {
            window.attendanceManager.updateBulkActions();
        }
    });
    
    // Add keyboard shortcuts specific to attendance
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + A to select all
        if ((e.ctrlKey || e.metaKey) && e.key === 'a' && e.target.tagName !== 'INPUT') {
            e.preventDefault();
            const selectAll = document.getElementById('selectAll');
            if (selectAll) {
                selectAll.checked = true;
                selectAll.dispatchEvent(new Event('change'));
                window.toggleSelectAll();
            }
        }
        
        // Ctrl/Cmd + Enter for bulk check-in
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            e.preventDefault();
            const bulkBtn = document.querySelector('[onclick="bulkCheckIn()"]');
            if (bulkBtn && !bulkBtn.disabled) {
                window.bulkCheckIn();
            }
        }
    });
});

// Export function for the attendance table
function exportAttendanceList() {
    const table = document.getElementById('attendanceTable');
    if (table && window.spondManager) {
        const eventName = document.querySelector('h4').textContent;
        const eventDate = new Date().toISOString().split('T')[0];
        const filename = `attendance_${eventName.replace(/\s+/g, '_')}_${eventDate}.csv`;
        
        // Use the utility function from main.js
        exportTableToCSV(table, filename);
    }
}

// Print function for the attendance list
function printAttendanceList() {
    const attendanceCard = document.querySelector('.card.shadow');
    const eventInfo = document.querySelector('.card').cloneNode(true);
    
    const printContent = document.createElement('div');
    printContent.appendChild(eventInfo);
    printContent.appendChild(attendanceCard.cloneNode(true));
    
    if (window.printElement) {
        printElement(printContent);
    } else {
        window.print();
    }
}

// Add export and print buttons to the toolbar
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        const toolbar = document.querySelector('.btn-toolbar .btn-group');
        if (toolbar) {
            const exportBtn = document.createElement('button');
            exportBtn.type = 'button';
            exportBtn.className = 'btn btn-sm btn-outline-secondary';
            exportBtn.innerHTML = '<i class="fas fa-download"></i> Export';
            exportBtn.onclick = exportAttendanceList;
            
            const printBtn = document.createElement('button');
            printBtn.type = 'button';
            printBtn.className = 'btn btn-sm btn-outline-secondary';
            printBtn.innerHTML = '<i class="fas fa-print"></i> Print';
            printBtn.onclick = printAttendanceList;
            
            toolbar.appendChild(exportBtn);
            toolbar.appendChild(printBtn);
        }
    }, 100);
});