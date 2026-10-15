const form = document.querySelector("[data-editor]");
const dirtyNote = document.querySelector("[data-dirty-note]");
// «Сохранить» в шапке: неактивна, пока ничего не изменено
const saveBtn = document.querySelector("[data-save]");
const saveLabel = saveBtn.querySelector("span");
let dirty = false;
let uid = 0;

const markDirty = () => {
  dirty = true;
  dirtyNote.hidden = false;
  saveBtn.disabled = false;
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

// «Отзывы»: панели переключаются меню слева, видна одна
const paneLinks = [...document.querySelectorAll("[data-pane-link]")];
const paneInput = document.querySelector("[data-pane-input]");
const showPane = (id) => {
  const pane = document.querySelector(`[data-pane="${id}"]`);
  if (!pane) return;
  document.querySelectorAll("[data-pane]").forEach((p) => (p.hidden = p !== pane));
  paneLinks.forEach((a) => (a.dataset.paneLink === id ? a.setAttribute("aria-current", "page") : a.removeAttribute("aria-current")));
  if (paneInput) paneInput.value = id;
  history.replaceState(null, "", `#${id}`);
};
paneLinks.forEach((a) =>
  a.addEventListener("click", (e) => {
    e.preventDefault();
    showPane(a.dataset.paneLink);
    window.scrollTo({ top: 0 });
  }),
);
// после «Опубликовать» и «Сохранить» адрес заканчивается на #панель — открываем её; так же при смене # без перезагрузки
if (paneLinks.length && location.hash) showPane(location.hash.slice(1));
if (paneLinks.length) addEventListener("hashchange", () => showPane(location.hash.slice(1)));

// браузерная проверка обязательных полей с прокруткой к первому пустому
form.addEventListener("submit", (e) => {
  const invalid = form.querySelector(":invalid");
  if (invalid) {
    e.preventDefault();
    // пустое поле в скрытой панели — сначала показать её
    const pane = invalid.closest("[data-pane][hidden]");
    if (pane) showPane(pane.dataset.pane);
    invalid.scrollIntoView({ block: "center", behavior: "smooth" });
    invalid.focus({ preventScroll: true });
    invalid.reportValidity();
    return;
  }
  dirty = false;
  // картинки нарезаются на сервере несколько секунд — показываем, что сохранение идёт
  saveBtn.disabled = true;
  saveLabel.textContent = "Сохраняем…";
});

window.addEventListener("beforeunload", (e) => {
  if (dirty) e.preventDefault();
});

// вернулись назад после сохранения — подпись обратно; активна, только если на странице есть изменения
window.addEventListener("pageshow", () => {
  saveLabel.textContent = "Сохранить";
  saveBtn.disabled = !dirty && dirtyNote.hidden;
});

// модерация отзывов: удаление — только после подтверждения
document.addEventListener("click", (e) => {
  const btn = e.target.closest("[data-confirm]");
  if (btn && !confirm(btn.dataset.confirm)) e.preventDefault();
});

// меню слева: подсвечен блок, который сейчас вверху экрана (под шапкой)
// в «Отзывах» меню переключает панели — там подсветка по прокрутке не нужна
const navLinks = [...document.querySelectorAll(".sidenav:not([data-panes]) a[href^='#']")];
const targets = navLinks.map((a) => document.getElementById(decodeURIComponent(a.hash.slice(1))));

const spy = () => {
  const line = document.querySelector(".topbar").offsetHeight + 40;
  let active = 0;
  targets.forEach((el, i) => {
    if (el && el.getBoundingClientRect().top <= line) active = i;
  });
  // докрутили до конца — последний пункт, даже если его блок не дошёл до верха
  if (window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 2) active = targets.length - 1;
  navLinks.forEach((a, i) => (i === active ? a.setAttribute("aria-current", "location") : a.removeAttribute("aria-current")));
};

if (navLinks.length) {
  let ticking = false;
  window.addEventListener(
    "scroll",
    () => {
      if (ticking) return;
      ticking = true;
      requestAnimationFrame(() => {
        spy();
        ticking = false;
      });
    },
    { passive: true },
  );
  window.addEventListener("resize", spy);
  spy();
}

// «Обновить» в модерации: свежая очередь и счётчики без перезагрузки страницы.
// Ещё сама проверяет раз в минуту и при возвращении на вкладку, пока открыта модерация
const modList = document.querySelector("[data-moderation-list]");
const modBtn = document.querySelector("[data-moderation-refresh]");
const modUpdated = document.querySelector("[data-moderation-updated]");

const refreshModeration = async (silent = false) => {
  if (!modList || modBtn.disabled) return;
  modBtn.disabled = true;
  modBtn.classList.add("is-loading");
  try {
    const res = await fetch(`${location.pathname}?part=moderation`, { headers: { Accept: "application/json" }, cache: "no-store" });
    if (res.status === 401) {
      location.reload(); // сессия закончилась — покажется вход
      return;
    }
    const data = await res.json();
    modList.innerHTML = data.html;
    document.querySelectorAll("[data-queue-count]").forEach((b) => {
      b.textContent = data.count;
      b.hidden = data.count === 0;
    });
    modUpdated.textContent = `Обновлено в ${data.time}`;
  } catch {
    if (!silent) modUpdated.textContent = "Не удалось обновить — проверьте интернет";
  } finally {
    modBtn.disabled = false;
    modBtn.classList.remove("is-loading");
  }
};

if (modList) {
  modBtn.addEventListener("click", () => refreshModeration());
  const visibleModeration = () => document.visibilityState === "visible" && !document.querySelector("[data-pane=moderation]").hidden;
  setInterval(() => visibleModeration() && refreshModeration(true), 60000);
  document.addEventListener("visibilitychange", () => visibleModeration() && refreshModeration(true));
}
