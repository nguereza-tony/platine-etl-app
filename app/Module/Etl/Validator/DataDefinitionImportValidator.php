<?php

declare(strict_types=1);

namespace Platine\App\Module\Etl\Validator;

use Platine\App\Module\Etl\Enum\DataDefinitionImportStatus;
use Platine\App\Module\Etl\Param\DataDefinitionImportParam;
use Platine\Framework\Form\Validator\AbstractValidator;
use Platine\Lang\Lang;
use Platine\Validator\Rule\InList;
use Platine\Validator\Rule\MinLength;
use Platine\Validator\Rule\NotEmpty;

/**
* @class DataDefinitionImportValidator
* @package Platine\App\Module\Etl\Validator
* @template TEntity as \Platine\Orm\Entity
*/
class DataDefinitionImportValidator extends AbstractValidator
{
    /**
    * The parameter instance
    * @var DataDefinitionImportParam<TEntity>
    */
    protected DataDefinitionImportParam $param;

    /**
    * Create new instance
    * @param DataDefinitionImportParam<TEntity> $param
    * @param Lang $lang
    */
    public function __construct(DataDefinitionImportParam $param, Lang $lang)
    {
        parent::__construct($lang);
        $this->param = $param;
    }

    /**
    * {@inheritdoc}
    */
    public function setValidationData(): void
    {
        $this->addData('description', $this->param->getDescription());
        $this->addData('status', $this->param->getStatus());
        $this->addData('data_definition', $this->param->getDataDefinition());
    }

    /**
    * {@inheritdoc}
    */
    public function setValidationRules(): void
    {
        $this->addRules('description', [
             new MinLength(2),
        ]);

        $this->addRules('status', [
            new NotEmpty(),
            new InList([
                DataDefinitionImportStatus::CANCELLED,
                DataDefinitionImportStatus::PROCESSED,
                DataDefinitionImportStatus::PENDING,
                DataDefinitionImportStatus::ERROR,
            ]),
        ]);

        $this->addRules('data_definition', [
            new NotEmpty(),
        ]);
    }
}
