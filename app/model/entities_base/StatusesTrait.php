<?php
/**
 * Поддержка статусов для контроллеров сущностей.
 *
 * @author Dmitriy Lunin
 */
trait StatusesTrait
{
    /**
     * Расшифровка статусов сущности.
     *
     * @param   int  $value
     * @return  string
     */
    final public function codesToString(int $value): string
    {
        if ($value < 2) {
            return '';
        }

        $description = '';
        $comma = false;
        foreach ($this->getMetaInfo()->getDescriptions() as $code => $name) {
            if ($value < 2) {
                break;
            }
            if (!($value % $code)) {
                $value /= $code;
                if ($comma) {
                    $description .= ', ';
                } else {
                    $comma = true;
                }
                $description .= $name;
            }
        }

        return $description;
    }

    /**
     * Проверка наличия заданного статуса.
     *
     * @param   string|int  $status  Текстовое обозначение или код статуса
     * @return  bool
     */
    final public function isSetStatus($status): bool
    {
        $code = is_int($status)
            ? $status
            : $this->getMetaInfo()->getStatusCode($status);

        return $this->statuses && $code && !($this->statuses % $code);
    }

    /**
     * Добавление нового статуса (если такого ещё нет).
     *
     * @param   string  $status  Новый статус
     * @return  bool    TRUE, если статус действительно был добавлен. Иначе FALSE.
     */
    final public function addStatus(string $status): bool
    {
        $code = $this->getMetaInfo()->getStatusCode($status);

        if ($code && !$this->isSetStatus($code)) {
            $this->setTrust(
                'statuses',
                $this->statuses ? $this->statuses * $code : $code
            );
            return true;
        }

        return false;
    }

    /**
     * Удаление имеющегося статуса (если такой есть).
     *
     * @param   string  $status  Статус
     * @return  bool    TRUE, если статус действительно был удалён. Иначе FALSE.
     */
    final public function removeStatus(string $status): bool
    {
        $code = $this->getMetaInfo()->getStatusCode($status);

        if ($code && $this->isSetStatus($code)) {
            $this->setTrust(
                'statuses',
                $this->statuses / $code
            );
            return true;
        }

        return false;
    }

    /**
     * Переключение статуса (если есть - удаляется, нет - добавляется).
     *
     * @param   string  $status  Статус
     * @return  bool    TRUE, если статус действительно был удалён. Иначе FALSE.
     */
    final public function changeStatus(string $status): bool
    {
        if ($this->isSetStatus($status)) {
            return $this->removeStatus($status);
        }

        return $this->addStatus($status);
    }
}
