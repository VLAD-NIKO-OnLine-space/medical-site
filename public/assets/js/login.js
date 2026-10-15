// вход: показать/скрыть пароль; пока аккаунтов клиентов нет — вместо отправки показываем сообщение,
// пароль не уходит на сервер
const form = document.querySelector("[data-login-form]");

if (form) {
  const toggle = form.querySelector("[data-password-toggle]");
  const password = form.querySelector("#login-password");
  const notice = form.querySelector("[data-login-notice]");

  toggle.addEventListener("click", () => {
    const show = password.type === "password";
    password.type = show ? "text" : "password";
    toggle.setAttribute("aria-pressed", String(show));
    toggle.setAttribute("aria-label", show ? "Скрыть пароль" : "Показать пароль");
    password.focus();
  });

  form.addEventListener("submit", (e) => {
    e.preventDefault();
    if (!form.reportValidity()) return;
    notice.hidden = false;
    notice.animate?.([{ opacity: 0, transform: "translateY(-6px)" }, { opacity: 1, transform: "none" }], { duration: 300, easing: "ease-out" });
  });
}
