'use strict';
let selectedFilter = 'all';
const cards = [...document.querySelectorAll('.poll-card')];
const search = document.querySelector('#poll-search');
function filterPolls() {
  const term = (search?.value || '').toLowerCase().trim();
  let visible = 0;
  cards.forEach(card => {
    const show = (selectedFilter === 'all' || card.dataset.status === selectedFilter) && card.dataset.search.includes(term);
    card.hidden = !show;
    if (show) visible++;
  });
  const empty = document.querySelector('#no-polls');
  if (empty) empty.hidden = visible > 0;
}
document.querySelectorAll('[data-filter]').forEach(button => button.addEventListener('click', () => {
  selectedFilter = button.dataset.filter;
  document.querySelectorAll('[data-filter]').forEach(b => {
    const active = b === button;
    b.classList.toggle('selected', active);
    b.setAttribute('aria-pressed', String(active));
  });
  filterPolls();
}));
search?.addEventListener('input', filterPolls);
document.querySelectorAll('form[data-confirm]').forEach(form => form.addEventListener('submit', event => {
  if (!window.confirm(form.dataset.confirm)) event.preventDefault();
}));
document.querySelectorAll('input[type="datetime-local"]').forEach(input => input.addEventListener('change', () => {
  const start = document.querySelector('[name="starts_at"]');
  const end = document.querySelector('[name="ends_at"]');
  if (start && end) end.setCustomValidity(start.value && end.value && end.value <= start.value ? 'Choose an end time after the start time.' : '');
}));
