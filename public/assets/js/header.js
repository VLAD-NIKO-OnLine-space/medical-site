const header = document.querySelector(".header");
const mobileHeader = window.matchMedia("(max-width: 640px)");
const update = () => header.classList.toggle("is-scrolled", !mobileHeader.matches && window.scrollY > 10);

update();
window.addEventListener("scroll", update, { passive: true });
mobileHeader.addEventListener("change", update);

// бургер-меню: иконка меню перетекает в крестик и обратно
const burger = header.querySelector("[data-burger]");
const menu = document.getElementById(burger.getAttribute("aria-controls"));
const menuClose = menu.querySelector("[data-menu-close]");
const menuBackdrop = header.querySelector("[data-menu-backdrop]");
const outsideDrawer = [
  ...header.querySelectorAll(".header__bar > :not(.header__nav)"),
  ...document.querySelectorAll("body > main, body > footer"),
];
const burgerIcon = burger.querySelector("morph-icon");
const openD = burgerIcon.querySelector("path").getAttribute("d");
const closeD = burger.dataset.closeIcon;

const syncDrawer = (open) => {
  const drawerOpen = mobileHeader.matches && open;
  menu.inert = mobileHeader.matches && !open;
  for (const element of outsideDrawer) element.inert = drawerOpen;
  if (drawerOpen) menuClose.focus({ preventScroll: true });
};

const setOpen = (open) => {
  if (open === header.classList.contains("is-menu-open")) return;
  header.classList.toggle("is-menu-open", open);
  document.documentElement.classList.toggle("is-menu-open", open);
  burger.setAttribute("aria-expanded", String(open));
  burger.setAttribute("aria-label", open ? "Закрыть меню" : "Открыть меню");
  burgerIcon.morphTo?.(open ? closeD : openD);
  syncDrawer(open);
  if (!open && mobileHeader.matches) burger.focus({ preventScroll: true });
};

syncDrawer(false);
mobileHeader.addEventListener("change", () => syncDrawer(header.classList.contains("is-menu-open")));
burger.addEventListener("click", () => setOpen(!header.classList.contains("is-menu-open")));
menuClose.addEventListener("click", () => setOpen(false));
menuBackdrop.addEventListener("click", () => setOpen(false));
menu.addEventListener("click", (e) => {
  if (e.target.closest("a")) setOpen(false);
});
document.addEventListener("click", (e) => {
  if (!header.contains(e.target)) setOpen(false);
});
document.addEventListener("keydown", (e) => {
  if (!header.classList.contains("is-menu-open")) return;
  if (e.key === "Escape") {
    setOpen(false);
    burger.focus({ preventScroll: true });
  }
  if (e.key === "Tab" && mobileHeader.matches) {
    const controls = menu.querySelectorAll("a[href], button:not([disabled])");
    const last = controls[controls.length - 1];
    if (document.activeElement === (e.shiftKey ? menuClose : last)) {
      e.preventDefault();
      (e.shiftKey ? last : menuClose).focus();
    }
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
