<?php

declare(strict_types=1);

namespace Platine\App\Module\Etl\Validator;

use Platine\App\Helper\StatusList;
use Platine\App\Module\Etl\Param\DataDefinitionFieldParam;
use Platine\Framework\Form\Validator\AbstractValidator;
use Platine\Lang\Lang;
use Platine\Validator\Rule\AlphaNumericDash;
use Platine\Validator\Rule\InList;
use Platine\Validator\Rule\MinLength;
use Platine\Validator\Rule\Natural;
use Platine\Validator\Rule\NotEmpty;

/**
* @class DataDefinitionFieldValidator
* @package Platine\App\Module\Etl\Validator
* @template TEntity as \Platine\Orm\Entity
*/
class DataDefinitionFieldValidator extends AbstractValidator
{
    /**
    * The parameter instance
    * @var DataDefinitionFieldParam<TEntity>
    */
    protected DataDefinitionFieldParam $param;

    /**
     *
     * @var StatusList
     */
    protected StatusList $statusList;

    /**
    * Create new instance
    * @param DataDefinitionFieldParam<TEntity> $param
    * @param Lang $lang
    * @param StatusList $statusList
    */
    public function __construct(
        DataDefinitionFieldParam $param,
        Lang $lang,
        StatusList $statusList
    ) {
        parent::__construct($lang);
        $this->param = $param;
        $this->statusList = $statusList;
    }

    /**
    * {@inheritdoc}
    */
    public function setValidationData(): void
    {
        $this->addData('field', $this->param->getField());
        $this->addData('name', $this->param->getName());
        $this->addData('column', $this->param->getColumn());
        $this->addData('transformer', $this->param->getTransformer());
        $this->addData('position', $this->param->getPosition());
        $this->addData('default_value', $this->param->getDefaultValue());
    }

    /**
    * {@inheritdoc}
    */
    public function setValidationRules(): void
    {
        $this->addRules('field', [
            new NotEmpty(),
            new MinLength(2),
            new AlphaNumericDash(),
        ]);

        $this->addRules('name', [
            new NotEmpty(),
            new MinLength(2),
        ]);

        $this->addRules('position', [
            new NotEmpty(),
            new Natural(),
        ]);

        $this->addRules('default_value', [
            new MinLength(1),
        ]);

        $this->addRules('column', [
            new MinLength(2),
            new AlphaNumericDash(),
        ]);

        $this->addRules('transformer', [
            new MinLength(2),
            new InList(array_keys($this->statusList->getDataDefinitionFieldTransformer())),
        ]);
    }
}
