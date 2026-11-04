<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

class JapaneseText
{
    /**
     * Монгол текст доторх Япон үгсийг lang="ja"-аар тэмдэглэнэ, ингэснээр дэлгэц
     * уншигч тухайн хэсгийг Япон дуу хоолойгоор уншина. Текстийг эхлээд escape хийдэг.
     */
    public static function mark(?string $text): HtmlString
    {
        $marked = preg_replace_callback(
            '/[\p{Hiragana}\p{Katakana}\p{Han}ー～。、]+/u',
            fn (array $match) => '<span lang="ja">'.$match[0].'</span>',
            e((string) $text)
        );

        return new HtmlString($marked ?? e((string) $text));
    }
}
