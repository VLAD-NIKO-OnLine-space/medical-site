const section = document.querySelector(".reviews");
const el = section.querySelector("[data-reviews]");

// Swiper (~40 КБ) качается только на подходе к секции: первому экрану он не нужен,
// а до запуска карточки и так стоят рядами за счёт своих стилей
const init = async () => {
  // ядро Swiper без бандла: экспортирует класс под коротким именем S
  const [{ S: Swiper }, ...modules] = await Promise.all([
    import("./vendor/swiper/shared/swiper-core.js"),
    import("./vendor/swiper/modules/navigation.js"),
    import("./vendor/swiper/modules/pagination.js"),
    import("./vendor/swiper/modules/keyboard.js"),
    import("./vendor/swiper/modules/a11y.js"),
  ]);
  new Swiper(el, {
    modules: modules.map((m) => m.default),
    slidesPerView: 1.08,
    spaceBetween: 16,
    speed: 700,
    grabCursor: true,
    keyboard: { enabled: true, onlyInViewport: true },
    navigation: {
      prevEl: section.querySelector("[data-reviews-prev]"),
      nextEl: section.querySelector("[data-reviews-next]"),
    },
    pagination: {
      el: section.querySelector("[data-reviews-pagination]"),
      clickable: true,
    },
    a11y: {
      prevSlideMessage: "Предыдущий отзыв",
      nextSlideMessage: "Следующий отзыв",
      paginationBulletMessage: "Перейти к отзыву {{index}}",
      slideLabelMessage: "Отзыв {{index}} из {{slidesLength}}",
    },
    breakpoints: {
      641: { slidesPerView: 2, spaceBetween: 20 },
      1241: { slidesPerView: 3, spaceBetween: 24 },
    },
  });
  // Swiper задал карточкам свою ширину — высота могла измениться
  lockHeight();
};

const loader = new IntersectionObserver(
  ([entry]) => {
    if (!entry.isIntersecting) return;
    loader.disconnect();
    init();
  },
  { rootMargin: "1000px 0px" },
);
loader.observe(section);

// карточки появляются по очереди, когда секция доходит до экрана;
// прячутся только здесь, чтобы при сбое скрипта отзывы остались видны
section.classList.add("is-animated");
const observer = new IntersectionObserver(
  ([entry]) => {
    if (!entry.isIntersecting) return;
    section.classList.add("is-inview");
    observer.disconnect();
  },
  { threshold: 0.2 },
);
observer.observe(section);

// фильтр «Все / Положительные / С замечаниями»: в слайдере остаются только подходящие карточки
// (тон карточки — data-tone, его задаёт оценка автора отзыва)
const slides = [...el.querySelectorAll(".reviews__slide")];
const wrapper = el.querySelector(".swiper-wrapper");
const empty = section.querySelector("[data-reviews-empty]");
const pagination = section.querySelector("[data-reviews-pagination]");
const filterBtns = [...section.querySelectorAll("[data-reviews-filter]")];
const emptyText = {
  positive: "Положительных отзывов пока нет.",
  critical: "Отзывов с замечаниями пока нет.",
};
const calm = matchMedia("(prefers-reduced-motion: reduce)");

// карточки в ряду тянутся по самой высокой; после фильтра «соседей» меньше и карточки сжимались бы —
// поэтому высоту меряем по всем отзывам сразу и закрепляем (пересчёт при смене ширины)
const lockHeight = () => {
  const current = [...wrapper.children];
  slides.forEach((s) => (s.style.minHeight = ""));
  wrapper.replaceChildren(...slides);
  const h = Math.max(...slides.map((s) => s.offsetHeight));
  wrapper.replaceChildren(...current);
  slides.forEach((s) => (s.style.minHeight = `${h}px`));
};
let resizeTimer;
addEventListener("resize", () => {
  clearTimeout(resizeTimer);
  resizeTimer = setTimeout(lockHeight, 150);
});
document.fonts?.ready.then(lockHeight);

filterBtns.forEach((btn) =>
  btn.addEventListener("click", () => {
    const tone = btn.dataset.reviewsFilter;
    lockHeight();
    filterBtns.forEach((b) => b.setAttribute("aria-pressed", String(b === btn)));
    const shown = slides.filter((s) => tone === "all" || s.dataset.tone === tone);
    shown.forEach((s, i) => s.style.setProperty("--i", i));
    wrapper.replaceChildren(...shown);
    const none = shown.length === 0;
    el.style.display = none ? "none" : "";
    pagination.style.display = none ? "none" : "";
    empty.hidden = !none;
    if (none) empty.textContent = emptyText[tone];
    if (el.swiper) {
      el.swiper.update();
      el.swiper.slideTo(0, 0);
    }
    // карточки по очереди чуть поднимаются; без прозрачности: полупрозрачный ряд отключает размытие
    // стеклянных карточек (backdrop-filter), и они мерцали. Двигаем слайды, а не обёртку — её сдвигает Swiper
    if (!calm.matches) {
      shown.slice(0, 4).forEach((s, i) =>
        s.animate([{ transform: "translateY(16px)" }, { transform: "none" }], { duration: 450, delay: i * 60, easing: "cubic-bezier(0.22, 1, 0.36, 1)", fill: "backwards" }),
      );
    }
  }),
);
