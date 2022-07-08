<?php
/* controller "admin_guard" */
try {
    if (!User::isAdmin()) {
        throw new AccessDeniedEx();
    }

    switch ($action) {
        case 'findGuardLogs':
            $page = Request::getVar('page', 'numeric');
            $rows = Request::getVar('rows', 'numeric');

            if ( !$page || !$rows ) {
                break;
            }

            $searchStr = Request::getVar('searchStr', 'string');
            $sort = Request::getVar('sort', 'string');
            $order = Request::getVar('order', 'string');

            $orderBy = ( $sort && $order )
                ? [$sort => $order]
                : ['id' => DBQueryBuilder::DESC];

            $data = Ajax::getDataOk();
            $data['list'] = Guard::findLogs($searchStr, $rows, ($page - 1) * $rows, $orderBy);
            $data['total'] = Guard::countLogs();

            break;

        case 'findGuardLockouts':
            $page = Request::getVar('page', 'numeric');
            $rows = Request::getVar('rows', 'numeric');

            if ( !$page || !$rows ) {
                break;
            }

            $searchStr = Request::getVar('searchStr', 'string');
            $sort = Request::getVar('sort', 'string');
            $order = Request::getVar('order', 'string');

            $orderBy = ( $sort && $order )
                ? [$sort => $order]
                : ['id' => DBQueryBuilder::DESC];

            $data = Ajax::getDataOk();
            $data['list'] = Guard::findLockouts($searchStr, $rows, ($page - 1) * $rows, $orderBy);
            $data['total'] = Guard::countLockouts();

            break;

        case 'makeDbDump':
            $data = Ajax::getDataOk();
            $data['msg'] = nl2br( DBDump::doIt() );

            break;

        default:
            break;
    }
} catch (Throwable $E) {
    $data = Ajax::getDataError($E);
}
