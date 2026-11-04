@php
    $user = auth()->user();

    // Зөвхөн бодитоор ажилладаг хуудсуудын холбоос.
    $links = $user->isAdmin()
        ? [
            ['Хянах самбар', route('admin.dashboard'), 'bi-speedometer2', ['admin.dashboard']],
            ['Хэрэглэгчид', route('admin.users.index'), 'bi-people', ['admin.users.*']],
            ['Ангилал', route('admin.categories.index'), 'bi-tags', ['admin.categories.*']],
            ['Сургалт, хичээл', route('admin.courses.index'), 'bi-collection', ['admin.courses.*', 'admin.lessons.*', 'courses.show', 'lessons.show']],
            ['Шалгалтын үр дүн', route('admin.quiz-results.index'), 'bi-clipboard-data', ['admin.quiz-results.*']],
            ['Тайлан', route('admin.reports.index'), 'bi-file-earmark-bar-graph', ['admin.reports.*']],
            ['Санал хүсэлт', route('admin.feedback.index'), 'bi-chat-left-text', ['admin.feedback.*']],
            ['Япон хэл', route('admin.japanese.hub'), 'bi-translate', ['admin.japanese.*', 'japanese.*']],
            ['Нийтийн каталог', route('courses.index'), 'bi-globe', ['courses.index']],
            ['Системийн тохиргоо', route('admin.settings.edit'), 'bi-gear', ['admin.settings.*']],
            ['Хүртээмжийн тохиргоо', route('settings.edit'), 'bi-universal-access', ['settings.*']],
        ]
        : [
            ['Хянах самбар', route('student.dashboard'), 'bi-speedometer2', ['student.dashboard']],
            ['Сургалтууд', route('courses.index'), 'bi-collection', ['courses.*', 'lessons.*']],
            ['Миний сургалтууд', route('student.my-courses'), 'bi-play-circle', ['student.my-courses']],
            ['Миний явц', route('student.progress'), 'bi-graph-up', ['student.progress']],
            ['Япон хэл', route('japanese.index'), 'bi-translate', ['japanese.*']],
            ['Хадгалсан хичээлүүд', route('student.bookmarks'), 'bi-bookmark', ['student.bookmarks']],
            ['Шалгалтууд', route('student.quizzes'), 'bi-pencil-square', ['student.quizzes', 'quizzes.*']],
            ['Санал хүсэлт', route('student.feedback'), 'bi-chat-left-text', ['student.feedback*']],
            ['Миний профайл', route('profile.edit'), 'bi-person', ['profile.*']],
            ['Хүртээмжийн тохиргоо', route('settings.edit'), 'bi-universal-access', ['settings.*']],
        ];
@endphp

{{-- Том дэлгэцэд тогтмол багана, жижиг дэлгэцэд focus-ийг дотроо барьдаг цэс. --}}
<aside @class(['sidebar', 'offcanvas-lg', 'offcanvas-start', 'sidebar--admin' => $user->isAdmin()])
       id="app-sidebar" tabindex="-1" aria-label="Хажуугийн цэс">
    <div class="sidebar-inner">
        <div class="d-flex align-items-center justify-content-between gap-2">
            <x-layout.brand />
            <button type="button" class="btn btn-outline-light btn-icon sidebar-close d-lg-none"
                    data-bs-dismiss="offcanvas" data-bs-target="#app-sidebar" aria-label="Цэс хаах">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        </div>

        <nav aria-label="Үндсэн цэс">
            <p class="sidebar-eyebrow">{{ $user->isAdmin() ? 'Админ хэсэг' : 'Суралцагчийн хэсэг' }}</p>
            <ul class="sidebar-nav" data-arrow-nav>
                @foreach ($links as [$label, $url, $icon, $patterns])
                    <li>
                        <a class="sidebar-link" href="{{ $url }}" @if (request()->routeIs(...$patterns)) aria-current="page" @endif>
                            <i class="bi {{ $icon }}" aria-hidden="true"></i>
                            <span>{{ $label }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>
    </div>
</aside>
