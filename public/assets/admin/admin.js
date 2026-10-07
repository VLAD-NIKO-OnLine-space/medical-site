const form = document.querySelector("[data-editor]");
const icons = JSON.parse(document.getElementById("icons-data").textContent);
const dirtyNote = document.querySelector("[data-dirty-note]");
let dirty = false;
let uid = 0;

const svg = (d) =>
  d
    ? `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="${d}"/></svg>`
    : "";

const markDirty = () => {
  dirty = true;
  dirtyNote.hidden = false;
};

form.addEventListener("input", markDirty);

form.addEventListener("change", (e) => {
  const select = e.target.closest("[data-icon-select]");
  if (select) {
    select.parentElement.querySelector(".field__icon-preview").innerHTML = svg(icons[select.value]);
  }
});

// списки: добавить, переместить, удалить элемент
form.addEventListener("click", (e) => {
  const btn = e.target.closest("[data-list-add], [data-list-up], [data-list-down], [data-list-remove]");
  if (!btn) return;
  const list = btn.closest("[data-list]");
  const item = btn.closest("[data-list-item]");

  if (btn.hasAttribute("data-list-add")) {
    const html = list.querySelector(":scope > [data-list-template]").innerHTML.replaceAll("__i__", `n${Date.now()}${uid++}`);
    const items = list.querySelector(":scope > [data-list-items]");
    items.insertAdjacentHTML("beforeend", html);
    items.lastElementChild.querySelector("input, textarea, select")?.focus();
  } else if (btn.hasAttribute("data-list-up")) {
    item.previousElementSibling?.before(item);
  } else if (btn.hasAttribute("data-list-down")) {
    item.nextElementSibling?.after(item);
  } else if (confirm("Удалить этот элемент?")) {
    item.remove();
  } else {
    return;
  }
  markDirty();
});

// браузерная проверка обязательных полей с прокруткой к первому пустому
form.addEventListener("submit", (e) => {
  const invalid = form.querySelector(":invalid");
  if (invalid) {
    e.preventDefault();
    invalid.scrollIntoView({ block: "center", behavior: "smooth" });
    invalid.focus({ preventScroll: true });
    invalid.reportValidity();
    return;
  }
  dirty = false;
});

window.addEventListener("beforeunload", (e) => {
  if (dirty) e.preventDefault();
});
