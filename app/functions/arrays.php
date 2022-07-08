<?php
/**
 * Утилиты для работы с массивами
 */

/**
 * Разбор полученных полей формы.
 *
 * @param   array  $input
 * @return  array
 */
function parseFields(array $input): array
{
    $fields = [];
    foreach ($input as $field => $value) {
        if (
            $value !== Cfg::DEFAULT_EMPTY_VALUE
            && stripos($field, Cfg::DEFAULT_EMPTY_PREFIX) === false
        ) {
            $fields[ $field ] = $value;
        }
    }

    return $fields;
}

/**
 * Разбор полей таблицы настройки прав для различных групп пользователей
 *
 * @param   array   $input   Входной массив
 * @param   string  $prefix  Префикс имён полей
 * @return  array
 */
function parsePermissions(array $input, string $prefix = 'perms_'): array
{
    $perms = [];
    $pattern = "/^{$prefix}(\d+)$/";

    foreach ($input as $field => $value) {
        if (preg_match($pattern, $field, $matches)) {
            $statuses = 1;
            if (is_array($value)) {
                foreach ($value as $permCode) {
                    if (is_numeric($permCode)) {
                        $statuses *= (int) $permCode;
                    }
                }
            }
            if ($statuses > 1) {
                $perms[ $matches[1] ] = $statuses;
            }
        }
    }

    return $perms;
}

/**
 * Разбор массива с парами "спец_название_поля" => "значение",
 * где "спец_название_поля" - строка вида "{$prefix}{$objId}_{$fieldName}".
 *
 * @param   array   $input   Входной массив
 * @param   string  $prefix  Префикс имён полей
 * @return  array   Массив вида [$objId => ['field' => 'value', ...], ...]
 */
function extractObjFields(array $input, string $prefix = ''): array
{
    $objFields = [];
    $pattern = "/^{$prefix}(\d+)_(\S+)$/";

    foreach ($input as $field => $value) {
        preg_match($pattern, $field, $matches);
        if (isset($matches[1], $matches[2])) {
            $objFields[ $matches[1] ][ $matches[2] ] = $value;
        }
    }

    if (
        isset($objFields[0])
        && is_array($objFields[0])
        && is_array($newObjects = current($objFields[0]))
    ) {
        $newObjCnt = count($newObjects);
        $fieldNames = array_keys($objFields[0]);
        for ($i = 0; $i < $newObjCnt; ++$i) {
            $row = [];
            foreach ($fieldNames as $field) {
                $row[ $field ] = $objFields[0][ $field ][ $i ] ?? null;
            }
            $objFields[] = $row;
        }
        unset($objFields[0]);
    }

    return $objFields;
}

/**
 * Объединение элементов массива в строку с применением callback-функции ко всем элементам массива.
 *
 * @param   array     $input  Входной массив
 * @param   string    $glue   Разделитель
 * @param   callable  $func   Callback-функция, применяемая к каждому элементу массива
 * @return  string
 */
function arrayToStr(array $input, string $glue = ', ', $func = null): string
{
    if (!$input) {
        return '';
    }

    if ($func !== null && is_callable($func)) {
        $input = array_map($func, $input);
    }

    return implode($glue, $input);
}

/**
 * Возвращает значение элемента массива с заданным ключом.
 * При отсутствии необходимого элемента возвращается значение по умолчанию.
 *
 * @param   array       $array    Входной массив
 * @param   int|string  $key      Ключ элемента массива
 * @param   mixed       $default  Значение по умолчанию
 * @return  mixed
 */
function arrayGetValue(array $array, $key, $default = null)
{
    return (is_string($key) || is_int($key)) && (isset($array[ $key ]) || array_key_exists($key, $array))
        ? $array[ $key ]
        : $default;
}

/**
 * Добавляет элемент в начало ассоциативного массива.
 *
 * @param  array   $array  Входной массив
 * @param  string  $key    Ключ элемента массива
 * @param  mixed   $value  Значение элемента
 */
function arrayUnshiftAssoc(array &$array, $key, $value): void
{
    $array = array_reverse($array, true);
    $array[ $key ] = $value;
    $array = array_reverse($array, true);
}

/**
 * Получение значений одного поля из массива массивов.
 *
 * @param   array   $arrayList  Массив массивов
 * @param   string  $fieldName  Название поля
 * @return  array
 */
function getColumn(array $arrayList, string $fieldName): array
{
    $result = [];
    if (!empty($arrayList) ) {
        foreach ($arrayList as $row) {
            if (is_array($row) && isset($row[ $fieldName ])) {
                $result[] = $row[ $fieldName ];
            }
        }
    }

    return $result;
}

/**
 * Получение первого ключа массива.
 * Полифилл для PHP < 7.3.0.
 * @link  https://www.php.net/manual/function.array-key-first.php#refsect1-function.array-key-first-notes
 *
 * @param   array  $array  Массив
 * @return  mixed  Первый ключ массива $array, если он не пустой; NULL в противном случае.
 */
if (!function_exists('array_key_first')) {
    function array_key_first(array $array)
    {
        foreach ($array as $key => $unused) {
            return $key;
        }

        return null;
    }
}

function getFieldsTypes(array $fields)
{
    $types = [];

    foreach ($fields as $name => $value) {
        if ($value === 'html') {
            $types[$name] = 'html';
            continue;
        }
        if ($value === 'filepath') {
            $types[$name] = 'filepath';
            continue;
        }
        $types[$name] = gettype($value);
    }

    return $types;
}

function filterFields(array $fields, array $types): array
{
    foreach ($fields as $name => $value) {
        if (isset($types[$name])) {
            switch ($types[$name]) {
                case 'integer':
                case 'boolean':
                    $fields[$name] = (int)$value;
                    break;

                case 'float':
                case 'double':
                    $fields[$name] = (float)$value;
                    break;

                case 'string':
                case 'html':
                case 'filepath':
                    $fields[$name] = $value;
                    break;

                default:
                    break;
            }
        }
    }

    return $fields;
}
