/**
 * Instant Client/AJAX Live Search & Filter Helper
 */

document.addEventListener('DOMContentLoaded', () => {
  // Generic Table Live Filter
  const tableSearchInputs = document.querySelectorAll('[data-table-search]');
  
  tableSearchInputs.forEach(input => {
    const targetTableId = input.getAttribute('data-table-search');
    const table = document.getElementById(targetTableId);
    if (!table) return;

    input.addEventListener('keyup', () => {
      const filterValue = input.value.toLowerCase().trim();
      const rows = table.querySelectorAll('tbody tr');

      rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(filterValue)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    });
  });

  // Card Grid Live Filter (For Companies / Jobs cards)
  const cardSearchInput = document.getElementById('card-search-input');
  if (cardSearchInput) {
    cardSearchInput.addEventListener('keyup', () => {
      const query = cardSearchInput.value.toLowerCase().trim();
      const cardItems = document.querySelectorAll('.searchable-card');

      cardItems.forEach(card => {
        const text = card.textContent.toLowerCase();
        if (text.includes(query)) {
          card.style.display = '';
        } else {
          card.style.display = 'none';
        }
      });
    });
  }
});
