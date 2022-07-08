<?php
/**
 * Утилиты для работы со строками
 */

/**
 * Проверка наличия в строке многобайтовых символов.
 *
 * @param   string  $string    Входная строка
 * @param   string  $encoding  Кодировка
 * @return  bool
 */
function hasMultiBytes(string $string, string $encoding = 'UTF-8'): bool
{
    if (function_exists('mb_strlen')) {
        return (strlen($string) > mb_strlen($string, $encoding));
    }

    return false;
}

/**
 * Содержит ли строка символы с задействованным 8-м битом (Extended ASCII)?
 *
 * @param   string  $string
 * @return  bool
 */
function has8bitChars(string $string): bool
{
    return (bool) preg_match('/[\x80-\xFF]/', $string);
}

/**
 * Получение количества байт в строке.
 *
 * @param   string  $string  Входная строка
 * @return  int
 */
function byteLength(string $string): int
{
    return mb_strlen($string, '8bit');
}

/**
 * Получение длины строки.
 *
 * @param   string  $string  Строка
 * @return  int
 */
function strLength(string $string): int
{
    return mb_strlen($string, Cfg::CHARSET);
}

/**
 * Кодирование строки для использования её в качестве части URL, например.
 *
 * @param   string  $string  Входная строка
 * @return  string
 */
function encodeString(string $string): string
{
    $replace_pairs = [
        'А' => 'a', 'Б' => 'b',  'В' => 'v', 'Г' => 'g',
        'Д' => 'd', 'Е' => 'e',  'Ж' => 'j', 'З' => 'z',  'И' => 'i',
        'Й' => 'y', 'К' => 'k',  'Л' => 'l', 'М' => 'm',  'Н' => 'n',
        'О' => 'o', 'П' => 'p',  'Р' => 'r', 'С' => 's',  'Т' => 't',
        'У' => 'u', 'Ф' => 'f',  'Х' => 'h', 'Ц' => 'ts', 'Ч' => 'ch',
        'Ш' => 'sh','Щ' => 'sch','Ъ' => '',  'Ы' => 'yi', 'Ь' => '',
        'Э' => 'e', 'Ю' => 'yu', 'Я' => 'ya','а' => 'a',  'б' => 'b',
        'в' => 'v', 'г' => 'g',  'д' => 'd', 'е' => 'e',  'ж' => 'j',
        'з' => 'z', 'и' => 'i',  'й' => 'y', 'к' => 'k',  'л' => 'l',
        'м' => 'm', 'н' => 'n',  'о' => 'o', 'п' => 'p',  'р' => 'r',
        'с' => 's', 'т' => 't',  'у' => 'u', 'ф' => 'f',  'х' => 'h',
        'ц' => 'ts','ч' => 'ch', 'ш' => 'sh','щ' => 'sch','ъ' => 'y',
        'ы' => 'yi','ь' => '',   'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
        ' ' => '_', '/' => '_'
    ];
    $string = strtr($string, $replace_pairs);
    $string = preg_replace('/[^A-Za-z0-9\-_.~]/', '', $string);

    return $string;
}

/**
 * Транслитерация строки.
 *
 * @param   string  $string  Входная строка
 * @return  string
 */
function translitIt(string $string): string
{
    $replace_pairs = [
        'А' => 'A', 'Б' => 'B',  'В' => 'V', 'Г'=> 'G',
        'Д' => 'D', 'Е' => 'E',  'Ж' => 'J', 'З'=> 'Z',  'И'=>'I',
        'Й' => 'Y', 'К' => 'K',  'Л' => 'L', 'М'=> 'M',  'Н'=>'N',
        'О' => 'O', 'П' => 'P',  'Р' => 'R', 'С'=> 'S',  'Т'=>'T',
        'У' => 'U', 'Ф' => 'F',  'Х' => 'H', 'Ц'=> 'TS', 'Ч'=>'CH',
        'Ш' => 'SH','Щ' => 'SCH','Ъ' => '',  'Ы'=> 'YI', 'Ь'=>'',
        'Э' => 'E', 'Ю' => 'YU', 'Я' => 'YA','а'=> 'a',  'б'=>'b',
        'в' => 'v', 'г' => 'g',  'д' => 'd', 'е'=> 'e',  'ж'=>'j',
        'з' => 'z', 'и' => 'i',  'й' => 'y', 'к'=> 'k',  'л'=>'l',
        'м' => 'm', 'н' => 'n',  'о' => 'o', 'п'=> 'p',  'р'=>'r',
        'с' => 's', 'т' => 't',  'у' => 'u', 'ф'=> 'f',  'х'=>'h',
        'ц' => 'ts','ч' => 'ch', 'ш' => 'sh','щ'=> 'sch','ъ'=>'y',
        'ы' => 'yi','ь' => '',   'э' => 'e', 'ю'=> 'yu', 'я'=>'ya'
    ];

    return strtr($string, $replace_pairs);
}

/**
 * Получение части строки, не превышающей заданную длину.
 *
 * Если [[$string]] превышает целевую длину,
 * а [[$suffix]] не пуст, и его удвоенная длина не превышает [[$length]],
 * то он добавляется к [[$string]] справа.
 *
 * @param   string  $string  Входная строка
 * @param   int     $length  Требуемая длина строки
 * @param   string  $suffix  Добавочная строка
 * @return  string
 */
function truncate(string $string, int $length, string $suffix = ''): string
{
    if (strLength($string) <= $length) {
        return $string;
    }

    $suffixLength = strLength($suffix);

    if (
        $suffixLength
        && $suffixLength * 2 <= $length
    ) {
        return mb_substr($string, 0, $length - $suffixLength, Cfg::CHARSET) . $suffix;
    }

    return mb_substr($string, 0, $length, Cfg::CHARSET);
}

/**
 * Кодирование строки в формат MIME base64 с исключением
 * из результата символов, требующих дополнительного URI-кодирования.
 *
 * @param   string  $string
 * @return  string
 */
function base64UrlEncode(string $string): string
{
    return str_replace(
        ['+', '/', '='],
        ['-', '_', ''],
        base64_encode($string)
    );
}

/**
 * Преобразование первого символа строки в верхний регистр.
 *
 * @param   string  $string  Входная строка
 * @return  string
 */
function mb_ucfirst(string $string): string
{
    $firstChar = mb_strtoupper(mb_substr($string, 0, 1));
    return $firstChar . mb_substr($string, 1);
}

/**
 * Преобразование UTF-8 строки в массив отдельных символов.
 *
 * @param   string  $string  Входная строка
 * @return  array
 */
function strToCharsArray(string $string): array {
	$result = preg_split('//u', $string, null, PREG_SPLIT_NO_EMPTY);
	return $result ?: [];
}

/**
 * Преобразование кодировки строки из CP866 в UTF-8.
 *
 * @param   string  $string  Входная строка
 * @return  string
 */
function CP866_UTF8(string $string): string
{
    return mb_convert_encoding($string, 'UTF-8', 'cp866');
}

/**
 * Преобразование кодировки строки из CP1251 в UTF-8
 *
 * @param   string  $string  Входная строка
 * @return  string
 */
function CP1251_UTF8(string $string): string
{
    return mb_convert_encoding($string, 'UTF-8', 'Windows-1251');
}

/**
 * Генерация короткой стоки на основе числа.
 *
 * @param   int  $n  Входное число
 * @return  string
 */
function compressNumber(int $n): string
{
    $codeset = '123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
    $base = strlen($codeset);
    $converted = '';

    while ($n > 0) {
        $converted = $codeset[$n % $base] . $converted;
        $n = floor($n / $base);
    }

    return $converted;
}

/**
 * Генерация короткой стоки на основе строки.
 *
 * @param   string  $string  Входная строка
 * @return  string
 */
function shortener(string $string): string
{
    return compressNumber(hexdec(hash('crc32b', $string)));
}

/**
 * Получение текстового представления логического значения.
 *
 * @param   mixed  $value  Преобразуемое значение (bool или 't'|'f' для pgsql)
 * @return  string
 */
function boolToStr($value): string
{
    return $value ? 'Да' : 'Нет';
}

/**
 * Получение буквы столбца в электронной таблице по его номеру.
 *
 * @param   int  $num
 * @return  string
 */
function colNumToChar(int $num): string
{
    if ($num > 0 && $num < 16384) {
        --$num;
        $name = '';

        while ($num >= 26) {
            $name = chr(($num % 26) + 65) . $name;
            $num = $num / 26 - 1;
        }
        $name = chr(($num % 26) + 65) . $name;
    } else {
        $name = '?';
    }

    return $name;
}

/**
 * Простейшая функция склонения слов после числительных.
 *
 * @param   int     $number    Количество
 * @param   string  $variant1  Ед. число, им. падеж (1 'вещь')
 * @param   string  $variant2  Ед. число, род. падеж (2 'вещи')
 * @param   string  $variant3  Мн. число, род. падеж (5 'вещей')
 * @return  string  Подходящая форма слова
 */
function choosePlural(int $number, string $variant1, string $variant2, string $variant3): string
{
    if (!is_string($variant1) || !is_string($variant2) || !is_string($variant3)) {
        return '?';
    }

    $number = abs($number);
    $mod10 = $number % 10;
    $mod100 = $number % 100;

    if ($mod10 === 1 && $mod100 !== 11) {
        return $variant1;
    }

    if ($mod10 >= 2 && $mod10 <= 4 && !($mod100 > 10 && $mod100 < 20)) {
        return $variant2;
    }

    return $variant3;
}

/**
 * @see choosePlural()
 *
 * @param   int
 * @param   string
 * @param   string
 * @param   string
 * @return  string  Количество + подходящая форма слова
 */
function getPlural(int $number, string $variant1, string $variant2, string $variant3): string
{
    return $number . ' ' . choosePlural($number, $variant1, $variant2, $variant3);
}

/**
 * Получение суммы прописью.
 *
 * @param   float  $num  Сумма
 * @return  string
 * @author  runcore
 */
function num2str(float $num): string
{
    $nul = 'ноль';
    $ten = [
        ['', 'один', 'два', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'],
        ['', 'одна', 'две', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять']
    ];
    $a20 = [
        'десять', 'одиннадцать', 'двенадцать', 'тринадцать', 'четырнадцать',
        'пятнадцать', 'шестнадцать', 'семнадцать', 'восемнадцать', 'девятнадцать'
    ];
    $tens = [
        2 => 'двадцать', 'тридцать', 'сорок', 'пятьдесят', 'шестьдесят', 'семьдесят', 'восемьдесят', 'девяносто'
    ];
    $hundred = [
        '', 'сто', 'двести', 'триста', 'четыреста', 'пятьсот',
        'шестьсот', 'семьсот', 'восемьсот', 'девятьсот'
    ];
    $unit = [
        ['копейка',  'копейки',   'копеек',     1],
        ['рубль',    'рубля',     'рублей',     0],
        ['тысяча',   'тысячи',    'тысяч',      1],
        ['миллион',  'миллиона',  'миллионов' , 0],
        ['миллиард', 'миллиарда', 'миллиардов', 0]
    ];

    [$rub, $kop] = explode('.', sprintf('%015.2f', $num));
    $out = [];

    if ((int) $rub > 0) {
        foreach(str_split($rub, 3) as $uk => $v) { // by 3 symbols
            if (!(int) $v) {
                continue;
            }
            $uk = count($unit) - $uk - 1; // unit key
            $gender = $unit[$uk][3];
            [$i1, $i2, $i3] = array_map('intval', str_split($v, 1));
            // mega-logic
            $out[] = $hundred[$i1]; # 1xx-9xx
            if ($i2 > 1) {
                $out[] = $tens[$i2] . ' ' . $ten[$gender][$i3]; # 20-99
            } else {
                $out[] = $i2 > 0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
            }
            // units without rub & kop
            if ($uk > 1) {
                $out[] = choosePlural($v, $unit[$uk][0], $unit[$uk][1], $unit[$uk][2]);
            }
        }
    } else {
        $out[] = $nul;
    }

    $out[] = choosePlural((int) $rub, $unit[1][0], $unit[1][1], $unit[1][2]); // rub
    $out[] = $kop . ' ' . choosePlural($kop, $unit[0][0], $unit[0][1], $unit[0][2]); // kop

    return trim(preg_replace('/ {2,}/', ' ', implode(' ', $out)));
}

/**
 * Преобразование строки к виду 9001234567 (номер сотового телефона)
 * с удалением всех символов, не являющихся цифрами.
 *
 * @param   string  $num  Входная строка
 * @return  string
 */
function clearMobileNumber(string $num): string
{
    return substr(preg_replace('/\D/', '', $num), -10);
}

/**
 * Удаление начальных и замыкающих пробелов,
 * замена повторяющихся пробелов одним.
 *
 * @param   string  $string  Входная строка
 * @return  string
 */
function clearWhiteSpaces(string $string): string
{
    return preg_replace('/\s+/', ' ', trim($string));
}

/**
 * Обезличивание адреса электоронной почты с сохранением узнаваемости.
 *
 * @param   string  $email
 * @return  string
 */
function depersonalizeEmail(string $email): string
{
    if (!PHPMailer\PHPMailer\PHPMailer::validateAddress($email)) {
        return 'unknown@address';
    }

    $atPos = mb_strpos($email, '@');

    return '...' . mb_substr($email, ($atPos + 1) / 2);
}

/**
 * @deprecated
 * @see Randomizer::getHex()
 *
 * @param   bool
 * @return  string
 */
function genString(bool $moreSecure = false): string
{
    return Randomizer::getHex($moreSecure ? 64 : 32);
}
