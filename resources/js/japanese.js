/** Япон хэлний модулийн тест, аудио, жагсаалтын гарын удирдлага. */
function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

/** [data-arrow-nav] доторх холбоосуудад сум, Home, End товчоор шилжинэ. */
export function initArrowNavigation() {
    document.querySelectorAll('[data-arrow-nav]').forEach((container) => {
        container.addEventListener('keydown', (event) => {
            if (!['ArrowDown', 'ArrowUp', 'ArrowRight', 'ArrowLeft', 'Home', 'End'].includes(event.key)) {
                return;
            }

            const links = [...container.querySelectorAll('a')];
            const current = links.indexOf(document.activeElement);

            if (current < 0) {
                return;
            }

            event.preventDefault();

            const target = {
                ArrowDown: current + 1,
                ArrowRight: current + 1,
                ArrowUp: current - 1,
                ArrowLeft: current - 1,
                Home: 0,
                End: links.length - 1,
            }[event.key];

            links[Math.min(links.length - 1, Math.max(0, target))].focus();
        });
    });
}

/** Япон үгсийг lang="ja"-аар тэмдэглэнэ: Монгол хариултыг Япон дуугаар уншихгүй. */
const JAPANESE_RUN = /([\p{Script=Hiragana}\p{Script=Katakana}\p{Script=Han}ー～。、]+)/u;

function appendMixed(parent, text) {
    text.split(JAPANESE_RUN).forEach((part, index) => {
        if (!part) {
            return;
        }

        if (index % 2) {
            const span = document.createElement('span');

            span.lang = 'ja';
            span.textContent = part;
            parent.append(span);
        } else {
            parent.append(part);
        }
    });
}

function setFeedback(box, result) {
    const line = (label, text) => {
        const p = document.createElement('p');

        p.className = 'mb-1';
        p.append(label);

        if (text) {
            appendMixed(p, text);
        }

        return p;
    };

    box.className = `jp-feedback mt-3 notice ${result.correct ? 'notice--success' : 'notice--error'}`;
    box.replaceChildren(line(result.correct ? 'Зөв байна.' : 'Буруу байна.'));

    if (!result.correct) {
        box.append(line('Зөв хариулт: ', result.correct_answer));
    }
    if (result.explanation) {
        box.append(line('Тайлбар: ', result.explanation));
    }
}

/** Асуултыг нэг нэгээр харуулж хариултыг шалгана. JavaScript-гүй бол form бүтнээр илгээгдэнэ. */
export function initQuiz() {
    const form = document.querySelector('[data-quiz]');

    if (!form) {
        return;
    }

    const questions = [...form.querySelectorAll('[data-question]')];
    const actionButton = form.querySelector('[data-quiz-action]');
    const submitButton = form.querySelector('[data-quiz-submit]');
    const scoreLabel = form.querySelector('[data-quiz-score]');
    const live = form.querySelector('[data-quiz-live]');
    const announce = (box) => {
        // Текстийг хоослоод дахин оруулснаар давтагдсан мэдээлэл ч зарлагдана.
        live.replaceChildren();
        live.append(...[...box.childNodes].map((node) => node.cloneNode(true)));
    };
    let current = 0;
    let checked = false;
    let score = 0;
    let answered = 0;

    const show = (index) => {
        questions.forEach((question, position) => {
            question.hidden = position !== index;
        });
    };

    const answerEntries = (question) => {
        const data = new FormData(form);
        const prefix = `answers[${question.dataset.question}]`;

        return [...data.entries()].filter(([name]) => name.startsWith(prefix));
    };

    /** Шалгасны дараа хариултыг өөрчлөх боломжгүй: зөв хариултыг харсны дараа оноо нэмэхгүй. */
    const lock = (question, entries) => {
        const controls = question.querySelector('[data-controls]');

        controls.querySelectorAll('input,select').forEach((control) => {
            control.disabled = true;
        });
        controls.hidden = true;

        entries.forEach(([name, value]) => {
            const hidden = document.createElement('input');

            hidden.type = 'hidden';
            hidden.name = name;
            hidden.value = value;
            question.append(hidden);
        });
    };

    const check = async () => {
        const question = questions[current];
        const entries = answerEntries(question);
        const box = question.querySelector('[data-feedback]');
        const body = new FormData();

        body.append('_token', csrfToken());
        body.append('question_id', question.dataset.question);
        entries.forEach(([name, value]) => body.append(name, value));

        let result;

        try {
            const response = await fetch(form.dataset.checkUrl, {
                method: 'POST',
                body,
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error(String(response.status));
            }
            result = await response.json();
        } catch {
            box.className = 'jp-feedback mt-3 notice notice--error';
            box.textContent = 'Холболтын алдаа гарлаа. Дахин оролдоно уу.';
            announce(box);
            return;
        }

        if (!result.answered) {
            box.className = 'jp-feedback mt-3 notice notice--warning';
            box.textContent = result.message;
            announce(box);
            return;
        }

        lock(question, entries);
        setFeedback(box, result);
        announce(box);
        answered += 1;

        if (result.correct) {
            score += result.points;
        }

        scoreLabel.hidden = false;
        scoreLabel.textContent = `Одоогийн оноо: ${score}`;
        checked = true;
        // Товч хэвээрээ тул focus алдагдахгүй, дэлгэц уншигч үр дүнг aria-live-аар уншина.
        actionButton.textContent = current + 1 === questions.length ? 'Дуусгаж дүн харах' : 'Дараагийн асуулт';
    };

    const advance = () => {
        if (current + 1 === questions.length) {
            form.submit();
            return;
        }

        current += 1;
        checked = false;
        show(current);
        actionButton.textContent = 'Хариулт шалгах';
        questions[current].querySelector('legend').focus();
    };

    actionButton.hidden = false;
    submitButton.hidden = true;
    show(0);

    actionButton.addEventListener('click', () => (checked ? advance() : check()));
    form.addEventListener('submit', (event) => {
        // Enter дарахад шалгах товчлуурын үйлдлийг хийнэ, дуусаагүй тест илгээгдэхгүй.
        if (answered < questions.length) {
            event.preventDefault();
            actionButton.click();
        }
    });
}

export function initAudioPlayer() {
    const root = document.querySelector('[data-audio-player]');

    if (!root) {
        return;
    }

    const audio = root.querySelector('audio');
    const live = root.querySelector('[data-audio-live]');
    const control = (name) => root.querySelector(`[data-audio="${name}"]`);
    const play = control('play');

    const say = (message) => {
        live.textContent = message;
    };

    play.addEventListener('click', () => {
        if (audio.paused) {
            audio.play().catch(() => say('Аудиог тоглуулж чадсангүй.'));
        } else {
            audio.pause();
        }
    });

    audio.addEventListener('play', () => {
        play.querySelector('i').className = 'bi bi-pause-fill';
        play.querySelector('span').textContent = 'Түр зогсоох';
    });

    audio.addEventListener('pause', () => {
        play.querySelector('i').className = 'bi bi-play-fill';
        play.querySelector('span').textContent = 'Тоглуулах';
    });

    audio.addEventListener('ended', () => say('Аудио дууслаа.'));
    audio.addEventListener('error', () => say('Аудиог ачаалж чадсангүй.'));

    control('stop').addEventListener('click', () => {
        audio.pause();
        audio.currentTime = 0;
        say('Зогслоо.');
    });

    control('back').addEventListener('click', () => {
        audio.currentTime = Math.max(0, audio.currentTime - 5);
        say('5 секунд буцаалаа.');
    });

    control('replay').addEventListener('click', () => {
        audio.currentTime = 0;
        audio.play().catch(() => say('Аудиог тоглуулж чадсангүй.'));
    });

    control('volume').addEventListener('input', (event) => {
        audio.volume = event.target.value / 100;
        event.target.setAttribute('aria-valuetext', `${event.target.value} хувь`);
    });

    control('rate').addEventListener('change', (event) => {
        audio.playbackRate = Number(event.target.value);
        say(`Хурд ${event.target.value} дахин.`);
    });
}
