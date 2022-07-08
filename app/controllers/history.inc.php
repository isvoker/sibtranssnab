<?php
/* controller "history" */
try {
    if (!Cfg::HISTORY_IS_ON) {
        throw new FeatureNotAvailableEx( FeatureNotAvailableEx::HISTORY_IS_DISABLED );
    }

    $page = Request::getVar('page', 'numeric');
    $rows = Request::getVar('rows', 'numeric');
    $entityId = Request::getVar('hEntityID', 'numeric');

    if ( !$page || !$rows || !$entityId ) {
        return;
    }

    $restricts = Request::getVar('restricts', 'string');
    $restricts = $restricts ? getFormDataFromJson($restricts) : [];

    $sort = Request::getVar('sort', 'string');
    $order = Request::getVar('order', 'string');

    $orderBy = ( $sort && $order )
        ? [$sort => $order]
        : ['time' => DBQueryBuilder::DESC];

    $FetchOptions = (new FetchOptions())
        ->setOrderBy($orderBy)
        ->setLimit($rows)
        ->setPage($page);

    $ObjectOptions = (new ObjectOptions())
        ->setForOutput();

    switch ($action) {
        case 'find_user_history':
            $restricts['user_id'] = $entityId;

            $data = Ajax::getDataOk();
            $data['rows'] = HistoryManager::findUserHistory($restricts, $FetchOptions, $ObjectOptions);
            $data['total'] = HistoryManager::count();

            break;

        default:
            break;
    }
} catch (Throwable $E) {
    $data = Ajax::getDataError($E);
}
