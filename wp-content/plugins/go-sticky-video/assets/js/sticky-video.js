(function () {
    if (!window.GoStickyVideoConfig) {
        return;
    }

    const config = window.GoStickyVideoConfig;
    const wrapper = document.querySelector('.gsv-sticky-wrapper');
    const player = document.querySelector('.gsv-sticky-player');
    const surface = document.querySelector('.gsv-video-surface');
    const modal = document.querySelector('.gsv-modal');
    const modalPlayer = document.querySelector('.gsv-modal-player');
    const modalOverlay = document.querySelector('.gsv-modal-overlay');
    const modalClose = document.querySelector('.gsv-modal-close');
    const btnExpand = document.querySelector('.gsv-btn-expand');
    const btnHide = document.querySelector('.gsv-btn-hide');
    const btnSide = document.querySelector('.gsv-btn-side');
    const btnMute = document.querySelector('.gsv-btn-mute');
    const handle = document.querySelector('.gsv-handle');

    if (!wrapper || !player || !surface) {
        return;
    }

    let stickyInstance = null;
    let modalInstance = null;
    let isMuted = config.autoplay ? true : !!config.mutedDefault;

    const isYouTube = config.type === 'youtube';
    const isVimeo = config.type === 'vimeo';

    function parseYouTubeId(url) {
        const match = url.match(/(?:youtu.be\/|v=|\/embed\/|\/shorts\/)([A-Za-z0-9_-]{6,})/);
        return match ? match[1] : '';
    }

    function parseVimeoId(url) {
        const match = url.match(/vimeo.com\/(?:video\/)?(\d+)/);
        return match ? match[1] : '';
    }

    function buildEmbedUrl(isModal) {
        const autoplay = isModal ? 1 : (config.autoplay ? 1 : 0);
        const muted = isModal ? 0 : (isMuted ? 1 : 0);
        const controls = config.playerUi === 'native' ? 1 : 0;

        if (isYouTube) {
            const id = parseYouTubeId(config.source);
            if (!id) {
                return '';
            }
            return `https://www.youtube.com/embed/${id}?enablejsapi=1&playsinline=1&autoplay=${autoplay}&mute=${muted}&controls=${controls}&modestbranding=1&rel=0`;
        }

        if (isVimeo) {
            const id = parseVimeoId(config.source);
            if (!id) {
                return '';
            }
            return `https://player.vimeo.com/video/${id}?autoplay=${autoplay}&muted=${muted}&controls=${controls}&playsinline=1&transparent=0&app_id=122963`;
        }

        return '';
    }

    function createVideoElement(target, isModal) {
        target.innerHTML = '';
        if (config.type === 'mp4') {
            const video = document.createElement('video');
            video.src = config.source;
            video.playsInline = true;
            video.muted = isModal ? false : isMuted;
            video.controls = config.playerUi === 'native';
            video.autoplay = isModal ? true : config.autoplay;
            video.loop = true;
            video.addEventListener('loadedmetadata', function () {
                if (video.videoWidth && video.videoHeight) {
                    const ratio = (video.videoHeight / video.videoWidth) * 100;
                    setAspectRatio(target, ratio);
                }
            });
            target.appendChild(video);
            return { type: 'mp4', element: video };
        }

        const iframe = document.createElement('iframe');
        iframe.allow = 'autoplay; fullscreen; picture-in-picture';
        iframe.src = buildEmbedUrl(isModal);
        iframe.setAttribute('allowfullscreen', 'allowfullscreen');
        iframe.setAttribute('title', 'Video player');
        target.appendChild(iframe);
        return { type: config.type, element: iframe };
    }

    function postToYouTube(instance, command, args) {
        if (!instance || instance.type !== 'youtube' || !instance.element.contentWindow) {
            return;
        }
        instance.element.contentWindow.postMessage(JSON.stringify({
            event: 'command',
            func: command,
            args: args || []
        }), '*');
    }

    function postToVimeo(instance, command, value) {
        if (!instance || instance.type !== 'vimeo' || !instance.element.contentWindow) {
            return;
        }
        instance.element.contentWindow.postMessage({ method: command, value: value }, '*');
    }

    function pauseInstance(instance) {
        if (!instance) {
            return;
        }
        if (instance.type === 'mp4') {
            instance.element.pause();
            return;
        }
        if (instance.type === 'youtube') {
            postToYouTube(instance, 'pauseVideo');
        }
        if (instance.type === 'vimeo') {
            postToVimeo(instance, 'pause');
        }
    }

    function playInstance(instance) {
        if (!instance) {
            return;
        }
        if (instance.type === 'mp4') {
            instance.element.play().catch(() => {});
            return;
        }
        if (instance.type === 'youtube') {
            postToYouTube(instance, 'playVideo');
        }
        if (instance.type === 'vimeo') {
            postToVimeo(instance, 'play');
        }
    }

    function setMuted(instance, muted) {
        if (!instance) {
            return;
        }
        if (instance.type === 'mp4') {
            instance.element.muted = muted;
            return;
        }
        if (instance.type === 'youtube') {
            postToYouTube(instance, muted ? 'mute' : 'unMute');
        }
        if (instance.type === 'vimeo') {
            postToVimeo(instance, 'setMuted', muted);
        }
    }

    function openModal() {
        if (!modal || !modalPlayer) {
            return;
        }
        pauseInstance(stickyInstance);
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        modalInstance = createVideoElement(modalPlayer, true);
        playInstance(modalInstance);
    }

    function closeModal() {
        if (!modal || !modalPlayer) {
            return;
        }
        pauseInstance(modalInstance);
        modalPlayer.innerHTML = '';
        modalInstance = null;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        pauseInstance(stickyInstance);
        if (config.autoplay) {
            playInstance(stickyInstance);
        }
    }

    function hidePlayer() {
        const side = wrapper.getAttribute('data-side');
        player.classList.remove('is-hidden-left', 'is-hidden-right');
        if (side === 'left') {
            player.classList.add('is-hidden-left');
        } else {
            player.classList.add('is-hidden-right');
        }
        pauseInstance(stickyInstance);
    }

    function showPlayer() {
        player.classList.remove('is-hidden-left', 'is-hidden-right');
    }

    function switchSide() {
        const current = wrapper.getAttribute('data-side') === 'left' ? 'left' : 'right';
        const next = current === 'left' ? 'right' : 'left';
        wrapper.setAttribute('data-side', next);
        showPlayer();
    }

    function setAspectRatio(target, ratio) {
        if (!ratio || Number.isNaN(ratio)) {
            return;
        }
        const value = `${ratio.toFixed(2)}%`;
        target.style.setProperty('--gsv-aspect', value);
    }

    function bindGalleryButtons() {
        const buttons = document.querySelectorAll('.gsv-gallery-play');
        buttons.forEach((btn) => {
            if (btn.dataset.gsvBound) {
                return;
            }
            btn.dataset.gsvBound = '1';
            btn.addEventListener('click', openModal);
        });
    }

    function ensureGalleryButton() {
        if (!config.isProduct) {
            return;
        }
        const gallery = document.querySelector('.woocommerce-product-gallery');
        if (!gallery) {
            return;
        }
        if (!gallery.querySelector('.gsv-gallery-play')) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'gsv-gallery-play';
            btn.setAttribute('aria-label', 'Play product video');
            btn.textContent = '▶';
            gallery.appendChild(btn);
        }
        bindGalleryButtons();
    }

    if (config.aspectRatio) {
        setAspectRatio(surface, config.aspectRatio);
        if (modalPlayer) {
            setAspectRatio(modalPlayer, config.aspectRatio);
        }
    }

    stickyInstance = createVideoElement(surface, false);
    if (config.autoplay) {
        playInstance(stickyInstance);
    }

    if (btnExpand) {
        btnExpand.addEventListener('click', openModal);
    }

    if (btnHide) {
        btnHide.addEventListener('click', hidePlayer);
    }

    if (handle) {
        handle.addEventListener('click', showPlayer);
    }

    if (btnSide) {
        btnSide.addEventListener('click', switchSide);
    }

    if (btnMute) {
        btnMute.addEventListener('click', function () {
            isMuted = !isMuted;
            setMuted(stickyInstance, isMuted);
            btnMute.textContent = isMuted ? '🔇' : '🔊';
        });
        btnMute.textContent = isMuted ? '🔇' : '🔊';
    }

    if (modalOverlay) {
        modalOverlay.addEventListener('click', closeModal);
    }

    if (modalClose) {
        modalClose.addEventListener('click', closeModal);
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal && modal.classList.contains('is-open')) {
            closeModal();
        }
    });

    if (config.isProduct) {
        ensureGalleryButton();
        document.addEventListener('DOMContentLoaded', ensureGalleryButton);
        const gallery = document.querySelector('.woocommerce-product-gallery');
        if (gallery) {
            const observer = new MutationObserver(() => {
                ensureGalleryButton();
            });
            observer.observe(gallery, { childList: true, subtree: true });
        }
    }
})();
