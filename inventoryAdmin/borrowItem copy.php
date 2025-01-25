<?php
session_start();
require_once '../conn/conn.php';
require_once '../conn/auth.php';

validateSessionRole('inventory_admin');
$inventoryAdminId = $_SESSION['user_id'];

$query = "SELECT first_name, middle_name, last_name, image FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $inventoryAdminId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $fullName = trim($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']);
    $image = $row['image'];
} else {
    $fullName = "User Not Found";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/borrowing.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
</head>
<style>
    .hover-unique-id {
        position: relative;
        cursor: pointer;
    }

    .hover-unique-id .tooltip {
        display: none;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: #f5f5f5;
        color: #000;
        border: 1px solid #ccc;
        padding: 5px;
        white-space: pre-wrap;
        z-index: 10;
        font-family: Arial, sans-serif;
        font-size: 1rem;
        text-align: center;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        border-radius: 5px;
        min-width: 100px;
        max-width: 200px;
    }

    .hover-unique-id:hover .tooltip {
        display: block;
    }
</style>

<body>
    <div class="body">
        <div class="sidebar">
            <div class="sidebarContent">
                <div class="arrowContainer" style="margin-left: 80rem;" id="toggleButton">
                    <div class="subArrowContainer">
                        <img class="hideIcon" src="../assets/img/arrow.png" alt="">
                    </div>
                </div>
            </div>
            <div class="userContainer">
                <div class="subUserContainer">
                    <div class="userPictureContainer">
                        <div class="subUserPictureContainer">
                            <img class="subUserPictureContainer"
                                src="../assets/img/<?= !empty($image) ? htmlspecialchars($image) : 'CSSPE.png' ?>"
                                alt="">
                        </div>
                    </div>

                    <div class="userPictureContainer1">
                        <p><?php echo $fullName; ?></p>
                    </div>
                </div>

                <div class="navContainer">
                    <div class="subNavContainer">

                        <a href="../homePage/">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Home</p>
                                </div>
                            </div>
                        </a>

                        <a href="../inventoryAdmin/inventory.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Inventories</p>
                                </div>
                            </div>
                        </a>

                        <a href="../inventoryAdmin/borrowing.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Borrow request</p>
                                </div>
                            </div>
                        </a>

                        <a href="../inventoryAdmin/borrowItem.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Borrowed Item</p>
                                </div>
                            </div>
                        </a>

                        <a href="../inventoryAdmin/notification.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Notification</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="subUserContainer">
                    <a href="../logout.php">
                        <div style="margin-left: 1.5rem;" class="userPictureContainer1">
                            <p>Logout</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <div class="mainContainer" style="margin-left: 250px;">
            <div class="container">
                <div class="headerContainer">
                    <div class="subHeaderContainer">
                        <div class="logoContainer">
                            <img class="logo" src="../assets/img/CSSPE.png" alt="">
                        </div>

                        <div class="collegeNameContainer">
                            <p>CSSPE Inventory & Information System</p>
                        </div>
                    </div>
                </div>

                <div class="textContainer">
                    <p class="text">Borrow Item</p>
                </div>

                <div class="searchContainer">
                    <input id="searchBar" class="searchBar" type="text" placeholder="Search..." oninput="filterTable()">
                    <div class="printButton" style="gap: 1rem; display: flex; width: 90%;">
                        <button onclick="printTable()" class="addButton size">Print</button>
                        <select name="" class="addButton size" id="">
                            <option value="">Filter</option>
                            <option value="">All</option>
                            <option value="">This day</option>
                            <option value=""> This week</option>
                            <option value="">This month</option>
                        </select>
                    </div>
                </div>

                <div class="tableContainer">
                    <table>
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Item Id</th>
                                <th>Item Name</th>
                                <th>Brand</th>
                                <th>Quantity</th>
                                <th>Borrow Date</th>
                                <th>Return Date</th>
                                <th>Fullname</th>
                                <th>Contact Number</th>
                                <th>Email</th>
                                <th>Note</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Rows will be dynamically inserted here -->
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <div class="addContainer" style="display: none; background-color: none;">
        <div class="addContainer">
            <div class="subAddContainer"
                style="background-color: white; padding: 20px; border-radius: 10px;transform: scale(0.80);">
                <div class="titleContainer">
                    <p>Return Item</p>
                </div>

                <div class="subLoginContainer">

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for="itemName"
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Item
                            Name:</label>
                        <input id="itemName" class="inputEmail" type="text">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for="itemBrand"
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Brand:</label>
                        <input id="itemBrand" class="inputEmail" type="text">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for="quantityBorrowed"
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Borrowed
                            Quantity:</label>
                        <input id="quantityBorrowed" class="inputEmail" type="number">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for="returnQuantity"
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Return
                            Quantity:</label>
                        <input id="returnQuantity" class="inputEmail" type="number">
                    </div>
                    <input id="damaged" class="inputEmail" type="hidden">
                    <input id="lost" class="inputEmail" type="hidden">
                    <input id="replaced" class="inputEmail" type="hidden">
                    <!-- <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for="damaged"
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Damaged:</label>
                        <input id="damaged" class="inputEmail" type="number">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for="lost"
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Lost:</label>
                        <input id="lost" class="inputEmail" type="number">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for="replaced"
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Replaced:</label>
                        <input id="replaced" class="inputEmail" type="number">
                    </div> -->

                    <div class="inputContainer" style="gap: 0.5rem; justify-content: right; padding-right: 0.9rem;">
                        <button class="addButton" style="width: 6rem;" onclick="confirmReturn()">Confirm</button>
                        <button onclick="closeReturnModal()" class="addButton1" style="width: 6rem;">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- <div class="inputContainer" style="gap: 0.5rem;">
        <select class="inputEmail" name="" id="">
            <option value="">Update Status</option>
            <option value="">Lost</option>
            <option value="">Damaged</option>
            <option value="">Replaced</option>
            <option value="">Overdue</option>
            <option value="">Returned</option>
        </select>
    </div> -->

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmReturn() {
            const itemName = document.getElementById('itemName').value.trim();
            const itemBrand = document.getElementById('itemBrand').value.trim();
            const quantityBorrowed = document.getElementById('quantityBorrowed').value.trim();
            const returnQuantity = document.getElementById('returnQuantity').value.trim();
            const damaged = document.getElementById('damaged').value.trim();
            const lost = document.getElementById('lost').value.trim();
            const replaced = document.getElementById('replaced').value.trim();

            if (!returnQuantity || returnQuantity <= 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Return Quantity',
                    text: 'Please enter a valid return quantity.',
                    confirmButtonText: 'OK'
                });
                return;
            }

            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you want to confirm the return?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, confirm it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const transactionId = document.querySelector('.addContainer').getAttribute('data-transaction-id');

                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', './endpoints/return_items.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/json');

                    xhr.onreadystatechange = function () {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                if (response.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: response.message,
                                        showConfirmButton: false,
                                        timer: 3000
                                    });
                                    closeReturnModal();
                                    fetchTransactions();
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.message
                                    });
                                }
                            } catch (e) {
                                console.error('Error parsing response:', e);
                            }
                        }
                    };

                    const requestData = {
                        transaction_id: transactionId,
                        return_quantity: returnQuantity,
                        damaged: damaged,
                        lost: lost,
                        replaced: replaced,
                    };

                    xhr.send(JSON.stringify(requestData));
                }
            });
        }

        function openReturnModal(transactionId) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `./endpoints/return_transaction_details.php?transaction_id=${transactionId}`, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    console.log('Response received:', xhr.responseText); // Debug log
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.status === 'success') {
                            const transaction = response.data;

                            // Populate modal fields
                            document.getElementById('itemName').value = transaction.item_name || '';
                            document.getElementById('itemBrand').value = transaction.item_brand || '';
                            document.getElementById('quantityBorrowed').value = transaction.quantity_borrowed || '';
                            document.getElementById('returnQuantity').value = transaction.quantity_borrowed || '';
                            document.getElementById('damaged').value = 0;
                            document.getElementById('lost').value = 0;
                            document.getElementById('replaced').value = 0;

                            // Set the transaction ID as a data attribute
                            const modal = document.querySelector('.addContainer');
                            modal.setAttribute('data-transaction-id', transactionId);

                            // Show the modal
                            modal.style.display = 'flex';
                        } else {
                            alert(response.message);
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                    }
                }
            };
            xhr.send();
        }




        function closeReturnModal() {
            document.querySelector('.addContainer').style.display = 'none';
            document.querySelector('.addContainer').removeAttribute('data-transaction-id');
        }

        function fetchTransactions() {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', './endpoints/get_item_transactions_approved.php', true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.status === 'success') {
                            const transactions = response.data;
                            const tbody = document.querySelector('.tableContainer tbody');
                            tbody.innerHTML = '';

                            function formatTime24To12(timeString) {
                                if (!timeString) return 'N/A';
                                const [hour, minute] = timeString.split(':');
                                const hourInt = parseInt(hour, 10);
                                const isPM = hourInt >= 12;
                                const formattedHour = hourInt % 12 || 12;
                                const suffix = isPM ? 'PM' : 'AM';
                                return `${formattedHour}:${minute} ${suffix}`;
                            }

                            function formatDateTimeWithNewline(datetimeString) {
                                if (!datetimeString) return 'N/A';
                                const [date, time] = datetimeString.split(' ');
                                const formattedTime = formatTime24To12(time);
                                return `${date}<br>${formattedTime}`;
                            }

                            transactions.forEach(transaction => {
                                const row = document.createElement('tr');
                                row.innerHTML = `
                            <td>${transaction.transaction_id}</td>
                            <td>${transaction.item_id}</td>
                            <td>${transaction.item_name}</td>
                            <td>${transaction.item_brand}</td>
                            <td>
                                <span class="hover-unique-id">
                                    ${transaction.quantity_borrowed}
                                    <div class="tooltip">${transaction.unique_ids}</div>
                                </span>
                            </td>
                            <td>${transaction.borrowed_at ? formatDateTimeWithNewline(transaction.borrowed_at) : 'N/A'}</td>
                            <td>${transaction.return_date}</td>
                            <td>${transaction.first_name} ${transaction.last_name}</td>
                            <td>${transaction.contact_no}</td>
                            <td>${transaction.email}</td>
                            <td>
                                ${transaction.status_remark}
                                <button class="addButton" style="height: 2rem;" onclick="editStatusRemark(${transaction.transaction_id}, '${transaction.status_remark}')"><i class="fa-solid fa-pen-to-square"></i></button>
                            </td>
                            <td class="button">
                                <button class="addButton" style="width: 7rem;" onclick="handleReturnOptions(${transaction.transaction_id}, '${transaction.item_name}', ${transaction.quantity_borrowed}, '${transaction.unique_ids}')">Return</button>
                            </td>
                        `;
                                tbody.appendChild(row);
                            });
                        } else {
                            alert(response.message);
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                    }
                }
            };
            xhr.send();
        }

        function handleReturnOptions(transactionId, itemName, quantityBorrowed, uniqueIds) {
            Swal.fire({
                title: 'Choose Return Option',
                showCancelButton: true,
                confirmButtonText: 'Replaced',
                showDenyButton: true,
                showCloseButton: true,
                denyButtonText: 'Damaged',
                cancelButtonText: 'Lost',
                footer: `<button class="swal2-footer-button swal2-confirm swal2-styled" onclick="closeAndOpenReturnModal(${transactionId})">Return</button>`,
            }).then(result => {
                if (result.isConfirmed) {
                    handleReplaced(transactionId, itemName, quantityBorrowed, uniqueIds);
                } else if (result.isDenied) {
                    handleDamagedReturn(transactionId, itemName, quantityBorrowed, uniqueIds);
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    handleLostReturn(transactionId, itemName, quantityBorrowed, uniqueIds);
                }
            });
        }

        // Helper function to close the current modal and open the return modal
        function closeAndOpenReturnModal(transactionId) {
            Swal.close(); // Close the current SweetAlert2 modal
            openReturnModal(transactionId); // Open the return modal
        }


        function handleItemSelection(transactionId, itemName, uniqueIds, actionType, callback) {
            const uniqueIdList = uniqueIds.split(','); // Assuming uniqueIds is comma-separated
            const checkboxHtml = uniqueIdList.map(id => `
        <div>
            <input type="checkbox" id="${actionType}_${id}" value="${id}">
            <label for="${actionType}_${id}">${itemName} - ID: ${id}</label>
        </div>
    `).join('');

            Swal.fire({
                title: `Select ${actionType.charAt(0).toUpperCase() + actionType.slice(1)} Items`,
                html: checkboxHtml,
                showCancelButton: true,
                confirmButtonText: 'Submit',
            }).then(result => {
                if (result.isConfirmed) {
                    const selectedItems = uniqueIdList.filter(id => document.getElementById(`${actionType}_${id}`).checked);
                    console.log(`${actionType.charAt(0).toUpperCase() + actionType.slice(1)} Items:`, selectedItems);
                    if (callback) callback(selectedItems);
                }
            });
        }

        // Function to track damaged items
        function trackDamagedItems(transactionId, damagedItems) {
            // Simulate server request logic
            fetch('./endpoints/', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    transactionId: transactionId,
                    damagedItems: damagedItems,
                }),
            })
                .then(response => response.json())
                .then(data => {
                    console.log('Damaged items tracked successfully:', data);
                    Swal.fire('Success', 'Damaged items have been tracked.', 'success');
                })
                .catch(error => {
                    console.error('Error tracking damaged items:', error);
                    Swal.fire('Error', 'Failed to track damaged items.', 'error');
                });
        }

        // Function to track lost items
        function trackLostItems(transactionId, lostItems) {
            // Simulate server request logic
            fetch('./endpoints/', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    transactionId: transactionId,
                    lostItems: lostItems,
                }),
            })
                .then(response => response.json())
                .then(data => {
                    console.log('Lost items tracked successfully:', data);
                    Swal.fire('Success', 'Lost items have been tracked.', 'success');
                })
                .catch(error => {
                    console.error('Error tracking lost items:', error);
                    Swal.fire('Error', 'Failed to track lost items.', 'error');
                });
        }

        // Function to track replaced items
        function trackReplacedItems(transactionId, replacedItems) {
            // Simulate server request logic
            fetch('./endpoints', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    transactionId: transactionId,
                    replacedItems: replacedItems,
                }),
            })
                .then(response => response.json())
                .then(data => {
                    console.log('Replaced items tracked successfully:', data);
                    Swal.fire('Success', 'Replaced items have been tracked.', 'success');
                })
                .catch(error => {
                    console.error('Error tracking replaced items:', error);
                    Swal.fire('Error', 'Failed to track replaced items.', 'error');
                });
        }

        // Updated handlers using the tracking functions
        function handleDamagedReturn(transactionId, itemName, quantityBorrowed, uniqueIds) {
            handleItemSelection(transactionId, itemName, uniqueIds, 'damaged', damagedItems => {
                trackDamagedItems(transactionId, damagedItems);
            });
        }

        function handleLostReturn(transactionId, itemName, quantityBorrowed, uniqueIds) {
            handleItemSelection(transactionId, itemName, uniqueIds, 'lost', lostItems => {
                trackLostItems(transactionId, lostItems);
            });
        }

        function handleReplaced(transactionId, itemName, quantityBorrowed, uniqueIds) {
            handleItemSelection(transactionId, itemName, uniqueIds, 'replaced', replacedItems => {
                console.log('Replaced items processed:', replacedItems);
                trackReplacedItems(transactionId, replacedItems);
            });
        }


        function editStatusRemark(transactionId, currentRemark) {
            Swal.fire({
                title: 'Edit Status Remark',
                input: 'text',
                inputValue: currentRemark,
                showCancelButton: true,
                confirmButtonText: 'Update',
                cancelButtonText: 'Cancel',
                preConfirm: (newRemark) => {
                    return new Promise((resolve) => {
                        // Send the update request
                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', './endpoints/update_status_remark.php', true);
                        xhr.setRequestHeader('Content-Type', 'application/json');
                        xhr.onreadystatechange = function () {
                            if (xhr.readyState === 4) {
                                if (xhr.status === 200) {
                                    const response = JSON.parse(xhr.responseText);
                                    if (response.status === 'success') {
                                        resolve(response.message);
                                    } else {
                                        Swal.showValidationMessage(response.message);
                                    }
                                } else {
                                    Swal.showValidationMessage('An error occurred. Please try again.');
                                }
                            }
                        };
                        xhr.send(JSON.stringify({ transaction_id: transactionId, status_remark: newRemark }));
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Status remark updated successfully!',
                        showConfirmButton: false,
                        timer: 3000
                    }).then(() => {
                        fetchTransactions(); // Refresh the table
                    });
                }
            });
        }




        function closeReturnModal() {
            document.querySelector('.addContainer').style.display = 'none';
        }

        // Call fetchTransactions on page load
        document.addEventListener('DOMContentLoaded', fetchTransactions);

        // Function to filter the table based on the search input
        function filterTable() {
            const searchValue = document.getElementById('searchBar').value.toLowerCase();
            const tableRows = document.querySelectorAll('.tableContainer tbody tr');
            tableRows.forEach(row => {
                const itemName = row.children[1].textContent.toLowerCase();
                if (itemName.includes(searchValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function printTable() {
            const tableContainer = document.querySelector('.tableContainer');
            const rows = tableContainer.querySelectorAll('tr');

            const printHeader = document.querySelector('.searchContainer');
            printHeader.style.display = 'none';

            rows.forEach(row => {
                const cells = row.children;
                if (cells.length > 0) {
                    cells[cells.length - 1].style.display = 'none';
                }
            });

            const printContent = tableContainer.outerHTML;
            const printWindow = window.open('', '', 'width=800, height=600');
            printWindow.document.write(`
    <html>
    <head>
        <title>Table</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
                color: #333;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            th, td {
                border: 1px solid black;
                padding: 8px;
                text-align: left;
            }
            th, td img {
                width: 90px;
            }                   
            th {
                background-color: #f4f4f4;
                font-weight: bold;
            }
            tr:nth-child(even) {
                background-color: #f9f9f9;
            }
        </style>
    </head>
    <body>
        ${printContent}
    </body>
    </html>
    `);
            printWindow.document.close();
            printWindow.print();

            // Restore visibility after print
            printHeader.style.display = ''; // Restore the visibility of the "Print Table" text
            rows.forEach(row => {
                const cells = row.children;
                if (cells.length > 0) {
                    cells[cells.length - 1].style.display = ''; // Restore visibility
                }
            });
        }

    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/sidebar.js"></script>
    <script src="../assets/js/program.js"></script>
</body>

</html>