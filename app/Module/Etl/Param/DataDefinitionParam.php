<?php

declare(strict_types=1);

namespace Platine\App\Module\Etl\Param;

use Platine\App\Param\AppParam;
use Platine\Orm\Entity;

/**
* @class DataDefinitionParam
* @package Platine\App\Module\Etl\Param
* @template TEntity as Entity
*/
class DataDefinitionParam extends AppParam
{
    /**
    * The name field
    * @var string
    */
    protected string $name = '';

    /**
    * The description field
    * @var string
    */
    protected string $description = '';

    /**
    * The model field
    * @var string
    */
    protected string $model = '';

    /**
    * The extractor field
    * @var string
    */
    protected string $extractor = '';

    /**
    * The transformer field
    * @var string
    */
    protected string $transformer = '';

    /**
    * The loader field
    * @var string
    */
    protected string $loader = '';

    /**
    * The direction field
    * @var string
    */
    protected string $direction = '';

    /**
    * The field separator field
    * @var string
    */
    protected string $fieldSeparator = '';

    /**
    * The text delimiter field
    * @var string
    */
    protected string $textDelimiter = '';

    /**
    * The escape char field
    * @var string
    */
    protected string $escapeChar = '';

    /**
    * The status field
    * @var string
    */
    protected string $status = '';

    /**
    * The header field
    * @var string
    */
    protected string $header = '';

    /**
    * The extension field
    * @var string
    */
    protected string $extension = '';

    /**
    * The filter field
    * @var string
    */
    protected string $filter = '';


    /**
    * @param TEntity $entity
    * @return $this
    */
    public function fromEntity(Entity $entity): self
    {
        $this->name = $entity->name;
        $this->extension = (string) $entity->extension;
        $this->filter = (string) $entity->filter;
        $this->header = $entity->header;
        $this->status = $entity->status;
        $this->description = (string) $entity->description;
        $this->model = (string) $entity->model;
        $this->extractor = $entity->extractor;
        $this->transformer = (string) $entity->transformer;
        $this->loader = $entity->loader;
        $this->direction = $entity->direction;
        $this->fieldSeparator = (string) $entity->field_separator;
        $this->textDelimiter = (string) $entity->text_delimiter;
        $this->escapeChar = (string) $entity->escape_char;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getFilter(): string
    {
        return $this->filter;
    }

    /**
     *
     * @param string $filter
     * @return $this
     */
    public function setFilter(string $filter): self
    {
        $this->filter = $filter;
        return $this;
    }


    /**
     *
     * @return string
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     *
     * @param string $extension
     * @return $this
     */
    public function setExtension(string $extension): self
    {
        $this->extension = $extension;
        return $this;
    }


    /**
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     *
     * @return string
     */
    public function getHeader(): string
    {
        return $this->header;
    }

    /**
     *
     * @param string $status
     * @return $this
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     *
     * @param string $header
     * @return $this
     */
    public function setHeader(string $header): self
    {
        $this->header = $header;
        return $this;
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
    * Return the description value
    * @return string
    */
    public function getDescription(): string
    {
        return $this->description;
    }

   /**
    * Return the model value
    * @return string
    */
    public function getModel(): string
    {
        return $this->model;
    }

   /**
    * Return the extractor value
    * @return string
    */
    public function getExtractor(): string
    {
        return $this->extractor;
    }

   /**
    * Return the transformer value
    * @return string
    */
    public function getTransformer(): string
    {
        return $this->transformer;
    }

   /**
    * Return the loader value
    * @return string
    */
    public function getLoader(): string
    {
        return $this->loader;
    }

   /**
    * Return the direction value
    * @return string
    */
    public function getDirection(): string
    {
        return $this->direction;
    }

   /**
    * Return the field separator value
    * @return string
    */
    public function getFieldSeparator(): string
    {
        // TODO can have some char that is escaped
        return html_entity_decode($this->fieldSeparator);
    }

   /**
    * Return the text delimiter value
    * @return string
    */
    public function getTextDelimiter(): string
    {
        return html_entity_decode($this->textDelimiter);
    }

   /**
    * Return the escape char value
    * @return string
    */
    public function getEscapeChar(): string
    {
        return html_entity_decode($this->escapeChar);
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
    * Set the model value
    * @param string $model
    * @return $this
    */
    public function setModel(string $model): self
    {
        $this->model = $model;

        return $this;
    }

   /**
    * Set the extractor value
    * @param string $extractor
    * @return $this
    */
    public function setExtractor(string $extractor): self
    {
        $this->extractor = $extractor;

        return $this;
    }

   /**
    * Set the transformer value
    * @param string $transformer
    * @return $this
    */
    public function setTransformer(string $transformer): self
    {
        $this->transformer = $transformer;

        return $this;
    }

   /**
    * Set the loader value
    * @param string $loader
    * @return $this
    */
    public function setLoader(string $loader): self
    {
        $this->loader = $loader;

        return $this;
    }

   /**
    * Set the direction value
    * @param string $direction
    * @return $this
    */
    public function setDirection(string $direction): self
    {
        $this->direction = $direction;

        return $this;
    }

   /**
    * Set the field separator value
    * @param string $fieldSeparator
    * @return $this
    */
    public function setFieldSeparator(string $fieldSeparator): self
    {
        $this->fieldSeparator = $fieldSeparator;

        return $this;
    }

   /**
    * Set the text delimiter value
    * @param string $textDelimiter
    * @return $this
    */
    public function setTextDelimiter(string $textDelimiter): self
    {
        $this->textDelimiter = $textDelimiter;

        return $this;
    }

   /**
    * Set the escape char value
    * @param string $escapeChar
    * @return $this
    */
    public function setEscapeChar(string $escapeChar): self
    {
        $this->escapeChar = $escapeChar;

        return $this;
    }
}
