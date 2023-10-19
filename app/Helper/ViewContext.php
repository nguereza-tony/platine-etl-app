<?php

declare(strict_types=1);

namespace Platine\App\Helper;

use ArrayAccess;

/**
 * @class ViewContext
 * @package Platine\App\Helper
 * @template T
 * @implements ArrayAccess<string, mixed>
 */
class ViewContext implements ArrayAccess
{
    /**
     * The context data
     * @var array<string, mixed>
     */
    protected array $data = [];

    /**
     * Return all data
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Set the context data
     * @param string $name
     * @param mixed $value
     * @return self
     */
    public function set(string $name, $value): self
    {
        $this->data[$name] = $value;

        return $this;
    }

    /**
     *
     * @param string $name
     * @return mixed|null
     */
    public function get(string $name)
    {
        return $this->data[$name] ?? null;
    }

    /**
     * {@inheritodc}
     */
    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]);
    }

    /**
     * {@inheritodc}
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * {@inheritodc}
     */
    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * {@inheritodc}
     */
    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }
}
