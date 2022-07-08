<?php
/**
 * Статичный класс для отправки email c текстовыми данными
 * HTML-формы администратору сайта
 *
 * @author Dmitriy Lunin
 */
class FormSender
{
	/** Параметры ожидаемых полей формы */
    protected const FIELDS_META = [
		'field_name' => [
			'name'      => 'Название поля',
			'maxlength' => 64,
			'required'  => false
		]
	];

	/**
	 * Проверка полученных полей формы
	 *
	 * @param   array  $formFields
	 * @return  array  Поля, прошедшие проверку
	 */
	protected static function validateFields(array $formFields): array
	{
		$validFields = [];
		foreach (static::FIELDS_META as $field => $props) {
			$value = isset($formFields[ $field ]) && is_string($formFields[ $field ])
				? trim($formFields[ $field ])
				: '';
			if (empty($value) && $props['required']) {
				throw new RequiredFieldIsEmptyEx($props['name']);
			}
			if (
				$field === 'email'
				&& $props['required']
				&& !($value = validEmail($value))
			) {
				throw new RequiredFieldIsEmptyEx($props['name']);
			}
			if (isset($props['maxlength'])) {
				$value = truncate($value, $props['maxlength']);
			}
			$validFields[ $field ] = Html::qSC($value);
		}
		return $validFields;
	}

	/**
	 * Отправка администратору сайта email с данными формы.
	 *
	 * @param   string  $template      Smarty-шаблон сообщения, находящийся в директории "mail"
	 * @param   array   $formFields    Поля формы
     * @param   array   $subject       Тема сообщения
	 * @param   bool    $checkCaptcha  Надо ли проверить код captcha
	 * @return  bool    TRUE в случае успеха, иначе FALSE
	 */
	public static function sendNotice(
	    string $template,
        array $formFields,
        string $subject = 'Обратная связь',
        bool $checkCaptcha = false
    ): bool {
		if ($checkCaptcha) {
			checkCaptcha($formFields['captcha_code'] ?? '');
		}

        $lastTime = (int)Session::get('form-sender', 'last-feedback-time');
        $counter = (int)Session::get('form-sender', 'feedback-counter');

        if ($lastTime) {
            $TimeDifference = time() - $lastTime;
            if ($TimeDifference > 3600) {
                Session::set('form-sender', 'last-feedback-time', false);
                Session::set('form-sender', 'feedback-counter', 0);
            } elseif ($TimeDifference <= 5) {
                throw new RuntimeException('Слишком частая отправка сообщений. Пожалуйста, повторите попытку через несколько секунд.');
            } elseif ($counter >= 10) {
                throw new RuntimeException('Вы отправили слишком много сообщений. Пожалуйста, повторите попытку чуть позже.');
            }
        }

		$data = self::validateFields($formFields);
		$data['time'] = Time::toDateTime();
		$data['subject'] = $subject;

        Session::set('form-sender', 'last-feedback-time', time());
        Session::set('form-sender', 'feedback-counter', $counter + 1);

        return Notifier::sendMail(
			[
			    'to_address' => SiteOptions::get('admin_email'),
                'template' => $template,
                'subject' => $subject
            ],
			['data' => $data]
		);
	}
}
