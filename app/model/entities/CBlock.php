<?php

ClassLoader::preloadClass('entities_base/BaseCBlock');

/**
 * Реализация класса [Блок страницы].
 *
 * @author Dmitriy Lunin
 */
class CBlock extends BaseCBlock {
    /** @see [[AbstractEntity]] */
    public function buildExtraData(): void
    {
        $this->extraData['icon'] = BlockManager::getIcon($this->ident);
        $this->buildButtons();
    }
}
