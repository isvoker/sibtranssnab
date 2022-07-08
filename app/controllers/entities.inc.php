<?php
/* controller "entities" */
try {
    if (!User::isLoggedIn()) {
        throw new AccessDeniedEx( AccessDeniedEx::YOU_CANT );
    }

    DBCommand::begin();

    switch ($action) {
        case 'insert':
            $className = Request::getVar('entity', 'string');
            $fields = Request::getVar('fields', 'string');

            if ( !$className || !$fields ) {
                break;
            }

            $fields = getFormDataFromJson($fields);

            $Entity = EntityManager::createObject($className, $fields);
            if (method_exists($Entity, 'setExtraFields')) {
                $Entity->setExtraFields($fields);
            }
            $Entity = EntityManager::useBetterManager(
                $className,
                'add',
                $Entity
            );

            $data = Ajax::getDataOk();
            $data['id'] = $Entity->id;
            $data['msg'] = 'Объект успешно сохранён';

            break;

        case 'update':
            $className = Request::getVar('entity', 'string');
            $fields = Request::getVar('fields', 'string');

            if ( !$className || !$fields ) {
                break;
            }

            $fields = getFormDataFromJson($fields);
            if (!is_numeric($fields['id'] ?? null)) {
                break;
            }

            $Entity = EntityManager::createObject($className, $fields);
            if (method_exists($Entity, 'setExtraFields')) {
                $Entity->setExtraFields($fields);
            }
            EntityManager::useBetterManager(
                $className,
                'update',
                EntityManager::baseGetById($className, '', $fields['id']),
                $Entity
            );

            $data = Ajax::getDataOk();
            $data['msg'] = 'Объект успешно сохранён';

            break;

        case 'delete':
            $className = Request::getVar('entity', 'string');
            $id = Request::getVar('id', 'numeric');

            if ( !$className || !$id ) {
                break;
            }

            ClassLoader::loadClass($className); // для baseGetById

            EntityManager::useBetterManager(
                $className,
                'delete',
                EntityManager::baseGetById($className, '', $id)
            );

            $data = Ajax::getDataOk();
            $data['msg'] = 'Объект успешно удалён';

            break;

        case 'findRelObjects':
            $className = Request::getVar('entity', 'string');
            $relClassName = Request::getVar('relEntity', 'string');
            $nameLike = Request::getVar('q', 'string');

            if ( !$className || !$relClassName || !$nameLike ) {
                break;
            }

            $data = Ajax::getDataOk();
            $data['data'] = EntityManager::useBetterManager(
                $className,
                'findFieldsRelatedObjects',
                $relClassName,
                $nameLike,
                Request::getVar('idNot', 'array', []),
                (new FetchOptions())
                    ->setLimit(10)
            );

            break;

        default:
            break;
    }

    DBCommand::commit();
} catch (Throwable $E) {
    DBCommand::rollback();
    $data = Ajax::getDataError($E);
}
