// вход: показать/скрыть пароль
const form = document.querySelector("[data-login-form]");

if (form) {
  const toggle = form.querySelector("[data-password-toggle]");
  const password = form.querySelector("#login-password");

  toggle.addEventListener("click", () => {
    const show = password.type === "password";
    password.type = show ? "text" : "password";
    toggle.setAttribute("aria-pressed", String(show));
    toggle.setAttribute("aria-label", show ? "Скрыть пароль" : "Показать пароль");
    password.focus();
  });

  // повторная отправка, пока сервер отвечает, не нужна
  form.addEventListener("submit", () => {
    form.querySelector("[type=submit]").disabled = true;
  });
}
