// ядро Swiper без бандла: экспортирует класс под коротким именем S
import { S as Swiper } from "./vendor/swiper/shared/swiper-core.js";
import Navigation from "./vendor/swiper/modules/navigation.js";
import Pagination from "./vendor/swiper/modules/pagination.js";
import Keyboard from "./vendor/swiper/modules/keyboard.js";
import A11y from "./vendor/swiper/modules/a11y.js";

const section = document.querySelector(".reviews");
const el = section.querySelector("[data-reviews]");

new Swiper(el, {
  modules: [Navigation, Pagination, Keyboard, A11y],
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
