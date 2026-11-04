<?php

namespace Database\Seeders;

use App\Models\Japanese\Answer;
use App\Models\Japanese\Audio;
use App\Models\Japanese\Category;
use App\Models\Japanese\Character;
use App\Models\Japanese\Example;
use App\Models\Japanese\Grammar;
use App\Models\Japanese\Kanji;
use App\Models\Japanese\Lesson;
use App\Models\Japanese\Level;
use App\Models\Japanese\Question;
use App\Models\Japanese\Quiz;
use App\Models\Japanese\Vocabulary;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * N5 модулийн анхны агуулга. Давтан ажиллуулж болно: одоо байгаа мөрийг дарж
 * бичихгүй тул админы засвар хадгалагдана.
 *
 * Хирагана, катакана нь тогтсон цагаан толгой тул нийтлэгдсэн. Бусад агуулга
 * багшаар шалгагдаагүй тул НООРОГ төлөвтэй: админ шалгаад нийтэлнэ.
 */
class JapaneseN5Seeder extends Seeder
{
    private Level $n5;

    public function run(): void
    {
        foreach ([
            ['N5', 'Анхан шат', 'Хирагана, катакана, үндсэн үг, дүрмийн анхны түвшин.'],
            ['N4', 'Суурь түвшин', null],
            ['N3', 'Дунд шат', null],
            ['N2', 'Дундаас дээш шат', null],
            ['N1', 'Ахисан шат', null],
        ] as $index => [$code, $name, $description]) {
            Level::firstOrCreate(['code' => $code], [
                'name' => $name,
                'description' => $description,
                'position' => $index + 1,
                'status' => $code === 'N5' ? 'published' : 'draft',
            ]);
        }

        $this->n5 = Level::where('code', 'N5')->firstOrFail();

        $this->seedKana();
        $this->seedVocabulary();
        $this->seedKanji();
        $this->seedGrammar();
        $this->seedLessons();
        $this->seedListening();
        $this->seedQuizzes();
    }

    private function seedKana(): void
    {
        // [хирагана, ромажи, монгол дуудлага, эгнээ, жишээ үг, уншлага, ромажи, монгол]
        $hiragana = [
            ['あ', 'a', 'а', 'А эгнээ', '雨', 'あめ', 'ame', 'бороо'],
            ['い', 'i', 'и', 'А эгнээ', '犬', 'いぬ', 'inu', 'нохой'],
            ['う', 'u', 'у', 'А эгнээ', '海', 'うみ', 'umi', 'далай'],
            ['え', 'e', 'э', 'А эгнээ', '駅', 'えき', 'eki', 'галт тэрэгний буудал'],
            ['お', 'o', 'о', 'А эгнээ', 'お金', 'おかね', 'okane', 'мөнгө'],
            ['か', 'ka', 'ка', 'Ка эгнээ', '傘', 'かさ', 'kasa', 'шүхэр'],
            ['き', 'ki', 'ки', 'Ка эгнээ', '切手', 'きって', 'kitte', 'шуудангийн марк'],
            ['く', 'ku', 'ку', 'Ка эгнээ', '車', 'くるま', 'kuruma', 'машин'],
            ['け', 'ke', 'кэ', 'Ка эгнээ', '今朝', 'けさ', 'kesa', 'өнөө өглөө'],
            ['こ', 'ko', 'ко', 'Ка эгнээ', '子供', 'こども', 'kodomo', 'хүүхэд'],
            ['さ', 'sa', 'са', 'Са эгнээ', '魚', 'さかな', 'sakana', 'загас'],
            ['し', 'shi', 'ши', 'Са эгнээ', '塩', 'しお', 'shio', 'давс'],
            ['す', 'su', 'су', 'Са эгнээ', '寿司', 'すし', 'sushi', 'суши'],
            ['せ', 'se', 'сэ', 'Са эгнээ', '先生', 'せんせい', 'sensei', 'багш'],
            ['そ', 'so', 'со', 'Са эгнээ', '空', 'そら', 'sora', 'тэнгэр'],
            ['た', 'ta', 'та', 'Та эгнээ', '卵', 'たまご', 'tamago', 'өндөг'],
            ['ち', 'chi', 'чи', 'Та эгнээ', '地図', 'ちず', 'chizu', 'газрын зураг'],
            ['つ', 'tsu', 'цу', 'Та эгнээ', '月', 'つき', 'tsuki', 'сар'],
            ['て', 'te', 'тэ', 'Та эгнээ', '手', 'て', 'te', 'гар'],
            ['と', 'to', 'то', 'Та эгнээ', '鳥', 'とり', 'tori', 'шувуу'],
            ['な', 'na', 'на', 'На эгнээ', '夏', 'なつ', 'natsu', 'зун'],
            ['に', 'ni', 'ни', 'На эгнээ', '肉', 'にく', 'niku', 'мах'],
            ['ぬ', 'nu', 'ну', 'На эгнээ', '布', 'ぬの', 'nuno', 'даавуу'],
            ['ね', 'ne', 'нэ', 'На эгнээ', '猫', 'ねこ', 'neko', 'муур'],
            ['の', 'no', 'но', 'На эгнээ', '飲み物', 'のみもの', 'nomimono', 'ундаа'],
            ['は', 'ha', 'ха', 'Ха эгнээ', '花', 'はな', 'hana', 'цэцэг'],
            ['ひ', 'hi', 'хи', 'Ха эгнээ', '人', 'ひと', 'hito', 'хүн'],
            ['ふ', 'fu', 'фу', 'Ха эгнээ', '冬', 'ふゆ', 'fuyu', 'өвөл'],
            ['へ', 'he', 'хэ', 'Ха эгнээ', '部屋', 'へや', 'heya', 'өрөө'],
            ['ほ', 'ho', 'хо', 'Ха эгнээ', '本', 'ほん', 'hon', 'ном'],
            ['ま', 'ma', 'ма', 'Ма эгнээ', '窓', 'まど', 'mado', 'цонх'],
            ['み', 'mi', 'ми', 'Ма эгнээ', '水', 'みず', 'mizu', 'ус'],
            ['む', 'mu', 'му', 'Ма эгнээ', '虫', 'むし', 'mushi', 'хорхой'],
            ['め', 'me', 'мэ', 'Ма эгнээ', '目', 'め', 'me', 'нүд'],
            ['も', 'mo', 'мо', 'Ма эгнээ', '森', 'もり', 'mori', 'ой'],
            ['や', 'ya', 'я', 'Я эгнээ', '山', 'やま', 'yama', 'уул'],
            ['ゆ', 'yu', 'ю', 'Я эгнээ', '雪', 'ゆき', 'yuki', 'цас'],
            ['よ', 'yo', 'ё', 'Я эгнээ', '夜', 'よる', 'yoru', 'шөнө'],
            ['ら', 'ra', 'ра', 'Ра эгнээ', '来年', 'らいねん', 'rainen', 'ирэх жил'],
            ['り', 'ri', 'ри', 'Ра эгнээ', '林檎', 'りんご', 'ringo', 'алим'],
            ['る', 'ru', 'ру', 'Ра эгнээ', '留守', 'るす', 'rusu', 'гэртээ байхгүй'],
            ['れ', 're', 'рэ', 'Ра эгнээ', '歴史', 'れきし', 'rekishi', 'түүх'],
            ['ろ', 'ro', 'ро', 'Ра эгнээ', '六', 'ろく', 'roku', 'зургаа'],
            ['わ', 'wa', 'ва', 'Ва эгнээ', '私', 'わたし', 'watashi', 'би'],
            ['を', 'wo', 'о', 'Ва эгнээ', null, null, null, null],
            ['ん', 'n', 'н', 'Ва эгнээ', '蜜柑', 'みかん', 'mikan', 'жүрж'],
            ['が', 'ga', 'га', 'Га эгнээ', null, null, null, null],
            ['ぎ', 'gi', 'ги', 'Га эгнээ', null, null, null, null],
            ['ぐ', 'gu', 'гу', 'Га эгнээ', null, null, null, null],
            ['げ', 'ge', 'гэ', 'Га эгнээ', null, null, null, null],
            ['ご', 'go', 'го', 'Га эгнээ', null, null, null, null],
            ['ざ', 'za', 'за', 'За эгнээ', null, null, null, null],
            ['じ', 'ji', 'жи', 'За эгнээ', null, null, null, null],
            ['ず', 'zu', 'зу', 'За эгнээ', null, null, null, null],
            ['ぜ', 'ze', 'зэ', 'За эгнээ', null, null, null, null],
            ['ぞ', 'zo', 'зо', 'За эгнээ', null, null, null, null],
            ['だ', 'da', 'да', 'Да эгнээ', null, null, null, null],
            ['ぢ', 'ji', 'жи', 'Да эгнээ', null, null, null, null],
            ['づ', 'zu', 'зу', 'Да эгнээ', null, null, null, null],
            ['で', 'de', 'дэ', 'Да эгнээ', null, null, null, null],
            ['ど', 'do', 'до', 'Да эгнээ', null, null, null, null],
            ['ば', 'ba', 'ба', 'Ба эгнээ', null, null, null, null],
            ['び', 'bi', 'би', 'Ба эгнээ', null, null, null, null],
            ['ぶ', 'bu', 'бу', 'Ба эгнээ', null, null, null, null],
            ['べ', 'be', 'бэ', 'Ба эгнээ', null, null, null, null],
            ['ぼ', 'bo', 'бо', 'Ба эгнээ', null, null, null, null],
            ['ぱ', 'pa', 'па', 'Па эгнээ', null, null, null, null],
            ['ぴ', 'pi', 'пи', 'Па эгнээ', null, null, null, null],
            ['ぷ', 'pu', 'пу', 'Па эгнээ', null, null, null, null],
            ['ぺ', 'pe', 'пэ', 'Па эгнээ', null, null, null, null],
            ['ぽ', 'po', 'по', 'Па эгнээ', null, null, null, null],
        ];

        // Катакана жишээ: гадаад гаралтай үгс, ханзгүй.
        $katakanaExamples = [
            'ア' => ['アメリカ', 'amerika', 'Америк'], 'イ' => ['インド', 'indo', 'Энэтхэг'], 'ウ' => ['ウイスキー', 'uisukii', 'виски'],
            'エ' => ['エアコン', 'eakon', 'агааржуулагч'], 'オ' => ['オレンジ', 'orenji', 'жүрж'], 'カ' => ['カメラ', 'kamera', 'камер'],
            'キ' => ['キロ', 'kiro', 'килограмм, километр'], 'ク' => ['クラス', 'kurasu', 'анги'], 'ケ' => ['ケーキ', 'keeki', 'бялуу'],
            'コ' => ['コーヒー', 'koohii', 'кофе'], 'サ' => ['サラダ', 'sarada', 'салат'], 'シ' => ['シャツ', 'shatsu', 'цамц'],
            'ス' => ['スプーン', 'supuun', 'халбага'], 'セ' => ['セーター', 'seetaa', 'ноосон цамц'], 'ソ' => ['ソファー', 'sofaa', 'буйдан'],
            'タ' => ['タクシー', 'takushii', 'такси'], 'チ' => ['チーズ', 'chiizu', 'бяслаг'], 'ツ' => ['ツアー', 'tsuaa', 'аялал'],
            'テ' => ['テレビ', 'terebi', 'зурагт'], 'ト' => ['トマト', 'tomato', 'улаан лооль'], 'ナ' => ['ナイフ', 'naifu', 'хутга'],
            'ニ' => ['ニュース', 'nyuusu', 'мэдээ'], 'ヌ' => ['ヌードル', 'nuudoru', 'гоймон'], 'ネ' => ['ネクタイ', 'nekutai', 'зангиа'],
            'ノ' => ['ノート', 'nooto', 'дэвтэр'], 'ハ' => ['ハンバーガー', 'hanbaagaa', 'гамбургер'], 'ヒ' => ['ヒーター', 'hiitaa', 'халаагуур'],
            'フ' => ['フォーク', 'fooku', 'сэрээ'], 'ヘ' => ['ヘリコプター', 'herikoputaa', 'нисдэг тэрэг'], 'ホ' => ['ホテル', 'hoteru', 'зочид буудал'],
            'マ' => ['マスク', 'masuku', 'амны хаалт'], 'ミ' => ['ミルク', 'miruku', 'сүү'], 'メ' => ['メール', 'meeru', 'и-мэйл'],
            'モ' => ['モニター', 'monitaa', 'дэлгэц'], 'ユ' => ['ユニホーム', 'yunihoomu', 'дүрэмт хувцас'], 'ヨ' => ['ヨーグルト', 'yooguruto', 'тараг'],
            'ラ' => ['ラジオ', 'rajio', 'радио'], 'リ' => ['リモコン', 'rimokon', 'алсын удирдлага'], 'ル' => ['ルール', 'ruuru', 'дүрэм'],
            'レ' => ['レストラン', 'resutoran', 'ресторан'], 'ロ' => ['ロボット', 'robotto', 'робот'], 'ワ' => ['ワイン', 'wain', 'дарс'],
            'ン' => ['パン', 'pan', 'талх'],
        ];

        foreach ($hiragana as $index => [$char, $romaji, $mn, $row, $word, $reading, $wordRomaji, $wordMn]) {
            Character::firstOrCreate(['script' => 'hiragana', 'character' => $char], [
                'level_id' => $this->n5->id, 'romaji' => $romaji, 'reading_mn' => $mn, 'row_name' => $row, 'position' => $index + 1,
                'example_word' => $word, 'example_reading' => $reading, 'example_romaji' => $wordRomaji, 'example_mn' => $wordMn,
                'status' => 'published',
            ]);

            $katakana = mb_convert_kana($char, 'C', 'UTF-8');
            $example = $katakanaExamples[$katakana] ?? null;

            Character::firstOrCreate(['script' => 'katakana', 'character' => $katakana], [
                'level_id' => $this->n5->id, 'romaji' => $romaji, 'reading_mn' => $mn, 'row_name' => $row, 'position' => $index + 1,
                'example_word' => $example[0] ?? null, 'example_reading' => $example[0] ?? null,
                'example_romaji' => $example[1] ?? null, 'example_mn' => $example[2] ?? null,
                'status' => 'published',
            ]);
        }
    }

    private function seedVocabulary(): void
    {
        $themes = [
            'Мэндчилгээ' => [
                ['おはようございます', 'おはようございます', 'ohayou gozaimasu', 'Өглөөний мэнд'],
                ['こんにちは', 'こんにちは', 'konnichiwa', 'Сайн байна уу (өдрийн мэндчилгээ)'],
                ['こんばんは', 'こんばんは', 'konbanwa', 'Оройн мэнд'],
                ['さようなら', 'さようなら', 'sayounara', 'Баяртай'],
                ['ありがとうございます', 'ありがとうございます', 'arigatou gozaimasu', 'Баярлалаа'],
                ['すみません', 'すみません', 'sumimasen', 'Уучлаарай'],
                ['はじめまして', 'はじめまして', 'hajimemashite', 'Танилцахад таатай байна'],
                ['お願いします', 'おねがいします', 'onegai shimasu', 'Гуйя'],
            ],
            'Гэр бүл' => [
                ['家族', 'かぞく', 'kazoku', 'Гэр бүл', 'わたしの かぞくは よにんです。', 'わたしの かぞくは よにんです。', 'Watashi no kazoku wa yonin desu.', 'Манай гэр бүл дөрвөн хүнтэй.'],
                ['父', 'ちち', 'chichi', 'Аав (өөрийн)'],
                ['母', 'はは', 'haha', 'Ээж (өөрийн)'],
                ['兄', 'あに', 'ani', 'Ах (өөрийн)'],
                ['姉', 'あね', 'ane', 'Эгч (өөрийн)'],
                ['弟', 'おとうと', 'otouto', 'Эрэгтэй дүү'],
                ['妹', 'いもうと', 'imouto', 'Эмэгтэй дүү'],
                ['子供', 'こども', 'kodomo', 'Хүүхэд'],
            ],
            'Сургууль' => [
                ['私', 'わたし', 'watashi', 'Би', '私は学生です。', 'わたしは がくせいです。', 'Watashi wa gakusei desu.', 'Би оюутан.'],
                ['学生', 'がくせい', 'gakusei', 'Оюутан', '私は学生です。', 'わたしは がくせいです。', 'Watashi wa gakusei desu.', 'Би оюутан.'],
                ['学校', 'がっこう', 'gakkou', 'Сургууль', '学校へ行きます。', 'がっこうへ いきます。', 'Gakkou e ikimasu.', 'Сургууль руу явна.'],
                ['先生', 'せんせい', 'sensei', 'Багш', '田中さんは先生です。', 'たなかさんは せんせいです。', 'Tanaka-san wa sensei desu.', 'Танака багш.'],
                ['友達', 'ともだち', 'tomodachi', 'Найз', '友達と話します。', 'ともだちと はなします。', 'Tomodachi to hanashimasu.', 'Найзтайгаа ярилцана.'],
                ['本', 'ほん', 'hon', 'Ном', '本を読みます。', 'ほんを よみます。', 'Hon o yomimasu.', 'Ном уншина.'],
                ['教室', 'きょうしつ', 'kyoushitsu', 'Анги танхим'],
                ['勉強', 'べんきょう', 'benkyou', 'Хичээл, суралцах', '日本語を勉強します。', 'にほんごを べんきょうします。', 'Nihongo o benkyou shimasu.', 'Япон хэл сурна.'],
            ],
            'Хоол хүнс' => [
                ['ご飯', 'ごはん', 'gohan', 'Будаа, хоол', 'ご飯を食べます。', 'ごはんを たべます。', 'Gohan o tabemasu.', 'Хоол иднэ.'],
                ['水', 'みず', 'mizu', 'Ус', '水を飲みます。', 'みずを のみます。', 'Mizu o nomimasu.', 'Ус ууна.'],
                ['お茶', 'おちゃ', 'ocha', 'Цай'],
                ['魚', 'さかな', 'sakana', 'Загас'],
                ['肉', 'にく', 'niku', 'Мах'],
                ['卵', 'たまご', 'tamago', 'Өндөг'],
                ['林檎', 'りんご', 'ringo', 'Алим'],
                ['パン', 'ぱん', 'pan', 'Талх', null, null, null, null, 'パン'],
            ],
            'Цаг хугацаа' => [
                ['今日', 'きょう', 'kyou', 'Өнөөдөр', '今日は月曜日です。', 'きょうは げつようびです。', 'Kyou wa getsuyoubi desu.', 'Өнөөдөр даваа гараг.'],
                ['明日', 'あした', 'ashita', 'Маргааш'],
                ['昨日', 'きのう', 'kinou', 'Өчигдөр'],
                ['今', 'いま', 'ima', 'Одоо'],
                ['朝', 'あさ', 'asa', 'Өглөө'],
                ['晩', 'ばん', 'ban', 'Орой'],
                ['月曜日', 'げつようび', 'getsuyoubi', 'Даваа гараг'],
                ['時間', 'じかん', 'jikan', 'Цаг хугацаа'],
            ],
            'Тоо' => [
                ['一', 'いち', 'ichi', 'Нэг'], ['二', 'に', 'ni', 'Хоёр'], ['三', 'さん', 'san', 'Гурав'], ['四', 'よん', 'yon', 'Дөрөв'],
                ['五', 'ご', 'go', 'Тав'], ['六', 'ろく', 'roku', 'Зургаа'], ['七', 'なな', 'nana', 'Долоо'], ['八', 'はち', 'hachi', 'Найм'],
                ['九', 'きゅう', 'kyuu', 'Ес'], ['十', 'じゅう', 'juu', 'Арав'],
            ],
            'Өнгө' => [
                ['赤', 'あか', 'aka', 'Улаан'], ['青', 'あお', 'ao', 'Цэнхэр'], ['白', 'しろ', 'shiro', 'Цагаан'],
                ['黒', 'くろ', 'kuro', 'Хар'], ['黄色', 'きいろ', 'kiiro', 'Шар'], ['緑', 'みどり', 'midori', 'Ногоон'],
            ],
            'Ажил мэргэжил' => [
                ['医者', 'いしゃ', 'isha', 'Эмч'], ['会社員', 'かいしゃいん', 'kaishain', 'Компанийн ажилтан'], ['店員', 'てんいん', 'ten\'in', 'Дэлгүүрийн худалдагч'],
                ['銀行員', 'ぎんこういん', 'ginkouin', 'Банкны ажилтан'], ['警察官', 'けいさつかん', 'keisatsukan', 'Цагдаа'], ['看護師', 'かんごし', 'kangoshi', 'Сувилагч'],
            ],
            'Тээвэр' => [
                ['電車', 'でんしゃ', 'densha', 'Галт тэрэг', '電車で行きます。', 'でんしゃで いきます。', 'Densha de ikimasu.', 'Галт тэргээр явна.'],
                ['バス', 'ばす', 'basu', 'Автобус', null, null, null, null, 'バス'],
                ['車', 'くるま', 'kuruma', 'Машин'],
                ['自転車', 'じてんしゃ', 'jitensha', 'Унадаг дугуй'],
                ['飛行機', 'ひこうき', 'hikouki', 'Онгоц'],
                ['駅', 'えき', 'eki', 'Галт тэрэгний буудал'],
                ['地下鉄', 'ちかてつ', 'chikatetsu', 'Метро'],
                ['タクシー', 'たくしー', 'takushii', 'Такси', null, null, null, null, 'タクシー'],
            ],
            'Өдөр тутмын амьдрал' => [
                ['家', 'いえ', 'ie', 'Гэр, байшин'], ['部屋', 'へや', 'heya', 'Өрөө'], ['電話', 'でんわ', 'denwa', 'Утас'],
                ['新聞', 'しんぶん', 'shinbun', 'Сонин'], ['服', 'ふく', 'fuku', 'Хувцас'], ['傘', 'かさ', 'kasa', 'Шүхэр'],
                ['お金', 'おかね', 'okane', 'Мөнгө'], ['買い物', 'かいもの', 'kaimono', 'Дэлгүүр хэсэх'],
            ],
        ];

        $themeIndex = 0;

        foreach ($themes as $name => $words) {
            $themeIndex++;
            $category = Category::firstOrCreate(['name' => $name], ['slug' => 'theme-'.$themeIndex, 'position' => $themeIndex]);

            foreach ($words as $index => $row) {
                $row = array_pad($row, 9, null);

                Vocabulary::firstOrCreate(
                    ['level_id' => $this->n5->id, 'word' => $row[0], 'reading' => $row[1]],
                    [
                        'category_id' => $category->id, 'romaji' => $row[2], 'meaning_mn' => $row[3],
                        'example_ja' => $row[4], 'example_reading' => $row[5], 'example_romaji' => $row[6], 'example_mn' => $row[7],
                        'katakana' => $row[8], 'position' => $index + 1, 'status' => 'draft',
                    ]
                );
            }
        }
    }

    private function seedKanji(): void
    {
        $rows = [
            ['日', 'ニチ、ジツ', 'ひ、か', 'にち', 'nichi', 'Өдөр, нар', '日本', 'にほん', 'Япон', '日本へ行きます。', 'にほんへ いきます。', 'Япон руу явна.'],
            ['月', 'ゲツ、ガツ', 'つき', 'つき', 'tsuki', 'Сар', '月曜日', 'げつようび', 'Даваа гараг', null, null, null],
            ['火', 'カ', 'ひ', 'ひ', 'hi', 'Гал', '火曜日', 'かようび', 'Мягмар гараг', null, null, null],
            ['水', 'スイ', 'みず', 'みず', 'mizu', 'Ус', '水曜日', 'すいようび', 'Лхагва гараг', '水を飲みます。', 'みずを のみます。', 'Ус ууна.'],
            ['木', 'モク、ボク', 'き', 'き', 'ki', 'Мод', '木曜日', 'もくようび', 'Пүрэв гараг', null, null, null],
            ['金', 'キン、コン', 'かね', 'かね', 'kane', 'Алт, мөнгө', '金曜日', 'きんようび', 'Баасан гараг', null, null, null],
            ['土', 'ド、ト', 'つち', 'つち', 'tsuchi', 'Шороо', '土曜日', 'どようび', 'Бямба гараг', null, null, null],
            ['山', 'サン', 'やま', 'やま', 'yama', 'Уул', '富士山', 'ふじさん', 'Фүжи уул', null, null, null],
            ['川', 'セン', 'かわ', 'かわ', 'kawa', 'Гол', null, null, null, null, null, null],
            ['人', 'ジン、ニン', 'ひと', 'ひと', 'hito', 'Хүн', '日本人', 'にほんじん', 'Япон хүн', null, null, null],
            ['学', 'ガク', 'まな(ぶ)', 'がく', 'gaku', 'Суралцах', '学校', 'がっこう', 'Сургууль', '学校へ行きます。', 'がっこうへ いきます。', 'Сургууль руу явна.'],
            ['生', 'セイ、ショウ', 'い(きる)、う(まれる)', 'せい', 'sei', 'Амьдрал, төрөх', '学生', 'がくせい', 'Оюутан', '私は学生です。', 'わたしは がくせいです。', 'Би оюутан.'],
            ['先', 'セン', 'さき', 'さき', 'saki', 'Өмнө, урьд', '先生', 'せんせい', 'Багш', null, null, null],
            ['本', 'ホン', 'もと', 'ほん', 'hon', 'Ном, үндэс', '本', 'ほん', 'Ном', '本を読みます。', 'ほんを よみます。', 'Ном уншина.'],
            ['一', 'イチ、イツ', 'ひと(つ)', 'いち', 'ichi', 'Нэг', '一人', 'ひとり', 'Нэг хүн', null, null, null],
            ['二', 'ニ', 'ふた(つ)', 'に', 'ni', 'Хоёр', '二人', 'ふたり', 'Хоёр хүн', null, null, null],
            ['三', 'サン', 'みっ(つ)', 'さん', 'san', 'Гурав', null, null, null, null, null, null],
            ['年', 'ネン', 'とし', 'ねん', 'nen', 'Жил', '来年', 'らいねん', 'Ирэх жил', null, null, null],
            ['時', 'ジ', 'とき', 'じ', 'ji', 'Цаг', '時間', 'じかん', 'Цаг хугацаа', null, null, null],
            ['名', 'メイ、ミョウ', 'な', 'な', 'na', 'Нэр', '名前', 'なまえ', 'Нэр', null, null, null],
        ];

        foreach ($rows as $index => $row) {
            Kanji::firstOrCreate(['level_id' => $this->n5->id, 'kanji' => $row[0]], [
                'onyomi' => $row[1], 'kunyomi' => $row[2], 'reading' => $row[3], 'romaji' => $row[4], 'meaning_mn' => $row[5],
                'example_word' => $row[6], 'example_reading' => $row[7], 'example_mn' => $row[8],
                'example_sentence' => $row[9], 'example_sentence_reading' => $row[10], 'example_sentence_mn' => $row[11],
                'position' => $index + 1, 'status' => 'draft',
            ]);
        }
    }

    private function seedGrammar(): void
    {
        $rows = [
            ['～です', 'Нэр үг + です', 'Ямар нэг зүйл эсвэл хүнийг тодорхойлохдоо хэрэглэнэ. Өгүүлбэрийг эелдэгээр төгсгөнө.', 'Танилцах, зүйлийг нэрлэх үед.', [
                ['わたしは がくせいです。', 'わたしは がくせいです。', 'Watashi wa gakusei desu.', 'Би оюутан.'],
                ['これは ほんです。', 'これは ほんです。', 'Kore wa hon desu.', 'Энэ бол ном.'],
            ]],
            ['～ます', 'Үйл үгийн баганын хэлбэр + ます', 'Үйл үгийн эелдэг хэлбэр. Одоо болон ирээдүйн үйлийг илэрхийлнэ.', 'Хүнтэй эелдэг ярих үед.', [
                ['わたしは がっこうへ いきます。', 'わたしは がっこうへ いきます。', 'Watashi wa gakkou e ikimasu.', 'Би сургууль руу явдаг.'],
                ['まいにち ごはんを たべます。', 'まいにち ごはんを たべます。', 'Mainichi gohan o tabemasu.', 'Өдөр бүр хоол иднэ.'],
            ]],
            ['～ません', 'Үйл үгийн баганын хэлбэр + ません', '～ます хэлбэрийн үгүйсгэл.', 'Хийдэггүй, хийхгүй үйлийг хэлэх үед.', [
                ['おさけを のみません。', 'おさけを のみません。', 'Osake o nomimasen.', 'Архи уудаггүй.'],
            ]],
            ['～ました', 'Үйл үгийн баганын хэлбэр + ました', '～ます хэлбэрийн өнгөрсөн цаг.', 'Хийсэн үйлийг хэлэх үед.', [
                ['きのう ほんを よみました。', 'きのう ほんを よみました。', 'Kinou hon o yomimashita.', 'Өчигдөр ном уншсан.'],
            ]],
            ['～か', 'Өгүүлбэр + か', 'Өгүүлбэрийн төгсгөлд нэмж асуулт болгоно.', 'Асуулт асуух үед.', [
                ['がくせいですか。', 'がくせいですか。', 'Gakusei desu ka.', 'Та оюутан уу?'],
            ]],
            ['の', 'Нэр үг + の + нэр үг', 'Эзэмшил, хамаарлыг илэрхийлнэ.', 'Хэний, юуны гэдгийг хэлэх үед.', [
                ['わたしの ほんです。', 'わたしの ほんです。', 'Watashi no hon desu.', 'Миний ном.'],
            ]],
            ['を', 'Нэр үг + を + үйл үг', 'Үйлийн тусагдахууныг заана.', 'Юуг хийж буйг хэлэх үед.', [
                ['みずを のみます。', 'みずを のみます。', 'Mizu o nomimasu.', 'Ус ууна.'],
            ]],
            ['へ', 'Газар + へ + явах үйл үг', 'Хөдөлгөөний чиглэлийг заана. へ тэмдэгтийг энд "э" гэж уншина.', 'Хаашаа явж буйг хэлэх үед.', [
                ['にほんへ いきます。', 'にほんへ いきます。', 'Nihon e ikimasu.', 'Япон руу явна.'],
            ]],
            ['で', 'Газар эсвэл хэрэгсэл + で', 'Үйл болох газар эсвэл хэрэгслийг заана.', 'Хаана, юугаар хийж буйг хэлэх үед.', [
                ['がっこうで べんきょうします。', 'がっこうで べんきょうします。', 'Gakkou de benkyou shimasu.', 'Сургууль дээр хичээллэнэ.'],
                ['でんしゃで いきます。', 'でんしゃで いきます。', 'Densha de ikimasu.', 'Галт тэргээр явна.'],
            ]],
            ['～たいです', 'Үйл үгийн баганын хэлбэр + たいです', 'Өөрийн хүслийг илэрхийлнэ.', 'Хийхийг хүсэж буйгаа хэлэх үед.', [
                ['にほんへ いきたいです。', 'にほんへ いきたいです。', 'Nihon e ikitai desu.', 'Япон руу явмаар байна.'],
            ]],
        ];

        foreach ($rows as $index => [$title, $structure, $explanation, $usage, $examples]) {
            $grammar = Grammar::firstOrCreate(['level_id' => $this->n5->id, 'title' => $title], [
                'structure' => $structure, 'explanation_mn' => $explanation, 'usage_mn' => $usage,
                'position' => $index + 1, 'status' => 'draft',
            ]);

            if ($grammar->wasRecentlyCreated) {
                foreach ($examples as $position => [$ja, $reading, $romaji, $mn]) {
                    Example::create(['grammar_id' => $grammar->id, 'ja' => $ja, 'reading' => $reading, 'romaji' => $romaji, 'mn' => $mn, 'position' => $position + 1]);
                }
            }
        }
    }

    private function seedLessons(): void
    {
        $rows = [
            ['reading', 'Миний гэр бүл', 'Гэр бүлийн тухай богино эх уншина.', [
                [null, 'わたしの かぞくは よにんです。', 'わたしの かぞくは よにんです。', 'Watashi no kazoku wa yonin desu.', 'Манай гэр бүл дөрвөн хүнтэй.'],
                [null, 'ちちは いしゃです。', 'ちちは いしゃです。', 'Chichi wa isha desu.', 'Аав маань эмч.'],
                [null, 'わたしは がくせいです。', 'わたしは がくせいです。', 'Watashi wa gakusei desu.', 'Би оюутан.'],
            ]],
            ['conversation', 'Анх танилцах', 'Хоёр хүн анх танилцаж байна.', [
                ['А', 'はじめまして。バトです。', 'はじめまして。ばとです。', 'Hajimemashite. Bato desu.', 'Танилцахад таатай байна. Би Бат.'],
                ['Б', 'はじめまして。たなかです。', 'はじめまして。たなかです。', 'Hajimemashite. Tanaka desu.', 'Танилцахад таатай байна. Би Танака.'],
                ['А', 'がくせいですか。', 'がくせいですか。', 'Gakusei desu ka.', 'Та оюутан уу?'],
                ['Б', 'はい、がくせいです。', 'はい、がくせいです。', 'Hai, gakusei desu.', 'Тийм, оюутан.'],
            ]],
            ['daily', 'Дэлгүүрт', 'Дэлгүүрт юм авах богино яриа.', [
                ['Худалдан авагч', 'すみません、これは いくらですか。', 'すみません、これは いくらですか。', 'Sumimasen, kore wa ikura desu ka.', 'Уучлаарай, энэ хэд вэ?'],
                ['Худалдагч', 'ひゃくえんです。', 'ひゃくえんです。', 'Hyaku en desu.', 'Зуун иен.'],
                ['Худалдан авагч', 'これを おねがいします。', 'これを おねがいします。', 'Kore o onegai shimasu.', 'Үүнийг авъя.'],
            ]],
        ];

        foreach ($rows as $index => [$type, $title, $intro, $lines]) {
            $lesson = Lesson::firstOrCreate(['level_id' => $this->n5->id, 'type' => $type, 'title' => $title], [
                'intro_mn' => $intro, 'position' => $index + 1, 'status' => 'draft',
            ]);

            if ($lesson->wasRecentlyCreated) {
                foreach ($lines as $position => [$speaker, $ja, $reading, $romaji, $mn]) {
                    Example::create(['lesson_id' => $lesson->id, 'speaker' => $speaker, 'ja' => $ja, 'reading' => $reading, 'romaji' => $romaji, 'mn' => $mn, 'position' => $position + 1]);
                }
            }
        }
    }

    private function seedListening(): void
    {
        // Аудио файлгүй: админ бодит бичлэг оруулах хүртэл нийтлэгдэх боломжгүй.
        Audio::firstOrCreate(['level_id' => $this->n5->id, 'title' => 'Өөрийгөө танилцуулах'], [
            'transcript_ja' => 'はじめまして。わたしは バトです。モンゴルから きました。',
            'reading' => 'はじめまして。わたしは ばとです。もんごるから きました。',
            'romaji' => 'Hajimemashite. Watashi wa Bato desu. Mongoru kara kimashita.',
            'translation_mn' => 'Танилцахад таатай байна. Би Бат. Монголоос ирсэн.',
            'position' => 1,
            'status' => 'draft',
        ]);
    }

    private function seedQuizzes(): void
    {
        $this->kanaQuiz('hiragana', 'Хирагана таних', 'published');
        $this->kanaQuiz('katakana', 'Катакана таних', 'published');

        $this->quiz('vocabulary', 'Үгийн сан: сургууль', [
            ['choice', 'Энэ үгийн утга юу вэ?', '学校', 'がっこう гэж уншдаг бөгөөд сургууль гэсэн утгатай.', [['Сургууль', 'mn', true], ['Багш', 'mn'], ['Найз', 'mn'], ['Ном', 'mn']]],
            ['choice', 'Энэ үгийн утга юу вэ?', '先生', 'せんせい гэж уншдаг бөгөөд багш гэсэн утгатай.', [['Багш', 'mn', true], ['Оюутан', 'mn'], ['Сургууль', 'mn'], ['Найз', 'mn']]],
            ['choice', '"Найз" гэдэг үг аль нь вэ?', null, '友達 (ともだち) нь найз гэсэн утгатай.', [['友達', 'ja', true], ['学生', 'ja'], ['先生', 'ja'], ['学校', 'ja']]],
            ['match', 'Япон үгийг Монгол орчуулгатай нь тааруулна уу.', null, '私 нь би, 学生 нь оюутан, 本 нь ном гэсэн утгатай.', [['私', 'Би'], ['学生', 'Оюутан'], ['本', 'Ном']]],
        ]);

        $this->quiz('grammar', 'Дүрэм: үндсэн хэлбэрүүд', [
            ['choice', 'Өгүүлбэрийг зөв нөхнө үү.', 'わたしは がくせい（　）。', 'Нэр үгээр төгссөн эелдэг өгүүлбэрт です хэрэглэнэ.', [['です', 'ja', true], ['ます', 'ja'], ['を', 'ja'], ['へ', 'ja']]],
            ['choice', 'Өгүүлбэрийг зөв нөхнө үү.', 'みず（　）のみます。', 'Үйлийн тусагдахууныг を заана.', [['を', 'ja', true], ['へ', 'ja'], ['の', 'ja'], ['か', 'ja']]],
            ['choice', 'Өгүүлбэрийг зөв нөхнө үү.', 'がっこう（　）いきます。', 'Явах чиглэлийг へ заана.', [['へ', 'ja', true], ['を', 'ja'], ['か', 'ja'], ['の', 'ja']]],
            ['order', 'Хэсгүүдийг зөв дараалалд оруулж "Би оюутан." гэсэн өгүүлбэр үүсгэнэ үү.', null, 'Зөв өгүүлбэр: わたしは がくせいです。', [['わたしは'], ['がくせい'], ['です']]],
        ]);

        $this->quiz('kanji', 'Ханзны утга', [
            ['choice', 'Энэ ханзны утга юу вэ?', '山', '山 нь уул гэсэн утгатай.', [['Уул', 'mn', true], ['Гол', 'mn'], ['Мод', 'mn'], ['Ус', 'mn']]],
            ['choice', 'Энэ ханзны утга юу вэ?', '水', '水 нь ус гэсэн утгатай.', [['Ус', 'mn', true], ['Гал', 'mn'], ['Шороо', 'mn'], ['Уул', 'mn']]],
            ['choice', 'Энэ ханзны утга юу вэ?', '人', '人 нь хүн гэсэн утгатай.', [['Хүн', 'mn', true], ['Сар', 'mn'], ['Ном', 'mn'], ['Нэр', 'mn']]],
        ]);

        $this->quiz('jlpt', 'JLPT N5 бэлтгэл: жишиг дасгал', [
            ['choice', 'Зөв уншлагыг сонгоно уу.', '学生', '学生 нь がくせい гэж уншигдана.', [['がくせい', 'ja', true], ['がっこう', 'ja'], ['せんせい', 'ja'], ['ともだち', 'ja']]],
            ['choice', 'Зөв уншлагыг сонгоно уу.', '先生', '先生 нь せんせい гэж уншигдана.', [['せんせい', 'ja', true], ['がくせい', 'ja'], ['さきせい', 'ja'], ['せんしょう', 'ja']]],
            ['choice', 'Өгүүлбэрийг зөв нөхнө үү.', 'これは わたし（　）ほんです。', 'Эзэмшлийг の илэрхийлнэ.', [['の', 'ja', true], ['を', 'ja'], ['へ', 'ja'], ['で', 'ja']]],
        ]);
    }

    private function kanaQuiz(string $script, string $title, string $status): void
    {
        $characters = Character::where('script', $script)->orderBy('position')->limit(46)->get();
        $questions = [];

        // Эгнээ бүрээс тэнцвэртэй сонгоно. Хариултууд тогтмол тул давтан ажиллуулахад ижил.
        foreach ($characters->values()->filter(fn ($c, $i) => $i % 4 === 0) as $character) {
            $wrong = $characters
                ->where('romaji', '!=', $character->romaji)
                ->sortBy(fn ($c) => crc32($character->character.$c->character))
                ->take(3)
                ->map(fn ($c) => [$c->romaji, 'ja-Latn']);

            $questions[] = [
                'choice',
                'Энэ үсгийн дуудлага аль нь вэ?',
                $character->character,
                "{$character->character} үсгийг {$character->romaji} ({$character->reading_mn}) гэж уншина.",
                array_merge([[$character->romaji, 'ja-Latn', true]], $wrong->values()->all()),
            ];
        }

        $this->quiz($script, $title, $questions, $status);
    }

    /**
     * @param  array<int, array<int, mixed>>  $questions
     */
    private function quiz(string $type, string $title, array $questions, string $status = 'draft'): void
    {
        $quiz = Quiz::firstOrCreate(['level_id' => $this->n5->id, 'title' => $title], [
            'type' => $type,
            'description' => 'N5 түвшний дасгал.',
            'position' => Quiz::where('level_id', $this->n5->id)->count() + 1,
            'status' => $status,
        ]);

        if (! $quiz->wasRecentlyCreated) {
            return;
        }

        foreach ($questions as $position => [$kind, $prompt, $promptJa, $explanation, $answers]) {
            $question = Question::create([
                'quiz_id' => $quiz->id, 'kind' => $kind, 'prompt' => $prompt, 'prompt_ja' => $promptJa,
                'explanation_mn' => $explanation, 'points' => 1, 'position' => $position + 1,
            ]);

            foreach ($answers as $answerPosition => $answer) {
                Answer::create(match ($kind) {
                    'match' => ['question_id' => $question->id, 'text' => $answer[0], 'lang' => 'ja', 'match_text' => $answer[1], 'position' => $answerPosition + 1],
                    'order' => ['question_id' => $question->id, 'text' => $answer[0], 'lang' => 'ja', 'position' => $answerPosition + 1],
                    default => ['question_id' => $question->id, 'text' => $answer[0], 'lang' => $answer[1], 'is_correct' => $answer[2] ?? false, 'position' => $answerPosition + 1],
                });
            }
        }
    }
}
