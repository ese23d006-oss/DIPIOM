/**
 * Web Speech API-ийн нийтлэг хэсэг. Хөтчид байгаа дуу хоолойг л ашиглана:
 * тухайн хэлний дуу хоолой байхгүй бол буруу хэлээр уншихгүй.
 */
const VOICE_WAIT_MS = 1500;

export const hasSpeech = () => 'speechSynthesis' in window;

export function voicesReady() {
    return new Promise((resolve) => {
        if (!hasSpeech()) {
            resolve([]);
            return;
        }

        const voices = window.speechSynthesis.getVoices();

        if (voices.length) {
            resolve(voices);
            return;
        }

        const finish = () => resolve(window.speechSynthesis.getVoices());

        window.speechSynthesis.addEventListener('voiceschanged', finish, { once: true });
        setTimeout(finish, VOICE_WAIT_MS);
    });
}

function preferredUri(lang) {
    return lang === 'ja' ? document.body.dataset.voiceJa : document.body.dataset.voiceMn;
}

export function findVoice(voices, lang) {
    const matching = voices.filter((voice) => voice.lang.toLowerCase().startsWith(lang));

    return matching.find((voice) => voice.voiceURI === preferredUri(lang)) ?? matching[0] ?? null;
}

export function speakText(text, voice, options = {}) {
    const utterance = new SpeechSynthesisUtterance(text);

    utterance.voice = voice;
    utterance.lang = voice.lang;
    utterance.rate = options.rate ?? Number(document.body.dataset.ttsRate || 1);
    utterance.volume = Number(document.body.dataset.ttsVolume || 1);
    utterance.onend = options.onend ?? null;
    utterance.onerror = options.onerror ?? null;

    window.speechSynthesis.speak(utterance);
}

/** "Сонсох" товчлуурууд: тухайн хэлний дуу хоолой олдвол л харагдана. */
export async function initSpeakButtons() {
    const buttons = document.querySelectorAll('[data-speak]');
    const notices = document.querySelectorAll('[data-voice-notice]');

    if (!buttons.length && !notices.length) {
        return;
    }

    const voices = await voicesReady();

    buttons.forEach((button) => {
        const voice = findVoice(voices, button.dataset.speakLang);

        if (!voice) {
            return;
        }

        button.hidden = false;
        button.addEventListener('click', () => {
            window.speechSynthesis.cancel();
            speakText(button.dataset.speak, voice);
        });
    });

    notices.forEach((notice) => {
        notice.hidden = Boolean(findVoice(voices, notice.dataset.voiceNotice));
    });
}

/** Тохиргооны хуудас: зөвхөн энэ төхөөрөмж дээр бодитоор байгаа дуу хоолойг санал болгоно. */
export async function initVoiceSelects() {
    const selects = document.querySelectorAll('[data-voice-select]');

    if (!selects.length) {
        return;
    }

    const voices = await voicesReady();

    selects.forEach((select) => {
        const lang = select.dataset.voiceSelect;
        const matching = voices.filter((voice) => voice.lang.toLowerCase().startsWith(lang));

        if (!matching.length) {
            return;
        }

        matching.forEach((voice) => {
            const option = new Option(`${voice.name} (${voice.lang})`, voice.voiceURI, false, voice.voiceURI === select.dataset.current);

            select.add(option);
        });

        select.disabled = false;
        select.hidden = false;
        document.querySelector(`[data-voice-empty="${lang}"]`).hidden = true;
    });
}
