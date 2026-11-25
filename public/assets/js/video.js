// видео в окне поверх сайта: плеер создаётся при открытии и удаляется при закрытии, чтобы звук не продолжал играть
const modal = document.querySelector("[data-video-modal]");

if (modal) {
  const frame = modal.querySelector("[data-video-frame]");
  const hlsType = "application/vnd.apple.mpegurl";
  let stream;

  function showError(player) {
    if (!modal.open || !frame.contains(player)) return;
    const message = document.createElement("p");
    message.className = "video-modal__error";
    message.setAttribute("role", "alert");
    message.textContent = "Видео не загрузилось. Попробуйте открыть его ещё раз или сменить браузер.";
    frame.replaceChildren(message);
    stream?.destroy();
    stream = undefined;
  }

  async function playStream(player, src) {
    if (player.canPlayType(hlsType) === "probably") {
      player.src = src;
      return;
    }

    try {
      const { default: Hls } = await import("./vendor/hls/hls.light-1.7.4.js");
      if (!modal.open || !frame.contains(player)) return;
      if (!Hls.isSupported()) {
        if (player.canPlayType(hlsType)) player.src = src;
        else showError(player);
        return;
      }
      const playback = new Hls();
      stream = playback;
      playback.on(Hls.Events.ERROR, (_, error) => {
        if (error.fatal) showError(player);
      });
      playback.loadSource(src);
      playback.attachMedia(player);
    } catch {
      showError(player);
    }
  }

  document.addEventListener("click", (e) => {
    const btn = e.target.closest("[data-video-open]");
    if (!btn) return;
    const { videoType, videoSrc } = btn.dataset;
    let player;
    if (videoType === "file" || videoType === "hls") {
      player = document.createElement("video");
      player.controls = true;
      player.autoplay = true;
      player.playsInline = true;
      player.setAttribute("controlslist", "nodownload");
      player.addEventListener("error", () => showError(player));
    } else {
      player = document.createElement("iframe");
      player.allow = "autoplay; fullscreen; picture-in-picture; encrypted-media";
      player.allowFullscreen = true;
      player.title = btn.getAttribute("aria-label");
    }
    modal.setAttribute("aria-label", btn.getAttribute("aria-label") || "Видео");
    frame.replaceChildren(player);
    modal.showModal();
    if (videoType === "hls") playStream(player, videoSrc);
    else player.src = videoSrc;
  });

  modal.addEventListener("click", (e) => {
    if (e.target === modal || e.target.closest("[data-video-close]")) modal.close();
  });
  modal.addEventListener("close", () => {
    frame.replaceChildren();
    stream?.destroy();
    stream = undefined;
  });
}
