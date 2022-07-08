<?php

ClassLoader::loadClass('MailEx');

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Класс для рассылки email-сообщений.
 *
 * @author Dmitry Lunin
 * @author Anatolii Shevelev
 */
class Notifier
{
    const ATTACHMENTS_MAX_SIZE = 10485760; // 10 Megabyte

    /**
     * Отправка сообщения на электронную почту.
     * Обязательно должны быть заданы хотя бы один адрес получателя (любым из доступных способов) и тема сообщения.
     * Так же нужно указать $option['template'], $option['module_template'] или $tplVars['message']
     *
     * @param  array  $options  Список параметров:
     * ~~~
     * array   recipients       Получатели - список массивов из адресов и имён вида
     *                          [['foo@example.com', 'Foo User'], ...] (имя можно опустить)
     * string  to_address       Адрес получателя
     * string  to_name          Имя получателя
     * string  from_address     Адрес отправителя
     * string  from_name        Имя отправителя
     * array   replyTo          Адреса для ответов (формат как у recipients)
     * string  template         Smarty-шаблон сообщения, находящийся в директории 'mail'
     * string  module_template  Smarty-шаблон сообщения для модуля (путь относительно директории '/modules/')
     * string  subject          Тема сообщения
     * ~~~
     * @param  array  $tplVars      Параметры, передаваемые в шаблон
     * @param  array  $attachments  Вложения - список файлов вида
     *     [['/path/to/file', 'original-filename', 'mime-type'], ...]
     *     (оригинальное имя файла и тип можно опустить)
     * @return  bool  TRUE в случае успеха, иначе FALSE
     */
    public static function sendMail(array $options, array $tplVars = [], array $attachments = [])
    {
        $Mail = new PHPMailer();
        $Mail->CharSet = "UTF-8";                      // Кодировка
        $Mail->Subject = $options['subject'] ?? false; // Тема письма

        $siteName = SiteOptions::get('site_name');

        // Получатели
        if ($recipients = arrayGetValue($options, 'recipients', [])) {
            $recipients = array_filter($recipients, static function($addr) {
                return !empty($addr[0]) && PHPMailer::validateAddress($addr[0]);
            });
        }
        if ($toAddress = arrayGetValue($options, 'to_address')) {
            if (!PHPMailer::validateAddress($toAddress)) {
                return false;
            }
            $recipients[] = [$toAddress, $options['to_name'] ?? null];
        }
        if (empty($recipients)) {
            return false;
        }

        // Адреса для ответов
        if (!empty($options['replyTo'])) {
            foreach ($options['replyTo'] as $i => $replyTo) {
                if (!empty($replyTo[0])) {
                    $Mail->addReplyTo($replyTo[0], !empty($replyTo[1]) ? $replyTo[1]  : '');
                }
            }
        } else {
            $Mail->addReplyTo(
            	SiteOptions::get('admin_email'),
	            $siteName
            );
        }

        // Настройки шаблона
        $tpl_dir = '';
        if ($template = arrayGetValue($options, 'template')) {
            $tpl_dir = 'mail';
        } elseif ($template = arrayGetValue($options, 'module_template')){
            $tpl_dir = 'modules';
        } elseif (empty($tplVars['message'])) {
            return false;
        }
        if ($tpl_dir && $template) {
            $tplVars['template'] = "file:[{$tpl_dir}]" . $template . Cfg::SMARTY_TEMPLATE_EXT;
        }
        $tplVars['server_name'] = Request::getServerName(false);
        $tplVars['host'] = Request::getServerName();
        $tplVars['site_name'] = $siteName;
        $tplVars['site_copyright'] = SiteOptions::get('copyright');
        $tplVars['theme_color_1'] = SiteOptions::get('theme_color_1');
        $tplVars['theme_color_2'] = SiteOptions::get('theme_color_2');
        Application::assign($tplVars);
        $Mail->msgHTML(Application::getContent('mail', 'defaultMailTemplate'), __DIR__);

        // SMTP
        if (Cfg::MAILER === 'smtp') {
            $Mail->isSMTP();

            $Mail->Host = Cfg::MAILER_SMTP_HOST;
            $Mail->Port = Cfg::MAILER_SMTP_PORT;
            $Mail->SMTPSecure = Cfg::MAILER_SMTP_ENCRYPTION === 'ssl'
                ? PHPMailer::ENCRYPTION_SMTPS
                : PHPMailer::ENCRYPTION_STARTTLS;

            if (Cfg::MAILER_SMTP_AUTH) {
                $Mail->SMTPAuth = true;
                $Mail->Username = Cfg::MAILER_SMTP_USER;
                $Mail->Password = Cfg::MAILER_SMTP_PASSWORD;
            }

            $fromAddr = Cfg::MAILER_SMTP_USER;
        } else {
            $Mail->isMail();
	        $fromAddr = $options['from_address'] ?? SiteOptions::get('robot_email');
        }

	    $Mail->setFrom(
		    $fromAddr,
		    $options['from_name'] ?? $siteName
	    );

        // Прикрепленные файлы
        if ($attachments) {
            $attachmentsSize = 0;
            foreach ($attachments as $file) {
                if (!is_array($file) || !isset($file[0])) {
                    continue;
                }
                $fileSize = filesize($file[0]);
                if ($fileSize === false || $attachmentsSize + $fileSize > self::ATTACHMENTS_MAX_SIZE) {
                    throw new MailEx( MailEx::ATTACHMENT_SIZE_IS_INVALID );
                }
                $attachmentsSize += $fileSize;
                $Mail->addAttachment(
                    $file[0],
                    $file[1] ?? ''
                );
            }
        }

        // Рассылка писем получателям и обработка ошибок
        $SendErrors = [];
        foreach ($recipients as $addr) {
            $Mail->clearAddresses();

            if (is_array($addr)) {
                $Mail->addAddress($addr[0], empty($addr[1]) ? '' : $addr[1]);
            } else {
                $Mail->addAddress($addr);
            }

            if (!$Mail->send()) {
                $SendErrors[] = [
                    'recipient' => $addr,
                    'info' => $Mail->ErrorInfo
                ];

                $content = "To: " . $addr[0] . "\nSubject: " . $Mail->Subject;

                foreach ($tplVars as $key => $tplvar) {
                    if (is_array($tplvar)) {
                        $content .= "\n\n******* Fields *******";
                        foreach ($tplvar as $field => $value) {
                            if (!empty($value)) {
                                $content .= "\n" . $field . ": " . $value;
                            }
                        }
                        $content .= "\n";
                    } else {
                        $content .= "\n" . $key . ": " . $tplvar;
                    }
                }

                FsFile::make(
                    Cfg::DIR_LOG_NOTICES
                        . date('Y-m-d_H-i-s') . '_[' . Randomizer::getString(8) . '].log',
                    $content
                );
            }
        }

        if (!empty($SendErrors)) {
            Logger::error(
                'Notifier::sendMail() errors',
                json_encode($SendErrors),
                Logger\LogToFile::HANDLER
            );
            return false;
        }

        return true;
    }

    /**
     * @see [[self::update()]]
     * МЕТОД ВРЕМЕННО ОСТАВЛЕН ТОЛЬКО ДЛЯ ОБРАТНОЙ СОВМЕСТИМОСТИ.
     */
    public static function sendEmail($recipients, $subject, $сontent = '', $attachments = [])
    {
        if (is_string($recipients)) {
            $recipients = [[$recipients]];
        }
        return self::sendMail(
            ['recipients' => $recipients,
             'subject' => $subject],
            ['message' => $сontent],
            $attachments
        );
    }

    /**
     * Отправка сообщения на электронную почту Администратора.
     *
     * @param   string  $event    Заголовок сообщения
     * @param   string  $message  Текст сообщения
     * @return  bool    TRUE в случае успеха, иначе FALSE
     */
    public static function adminNotice(string $event, string $message): bool
    {
        if ($event && $message) {
            $subject = Request::getServerName() . ', ' . $event;
            return self::sendMail(
                ['to_address' => SiteOptions::get('admin_email'),
                 'template' => 'adminNotice',
                 'subject' => $subject],
                ['event' => $event,
                 'message' => $message,
                 'time' => Time::toDateTime(),
                 'ip' => Request::getUserIP(),
                 'userAgent' => Html::qSC(Request::getUserAgent())]
            );
        }
        return false;
    }
}
