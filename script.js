// Gestion du mode sombre
const toggleDarkMode = document.getElementById('toggle-dark-mode');
const body = document.body;

toggleDarkMode.addEventListener('click', function () {
  body.classList.toggle('dark-mode');
  if (body.classList.contains('dark-mode')) {
    toggleDarkMode.textContent = 'Light Mode';
  } else {
    toggleDarkMode.textContent = 'Dark Mode';
  }
});
