// видео в окне поверх сайта: плеер создаётся при открытии и удаляется при закрытии, чтобы звук не продолжал играть
const modal = document.querySelector("[data-video-modal]");

if (modal) {
  const frame = modal.querySelector("[data-video-frame]");
  const controlsTemplate = document.querySelector("[data-video-controls-template]");
  const previewEndTemplate = document.querySelector("[data-video-preview-end-template]");
  const touchPlayer = window.matchMedia("(pointer: coarse), (max-width: 640px)");
  const hlsType = "application/vnd.apple.mpegurl";
  let stream;

  function formatTime(time) {
    if (!Number.isFinite(time)) return "—";
    const total = Math.floor(time);
    return `${Math.floor(total / 60)}:${String(total % 60).padStart(2, "0")}`;
  }

  function addControls(player) {
    const controls = controlsTemplate.content.firstElementChild.cloneNode(true);
    const play = controls.querySelector("[data-video-play]");
    const mute = controls.querySelector("[data-video-mute]");
    const seek = controls.querySelector("[data-video-seek]");
    const time = controls.querySelector("[data-video-time]");
    const fullscreen = controls.querySelector("[data-video-fullscreen]");
    const notice = controls.querySelector("[data-video-notice]");

    function syncPlayback() {
      const playing = !player.paused && !player.ended;
      play.toggleAttribute("data-active", playing);
      play.setAttribute("aria-label", playing ? "Пауза" : "Воспроизвести");
      if (playing) notice.textContent = "";
    }

    function syncVolume() {
      mute.toggleAttribute("data-active", player.muted);
      mute.setAttribute("aria-label", player.muted ? "Включить звук" : "Выключить звук");
    }

    function syncPosition() {
      const live = !Number.isFinite(player.duration);
      const ranges = player.seekable;
      seek.min = live && ranges.length ? ranges.start(0) : 0;
      seek.max = live ? (ranges.length ? ranges.end(ranges.length - 1) : 0) : player.duration;
      seek.disabled = Number(seek.max) <= Number(seek.min);
      seek.value = player.currentTime;
      const current = formatTime(player.currentTime);
      const total = formatTime(player.duration);
      time.textContent = `${current} / ${total}`;
      seek.setAttribute("aria-valuetext", live ? current : `${current} из ${total}`);
    }

    play.addEventListener("click", async (event) => {
      if (event.detail > 0) play.blur();
      if (!player.paused && !player.ended) {
        player.pause();
        syncPlayback();
        return;
      }
      try {
        await player.play();
      } catch {
        notice.textContent = "Не удалось начать воспроизведение. Попробуйте ещё раз.";
      }
    });
    mute.addEventListener("click", () => { player.muted = !player.muted; });
    seek.addEventListener("input", () => { player.currentTime = seek.valueAsNumber; });

    const standardFullscreen = document.fullscreenEnabled && typeof frame.requestFullscreen === "function";
    fullscreen.hidden = !standardFullscreen && typeof player.webkitEnterFullscreen !== "function";
    fullscreen.addEventListener("click", async () => {
      try {
        if (document.fullscreenElement === frame) await document.exitFullscreen();
        else if (standardFullscreen) await frame.requestFullscreen();
        else player.webkitEnterFullscreen();
        notice.textContent = "";
      } catch {
        notice.textContent = "Не удалось переключить полноэкранный режим.";
      }
    });

    for (const event of ["play", "pause", "ended"]) player.addEventListener(event, syncPlayback);
    for (const event of ["loadedmetadata", "durationchange", "timeupdate"]) player.addEventListener(event, syncPosition);
    player.addEventListener("volumechange", syncVolume);
    frame.append(controls);
    player.controls = false;
  }

  function addPreviewEnding(player) {
    const controls = frame.querySelector(".video-controls");
    const ending = previewEndTemplate.content.firstElementChild.cloneNode(true);
    const action = ending.querySelector("a");

    player.addEventListener("ended", async () => {
      if (document.fullscreenElement === frame || document.fullscreenElement === player) {
        await document.exitFullscreen().catch(() => {});
      } else if (player.webkitDisplayingFullscreen) {
        player.webkitExitFullscreen();
      }
      if (!modal.open || !frame.contains(player)) return;
      player.inert = true;
      if (controls) controls.inert = true;
      frame.append(ending);
      action.focus({ preventScroll: true });
    });

    ending.querySelector("[data-video-preview-replay]").addEventListener("click", () => {
      ending.remove();
      player.inert = false;
      if (controls) controls.inert = false;
      player.currentTime = 0;
      (controls?.querySelector("[data-video-play]") ?? player).focus({ preventScroll: true });
      player.play().catch(() => {});
    });
  }

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
    const useTouchPlayer = touchPlayer.matches;
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
    modal.classList.toggle("video-modal--touch", useTouchPlayer);
    frame.replaceChildren(player);
    if (player instanceof HTMLVideoElement) {
      if (useTouchPlayer && controlsTemplate) addControls(player);
      if (previewEndTemplate && btn.hasAttribute("data-video-preview")) addPreviewEnding(player);
    }
    modal.showModal();
    if (videoType === "hls") playStream(player, videoSrc);
    else player.src = videoSrc;
  });

  modal.addEventListener("click", (e) => {
    const desktopBackdrop = e.target === modal && !modal.classList.contains("video-modal--touch");
    if (desktopBackdrop || e.target.closest("[data-video-close]")) modal.close();
  });
  modal.addEventListener("close", () => {
    frame.querySelector("video")?.pause();
    frame.replaceChildren();
    stream?.destroy();
    stream = undefined;
  });
}
