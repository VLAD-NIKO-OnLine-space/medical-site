// чат «Обмен опытом»: кнопка с data-chat="номер главы" открывает окно, сообщения подгружаются с /chat/,
// пока окно открыто — обновляются раз в 15 секунд
const modal = document.querySelector("[data-chat-modal]");

if (modal) {
  const list = modal.querySelector("[data-chat-list]");
  const form = modal.querySelector("[data-chat-form]");
  const input = form.querySelector("textarea");
  const send = form.querySelector("button");
  const error = modal.querySelector("[data-chat-error]");
  const chapterEl = modal.querySelector("[data-chat-chapter]");
  const auth = modal.querySelector("[data-chat-auth]");

  // вход сброшен или срок доступа кончился: вместо формы — понятное объяснение и кнопка, обновления останавливаются
  const lockOut = (data) => {
    clearInterval(timer);
    form.hidden = true;
    error.hidden = true;
    auth.hidden = false;
    auth.querySelector("[data-chat-auth-text]").textContent = data.error;
    const link = auth.querySelector("[data-chat-auth-link]");
    if (data.auth === "expired") {
      link.href = "/kabinet/";
      link.lastChild.textContent = "В личный кабинет";
    }
    list.hidden = true;
  };
  let chapter = 0;
  let timer;
  let opener;
  let lastId = -1;

  const esc = (s) => s.replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[c]);

  const render = (messages) => {
    const newest = messages.at(-1)?.id ?? 0;
    if (newest === lastId) return;
    const nearBottom = list.scrollHeight - list.scrollTop - list.clientHeight < 80;
    list.innerHTML = messages.length
      ? messages
          .map(
            (m) => `<article class="chat-msg${m.own ? " chat-msg--own" : ""}">
<span class="chat-msg__avatar" aria-hidden="true">${esc(m.initials.toUpperCase())}</span>
<div class="chat-msg__body"><p class="chat-msg__meta"><strong>${m.own ? "Вы" : esc(m.name)}</strong> · ${esc(m.time)}</p><p class="chat-msg__text">${esc(m.text).replace(/\n/g, "<br>")}</p></div>
</article>`,
          )
          .join("")
      : '<p class="chat-modal__empty">Пока никто не написал. Поделитесь опытом первым — вашим советом могут воспользоваться другие.</p>';
    if (lastId === -1 || nearBottom) list.scrollTop = list.scrollHeight;
    lastId = newest;
  };

  const showError = (msg) => {
    error.textContent = msg || "";
    error.hidden = !msg;
  };

  const load = async () => {
    try {
      const res = await fetch(`/chat/?ch=${chapter}`, { headers: { Accept: "application/json" }, cache: "no-store" });
      const data = await res.json();
      if (data.ok) render(data.messages);
      else if (data.auth) lockOut(data);
      else showError(data.error);
    } catch {
      /* при обрыве связи просто ждём следующего обновления */
    }
  };

  document.addEventListener("click", (e) => {
    const btn = e.target.closest("[data-chat]");
    if (!btn) return;
    opener = btn;
    chapter = +btn.dataset.chat;
    chapterEl.textContent = btn.dataset.chapter;
    lastId = -1;
    list.innerHTML = '<p class="chat-modal__empty">Загружаем сообщения…</p>';
    showError();
    form.hidden = false;
    auth.hidden = true;
    list.hidden = false;
    modal.showModal();
    modal.focus();
    load();
    timer = setInterval(load, 15000);
  });

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const text = input.value.trim();
    if (!text) return;
    send.disabled = true;
    showError();
    try {
      const body = new FormData();
      body.set("ch", chapter);
      body.set("text", text);
      const res = await fetch("/chat/", { method: "POST", body, headers: { Accept: "application/json" } });
      const data = await res.json();
      if (data.ok) {
        input.value = "";
        lastId = -2; // своё сообщение — всегда прокрутить вниз
        render(data.messages);
        list.scrollTop = list.scrollHeight;
      } else if (data.auth) lockOut(data);
      else showError(data.error);
    } catch {
      showError("Нет связи с сайтом. Проверьте интернет и попробуйте ещё раз.");
    } finally {
      send.disabled = false;
      input.focus();
    }
  });

  // Enter — отправить, Shift+Enter — новая строка
  input.addEventListener("keydown", (e) => {
    if (e.key === "Enter" && !e.shiftKey && !e.isComposing) {
      e.preventDefault();
      form.requestSubmit();
    }
  });

  // закрытие: крестик, Esc, клик по затемнению (нажали и отпустили снаружи окна)
  const outside = (e) => {
    const r = modal.getBoundingClientRect();
    return e.clientX < r.left || e.clientX > r.right || e.clientY < r.top || e.clientY > r.bottom;
  };
  let pressedOutside = false;
  modal.addEventListener("pointerdown", (e) => (pressedOutside = e.target === modal && outside(e)));
  modal.addEventListener("click", (e) => {
    if ((pressedOutside && e.target === modal && outside(e)) || e.target.closest("[data-chat-close]")) modal.close();
    pressedOutside = false;
  });
  modal.addEventListener("close", () => {
    clearInterval(timer);
    opener?.focus({ preventScroll: true });
  });
}
