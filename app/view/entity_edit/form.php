<?php

$entity = Request::getVar('entity', 'string');

if (!$entity) {
    Response::redirect(Cfg::URL_ADMIN_PANEL);
}

StaticResourceImporter::js('entity-edit');
StaticResourceImporter::js('dictionaries-init');

$id = Request::getVar('id', 'numeric', -1);

if ($id === -1) {
	$action = Action::INSERT;

	$Object = EntityManager::createObject(
		$entity,
        Request::getVar('f', 'array', [])
	);

	if (
	    $Object->fieldExists('posit')
        && ($positAfter = Request::getVar('posit_after', 'string'))
    ) {
		$Object->extraData['posit_after'] = $positAfter;
	}
    RTP::setPageName("Добавление | {$Object}");
} else {
	$action = Action::UPDATE;

	ClassLoader::loadClass($entity);

    $Object = EntityManager::baseGetById(
        $entity,
        '',
        $id,
        (new ObjectOptions())->setForOutput()
    );

    RTP::setPageName("Редактирование | {$Object}");
}

if (!$Object->getMetaInfo()->canWeDoIt($action)) {
    throw new AccessDeniedEx( AccessDeniedEx::YOU_CANT );
}

Application::deleteLastBreadcrumbs();
Application::addBreadcrumbs((string)$Object);

$options = ['formType' => ['vertical', 'need-validation']];
echo EntityRender::singleObjForm($Object, $action, $options);
