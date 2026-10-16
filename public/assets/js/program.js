const section = document.querySelector(".program");
const el = section?.querySelector("[data-chapters]");

// ползунок пропорционален видимой части, но не уже 64px — иначе на телефоне (видна 1 глава из 17) его не поймать пальцем
const MIN_DRAG = 64;
const minDragSize = (swiper) => {
  const { scrollbar } = swiper;
  swiper.params.scrollbar.dragSize = "auto";
  scrollbar.updateSize();
  if (scrollbar.dragEl.offsetWidth < MIN_DRAG) {
    swiper.params.scrollbar.dragSize = MIN_DRAG;
    scrollbar.updateSize();
  }
  scrollbar.setTranslate();
};

// слайдер глав; Swiper качается только на подходе к секции, как в отзывах
const init = async () => {
  const [{ S: Swiper }, ...modules] = await Promise.all([
    import("./vendor/swiper/shared/swiper-core.js"),
    import("./vendor/swiper/modules/navigation.js"),
    import("./vendor/swiper/modules/scrollbar.js"),
    import("./vendor/swiper/modules/keyboard.js"),
    import("./vendor/swiper/modules/a11y.js"),
  ]);
  new Swiper(el, {
    modules: modules.map((m) => m.default),
    // ширина карточек задана в CSS
    slidesPerView: "auto",
    spaceBetween: 16,
    speed: 700,
    grabCursor: true,
    keyboard: { enabled: true, onlyInViewport: true },
    navigation: {
      prevEl: section.querySelector("[data-chapters-prev]"),
      nextEl: section.querySelector("[data-chapters-next]"),
    },
    // ползунок под карточками: его можно тащить, клик по полосе — переход к этому месту;
    // после отпускания слайдер доводится до ближайшей главы
    scrollbar: {
      el: section.querySelector("[data-chapters-scrollbar]"),
      draggable: true,
    },
    on: {
      afterInit: minDragSize,
      resize: minDragSize,
    },
    a11y: {
      prevSlideMessage: "Предыдущая глава",
      nextSlideMessage: "Следующая глава",
      slideLabelMessage: "Глава {{index}} из {{slidesLength}}",
    },
    breakpoints: {
      641: { spaceBetween: 24 },
    },
  });
};

if (el) {
  const loader = new IntersectionObserver(
    ([entry]) => {
      if (!entry.isIntersecting) return;
      loader.disconnect();
      init();
    },
    { rootMargin: "1000px 0px" },
  );
  loader.observe(section);
}

// «Обмен опытом»: аккаунтов покупателей пока нет — всем открывается окно с предложением купить доступ.
// Когда появятся аккаунты, оплатившим кнопка будет открывать чат главы
const exModal = document.querySelector("[data-exchange-modal]");
if (exModal) {
  const chapterEl = exModal.querySelector("[data-exchange-chapter]");
  let opener = null;
  section.addEventListener("click", (e) => {
    const btn = e.target.closest("[data-exchange]");
    if (!btn) return;
    opener = btn;
    chapterEl.textContent = btn.dataset.chapter;
    exModal.showModal();
    // фокус на само окно, а не на крестик — иначе вокруг крестика сразу рамка фокуса
    exModal.focus();
  });
  // закрываем по крестику, ссылке «Подробнее» и клику по затемнению — только если кнопку мыши
  // и нажали, и отпустили снаружи окна (выделение текста не закрывает)
  const outside = (e) => {
    const r = exModal.getBoundingClientRect();
    return e.clientX < r.left || e.clientX > r.right || e.clientY < r.top || e.clientY > r.bottom;
  };
  let pressedOutside = false;
  exModal.addEventListener("pointerdown", (e) => {
    pressedOutside = e.target === exModal && outside(e);
  });
  exModal.addEventListener("click", (e) => {
    if ((pressedOutside && e.target === exModal && outside(e)) || e.target.closest("[data-exchange-close]")) exModal.close();
    pressedOutside = false;
  });
  exModal.addEventListener("close", () => opener?.focus({ preventScroll: true }));
}
