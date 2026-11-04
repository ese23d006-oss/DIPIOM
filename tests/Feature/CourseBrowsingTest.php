<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Progress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseBrowsingTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 0;

    private function course(array $attributes = [], ?Category $category = null): Course
    {
        $this->sequence++;
        $category ??= Category::firstOrCreate(['slug' => 'computer'], ['name' => 'Компьютер']);

        return Course::create(array_merge([
            'category_id' => $category->id,
            'title' => "Сургалт {$this->sequence}",
            'slug' => "course-{$this->sequence}",
            'description' => 'Тайлбар',
            'is_published' => true,
        ], $attributes));
    }

    private function lesson(Course $course, array $attributes = []): Lesson
    {
        return Lesson::create(array_merge([
            'course_id' => $course->id,
            'title' => 'Хичээл',
            'is_published' => true,
        ], $attributes));
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->role = UserRole::Admin;
        $admin->save();

        return $admin;
    }

    public function test_guest_sees_only_published_courses(): void
    {
        $this->course(['title' => 'Нээлттэй сургалт']);
        $this->course(['title' => 'Ноорог сургалт', 'is_published' => false]);

        $this->get('/courses')
            ->assertOk()
            ->assertSee('Нээлттэй сургалт')
            ->assertDontSee('Ноорог сургалт')
            ->assertSee('Нийт 1 сургалт');
    }

    public function test_empty_catalog_is_stated_honestly(): void
    {
        $this->get('/courses')->assertOk()->assertSee('Одоогоор нийтлэгдсэн сургалт байхгүй байна.');
    }

    public function test_search_matches_title_and_description_and_moves_focus_to_result(): void
    {
        $this->course(['title' => 'Англи хэл']);
        $this->course(['title' => 'Математик', 'description' => 'Англи хэлтэй холбоотой']);
        $this->course(['title' => 'Түүх']);

        $this->get('/courses?q='.urlencode('Англи'))
            ->assertSee('Англи хэл')
            ->assertSee('Математик')
            ->assertDontSee('Түүх')
            ->assertSee('Хайлтын үр дүн: 2 сургалт олдлоо')
            ->assertSee('data-focus-on-load', false);
    }

    public function test_search_treats_wildcards_literally(): void
    {
        $this->course(['title' => 'Энгийн сургалт']);

        $this->get('/courses?q='.urlencode('%'))
            ->assertDontSee('Энгийн сургалт')
            ->assertSee('Таны хайлтад тохирох сургалт олдсонгүй');
    }

    public function test_category_filter_returns_only_that_category(): void
    {
        $languages = Category::create(['name' => 'Хэл', 'slug' => 'languages']);
        $this->course(['title' => 'Англи хэл'], $languages);
        $this->course(['title' => 'Excel']);

        $this->get('/courses?category=languages')
            ->assertSee('Англи хэл')
            ->assertDontSee('Excel')
            ->assertSee('<option value="languages" selected>Хэл</option>', false);
    }

    public function test_malformed_query_parameters_do_not_crash(): void
    {
        $this->course(['title' => 'Англи хэл']);

        $this->get('/courses?q[]=x&category[]=y')->assertOk()->assertSee('Англи хэл');
    }

    public function test_pagination_keeps_search_and_is_labelled(): void
    {
        foreach (range(1, 13) as $ignored) {
            $this->course(['description' => 'зорилтот']);
        }

        $this->get('/courses?q='.urlencode('зорилтот'))
            ->assertSee('aria-label="Хуудас солих"', false)
            ->assertSee('aria-current="page"', false)
            ->assertSee('Нийт 2 хуудасны 1-р хуудас')
            ->assertSee('page=2', false)
            ->assertSee('q=%D0%B7', false);
    }

    public function test_course_content_is_escaped(): void
    {
        $course = $this->course(['title' => '<script>alert(1)</script>', 'description' => '<img src=x onerror=alert(2)>']);

        $this->get('/courses')->assertDontSee('<script>alert(1)</script>', false);
        $this->get(route('courses.show', $course))
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('<img src=x', false);
    }

    public function test_course_detail_lists_published_lessons_in_order(): void
    {
        $course = $this->course();
        $this->lesson($course, ['title' => 'Хоёрдугаар', 'position' => 2, 'duration_seconds' => 125]);
        $this->lesson($course, ['title' => 'Нэгдүгээр', 'position' => 1]);
        $this->lesson($course, ['title' => 'Ноорог хичээл', 'position' => 3, 'is_published' => false]);

        $this->get(route('courses.show', $course))
            ->assertOk()
            ->assertSeeInOrder(['Нэгдүгээр', 'Хоёрдугаар'])
            ->assertSee('2 минут 5 секунд')
            ->assertDontSee('Ноорог хичээл')
            ->assertSee('Хичээлүүд (2)');
    }

    public function test_unpublished_course_is_hidden_except_from_admin(): void
    {
        $course = $this->course(['is_published' => false]);

        $this->get(route('courses.show', $course))->assertNotFound();
        $this->actingAs(User::factory()->create())->get(route('courses.show', $course))->assertNotFound();
        $this->actingAs($this->admin())->get(route('courses.show', $course))->assertOk();
    }

    public function test_my_courses_requires_student_role(): void
    {
        $this->get('/my-courses')->assertRedirect(route('login'));
        $this->actingAs($this->admin())->get('/my-courses')->assertForbidden();
    }

    public function test_my_courses_is_empty_until_a_lesson_is_started(): void
    {
        $this->course(['title' => 'Эхлээгүй сургалт']);

        $this->actingAs(User::factory()->create())
            ->get('/my-courses')
            ->assertOk()
            ->assertDontSee('Эхлээгүй сургалт')
            ->assertSee('Та одоогоор сургалт эхлээгүй байна.');
    }

    public function test_my_courses_shows_own_progress_and_correct_percent(): void
    {
        $student = User::factory()->create();
        $other = User::factory()->create();
        $mine = $this->course(['title' => 'Миний сургалт']);
        $theirs = $this->course(['title' => 'Бусдын сургалт']);

        $lessons = collect(range(1, 3))->map(fn ($i) => $this->lesson($mine, ['position' => $i]));
        $this->lesson($mine, ['position' => 4, 'is_published' => false]);

        Progress::create(['user_id' => $student->id, 'lesson_id' => $lessons[0]->id, 'is_completed' => true, 'last_watched_at' => now()]);
        Progress::create(['user_id' => $student->id, 'lesson_id' => $lessons[1]->id, 'percent' => 40, 'last_watched_at' => now()]);
        // Өөр хэрэглэгчийн дуусгасан нь энэ суралцагчийн тоонд орохгүй.
        Progress::create(['user_id' => $other->id, 'lesson_id' => $lessons[2]->id, 'is_completed' => true]);
        Progress::create(['user_id' => $other->id, 'lesson_id' => $this->lesson($theirs)->id, 'is_completed' => true]);

        $this->actingAs($student)
            ->get('/my-courses')
            ->assertOk()
            ->assertSee('Миний сургалт')
            ->assertDontSee('Бусдын сургалт')
            ->assertSee('3 хичээлээс 1 нь дууссан (33%)')
            ->assertSee('<progress', false);

        $this->actingAs($student)
            ->get(route('courses.show', $mine))
            ->assertSee('дууссан')
            ->assertSee('40% үзсэн');
    }

    public function test_most_recently_watched_course_comes_first(): void
    {
        $student = User::factory()->create();
        $older = $this->course(['title' => 'Хуучин үзсэн']);
        $newer = $this->course(['title' => 'Шинэ үзсэн']);

        Progress::create(['user_id' => $student->id, 'lesson_id' => $this->lesson($older)->id, 'last_watched_at' => now()->subDay()]);
        Progress::create(['user_id' => $student->id, 'lesson_id' => $this->lesson($newer)->id, 'last_watched_at' => now()]);

        $this->actingAs($student)->get('/dashboard')->assertSeeInOrder(['Шинэ үзсэн', 'Хуучин үзсэн']);
    }

    public function test_about_page_is_public(): void
    {
        $this->get('/about')->assertOk()->assertSee('Системийн тухай');
    }
}
