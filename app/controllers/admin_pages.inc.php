<?php
/* controller "admin_pages" */
try {
    if (!CPageMeta::canWeDoIt(Action::UPDATE)) {
        throw new AccessDeniedEx( AccessDeniedEx::YOU_CANT );
    }

    DBCommand::begin();

    switch ($action) {
        case 'pages':
            $data = Ajax::getDataOk();
            $data['data'] = PageManager::getPageTree(
                Request::getVar('id', 'numeric', 0)
            );

            break;

        case 'find':
            $query = Request::getVar('q', 'string');

            if (!$query) {
                break;
            }

            $data = Ajax::getDataOk();
            $data['items'] = PageManager::fetch(
                (new FetchBy())
                    ->or(['name' => $query])
                    ->or(['full_path' => $query]),
                (new FetchOptions())
                    ->setRawRecords()
                    ->setSelect(['id', 'text' => 'name', 'full_path'])
            );

            break;

        case 'updateName':
            $id = Request::getVar('id', 'numeric');
            $text = Request::getVar('text', 'string');

            if ( !$id || !$text ) {
                break;
            }

            PageManager::updateName($id, $text);

            $data = Ajax::getDataOk();
            $data['data'] = ['id' => $id, 'text' => $text];

            break;

        case 'delete':
            $id = Request::getVar('id', 'numeric');

            if (!$id) {
                break;
            }

            PageManager::delete(
                PageManager::getById($id),
                true
            );

            $data = Ajax::getDataOk();

            break;

        case 'dnd':
            $id = Request::getVar('id', 'numeric');
            $targetId = Request::getVar('targetId', 'numeric');
            $point = Request::getVar('point', 'string');

            if ( !$id || !$targetId || !$point ) {
                break;
            }

            if (PageManager::dnd($id, $targetId, $point)) {
                $data = Ajax::getDataOk();
                $data['data'] = ['success' => true];
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
