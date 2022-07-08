<?php
/**
 * Поддержка работы с ManyToMany и OneToMany связями.
 *
 * @author Dmitriy Lunin
 */
trait RelationshipsManagerTrait
{
	/**
	 * @see EntityManager::add()
	 *
	 * Дополнительно сохраняются "связанные" объекты.
	 *
	 * @param   AbstractEntity  $Entity
	 * @param   bool            $isTrusted
	 * @return  AbstractEntity
	 */
	public static function add(AbstractEntity $Entity, bool $isTrusted = false): AbstractEntity
	{
		$Entity = parent::add($Entity, $isTrusted);
		self::saveRelatedObjects($Entity, Action::INSERT);
		return $Entity;
	}

	/**
	 * @see EntityManager::update()
	 *
	 * Дополнительно сохраняются "связанные" объекты.
	 *
	 * @param   AbstractEntity  $Entity
	 * @param   AbstractEntity  $NewEntity
	 * @param   bool            $isTrusted
	 * @return  AbstractEntity
	 */
	public static function update(
		AbstractEntity $Entity,
		AbstractEntity $NewEntity,
		bool $isTrusted = false
	): AbstractEntity {
		$Entity = parent::update($Entity, $NewEntity, $isTrusted);
		self::saveRelatedObjects($NewEntity, Action::UPDATE);
		return $Entity;
	}

	/**
	 * @see EntityManager::baseFetch()
	 *
	 * Дополнительно осуществляется поиск по полям объектов со связью ManyToMany.
	 *
	 * @param   AbstractEntityMeta  $Meta
	 * @param  ?FetchBy             $FetchBy
	 * @param   FetchBy[]           $relRestricts  ['relEntity' => $RelFetchBy]
	 * @param  ?FetchOptions        $FetchOptions
	 * @param  ?ObjectOptions       $ObjectOptions
	 * @return  array
	 */
	public static function fetchByRelatedObjects(
		AbstractEntityMeta $Meta,
		FetchBy $FetchBy = null,
		array $relRestricts = [],
		FetchOptions $FetchOptions = null,
		ObjectOptions $ObjectOptions = null
	): array {
		if (empty($relRestricts)) {
			return parent::baseFetch($Meta, $FetchBy, $FetchOptions, $ObjectOptions);
		}

		is_null($FetchOptions) && $FetchOptions = new FetchOptions();
		$FetchOptions->getSelect() || $FetchOptions->setSelect(['*']);

		$tblLeft = $Meta->getDBTable();
		$query = [
			'where' => [],
			'group' => DBCommand::qC("{$tblLeft}.id")
		];

		foreach ($relRestricts as $relClassName => $RelFetchBy) {
			$rel = $Meta->getMapping($relClassName);
			$RelMeta = call_user_func(["{$relClassName}Meta", 'getInstance']);

			$tblRight = DBCommand::qC( $RelMeta::getDBTable() );

			$query['join'][] = "LEFT JOIN `{$rel['table']}` rel_t"
				. " ON `{$tblLeft}`.`{$rel['join']['reference']}` = rel_t.`{$rel['join']['column']}`";
			$query['join'][] = "LEFT JOIN {$tblRight}"
				. " ON rel_t.`{$rel['inverse_join']['column']}` = {$tblRight}.`{$rel['inverse_join']['reference']}`";

			$query['where'] = array_merge(
				$query['where'],
				$RelFetchBy->buildWhereFromMeta($RelMeta)
			);
		}

		$FetchOptions->setQueryAppendix($query);

		return static::fetch($FetchBy, $FetchOptions, $ObjectOptions);
	}

	/**
	 * Сохранение объектов со связью ManyToMany.
	 *
	 * @param  AbstractEntity  $Entity        Объект, для которого сохраняются связи
	 * @param  string          $action        Тип операции: [ Action::INSERT | Action::UPDATE ]
	 * @param  string          $relClassName  Имя класса "связанных" объектов
	 * @param  array           $rel           Описание связи
	 */
	protected static function saveRelatedObjectsManyToMany(
		AbstractEntity $Entity,
		string $action,
		string $relClassName,
		array $rel
	): void {
		$refFieldValue = $Entity->fields[ $rel['join']['reference'] ];
		if (!$refFieldValue) {
			return;
		}

		if ($action === Action::UPDATE) {
			DBCommand::delete(
				$rel['table'],
				DBCommand::qC($rel['join']['column']) . ' = ' . DBCommand::qV($refFieldValue)
			);
		}

		$relObjects = $Entity->getRelObjects($relClassName);
		if (empty($relObjects)) {
			return;
		}

		$rows = [];
		foreach ($relObjects as $objId) {
			$rows[ $rel['join']['column'] ][] = $refFieldValue;
			$rows[ $rel['inverse_join']['column'] ][] = $objId;
		}
		DBCommand::insert($rel['table'], $rows);
	}

	/**
	 * Сохранение объектов со связью OneToMany.
	 *
	 * @param  AbstractEntity  $Entity        Объект, для которого сохраняются связи
	 * @param  string          $action        Тип операции: [ Action::INSERT | Action::UPDATE ]
	 * @param  string          $relClassName  Имя класса "связанных" объектов
	 * @param  array           $rel           Описание связи
	 */
	protected static function saveRelatedObjectsOneToMany(
		AbstractEntity $Entity,
		string $action,
		string $relClassName,
		array $rel
	): void {
		$refFieldValue = $Entity->fields[ $rel['field'] ] ?? null;
		if (empty($refFieldValue)) {
			return;
		}

		if ($action === Action::UPDATE) {
			DBCommand::delete(
				call_user_func(["{$relClassName}Meta", 'getDBTable']),
				DBCommand::qC($rel['referenced_field']) . ' = ' . DBCommand::qV($refFieldValue)
			);
		}

		$relObjects = $Entity->getRelObjects($relClassName);
		if (empty($relObjects)) {
			return;
		}

		foreach ($relObjects as $relObjFields) {
			$RelObject = new $relClassName( $relObjFields );

			if ($RelObject->fields[ $rel['required_field'] ]) {
				$RelObject->setTrust($rel['referenced_field'], $refFieldValue);
				call_user_func([ $rel['manager'], 'add' ], $RelObject);
			}
		}
	}

	/**
	 * Сохранение "связанных" объектов.
	 *
	 * @param  AbstractEntity  $Entity  Объект, для которого сохраняются связи
	 * @param  string          $action  Тип операции: [ Action::INSERT | Action::UPDATE ]
	 */
	protected static function saveRelatedObjects(
		AbstractEntity $Entity,
		string $action
	): void {
		foreach ($Entity->getMetaInfo()->getMappingWithPermissions() as $relClassName => $rel) {
			if (!$rel['is_editable']) {
				continue;
			}

			if ($rel['relation_type'] === 'ManyToMany') {
				self::saveRelatedObjectsManyToMany($Entity, $action, $relClassName, $rel);
			} else if ($rel['relation_type'] === 'OneToMany') {
				self::saveRelatedObjectsOneToMany($Entity, $action, $relClassName, $rel);
			}
		}
	}

	/**
	 * Получение списка объектов по связи ManyToMany.
	 *
	 * @param   AbstractEntity  $Entity         Объект
	 * @param   string          $relClassName   Имя класса "связанных" объектов
	 * @param  ?FetchOptions    $FetchOptions   Параметры выборки :
	 * ~~~
	 *   array  $select     = []
	 *   int    $limit      = Cfg::DEFAULT_RECORDS_LIMIT
	 *   int    $offset     = 0
	 *   bool   $rawRecords = false
	 * ~~~
	 * @param  ?ObjectOptions   $ObjectOptions  Параметры создания объектов
	 * @return  array
	 */
	public static function getRelatedObjects(
		AbstractEntity $Entity,
		string $relClassName,
		FetchOptions $FetchOptions = null,
		ObjectOptions $ObjectOptions = null
	): array {
		$rel = $Entity->getMetaInfo()->getMapping($relClassName);

		if (!$Entity->fields[ $rel['join']['reference'] ]) {
			return [];
		}

		$rTable = call_user_func(["{$relClassName}Meta", 'getDBTable']);
		$rAlias = 't';

		is_null($FetchOptions) && $FetchOptions = new FetchOptions();
		$FetchOptions->getSelect() || $FetchOptions->setSelect(['*']);

		$dbRows = DBCommand::select([
			'select' => $FetchOptions->getSelect( $rAlias ),
			'from'   => ['rel' => $rel['table']],
			'join'   => "LEFT JOIN `{$rTable}` `{$rAlias}`"
				. " ON `rel`.`{$rel['inverse_join']['column']}` = `{$rAlias}`.`{$rel['inverse_join']['reference']}`",
			'where'  => "`rel`.`{$rel['join']['column']}` = " . DBCommand::qV($Entity->fields[ $rel['join']['reference'] ]),
			'order'  => "`{$rAlias}`.`{$rel['inverse_join']['label']}`",
			'limit'  => $FetchOptions->getLimit(),
			'offset' => $FetchOptions->getOffset()
		]);

		if (empty($dbRows)) {
			return [];
		}

		return call_user_func(
			[ $rel['manager'], 'toObjects' ],
			$dbRows,
			$ObjectOptions
		);
	}

	/**
	 * Получение списка записей по связи OneToMany.
	 *
	 * @param   AbstractEntity  $Entity        Объект
	 * @param   string          $relClassName  Имя класса "связанных" объектов
	 * @param  ?FetchOptions    $FetchOptions  Параметры выборки :
	 * ~~~
	 *   array  $select  = []
	 *   mixed  $orderBy = []
	 *   int    $limit   = Cfg::DEFAULT_RECORDS_LIMIT
	 *   int    $offset  = 0
	 * ~~~
	 * @return  array
	 */
	public static function getRelatedRows(
		AbstractEntity $Entity,
		string $relClassName,
		FetchOptions $FetchOptions = null
	): array {
		$rel = $Entity->getMetaInfo()->getMapping($relClassName, false);

		if (!$Entity->fields[ $rel['field'] ]) {
			return [];
		}

		is_null($FetchOptions) && $FetchOptions = new FetchOptions();
		$FetchOptions->getSelect() || $FetchOptions->setSelect(['*']);

		return DBCommand::select([
			'select' => $FetchOptions->getSelect(),
			'from'   => DBCommand::qC($rel['table']),
			'where'  => [[
				'clause' => "{$rel['referenced_field']} = {value}",
				'values' => [$Entity->fields[ $rel['field'] ]]
			]],
			'order'  => $FetchOptions->getOrderBy(),
			'limit'  => $FetchOptions->getLimit(),
			'offset' => $FetchOptions->getOffset()
		]);
	}

	/**
	 * Получение редактируемой таблицы объектов со связью ManyToMany.
	 *
	 * @see     RelationshipsManagerTrait::getHtmlForSingleObjForm()
	 * @param   AbstractEntity  $Entity
	 * @param   string          $action
	 * @param   array           $mapping  Описания связей
	 * @return  string
	 */
	protected static function getHtmlForSingleObjFormManyToMany(
		AbstractEntity $Entity,
		string $action,
		array $mapping
	): string {
		$relationships = [];

		$ObjectOptions = (new ObjectOptions())->setForOutput();

		foreach ($mapping as $relClassName => $rel) {
			if (!$rel['is_editable'] || ($rel['is_hidden'] ?? false)) {
				continue;
			}
			if ($rel['relation_type'] !== 'ManyToMany') {
				continue;
			}

			$FetchOptions = (new FetchOptions())->setSelect([
				'id' => $rel['inverse_join']['reference'],
				'name' => $rel['inverse_join']['label']
			]);

			$relationships[ $relClassName ]['name'] = $rel['name'];
			$relationships[ $relClassName ]['objects'] = objectToArray(self::getRelatedObjects(
				$Entity,
				$relClassName,
				$FetchOptions,
				$ObjectOptions
			));
		}

		if ($relationships) {
			Sensei::assign('relationships', $relationships);
			return Sensei::getContent('entity_edit', 'related_objects');
		}

		return '';
	}

	/**
	 * Получение редактируемой таблицы объектов со связью OneToMany.
	 *
	 * @see     RelationshipsManagerTrait::getHtmlForSingleObjForm()
	 * @param   AbstractEntity  $Entity
	 * @param   string          $action
	 * @param   array           $mapping  Описания связей
	 * @return  string
	 */
	protected static function getHtmlForSingleObjFormOneToMany(
		AbstractEntity $Entity,
		string $action,
		array $mapping
	): string {
		$html = '';

		$ObjectOptions = (new ObjectOptions())->setForOutput();

		foreach ($mapping as $relClassName => $rel) {
			if (!$rel['is_editable'] || ($rel['is_hidden'] ?? false)) {
				continue;
			}
			if ($rel['relation_type'] !== 'OneToMany') {
				continue;
			}

			$reference = [ $rel['referenced_field'] => $Entity->fields[ $rel['field'] ] ];

			if ($action === Action::UPDATE) {
				$RelObjects = call_user_func(
					[ $rel['manager'], 'fetch' ],
					(new FetchBy())->and($reference),
					null,
					$ObjectOptions
				);
			} else {
				$RelObjects = [];
			}

			$RelObjects[] = new $relClassName( $reference, $ObjectOptions );

			$html .= EntityRender::multiObjForm(
				$RelObjects,
				$action,
				[
					'namePrefix' => $rel['fields_prefix'],
					'tblTitle' => $rel['name'],
					'withButtons' => true
				]
			);
		}

		return $html;
	}

	/**
	 * Добавляются редактируемые списки "связанных" объектов.
	 *
	 * @see     EntityManager::getHtmlForSingleObjForm()
	 * @param   AbstractEntity  $Entity  Объект, для которого генерируется HTML-форма
	 * @param  ?string          $action  Тип операции, для которой нужна форма
	 * @return  string
	 */
	public static function getHtmlForSingleObjForm(
		AbstractEntity $Entity,
		?string $action = null
	): string {
		if ($action === Action::SEARCH) {
			return '';
		}

		$mapping = $Entity->getMetaInfo()->getMappingWithPermissions();

		return self::getHtmlForSingleObjFormManyToMany($Entity, $action, $mapping)
			 . self::getHtmlForSingleObjFormOneToMany($Entity, $action, $mapping);
	}

	/**
	 * Поиск полей объектов для предложения сделать их "связанными" (ManyToMany).
	 *
	 * @param   string        $relClassName  Имя класса "связанных" объектов
	 * @param   string        $nameLike      Значение для поиска по названию
	 * @param   array         $idNot         Список ID, которые надо исключить из поиска
	 * @param  ?FetchOptions  $FetchOptions  Параметры выборки :
	 * ~~~
	 *   int  $limit = Cfg::DEFAULT_RECORDS_LIMIT
	 * ~~~
	 * @return  array
	 */
	public static function findFieldsRelatedObjects(
		string $relClassName,
		string $nameLike,
		array $idNot = [],
		FetchOptions $FetchOptions = null
	): array {
		$className = 'C' . substr(__CLASS__, 0, -7); // 7 == strlen('Manager')
		ClassLoader::loadClass($className);
		ClassLoader::loadClass($relClassName);

		$rel = call_user_func([$className . 'Meta', 'getMapping'], $relClassName);

		$select = [
			'id' => $rel['inverse_join']['reference'],
			'name' => $rel['inverse_join']['label']
		];

		$relCol = DBCommand::qC($select['id']);
		$relLabel = DBCommand::qC($select['name']);
		$nameLike = DBCommand::eV($nameLike);
		$where = "{$relLabel} LIKE ('%{$nameLike}%')";
		$idNot = array_filter($idNot, 'is_numeric');
		if (count($idNot) > 1) {
			$where .= " AND {$relCol} NOT IN (" . arrayToStr($idNot, ',', 'DBCommand::qV') . ')';
		} elseif (isset($idNot[0])) {
			$where .= " AND {$relCol} <> " . DBCommand::qV($idNot[0]);
		}

		is_null($FetchOptions) && $FetchOptions = new FetchOptions();

		return DBCommand::select([
			'select' => [$select],
			'from'   => DBCommand::qC( call_user_func(["{$relClassName}Meta", 'getDBTable']) ),
			'where'  => $where,
			'order'  => $relLabel,
			'limit'  => $FetchOptions->getLimit()
		]);
	}
}
