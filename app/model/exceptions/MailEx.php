<?php
/**
 * Исключения, связанные с отправкой email-сообщений.
 *
 * @author Dmitriy Lunin
 */
class MailEx extends Exception implements SenseiExceptionInterface
{
    /** Возможные причины */
    public const ADDRESS_IS_INCORRECT = 1;
    public const RECIPIENT_IS_UNDEFINED = 2;
    public const SENDER_IS_UNDEFINED = 3;
    public const SUBJECT_IS_UNDEFINED = 4;
    public const ATTACHMENT_SIZE_IS_INVALID = 5;
    public const ATTACHMENT_IS_NOT_READABLE = 6;

    private $reason;

    public function __construct(string $reason = '')
    {
        $this->reason = $reason;
        parent::__construct('', 0);
    }

    public function getError(): string
    {
        switch ($this->reason) {
            case self::ADDRESS_IS_INCORRECT:
                return 'MailEx: Некорректное значение Email';

            case self::RECIPIENT_IS_UNDEFINED:
                return 'MailEx: Получатель не определён';

            case self::SENDER_IS_UNDEFINED:
                return 'MailEx: Отправитель не определён';

            case self::SUBJECT_IS_UNDEFINED:
                return 'MailEx: Тема не определена';

            case self::ATTACHMENT_SIZE_IS_INVALID:
                return 'MailEx: Недопустимый размер вложения';

            case self::ATTACHMENT_IS_NOT_READABLE:
                return 'MailEx: Вложение не может быть прочитано';

            default:
                return 'MailEx: При попытке отправить письмо возникла ошибка';
        }
    }
}
