@props(['id', 'title', 'method' => 'DELETE', 'confirmLabel' => 'Устгах'])

{{-- Гохын товчлуур data-action болон data-message-ээр өгөгдлөө дамжуулна. --}}
<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}-title" aria-hidden="true" data-confirm-modal>
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" action="">
            @csrf
            @method($method)
            <div class="modal-header">
                <h2 class="modal-title h5" id="{{ $id }}-title">{{ $title }}</h2>
                <button type="button" class="btn btn-outline-primary btn-icon ms-auto" data-bs-dismiss="modal" aria-label="Хаах">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-0" data-confirm-text></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Болих</button>
                <button type="submit" class="btn btn-danger-solid">{{ $confirmLabel }}</button>
            </div>
        </form>
    </div>
</div>
