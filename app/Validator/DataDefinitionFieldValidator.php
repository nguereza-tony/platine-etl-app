<?php

declare(strict_types=1);

namespace Platine\App\Validator;

use Platine\App\Param\DataDefinitionFieldParam;
use Platine\Framework\Form\Validator\AbstractValidator;
use Platine\Lang\Lang;
use Platine\Validator\Rule\AlphaNumericDash;
use Platine\Validator\Rule\MinLength;
use Platine\Validator\Rule\Natural;
use Platine\Validator\Rule\NotEmpty;
use Platine\Validator\Rule\Number;

/**
* @class DataDefinitionFieldValidator
* @package Platine\App\Validator
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
    * Create new instance
    * @param DataDefinitionFieldParam<TEntity> $param
    * @param Lang $lang
    */
    public function __construct(DataDefinitionFieldParam $param, Lang $lang)
    {
        parent::__construct($lang);
        $this->param = $param;
    }

    /**
    * {@inheritdoc}
    */
    public function setValidationData(): void
    {
        $this->addData('field', $this->param->getField());
        $this->addData('name', $this->param->getName());
        $this->addData('position', $this->param->getPosition());
        $this->addData('default_value', $this->param->getDefaultValue());
        $this->addData('parent', $this->param->getParent());
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

        $this->addRules('parent', [
            new Number(),
        ]);
    }
}
