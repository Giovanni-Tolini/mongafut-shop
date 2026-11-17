(() => {
  const menuButton = document.getElementById('menu');
  const nav = document.getElementById('nav');
  if (menuButton && nav) {
    menuButton.addEventListener('click', () => nav.classList.toggle('is-open'));
  }
})();
