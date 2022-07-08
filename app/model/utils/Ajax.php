<?php
/**
 * Статичный класс Ajax.
 *
 * Класс для обработки ajax-запросов.
 *
 * @author Dmitriy Lunin
 */
class Ajax
{
    /**
     * Получение данных для передачи в качестве ответа
     * в случае успешного выполнения операции.
     *
     * @return array
     */
    public static function getDataOk(): array
    {
        return ['status' => Response::STATUS_OK];
    }

    /**
     * Получение данных для передачи в качестве ответа
     * в случае некорректного запроса.
     *
     * @return array
     */
    public static function getDataBadRequest(): array
    {
        return [
            'status' => Response::STATUS_BAD_REQUEST,
            'error' => 'Bad Request',
            'error_debug' => ''
        ];
    }

    /**
     * Получение данных для передачи в качестве ответа
     * в случае отсутствия требуемого контроллера.
     *
     * @return array
     */
    public static function getDataControllerNotFound(): array
    {
        return [
            'status' => Response::STATUS_NOT_FOUND,
            'error' => 'Controller not found',
            'error_debug' => ''
        ];
    }

    /**
     * Получение данных для передачи в качестве ответа
     * в случае возникновения исключения в процессе выполнения.
     *
     * @param   Throwable  $E  Перехваченное исключение
     * @return  array
     */
    public static function getDataError(Throwable $E): array
    {
        return [
            'status' => Response::STATUS_INTERNAL_SERVER_ERROR,
            'error' => Logger::getError($E),
            'error_debug' => Logger::getDebugInfo($E)
        ];
    }

    /**
     * Получение наиболее подходящего для браузера пользователя
     * 'Content type' для передачи JSON.
     *
     * @return string
     */
    public static function getJsonType(): string
    {
        $userBrowser = Request::getUserBrowser();
        if (isset($userBrowser['browser'])) {
            if ($userBrowser['browser'] === 'IE') {
                return 'text/x-json';
            }

            if (
                ($userBrowser['browser'] === 'Opera')
                && ($userBrowser['version'] < '12.02')
            ) {
                return 'text/plain';
            }
        }

        return 'application/json';
    }

    /**
     * Отправка данных в виде JSON.
     *
     * @param  mixed  $data  Передаваемая переменная
     */
    public static function submitJson($data): void
    {
        Response::setContentType(self::getJsonType());
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
