<?php

namespace Tests\Feature;

use App\Enums\FeedbackStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Course;
use App\Models\Feedback;
use App\Models\Lesson;
use App\Models\Progress;
use App\Models\Quiz;
use App\Models\QuizResult;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class FeedbackAndReportsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->role = UserRole::Admin;
        $admin->save();

        return $admin;
    }

    /** Санал хүсэлт */
    public function test_student_can_send_and_see_own_feedback_only(): void
    {
        $student = User::factory()->create();
        Feedback::create(['user_id' => User::factory()->create()->id, 'subject' => 'Бусдын асуулт', 'message' => 'Х']);

        $this->actingAs($student)->post('/feedback', ['subject' => '', 'message' => ''])
            ->assertSessionHasErrors(['subject' => 'Гарчиг талбарыг бөглөнө үү.', 'message' => 'Агуулга талбарыг бөглөнө үү.']);

        $this->post('/feedback', ['subject' => 'Миний асуулт', 'message' => "Нэг\nХоёр", 'status' => 'answered', 'user_id' => 999])
            ->assertRedirect(route('student.feedback'))
            ->assertSessionHas('status');

        $feedback = Feedback::where('subject', 'Миний асуулт')->firstOrFail();
        $this->assertSame($student->id, $feedback->user_id);
        $this->assertSame(FeedbackStatus::Pending, $feedback->status);

        $this->get('/feedback')->assertOk()->assertSee('Миний асуулт')->assertDontSee('Бусдын асуулт')->assertSee('Хариу хүлээж буй');
    }

    public function test_feedback_content_is_escaped(): void
    {
        $student = User::factory()->create();
        Feedback::create(['user_id' => $student->id, 'subject' => '<script>a()</script>', 'message' => '<img src=x>']);

        $this->actingAs($student)->get('/feedback')->assertDontSee('<script>a()</script>', false)->assertDontSee('<img src=x>', false);
        $this->actingAs($this->admin())->get(route('admin.feedback.index'))->assertDontSee('<script>a()</script>', false);
    }

    public function test_admin_replies_and_student_is_notified(): void
    {
        $student = User::factory()->create();
        $feedback = Feedback::create(['user_id' => $student->id, 'subject' => 'Хадмалын асуудал', 'message' => 'Агуулга']);
        $admin = $this->admin();

        // Шинэ санал хүсэлт админд мэдэгдэл болж, холбоостой гарна.
        $this->actingAs($admin)->get('/admin')
            ->assertSee('Шинэ санал хүсэлт: Хадмалын асуудал')
            ->assertSee(route('admin.feedback.show', $feedback), false);

        // Гарчиг дээд мөрний мэдэгдэлд ч гардаг тул хүснэгтийн тоог шалгана.
        $this->get(route('admin.feedback.index', ['status' => 'answered']))->assertSee('Олдсон: 0');
        $this->get(route('admin.feedback.index', ['status' => 'pending']))->assertSee('Олдсон: 1');
        $this->get(route('admin.feedback.index', ['status' => ['x']]))->assertOk()->assertSee('Олдсон: 1');
        $this->get(route('admin.feedback.show', $feedback))->assertOk()->assertSee('Агуулга');

        $this->put(route('admin.feedback.reply', $feedback), ['admin_reply' => ''])->assertSessionHasErrors('admin_reply');

        $this->put(route('admin.feedback.reply', $feedback), ['admin_reply' => 'Засагдсан.'])->assertRedirect(route('admin.feedback.index'));

        $feedback->refresh();
        $this->assertSame(FeedbackStatus::Answered, $feedback->status);
        $this->assertSame($admin->id, $feedback->replied_by);
        $this->assertNotNull($feedback->replied_at);

        $this->actingAs($student)->get('/feedback')->assertSee('Засагдсан.')->assertSee('Хариу өгсөн');
        $this->get('/dashboard')
            ->assertSee('Хариу ирлээ: Хадмалын асуудал')
            ->assertSee('Мэдэгдэл, 1 шинэ')
            ->assertSee(route('student.feedback'), false);
    }

    public function test_feedback_access_is_role_protected(): void
    {
        $feedback = Feedback::create(['user_id' => User::factory()->create()->id, 'subject' => 'С', 'message' => 'М']);

        $this->get('/feedback')->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create());
        $this->get(route('admin.feedback.index'))->assertForbidden();
        $this->get(route('admin.feedback.show', $feedback))->assertForbidden();
        $this->put(route('admin.feedback.reply', $feedback), ['admin_reply' => 'Х'])->assertForbidden();
        $this->assertNull($feedback->fresh()->admin_reply);

        $this->actingAs($this->admin())->post('/feedback', ['subject' => 'С', 'message' => 'М'])->assertForbidden();
    }

    /** @return array{0: User, 1: Quiz} */
    private function seedReportData(): array
    {
        $student = User::factory()->create(['name' => 'Өлзий Бат']);
        $category = Category::create(['name' => 'К', 'slug' => 'k']);
        $course = Course::create(['category_id' => $category->id, 'title' => 'Сургалт Ү', 'slug' => 'c', 'description' => 'Т', 'is_published' => true]);
        $lesson = Lesson::create(['course_id' => $course->id, 'title' => 'Хичээл', 'is_published' => true]);
        Progress::create(['user_id' => $student->id, 'lesson_id' => $lesson->id, 'is_completed' => true, 'last_watched_at' => now()]);
        $quiz = Quiz::create(['course_id' => $course->id, 'title' => 'Шалгалт Ө']);
        QuizResult::create(['user_id' => $student->id, 'quiz_id' => $quiz->id, 'score' => 3, 'max_score' => 4, 'percent' => 75, 'passed' => true, 'answers' => [], 'submitted_at' => now()]);

        return [$student, $quiz];
    }

    public function test_report_pages_show_real_data_and_empty_state(): void
    {
        $this->actingAs($this->admin());

        $this->get(route('admin.reports.index'))->assertOk()->assertSee('Суралцагчдын явц');
        $this->get(route('admin.reports.show', 'results'))->assertOk()->assertSee('Энэ тайланд одоогоор өгөгдөл алга байна.');

        $this->seedReportData();

        $this->get(route('admin.reports.show', 'progress'))->assertOk()->assertSee('Өлзий Бат')->assertSee('75%');
        $this->get(route('admin.reports.show', 'results'))->assertOk()->assertSee('Шалгалт Ө')->assertSee('3 / 4')->assertSee('Тэнцсэн');
        $this->get(route('admin.reports.show', 'stats'))->assertOk()->assertSee('Нийт сургалт');
        $this->get('/admin/reports/hack')->assertNotFound();
    }

    public function test_excel_export_contains_mongolian_data_and_neutralizes_formulas(): void
    {
        [$student] = $this->seedReportData();
        User::factory()->create(['name' => '=HYPERLINK("http://evil","x")']);

        $response = $this->actingAs($this->admin())->get(route('admin.reports.download', ['progress', 'xlsx']))->assertOk();
        $path = $response->baseResponse->getFile()->getPathname();

        $sheet = IOFactory::load($path)->getActiveSheet();
        $values = collect($sheet->toArray(null, false, false));

        $this->assertSame('Суралцагч', $values[0][0]);
        $row = $values->first(fn ($r) => $r[0] === $student->name);
        $this->assertNotNull($row);
        $this->assertSame(1, (int) $row[3]);
        $this->assertSame('75%', $row[6]);

        $evil = null;
        foreach ($sheet->getRowIterator(2) as $rowIterator) {
            $cell = $sheet->getCell([1, $rowIterator->getRowIndex()]);
            if (str_starts_with((string) $cell->getValue(), '=')) {
                $evil = $cell;
            }
        }
        $this->assertNotNull($evil);
        $this->assertSame(DataType::TYPE_STRING, $evil->getDataType());

        @unlink($path);
    }

    public function test_pdf_export_is_a_real_pdf_with_download_name(): void
    {
        $this->seedReportData();

        foreach (['progress', 'results', 'stats'] as $report) {
            $response = $this->actingAs($this->admin())->get(route('admin.reports.download', [$report, 'pdf']))->assertOk();

            $this->assertStringStartsWith('%PDF', $response->getContent());
            $this->assertStringContainsString("{$report}-", $response->headers->get('content-disposition'));
        }
    }

    public function test_large_pdf_is_refused_with_guidance_instead_of_crashing(): void
    {
        config(['lms.reports.pdf_max_rows' => 1]);
        User::factory()->count(2)->create();

        $this->actingAs($this->admin())
            ->get(route('admin.reports.download', ['progress', 'pdf']))
            ->assertRedirect(route('admin.reports.show', 'progress'))
            ->assertSessionHas('error');

        $this->get(route('admin.reports.show', 'progress'))->assertSee('Excel файлаар татна уу');
        $this->get(route('admin.reports.download', ['progress', 'xlsx']))->assertOk();
    }

    public function test_dashboard_course_stats_count_distinct_learners_and_list_empty_courses(): void
    {
        [$student] = $this->seedReportData();
        $course = Course::where('slug', 'c')->firstOrFail();
        Course::create(['category_id' => $course->category_id, 'title' => 'Суралцагчгүй сургалт', 'slug' => 'empty', 'description' => 'Т']);

        // Нэг суралцагч хоёр хичээл эхэлсэн ч нэг л тоологдоно.
        $second = Lesson::create(['course_id' => $course->id, 'title' => 'Хоёр', 'is_published' => true]);
        Progress::create(['user_id' => $student->id, 'lesson_id' => $second->id]);

        $this->actingAs($this->admin())->get('/admin')
            ->assertOk()
            ->assertSeeInOrder(['Сургалт Ү', '2', '1'])
            ->assertSeeInOrder(['Суралцагчгүй сургалт', '0', '0']);
    }

    public function test_reports_are_admin_only(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('admin.reports.index'))->assertForbidden();
        $this->get(route('admin.reports.download', ['progress', 'xlsx']))->assertForbidden();
        $this->get(route('admin.reports.download', ['progress', 'pdf']))->assertForbidden();
    }
}
