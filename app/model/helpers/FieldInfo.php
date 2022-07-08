<?php
/**
 * Интерфейс для работы с метаинформацией поля [Сущности].
 *
 * @author Lunin Dmitriy
 */
class FieldInfo
{
    /**
     * Типы полей.
     */
    public const FT_RAW = 'raw'; // хранится БЕЗ преобразований
    public const FT_BOOLEAN = 'boolean';
    public const FT_DATE = 'date';
    public const FT_TIME = 'time';
    public const FT_DATETIME = 'datetime';
    public const FT_INTEGER = 'integer';
    public const FT_FLOAT = 'float';
    public const FT_STATUSES = 'statuses'; // произведение кодов статусов
    public const FT_TEXT = 'text';
    public const FT_MULTILINETEXT = 'multilinetext'; // многострочный текст
    public const FT_EMAIL = 'email';
    public const FT_PASS = 'password';
    public const FT_HTML = 'html'; // HTML-код, НЕ экранируемый при отображении
    public const FT_FILEPATH = 'filepath'; // путь к файлу в ФС, преобразуется для использования в URL
    public const FT_URL = 'url'; // http-ссылка (абсолютная или относительно корня сайта)
    public const FT_FTS = 'fts'; // tsvector

    /**
     * Способы редактирования полей.
     */
    public const EM_TEXT = 'text';
    public const EM_TEXTAREA = 'textarea';
    public const EM_RAW = 'raw'; // то же, что и 'textarea', но без удаления whitespace-символов
    public const EM_CHECKBOX = 'checkbox';
    public const EM_URL = 'url'; // ссылка с возможным выбором на сервере
    public const EM_HTMLEDITOR = 'htmleditor'; // HTML-код; WYSIWYG редактор
    public const EM_SELECT_FROM_DB = 'select_from_db'; // select со значениями, полученными из БД
    public const EM_AUTOCOMPLETE = 'autocomplete'; // текстовое поле с автодополнением
	public const EM_DATETIME = 'datetime'; // дата и время
	public const EM_DATE = 'date'; // дата
	public const EM_TIME = 'time'; // время

    /**
     * Способы редактирования полей из справочника.
     */
    public const EM_DIC_AUTOCOMPLETE = 'dic_autocomplete';
    public const EM_DIC_SELECT = 'dic_select'; // select со значениями из справочника
    public const EM_DIC_TREE = 'dic_tree'; // EasyUI tree

    /**
     * Способы поиска по полю.
     */
    public const SM_SIMPLE = 'simple';
    public const SM_STRICT = 'strict';
    public const SM_BOOLEAN = 'boolean';
    public const SM_LIST = 'list';
    public const SM_INTERVAL = 'interval';
    public const SM_STATUSES = 'statuses';

    /**
     * Возможные значения первого элемента
     * $fieldMeta['editable_for'] и $fieldMeta['visible_for'].
     */
    public const PERMS_INSERT_GROUPS = 'AS_INSERT';
    public const PERMS_UPDATE_GROUPS = 'AS_UPDATE';
    public const PERMS_DELETE_GROUPS = 'AS_DELETE';

    /**
     * Описание поля [Сущности] - [[$fieldMeta]].
     *
     * @var array
     */
    protected $info = [];

    /**
     * Списки групп пользователей, которым разрешено
     * добавлять/изменять/удалять [Сущность].
     */
    protected $INSERT_GROUPS = [];
    protected $UPDATE_GROUPS = [];
    protected $DELETE_GROUPS = [];

    /**
     * Список групп пользователей, от лица которых запрашиваются данные.
     *
     * @var array
     */
    protected $forUserGroups = [];

    /**
     * Поле доступно для просмотра.
     *
     * @var bool
     */
    protected $isVisible;

    /**
     * Поле доступно для редактирования.
     *
     * @var bool
     */
    protected $isEditable;

    /**
     * FieldInfo constructor.
     *
     * @param  array  $info
     * @param  array  $permissions
     * @param  array  $forUserGroups
     */
    public function __construct(
        array $info,
        array $permissions = [],
        array $forUserGroups = []
    ) {
        if (!is_string($info['type'] ?? null)) {
            throw new InternalEx('Field type is undefined');
        }

        $this->info = $info;

        if (isset($permissions[2])) {
            $this->INSERT_GROUPS = $permissions[0];
            $this->UPDATE_GROUPS = $permissions[1];
            $this->DELETE_GROUPS = $permissions[2];
        }

        $this->forUserGroups = $forUserGroups;
    }

    /**
     * Имеется ли указанное свойство.
     *
     * @param   string  $name  Имя свойства
     * @return  bool
     */
    public function __isset(string $name): bool
    {
        return isset($this->info[ $name ]);
    }

    /**
     * Присвоение значения произвольному свойству.
     *
     * @param  string  $name   Имя свойства
     * @param  mixed   $value  Значение
     */
    public function __set(string $name, $value)
    {
        throw new InternalEx('This class does not support method `__set()`');
    }

    /**
     * Получение значения произвольного свойства.
     *
     * @param   string  $name  Имя свойства
     * @return  mixed
     */
    public function __get(string $name)
    {
        return $this->info[ $name ] ?? null;
    }

    /**
     * Получение имени поля.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->info['name'] ?? 'unknown_field';
    }

    /**
     * Получение описания поля.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->info['description'] ?? '';
    }

    /**
     * Получение типа поля.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->info['type'];
    }

    /**
     * Значение поля может принимать только неотрицательные значения.
     *
     * @return bool
     */
    public function isUnsigned(): bool
    {
        return $this->info['unsigned'] ?? false;
    }

    /**
     * Значение поля не должно быть пустым.
     *
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->info['required'] ?? false;
    }

    /**
     * Получение максимальная длины текстового поля.
     *
     * @return int
     */
    public function getMaxLength(): int
    {
        return $this->info['maxlength'] ?? 0;
    }

    /**
     * Получение допустимого кол-ва десятичных знаков поля типа [[FT_FLOAT]].
     *
     * @return int
     */
    public function getPrecision(): int
    {
        return $this->info['precision'] ?? 0;
    }

    /**
     * Получение значения поля по умолчанию.
     *
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->info['default'] ?? null;
    }

    /**
     * Получение регулярного выражения, которому должно соответствовать значение поля.
     *
     * @return string
     */
    public function getRegexp(): string
    {
        return $this->info['regexp'] ?? '';
    }

    /**
     * Получение минимального из возможных значения числового поля.
     *
     * @return int
     */
    public function getMinValue(): int
    {
        return $this->info['morethen_value'] ?? 0;
    }

    /**
     * Получение алиаса словаря, кодами которого являются значения поля.
     *
     * @return string
     */
    public function getDic(): string
    {
        return $this->info['dic'] ?? '';
    }

    /**
     * Получение максимального кол-ва значений для выбора из словаря.
     *
     * @return int
     */
    public function getDicMultiMax(): int
    {
        return $this->info['dic_multi_max'] ?? 0;
    }

    /**
     * Получение "способа поиска" по полю.
     *
     * @return string
     */
    public function getSearchMode(): string
    {
        return $this->info['search_mode'] ?? '';
    }

    /**
     * Получение "способа редактирования" поля.
     *
     * @return string
     */
    public function getEditingMode(): string
    {
        return $this->info['editing_mode'] ?? '';
    }

    /**
     * Получение "параметров редактирования" поля (для [[EM_SELECT_FROM_DB]] и [[EM_AUTOCOMPLETE]]).
     *
     * @return array
     */
    public function getEditingParam(): array
    {
        return $this->info['editing_param'] ?? [];
    }

    /**
     * Получение имени поля, кодированное значение которого будет значением по умолчанию.
     *
     * @return string
     */
    public function getSourceForEncoding(): string
    {
        return $this->info['encode_from'] ?? '';
    }

    /**
     * В истории изменения полей [Сущности] это поле учитывать не надо.
     *
     * @return bool
     */
    public function isIgnoredByHistory(): bool
    {
        return $this->info['history_ignore'] ?? false;
    }

    /**
     * Получение названия группы, в которую будет отнесено поле
     * в форме, генерируемой [[EntityRender]]'ом.
     *
     * @return string
     */
    public function getFieldsetName(): string
    {
        return $this->info['fieldset'] ?? '';
    }

    /**
     * @return bool
     */
    public function typeIsRaw(): bool
    {
        return $this->info['type'] === self::FT_RAW;
    }

    /**
     * @return bool
     */
    public function typeIsBoolean(): bool
    {
        return $this->info['type'] === self::FT_BOOLEAN;
    }

    /**
     * @return bool
     */
    public function typeIsDate(): bool
    {
        return $this->info['type'] === self::FT_DATE;
    }

    /**
     * @return bool
     */
    public function typeIsTime(): bool
    {
        return $this->info['type'] === self::FT_TIME;
    }

    /**
     * @return bool
     */
    public function typeIsDatetime(): bool
    {
        return $this->info['type'] === self::FT_DATETIME;
    }

    /**
     * @return bool
     */
    public function typeIsInteger(): bool
    {
        return $this->info['type'] === self::FT_INTEGER;
    }

    /**
     * @return bool
     */
    public function typeIsFloat(): bool
    {
        return $this->info['type'] === self::FT_FLOAT;
    }

    /**
     * @return bool
     */
    public function typeIsStatuses(): bool
    {
        return $this->info['type'] === self::FT_STATUSES;
    }

    /**
     * @return bool
     */
    public function typeIsText(): bool
    {
        return $this->info['type'] === self::FT_TEXT;
    }

    /**
     * @return bool
     */
    public function typeIsMultilineText(): bool
    {
        return $this->info['type'] === self::FT_MULTILINETEXT;
    }

    /**
     * @return bool
     */
    public function typeIsEmail(): bool
    {
        return $this->info['type'] === self::FT_EMAIL;
    }

    /**
     * @return bool
     */
    public function typeIsPassword(): bool
    {
        return $this->info['type'] === self::FT_PASS;
    }

    /**
     * @return bool
     */
    public function typeIsHtml(): bool
    {
        return $this->info['type'] === self::FT_HTML;
    }

    /**
     * @return bool
     */
    public function typeIsFilepath(): bool
    {
        return $this->info['type'] === self::FT_FILEPATH;
    }

    /**
     * @return bool
     */
    public function typeIsUrl(): bool
    {
        return $this->info['type'] === self::FT_URL;
    }

    /**
     * @return bool
     */
    public function typeIsFts(): bool
    {
        return $this->info['type'] === self::FT_FTS;
    }

    /**
     * @return bool
     */
    public function editingIsText(): bool
    {
        return $this->getEditingMode() === self::EM_TEXT;
    }

    /**
     * @return bool
     */
    public function editingIsTextarea(): bool
    {
        return $this->getEditingMode() === self::EM_TEXTAREA;
    }

    /**
     * @return bool
     */
    public function editingIsRaw(): bool
    {
        return $this->getEditingMode() === self::EM_RAW;
    }

    /**
     * @return bool
     */
    public function editingIsCheckbox(): bool
    {
        return $this->getEditingMode() === self::EM_CHECKBOX;
    }

    /**
     * @return bool
     */
    public function editingIsUrl(): bool
    {
        return $this->getEditingMode() === self::EM_URL;
    }

    /**
     * @return bool
     */
    public function editingIsHtmlEditor(): bool
    {
        return $this->getEditingMode() === self::EM_HTMLEDITOR;
    }

    /**
     * @return bool
     */
    public function editingIsSelectFromDb(): bool
    {
        return $this->getEditingMode() === self::EM_SELECT_FROM_DB;
    }

    /**
     * @return bool
     */
    public function editingIsAutocomplete(): bool
    {
        return $this->getEditingMode() === self::EM_AUTOCOMPLETE;
    }

    /**
     * @return bool
     */
    public function editingIsDicAutocomplete(): bool
    {
        return $this->getEditingMode() === self::EM_DIC_AUTOCOMPLETE;
    }

    /**
     * @return bool
     */
    public function editingIsDicSelect(): bool
    {
        return $this->getEditingMode() === self::EM_DIC_SELECT;
    }

    /**
     * @return bool
     */
    public function editingIsDicTree(): bool
    {
        return $this->getEditingMode() === self::EM_DIC_TREE;
    }

    /**
     * @return bool
     */
    public function searchIsSimple(): bool
    {
        return $this->getSearchMode() === self::SM_SIMPLE;
    }

    /**
     * @return bool
     */
    public function searchIsStrict(): bool
    {
        return $this->getSearchMode() === self::SM_STRICT;
    }

    /**
     * @return bool
     */
    public function searchIsBoolean(): bool
    {
        return $this->getSearchMode() === self::SM_BOOLEAN;
    }

    /**
     * @return bool
     */
    public function searchIsList(): bool
    {
        return $this->getSearchMode() === self::SM_LIST;
    }

    /**
     * @return bool
     */
    public function searchIsInterval(): bool
    {
        return $this->getSearchMode() === self::SM_INTERVAL;
    }

    /**
     * @return bool
     */
    public function searchIsStatuses(): bool
    {
        return $this->getSearchMode() === self::SM_STATUSES;
    }

    /**
     * Определение видимости/редактируемости поля.
     *
     * @param   string  $action
     * @return  bool
     */
    protected function definePermission(string $action): bool
    {
        if (empty($this->forUserGroups)) {
            return false;
        }

        $infoProperty = $action === Action::VIEW
            ? 'visible_for'
            : 'editable_for';

        if (!is_array($this->info[ $infoProperty ] ?? null)) {
            return true;
        }

        if (empty($this->info[ $infoProperty ])) {
            return false;
        }

        if ($this->info[ $infoProperty ][0] === self::PERMS_INSERT_GROUPS) {
            $this->info[ $infoProperty ] = $this->INSERT_GROUPS;
        }
        elseif ($this->info[ $infoProperty ][0] === self::PERMS_UPDATE_GROUPS) {
            $this->info[ $infoProperty ] = $this->UPDATE_GROUPS;
        }
        elseif ($this->info[ $infoProperty ][0] === self::PERMS_DELETE_GROUPS) {
            $this->info[ $infoProperty ] = $this->DELETE_GROUPS;
        }

        return $this->info[ $infoProperty ][0] === AbstractEntityMeta::PERMS_ALL_GROUPS
            || array_intersect($this->info[ $infoProperty ], $this->forUserGroups);
    }

    /**
     * Доступно ли поле для просмотра?
     *
     * @return bool
     */
    public function isVisible(): bool
    {
        if ($this->isVisible === null) {
            $this->isVisible = $this->definePermission(Action::VIEW);
        }

        return $this->isVisible;
    }

    /**
     * Доступно ли поле для редактирования?
     *
     * @return bool
     */
    public function isEditable(): bool
    {
        if ($this->isEditable === null) {
            $this->isEditable = $this->definePermission(Action::UPDATE);

            if ($this->isEditable) {
                $this->isVisible = true;
            }
        }

        return $this->isEditable;
    }
}
