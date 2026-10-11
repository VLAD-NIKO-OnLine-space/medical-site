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
