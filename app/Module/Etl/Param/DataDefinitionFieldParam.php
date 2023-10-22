<?php

declare(strict_types=1);

namespace Platine\App\Module\Etl\Param;

use Platine\App\Param\AppParam;
use Platine\Orm\Entity;

/**
* @class DataDefinitionFieldParam
* @package Platine\App\Module\Etl\Param
* @template TEntity as Entity
*/
class DataDefinitionFieldParam extends AppParam
{
    /**
    * The field field
    * @var string
    */
    protected string $field = '';

    /**
    * The name field
    * @var string
    */
    protected string $name = '';

    /**
    * The column field
    * @var string
    */
    protected string $column = '';

    /**
    * The position field
    * @var string
    */
    protected string $position = '1';

    /**
    * The default value field
    * @var string
    */
    protected string $defaultValue = '';

    /**
    * The parent field
    * @var string
    */
    protected string $parent = '';


    /**
    * @param TEntity $entity
    * @return $this
    */
    public function fromEntity(Entity $entity): self
    {
        $this->field = $entity->field;
        $this->name = $entity->name;
        $this->column = (string) $entity->column;
        $this->position = (string) $entity->position;
        $this->defaultValue = (string) $entity->default_value;
        $this->parent = (string) $entity->parent_id;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getColumn(): string
    {
        return $this->column;
    }

    /**
     *
     * @param string $column
     * @return $this
     */
    public function setColumn(string $column): self
    {
        $this->column = $column;
        return $this;
    }


    /**
    * Return the field value
    * @return string
    */
    public function getField(): string
    {
        return $this->field;
    }

   /**
    * Return the name value
    * @return string
    */
    public function getName(): string
    {
        return $this->name;
    }

   /**
    * Return the position value
    * @return string
    */
    public function getPosition(): string
    {
        return $this->position;
    }

   /**
    * Return the default value value
    * @return string
    */
    public function getDefaultValue(): string
    {
        return $this->defaultValue;
    }

   /**
    * Return the parent value
    * @return string
    */
    public function getParent(): string
    {
        return $this->parent;
    }


    /**
    * Set the field value
    * @param string $field
    * @return $this
    */
    public function setField(string $field): self
    {
        $this->field = $field;

        return $this;
    }

   /**
    * Set the name value
    * @param string $name
    * @return $this
    */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

   /**
    * Set the position value
    * @param string $position
    * @return $this
    */
    public function setPosition(string $position): self
    {
        $this->position = $position;

        return $this;
    }

   /**
    * Set the default value value
    * @param string $defaultValue
    * @return $this
    */
    public function setDefaultValue(string $defaultValue): self
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

   /**
    * Set the parent value
    * @param string $parent
    * @return $this
    */
    public function setParent(string $parent): self
    {
        $this->parent = $parent;

        return $this;
    }
}
