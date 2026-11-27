const toc = document.querySelector(".doc__toc");
const list = toc.querySelector("ol");
const entries = Array.from(toc.querySelectorAll("a"), (link) => ({
  link,
  section: document.getElementById(link.hash.slice(1)),
}));

let activeLink;
let updatePending = false;

function positionMarker(link) {
  list.style.setProperty("--toc-marker-offset", (link.offsetTop + 5) + "px");
  list.style.setProperty("--toc-marker-size", String(link.offsetHeight - 10));
}

function updateCurrentSection() {
  updatePending = false;
  const readingLine = parseFloat(getComputedStyle(entries[0].section).scrollMarginTop) + 4;
  let current = entries[0];
  for (const entry of entries) {
    if (entry.section.getBoundingClientRect().top > readingLine) break;
    current = entry;
  }
  const atPageEnd = window.scrollY + window.innerHeight >= document.documentElement.scrollHeight - 2;
  const link = atPageEnd ? entries[entries.length - 1].link : current.link;
  if (link === activeLink) return;

  activeLink?.removeAttribute("aria-current");
  link.setAttribute("aria-current", "location");
  activeLink = link;
  positionMarker(link);

  if (toc.scrollHeight > toc.clientHeight) {
    const tocRect = toc.getBoundingClientRect();
    const linkRect = link.getBoundingClientRect();
    if (linkRect.top < tocRect.top + 8) {
      toc.scrollTop += linkRect.top - tocRect.top - 8;
    } else if (linkRect.bottom > tocRect.bottom - 8) {
      toc.scrollTop += linkRect.bottom - tocRect.bottom + 8;
    }
  }
}

function scheduleUpdate() {
  if (updatePending) return;
  updatePending = true;
  requestAnimationFrame(updateCurrentSection);
}

function updateLayout() {
  if (activeLink) positionMarker(activeLink);
  scheduleUpdate();
}

window.addEventListener("scroll", scheduleUpdate, { passive: true });
window.addEventListener("resize", updateLayout);
window.addEventListener("hashchange", scheduleUpdate);
document.fonts.ready.then(updateLayout);
scheduleUpdate();
