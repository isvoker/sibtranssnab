<?php
/**
 * Статичный класс Html.
 *
 * Набор методов для создания часто используемых HTML тегов.
 *
 * @author Lunin Dmitriy
 * @author Yii2
 */
class Html
{
    /** "Настоящий" <br> */
    public const BR_TRUE = '<br/>';

    /** <br>-заменитель */
    public const BR_REPLACEMENT = '^br^';

    /**
     * List of void elements
     * @link  http://www.w3.org/TR/html-markup/syntax.html#void-element
     */
    public const VOID_ELEMENTS = [
        'area'    => true,
        'base'    => true,
        'br'      => true,
        'col'     => true,
        'command' => true,
        'embed'   => true,
        'hr'      => true,
        'img'     => true,
        'input'   => true,
        'keygen'  => true,
        'link'    => true,
        'meta'    => true,
        'param'   => true,
        'source'  => true,
        'track'   => true,
        'wbr'     => true
    ];

    /**
     * Преобразует специальные символы в HTML-сущности.
     *
     * @param   string  $string
     * @return  string
     */
    public static function qSC(string $string): string
    {
        return htmlspecialchars(
            $string,
            ENT_QUOTES | ENT_SUBSTITUTE | ENT_DISALLOWED | ENT_XHTML,
            Cfg::CHARSET,
            false
        );
    }

    /**
     * Преобразует HTML-сущности обратно в соответствующие символы
     *
     * @param   string  $string
     * @return  string
     */
    public static function dSC(string $string): string
    {
        return htmlspecialchars_decode($string, ENT_QUOTES | ENT_XHTML);
    }

    /**
     * Замена подстановки, обозначающей тег [<br>], этим тегом.
     *
     * @param   string  $string
     * @return  string
     */
    public static function makeBrReal(string $string): string
    {
        return str_replace(self::BR_REPLACEMENT, self::BR_TRUE, $string);
    }

    /**
     * Удаление "лишних" пробелов и переводов строк из HTML-кода
     * @link  https://stackoverflow.com/questions/6225351
     *
     * @param   string  $rawHTML  Исходный HTML-код
     * @return  string
     */
    public static function strip(string $rawHTML): string
    {
        $search = [
            '/\>[^\S ]+/s', // strip whitespaces after tags, except space
            '/[^\S ]+\</s', // strip whitespaces before tags, except space
            '/(\s)+/s'      // shorten multiple whitespace sequences
        ];
        $replace = [
            '>',
            '<',
            '\\1'
        ];

        return preg_replace($search, $replace, $rawHTML);
    }

    /**
     * Генерация кода атрибутов HTML тегов.
     * Атрибуты с булевым значением будут иметь вид 'name="name"'.
     * Атрибуты со значением, равным null, игнорируются.
     *
     * @param   array  $attributes  Список атрибутов ['name' => 'value', ...]
     * @return  string
     */
    public static function renderTagAttributes(array $attributes): string
    {
        $html = '';
        foreach ($attributes as $attr => $value) {
            if (is_bool($value)) {
                if ($value) {
                    $html .= " {$attr}=\"{$attr}\"";
                }
            } elseif ($value !== null) {
                if ($attr === 'class' && is_array($value)) {
                    $value = implode(' ', $value);
                }
                $html .= " {$attr}=\"" . str_replace('"', '`', self::qSC($value)) . '"';
            }
        }

        return $html;
    }

    /**
     * Генерация кода атрибута data-"$suffix" стандарта HTML5,
     * позволяющего указывать нестандартные свойства тега в формате JSON.
     *
     * @param   array   $options  Список свойств ['name' => 'value', ...]
     * @param   string  $suffix   Суффикс атрибута
     * @return  string
     */
    public static function renderTagOptions(array $options, string $suffix = 'options'): string
    {
        if (empty($options)) {
            return '';
        }
        // так ли уж надо отсеивать NULL'ы?
        return " data-{$suffix}='"
            . json_encode(
                array_filter($options, static function ($v) { return $v !== null; }),
                JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
            ) . "'";
    }

    /**
     * Генерация кода HTML тега.
     *
     * @param   string  $tag         Название тега
     * @param   array   $attributes  Список атрибутов ['name' => 'value', ...]
     * @param  ?string  $innerHtml   Внутренний код
     * @return  string
     */
    public static function tag(
        string $tag,
        array $attributes = [],
        ?string $innerHtml = null
    ): string {
        $html = '<' . $tag . self::renderTagAttributes($attributes);

        if (isset(self::VOID_ELEMENTS[ strtolower($tag) ])) {
            return $html . '/>';
        }

        return "{$html}>{$innerHtml}</{$tag}>";
    }

    /**
     * Генерация кода input'ов.
     *
     * @param   array  $attributes  Список атрибутов ['name' => 'value', ...]
     * @return  string
     */
    public static function input(array $attributes): string
    {
        return self::tag('input', $attributes);
    }

    /**
     * Генерация кода HTML тега select.
     *
     * @param   array        $rawAttributes  Атрибуты тега:
     * ~~~
     *   string  $id        Значение атрибута 'id'
     *   array   $class     Значение атрибута 'class'
     *   string  $name      Значение атрибута 'name'
     *   string  $value     Значение атрибута 'value' пункта списка, который должен быть выбран
     *   bool    $required  Обязательно ли выбирать какое-то значение
     *   bool    $multiple  Это список множественного выбора?
     *   int     $size      Значение атрибута 'size' в случае $multiple == true. По умолчанию - 5.
     * ~~~
	 * @param   array        $items  Список доступных значений вида
	 *   [ 1 => ['value' => 'VAL', 'text' => 'TXT', 'active' => $selected], ... ]
     * @param   string|null  $defaultValue   Значение по умолчанию
     * @return  string
     */
    public static function selectField(
        array $rawAttributes,
        array $items,
        string $defaultValue = null
    ): string {
        if (empty($items)) {
            return 'Не из чего выбирать';
        }

        $attributes['id']    = $rawAttributes['id'] ?? null;
        $attributes['class'] = $rawAttributes['class'] ?? [];
        $attributes['name']  = $rawAttributes['name'] ?? null;

        $attributes['class'][] = 'input_select';

        $value    = $rawAttributes['value'] ?? null;
        $multiple = $rawAttributes['multiple'] ?? false;
        $required = $rawAttributes['required'] ?? false;

        if ($multiple) {
            $attributes['class'][] = 'multiple';
            $attributes['multiple'] = 'multiple';
            $attributes['size'] = $rawAttributes['size'] ?? 5;
        }
        if ($required) {
            $attributes['class'][] = 'required';
            $attributes['required'] = 'required';
            $innerHtml = '';
        } else {
            if ($defaultValue === null) {
                $defaultValue = Cfg::DEFAULT_EMPTY_VALUE;
            }
            $innerHtml = "<option value=\"{$defaultValue}\">&nbsp;</option>";
        }

        foreach ($items as $option) {
            $innerHtml .= "<option value=\"{$option['value']}\"";
            if ($value !== null && $value == $option['value']) {
                $value = null;
                $innerHtml .= ' selected="selected"';
            }
			if ($option['active'] ?? false) {
				$innerHtml .= ' selected="selected"';
			}
            $innerHtml .= ">{$option['text']}</option>";
        }

        return self::tag('select', $attributes, $innerHtml);
    }

    /**
     * Генерация кода HTML тега button.
     *
     * @param   string  $label       Текст кнопки
     * @param   array   $attributes  Список доп. атрибутов кнопки
     * @return  string
     */
    public static function button(string $label = 'button', array $attributes = []): string
    {
        if (!isset($attributes['type'])) {
            $attributes['type'] = 'button';
        }

        return self::tag('button', $attributes, $label);
    }

    /**
     * Генерация кода кнопки Добавить/Редактировать/Удалить.
     *
     * @param   string  $action      Операция, для которой требуется кнопка
     * @param   array   $attributes  Список доп. атрибутов кнопки
     * @return  string
     */
    public static function buttonForEdit(string $action, array $attributes = []): string
    {
        $attributes['class'] = ['control-btn'];

        switch ($action) {
            case Action::INSERT:
                $attributes['class'][] = 'control-btn_add js__entity_add';
                $attributes['title'] = 'Добавить';
                break;
            case Action::UPDATE:
                $attributes['class'][] = 'control-btn_edit js__entity_edit';
                $attributes['title'] = 'Редактировать';
                break;
            case Action::DELETE:
                $attributes['class'][] = 'control-btn_delete js__entity_delete';
                $attributes['title'] = 'Удалить';
                break;
            default:
                break;
        }

        return self::button('', $attributes);
    }

    /**
     * Генерация кода кнопки Добавить/Редактировать/Удалить
     * с проверкой принадлежности текущего пользователя к заданным группам.
     *
     * @param   string  $action      Операция, для которой требуется кнопка
     * @param   array   $attributes  Список доп. атрибутов кнопки
     * @param   array   $userGroups  Группы пользователей, для которых надо генерировать код
     * @return  string
     */
    public static function buttonForEditByGroups(
        string $action,
        array $attributes = [],
        array $userGroups = [Cfg::GRP_ADMINS]
    ): string {
        return User::isInGroup($userGroups, false)
            ? self::buttonForEdit($action, $attributes)
            : '';
    }

    /**
     * Генерация кода набора кнопок Добавить/Редактировать/Удалить
     * для объектов класса AbstractEntity.
     *
     * @param  string  $entity   Класс объекта
     * @param  array   $actions  Список операций, для которых требуются кнопки
     * @param  array   $options  Доп. параметры:
     * ~~~
     *   array   $userGroups  Группы пользователей, для которых надо генерировать код
     *   string  $url         URL страницы добавления/редактирования
     *   int     $id          ID объекта
     *   array   $fields      Предустановленные значения полей объекта
     * ~~~
     * @return  string
     */
    public static function entityEditButtons(
        string $entity,
        array $actions,
        array $options = []
    ): string {
        $userGroups = $options['userGroups'] ?? [Cfg::GRP_ADMINS];

        if (
            empty($actions)
            || !User::isInGroup($userGroups, false)
        ) {
            return '';
        }

        $url = is_string($options['url'] ?? null)
            ? $options['url']
            : Cfg::URL_ENTITY_EDIT;
        $id = $options['id'] ?? null;

        $get = "?entity={$entity}";
        if ($id && is_numeric($id)) {
            $get .= "&amp;id={$id}";
        }
        if (is_array($options['fields'] ?? null)) {
            foreach ($options['fields'] as $field => $value) {
                if (is_numeric($value) || is_string($value)) {
                    $get .= "&amp;f[{$field}]={$value}";
                }
            }
        }

        $html = '';
        foreach ($actions as $key => $action) {
            switch ($action) {
                case Action::INSERT:
                case Action::UPDATE:
                    $html .= self::buttonForEdit(
                        $action,
                        ['onclick' => "Common.redirect(&apos;{$url}{$get}&apos;,true);"]
                    );
                    break;
                case Action::DELETE:
                    $html .= self::buttonForEdit(
                        $action,
                        ['data-entity' => $entity, 'data-id' => $id]
                    );
                    break;
                default:
                    break;
            }
        }

        return "<div class=\"control-btns\">{$html}</div>";
    }

    /**
     * Генерация кода paginator'а.
     *
     * @param   int      $page        Текущая страница
     * @param   int      $totalItems  Общее кол-во записей
     * @param   int      $perPage     Кол-во записей на странице
     * @param   int|null $pagesToShow Сколько страниц показывать в пагинаторе
     * @return  string
     */
    public static function paginator(
        int $page,
        int $totalItems,
        int $perPage = Cfg::DEFAULT_RECORDS_LIMIT,
		int $pagesToShow = null
    ): string {
        if ($totalItems <= $perPage) {
            return '';
        }

        if (!$pagesToShow) {
	        $pagesToShow = Application::isMobileSite() ? 6 : 12;
        }

        ClassLoader::loadClass('EasyUILoader');
        EasyUILoader::putPlugins(['slider']);

        StaticResourceImporter::css('ext/jquery.paginator');
        StaticResourceImporter::js('ext/jquery.paginator');

        return "<div class='paginator' data-current-page='{$page}' data-total-items='{$totalItems}' data-per-page='{$perPage}' data-pages_to_show='{$pagesToShow}'></div>";
    }
}
