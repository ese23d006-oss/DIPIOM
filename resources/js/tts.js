/**
 * Хичээлийн бичвэрийг Web Speech API-аар дуугаар уншина. Монгол дуу хоолой
 * байхгүй бол өөр хэлний дуугаар буруу уншихгүй, харин хэрэглэгчид мэдэгдэнэ.
 */
import { findVoice, voicesReady } from './speech';

/** Хөтчүүд урт текстийг тасалдаг тул өгүүлбэрээр хуваана. */
function toChunks(text) {
    return text.match(/[^.!?\n]+[.!?]*\s*/g)?.map((part) => part.trim()).filter(Boolean) ?? [];
}

export async function initTextToSpeech() {
    const root = document.querySelector('[data-tts]');

    if (!root) {
        return;
    }

    const transcript = document.getElementById(root.dataset.target);
    const status = root.querySelector('[data-tts-status]');
    const controls = root.querySelector('[data-tts-controls]');
    const rate = root.querySelector('[data-tts-rate]');

    const setStatus = (message) => {
        status.textContent = message;
    };

    if (!('speechSynthesis' in window)) {
        setStatus('Таны хөтөч текстийг дуугаар уншихыг дэмждэггүй. Бичвэрийг доор уншиж болно.');
        return;
    }

    // Хэрэглэгчийн тохиргоонд сонгосон дуу хоолойг эхэлж хэрэглэнэ.
    const voice = findVoice(await voicesReady(), 'mn');

    if (!voice) {
        setStatus('Таны төхөөрөмжид Монгол хэлний дуу хоолой суугаагүй байна. Бичвэрийг доор уншиж эсвэл дэлгэц уншигч программаа ашиглаж болно.');
        return;
    }

    setStatus('');
    controls.hidden = false;

    const pauseButton = root.querySelector('[data-tts-action="pause"]');
    const resumeButton = root.querySelector('[data-tts-action="resume"]');
    const stopButton = root.querySelector('[data-tts-action="stop"]');

    let queue = [];
    let speaking = false;

    const refresh = (paused = false) => {
        pauseButton.setAttribute('aria-disabled', String(!speaking || paused));
        resumeButton.setAttribute('aria-disabled', String(!paused));
        stopButton.setAttribute('aria-disabled', String(!speaking));
    };

    const speakNext = () => {
        const chunk = queue.shift();

        if (!chunk) {
            speaking = false;
            refresh();
            setStatus('Уншиж дууслаа.');
            return;
        }

        const utterance = new SpeechSynthesisUtterance(chunk);

        utterance.voice = voice;
        utterance.lang = voice.lang;
        utterance.rate = Number(rate.value);
        utterance.volume = Number(document.body.dataset.ttsVolume || 1);
        utterance.onend = () => {
            if (speaking) {
                speakNext();
            }
        };
        utterance.onerror = (event) => {
            if (event.error !== 'canceled' && event.error !== 'interrupted') {
                speaking = false;
                refresh();
                setStatus('Дуугаар уншиж чадсангүй. Бичвэрийг уншиж болно.');
            }
        };

        window.speechSynthesis.speak(utterance);
    };

    const start = (text) => {
        window.speechSynthesis.cancel();
        queue = toChunks(text);
        speaking = queue.length > 0;
        refresh();
        setStatus('Уншиж байна.');
        speakNext();
    };

    const stop = () => {
        speaking = false;
        queue = [];
        window.speechSynthesis.cancel();
        refresh();
    };

    root.querySelector('[data-tts-action="read"]').addEventListener('click', () => start(transcript.textContent));

    const selectionButton = root.querySelector('[data-tts-action="selection"]');

    // Хулганаар дарахад сонгосон текст арилахгүй байхаар focus-ийг гараар шилжүүлнэ.
    selectionButton.addEventListener('mousedown', (event) => {
        event.preventDefault();
        selectionButton.focus();
    });

    selectionButton.addEventListener('click', () => {
        const selection = window.getSelection();
        const text = selection ? selection.toString().trim() : '';
        const inside = selection?.anchorNode && transcript.contains(selection.anchorNode);

        if (!text || !inside) {
            setStatus('Эхлээд хичээлийн бичвэрээс текст сонгоно уу.');
            return;
        }

        start(text);
    });

    pauseButton.addEventListener('click', () => {
        if (pauseButton.getAttribute('aria-disabled') === 'true') {
            return;
        }
        window.speechSynthesis.pause();
        refresh(true);
        setStatus('Түр зогслоо.');
    });

    resumeButton.addEventListener('click', () => {
        if (resumeButton.getAttribute('aria-disabled') === 'true') {
            return;
        }
        window.speechSynthesis.resume();
        refresh();
        setStatus('Уншиж байна.');
    });

    stopButton.addEventListener('click', () => {
        if (stopButton.getAttribute('aria-disabled') === 'true') {
            return;
        }
        stop();
        setStatus('Зогслоо.');
    });

    window.addEventListener('pagehide', () => window.speechSynthesis.cancel());
    refresh();
}
