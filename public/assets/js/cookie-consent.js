const banner = document.querySelector("[data-cookie-banner]");
const key = "bestoncolog_cookie_consent";
const cookiePrefix = key + "=";

function isChoice(value) {
  return value === "accepted" || value === "necessary";
}

function hasSavedChoice() {
  try {
    if (isChoice(localStorage.getItem(key))) return true;
  } catch {
    // Некоторые настройки браузера блокируют localStorage.
  }

  try {
    const cookie = document.cookie.split("; ").find(item => item.startsWith(cookiePrefix));
    return isChoice(cookie?.slice(cookiePrefix.length));
  } catch {
    return false;
  }
}

function saveChoice(choice) {
  try {
    localStorage.setItem(key, choice);
  } catch {
    const secure = location.protocol === "https:" ? "; Secure" : "";
    document.cookie = cookiePrefix + choice + "; Path=/; Max-Age=31536000; SameSite=Lax" + secure;
  }
}

let selected = false;
function updateBanner() {
  banner.hidden = selected || hasSavedChoice();
}

banner.addEventListener("click", event => {
  const choice = event.target.closest("[data-cookie-choice]")?.dataset.cookieChoice;
  if (!isChoice(choice)) return;

  selected = true;
  banner.hidden = true;
  try {
    saveChoice(choice);
  } catch {
    // Текущая страница запомнит выбор, даже если браузер запретил оба хранилища.
  }
});

window.addEventListener("pageshow", updateBanner);
window.addEventListener("storage", event => {
  if (event.key === key || event.key === null) updateBanner();
});
updateBanner();
