/* Lógica del cliente - Filtro de casas, modales e ingreso por ID */

document.addEventListener('DOMContentLoaded', () => {
  // Búsqueda y filtrado de casas
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

  // Manejo de Modal de ID de Acceso
  const idModal = document.getElementById('idAccessModal');
  if (idModal) {
    // Si la URL tiene ?openId=1 o si el usuario no tiene sesión iniciada y entra por primera vez
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('openId') || idModal.classList.contains('auto-open')) {
      openIdModal();
    }
  }
});

function openIdModal() {
  const idModal = document.getElementById('idAccessModal');
  if (idModal) {
    idModal.classList.add('active');
    const input = document.getElementById('modalCodigoId');
    if (input) input.focus();
  }
}

function closeIdModal() {
  const idModal = document.getElementById('idAccessModal');
  if (idModal) {
    idModal.classList.remove('active');
  }
}

function confirmDelete(msg) {
  return confirm(msg || '¿Estás seguro de que deseas eliminar este registro?');
}
