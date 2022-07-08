<?php
/* controller "dictionaries" */
try {
    ClassLoader::loadClass('Dictionary');

    DBCommand::begin();

    switch ($action) {
        case 'getText':
            $dicAlias = Request::getVar('dic', 'string');
            $code = Request::getVar('code', 'string');

            if ( !$dicAlias || !$code ) {
                break;
            }

            $data = Ajax::getDataOk();
            $data['text'] = Dictionary::getText($dicAlias, $code);

            break;

        case 'getRows':
            $dicAlias = Request::getVar('dic', 'string');

            if (!$dicAlias) {
                break;
            }

            $revisionInCache = Request::getVar('revisionInCache', 'numeric');
            $parent = Request::getVar('id', 'string', 'NULL');

            $newRevision = Dictionary::checkRevisionUpdate($dicAlias, $revisionInCache);

            $data = Ajax::getDataOk();

            if ($newRevision) {
                $data['revision'] = $newRevision;
                $data['rows'] = Dictionary::getRowsFromDic($dicAlias, $parent);
            }

            break;

        case 'getRowsLike':
            $dicAlias = Request::getVar('dic', 'string');
            $query = Request::getVar('q', 'string');

            if ( !$dicAlias || !$query ) {
                break;
            }

            $limit = min(
                Request::getVar('limit', 'numeric', Cfg::DEFAULT_RECORDS_LIMIT),
                Cfg::DEFAULT_RECORDS_LIMIT
            );

            $data = Ajax::getDataOk();
            $data['rows'] = Dictionary::getRowsFromDic($dicAlias, null, "%{$query}%", $limit);

            break;

        case 'tree':
            $dicId = Request::getVar('dicId', 'numeric');

            if (!$dicId) {
                break;
            }

            $parent = Request::getVar('id', 'numeric', 0);

            $data = Ajax::getDataOk();
            $data['data'] = Dictionary::getTreeNodesByDicId($dicId, $parent);

            break;

        case 'treeNodeCreate':
            $dicId = Request::getVar('dicId', 'numeric');
            $parentId = Request::getVar('parentId', 'numeric');

            if ( !$dicId || $parentId === null ) {
                break;
            }

            $data = Ajax::getDataOk();
            $data['data'] = [
                'id' => Dictionary::createRow($dicId, $parentId),
                'text' => Dictionary::DEFAULT_TEXT
            ];

            break;

        case 'treeNodeUpdate':
            $dicId = Request::getVar('dicId', 'numeric');
            $id = Request::getVar('id', 'numeric');
            $text = Request::getVar('text', 'string');

            if ( !$dicId || !$id || !$text ) {
                break;
            }

            Dictionary::updateRow($dicId, $id, $text);
            $data = Ajax::getDataOk();

            break;

        case 'treeNodeDestroy':
            $dicId = Request::getVar('dicId', 'numeric');
            $id = Request::getVar('id', 'numeric');

            if ( !$dicId || !$id ) {
                break;
            }

            Dictionary::deleteRow($dicId, $id);
            $data = Ajax::getDataOk();

            break;

        case 'treeNodeDnD':
            $dicId = Request::getVar('dicId', 'numeric');
            $id = Request::getVar('id', 'numeric');
            $targetId = Request::getVar('targetId', 'numeric');
            $point = Request::getVar('point', 'string');

            if ( !$dicId || !$id || !$targetId || !$point ) {
                break;
            }

            Dictionary::dndRow($dicId, $id, $targetId, $point);
            $data = Ajax::getDataOk();

            break;

        default:
            break;
    }

    DBCommand::commit();
} catch (Throwable $E) {
    DBCommand::rollback();
    $data = Ajax::getDataError($E);
}
