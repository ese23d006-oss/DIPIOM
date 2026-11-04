/**
 * Видео тоглуулагч: тусгай удирдлага, аудио тайлбарын синхрончлол, явц хадгалалт.
 * Гарын shortcut санаатай нэмээгүй: дэлгэц уншигчийн товчтой давхцдаг (WCAG 2.1.4).
 */
const SAVE_INTERVAL_MS = 10000;
const SYNC_TOLERANCE_SECONDS = 0.3;

function spokenTime(totalSeconds) {
    const total = Math.max(0, Math.floor(totalSeconds || 0));
    const minutes = Math.floor(total / 60);
    const seconds = total % 60;

    return minutes > 0 ? `${minutes} минут ${seconds} секунд` : `${seconds} секунд`;
}

function clockTime(totalSeconds) {
    const total = Math.max(0, Math.floor(totalSeconds || 0));
    const minutes = Math.floor(total / 60);

    return `${minutes}:${String(total % 60).padStart(2, '0')}`;
}

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

/** Хадгалж чадаагүй явцыг холболт сэргэхэд илгээнэ. */
const offlineQueue = {
    key: (url) => `lms-progress:${url}`,

    put(url, payload) {
        try {
            localStorage.setItem(this.key(url), JSON.stringify(payload));
        } catch {
            // Хадгалах боломжгүй бол дараагийн 10 секундын оролдлогод найдна.
        }
    },

    get(url) {
        try {
            return JSON.parse(localStorage.getItem(this.key(url)));
        } catch {
            return null;
        }
    },

    clear(url) {
        try {
            localStorage.removeItem(this.key(url));
        } catch {
            // Хоосон.
        }
    },
};

function formBody(payload) {
    const body = new FormData();

    body.append('_token', csrfToken());
    Object.entries(payload).forEach(([key, value]) => body.append(key, value));

    return body;
}

async function sendProgress(url, payload) {
    const response = await fetch(url, {
        method: 'POST',
        body: formBody(payload),
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
    });

    if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
    }

    return response.json();
}

export function initPlayer() {
    const root = document.querySelector('[data-player]');

    if (!root) {
        return;
    }

    const video = root.querySelector('video');
    const description = root.querySelector('audio');
    const track = video.textTracks[0] ?? null;
    const live = document.querySelector('[data-player-live]');
    const saveUrl = root.dataset.saveUrl || null;
    const el = (name) => root.querySelector(`[data-control="${name}"]`);

    const playButton = el('play');
    const seek = el('seek');
    const timeLabel = el('time');
    const volume = el('volume');
    const rate = el('rate');
    const captions = el('captions');
    const describe = el('describe');
    const fullscreen = el('fullscreen');
    const resumeNote = document.querySelector('[data-resume-note]');
    const restart = document.querySelector('[data-control="restart"]');

    let isScrubbing = false;
    let lastSavedAt = 0;

    const announce = (message) => {
        if (live) {
            live.textContent = message;
        }
    };

    /* Хадмал */
    const setCaptions = (on) => {
        if (track) {
            track.mode = on ? 'showing' : 'hidden';
        }
        if (captions) {
            captions.setAttribute('aria-pressed', String(on));
            captions.querySelector('i').className = on ? 'bi bi-badge-cc-fill' : 'bi bi-badge-cc';
        }
    };

    /* Аудио тайлбар: видеотой нэг цаг, хурд, дууны түвшинд ажиллана. */
    let describeOn = false;

    const syncDescription = () => {
        if (!description || !describeOn) {
            return;
        }
        description.playbackRate = video.playbackRate;
        description.volume = video.volume;

        if (Math.abs(description.currentTime - video.currentTime) > SYNC_TOLERANCE_SECONDS) {
            description.currentTime = video.currentTime;
        }
        if (video.paused || video.ended) {
            description.pause();
        } else if (description.paused) {
            // Видео зогсоход play() AbortError-оор тасалдана: энэ бол алдаа биш, зарлахгүй.
            description.play().catch((error) => {
                if (error.name !== 'AbortError') {
                    announce('Аудио тайлбарыг тоглуулж чадсангүй.');
                }
            });
        }
    };

    const setDescription = (on) => {
        describeOn = on;

        if (!description) {
            return;
        }
        describe.setAttribute('aria-pressed', String(on));
        describe.querySelector('i').className = on ? 'bi bi-volume-up-fill' : 'bi bi-volume-mute';

        if (on) {
            syncDescription();
        } else {
            description.pause();
        }
    };

    /* Явц хадгалах */
    const payload = (ended) => ({
        position: Math.floor(video.currentTime),
        duration: Math.floor(video.duration || 0),
        ended: ended ? 1 : 0,
    });

    const save = async (ended = false) => {
        if (!saveUrl || !video.duration) {
            return;
        }

        const data = payload(ended);

        lastSavedAt = Date.now();

        try {
            const result = await sendProgress(saveUrl, data);

            offlineQueue.clear(saveUrl);

            if (result.is_completed && ended) {
                announce('Хичээл дууслаа. Таны явц хадгалагдлаа.');
            }
        } catch {
            offlineQueue.put(saveUrl, data);
        }
    };

    const flushQueue = async () => {
        const queued = saveUrl ? offlineQueue.get(saveUrl) : null;

        if (!queued) {
            return;
        }

        try {
            await sendProgress(saveUrl, queued);
            offlineQueue.clear(saveUrl);
        } catch {
            // Дараагийн холболт сэргэхэд дахин оролдоно.
        }
    };

    /* Товчлуурууд */
    const skip = (seconds) => {
        const target = Math.min(video.duration || 0, Math.max(0, video.currentTime + seconds));

        video.currentTime = target;
        announce(`${seconds > 0 ? 'Урагшлууллаа' : 'Буцаалаа'}. Одоо ${spokenTime(target)}.`);
    };

    playButton.addEventListener('click', () => {
        if (video.paused) {
            video.play().catch(() => announce('Видеог тоглуулж чадсангүй.'));
        } else {
            video.pause();
        }
    });

    el('stop').addEventListener('click', () => {
        video.pause();
        video.currentTime = 0;
        announce('Зогслоо. Эхэнд буцлаа.');
    });

    el('back').addEventListener('click', () => skip(-5));
    el('forward').addEventListener('click', () => skip(5));

    seek.addEventListener('input', () => {
        isScrubbing = true;
        timeLabel.textContent = `${clockTime(seek.value)} / ${clockTime(video.duration)}`;
        seek.setAttribute('aria-valuetext', spokenTime(seek.value));
    });

    seek.addEventListener('change', () => {
        isScrubbing = false;
        video.currentTime = Number(seek.value);
    });

    volume.addEventListener('input', () => {
        video.volume = volume.value / 100;
        volume.setAttribute('aria-valuetext', `${volume.value} хувь`);
        syncDescription();
    });

    rate.addEventListener('change', () => {
        video.playbackRate = Number(rate.value);
        syncDescription();
        announce(`Тоглуулах хурд ${rate.value} дахин.`);
    });

    captions?.addEventListener('click', () => {
        const on = captions.getAttribute('aria-pressed') !== 'true';

        setCaptions(on);
        announce(on ? 'Хадмал асаалаа.' : 'Хадмал унтраалаа.');
    });

    describe?.addEventListener('click', () => {
        const on = describe.getAttribute('aria-pressed') !== 'true';

        setDescription(on);
        announce(on ? 'Аудио тайлбар асаалаа.' : 'Аудио тайлбар унтраалаа.');
    });

    fullscreen.addEventListener('click', () => {
        if (document.fullscreenElement) {
            document.exitFullscreen();
        } else {
            root.requestFullscreen?.().catch(() => announce('Бүтэн дэлгэцэд шилжиж чадсангүй.'));
        }
    });

    document.addEventListener('fullscreenchange', () => {
        const on = document.fullscreenElement === root;

        fullscreen.querySelector('span').textContent = on ? 'Бүтэн дэлгэцээс гарах' : 'Бүтэн дэлгэц';
        fullscreen.querySelector('i').className = on ? 'bi bi-fullscreen-exit' : 'bi bi-fullscreen';
    });

    restart?.addEventListener('click', () => {
        video.currentTime = 0;
        resumeNote.hidden = true;
        playButton.focus();
        announce('Хичээлийг эхнээс нь эхлүүллээ.');
    });

    /* Видеоны үйл явдлууд */
    video.addEventListener('loadedmetadata', () => {
        seek.max = Math.floor(video.duration);
        seek.disabled = false;
        timeLabel.textContent = `0:00 / ${clockTime(video.duration)}`;

        const resumeAt = Number(root.dataset.resume || 0);

        if (resumeAt > 0 && resumeAt < video.duration - 5) {
            video.currentTime = resumeAt;

            if (resumeNote) {
                resumeNote.hidden = false;
                resumeNote.querySelector('[data-resume-time]').textContent = spokenTime(resumeAt);
            }

            announce(`Өмнөх үзсэн ${spokenTime(resumeAt)}-ээс үргэлжлүүлнэ.`);
        }
    });

    video.addEventListener('timeupdate', () => {
        if (!isScrubbing) {
            seek.value = Math.floor(video.currentTime);
            seek.setAttribute('aria-valuetext', spokenTime(video.currentTime));
            timeLabel.textContent = `${clockTime(video.currentTime)} / ${clockTime(video.duration)}`;
        }

        syncDescription();

        if (!video.paused && Date.now() - lastSavedAt >= SAVE_INTERVAL_MS) {
            save();
        }
    });

    video.addEventListener('play', () => {
        playButton.querySelector('i').className = 'bi bi-pause-fill';
        playButton.querySelector('span').textContent = 'Түр зогсоох';
        announce('Тоглож байна.');
        lastSavedAt = Date.now();
        syncDescription();
    });

    video.addEventListener('pause', () => {
        playButton.querySelector('i').className = 'bi bi-play-fill';
        playButton.querySelector('span').textContent = 'Тоглуулах';
        syncDescription();

        if (!video.ended) {
            announce(`Түр зогслоо. ${spokenTime(video.currentTime)} дээр байна.`);
            save();
        }
    });

    video.addEventListener('seeked', syncDescription);

    video.addEventListener('ended', () => {
        announce('Видео дууслаа.');
        syncDescription();
        save(true);
    });

    video.addEventListener('error', () => {
        announce('Видеог ачаалж чадсангүй.');

        const failure = document.querySelector('[data-player-error]');

        if (failure) {
            failure.hidden = false;
        }
    });

    /* Хаах үед сүүлийн байрлалыг хадгална. */
    window.addEventListener('pagehide', () => {
        if (!saveUrl || !video.duration) {
            return;
        }

        const data = payload(false);

        if (!navigator.sendBeacon?.(saveUrl, formBody(data))) {
            offlineQueue.put(saveUrl, data);
        }
    });

    window.addEventListener('online', flushQueue);
    flushQueue();

    /* Эхний төлөв: автоматаар тоглуулахгүй. */
    setCaptions(root.dataset.captions === '1');
    setDescription(root.dataset.describe === '1' && Boolean(description));
    volume.setAttribute('aria-valuetext', `${volume.value} хувь`);
}
