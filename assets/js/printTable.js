function printTable() {
    const tableContainer = document.querySelector('.tableContainer');
    const tableHeader = document.querySelector('.textContainer');

    // Temporarily hide the last column
    const rows = tableContainer.querySelectorAll('tr');
    rows.forEach(row => {
        const cells = row.children;
        if (cells.length > 0) {
            cells[cells.length - 1].style.display = 'none'; // Hide the last cell
        }
    });

    // Get the HTML for printing
    const printContent = tableContainer.outerHTML;
    const printtableHeader = tableHeader.outerHTML;
    const printWindow = window.open('', '', 'width=800, height=600');
    printWindow.document.write(`
    <html>
    <head>
        <title>Print Table</title>
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
                width:90px;
            }                   
            th {
                background-color: #f4f4f4;
                font-weight: bold;
            }
            tr:nth-child(even) {
                background-color: #f9f9f9;
            }
            .table-header {
                text-align: center;
                font-size: 24px;
                margin-bottom: 10px;
            }
        </style>
    </head>
    <body>
        ${printtableHeader}
        ${printContent}
    </body>
    </html>
    `);
    printWindow.document.close();
    printWindow.print();

    // Restore the visibility of the last column
    rows.forEach(row => {
        const cells = row.children;
        if (cells.length > 0) {
            cells[cells.length - 1].style.display = ''; // Restore visibility
        }
    });
}