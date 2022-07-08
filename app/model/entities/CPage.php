<?php

ClassLoader::preloadClass('entities_base/BaseCPage');

/**
 * Реализация класса [Страница сайта].
 *
 * @author Dmitriy Lunin
 */
class CPage extends BaseCPage {
    public function __toString():string
    {
        $name = 'Страница';
        if (isset($this->name)) {
            $name .= ' "' . $this->name . '"';
        }
        return $name;
    }
}
