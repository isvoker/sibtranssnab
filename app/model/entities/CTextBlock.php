<?php

ClassLoader::preloadClass('entities_base/BaseCTextBlock');

/**
 * Реализация класса [Текстовый блок].
 *
 * @author Dmitriy Lunin
 */
class CTextBlock extends BaseCTextBlock {
    public function __toString():string
    {
        $name = 'Текстовый блок';
        if (isset($this->ident)) {
            $name .= ' "' . $this->ident . '"';
        }
        return $name;
    }
}
