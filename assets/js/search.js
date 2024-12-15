document.getElementById('search').addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr'); // Target all table rows in the tbody

    rows.forEach(row => {
        const locationCell = row.querySelector('td:first-child'); // Target the first <td> (Location column)
        if (locationCell) {
            const location = locationCell.textContent.toLowerCase();
            row.style.display = location.includes(filter) ? '' : 'none';
        }
    });
});