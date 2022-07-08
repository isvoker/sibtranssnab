<?php
/**
 * Базовая реализация класса [[CUser]].
 *
 * Не меняйте и не используйте напрямую этот класс! Для этих целей есть [[CUser]].
 *
 * @author Dmitriy Lunin
 */
class BaseCUser extends AbstractEntity
{
    use StatusesTrait;

    /**
     * Формирование информации о группах, включающих учётную запись.
     */
    public function pullGroups(): void
    {
        $this->privateExtraData['grp'] = [];
        $this->privateExtraData['grp_ids_str'] = '';
        $this->extraData['grp_list'] = '';

        $userGroups = DBCommand::select([
            'select' => ['g' => ['id', 'name']],
            'from'   => ['g' => CUserMeta::getDBTableGrp()],
            'join'   => 'INNER JOIN ' . CUserMeta::getDBTableUsrGrp() . ' ug ON g.id = ug.group_id',
            'where'  => 'ug.user_id = ' . DBCommand::qV($this->id)
        ]);
        foreach ($userGroups as $i => $grp) {
            if ($i) {
                $this->privateExtraData['grp_ids_str'] .= ', ';
                $this->extraData['grp_list'] .= ', ';
            }
            $this->privateExtraData['grp'][ $grp['name'] ] = true;
            $this->privateExtraData['grp_ids_str'] .= $grp['id'];
            $this->extraData['grp_list'] .= $grp['name'];
        }
    }

    /** @see AbstractEntity::buildExtraData() */
    public function buildExtraData(): void
    {
        $this->pullGroups();
    }

    /**
     * Состоит ли учётная запись в заданных группах.
     *
     * @param   string|array  $groups  Имя группы или список имён групп (string|array)
     * @param   bool          $inAll   Пользователь должен состоять во всех указанных группах
     *                                 или хотя бы в одной из них?
     * @return  bool
     */
    public function isInGroup($groups, bool $inAll = true): bool
    {
        if (empty($groups)) {
            return false;
        }

        if (!array_key_exists('grp', $this->privateExtraData)) {
            $this->pullGroups();
        }

        if (empty($this->privateExtraData['grp'])) {
            return false;
        }

        if (is_string($groups)) {
            return isset( $this->privateExtraData['grp'][ $groups ] );
        }

        if (is_array($groups)) {
            if (!isset($groups[1])) {
                return isset( $this->privateExtraData['grp'][ $groups[0] ] );
            }

            foreach ($groups as $grp) {
                if (!$inAll && isset( $this->privateExtraData['grp'][ $grp ] )) {
                    return true;
                }

                if ($inAll && !isset( $this->privateExtraData['grp'][ $grp ] )) {
                    return false;
                }
            }

            return $inAll;
        }

        return false;
    }

    /**
     * Активирована ли учётная запись.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isSetStatus( CUserMeta::STATUS_CODES['active'] );
    }

    /**
     * Заблокирована ли учётная запись.
     *
     * @return bool
     */
    public function isBanned(): bool
    {
        return $this->isSetStatus( CUserMeta::STATUS_CODES['banned'] );
    }
}
