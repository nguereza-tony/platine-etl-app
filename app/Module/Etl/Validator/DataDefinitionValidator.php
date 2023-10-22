<?php

declare(strict_types=1);

namespace Platine\App\Module\Etl\Validator;

use Platine\App\Module\Etl\Enum\DataDefinitionDirection;
use Platine\App\Enum\YesNoStatus;
use Platine\App\Helper\StatusList;
use Platine\App\Module\Etl\Param\DataDefinitionParam;
use Platine\Framework\Form\Validator\AbstractValidator;
use Platine\Lang\Lang;
use Platine\Validator\Rule\InList;
use Platine\Validator\Rule\MinLength;
use Platine\Validator\Rule\NotEmpty;

/**
* @class DataDefinitionValidator
* @package Platine\App\Module\Etl\Validator
* @template TEntity as \Platine\Orm\Entity
*/
class DataDefinitionValidator extends AbstractValidator
{
    /**
    * The parameter instance
    * @var DataDefinitionParam<TEntity>
    */
    protected DataDefinitionParam $param;

    /**
     *
     * @var StatusList
     */
    protected StatusList $statusList;

    /**
    * Create new instance
    * @param DataDefinitionParam<TEntity> $param
    * @param Lang $lang
    * @param StatusList $statusList
    */
    public function __construct(DataDefinitionParam $param, Lang $lang, StatusList $statusList)
    {
        parent::__construct($lang);
        $this->param = $param;
        $this->statusList = $statusList;
    }

    /**
    * {@inheritdoc}
    */
    public function setValidationData(): void
    {
        $this->addData('name', $this->param->getName());
        $this->addData('status', $this->param->getStatus());
        $this->addData('extension', $this->param->getExtension());
        $this->addData('header', $this->param->getHeader());
        $this->addData('description', $this->param->getDescription());
        $this->addData('model', $this->param->getModel());
        $this->addData('extractor', $this->param->getExtractor());
        $this->addData('transformer', $this->param->getTransformer());
        $this->addData('loader', $this->param->getLoader());
        $this->addData('filter', $this->param->getFilter());
        $this->addData('direction', $this->param->getDirection());
        $this->addData('field_separator', $this->param->getFieldSeparator());
        $this->addData('text_delimiter', $this->param->getTextDelimiter());
        $this->addData('escape_char', $this->param->getEscapeChar());
    }

    /**
    * {@inheritdoc}
    */
    public function setValidationRules(): void
    {
        $this->addRules('name', [
            new NotEmpty(),
            new MinLength(2),
        ]);

        $this->addRules('description', [
            new MinLength(2),
        ]);

        $this->addRules('model', [
            new MinLength(2),
        ]);

        $this->addRules('extractor', [
            new NotEmpty(),
            new InList(array_keys($this->statusList->getDataDefinitionExtractor())),
        ]);

        $this->addRules('transformer', [
            new MinLength(2),
            new InList(array_keys($this->statusList->getDataDefinitionTransformer())),
        ]);

        $this->addRules('filter', [
            new MinLength(2),
            new InList(array_keys($this->statusList->getDataDefinitionFilter())),
        ]);

        $this->addRules('loader', [
            new NotEmpty(),
            new InList(array_keys($this->statusList->getDataDefinitionLoader())),
        ]);

        $this->addRules('direction', [
            new NotEmpty(),
            new InList([
                DataDefinitionDirection::IN,
                DataDefinitionDirection::OUT,
            ]),
        ]);

        $this->addRules('header', [
            new NotEmpty(),
            new InList([
                YesNoStatus::YES,
                YesNoStatus::NO,
            ]),
        ]);

        $this->addRules('extension', [
            new NotEmpty(),
        ]);

        $this->addRules('field_separator', [

        ]);

        $this->addRules('text_delimiter', [

        ]);

        $this->addRules('escape_char', [

        ]);
    }
}
