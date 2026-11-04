/**
 * Том файл оруулах явцыг харуулж, дэлгэц уншигчид 25% тутамд мэдэгдэнэ.
 * JavaScript ажиллахгүй бол form энгийнээр илгээгдэнэ.
 */
function readVideoDuration(file) {
    return new Promise((resolve) => {
        const video = document.createElement('video');
        const url = URL.createObjectURL(file);
        const finish = (value) => {
            URL.revokeObjectURL(url);
            resolve(value);
        };

        video.preload = 'metadata';
        video.onloadedmetadata = () => finish(Number.isFinite(video.duration) ? Math.round(video.duration) : null);
        video.onerror = () => finish(null);
        video.src = url;
    });
}

function errorMessage(xhr) {
    if (xhr.status === 422) {
        try {
            return Object.values(JSON.parse(xhr.responseText).errors).flat().join(' ');
        } catch {
            return 'Файл буруу байна.';
        }
    }

    if (xhr.status === 413) {
        return 'Файл серверийн зөвшөөрсөн хэмжээнээс том байна.';
    }

    if (xhr.status === 419) {
        return 'Хуудасны хугацаа дууссан байна. Хуудсаа дахин ачаалаад оролдоно уу.';
    }

    return 'Файлыг оруулж чадсангүй. Дахин оролдоно уу.';
}

export function initUploads() {
    document.querySelectorAll('[data-upload-form]').forEach((form) => {
        const fileInput = form.querySelector('input[type="file"]');
        const submit = form.querySelector('button[type="submit"]');
        const errorBox = form.querySelector('[data-upload-error]');
        const errorText = form.querySelector('[data-upload-error-text]');
        const progressBox = form.querySelector('[data-upload-progress]');
        const progress = progressBox.querySelector('progress');
        // Нуугдмал хэсгээс гадна байх ёстой, эс бөгөөс эхний зарлал алдагдана.
        const live = form.querySelector('[data-upload-live]');
        const durationField = form.querySelector('[data-duration-field]');

        const showError = (message) => {
            progressBox.hidden = true;
            submit.removeAttribute('aria-disabled');
            errorText.textContent = message;
            errorBox.hidden = false;
            errorBox.focus();
        };

        let busy = false;

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (busy) {
                return;
            }

            errorBox.hidden = true;

            const file = fileInput.files[0];

            if (!file) {
                showError('Эхлээд файл сонгоно уу.');
                return;
            }

            if (durationField) {
                durationField.value = (await readVideoDuration(file)) ?? '';
            }

            busy = true;
            submit.setAttribute('aria-disabled', 'true');
            progress.value = 0;
            progressBox.hidden = false;
            live.textContent = `${form.dataset.label} файлыг оруулж эхэллээ.`;

            let lastAnnounced = 0;
            const xhr = new XMLHttpRequest();

            xhr.open('POST', form.action);
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            xhr.upload.addEventListener('progress', (progressEvent) => {
                if (!progressEvent.lengthComputable) {
                    return;
                }

                const percent = Math.floor((progressEvent.loaded / progressEvent.total) * 100);

                progress.value = percent;

                if (percent - lastAnnounced >= 25) {
                    lastAnnounced = percent - (percent % 25);
                    live.textContent = `${lastAnnounced} хувь оруулсан.`;
                }
            });

            xhr.addEventListener('load', () => {
                busy = false;

                if (xhr.status >= 200 && xhr.status < 300) {
                    window.location.assign(JSON.parse(xhr.responseText).redirect);
                } else {
                    showError(errorMessage(xhr));
                }
            });

            xhr.addEventListener('error', () => {
                busy = false;
                showError('Холболт тасарлаа. Файлыг дахин оруулна уу.');
            });

            xhr.send(new FormData(form));
        });
    });
}
