<?php
/**
 * Набор функций общего назначения
 */

/**
 * Проверка наличия ошибки при последнем кодировании/декодировании JSON
 * и бросок исключения, если она была.
 */
function checkJsonError(): void
{
    if (($error = json_last_error()) !== JSON_ERROR_NONE) {
        throw new JSONEx($error);
    }
}

/**
 * Кодирование данных в JSON-представление.
 *
 * @param   array  $data
 * @return  string
 */
function toJson($data): string
{
    $json = json_encode($data, JSON_UNESCAPED_UNICODE);
    checkJsonError();

    return $json;
}

/**
 * Декодирование строки JSON в ассоциативный массив.
 *
 * @param   ?string  $dataEnc
 * @return  array
 */
function fromJson($dataEnc): array
{
    if (!$dataEnc || !is_string($dataEnc)) {
        return [];
    }

    $data = json_decode($dataEnc, true, 512, JSON_BIGINT_AS_STRING);
    checkJsonError();

    return is_array($data) ? $data : [];
}

/**
 * Преобразование объекта в ассоциативный массив.
 * Переменные простых типов не изменяются.
 *
 * @param   mixed  $value  Значение, которое будет преобразовано
 * @return  mixed
 */
function objectToArray($value)
{
    return json_decode(json_encode($value), true, 512, JSON_BIGINT_AS_STRING);
}

/**
 * Получение имени класса объекта
 *
 * @param   object  $object
 * @return  string
 */
function getClass($object): string
{
    $class = get_class($object);
    return $class[0] === 'c' && strpos($class, "class@anonymous\0") === 0
        ? get_parent_class($class) . '@anonymous'
        : $class;
}

/**
 * Проверка корректности email-адреса
 *
 * @param   mixed  $email  Значение для проверки
 * @return  mixed  Корректный email или FALSE
 */
function validEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Проверка типа значения переменной
 *
 * @param   mixed   $value   Переменная
 * @param   string  $type    Требуемый тип
 * @param   bool    $filled  Должно ли значение быть !empty ?
 * @return  bool
 */
function verifyValue($value, string $type, bool $filled = null): bool
{
    if ($filled !== null && $filled !== !empty($value)) {
        return false;
    }

    switch ($type) {
        case 'bool':
            return is_bool($value);
        case 'numeric':
            return is_numeric($value) && ((abs($value) - PHP_INT_MAX < 0) || $value === PHP_INT_MAX);
        case 'int':
        case 'integer':
            return is_int($value);
        case 'float':
            return is_float($value);
        case 'string':
            return is_string($value);
        case 'object':
            return is_object($value);
        case 'array':
            return is_array($value);
        default:
            return false;
    }
}

/**
 * Разбор JSON, полученного от Common.retrieveFormDataToJson().
 * Поля с названием на [[Cfg::DEFAULT_EMPTY_PREFIX]]
 * или со значением [[Cfg::DEFAULT_EMPTY_VALUE]] пропускаются.
 *
 * @param   string  $json             Закодированная в JSON строка
 * @param   bool    $isArraysAllowed  Являются ли массивы допустимыми значениями
 * @return  array
 */
function getFormDataFromJson(string $json, bool $isArraysAllowed = true): array
{
    if (empty($json)) {
        return [];
    }

    $data = fromJson($json);

    if (empty($data)) {
        return [];
    }

    $decode = static function(string $string): string
    {
        if (!empty($string)) {
            $string = base64_decode($string);
        }
        return $string ?: '';
    };

    $inputs = [];

    foreach ($data as $name => $value) {
        if (
            $value === Cfg::DEFAULT_EMPTY_VALUE
            || strpos($name, Cfg::DEFAULT_EMPTY_PREFIX) === 0
        ) {
            continue;
        }

        if (strpos($name, '*') === 0) {
            $isEncoded = true;
            $name = substr($name, 1);
        } else {
            $isEncoded = false;
        }

        if (is_string($value)) {
            $inputs[ $name ] = $isEncoded ? $decode( $value ) : $value;
        } elseif ($isArraysAllowed && is_array($value)) {
            foreach ($value as $key => $valuePart) {
                if (is_string($valuePart)) {
                    $inputs[ $name ][] = $isEncoded ? $decode( $valuePart ) : $valuePart;
                }
            }
        }
    }

    return $inputs;
}

/**
 * Разбор JSON, полученного от jQuery Form Plugin.
 * Поля с названием на [[Cfg::DEFAULT_EMPTY_PREFIX]]
 * или со значением [[Cfg::DEFAULT_EMPTY_VALUE]] пропускаются.
 *
 * @param   string  $json  Закодированная в JSON строка
 * @return  array
 *
 * @see getFormDataFromJson()
 * @deprecated
 */
function parseJSON(string $json): array
{
    if (empty($json)) {
        return [];
    }
    $form = json_decode($json);
    if (($error = json_last_error()) !== JSON_ERROR_NONE) {
        ClassLoader::loadClass('JSONEx');
        throw new JSONEx($error);
    }
    $fields = [];
    if (is_array($form)) {
        foreach ($form as $stdClass) {
            if (
                is_string($stdClass->value)
                && $stdClass->value !== Cfg::DEFAULT_EMPTY_VALUE
                && stripos($stdClass->name, Cfg::DEFAULT_EMPTY_PREFIX) === false
            ) {
                if (isset($fields[$stdClass->name])) {
                    if (is_array($fields[$stdClass->name])) {
                        $fields[$stdClass->name][] = $stdClass->value;
                    } elseif (is_string($fields[$stdClass->name])) {
                        $fields[$stdClass->name] = [$fields[$stdClass->name]];
                        $fields[$stdClass->name][] = $stdClass->value;
                    } else {
                        $fields[$stdClass->name] = $stdClass->value;
                    }
                } else {
                    $fields[$stdClass->name] = $stdClass->value;
                }
            }
        }
    }
    return $fields;
}

/**
 * Проверка правильности полученного значения captcha
 *
 * @param  mixed   $captcha  Введённое значение
 * @param  string  $namespace
 */
function checkCaptcha($captcha, string $namespace = ''): void
{
    if (empty($captcha) || !is_string($captcha)) {
        throw new SecurImageEx();
    }

    $SI = new Securimage();
    $namespace && $SI->setNamespace($namespace);

    if ($SI->check($captcha) === false) {
        throw new SecurImageEx();
    }
}

require __DIR__ . Cfg::DS . 'arrays.php';
require __DIR__ . Cfg::DS . 'files.php';
require __DIR__ . Cfg::DS . 'strings.php';
