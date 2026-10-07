const header = document.querySelector(".header");
const update = () => header.classList.toggle("is-scrolled", window.scrollY > 10);

update();
window.addEventListener("scroll", update, { passive: true });
