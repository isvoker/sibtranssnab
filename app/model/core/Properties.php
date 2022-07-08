<?php
/**
 * Класс Properties.
 *
 * Работа со свойствами объектов.
 *
 * @author Dmitriy Lunin
 */
class Properties
{
    /**  Место хранения свойств объекта */
    protected $props = [];

    /**
     * Конструктор.
     *
     * @see    self::setProps()
     * @param  string|array  $props
     */
    public function __construct($props = '')
    {
        $this->setProps($props);
    }

    /**
     * Существует ли указанное свойство.
     *
     * @param   string  $name  Имя свойства
     * @return  bool
     */
    public function __isset(string $name): bool
    {
        return isset($this->props[$name]);
    }

    /**
     * Вызывается при присвоении значения неопределённому свойству.
     *
     * @param  string  $name   Имя свойства
     * @param  mixed   $value  Значение
     */
    public function __set(string $name, $value)
    {
        $this->props[$name] = $value;
    }

    /**
     * Вызывается при обращении к неопределённому свойству.
     *
     * @param   string  $name  Имя свойства
     * @return  mixed
     */
    public function __get(string $name)
    {
        if ($this->__isset($name)) {
            return $this->props[$name];
        }

        return null;
    }

    /**
     * "Пакетная" установка свойств объекта.
     *
     * @param  string|array  $props  Строка вида "var1=value1&var2=value2&..."
     *                               или массив с парами "имя_переменной - значение"
     */
    public function setProps($props): void
    {
        if (empty($props)) {
            return;
        }

        if (is_string($props)) {
            parse_str($props, $props);
        }

        if (is_array($props)) {
            foreach ($props as $name => $value) {
                $this->props[$name] = $value;
            }
        }
    }
}
