// видео в окне поверх сайта: плеер создаётся при открытии и удаляется при закрытии, чтобы звук не продолжал играть
const modal = document.querySelector("[data-video-modal]");

if (modal) {
  const frame = modal.querySelector("[data-video-frame]");
  const controlsTemplate = document.querySelector("[data-video-controls-template]");
  const previewEndTemplate = document.querySelector("[data-video-preview-end-template]");
  const touchPlayer = window.matchMedia("(pointer: coarse), (max-width: 640px)");
  const hlsType = "application/vnd.apple.mpegurl";
  const playbackUrl = "/course-video/playback/";
  let stream;
  let coursePlayback;

  function formatTime(time) {
    if (!Number.isFinite(time)) return "—";
    const total = Math.floor(time);
    return `${Math.floor(total / 60)}:${String(total % 60).padStart(2, "0")}`;
  }

  function addControls(player, marked) {
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
    fullscreen.hidden = !marked && !standardFullscreen && typeof player.webkitEnterFullscreen !== "function";
    fullscreen.addEventListener("click", async () => {
      try {
        if (document.fullscreenElement === frame) await document.exitFullscreen();
        else if (modal.classList.contains("video-modal--expanded")) modal.classList.remove("video-modal--expanded");
        else if (standardFullscreen) {
          await frame.requestFullscreen().catch((error) => {
            if (!marked) throw error;
            modal.classList.add("video-modal--expanded");
          });
        } else if (marked) modal.classList.add("video-modal--expanded");
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
      ending.querySelector("p").focus({ preventScroll: true });
    }, { once: true });
  }

  function playbackHeaders(id) {
    return { "X-Course-Playback": "1", "X-Playback-Id": id };
  }

  function endCoursePlayback() {
    const current = coursePlayback;
    if (!current) return;
    coursePlayback = undefined;
    clearInterval(current.timer);
    current.controller.abort();
    if (current.id) {
      fetch(playbackUrl, { method: "DELETE", headers: playbackHeaders(current.id), keepalive: true }).catch(() => {});
    }
  }

  function resetPlayback() {
    frame.querySelector("video")?.pause();
    frame.replaceChildren();
    stream?.destroy();
    stream = undefined;
    endCoursePlayback();
    modal.classList.remove("video-modal--expanded");
  }

  function showError(player, text = "Видео не загрузилось. Попробуйте открыть его ещё раз или сменить браузер.") {
    if (!modal.open || !frame.contains(player)) return;
    const message = document.createElement("p");
    message.className = "video-modal__error";
    message.setAttribute("role", "alert");
    message.textContent = text;
    resetPlayback();
    frame.append(message);
    if (document.fullscreenElement === frame) document.exitFullscreen().catch(() => {});
  }

  function checkCoursePlayback(current) {
    current.check ??= fetch(playbackUrl, {
      headers: playbackHeaders(current.id),
      cache: "no-store",
      signal: current.controller.signal,
    }).then((response) => {
      if (current !== coursePlayback) return;
      if (response.status === 409) {
        showError(current.player, "Видео открыто на другом устройстве или в другой вкладке. Откройте главу снова, чтобы продолжить здесь.");
      } else if (response.status === 403) {
        showError(current.player, "Сеанс завершён. Войдите в личный кабинет снова.");
      }
    }).catch(() => {}).finally(() => { current.check = undefined; });
    return current.check;
  }

  async function handlePlaybackError(player) {
    const current = coursePlayback;
    if (current?.player === player && current.id) await checkCoursePlayback(current);
    showError(player);
  }

  async function startCoursePlayback(player, chapter, mark) {
    const current = { player, controller: new AbortController() };
    coursePlayback = current;
    try {
      const response = await fetch(playbackUrl, {
        method: "POST",
        headers: { "X-Course-Playback": "1" },
        body: new URLSearchParams({ chapter }),
        signal: current.controller.signal,
      });
      if (response.status === 403) {
        showError(player, "Сеанс завершён. Войдите в личный кабинет снова.");
        return;
      }
      if (!response.ok) {
        showError(player);
        return;
      }
      const data = await response.json();
      if (current !== coursePlayback || !modal.open || !frame.contains(player)) return;
      current.id = data.playback;
      mark.textContent = data.watermark;
      current.timer = setInterval(() => checkCoursePlayback(current), 5000);
      playStream(player, data.src);
    } catch {
      if (current === coursePlayback) showError(player);
    }
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
        if (error.fatal) handlePlaybackError(player);
      });
      playback.loadSource(src);
      playback.attachMedia(player);
    } catch {
      showError(player);
    }
  }

  document.addEventListener("click", (e) => {
    const btn = e.target.closest("[data-video-open]");
    if (!btn || modal.open) return;
    resetPlayback();
    const { videoType, videoSrc } = btn.dataset;
    const courseChapter = /^\/course-video\/(chapter-[0-9]{2})\/index\.m3u8$/.exec(videoSrc)?.[1];
    const useTouchPlayer = touchPlayer.matches;
    let player;
    if (videoType === "file" || videoType === "hls") {
      player = document.createElement("video");
      player.controls = true;
      player.autoplay = true;
      player.playsInline = true;
      player.setAttribute("controlslist", "nodownload");
      player.addEventListener("error", () => handlePlaybackError(player));
    } else {
      player = document.createElement("iframe");
      player.allow = "autoplay; fullscreen; picture-in-picture; encrypted-media";
      player.allowFullscreen = true;
      player.title = btn.getAttribute("aria-label");
    }
    modal.setAttribute("aria-label", btn.getAttribute("aria-label") || "Видео");
    modal.classList.toggle("video-modal--touch", useTouchPlayer);
    let watermark;
    if (courseChapter) {
      const picture = document.createElement("div");
      picture.className = "video-modal__picture";
      watermark = document.createElement("span");
      watermark.className = "video-modal__watermark";
      watermark.setAttribute("aria-hidden", "true");
      picture.append(player, watermark);
      frame.replaceChildren(picture);
      player.disablePictureInPicture = true;
      player.disableRemotePlayback = true;
      player.addEventListener("timeupdate", () => {
        const position = String(Math.floor(player.currentTime / 15) % 3);
        if (watermark.dataset.position !== position) watermark.dataset.position = position;
      });
    } else {
      frame.replaceChildren(player);
    }
    if (player instanceof HTMLVideoElement) {
      if ((useTouchPlayer || courseChapter) && controlsTemplate) addControls(player, Boolean(courseChapter));
      if (previewEndTemplate && btn.hasAttribute("data-video-preview")) addPreviewEnding(player);
    }
    modal.showModal();
    if (courseChapter) startCoursePlayback(player, courseChapter, watermark);
    else if (videoType === "hls") playStream(player, videoSrc);
    else player.src = videoSrc;
  });

  document.addEventListener("visibilitychange", () => {
    if (!document.hidden && coursePlayback?.id) checkCoursePlayback(coursePlayback);
  });

  modal.addEventListener("click", (e) => {
    const desktopBackdrop = e.target === modal && !modal.classList.contains("video-modal--touch");
    if (desktopBackdrop || e.target.closest("[data-video-close]")) modal.close();
  });
  modal.addEventListener("close", () => {
    if (!modal.open) resetPlayback();
  });
}
