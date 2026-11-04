<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Japanese\Audio;
use App\Models\Japanese\Character;
use App\Models\Japanese\Flashcard;
use App\Models\Japanese\Grammar;
use App\Models\Japanese\Kanji;
use App\Models\Japanese\Level;
use App\Models\Japanese\Question;
use App\Models\Japanese\Quiz;
use App\Models\Japanese\TestResult;
use App\Models\Japanese\UserProgress;
use App\Models\Japanese\Vocabulary;
use App\Models\User;
use Database\Seeders\JapaneseN5Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JapaneseModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $student;

    private Level $n5;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(JapaneseN5Seeder::class);
        $this->n5 = Level::where('code', 'N5')->firstOrFail();
        $this->student = User::factory()->create();
        $this->actingAs($this->student);
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->role = UserRole::Admin;
        $admin->save();

        return $admin;
    }

    private function publishAll(): void
    {
        foreach ([Vocabulary::class, Kanji::class, Grammar::class, Quiz::class] as $model) {
            $model::query()->update(['status' => 'published']);
        }
    }

    public function test_kana_are_complete_and_dakuten_are_not_merged_by_collation(): void
    {
        $this->assertSame(71, Character::where('script', 'hiragana')->count());
        $this->assertSame(71, Character::where('script', 'katakana')->count());
        $this->assertSame('ka', Character::where('character', 'か')->first()->romaji);
        $this->assertSame('ga', Character::where('character', 'が')->first()->romaji);
        $this->assertSame('pa', Character::where('character', 'パ')->first()->romaji);
        $this->assertSame('ba', Character::where('character', 'バ')->first()->romaji);
    }

    public function test_seeder_is_idempotent_and_keeps_admin_edits(): void
    {
        $word = Vocabulary::where('word', '学生')->firstOrFail();
        $word->update(['meaning_mn' => 'Админы засвар']);
        $count = Vocabulary::count();

        $this->seed(JapaneseN5Seeder::class);

        $this->assertSame($count, Vocabulary::count());
        $this->assertSame('Админы засвар', $word->fresh()->meaning_mn);
    }

    public function test_unverified_content_starts_as_draft_and_is_hidden(): void
    {
        $this->assertSame(0, Vocabulary::published()->count());
        $this->assertSame(0, Grammar::published()->count());
        $this->assertSame(0, Kanji::published()->count());

        $this->get(route('japanese.vocabulary', $this->n5))->assertOk()->assertDontSee('学生');
        $this->get(route('japanese.kanji', $this->n5))->assertDontSee('学');

        $grammar = Grammar::firstOrFail();
        $this->get(route('japanese.grammar.show', [$this->n5, $grammar]))->assertNotFound();
    }

    public function test_only_published_levels_open_and_others_are_stated_honestly(): void
    {
        $this->get(route('japanese.index'))->assertOk()->assertSee('N5')->assertSee('Агуулга хараахан ороогүй');
        $this->get(route('japanese.level', Level::where('code', 'N4')->first()))->assertNotFound();
        $this->get(route('japanese.level', $this->n5))->assertOk()->assertSee('Хирагана');
    }

    public function test_japanese_text_is_marked_with_lang_attributes(): void
    {
        $this->publishAll();

        $this->get(route('japanese.kana', [$this->n5, 'hiragana']))
            ->assertSee('<span lang="ja" class="jp-kana-char">あ</span>', false)
            ->assertSee('<span lang="ja-Latn" class="jp-kana-romaji">a</span>', false);

        $this->get(route('japanese.vocabulary', $this->n5))
            ->assertSee('<span lang="ja" >学生</span>', false)
            ->assertSee('<span lang="ja-Latn" >gakusei</span>', false)
            ->assertSee('Оюутан');

        $this->get(route('japanese.index'))->assertSee('<html lang="mn"', false);
    }

    public function test_listen_buttons_start_hidden_until_a_voice_is_found(): void
    {
        $character = Character::where('character', 'あ')->first();

        $this->get(route('japanese.character', [$this->n5, $character]))
            ->assertSee('data-speak="あ"', false)
            ->assertSeeInOrder(['hidden', 'data-speak="あ"'], false)
            ->assertSee('data-voice-notice="ja"', false);
    }

    public function test_character_page_shows_all_required_fields_and_navigation(): void
    {
        $character = Character::where('character', 'い')->first();

        $this->get(route('japanese.character', [$this->n5, $character]))
            ->assertOk()
            ->assertSee('い')->assertSee('и')->assertSee('いぬ')->assertSee('inu')->assertSee('нохой')
            ->assertSee('Өмнөх үсэг')->assertSee('Дараагийн үсэг');
    }

    public function test_progress_can_be_marked_and_cannot_target_drafts(): void
    {
        $character = Character::firstOrFail();

        $this->post(route('japanese.progress'), ['type' => 'character', 'id' => $character->id])->assertSessionHas('status');
        $this->assertSame(1, UserProgress::where('user_id', $this->student->id)->count());

        $this->post(route('japanese.progress'), ['type' => 'character', 'id' => $character->id]);
        $this->assertSame(0, UserProgress::count());

        $this->post(route('japanese.progress'), ['type' => 'grammar', 'id' => Grammar::first()->id])->assertNotFound();
        $this->post(route('japanese.progress'), ['type' => 'hack', 'id' => 1])->assertSessionHasErrors('type');
    }

    public function test_flashcards_store_status_per_user_and_filter(): void
    {
        $this->publishAll();
        $word = Vocabulary::orderBy('category_id')->orderBy('position')->first();

        $this->get(route('japanese.flashcards', $this->n5))
            ->assertOk()->assertSee($word->word)->assertSee('Карт 1 / 78')->assertSee('<details', false);

        $this->post(route('japanese.flashcards.update', [$this->n5, $word]), ['status' => 'hard', 'filter' => 'all', 'card' => 0])
            ->assertRedirect(route('japanese.flashcards', [$this->n5, 'filter' => 'all', 'card' => 1]));

        $card = Flashcard::firstOrFail();
        $this->assertSame('hard', $card->status);
        $this->assertSame($this->student->id, $card->user_id);

        $this->get(route('japanese.flashcards', [$this->n5, 'filter' => 'hard']))->assertSee('Карт 1 / 1')->assertSee($word->word);
        $this->get(route('japanese.flashcards', [$this->n5, 'filter' => 'known']))->assertSee('Энэ шүүлтэд карт алга байна.');

        $this->post(route('japanese.flashcards.update', [$this->n5, $word]), ['status' => 'bad'])->assertSessionHasErrors('status');
    }

    public function test_quiz_page_never_contains_correct_answers(): void
    {
        $quiz = Quiz::where('type', 'hiragana')->firstOrFail();
        $html = $this->get(route('japanese.quizzes.show', [$this->n5, $quiz]))->assertOk()->getContent();

        $this->assertStringNotContainsString('is_correct', $html);
        $this->assertStringNotContainsString('data-correct', $html);
        $this->assertStringNotContainsString('explanation', $html);
    }

    public function test_check_reports_right_and_wrong_with_explanation(): void
    {
        $quiz = Quiz::where('type', 'hiragana')->firstOrFail();
        $question = $quiz->questions()->with('answers')->first();
        $right = $question->answers->firstWhere('is_correct', true);
        $wrong = $question->answers->firstWhere('is_correct', false);

        $this->postJson(route('japanese.quizzes.check', $quiz), ['question_id' => $question->id, 'answers' => [$question->id => $right->id]])
            ->assertOk()->assertJson(['answered' => true, 'correct' => true]);

        $this->postJson(route('japanese.quizzes.check', $quiz), ['question_id' => $question->id, 'answers' => [$question->id => $wrong->id]])
            ->assertJson(['correct' => false, 'correct_answer' => $right->text, 'explanation' => $question->explanation_mn]);

        $this->postJson(route('japanese.quizzes.check', $quiz), ['question_id' => $question->id])
            ->assertJson(['answered' => false]);
    }

    public function test_check_rejects_question_from_other_quiz_and_unpublished_quiz(): void
    {
        $hiragana = Quiz::where('type', 'hiragana')->firstOrFail();
        $katakana = Quiz::where('type', 'katakana')->firstOrFail();
        $foreign = $katakana->questions()->first();

        $this->postJson(route('japanese.quizzes.check', $hiragana), ['question_id' => $foreign->id])->assertNotFound();

        $draft = Quiz::where('status', 'draft')->firstOrFail();
        $this->postJson(route('japanese.quizzes.check', $draft), ['question_id' => $draft->questions()->first()->id])->assertNotFound();
    }

    public function test_submit_grades_on_server_including_match_and_order(): void
    {
        $this->publishAll();

        $quiz = Quiz::where('type', 'vocabulary')->firstOrFail();
        $given = [];

        foreach ($quiz->questions()->with('answers')->get() as $question) {
            if ($question->kind === 'match') {
                $given[$question->id] = $question->answers->mapWithKeys(fn ($a) => [$a->id => $a->id])->all();
            } else {
                $given[$question->id] = $question->answers->firstWhere('is_correct', true)->id;
            }
        }

        $this->post(route('japanese.quizzes.submit', [$this->n5, $quiz]), ['answers' => $given])->assertRedirect();

        $result = TestResult::firstOrFail();
        $this->assertSame(100, $result->percent);
        $this->assertTrue($result->passed);

        $this->get(route('japanese.quizzes.result', [$this->n5, $result]))
            ->assertOk()->assertSee('4 / 4 оноо (100%)')->assertSee('Та тэнцлээ.');

        $grammar = Quiz::where('type', 'grammar')->firstOrFail();
        $order = $grammar->questions()->where('kind', 'order')->with('answers')->firstOrFail();
        $ids = $order->answers->pluck('id')->all();

        $this->postJson(route('japanese.quizzes.check', $grammar), ['question_id' => $order->id, 'answers' => [$order->id => $ids]])
            ->assertJson(['correct' => true]);
        $this->postJson(route('japanese.quizzes.check', $grammar), ['question_id' => $order->id, 'answers' => [$order->id => array_reverse($ids)]])
            ->assertJson(['correct' => false]);

        // Хоосон илгээвэл 0 оноо, алдаа гарахгүй.
        $this->post(route('japanese.quizzes.submit', [$this->n5, $grammar]), [])->assertRedirect();
        $this->assertSame(0, TestResult::latest('id')->first()->percent);
    }

    public function test_results_are_private_to_their_owner(): void
    {
        $quiz = Quiz::where('type', 'hiragana')->firstOrFail();
        $this->post(route('japanese.quizzes.submit', [$this->n5, $quiz]), []);
        $result = TestResult::firstOrFail();

        $this->actingAs(User::factory()->create())
            ->get(route('japanese.quizzes.result', [$this->n5, $result]))
            ->assertForbidden();
    }

    public function test_dashboard_shows_real_progress_and_saves_level_and_goal(): void
    {
        $this->post(route('japanese.preferences'), ['level_id' => $this->n5->id, 'daily_goal' => 25])->assertSessionHas('status');

        $this->post(route('japanese.progress'), ['type' => 'character', 'id' => Character::where('character', 'あ')->first()->id]);

        $this->get(route('japanese.index'))
            ->assertSee('Одоо сурч буй')
            ->assertSee('Өнөөдөр 25 зорилтоос 1 зүйл суралцсан (4%)')
            ->assertSee('あ');

        $this->publishAll();
        $word = Vocabulary::where('word', '学校')->firstOrFail();
        $this->travel(1)->minute();
        $this->post(route('japanese.flashcards.update', [$this->n5, $word]), ['status' => 'known']);
        $this->get(route('japanese.index'))->assertSee('<p class="mb-0"><span lang="ja">学校</span>', false);

        $n4 = Level::where('code', 'N4')->first();
        $this->post(route('japanese.preferences'), ['level_id' => $n4->id])->assertSessionHasErrors('level_id');
    }

    public function test_audio_player_is_disabled_without_file_and_listening_cannot_publish(): void
    {
        $audio = Audio::firstOrFail();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->patch(route('admin.japanese.status', ['audio', $audio->id]))
            ->assertSessionHas('error', 'Аудио файл оруулаагүй тул нийтлэх боломжгүй.');
        $this->assertSame('draft', $audio->fresh()->status);

        $this->actingAs($this->student)->get(route('japanese.listening', $this->n5))->assertSee('аудио файлтай сонсох дасгал нийтлэгдээгүй');
        $this->get(route('japanese.listening.show', [$this->n5, $audio]))->assertNotFound();

        $this->actingAs($admin)->get(route('admin.japanese.edit', ['audio', $audio->id]))->assertSee('Аудио файл оруулаагүй');
    }

    public function test_audio_upload_publish_listen_and_delete_flow(): void
    {
        Storage::fake('local');
        $audio = Audio::firstOrFail();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.japanese.audio.file.store', $audio), ['file' => UploadedFile::fake()->create('a.mp4', 1, 'video/mp4')])
            ->assertSessionHasErrors('file');

        $this->post(route('admin.japanese.audio.file.store', $audio), ['file' => UploadedFile::fake()->create('a.mp3', 1, 'audio/mpeg')])
            ->assertSessionHas('status');
        $path = $audio->fresh()->file_path;
        Storage::disk('local')->assertExists($path);

        $this->patch(route('admin.japanese.status', ['audio', $audio->id]))->assertSessionHas('status', 'Нийтэллээ.');

        $this->actingAs($this->student)
            ->get(route('japanese.listening.show', [$this->n5, $audio]))
            ->assertOk()->assertSee('data-audio-player', false)->assertSee('data-audio="stop"', false)->assertDontSee('autoplay', false);
        $this->get(route('japanese.audio.file', $audio))->assertOk();

        $this->actingAs($admin)->delete(route('admin.japanese.audio.file.destroy', $audio));
        Storage::disk('local')->assertMissing($path);
        $this->assertSame('draft', $audio->fresh()->status);
    }

    public function test_settings_store_screen_reader_preferences(): void
    {
        $this->put(route('settings.update'), [
            'font_size_percent' => 100, 'tts_rate' => '1.5', 'tts_volume' => '0.5',
            'tts_enabled' => '1', 'tts_read_japanese' => '1', 'tts_highlight' => '1',
            'tts_voice_ja' => 'voice-uri',
        ])->assertSessionHasNoErrors();

        $settings = $this->student->fresh()->settings;
        $this->assertTrue($settings->tts_enabled);
        $this->assertSame(0.5, $settings->tts_volume);
        $this->assertSame('voice-uri', $settings->tts_voice_ja);
        $this->assertFalse($settings->tts_read_mongolian);

        $this->actingAs($this->student->fresh())->get('/dashboard')->assertSee('data-reader', false);
    }

    public function test_reader_panel_is_off_by_default(): void
    {
        $this->assertFalse($this->student->settings->tts_enabled);
        $this->get('/dashboard')->assertDontSee('data-reader', false);
    }

    public function test_admin_can_manage_content_and_student_cannot(): void
    {
        $this->get(route('admin.japanese.hub'))->assertForbidden();
        $this->post(route('admin.japanese.store', 'vocabulary'), [])->assertForbidden();

        $admin = $this->admin();
        $this->actingAs($admin);

        foreach (\App\Support\Japanese\AdminResources::KEYS as $resource) {
            $this->get(route('admin.japanese.index', $resource))->assertOk();
        }
        $this->get(route('admin.japanese.hub'))->assertOk()->assertSee('баталгаажуулаагүй');
        $this->get(route('admin.japanese.progress'))->assertOk()->assertSee($this->student->name);

        $category = \App\Models\Japanese\Category::first();
        $this->post(route('admin.japanese.store', 'vocabulary'), [
            'level_id' => $this->n5->id, 'category_id' => $category->id,
            'word' => '猫', 'reading' => 'ねこ', 'romaji' => 'neko', 'meaning_mn' => 'Муур',
        ])->assertRedirect(route('admin.japanese.index', 'vocabulary'));

        $word = Vocabulary::where('word', '猫')->firstOrFail();
        $this->assertSame('draft', $word->status);

        $this->post(route('admin.japanese.store', 'vocabulary'), [
            'level_id' => $this->n5->id, 'category_id' => $category->id,
            'word' => '猫', 'reading' => 'ねこ', 'romaji' => 'neko', 'meaning_mn' => 'Давхар',
        ])->assertSessionHas('error');

        $this->put(route('admin.japanese.update', ['vocabulary', $word->id]), [
            'level_id' => $this->n5->id, 'category_id' => $category->id,
            'word' => '猫', 'reading' => 'ねこ', 'romaji' => 'neko', 'meaning_mn' => 'Засагдсан',
        ]);
        $this->assertSame('Засагдсан', $word->fresh()->meaning_mn);

        $this->patch(route('admin.japanese.status', ['vocabulary', $word->id]));
        $this->assertSame('published', $word->fresh()->status);

        $this->delete(route('admin.japanese.destroy', ['vocabulary', $word->id]));
        $this->assertNull($word->fresh());

        $this->delete(route('admin.japanese.destroy', ['categories', $category->id]))->assertSessionHas('error');
    }

    public function test_publish_all_skips_items_that_cannot_be_published(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('admin.japanese.publish-all', 'vocabulary'))->assertSessionHas('status');
        $this->assertSame(0, Vocabulary::where('status', 'draft')->count());

        $this->post(route('admin.japanese.publish-all', 'audio'));
        $this->assertSame(0, Audio::published()->count());
    }

    public function test_admin_can_create_questions_of_each_kind_with_validation(): void
    {
        $this->actingAs($this->admin());
        $quiz = Quiz::create(['level_id' => $this->n5->id, 'type' => 'vocabulary', 'title' => 'Шинэ тест']);

        $this->patch(route('admin.japanese.status', ['quizzes', $quiz->id]))->assertSessionHas('error');

        $this->post(route('admin.japanese.questions.store', $quiz), [
            'kind' => 'choice', 'prompt' => 'Асуулт', 'points' => 1,
            'answers' => [['text' => 'А', 'lang' => 'mn'], ['text' => 'Б', 'lang' => 'mn']],
        ])->assertSessionHasErrors('answers');

        $this->post(route('admin.japanese.questions.store', $quiz), [
            'kind' => 'choice', 'prompt' => 'Асуулт', 'points' => 2, 'correct' => 1,
            'answers' => [['text' => 'А', 'lang' => 'mn'], ['text' => 'Б', 'lang' => 'mn'], ['text' => '']],
        ])->assertSessionHasNoErrors();

        $question = Question::where('quiz_id', $quiz->id)->with('answers')->firstOrFail();
        $this->assertCount(2, $question->answers);
        $this->assertSame('Б', $question->answers->firstWhere('is_correct', true)->text);

        $this->post(route('admin.japanese.questions.store', $quiz), [
            'kind' => 'match', 'prompt' => 'Тааруул', 'points' => 1,
            'answers' => [['text' => '犬', 'match_text' => 'Нохой'], ['text' => '猫']],
        ])->assertSessionHasErrors('answers');

        $this->get(route('admin.japanese.questions.edit', $question))->assertOk();
        $this->delete(route('admin.japanese.questions.destroy', $question));
        $this->assertNull($question->fresh());
    }
}
