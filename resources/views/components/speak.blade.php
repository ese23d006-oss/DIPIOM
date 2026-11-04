@props(['text', 'lang' => 'ja', 'label'])

{{-- JavaScript тухайн хэлний дуу хоолой олдвол л харуулна: байхгүй дууг байгаа мэт харуулахгүй. --}}
<button type="button" class="btn btn-sm btn-outline-primary" hidden
        data-speak="{{ $text }}" data-speak-lang="{{ $lang }}" aria-label="{{ $label }}">
    <i class="bi bi-volume-up" aria-hidden="true"></i> Сонсох
</button>
