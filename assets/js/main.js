/* Lógica del cliente - Filtro de casas y confirmaciones */

document.addEventListener('DOMContentLoaded', () => {
  const searchInput = document.getElementById('searchHouses');
  const dayFilter = document.getElementById('filterDay');
  const houseCards = document.querySelectorAll('.house-card-item');

  function filterCards() {
    if (!houseCards.length) return;
    
    const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const selectedDay = dayFilter ? dayFilter.value : '';

    houseCards.forEach(card => {
      const name = card.getAttribute('data-name') || '';
      const sector = card.getAttribute('data-sector') || '';
      const day = card.getAttribute('data-day') || '';
      const host = card.getAttribute('data-host') || '';

      const matchesSearch = name.includes(query) || sector.includes(query) || host.includes(query);
      const matchesDay = !selectedDay || day === selectedDay;

      if (matchesSearch && matchesDay) {
        card.style.display = 'flex';
      } else {
        card.style.display = 'none';
      }
    });
  }

  if (searchInput) searchInput.addEventListener('input', filterCards);
  if (dayFilter) dayFilter.addEventListener('change', filterCards);
});

function confirmDelete(msg) {
  return confirm(msg || '¿Estás seguro de que deseas eliminar este registro?');
}
