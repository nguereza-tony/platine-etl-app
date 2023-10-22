<?php

declare(strict_types=1);

namespace Platine\App\Module\Etl\Param;

use Platine\App\Param\AppParam;
use Platine\Orm\Entity;

/**
* @class DataDefinitionImportParam
* @package Platine\App\Module\Etl\Param
* @template TEntity as Entity
*/
class DataDefinitionImportParam extends AppParam
{
    /**
    * The description field
    * @var string
    */
    protected string $description = '';

    /**
    * The status field
    * @var string
    */
    protected string $status = '';

    /**
    * The data definition field
    * @var string
    */
    protected string $dataDefinition = '';


    /**
    * @param TEntity $entity
    * @return $this
    */
    public function fromEntity(Entity $entity): self
    {
        $this->description = (string) $entity->description;
        $this->status = $entity->status;
        $this->dataDefinition = (string) $entity->data_definition_id;

        return $this;
    }

    /**
    * Return the description value
    * @return string
    */
    public function getDescription(): string
    {
        return $this->description;
    }

   /**
    * Return the status value
    * @return string
    */
    public function getStatus(): string
    {
        return $this->status;
    }

   /**
    * Return the data definition value
    * @return string
    */
    public function getDataDefinition(): string
    {
        return $this->dataDefinition;
    }


    /**
    * Set the description value
    * @param string $description
    * @return $this
    */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

   /**
    * Set the status value
    * @param string $status
    * @return $this
    */
    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

   /**
    * Set the data definition value
    * @param string $dataDefinition
    * @return $this
    */
    public function setDataDefinition(string $dataDefinition): self
    {
        $this->dataDefinition = $dataDefinition;

        return $this;
    }
}
