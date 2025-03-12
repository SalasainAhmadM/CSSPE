/**
 * CSSPE Inventory & Information System
 * Admin UI JavaScript
 * 
 * This file contains reusable JavaScript functions for the admin interface
 */

// DOM Elements
const sidebar = document.getElementById('sidebar');
const sidebarOverlay = document.getElementById('sidebarOverlay');
const toggleSidebarBtn = document.getElementById('toggleSidebar');
const mainContent = document.getElementById('mainContent');
const searchInput = document.getElementById('search');

// ===== SIDEBAR FUNCTIONS =====

/**
 * Toggle sidebar visibility on mobile devices
 */
function toggleSidebar() {
    const isSidebarVisible = sidebar.classList.contains('left-0');
    
    if (isSidebarVisible) {
        // Hide sidebar
        sidebar.classList.remove('left-0');
        sidebar.classList.add('-left-64');
        sidebarOverlay.classList.add('hidden');
        mainContent.classList.remove('lg:ml-0');
    } else {
        // Show sidebar
        sidebar.classList.remove('-left-64');
        sidebar.classList.add('left-0');
        sidebarOverlay.classList.remove('hidden');
    }
}

/**
 * Initialize sidebar functionality
 */
function initSidebar() {
    // Check if elements exist to prevent errors
    if (!sidebar || !sidebarOverlay || !toggleSidebarBtn) return;
    
    // Add event listeners
    toggleSidebarBtn.addEventListener('click', toggleSidebar);
    sidebarOverlay.addEventListener('click', toggleSidebar);
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 1024) {
            // On large screens, ensure sidebar is visible
            sidebar.classList.remove('-left-64');
            sidebar.classList.add('left-0');
            sidebarOverlay.classList.add('hidden');
            mainContent.classList.add('lg:ml-64');
        } else {
            // On small screens, hide sidebar by default
            if (!sidebar.classList.contains('left-0')) {
                sidebar.classList.remove('left-0');
                sidebar.classList.add('-left-64');
            }
        }
    });
    
    // Set initial state based on screen size
    if (window.innerWidth < 1024) {
        sidebar.classList.remove('left-0');
        sidebar.classList.add('-left-64');
    }
}

// ===== TABLE FUNCTIONS =====

/**
 * Filter table rows by search term
 */
function filterTable(searchTerm) {
    const tableRows = document.querySelectorAll('tbody tr');
    let visibleRows = 0;
    
    // Skip if no rows or searchTerm is empty and return value is true
    if (tableRows.length === 0) return true;
    
    searchTerm = searchTerm.toLowerCase();
    
    tableRows.forEach(row => {
        // Skip empty state rows
        if (row.querySelector('td[colspan]')) return;
        
        const rowText = row.textContent.toLowerCase();
        if (rowText.includes(searchTerm)) {
            row.classList.remove('hidden');
            visibleRows++;
        } else {
            row.classList.add('hidden');
        }
    });
    
    // Show or hide empty state message
    const tbody = document.querySelector('tbody');
    const noResultsRow = document.getElementById('noSearchResults');
    
    if (visibleRows === 0) {
        if (!noResultsRow) {
            const tr = document.createElement('tr');
            tr.id = 'noSearchResults';
            tr.innerHTML = `
                <td colspan="10" class="py-8 text-center text-gray-500">
                    <div class="flex flex-col items-center">
                        <i class="fas fa-search text-gray-300 text-4xl mb-3"></i>
                        <p>No matching records found</p>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        }
    } else if (noResultsRow) {
        noResultsRow.remove();
    }
    
    return visibleRows > 0;
}

/**
 * Filter table by role/position
 */
function filterByRole() {
    const roleFilter = document.getElementById('roleFilter');
    if (!roleFilter) return;
    
    const selectedRole = roleFilter.value.toLowerCase();
    const tableRows = document.querySelectorAll('tbody tr');
    let visibleRows = 0;
    
    tableRows.forEach(row => {
        // Skip empty state rows
        if (row.querySelector('td[colspan]')) return;
        
        // Role is usually in the 8th column, but check first
        const roleCell = row.querySelector('td:nth-child(8)');
        if (!roleCell) return;
        
        const role = roleCell.textContent.trim().toLowerCase();
        
        if (
            selectedRole === "" || // Show all if no role is selected
            (selectedRole === "instructor" && role.includes("instructor")) || // Match "instructor"
            (selectedRole === "admin" &&
                (role.includes("information_admin") ||
                    role.includes("super_admin") ||
                    role.includes("inventory_admin"))
            ) // Match any admin type
        ) {
            row.classList.remove('hidden');
            visibleRows++;
        } else {
            row.classList.add('hidden');
        }
    });
    
    // Show or hide empty state message
    const tbody = document.querySelector('tbody');
    const noResultsRow = document.getElementById('noFilterResults');
    
    if (visibleRows === 0) {
        if (!noResultsRow) {
            const tr = document.createElement('tr');
            tr.id = 'noFilterResults';
            tr.innerHTML = `
                <td colspan="10" class="py-8 text-center text-gray-500">
                    <div class="flex flex-col items-center">
                        <i class="fas fa-filter text-gray-300 text-4xl mb-3"></i>
                        <p>No records found matching the selected filter</p>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        }
    } else if (noResultsRow) {
        noResultsRow.remove();
    }
    
    // Apply search filter after role filter if there's a search term
    const searchTerm = searchInput ? searchInput.value.trim() : '';
    if (searchTerm) {
        filterTable(searchTerm);
    }
    
    // Hide any "no results" rows if we have visible rows
    if (visibleRows > 0) {
        const noResultsRow = document.getElementById('noFilterResults');
        if (noResultsRow) noResultsRow.remove();
    }
    
    return visibleRows > 0;
}

// ===== ACTION MENU FUNCTIONS =====

/**
 * Toggle mobile action menu
 */
function toggleActionMenu(button) {
    // Close all other open menus first
    document.querySelectorAll('.action-menu').forEach(menu => {
        if (menu !== button.nextElementSibling) {
            menu.classList.add('hidden');
        }
    });
    
    // Toggle this menu
    const menu = button.nextElementSibling;
    menu.classList.toggle('hidden');
    
    // Close menu when clicking outside
    const closeMenu = function(e) {
        if (!menu.contains(e.target) && e.target !== button) {
            menu.classList.add('hidden');
            document.removeEventListener('click', closeMenu);
        }
    };
    
    if (!menu.classList.contains('hidden')) {
        // Add a slight delay before adding the event listener to prevent immediate closure
        setTimeout(() => {
            document.addEventListener('click', closeMenu);
        }, 100);
    }
}

/**
 * Close all action menus
 */
function closeAllActionMenus() {
    document.querySelectorAll('.action-menu').forEach(menu => {
        menu.classList.add('hidden');
    });
}

// ===== MODAL FUNCTIONS =====

/**
 * Open the edit modal
 */
function editProgram(id, image, firstName, middleName, lastName, email, address, contactNo, department, rank, status) {
    const editModal = document.getElementById('editModal');
    if (!editModal) return;
    
    // Set the form values
    document.getElementById('faculty_id').value = id;
    document.getElementById('first_name').value = firstName;
    document.getElementById('middle_name').value = middleName;
    document.getElementById('last_name').value = lastName;
    document.getElementById('email').value = email;
    document.getElementById('address').value = address;
    document.getElementById('contact_no').value = contactNo;
    document.getElementById('department').value = department;
    document.getElementById('rank').value = rank;
    document.getElementById('status').value = status;
    
    // Set the image preview
    const imageElem = document.getElementById('faculty_image');
    imageElem.src = image && image.trim() !== '' ? image : '../assets/img/CSSPE.png';
    
    // Show the modal
    editModal.classList.remove('hidden');
    
    // Add event listener to close modal when clicking outside
    const modalContent = editModal.querySelector('.bg-white');
    editModal.addEventListener('click', function(e) {
        if (!modalContent.contains(e.target)) {
            cancelEdit();
        }
    });
}

/**
 * Close the edit modal
 */
function cancelEdit() {
    const editModal = document.getElementById('editModal');
    if (!editModal) return;
    
    editModal.classList.add('hidden');
}

/**
 * Preview image before upload
 */
function previewImage() {
    const input = document.getElementById('imageUpload');
    const image = document.getElementById('faculty_image');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            image.src = e.target.result;
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

/**
 * View user details in a modal (especially helpful on mobile)
 */
function viewDetails(userId) {
    // Find the row containing the user data
    const row = document.querySelector(`tr[data-id="${userId}"]`) || 
                document.querySelector(`button[onclick*="viewDetails(${userId})"]`)?.closest('tr');
    
    if (!row) return;
    
    // Extract data from the row
    const cells = row.querySelectorAll('td');
    const fullName = cells[0].textContent.trim();
    const imageSrc = cells[1]?.querySelector('img')?.src;
    
    let email = 'N/A';
    let address = 'N/A';
    let contactNo = 'N/A';
    let department = 'N/A';
    let position = 'N/A';
    let role = 'N/A';
    let status = 'N/A';
    
    // Try to extract from visible or hidden columns
    if (cells[1]) email = cells[1].textContent.trim();
    if (cells[2]) address = cells[2].textContent.trim();
    if (cells[3]) contactNo = cells[3].textContent.trim();
    if (cells[4]) department = cells[4].textContent.trim();
    if (cells[5]) position = cells[5].textContent.trim();
    if (cells[6]) role = cells[6].textContent.trim();
    if (cells[7]) {
        const statusBadge = cells[7].querySelector('span');
        status = statusBadge ? statusBadge.textContent.trim() : cells[7].textContent.trim();
    }
    
    // Try to extract data from the data attribute if available
    try {
        const userData = row.dataset.user ? JSON.parse(row.dataset.user) : null;
        if (userData) {
            if (userData.name) fullName = userData.name;
            if (userData.email) email = userData.email;
            if (userData.address) address = userData.address;
            if (userData.contact) contactNo = userData.contact;
            if (userData.department) department = userData.department;
            if (userData.position) position = userData.position;
            if (userData.role) role = userData.role;
            if (userData.status) status = userData.status;
        }
    } catch (e) {
        console.error('Error parsing user data:', e);
    }
    
    // Populate the details modal
    const detailsModal = document.getElementById('detailsModal');
    const detailsContent = document.getElementById('userDetailsContent');
    
    if (!detailsModal || !detailsContent) return;
    
    detailsContent.innerHTML = `
        <div class="flex flex-col items-center mb-6">
            ${imageSrc ? `<img src="${imageSrc}" alt="User Image" class="w-24 h-24 rounded-full object-cover mb-3">` : ''}
            <h3 class="text-xl font-semibold">${fullName}</h3>
            <p class="text-gray-500 text-sm">${position}</p>
        </div>
        
        <div class="space-y-3">
            <div class="flex border-b border-gray-200 pb-2">
                <div class="w-1/3 font-semibold">Email:</div>
                <div class="w-2/3">${email}</div>
            </div>
            <div class="flex border-b border-gray-200 pb-2">
                <div class="w-1/3 font-semibold">Address:</div>
                <div class="w-2/3">${address}</div>
            </div>
            <div class="flex border-b border-gray-200 pb-2">
                <div class="w-1/3 font-semibold">Contact:</div>
                <div class="w-2/3">${contactNo}</div>
            </div>
            <div class="flex border-b border-gray-200 pb-2">
                <div class="w-1/3 font-semibold">Department:</div>
                <div class="w-2/3">${department}</div>
            </div>
            <div class="flex border-b border-gray-200 pb-2">
                <div class="w-1/3 font-semibold">Position:</div>
                <div class="w-2/3">${position}</div>
            </div>
            ${role !== 'N/A' ? `
            <div class="flex border-b border-gray-200 pb-2">
                <div class="w-1/3 font-semibold">Role:</div>
                <div class="w-2/3">${role}</div>
            </div>
            ` : ''}
            ${status !== 'N/A' ? `
            <div class="flex">
                <div class="w-1/3 font-semibold">Status:</div>
                <div class="w-2/3">
                    <span class="px-2 py-1 rounded-full text-xs font-semibold 
                        ${status.toLowerCase().includes('activate') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                        ${status}
                    </span>
                </div>
            </div>
            ` : ''}
        </div>
    `;
    
    // Update action buttons if they exist
    const approveBtn = document.getElementById('detailsApproveBtn');
    const deleteBtn = document.getElementById('detailsDeleteBtn');
    
    if (approveBtn && userId) {
        approveBtn.onclick = function() {
            closeDetailsModal();
            if (typeof approveUser === 'function') approveUser(userId);
        };
    }
    
    if (deleteBtn && userId) {
        deleteBtn.onclick = function() {
            closeDetailsModal();
            if (typeof deleteUser === 'function') deleteUser(userId);
        };
    }
    
    // Show the modal
    detailsModal.classList.remove('hidden');
}

/**
 * Close the details modal
 */
function closeDetailsModal() {
    const detailsModal = document.getElementById('detailsModal');
    if (!detailsModal) return;
    
    detailsModal.classList.add('hidden');
}

// ===== UTILITY FUNCTIONS =====

/**
 * Toggle password visibility
 */
function togglePasswordVisibility() {
    const passwordField = document.getElementById('password');
    const toggleBtn = document.getElementById('togglePassword');
    
    if (!passwordField || !toggleBtn) return;
    
    // Toggle password visibility
    const type = passwordField.type === 'password' ? 'text' : 'password';
    passwordField.type = type;
    
    // Toggle icon
    toggleBtn.classList.toggle('fa-eye');
    toggleBtn.classList.toggle('fa-eye-slash');
}

/**
 * Print table functionality
 */
function printTable() {
    // Add print-only class to body
    document.body.classList.add('print-only');
    
    // Create styles for printing
    const style = document.createElement('style');
    style.innerHTML = `
        @media print {
            body * {
                visibility: hidden;
            }
            .no-print {
                display: none !important;
            }
            #mainContent, #mainContent * {
                visibility: visible;
            }
            #mainContent {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                margin: 0 !important;
                padding: 0 !important;
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            th, td {
                border: 1px solid #ddd;
                padding: 8px;
            }
            thead {
                background-color: #f2f2f2;
                color: black !important;
            }
            th {
                color: black !important;
            }
        }
    `;
    document.head.appendChild(style);
    
    // Print the page
    window.print();
    
    // Remove temporary elements
    setTimeout(() => {
        document.body.classList.remove('print-only');
        document.head.removeChild(style);
    }, 100);
}

/**
 * Delete user with confirmation
 */
function deleteUser(userId) {
    // Check if SweetAlert2 is available
    if (typeof Swal === 'undefined') {
        if (confirm('Are you sure you want to delete this user?')) {
            window.location.href = `?delete_id=${userId}`;
        }
        return;
    }
    
    // Use SweetAlert2 for better UX
    Swal.fire({
        title: 'Confirm Deletion',
        text: 'Are you sure you want to delete this user? This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `?delete_id=${userId}`;
        }
    });
}

/**
 * Approve user with confirmation
 */
function approveUser(userId) {
    // Check if SweetAlert2 is available
    if (typeof Swal === 'undefined') {
        if (confirm('Are you sure you want to approve this user?')) {
            window.location.href = `?approve_id=${userId}`;
        }
        return;
    }
    
    // Use SweetAlert2 for better UX
    Swal.fire({
        title: 'Confirm Approval',
        text: 'Are you sure you want to approve this user?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, approve',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `?approve_id=${userId}`;
        }
    });
}

// ===== EVENT LISTENERS =====

document.addEventListener('DOMContentLoaded', function() {
    // Initialize sidebar
    initSidebar();
    
    // Set up search functionality
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            filterTable(this.value);
        });
    }
    
    // Set up role filter if it exists
    const roleFilter = document.getElementById('roleFilter');
    if (roleFilter) {
        roleFilter.addEventListener('change', filterByRole);
    }
    
    // Set up password toggle
    const togglePasswordBtn = document.getElementById('togglePassword');
    if (togglePasswordBtn) {
        togglePasswordBtn.addEventListener('click', togglePasswordVisibility);
    }
    
    // Close action menus when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.action-menu') && !e.target.closest('button[onclick*="toggleActionMenu"]')) {
            closeAllActionMenus();
        }
    });
    
    // Handle escape key for modals and sidebar
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const editModal = document.getElementById('editModal');
            const detailsModal = document.getElementById('detailsModal');
            
            if (editModal && !editModal.classList.contains('hidden')) {
                cancelEdit();
            }
            
            if (detailsModal && !detailsModal.classList.contains('hidden')) {
                closeDetailsModal();
            }
            
            if (sidebar && window.innerWidth < 1024 && !sidebar.classList.contains('-left-64')) {
                toggleSidebar();
            }
        }
    });
    
    // Make table rows focusable for better accessibility
    document.querySelectorAll('tbody tr').forEach(row => {
        if (!row.querySelector('td[colspan]')) {
            row.setAttribute('tabindex', '0');
            row.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    // Try to find the user ID
                    const viewBtn = row.querySelector('[onclick*="viewDetails"]');
                    if (viewBtn) {
                        const match = viewBtn.getAttribute('onclick').match(/viewDetails\((\d+)\)/);
                        if (match && match[1]) {
                            viewDetails(match[1]);
                        }
                    }
                }
            });
        }
    });
});