@props(['type', 'id', 'done' => false])

{{-- Товчлуурын нэр нь хийх үйлдлийг хэлнэ. aria-pressed-тэй давхарлавал төлөв ойлгомжгүй болно. --}}
@unless (auth()->user()->isAdmin())
    <form method="POST" action="{{ route('japanese.progress') }}">
        @csrf
        <input type="hidden" name="type" value="{{ $type }}">
        <input type="hidden" name="id" value="{{ $id }}">
        <button type="submit" class="btn btn-sm btn-outline-primary">
            <i class="bi {{ $done ? 'bi-check-circle-fill' : 'bi-check-circle' }}" aria-hidden="true"></i>
            {{ $done ? 'Сурсан тэмдгийг хасах' : 'Сурсан гэж тэмдэглэх' }}
        </button>
    </form>
@endunless
