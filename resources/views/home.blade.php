@extends('layouts.public')

@section('title', 'Нүүр')

@section('content')
    <section class="hero" aria-labelledby="hero-title">
        <div class="container-xl hero-grid">
            <div>
                <p class="eyebrow"><i class="bi bi-universal-access" aria-hidden="true"></i> Харааны бэрхшээлтэй иргэдэд зориулав</p>
                <h1 id="hero-title">Хүн бүрт хүртээмжтэй сургалт</h1>
                <p class="hero-lead">
                    Дэлгэц уншигч программ болон гар ашиглан онлайн сургалтад бие даан
                    хамрагдах зорилготой сургалтын систем.
                </p>
                <div class="hero-actions">
                    <a class="btn btn-primary btn-lg" href="{{ route('courses.index') }}">Сургалтуудыг үзэх</a>
                    @guest
                        @if (\App\Models\SystemSetting::registrationOpen())
                            <a class="btn btn-outline-primary btn-lg" href="{{ route('register') }}">Бүртгүүлэх</a>
                        @endif
                    @else
                        <a class="btn btn-outline-primary btn-lg" href="{{ auth()->user()->dashboardUrl() }}">Хянах самбар руу очих</a>
                    @endguest
                </div>
            </div>

            <svg class="hero-art" viewBox="0 0 520 380" aria-hidden="true" focusable="false">
                <rect class="art-frame" x="10" y="20" width="430" height="290" rx="16" stroke-width="1.5"/>
                <rect class="art-screen" x="26" y="36" width="398" height="200" rx="10"/>
                <circle class="art-on-screen" cx="225" cy="136" r="32"/>
                <path class="art-accent" d="M214 118 L242 136 L214 154 Z"/>
                <rect class="art-on-screen" x="110" y="204" width="230" height="10" rx="5" opacity=".85"/>
                <rect class="art-muted" x="26" y="256" width="398" height="8" rx="4"/>
                <rect class="art-accent" x="26" y="256" width="170" height="8" rx="4"/>
                <circle class="art-accent" cx="196" cy="260" r="8"/>
                <rect class="art-muted" x="26" y="280" width="52" height="16" rx="8"/>
                <rect class="art-muted" x="88" y="280" width="52" height="16" rx="8"/>
                <rect class="art-muted" x="150" y="280" width="52" height="16" rx="8"/>
                <rect class="art-tint" x="330" y="200" width="180" height="170" rx="16" stroke-width="1.5"/>
                <path class="art-track" d="M372 290 v-22 a48 48 0 0 1 96 0 v22"/>
                <rect class="art-accent" x="358" y="284" width="26" height="46" rx="10"/>
                <rect class="art-accent" x="456" y="284" width="26" height="46" rx="10"/>
            </svg>
        </div>
    </section>

    <section class="landing-section" aria-labelledby="features-title">
        <div class="container-xl">
            <div class="landing-section-head">
                <h2 id="features-title">Системийн боломжууд</h2>
                <p>Хичээлийг өөрт тохирсон хэлбэрээр үзэж, сонсож, уншина.</p>
            </div>
            <ul class="feature-grid">
                <li class="feature">
                    <span class="stat-icon" aria-hidden="true"><i class="bi bi-camera-video"></i></span>
                    <h3>Видео хичээл</h3>
                    <p>Том, тодорхой товчлууртай тоглуулагч. Сүүлд үзсэн хугацаанаасаа үргэлжлүүлнэ.</p>
                </li>
                <li class="feature">
                    <span class="stat-icon" aria-hidden="true"><i class="bi bi-soundwave"></i></span>
                    <h3>Аудио тайлбар</h3>
                    <p>Хичээлд оруулсан тайлбарын аудиог видеотой зэрэг сонсоно.</p>
                </li>
                <li class="feature">
                    <span class="stat-icon" aria-hidden="true"><i class="bi bi-badge-cc"></i></span>
                    <h3>Хадмал ба бичвэр</h3>
                    <p>Хадмалыг асааж унтрааж, хичээлийн бүрэн бичвэрийг уншина.</p>
                </li>
                <li class="feature">
                    <span class="stat-icon" aria-hidden="true"><i class="bi bi-graph-up"></i></span>
                    <h3>Явц хадгалалт</h3>
                    <p>Үзсэн хугацаа, дууссан хичээлүүд таны бүртгэлд хадгалагдана.</p>
                </li>
            </ul>
        </div>
    </section>

    <section class="landing-section landing-section--alt" aria-labelledby="a11y-title">
        <div class="container-xl a11y-grid">
            <div class="landing-section-head mb-0">
                <h2 id="a11y-title">Хүртээмжийг эхнээс нь бодсон</h2>
                <p>Хүртээмжийн тохиргоо таны бүртгэлд хадгалагдаж, нэвтрэх бүрт автоматаар хэрэгжинэ.</p>
            </div>
            <ul class="check-list">
                <li><i class="bi bi-check-circle-fill" aria-hidden="true"></i><span>Хуудас бүрийн эхэнд үндсэн агуулга руу шилжих холбоос</span></li>
                <li><i class="bi bi-check-circle-fill" aria-hidden="true"></i><span>Бүх үйлдлийг гараар хийх боломж, тод харагдах focus</span></li>
                <li><i class="bi bi-check-circle-fill" aria-hidden="true"></i><span>Текстийн хэмжээг 200% хүртэл томруулах тохиргоо</span></li>
                <li><i class="bi bi-check-circle-fill" aria-hidden="true"></i><span>Өндөр контраст горим, хөдөлгөөн багасгах сонголт</span></li>
            </ul>
        </div>
    </section>

    @if ($categories->isNotEmpty())
        <section class="landing-section" aria-labelledby="categories-title">
            <div class="container-xl">
                <div class="landing-section-head">
                    <h2 id="categories-title">Сургалтын ангиллууд</h2>
                </div>
                <ul class="category-chips">
                    @foreach ($categories as $category)
                        <li>
                            <a class="category-chip" href="{{ route('courses.index', ['category' => $category->slug]) }}">
                                {{ $category->name }}
                                <span>{{ $category->courses_count }} сургалт</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    @if ($featuredCourses->isNotEmpty())
        <section class="landing-section landing-section--alt" aria-labelledby="featured-title">
            <div class="container-xl">
                <div class="section-head">
                    <h2 id="featured-title">Шинэ сургалтууд</h2>
                    <a href="{{ route('courses.index') }}">Бүх сургалтыг харах</a>
                </div>
                <ul class="grid-cards">
                    @foreach ($featuredCourses as $course)
                        <li><x-course-card :course="$course" /></li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    <section class="landing-section" aria-labelledby="steps-title">
        <div class="container-xl">
            <div class="landing-section-head">
                <h2 id="steps-title">Хэрхэн эхлэх вэ</h2>
            </div>
            <ol class="steps">
                <li>
                    <h3>Бүртгүүлнэ</h3>
                    <p>Нэр, имэйл хаягаа оруулж үнэгүй бүртгэл үүсгэнэ.</p>
                </li>
                <li>
                    <h3>Сургалт сонгоно</h3>
                    <p>Хайлт эсвэл ангиллаар өөрт хэрэгтэй сургалтаа олно.</p>
                </li>
                <li>
                    <h3>Өөрийн хэмнэлээр суралцана</h3>
                    <p>Явц тань хадгалагдах тул хүссэн үедээ үргэлжлүүлнэ.</p>
                </li>
            </ol>
        </div>
    </section>
@endsection
