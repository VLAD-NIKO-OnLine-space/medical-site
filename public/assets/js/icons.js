import { defineMorphIcon } from "./vendor/morphicons/element.js";

defineMorphIcon();

// <morph-icon data-hover="…">: при наведении или фокусе на ссылку, кнопку или [data-morph-trigger] иконка перетекает в data-hover и обратно
for (const icon of document.querySelectorAll("morph-icon[data-hover]")) {
  const rest = icon.querySelector("path").getAttribute("d");
  const hover = icon.dataset.hover;
  const trigger = icon.closest("a, button, [data-morph-trigger]") ?? icon;
  const morph = (d) => () => icon.morphTo(d);
  trigger.addEventListener("pointerenter", morph(hover));
  trigger.addEventListener("pointerleave", morph(rest));
  trigger.addEventListener("focus", morph(hover));
  trigger.addEventListener("blur", morph(rest));
}

// частые вопросы: плюс перетекает в крестик при открытии ответа и обратно при закрытии
for (const item of document.querySelectorAll(".faq__item")) {
  const toggle = item.querySelector("[data-open-icon]");
  const icon = toggle.querySelector("morph-icon");
  const closedD = icon.querySelector("path").getAttribute("d");
  const openD = toggle.dataset.openIcon;
  if (item.open) icon.set(openD);
  item.addEventListener("toggle", () => icon.morphTo(item.open ? openD : closedD));
}
