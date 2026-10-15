// окно «Оставить отзыв»: кнопка активна, только когда заполнены обязательные поля (имя, оценка, текст);
// отправка без перезагрузки, ошибки сервера под полями, после успеха — «Спасибо»
const modal = document.querySelector("[data-review-modal]");

if (modal) {
  const form = modal.querySelector("[data-review-form]");
  const done = modal.querySelector("[data-review-done]");
  const submit = form.querySelector("[type=submit]");
  const submitLabel = submit.innerHTML;

  const showErrors = (errors = {}) => {
    form.querySelectorAll("[data-error]").forEach((el) => {
      const msg = errors[el.dataset.error] || "";
      el.textContent = msg;
      const input = form.elements[el.dataset.error];
      if (input && input.setAttribute) input.toggleAttribute("aria-invalid", Boolean(msg));
    });
    const first = Object.keys(errors).map((k) => form.elements[k]).find((el) => el && el.focus);
    first?.focus();
  };

  let sending = false;

  // те же правила, что на сервере: что ещё не заполнено
  const missing = () => {
    const list = [];
    const author = form.elements.author.value.trim();
    if ((author.match(/\p{L}/gu) || []).length < 2) list.push("имя");
    if (!form.elements.rating.value) list.push("оценку");
    if (form.elements.text.value.trim().length < 10) list.push("отзыв (от 10 символов)");
    return list;
  };

  const update = () => {
    submit.disabled = sending || missing().length > 0;
  };

  form.addEventListener("input", update);
  form.addEventListener("change", update);

  document.addEventListener("click", (e) => {
    if (e.target.closest("[data-review-open]")) {
      form.hidden = false;
      done.hidden = true;
      update();
      modal.showModal();
    }
  });

  // закрываем по клику на затемнение, только если кнопку мыши и нажали, и отпустили за пределами окна:
  // иначе выделение текста, отпущенное за краем поля, или клик по пустому отступу внутри окна его закрывали
  const outside = (e) => {
    const r = modal.getBoundingClientRect();
    return e.clientX < r.left || e.clientX > r.right || e.clientY < r.top || e.clientY > r.bottom;
  };
  let pressedOutside = false;
  modal.addEventListener("pointerdown", (e) => {
    pressedOutside = e.target === modal && outside(e);
  });
  modal.addEventListener("click", (e) => {
    if ((pressedOutside && e.target === modal && outside(e)) || e.target.closest("[data-review-close]")) modal.close();
    pressedOutside = false;
  });

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    if (missing().length) return;
    showErrors();
    sending = true;
    update();
    submit.classList.add("is-sending");
    submit.textContent = "Отправляем…";
    try {
      const res = await fetch(form.action, { method: "POST", body: new FormData(form), headers: { Accept: "application/json" } });
      const data = await res.json().catch(() => ({}));
      if (res.ok && data.ok) {
        form.reset();
        form.hidden = true;
        done.hidden = false;
        done.querySelector("button").focus();
      } else {
        showErrors(data.errors && Object.keys(data.errors).length ? data.errors : { form: "Не получилось отправить. Попробуйте ещё раз." });
      }
    } catch {
      showErrors({ form: "Нет связи с сайтом. Проверьте интернет и попробуйте ещё раз." });
    } finally {
      sending = false;
      submit.classList.remove("is-sending");
      submit.innerHTML = submitLabel;
      update();
    }
  });
}
