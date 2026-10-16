const header = document.querySelector(".header");
const update = () => header.classList.toggle("is-scrolled", window.scrollY > 10);

update();
window.addEventListener("scroll", update, { passive: true });

// бургер-меню: иконка меню перетекает в крестик и обратно
const burger = header.querySelector("[data-burger]");
const menu = document.getElementById(burger.getAttribute("aria-controls"));
const burgerIcon = burger.querySelector("morph-icon");
const openD = burgerIcon.querySelector("path").getAttribute("d");
const closeD = burger.dataset.closeIcon;

const setOpen = (open) => {
  if (open === header.classList.contains("is-menu-open")) return;
  header.classList.toggle("is-menu-open", open);
  document.documentElement.classList.toggle("is-menu-open", open);
  burger.setAttribute("aria-expanded", String(open));
  burger.setAttribute("aria-label", open ? "Закрыть меню" : "Открыть меню");
  burgerIcon.morphTo?.(open ? closeD : openD);
};

burger.addEventListener("click", () => setOpen(!header.classList.contains("is-menu-open")));
menu.addEventListener("click", (e) => {
  if (e.target.closest("a")) setOpen(false);
});
document.addEventListener("click", (e) => {
  if (!header.contains(e.target)) setOpen(false);
});
document.addEventListener("keydown", (e) => {
  if (e.key === "Escape" && header.classList.contains("is-menu-open")) {
    setOpen(false);
    burger.focus();
  }
});
window.matchMedia("(min-width: 1241px)").addEventListener("change", (e) => {
  if (e.matches) setOpen(false);
});

// меню аккаунта (почта клиента в шапке): плавно раскрывается по клику, закрывается кликом мимо и Esc
const account = header.querySelector("[data-account]");
if (account) {
  const toggle = account.querySelector("[data-account-toggle]");
  const accountMenu = account.querySelector("[data-account-menu]");
  const setAccount = (open) => {
    accountMenu.classList.toggle("is-open", open);
    toggle.setAttribute("aria-expanded", String(open));
  };
  toggle.addEventListener("click", () => setAccount(!accountMenu.classList.contains("is-open")));
  document.addEventListener("click", (e) => {
    if (!account.contains(e.target)) setAccount(false);
  });
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && accountMenu.classList.contains("is-open")) {
      setAccount(false);
      toggle.focus();
    }
  });
}
