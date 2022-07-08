<?php
/**
 * Базовый контроллер [Сущности].
 *
 * @author Pavel Nuzhdin <pnzhdin@gmail.com>
 * @author Dmitriy Lunin
 */
abstract class AbstractEntity
{
    /** Поля объекта и их значения */
    public $fields = [];

    /** Поля объекта в подходящем для отображения виде */
    public $fieldsForOutput = [];

    /**
     * Список полей, отдельные разрешения на редактирование которых
     * - $fieldMeta['editable_for'] - при сохранении изменений объекта игнорируются.
     */
    protected $fieldsTrusted = [];

    /** Дополнительные данные объекта */
    public $extraData = [];

    /** Защищённые дополнительные данные объекта */
    protected $privateExtraData = [];

    /**
     * Конструктор.
     *
     * @param  array          $fields   Поля объекта и их значения
     * @param ?ObjectOptions  $Options  Параметры создания объекта :
     * ~~~
     *   bool  $withExtraData  = false
     *   bool  $forOutput      = false
     *   bool  $skipValidation = false
     *   bool  $showSensitive  = false
     * ~~~
     */
    public function __construct(array $fields = [], ObjectOptions $Options = null)
    {
        $Options === null && $Options = new ObjectOptions();

        $this->setFields($fields, $Options);

        if ($Options->getWithExtraData()) {
            $this->buildExtraData();
        }

        if ($Options->getForOutput()) {
            $this->buildFieldsForOutput();
        }
    }

    /**
     * Вызывается при обращении к неопределённому свойству.
     *
     * @param   string  $field  Имя свойства
     * @return  mixed
     */
    public function __get(string $field)
    {
        if ($this->fieldExists($field)) {
            return $this->fields[ $field ];
        }

        throw new InvalidArgumentException("Trying to get undefined property `{$field}`");
    }

    /**
     * Вызывается при присвоении значения неопределённому свойству.
     *
     * @param  string  $field  Имя свойства
     * @param  mixed   $value  Значение
     */
    public function __set(string $field, $value)
    {
        if ($this->fieldExists($field)) {
            $this->fields[ $field ] = self::validateFieldValue(
                $this->getMetaInfo()->getFieldInfo($field),
                $value
            );
        } else {
            throw new InvalidArgumentException("Trying to set undefined property `{$field}`");
        }
    }

    /**
     * Существует ли указанное свойство объекта.
     *
     * @param   string  $field  Имя свойства
     * @return  bool
     */
    public function __isset(string $field): bool
    {
        return isset($this->fields[ $field ]);
    }

    /**
     * Получение названия класса объекта при обращении как к стоке.
     *
     * @return string
     */
    public function __toString(): string
    {
        return get_class($this);
    }

    /**
     * Существует ли указанное поле объекта.
     *
     * @param   string  $field  Имя поля
     * @return  bool
     */
    final public function fieldExists(string $field): bool
    {
        return array_key_exists($field, $this->fields);
    }

    /**
     * Получение всех имеющихся у объекта полей.
     *
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Установка свойств объекта (полей).
     *
     * @param   array          $fields   Поля объекта и их значения
     * @param  ?ObjectOptions  $Options  Параметры создания объекта
     * ~~~
     *   bool  $skipValidation = false
     *   bool  $showSensitive  = false
     * ~~~
     * @return  AbstractEntity
     */
    public function setFields(array $fields = [], ObjectOptions $Options = null): AbstractEntity
    {
        $Options === null && $Options = new ObjectOptions();

        $skipValidation = $Options->getSkipValidation();

        foreach ($this->getMetaInfo()->getFieldsInfo() as $field => $FieldInfo) {
            if (isset($fields[ $field ])) {
                $value = $skipValidation
                    ? $fields[ $field ]
                    : self::validateFieldValue($FieldInfo, $fields[ $field ]);
            } elseif (isset($this->fields[ $field ])) {
                continue;
            } else {
                $value = $FieldInfo->getDefaultValue();
            }

            $this->fields[ $field ] = $value;
        }

        if (!$Options->getShowSensitive()) {
            $this->clearSecretFields();
        }

        return $this;
    }

    /**
     * Присвоение полям из [[$Meta::SECRET_FIELDS]] значения NULL.
     *
     * @return AbstractEntity
     */
    final public function clearSecretFields(): AbstractEntity
    {
        foreach ($this->getMetaInfo()::SECRET_FIELDS as $field) {
            $this->fields[ $field ] = null;
            unset($this->fieldsForOutput[ $field ]);
        }

        return $this;
    }

    /**
     * Получение всех имеющихся у объекта полей в подходящем для отображения виде.
     *
     * @return array
     */
    public function getFieldsForOutput(): array
    {
        if (empty($this->fieldsForOutput)) {
            $this->buildFieldsForOutput();
        }

        return $this->fieldsForOutput;
    }

    /**
     * Получение всего массива "публичных" дополнительных данных объекта.
     *
     * @return array
     */
    final public function getExtraData(): array
    {
        return $this->extraData;
    }

    /**
     * Получение значения защищённых дополнительных данных объекта по названию.
     *
     * @param   string  $name  Название
     * @return  mixed
     */
    final public function getPrivateExtraData(string $name)
    {
        return $this->privateExtraData[ $name ] ?? null;
    }

    /**
     * Указание игнорировать разрешения на редактирование поля.
     *
     * @param   string  $field  Имя поля
     * @return  AbstractEntity
     */
    final public function mustTrust(string $field): AbstractEntity
    {
        $this->fieldsTrusted[ $field ] = true;
        return $this;
    }

    /**
     * Удаление указания игнорировать разрешения на редактирование поля.
     *
     * @param   string  $field  Имя поля
     * @return  AbstractEntity
     */
    final public function mustNotTrust(string $field): AbstractEntity
    {
        unset($this->fieldsTrusted[ $field ]);
        return $this;
    }

    /**
     * Установка нового значения поля и указание
     * игнорировать разрешения на редактирование поля.
     *
     * @param   string  $field  Имя поля
     * @param   mixed   $value  Новое значение поля
     * @return  AbstractEntity
     */
    final public function setTrust(string $field, $value): AbstractEntity
    {
        $this->__set($field, $value);
        return $this->mustTrust($field);
    }

    /**
     * Установлено ли значение указанного поля системой.
     *
     * @param   string  $field  Имя поля
     * @return  bool
     */
    final public function canTrust(string $field): bool
    {
        return array_key_exists($field, $this->fieldsTrusted);
    }

    /**
     * Получение объекта с метаинформацией для вызывающего объекта.
     *
     * @return AbstractEntityMeta
     */
    final public function getMetaInfo(): AbstractEntityMeta
    {
        return call_user_func([get_class($this) . 'Meta', 'getInstance']);
    }

    /**
     * Проверка соответствия значения поля его типу и корректировка при необходимости.
     *
     * @param   FieldInfo  $FieldInfo  Метаинформация поля
     * @param   mixed      $value      Значение переменной
     * @return  mixed      Валидное значение переменной ИЛИ null
     */
    final public static function validateFieldValue(FieldInfo $FieldInfo, $value)
    {
        if (
            ($value === null || $value === '')
            && !$FieldInfo->typeIsBoolean()
        ) {
            return null;
        }

        if ($FieldInfo->getDicMultiMax()) {
            if (is_array($value)) {
                $value = array_filter($value, 'is_numeric');
                if (empty($value)) {
                    return null;
                }
                $value = Dictionary::MULTI_SEPARATOR . implode(Dictionary::MULTI_SEPARATOR, $value);
            } elseif (verifyValue($value, 'numeric')) {
                $value = Dictionary::MULTI_SEPARATOR . $value;
            } elseif (!preg_match('/^(\|\d+)+$/', $value)) {
                return null;
            }
        }

        if (is_string($value)) {
	        if (!$FieldInfo->typeIsRaw()) {
		        $value = clearWhiteSpaces($value);
	        }

            if ($FieldInfo->getMaxLength()) {
                $value = truncate($value, $FieldInfo->getMaxLength());
            }

            if (
                $FieldInfo->getRegexp()
                && !$FieldInfo->typeIsDate()
                && !$FieldInfo->typeIsDatetime()
                && !preg_match("/{$FieldInfo->getRegexp()}/", $value)
            ) {
                return null;
            }
        }

        switch ($FieldInfo->getType()) {
            case FieldInfo::FT_TEXT:
            case FieldInfo::FT_PASS:
            case FieldInfo::FT_FILEPATH:
            case FieldInfo::FT_URL:
                if (is_string($value)) {
                    $value = str_replace(["\r", "\n"], '', $value);
                } else {
                    return null;
                }
                break;

            case FieldInfo::FT_EMAIL:
                $value = validEmail($value) ?: null;
                break;

            case FieldInfo::FT_MULTILINETEXT:
            case FieldInfo::FT_HTML:
                if (is_string($value)) {
                    $value = str_replace("\r", '', $value);
                } else {
                    return null;
                }
                break;

            case FieldInfo::FT_INTEGER:
            case FieldInfo::FT_STATUSES:
                if (verifyValue($value, 'numeric')) {
                    $value = (int) $value;
                } else {
                    return null;
                }
                break;

            case FieldInfo::FT_FLOAT:
                if (verifyValue($value, 'numeric')) {
                    $value = $FieldInfo->getPrecision()
                        ? round($value, $FieldInfo->getPrecision())
                        : (float) $value;
                } else {
                    return null;
                }
                break;

            case FieldInfo::FT_BOOLEAN:
                $value = $value ? 1 : 0;
                break;

            case FieldInfo::FT_DATE:
                if (!is_string($value) || !($value = Time::toSQLDate($value))) {
                    return null;
                }
                break;

            case FieldInfo::FT_TIME:
                if (!is_string($value) || !($value = Time::toSQLTime($value))) {
                    return null;
                }
                break;

            case FieldInfo::FT_DATETIME:
                if (!is_string($value) || !($value = Time::toSQLDateTime($value))) {
                    return null;
                }
                break;

            default:
                break;
        }

        if (
            ($FieldInfo->isUnsigned() && $value < 0)
            || ($FieldInfo->getMinValue() && $value < $FieldInfo->getMinValue())
        ) {
            return null;
        }

        return $value;
    }

    /**
     * Преобразование значения поля в пригодное для хранения.
     *
     * @param   FieldInfo  $FieldInfo  Метаинформация поля
     * @param   mixed      $value      Значение поля
     * @return  mixed
     */
    final public static function makeValueForStorage(FieldInfo $FieldInfo, $value)
    {
        if (
            empty($value)
            || !is_string($value)
            || $FieldInfo->typeIsRaw()
        ) {
            return $value;
        }

        return Html::qSC($value);
    }

    /**
     * Преобразование значения поля в пригодное для вывода.
     *
     * @param   FieldInfo  $FieldInfo  Метаинформация поля
     * @param   mixed      $value      Значение поля
     * @return  mixed
     */
    final protected function makeValueForOutput(FieldInfo $FieldInfo, $value)
    {
        if (
            $value === null
            || ($value === '' && !$FieldInfo->typeIsBoolean())
        ) {
            return '';
        }

        switch ($FieldInfo->getType()) {
            case FieldInfo::FT_BOOLEAN:
                return boolToStr($value);

            case FieldInfo::FT_DATE:
                return Time::toDate($value);

            case FieldInfo::FT_TIME:
                return Time::toTime($value);

            case FieldInfo::FT_DATETIME:
                return Time::toDateTime($value);

            case FieldInfo::FT_INTEGER:
                if ($dictionary = $FieldInfo->getDic()) {
                    return $FieldInfo->getDicMultiMax()
                        ? Dictionary::getTextMulti($dictionary, $value)
                        : Dictionary::getText($dictionary, $value);
                }

                return $value;

            case FieldInfo::FT_FLOAT:
            case FieldInfo::FT_RAW:
                return $value;

            case FieldInfo::FT_MULTILINETEXT:
                return Html::makeBrReal( Html::qSC($value) );

            case FieldInfo::FT_HTML:
                return Html::strip( Html::dSC($value) );

            case FieldInfo::FT_FILEPATH:
                //case AbstractEntityMeta::FT_URL:
                return FsDirectory::getRelativePath($value, 'files');

            case FieldInfo::FT_STATUSES:
                return $this->codesToString($value);

            case FieldInfo::FT_PASS:
            case FieldInfo::FT_FTS:
                return null;

            default:
                return Html::qSC($value);
        }
    }

    /**
     * Формирование в [[$this->fieldsForOutput]] значений полей,
     * пригодных для вывода.
     */
    public function buildFieldsForOutput(): void
    {
        foreach ($this->fields as $field => $value) {
            $this->fieldsForOutput[ $field ] = $this->makeValueForOutput(
                $this->getMetaInfo()->getFieldInfo($field),
                $value
            );
        }
    }

    /**
     * Получение имени первого найденного пустого обязательного поля,
     * или NULL, если таких нет.
     *
     * @return ?string
     */
    final public function getEmptyRequiredField(): ?string
    {
        foreach ($this->getMetaInfo()->getFieldsInfo() as $field => $FieldInfo) {
            if (
                ($this->fields[ $field ] === null || $this->fields[ $field ] === '')
                && $FieldInfo->isRequired()
            ) {
                return $FieldInfo->getName();
            }
        }

        return null;
    }

    /** Внедрение дополнительных данных объекта */
    abstract public function buildExtraData(): void;
}
