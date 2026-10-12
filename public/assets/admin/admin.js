const form = document.querySelector("[data-editor]");
const dirtyNote = document.querySelector("[data-dirty-note]");
let dirty = false;
let uid = 0;

const markDirty = () => {
  dirty = true;
  dirtyNote.hidden = false;
};

form.addEventListener("input", markDirty);

// картинка: сразу показываем выбранный файл вместо текущего, на сайт он попадёт после «Сохранить»
form.addEventListener("change", (e) => {
  const input = e.target.closest("[data-image-input]");
  const file = input?.files[0];
  if (!file) return;
  const field = input.closest(".image-field");
  const preview = field.querySelector("[data-image-preview]");
  if (preview.src.startsWith("blob:")) URL.revokeObjectURL(preview.src);
  preview.src = URL.createObjectURL(file);
  field.querySelector("[data-image-name]").textContent = `Новая: ${file.name} (${(file.size / 1048576).toFixed(1)} МБ)`;
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
  // картинки нарезаются на сервере несколько секунд — показываем, что сохранение идёт
  const btn = form.querySelector('[type="submit"]');
  btn.disabled = true;
  btn.lastChild.textContent = "Сохраняем…";
});

window.addEventListener("beforeunload", (e) => {
  if (dirty) e.preventDefault();
});

// вернулись назад после сохранения — кнопка снова рабочая
window.addEventListener("pageshow", () => {
  const btn = form.querySelector('[type="submit"]');
  btn.disabled = false;
  btn.lastChild.textContent = "Сохранить";
});
