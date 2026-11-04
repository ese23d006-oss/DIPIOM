/**
 * Системийн өөрийн уншигч. Хэрэглэгч самбарыг асааж, товч дарсан үед л уншина
 * (дэлгэц уншигчтай зэрэг дуугарахаас сэргийлнэ). Текстийг хэлээр нь хуваана:
 * Япон хэсгийг Япон дуу хоолойгоор, Монгол хэсгийг Монгол дуу хоолойгоор.
 */
import { findVoice, hasSpeech, speakText, voicesReady } from './speech';

const BLOCKS = 'h1,h2,h3,h4,h5,h6,p,li,dt,dd,th,td,summary,legend,label,figcaption';

function isVisible(element) {
    return element.getClientRects().length > 0 && !element.closest('[aria-hidden="true"],[hidden],[data-tts-skip],.visually-hidden');
}

/** Хэсгийн текстийг [{lang, text}] болгоно. Хэлийг ойрын lang аттрибутаас авна. */
function languageRuns(element) {
    const runs = [];
    const walker = document.createTreeWalker(element, NodeFilter.SHOW_TEXT);

    for (let node = walker.nextNode(); node; node = walker.nextNode()) {
        const text = node.textContent.replace(/\s+/g, ' ');

        if (!text.trim() || node.parentElement.closest('button,select,.visually-hidden,[aria-hidden="true"]')) {
            continue;
        }

        const lang = (node.parentElement.closest('[lang]')?.lang || 'mn').toLowerCase();
        const key = lang === 'ja' ? 'ja' : lang.startsWith('ja-latn') ? 'latin' : 'mn';
        const last = runs[runs.length - 1];

        if (last?.lang === key) {
            last.text += text;
        } else {
            runs.push({ lang: key, text });
        }
    }

    return runs.map((run) => ({ ...run, text: run.text.trim() })).filter((run) => run.text);
}

function collectSegments() {
    const main = document.getElementById('main-content');

    return [...main.querySelectorAll(BLOCKS)]
        .filter(isVisible)
        // Давхар уншихгүй: дотроо өөр блок агуулсныг алгасна.
        .filter((element) => !element.querySelector(BLOCKS))
        .filter((element) => languageRuns(element).length);
}

export async function initReader() {
    const root = document.querySelector('[data-reader]');

    if (!root) {
        return;
    }

    const status = root.querySelector('[data-reader-status]');
    const controls = root.querySelector('[data-reader-controls]');
    const rate = root.querySelector('[data-reader-rate]');
    const action = (name) => root.querySelector(`[data-reader-action="${name}"]`);
    const highlight = document.body.dataset.ttsHighlight === '1';

    if (!hasSpeech()) {
        status.textContent = 'Таны хөтөч текстийг дуугаар уншихыг дэмждэггүй.';
        return;
    }

    const allVoices = await voicesReady();
    const voices = { mn: findVoice(allVoices, 'mn'), ja: findVoice(allVoices, 'ja') };

    if (!voices.mn && !voices.ja) {
        status.textContent = 'Таны төхөөрөмжид Монгол болон Япон хэлний дуу хоолой олдсонгүй тул дуугаар унших боломжгүй. Үйлдлийн системийн хэлний тохиргооноос дуу хоолой суулгаж болно.';
        return;
    }

    const missing = [!voices.mn && 'Монгол', !voices.ja && 'Япон'].filter(Boolean);

    status.textContent = missing.length
        ? `${missing.join(', ')} хэлний дуу хоолой олдсонгүй тул тэр хэсгийг алгасна.`
        : '';
    controls.hidden = false;

    // Латин ромажийг Монгол дуу хоолой буруу уншдаг тул зөвхөн Япон унших үед Япон дуугаар.
    const voiceFor = (lang) => (lang === 'ja' || lang === 'latin' ? voices.ja : voices.mn);
    const langEnabled = (lang) => root.querySelector(`[data-reader-lang="${lang === 'mn' ? 'mn' : 'ja'}"]`).checked;

    let segments = [];
    let index = -1;
    let reading = false;
    let paused = false;
    let token = 0;

    const clearHighlight = () => document.querySelectorAll('.reader-current').forEach((el) => el.classList.remove('reader-current'));

    const refresh = () => {
        action('pause').setAttribute('aria-disabled', String(!reading || paused));
        action('resume').setAttribute('aria-disabled', String(!paused));
        action('stop').setAttribute('aria-disabled', String(!reading));
    };

    const stop = (message) => {
        token += 1;
        reading = false;
        paused = false;
        window.speechSynthesis.cancel();
        clearHighlight();
        refresh();
        if (message) {
            status.textContent = message;
        }
    };

    const speakRuns = (runs, done) => {
        const current = token;
        const queue = runs.filter((run) => langEnabled(run.lang) && voiceFor(run.lang));

        const next = () => {
            if (current !== token) {
                return;
            }

            const run = queue.shift();

            if (!run) {
                done();
                return;
            }

            speakText(run.text, voiceFor(run.lang), { rate: Number(rate.value), onend: next, onerror: next });
        };

        next();
    };

    const playSegment = () => {
        const element = segments[index];

        if (!element) {
            stop('Уншиж дууслаа.');
            return;
        }

        clearHighlight();

        if (highlight) {
            element.classList.add('reader-current');
            element.scrollIntoView({ block: 'center', behavior: 'auto' });
        }

        status.textContent = `${segments.length} хэсгээс ${index + 1}-р хэсгийг уншиж байна.`;
        speakRuns(languageRuns(element), () => {
            index += 1;
            playSegment();
        });
    };

    const startAt = (position) => {
        token += 1;
        window.speechSynthesis.cancel();
        segments = collectSegments();
        index = Math.min(Math.max(0, position), Math.max(0, segments.length - 1));
        reading = true;
        paused = false;
        refresh();
        playSegment();
    };

    const pauseOrResume = () => {
        if (!reading) {
            return;
        }

        if (paused) {
            window.speechSynthesis.resume();
            paused = false;
            status.textContent = 'Үргэлжлүүлж байна.';
        } else {
            window.speechSynthesis.pause();
            paused = true;
            status.textContent = 'Түр зогслоо.';
        }

        refresh();
    };

    const guarded = (button, handler) => button.addEventListener('click', () => {
        if (button.getAttribute('aria-disabled') !== 'true') {
            handler();
        }
    });

    action('read').addEventListener('click', () => startAt(0));
    guarded(action('pause'), pauseOrResume);
    guarded(action('resume'), pauseOrResume);
    guarded(action('stop'), () => stop('Зогслоо.'));
    action('next').addEventListener('click', () => startAt(index + 1));
    action('previous').addEventListener('click', () => startAt(index - 1));

    const selectionButton = action('selection');

    // Хулганаар дарахад сонгосон текст арилахгүй.
    selectionButton.addEventListener('mousedown', (event) => {
        event.preventDefault();
        selectionButton.focus();
    });

    selectionButton.addEventListener('click', () => {
        const selected = window.getSelection()?.toString().trim();

        if (!selected) {
            status.textContent = 'Эхлээд текст сонгоно уу.';
            return;
        }

        // Сонгосон текстийн хэлийг агуулгаар нь тодорхойлно (хирагана, катакана, ханз).
        const lang = /[぀-ヿ一-鿿]/.test(selected) ? 'ja' : 'mn';

        if (!voiceFor(lang)) {
            status.textContent = 'Сонгосон текстийн хэлний дуу хоолой олдсонгүй.';
            return;
        }

        stop();
        reading = true;
        refresh();
        status.textContent = 'Сонгосон текстийг уншиж байна.';
        speakRuns([{ lang, text: selected }], () => stop('Уншиж дууслаа.'));
    });

    // Alt + Shift хослол: нэг тэмдэгтийн товчлол дэлгэц уншигчтай зөрчилддөг (WCAG 2.1.4).
    document.addEventListener('keydown', (event) => {
        if (!event.altKey || !event.shiftKey) {
            return;
        }

        const handlers = {
            KeyR: () => startAt(0),
            KeyP: pauseOrResume,
            KeyS: () => stop('Зогслоо.'),
            KeyN: () => startAt(index + 1),
            KeyB: () => startAt(index - 1),
        };

        if (handlers[event.code]) {
            event.preventDefault();
            handlers[event.code]();
        }
    });

    window.addEventListener('pagehide', () => window.speechSynthesis.cancel());
    refresh();
}
