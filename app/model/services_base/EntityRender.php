<?php
/**
 * Генератор HTML-кода для отображения сущностей.
 *
 * @author Lunin Dmitriy
 */
class EntityRender
{
	/**
	 * Генерация HTML-формы на основе объекта.
	 *
	 * @param  AbstractEntity  $Obj      Объект, по которому генерируется основной HTML-код
	 * @param  string          $action   Тип операции, для которой нужна форма
	 * @param  array           $options  Дополнительные опции:
	 * ~~~
	 *   array   $userGroups     Группы пользователей, для которых генерируется форма
	 *   string  $namePrefix     Префикс для значений атрибутов 'name' в форме
	 *   bool    $objFieldsOnly  Нужен только HTML-код самих полей?
	 *   array   $formType       Типы формы ('horizontal', 'vertical', 'need-validation', ...)
	 *   string  $formClass      Дополнительные значения атрибута 'class' формы
	 *   string  $formAction     Значение атрибута 'action' формы
	 *   string  $additionHTML   Дополнительный HTML-код, включаемый после основного кода
	 *   bool    $needCaptcha    Надо ли добавить в форму капчу?
	 *   array   $buttons        Список кнопок формы в формате, совместимом с self::generateButton().
	 *                           Для стандартных значений $action есть наборы кнопок по умолчанию,
	 *                           которые можно дополнить или изменить.
	 * ~~~
	 * @return  string
	 */
	public static function singleObjForm(
		AbstractEntity $Obj,
		string $action,
		array $options = []
	): string {
		$userGroups = is_array($options['userGroups'] ?? null)
			? $options['userGroups'] : [];

		$namePrefix = is_string($options['namePrefix'] ?? null)
			? $options['namePrefix'] : '';

		$objFieldsOnly = (bool)($options['objFieldsOnly'] ?? false);

		$formType = is_array($options['formType'] ?? null)
			? $options['formType'] : [];

		$formClass = is_string($options['formClass'] ?? null)
			? $options['formClass'] : '';

		$formAction = is_string($options['formAction'] ?? null)
			? $options['formAction'] : '';

		$additionHTML = is_string($options['additionHTML'] ?? null)
			? $options['additionHTML'] : '';

		$needCaptcha = (bool)($options['needCaptcha'] ?? false);

		$buttons = is_array($options['buttons'] ?? null)
			? $options['buttons'] : [];


		if (empty($Obj->fieldsForOutput) && $action !== Action::SEARCH) {
			$Obj->buildFieldsForOutput();
		}

		$objHTML = '';

		if ($action !== Action::VIEW && $Obj->fieldExists('id') && $Obj->id) {
			$objHTML .= Html::input([
				'class' => 'js__input',
				'type' => 'hidden',
				'name' => $namePrefix . 'id',
				'value' => $Obj->id
			]);
		}

		if (isset($Obj->extraData['posit_after'])) {
			$objHTML .= Html::input([
				'class' => 'js__input',
				'type' => 'hidden',
				'name' => $namePrefix . 'posit_after',
				'value' => $Obj->extraData['posit_after']
			]);
		}

		$Meta = $Obj->getMetaInfo();
		$FieldsInfo = $userGroups
			? $Meta->getFieldsInfo($userGroups)
			: $Meta->getFieldsInfoForMe();

		foreach ($FieldsInfo as $field => $FieldInfo) {
			if (
				!$FieldInfo->isVisible()
				|| ($action === Action::INSERT && !$FieldInfo->isEditable())
				|| ($action === Action::SEARCH && !$FieldInfo->getSearchMode())
			) {
				continue;
			}

			if ($FieldInfo->getFieldsetName()) {
				$objHTML .= "<div class=\"row js__fieldset_{$FieldInfo->getFieldsetName()}\">"
					. '<div class="label';
			} else {
				$objHTML .= '<div class="row"><div class="label';
			}

			if (self::isRequiredField($action, $FieldInfo)) {
				$objHTML .= ' marked';
			}
			$objHTML .= "\">{$FieldInfo->getName()}</div><div class=\"input\">";
			$objHTML .= self::getInput(
				$action,
				$namePrefix . $field,
				$FieldInfo,
				$Obj->fields[$field],
				($action === Action::SEARCH ? null : $Obj->fieldsForOutput[$field])
			);

			$description = $FieldInfo->getDescription();

			if ($FieldInfo->isEditable()) {
				if ($encodeFrom = $FieldInfo->getSourceForEncoding()) {
					$btnAttr = [
						'class' => [
							'sensei-btn',
							'sensei-btn_s',
							'sensei-btn_' . Cfg::UI_BUTTONS_COLOR,
							'js__set-encoded-value'
						],
						'type' => 'button',
						'data-encode-from' => $encodeFrom
					];

					$objHTML .= Html::tag('button', $btnAttr, 'Сгенерировать');
				}

				if ($maxlength = $FieldInfo->getMaxLength()) {
					$maxlength = "Не более {$maxlength} символов.";
					if ($description) {
						$description .= ' ' . $maxlength;
					} else {
						$description = $maxlength;
					}
				}
			}

			if ($description) {
				$objHTML .= "<div class=\"text-note\">{$description}</div>";
			}

			$objHTML .= '</div></div>';
		}

		if ($objFieldsOnly) {
			return $objHTML;
		}

		$formAttributes = [
			'id' => 'entity-form',
			'class' => ['sensei-form'],
			'action' => !empty($formAction) ? $formAction : '.',
			'method' => 'post',
			'data-entity' => get_class($Obj),
			'data-action' => $action
		];

		if (!empty($formType)) {
			foreach ($formType as $type) {
				$formAttributes['class'][] = 'sensei-form_' . $type;
			}
		}
		if (!empty($formClass)) {
			$formAttributes['class'][] = $formClass;
		}

		$additionHTML = EntityManager::useBetterManager(
			get_class($Obj),
			'getHtmlForSingleObjForm',
			$Obj,
			$action
		) . $additionHTML;

		if ($needCaptcha) {
			$additionHTML .= self::getCaptchaField();
		}

		$buttonsHTML = '';

		switch ($action) {
			case Action::INSERT:
				$defButtons = $Meta::canWeDoIt(Action::INSERT)
					? [Action::SAVE => [], Action::RESET => []]
					: [];
				break;

			case Action::UPDATE:
				$defButtons = $Meta::canWeDoIt(Action::UPDATE)
					? [Action::SAVE => []]
					: [];
				break;

			case Action::SEARCH:
				$defButtons = [Action::SEARCH => [], Action::RESET => []];
				break;

			default:
				$defButtons = [];
				break;
		}

		if ($buttons || $defButtons) {
			$buttons = array_merge($defButtons, $buttons);
			foreach ($buttons as $act => $opt) {
				$buttonsHTML .= self::getButton($act, $opt);
			}
			if ($buttonsHTML) {
				$buttonsHTML =
				"<div class='top-buttons-float sensei-buttons'>
					{$buttonsHTML}
					<a class='go-to-top' href='#'>Наверх</a>
				</div>";
			}
		}

		return Html::tag('form', $formAttributes, $buttonsHTML . $objHTML . $additionHTML);
	}

	/**
	 * Генерация HTML-кода таблицы на основе списка объектов одного класса.
	 * NOTE: тип объекта в $objList жёстко проверяется только для первого элемента,
	 * остальные при несоответствии просто пропускаются.
	 *
	 * @param  array   $objList  Список объектов
	 * @param  string  $action   Тип операции, для которой нужна форма
	 * @param  array   $options  Дополнительные опции:
	 * ~~~
	 *   string  $tblTitle     Заголовок получившейся таблицы
	 *   array   $userGroups   Группы пользователей, для которых генерируется форма
	 *   string  $namePrefix   Префикс для значений атрибутов 'name' в форме
	 *   bool    $withButtons  Надо ли добавить кнопки btn_delete и btn_add_new?
	 * ~~~
	 * @return  string
	 */
	public static function multiObjForm(array $objList, string $action, array $options = []): string
	{
		if (empty($objList)) {
			return '';
		}

		$Obj = current($objList);
		if (!($Obj instanceof AbstractEntity)) {
			throw new InternalEx('You can render form only if object is an instance of a class AbstractEntity');
		}

		$tblTitle = is_string($options['tblTitle'] ?? null)
			? $options['tblTitle'] : '';

		$userGroups = is_array($options['userGroups'] ?? null)
			? $options['userGroups'] : [];

		$namePrefix = is_string($options['namePrefix'] ?? null)
			? $options['namePrefix'] : '';

		$withButtons = (bool) ($options['withButtons'] ?? false);

		$FieldsInfo = $userGroups
			? $Obj->getMetaInfo()->getFieldsInfo($userGroups)
			: $Obj->getMetaInfo()->getFieldsInfoForMe();
		unset($Obj);

		$tHead = '<thead><tr><th class="hidden-el"></th>';
		if ($withButtons) {
			$tHead .= '<th></th>';
		}
		$buildHead = true;
		$colNumber = 2; // кол-во столбцов в таблице, считая служебные

		$tBody = '<tbody>';
		foreach ($objList as $key => $Obj) {
			if (!($Obj instanceof AbstractEntity)) {
				break;
			}

			$prefix = $namePrefix . ($Obj->id ?: 0) . '_';
			$tBody .= '<tr>';
			if ($Obj->id) {
				$tBody .=
				'<td class="hidden-el">' .
					Html::input([
						'class' => 'js__input',
						'type' => 'hidden',
						'name' => $prefix . 'id',
						'value' => $Obj->id
					]) .
				'</td>';
			}
			if ($withButtons) {
				$tBody .= '<td>' . Html::buttonForEdit(Action::DELETE) . '</td>';
			}
			foreach ($FieldsInfo as $field => $FieldInfo) {
				if (!$FieldInfo->isVisible()) {
					continue;
				}

				if ($buildHead) {
					$tHead .= '<th';
					if ($description = $FieldInfo->getDescription()) {
						$tHead .= " title=\"{$description}\"";
					}
					if (self::isRequiredField($action, $FieldInfo)) {
						$tHead .= ' class="marked"';
					}
					$tHead .= ">{$FieldInfo->getName()}</th>";
					++$colNumber;
				}

				$tBody .=
				'<td>' .
					self::getInput(
						$action,
						$prefix . $field,
						$FieldInfo,
						$Obj->fields[ $field ],
						$Obj->fieldsForOutput[ $field ]
					) .
				'</td>';
			}
			if ($buildHead) {
				$tHead .= '</tr></thead>';
				$buildHead = false;
			}
			$tBody .= '</tr>';
		}
		$tBody .= '</tbody>';

		if ($withButtons) {
			$tFoot =
			'<tfoot>' .
				'<tr><td colspan="' . $colNumber . '">' . Html::buttonForEdit(Action::INSERT) . '</td></tr>' .
			'</tfoot>';
		} else {
			$tFoot = '';
		}

		if ($tblTitle) {
			$tblTitle = "<div class=\"label group-title\">{$tblTitle}</div>";
		}

		return "<div class=\"row\">{$tblTitle}<table class=\"sensei-form-table\">{$tHead}{$tBody}{$tFoot}</table></div>";
	}

	/**
	 * Надо ли пометить поле как обязательное для заполнения?
	 *
	 * @param   string     $action     Тип операции, для которой нужно поле
	 * @param   FieldInfo  $FieldInfo  Метаинформация поля
	 * @return  bool
	 */
	protected static function isRequiredField(string $action, FieldInfo $FieldInfo): bool
	{
		return $FieldInfo->isRequired()
			&& $FieldInfo->isEditable()
			&& $action !== Action::SEARCH;
	}

	/**
	 * Получение типа HTML-элемента <input>.
	 *
	 * @param   FieldInfo  $FieldInfo  Метаинформация поля
	 * @return  string
	 */
	protected static function getInputType(FieldInfo $FieldInfo): string
	{
		if ($FieldInfo->typeIsDate()) {
			return 'date';
		}
		if ($FieldInfo->typeIsEmail()) {
			return 'email';
		}
		if ($FieldInfo->typeIsPassword()) {
			return 'password';
		}
		return 'text';
	}

	/**
	 * Генерация HTML-кода элемента формы на основе поля объекта.
	 *
	 * @param   string     $action      Тип операции, для которой нужно поле
	 * @param   string     $field       Название поля
	 * @param   FieldInfo  $FieldInfo   Метаинформация поля
	 * @param  ?string     $value       Значение поля
	 * @param  ?string     $valuePrint  Пригодное для вывода значение поля
	 * @return  string
	 */
	protected static function getInput(
		string $action,
		string $field,
		FieldInfo $FieldInfo,
		$value,
		$valuePrint
	): string {
		if ($action === Action::VIEW) {
			return self::getReadonlyInput($valuePrint ?: '');
		}

		$attr['class'] = ['js__input', "ft_{$FieldInfo->getType()}"];
		$attr['name'] = $field;

		if ($action !== Action::SEARCH && !$FieldInfo->isEditable()) {
			if ($FieldInfo->typeIsBoolean()) {
				$attr['class'][] = 'input_checkbox';
				$attr['type'] = 'checkbox';
				$attr['disabled'] = true;
				$attr['onclick'] = 'return false;';

				if ($value) {
					$attr['value'] = 1;
					$attr['checked'] = true;
				} else {
					$attr['value'] = 0;
				}

				return Html::input($attr);
			}

			return self::getReadonlyInput($valuePrint ?: '', $attr);
		}

		$editingMode = $FieldInfo->getEditingMode();

		if ($editingMode === FieldInfo::EM_RAW) {
			$valuePrint = $value ? Html::dSC($value) : '';
			$editingMode = FieldInfo::EM_TEXTAREA;
		}

		if ($action !== Action::SEARCH && $FieldInfo->isRequired()) {
			$attr['class'][] = 'required';
			$attr['required'] = true;
		}
		if ($maxlength = $FieldInfo->getMaxLength()) {
			$attr['maxlength'] = $maxlength;
		}

		$inputDataDic = [];

		if (
			(
				$editingMode === FieldInfo::EM_TEXT
				&& !($action === Action::SEARCH && $FieldInfo->searchIsInterval())
			)
			|| ($action === Action::SEARCH && $FieldInfo->typeIsMultilineText())
		) {
			// обычное текстовое поле
			$attr['class'][] = 'input_text';
			$attr['type'] = self::getInputType($FieldInfo);
			$attr['value'] = $FieldInfo->typeIsDate() ? $value : $valuePrint;
			$attr['data-fieldname'] = $FieldInfo->getName();
			$attr['data-regexp'] = $FieldInfo->getRegexp() ?: null;

			return Html::input($attr);
		}

		if ($editingMode === FieldInfo::EM_AUTOCOMPLETE) {
			// текстовое поле с автодополнением
			$attr['class'][] = 'input_text js__autocomplete';
			$attr['type'] = 'text';
			$attr['value'] = $valuePrint;
			$attr['data-fieldname'] = $FieldInfo->getName();
			$attr['data-controller'] = $FieldInfo->getEditingParam()['controller'];

			return Html::input($attr);
		}

		if ($dictionary = $FieldInfo->getDic()) {
			// общая часть для полей из словарей
			$inputDataDic = [
				'data-fieldname' => $FieldInfo->getName(),
				'data-dic' => $dictionary,
				'data-code' => $value
			];

			if ($editingMode === FieldInfo::EM_DIC_AUTOCOMPLETE) {
				$attr['class'][] = 'input_text js__input_dic js__input_dic-autocomplete';
				$attr['type'] = 'text';

				return Html::input(array_merge($attr, $inputDataDic));
			}
		}

		if ($action === Action::INSERT || $action === Action::UPDATE) {
			// форма добавления/редактирования

			if ($editingMode === FieldInfo::EM_TEXTAREA) {
				// textarea
				$attr['class'][] = 'input_textarea';
				$attr['data-fieldname'] = $FieldInfo->getName();
				$attr['data-regexp'] = $FieldInfo->getRegexp() ?: null;

				return Html::tag('textarea', $attr, $valuePrint);
			}

			if ($editingMode === FieldInfo::EM_CHECKBOX) {
				// checkbox
				$attr['class'][] = 'input_checkbox';
				$attr['type'] = 'checkbox';
				$attr['value'] = 1;
				if ($value) {
					$attr['checked'] = true;
				}

				return Html::input($attr);
			}

			if ($editingMode === FieldInfo::EM_URL) {
				// url
				StaticResourceImporter::js('ckfinder');

				$attr['class'][] = 'input_text js__load-fm_input';
				$attr['type'] = 'text';
				$attr['value'] = $valuePrint;
				$attr['data-fieldname'] = $FieldInfo->getName();

				$btnAttr = [
					'class' => [
						'sensei-btn',
						'sensei-btn_s',
						'sensei-btn_' . Cfg::UI_BUTTONS_COLOR,
						'js__load-fm_btn'
					],
					'type' => 'button'
				];

				return Html::input($attr)
					. Html::tag('button', $btnAttr, 'Выбрать файл');
			}

			if ($editingMode === FieldInfo::EM_HTMLEDITOR) {
				// WYSIWYG HTML editor
				StaticResourceImporter::js('ckeditor');
				StaticResourceImporter::js('ckfinder');

				$attr['class'][] = 'input_textarea editor_textarea';
				$attr['readonly'] = true;

				$btnAttr = [
					'class' => [
						'sensei-btn',
						'sensei-btn_s',
						'sensei-btn_' . Cfg::UI_BUTTONS_COLOR,
						'js__load-editor'
					],
					'type' => 'button'
				];

				return Html::tag('textarea', $attr, $valuePrint)
					. Html::tag('button', $btnAttr, 'Редактировать');
			}

			if ($editingMode === FieldInfo::EM_SELECT_FROM_DB) {
				// select на основе выборки из БД
				$editingProps = $FieldInfo->getEditingParam();
				$editingProps['from'] = Cfg::DB_TBL_PREFIX . $editingProps['from'];
				$items = DBCommand::select($editingProps);
				$attr['value'] = $value;
				$attr['required'] = $FieldInfo->isRequired();

				return Html::selectField($attr, $items);
			}

			if ($editingMode === FieldInfo::EM_DIC_SELECT) {
				// select для поля из словаря
				$attr['class'][] = 'input_select js__input_dic js__input_dic-select';

				return Html::tag(
					'select',
					array_merge($attr, $inputDataDic),
					'<option></option>'
				);
			}

			if ($editingMode === FieldInfo::EM_DIC_TREE) {
				// EasyUI Tree
				$inputDataDic['data-name'] = $field;
				if ($multiMax = $FieldInfo->getDicMultiMax()) {
					$inputDataDic['data-checkbox'] = true;
					$inputDataDic['data-cascadeCheck'] = false;
					$inputDataDic['data-onlyLeafCheck'] = false;
					$inputDataDic['data-multimax'] = $multiMax;
				} else {
					$inputDataDic['data-onlyLeafCheck'] = true;
				}

				return Html::tag(
					'ul',
					array_merge(['class' => 'js__input_dic js__input_dic-tree'], $inputDataDic)
				);
			}

			if (
				$editingMode === FieldInfo::EM_DATETIME
				|| $editingMode === FieldInfo::EM_DATE
				|| $editingMode === FieldInfo::EM_TIME
			) {
				StaticResourceImporter::css('ext/datetimepicker');
				StaticResourceImporter::js('ext/datetimepicker');

				$attr['class'][] = 'input_text';
				$attr['type'] = 'text';
				$attr['value'] = $valuePrint;
				$attr['data-fieldname'] = $FieldInfo->getName();

				if ($editingMode === FieldInfo::EM_DATETIME) {
					$attr['class'][] = 'js__datetimepicker';
				}
				if ($editingMode === FieldInfo::EM_DATE) {
					$attr['class'][] = 'js__datepicker';
				}
				if ($editingMode === FieldInfo::EM_TIME) {
					$attr['class'][] = 'js__timepicker';
				}

				return Html::input($attr);
			}

		} elseif ($action === Action::SEARCH) {
			// форма поиска

			if ($FieldInfo->searchIsInterval()) {
				// поиск по диапазону значений
				unset($attr['name']);
				$attr['class'][] = 'input_text twosome';
				$attr['type'] = 'text';

				$attrStr = Html::renderTagAttributes(
					array_merge($attr, [
						'data-regexp' => $FieldInfo->getRegexp() ?: null,
						'data-fieldname' => $FieldInfo->getName()
					])
				);

				return
					'<input' . $attrStr . ' name="' . $field . '_min"/>–' .
					'<input' . $attrStr . ' name="' . $field . '_max" data-morethen="' . $field . '_min"/>';
			}

			if ($editingMode === FieldInfo::EM_CHECKBOX) {
				// переключатель "да"/"нет"
				return
				'<div>' .
					'<input class="js__input input_radio" type="radio" name="' . $field . '" value="1" data-label="да"/>' .
					'<input class="js__input input_radio" type="radio" name="' . $field . '" value="0" data-label="нет"/>' .
				'</div>';
			}

			if (
				$editingMode === FieldInfo::EM_DIC_SELECT
				|| $editingMode === FieldInfo::EM_DIC_TREE
			) {
				// EasyUI Tree с checkbox'ами
				$inputDataDic['data-name'] = $field;
				$inputDataDic['data-onlyLeafCheck'] = true;
				$inputDataDic['data-checkbox'] = true;

				return Html::tag(
					'ul',
					array_merge(['class' => 'js__input_dic js__input_dic-tree'], $inputDataDic)
				);
			}

		}

		return self::getReadonlyInput($valuePrint ?: '', $attr);
	}

	/**
	 * Генерация HTML-кода элемента формы нередактируемого поля объекта.
	 *
	 * @param string $valuePrint Пригодное для вывода значение поля
	 * @param array $attr Список дополнительных атрибутов тега ['name' => 'value', ...]
	 * @return  string
	 */
	protected static function getReadonlyInput(string $valuePrint, array $attr = []): string
	{
		$attr['class'][] = 'ft_readonly';
		foreach ($attr['class'] as $key => $class) {
			if ($class === 'js__input') {
				unset($attr['class'][ $key ]);
				break;
			}
		}

		return Html::tag('div', $attr, $valuePrint);
	}

	/**
	 * Генерация HTML-кода кнопки формы.
	 *
	 * @param  string  $action   Тип операции, для которой нужна кнопка
	 * @param  array   $options  Дополнительные опции:
	 * ~~~
	 *   string  $class     Доп. значение атрибута 'class' кнопки
	 *   string  $objId     Для кнопок-ссылок-на-историю: ID сущности
	 *   string  $objIdent  Для кнопок-ссылок-на-историю: ident сущности
	 *   string  $label     Для произвольных кнопок: текст кнопки
	 *   string  $color     Для произвольных кнопок: цвет кнопки
	 *   string  $href      Для произвольных кнопок: URL (для создания ссылки)
	 * ~~~
	 * @return  string
	 */
	protected static function getButton(string $action, array $options = []): string
	{
		$attr['class'] = ['sensei-btn', 'sensei-btn_' . Cfg::UI_BUTTONS_SIZE];
		if ($otherClasses = $options['class'] ?? null) {
			$attr['class'][] = $otherClasses;
		}

		switch ($action) {
			case Action::SAVE:
				$attr['class'][] = 'sensei-btn_' . Cfg::UI_BUTTONS_SUBMIT_COLOR . ' js__form-submit';
				return Html::button('Сохранить', $attr);

			case Action::DELETE:
				$attr['class'][] = 'sensei-btn_' . Cfg::UI_BUTTONS_DELETE_COLOR . ' js__form-delete';
				return Html::button('Удалить', $attr);

			case Action::RESET:
				$attr['class'][] = 'sensei-btn_' . Cfg::UI_BUTTONS_COLOR . ' js__form-reset';
				$attr['type'] = 'reset';
				return Html::button('Сброс', $attr);

			case Action::SEARCH:
				$attr['class'][] = 'sensei-btn_' . Cfg::UI_BUTTONS_SUBMIT_COLOR . ' js__form-run-search';
				return Html::button('Искать', $attr);

			case Action::HISTORY:
				if (isset($options['objId'], $options['objIdent'])) {
					$attr['class'][] = 'sensei-btn_' . Cfg::UI_BUTTONS_COLOR;
					$attr['href'] = Cfg::URL_HISTORY . "?{$options['objIdent']}={$options['objId']}";
					return Html::tag('a', $attr, 'История');
				}

				return '';

			default:
				if (isset($options['label'])) {
					$attr['class'][] = 'sensei-btn_' . ($options['color'] ?? Cfg::UI_BUTTONS_COLOR);

					if (isset($options['href'])) {
						$attr['href'] = $options['href'];
						return Html::tag('a', $attr, $options['label']);
					}

					return Html::button($options['label'], $attr);
				}

				return '';
		}
	}

	/**
	 * Получение HTML-кода поля ввода капчи.
	 *
	 * @return string
	 */
	protected static function getCaptchaField(): string
	{
		return '<div class="row">' .
				'<div class="label marked">Проверочный код</div>' .
				'<div class="input">' .
					'<img class="captcha captcha_refresh" src="/captcha.php" alt="CAPTCHA Image" title="Показать другое изображение"/><br/>' .
					'<input class="input_text captcha_code required js__input" type="text" autocomplete="off" name="captcha_code" required="required" size="10" maxlength="6" data-fieldname="Проверочный код"/>' .
				'</div>' .
			'</div>';
	}

	/**
	 * Генерация HTML-кода элемента select для статусов сущностей.
	 * Предполагается использовать в формах поиска.
	 *
	 * @param   AbstractEntityMeta  $Meta  Метаинформация
	 * @param   array  $attributes  Атрибуты тега (@see Html::selectField())
	 * @param   array  $activeStatuses  Список заранее выбранных статусов ([$code => true, ...])
	 * @return  string
	 */
	public static function selectForStatuses(
		AbstractEntityMeta $Meta,
		array $attributes = [],
		array $activeStatuses = []
	): string {
		if (!isset($attributes['name'])) {
			$attributes['name'] = 'statuses[]';
		}
		if (!isset($attributes['multiple'])) {
			$attributes['multiple'] = true;
		}

		$statuses = $Meta->getStatuses();
		$descriptions = $Meta->getDescriptions();
		$items = [];
		foreach ($statuses as $status => $code) {
			$items[] = [
				'value' => $status,
				'text' => $descriptions[ $code ],
				'active' => $activeStatuses[ $status ] ?? false
			];
		}

		return Html::selectField($attributes, $items);
	}
}
