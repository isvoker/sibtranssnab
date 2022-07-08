<?php
/**
 * Поддержка уникальности значений полей на уровне управления сущностями.
 *
 * @author Dmitriy Lunin
 */
trait UniqueFieldsProcessorTrait
{
	/**
	 * Генерация набора критериев поиска в формате [[DBQueryBuilder::where]] #2
	 * для выборки записи по уникальному ключу.
	 *
	 * @param   AbstractEntity      $Object  Объект с заданными значениями уникальных полей
	 * @param   AbstractEntityMeta  $Meta
	 * @return  array
	 */
	protected static function buildUniqueFieldsRestricts(
		AbstractEntity $Object,
		AbstractEntityMeta $Meta
	): array {
		$where = [];

		foreach ($Meta::UNIQUE_KEY as $field) {
			if ($Object->fields[ $field ] === null) {
				throw new EntityEditEx( EntityEditEx::UK_FIELD_IS_EMPTY, $field );
			}

			$where[] = [
				'oper' => 'AND',
				'clause' => ":{$field}: = {value}",
				'values' => [$Object->fields[ $field ]]
			];
		}

		if (empty($where)) {
			throw new EntityEditEx( EntityEditEx::UK_IS_EMPTY );
		}

		return $where;
	}

	/**
	 * Проверка наличия в БД записи со значением уникального ключа как у заданного объекта.
	 * Как правило, выполняется перед вставкой в БД.
	 *
	 * @param   AbstractEntity  $Object
	 * @return  bool            Значения уникальны ? TRUE : FALSE
	 */
	public static function isUniqueFields(AbstractEntity $Object): bool
	{
		$Meta = $Object->getMetaInfo();

		return !DBCommand::select([
			'select' => '1',
			'from'   => DBCommand::qC( $Meta::getDBTable() ),
			'where'  => self::buildUniqueFieldsRestricts($Object, $Meta)
		], DBCommand::OUTPUT_FIRST_CELL);
	}

	/**
	 * Сравнение значений уникальных полей двух объектов.
	 * Как правило, выполняется перед обновлением в БД.
	 *
	 * @param   AbstractEntity  $Object
	 * @param   AbstractEntity  $NewObject
	 * @return  bool            Значения совпадают ? TRUE : FALSE
	 */
	public static function compareUniqueFields(
		AbstractEntity $Object,
		AbstractEntity $NewObject
	): bool {
		foreach ($Object->getMetaInfo()::UNIQUE_KEY as $field) {
			if ($NewObject->fields[ $field ] != $Object->fields[ $field ]) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Восстановление объекта по значению полей уникального ключа.
	 *
	 * @param  AbstractEntity  $Object
	 * @param  string          $notFoundEx  Класс исключения типа [[EntityNotFoundEx]]
	 * @param ?ObjectOptions   $Options     Параметры создания объекта :
	 * ~~~
	 *   bool  $withExtraData  = false
	 *   bool  $forOutput      = false
	 *   bool  $showSensitive  = false
	 * ~~~
	 * @return  AbstractEntity
	 */
	public static function getByUniqueFields(
		AbstractEntity $Object,
		string $notFoundEx = '',
		ObjectOptions $Options = null
	): AbstractEntity {
		$Meta = $Object->getMetaInfo();

		$fields = DBCommand::select([
			'from'  => DBCommand::qC( $Meta::getDBTable() ),
			'where' => self::buildUniqueFieldsRestricts($Object, $Meta)
		], DBCommand::OUTPUT_FIRST_ROW);

		if (empty($fields)) {
			if ($notFoundEx === '') {
				$notFoundEx = 'EntityNotFoundEx';
			}
			throw new $notFoundEx();
		}

		$className = get_class($Object);

		$Options === null && $Options = new ObjectOptions();
		$Options->setSkipValidation();

		return new $className( $fields, $Options );
	}
}
