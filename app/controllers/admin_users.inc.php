<?php
/* controller "admin_users" */
try {
    if (!User::isAdmin()) {
        throw new AccessDeniedEx();
    }

    DBCommand::begin();

    switch ($action) {
        case 'find':
            $restricts = Request::getVar('restricts', 'string');
            $page = Request::getVar('page', 'numeric');
            $rows = Request::getVar('rows', 'numeric');

            if ( !$restricts || !$page || !$rows ) {
                break;
            }

            $sort = Request::getVar('sort', 'string');
            $order = Request::getVar('order', 'string');

            $orderBy = ( $sort && $order )
                ? [$sort => $order]
                : ['id' => DBQueryBuilder::ASC];

            $data = Ajax::getDataOk();
            $data['usersList'] = UserManager::fetch(
                (new FetchBy())
                    ->and( getFormDataFromJson($restricts) ),
                (new FetchOptions())
                    ->setCount()
                    ->setLimit($rows)
                    ->setPage($page)
                    ->setOrderBy($orderBy),
                (new ObjectOptions())
                    ->setWithExtraData()
                    ->setForOutput()
            );
            $data['total'] = UserManager::count();

            break;

        case 'insert':
            $login = Request::getVar('login', 'string');
            $email = Request::getVar('email', 'string');
            $name = Request::getVar('name', 'string');

            if ( !$login || !$email || !$name ) {
                break;
            }

            $User = new CUser([
                'login' => $login,
                'description' => Request::getVar('description', 'string'),
                'email' => $email,
                'name' => $name
            ]);
            $User = UserManager::add($User, true);
            $User->buildFieldsForOutput();
            $User->pullGroups();

            $data = Ajax::getDataOk();
            $data['user'] = $User;
            $data['newUser'] = true;

            break;

        case 'update':
            $id = Request::getVar('id', 'numeric');
            $login = Request::getVar('login', 'string');
            $email = Request::getVar('email', 'string');
            $name = Request::getVar('name', 'string');

            if ( !$id || !$login || !$email || !$name ) {
                break;
            }

            $User = UserManager::getById(
                $id,
                (new ObjectOptions())->setShowSensitive()
            );
            $NewUser = clone $User;
            $NewUser->setFields([
                'login' => $login,
                'description' => Request::getVar('description', 'string'),
                'email' => $email,
                'name' => $name
            ]);
            UserManager::update($User, $NewUser, true);
            unset($User, $NewUser);

            $data = Ajax::getDataOk();
            $data['newUser'] = false;

            break;

        case 'delete':
            $id = Request::getVar('id', 'numeric');

            if (!$id) {
                break;
            }

            UserManager::delete(
                UserManager::getById($id),
                true
            );

            $data = Ajax::getDataOk();

            break;

        case 'block':
            $id = Request::getVar('userId', 'numeric');

            if (!$id ) {
                break;
            }

            UserManager::blockUser(
                new CUser(['id' => $id])
            );

            $data = Ajax::getDataOk();

            break;

        case 'changePassword':
            $id = Request::getVar('userId', 'numeric');
            $passwordEnc = Request::getVar('value', 'string');

            if ( !$id || !$passwordEnc ) {
                break;
            }

            UserManager::changePassword(
                UserManager::getById($id),
                $passwordEnc,
                ['checkStrength' => false, 'skipIfEmpty' => false]
            );

            $data = Ajax::getDataOk();

            break;

        case 'setUserGroups':
            $id = Request::getVar('userId', 'numeric');

            if (!$id ) {
                break;
            }

            $User = UserManager::getById($id);
            $groups = Request::getVar('groups', 'string');
            $groups = $groups ? explode('|', $groups) : [];
            UserManager::setUserGroups($User, $groups);

            $data = Ajax::getDataOk();
            $data['msg'] = 'Группы успешно изменены';

            break;

        case 'getAllGroups':
            $data = Ajax::getDataOk();
            $data['groups'] = [];
            foreach (UserManager::getAllGroups() as $group) {
                $data['groups'][] = [
                    'id' => $group['name'],
                    'text' => "{$group['description']} ({$group['name']})",
                    'state' => 'open'
                ];
            }

            break;

        default:
            break;
    }

    DBCommand::commit();
} catch (Throwable $E) {
    DBCommand::rollback();
    $data = Ajax::getDataError($E);
}
