<?php
/**
 * Параметры поиска записей в БД методом [[EntityManager::baseFetch()]].
 *
 * @author Dmitriy Lunin
 */
class FetchBy
{
	/**
	 * Критерии поиска по основным полям [Сущности].
	 *
	 * @array
	 */
	protected $restricts = [];

	/**
	 * Расширенные критерии поиска.
	 *
	 * @array
	 */
	protected $extraWhere = [];

	/**
	 * @param   string  $field
	 * @return  mixed|null
	 */
	public function getPlain(string $field)
	{
		return isset($this->restricts[ $field ])
			? $this->restricts[ $field ]['value']
			: null;
	}

	/**
	 * @param   string  $field
	 * @return  mixed|null
	 */
	public function extractValue(string $field)
	{
		$value = $this->getPlain($field);
		unset($this->restricts[ $field ]);

		return $value;
	}

	/**
	 * @param   array  $plainRestricts
	 * @return  FetchBy
	 */
	public function and(array $plainRestricts): FetchBy
	{
		foreach ($plainRestricts as $field => $value) {
			$this->restricts[ $field ] = [
				'operator' => 'AND',
				'value' => $value
			];
		}

		return $this;
	}

	/**
	 * @param   array  $restricts
	 * @return  FetchBy
	 */
	public function or(array $restricts): FetchBy
	{
		foreach ($restricts as $field => $value) {
			$this->restricts[ $field ] = [
				'operator' => 'OR',
				'value' => $value
			];
		}

		return $this;
	}

	/**
	 * Добавление произвольного критерия поиска.
	 *
	 * ВНИМАНИЕ!
	 * Эти значения не валидируются на основе Метаинформации!
	 *
	 * @param   array  $where  {@link DBQueryBuilder::where()}
	 * @return  FetchBy
	 */
	public function setExtraWhere(array $where): FetchBy
	{
		$this->extraWhere = array_merge($this->extraWhere, $where);

		return $this;
	}

	/**
	 * Генерация набора критериев поиска в формате [[DBQueryBuilder::where()]] #2
	 *
	 * @param   AbstractEntityMeta  $Meta  Метаинформация
	 * @return  array
	 */
	public function buildWhereFromMeta(AbstractEntityMeta $Meta): array
	{
		if (empty($this->restricts)) {
			return $this->extraWhere;
		}

		$tbl = DBCommand::qC( $Meta::getDBTable() ) . '.';
		$where = [];

		foreach ($Meta->getFieldsInfo() as $field => $FieldInfo) {
			if ( !$FieldInfo->getSearchMode() ) {
				continue;
			}

			if ($FieldInfo->searchIsInterval()) {
				$value = [
					'min' => $this->getPlain("{$field}_min"),
					'max' => $this->getPlain("{$field}_max")
				];
				if ( is_null($value['min']) && is_null($value['max']) ) {
					continue;
				}

				$operator = $this->restricts[ "{$field}_min" ]['operator']
					?? $this->restricts[ "{$field}_max" ]['operator'];
			} else {
				$value = $this->getPlain($field);
				if (is_null($value)) {
					continue;
				}

				$operator = $this->restricts[ $field ]['operator'];
			}

			$expr = ['oper' => $operator];
			$column = $tbl . DBCommand::qC($field);

			if ($FieldInfo->searchIsSimple()) {

				$value = AbstractEntity::validateFieldValue($FieldInfo, $value);
				if (!empty($value)) {
					$expr['clause'] = "LOWER({$column}) LIKE LOWER('%{value}%')";
					$expr['values'] = [$value => true];
					$where[] = $expr;
				}

			} elseif ($FieldInfo->searchIsStrict()) {

				$isNot = is_string($value) && mb_strpos($value, '!') === 0;

				if ($isNot) {
					$value = mb_substr($value, 1);
				}

				if ($value === 'NULL') {
					$expr['clause'] = "{$column} IS" . ($isNot ? ' NOT' : '') . ' NULL';
					$where[] = $expr;
				} else {
					if ($value === Cfg::DEFAULT_EMPTY_VALUE) {
						$value = '';
					} else {
						$value = AbstractEntity::validateFieldValue($FieldInfo, $value);
					}

					if ($value !== null) {
						$expr['clause'] = $column . ($isNot ? ' <> ' : ' = ') . DBCommand::qV($value);
						$where[] = $expr;
					}
				}

			} elseif ($FieldInfo->searchIsBoolean()) {

				$value = AbstractEntity::validateFieldValue($FieldInfo, $value);
				if ($value !== null) {
					$expr['clause'] = "{$column} = " . ($value ? 'TRUE' : 'FALSE');
					$where[] = $expr;
				}

			} elseif ($FieldInfo->searchIsList()) {

				if (is_string($value) || is_numeric($value)) {
					if (strpos($value, '|') === 0) {
						$value = explode('|', $value);
						unset($value[0]);
					} else {
						$expr['clause'] = "{$column} = " . DBCommand::qV($value);
						$where[] = $expr;
					}
				}

				if (is_array($value) && $value) {
					$value = array_filter($value, 'is_numeric');

					if ($FieldInfo->getDicMultiMax()) {
						$expr['group'] = [];
						foreach ($value as $i => $code) {
							$code = DBCommand::eV($code);
							$expr['group'][] = ['oper' => 'OR', 'clause' => "{$column} LIKE '%|{$code}|%'"];
							$expr['group'][] = ['oper' => 'OR', 'clause' => "{$column} LIKE '%|{$code}'"];
						}
					} elseif (count($value) > 1) {
						$list = arrayToStr($value, ',', 'DBCommand::qV');
						$expr['clause'] = "{$column} IN ({$list})";
					} else {
						$expr['clause'] = "{$column} = " . DBCommand::qV(array_shift($value));
					}

					$where[] = $expr;
				}

			} elseif ($FieldInfo->searchIsInterval()) {

				$minValue = AbstractEntity::validateFieldValue($FieldInfo, $value['min']);
				if ($minValue) {
					$expr['clause'] = "{$column} >= " . DBCommand::qV($minValue);
					$where[] = $expr;
				}

				$maxValue = AbstractEntity::validateFieldValue($FieldInfo, $value['max']);
				if ($maxValue) {
					$expr['clause'] = "{$column} <= " . DBCommand::qV($maxValue);
					$where[] = $expr;
				}

			} elseif ($FieldInfo->searchIsStatuses()) {

				if (is_array($value)) {
					$neededStatus = 1;
					foreach ($value as $name => $need) {
						if ($code = $Meta::getStatusCode($name)) {
							if ($need) {
								$neededStatus *= $code;
							} else {
								$expr['clause'] = "NOT {$column} % {$code} = 0";
								$where[] = $expr;
							}
						}
					}
					$expr['clause'] = "{$column} % {$neededStatus} = 0";
					$where[] = $expr;
				}

			}
		}

		return array_merge($where, $this->extraWhere);
	}
}
