// видео в окне поверх сайта: плеер создаётся при открытии и удаляется при закрытии, чтобы звук не продолжал играть
const modal = document.querySelector("[data-video-modal]");

if (modal) {
  const frame = modal.querySelector("[data-video-frame]");

  document.addEventListener("click", (e) => {
    const btn = e.target.closest("[data-video-open]");
    if (!btn) return;
    const { videoType, videoSrc } = btn.dataset;
    let player;
    if (videoType === "file") {
      player = document.createElement("video");
      player.controls = true;
      player.autoplay = true;
      player.playsInline = true;
    } else {
      player = document.createElement("iframe");
      player.allow = "autoplay; fullscreen; picture-in-picture; encrypted-media";
      player.allowFullscreen = true;
      player.title = btn.getAttribute("aria-label");
    }
    player.src = videoSrc;
    frame.replaceChildren(player);
    modal.showModal();
  });

  modal.addEventListener("click", (e) => {
    if (e.target === modal || e.target.closest("[data-video-close]")) modal.close();
  });
  modal.addEventListener("close", () => frame.replaceChildren());
}
