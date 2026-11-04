<?php

namespace App\Support\Japanese;

use App\Models\Japanese\Audio;
use App\Models\Japanese\Category;
use App\Models\Japanese\Character;
use App\Models\Japanese\Example;
use App\Models\Japanese\Grammar;
use App\Models\Japanese\Kanji;
use App\Models\Japanese\Lesson;
use App\Models\Japanese\Level;
use App\Models\Japanese\Quiz;
use App\Models\Japanese\Vocabulary;

/**
 * Япон агуулгын админ CRUD-ийн тодорхойлолт. Нэг загвараар бүх төрлийг удирдана.
 * Талбар: [нэр, тайлбар, төрөл, заавал эсэх, хэл]
 */
class AdminResources
{
    public const KEYS = ['levels', 'categories', 'characters', 'vocabulary', 'kanji', 'grammar', 'lessons', 'audio', 'quizzes', 'examples'];

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        $level = ['level_id', 'JLPT түвшин', 'level', true];
        $position = ['position', 'Дараалал', 'number', false];

        return [
            'levels' => [
                'label' => 'JLPT түвшин', 'single' => 'түвшин', 'model' => Level::class, 'title' => 'code', 'hasStatus' => true,
                'columns' => ['code' => 'Код', 'name' => 'Нэр'],
                'fields' => [['code', 'Код (жишээ нь N5)', 'text', true], ['name', 'Нэр', 'text', true], ['description', 'Тайлбар', 'textarea', false], $position],
            ],
            'categories' => [
                'label' => 'Үгийн сангийн сэдэв', 'single' => 'сэдэв', 'model' => Category::class, 'title' => 'name', 'hasStatus' => false,
                'columns' => ['name' => 'Нэр', 'slug' => 'Хаяг'],
                'fields' => [['name', 'Нэр', 'text', true], ['slug', 'Хаягийн нэр (латин)', 'text', true], $position],
            ],
            'characters' => [
                'label' => 'Хирагана, катакана', 'single' => 'үсэг', 'model' => Character::class, 'title' => 'character', 'hasStatus' => true,
                'columns' => ['character' => 'Үсэг', 'romaji' => 'Ромажи', 'reading_mn' => 'Монгол дуудлага', 'script' => 'Төрөл'],
                'fields' => [
                    $level, ['script', 'Төрөл', 'script', true], ['character', 'Үсэг', 'text', true, 'ja'], ['romaji', 'Ромажи', 'text', true, 'ja-Latn'],
                    ['reading_mn', 'Монгол дуудлага', 'text', true], ['row_name', 'Эгнээ', 'text', true],
                    ['example_word', 'Жишээ үг', 'text', false, 'ja'], ['example_reading', 'Жишээ үгийн уншлага', 'text', false, 'ja'],
                    ['example_romaji', 'Жишээ үгийн ромажи', 'text', false, 'ja-Latn'], ['example_mn', 'Жишээ үгийн орчуулга', 'text', false], $position,
                ],
            ],
            'vocabulary' => [
                'label' => 'Үгийн сан', 'single' => 'үг', 'model' => Vocabulary::class, 'title' => 'word', 'hasStatus' => true,
                'columns' => ['word' => 'Япон', 'reading' => 'Уншлага', 'romaji' => 'Ромажи', 'meaning_mn' => 'Монгол'],
                'fields' => [
                    $level, ['category_id', 'Сэдэв', 'category', true], ['word', 'Япон үг', 'text', true, 'ja'], ['reading', 'Хирагана уншлага', 'text', true, 'ja'],
                    ['katakana', 'Катакана уншлага', 'text', false, 'ja'], ['romaji', 'Ромажи', 'text', true, 'ja-Latn'], ['meaning_mn', 'Монгол орчуулга', 'text', true],
                    ['example_ja', 'Жишээ өгүүлбэр', 'textarea', false, 'ja'], ['example_reading', 'Жишээ өгүүлбэрийн уншлага', 'textarea', false, 'ja'],
                    ['example_romaji', 'Жишээ өгүүлбэрийн ромажи', 'textarea', false, 'ja-Latn'], ['example_mn', 'Жишээ өгүүлбэрийн орчуулга', 'textarea', false], $position,
                ],
            ],
            'kanji' => [
                'label' => 'Ханз', 'single' => 'ханз', 'model' => Kanji::class, 'title' => 'kanji', 'hasStatus' => true,
                'columns' => ['kanji' => 'Ханз', 'reading' => 'Уншлага', 'meaning_mn' => 'Утга'],
                'fields' => [
                    $level, ['kanji', 'Ханз', 'text', true, 'ja'], ['onyomi', 'Онёми', 'text', false, 'ja'], ['kunyomi', 'Күнёми', 'text', false, 'ja'],
                    ['reading', 'Хирагана уншлага', 'text', true, 'ja'], ['romaji', 'Ромажи', 'text', true, 'ja-Latn'], ['meaning_mn', 'Монгол утга', 'text', true],
                    ['example_word', 'Жишээ үг', 'text', false, 'ja'], ['example_reading', 'Жишээ үгийн уншлага', 'text', false, 'ja'], ['example_mn', 'Жишээ үгийн орчуулга', 'text', false],
                    ['example_sentence', 'Жишээ өгүүлбэр', 'textarea', false, 'ja'], ['example_sentence_reading', 'Өгүүлбэрийн уншлага', 'textarea', false, 'ja'],
                    ['example_sentence_mn', 'Өгүүлбэрийн орчуулга', 'textarea', false], $position,
                ],
            ],
            'grammar' => [
                'label' => 'Дүрэм', 'single' => 'дүрэм', 'model' => Grammar::class, 'title' => 'title', 'hasStatus' => true, 'examples' => 'grammar_id',
                'columns' => ['title' => 'Дүрэм', 'structure' => 'Бүтэц'],
                'fields' => [$level, ['title', 'Дүрмийн нэр', 'text', true, 'ja'], ['structure', 'Дүрмийн бүтэц', 'text', true], ['explanation_mn', 'Монгол тайлбар', 'textarea', true], ['usage_mn', 'Хэрэглэх нөхцөл', 'textarea', false], $position],
            ],
            'lessons' => [
                'label' => 'Унших, ярианы хичээл', 'single' => 'хичээл', 'model' => Lesson::class, 'title' => 'title', 'hasStatus' => true, 'examples' => 'lesson_id',
                'columns' => ['title' => 'Гарчиг', 'type' => 'Төрөл'],
                'fields' => [$level, ['type', 'Төрөл', 'lesson_type', true], ['title', 'Гарчиг', 'text', true], ['intro_mn', 'Танилцуулга', 'textarea', false], $position],
            ],
            'audio' => [
                'label' => 'Сонсох дасгал', 'single' => 'сонсох дасгал', 'model' => Audio::class, 'title' => 'title', 'hasStatus' => true,
                'columns' => ['title' => 'Гарчиг', 'original_name' => 'Аудио файл'],
                'fields' => [$level, ['title', 'Гарчиг', 'text', true], ['transcript_ja', 'Япон бичвэр', 'textarea', true, 'ja'], ['reading', 'Хирагана уншлага', 'textarea', true, 'ja'], ['romaji', 'Ромажи', 'textarea', true, 'ja-Latn'], ['translation_mn', 'Монгол орчуулга', 'textarea', true], $position],
            ],
            'quizzes' => [
                'label' => 'Тест, дасгал', 'single' => 'тест', 'model' => Quiz::class, 'title' => 'title', 'hasStatus' => true,
                'columns' => ['title' => 'Гарчиг', 'type' => 'Төрөл'],
                'fields' => [$level, ['type', 'Төрөл', 'quiz_type', true], ['title', 'Гарчиг', 'text', true], ['description', 'Тайлбар', 'textarea', false], ['audio_id', 'Сонсох дасгалын аудио', 'audio', false], ['pass_percent', 'Тэнцэх хувь', 'number', true], $position],
            ],
            'examples' => [
                'label' => 'Жишээ өгүүлбэр', 'single' => 'жишээ', 'model' => Example::class, 'title' => 'ja', 'hasStatus' => false,
                'columns' => ['ja' => 'Япон', 'mn' => 'Монгол'],
                'fields' => [['speaker', 'Ярьж буй хүн', 'text', false], ['ja', 'Япон өгүүлбэр', 'textarea', true, 'ja'], ['reading', 'Хирагана уншлага', 'textarea', true, 'ja'], ['romaji', 'Ромажи', 'textarea', true, 'ja-Latn'], ['mn', 'Монгол орчуулга', 'textarea', true], $position],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function get(string $key): array
    {
        return self::all()[$key];
    }
}
