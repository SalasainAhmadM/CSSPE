function searchCard() {
    const searchInput = document.querySelector('.searchBar').value.toLowerCase();
    const inventoryItems = document.querySelectorAll('.subInventoryContainer1');

    inventoryItems.forEach(item => {
        const title = item.querySelector('.infoContainer1 p').textContent.toLowerCase();
        if (title.includes(searchInput)) {
            item.style.display = ''; // Show item
        } else {
            item.style.display = 'none'; // Hide item
        }
    });
}

function searchCard2() {
    const searchInput = document.querySelector('.searchBar2').value.toLowerCase();
    const inventoryItems = document.querySelectorAll('.subInventoryContainer');

    inventoryItems.forEach(item => {
        const itemName = item.querySelector('.infoContainer p').textContent.toLowerCase();
        if (itemName.includes(searchInput)) {
            item.style.display = ''; // Show item
        } else {
            item.style.display = 'none'; // Hide item
        }
    });
}