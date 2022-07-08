<?php

ClassLoader::loadClass('FormSender');

/**
 * Статичный класс для отправки заявок на обратный звонок.
 */
class FormCallbackOnBannerSender extends FormSender
{
	/** Параметры ожидаемых полей формы */
    protected const FIELDS_META = [
		'name' => [
			'name'      => 'Имя',
			'maxlength' => 64,
			'required'  => false
		],
		'phone' => [
			'name'      => 'Телефон',
			'maxlength' => 64,
			'required'  => true
		],
		'email' => [
			'name'      => 'Email',
			'maxlength' => 128,
			'required'  => false
		],
		'comment' => [
			'name'      => 'Комментарий',
			'required'  => false
		]
	];

    /** Smarty-шаблон сообщения, находящийся в директории "mail" */
    protected const TEMPLATE = 'requestCallback';

	/**
	 * Отправка email администратору сайта с данными формы.
	 *
	 * @param   array  $formFields
	 * @return  bool   TRUE в случае успеха, иначе FALSE
	 */
	public static function sendCustomNotice(array $formFields): bool
	{
		return parent::sendNotice(self::TEMPLATE, $formFields);
	}
}
