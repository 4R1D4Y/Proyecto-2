// en este script se guarda el menu hamburguesa (que ganas de una) para las opciones responsive de las paginas
// la parte renposive de este menu se encuentra en header.css

// Menu
// Selecciona el botón y el menú
const menuToggle = document.querySelector('.menu_toggle');
const navMenu = document.getElementById('menu');

// al pulsar el boton se pliega o despliega en menu
menuToggle.addEventListener('click', () => {
    // Alterna la clase 'is-active' en el menú para mostrarlo u ocultarlo
    navMenu.classList.toggle('is-active');
    
    // Cambiar el icono de hamburguesa (☰) a una X (✕) al abrir
    if (navMenu.classList.contains('is-active')) {
        menuToggle.textContent = '✕';
    } else {
        menuToggle.textContent = '☰';
    }
});