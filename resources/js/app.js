import './bootstrap';
import * as bootstrap from 'bootstrap';
import { initPlayer } from './player';
import { initTextToSpeech } from './tts';
import { initUploads } from './uploads';
import { initReader } from './reader';
import { initSpeakButtons, initVoiceSelects } from './speech';
import { initArrowNavigation, initAudioPlayer, initQuiz } from './japanese';

window.bootstrap = bootstrap;

document.addEventListener('DOMContentLoaded', () => {
    // Хуудас ачаалагдмагц алдаа эсвэл амжилтын мэдээлэл рүү focus шилжүүлнэ,
    // ингэснээр дэлгэц уншигч түүнийг шууд уншина.
    document.querySelector('[data-focus-on-load]')?.focus();

    // Нууц үгийг харуулах/нуух товчлуур.
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        const input = document.getElementById(button.dataset.passwordToggle);

        button.addEventListener('click', () => {
            const show = input.type === 'password';

            input.type = show ? 'text' : 'password';
            button.setAttribute('aria-pressed', String(show));
            button.querySelector('i').className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    });

    // Баталгаажуулах modal-д тухайн мөрийн мэдээллийг дамжуулна.
    document.querySelectorAll('[data-confirm-modal]').forEach((modal) => {
        modal.addEventListener('show.bs.modal', (event) => {
            const trigger = event.relatedTarget;

            modal.querySelector('form').action = trigger.dataset.action;
            modal.querySelector('[data-confirm-text]').textContent = trigger.dataset.message;
        });
    });

    // Гар утасны нээлттэй цэсийг Escape-ээр хааж, focus-ийг товчлуурт нь буцаана.
    document.addEventListener('keydown', (event) => {
        const open = document.querySelector('.navbar-collapse.show');

        if (event.key === 'Escape' && open) {
            bootstrap.Collapse.getOrCreateInstance(open).hide();
            document.querySelector(`[aria-controls="${open.id}"]`)?.focus();
        }
    });

    initPlayer();
    initTextToSpeech();
    initUploads();
    initReader();
    initSpeakButtons();
    initVoiceSelects();
    initArrowNavigation();
    initAudioPlayer();
    initQuiz();
});
