<?php
/**
 * Параметры создания объекта [[AbstractEntity]].
 *
 * @author Dmitriy Lunin
 */
class ObjectOptions
{
    /**
     * Внедрить дополнительные данные объекта?
     *
     * @bool
     */
    protected $withExtraData = false;

    /**
     * Сформировать значения полей, пригодные для вывода?
     *
     * @bool
     */
    protected $forOutput = false;

    /**
     * Пропустить проверку корректности типа значений полей?
     *
     * @bool
     */
    protected $skipValidation = false;

    /**
     * Показать значения полей из [[$Meta::SECRET_FIELDS]]?
     *
     * @bool
     */
    protected $showSensitive = false;

    /**
     * @see     ObjectOptions::withExtraData
     * @param   bool  $withExtraData
     * @return  ObjectOptions
     */
    public function setWithExtraData(bool $withExtraData = true): ObjectOptions
    {
        $this->withExtraData = $withExtraData;
        return $this;
    }

    /**
     * @see     ObjectOptions::withExtraData
     * @return  bool
     */
    public function getWithExtraData(): bool
    {
        return $this->withExtraData;
    }

    /**
     * @see     ObjectOptions::forOutput
     * @param   bool  $forOutput
     * @return  ObjectOptions
     */
    public function setForOutput(bool $forOutput = true): ObjectOptions
    {
        $this->forOutput = $forOutput;
        return $this;
    }

    /**
     * @see     ObjectOptions::forOutput
     * @return  bool
     */
    public function getForOutput(): bool
    {
        return $this->forOutput;
    }

    /**
     * @see     ObjectOptions::skipValidation
     * @param   bool  $skipValidation
     * @return  ObjectOptions
     */
    public function setSkipValidation(bool $skipValidation = true): ObjectOptions
    {
        $this->skipValidation = $skipValidation;
        return $this;
    }

    /**
     * @see     ObjectOptions::skipValidation
     * @return  bool
     */
    public function getSkipValidation(): bool
    {
        return $this->skipValidation;
    }

    /**
     * @see     ObjectOptions::showSensitive
     * @param   bool  $showSensitive
     * @return  ObjectOptions
     */
    public function setShowSensitive(bool $showSensitive = true): ObjectOptions
    {
        $this->showSensitive = $showSensitive;
        return $this;
    }

    /**
     * @see     ObjectOptions::showSensitive
     * @return  bool
     */
    public function getShowSensitive(): bool
    {
        return $this->showSensitive;
    }
}