<?php

ClassLoader::loadClass('TextBlockManager');

$page = Request::getVar('page', 'numeric', 1);

$textBlocks = [];
$TextBlocksObj = TextBlockManager::fetch(
    null,
    (new FetchOptions())
        ->setCount()
        ->setOrderBy(['ident' => DBQueryBuilder::ASC])
        ->setLimit()
        ->setPage($page),
    (new ObjectOptions())
        ->setWithExtraData()
        ->setForOutput()
);
foreach ($TextBlocksObj as $TextBlock) {
	$textBlocks[] = [
		'edit_btns' => $TextBlock->getExtraData()['edit_btns'],
		'ident' => $TextBlock->fieldsForOutput['ident'],
        'description' => $TextBlock->fieldsForOutput['description'],
		'content' => $TextBlock->fieldsForOutput['content']
			? truncate( Html::qSC($TextBlock->fieldsForOutput['content']), 400, '...' )
			: ''
	];
}

Application::assign([
	'textBlocks' => $textBlocks,
	'insBtn' => CTextBlock::getInsertButton(),
	'paginator' => Html::paginator($page, TextBlockManager::count()),
]);
Application::showContent('admin', 'text_blocks');
