<?php

declare(strict_types=1);

namespace Platine\App\Helper;

use Platine\App\Enum\FilterFieldType;
use Platine\Stdlib\Helper\Arr;
use Platine\Stdlib\Helper\Str;

/**
 * @class Filter
 * @package Platine\App\Helper
 */
class Filter
{
    /**
     * The filter fields
     * @var array<string, array<string, mixed>>
     */
    protected array $fields = [];

    /**
     * The queries parameters to be used
     * @var array<string, mixed>
     */
    protected array $params = [];

    /**
     * Ignore other filters if search filter is used
     * @var bool
     */
    protected bool $searchIgnoreFilters = true;

    /**
     * Keep these fields if search filter ignore is used
     * @var array<string>
     */
    protected array $searchIgnoreKeepFields = [];

    /**
     * The attributes to be used
     * @var array<string, mixed>
     */
    protected array $attributes = [];

    /**
     * Whether to call configure method in constructor
     * @var bool
     */
    protected bool $autoConfigure = true;

    /**
     * Create new instance
     */
    public function __construct()
    {
        if ($this->autoConfigure) {
            $this->configure();
        }
    }

    /**
     *
     * @param bool $autoConfigure
     * @return $this
     */
    public function setAutoConfigure(bool $autoConfigure): self
    {
        $this->autoConfigure = $autoConfigure;
        return $this;
    }


    /**
     * Return the attributes
     * @return array<string, mixed>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Set the attributes
     * @param array<string, mixed> $attributes
     * @return $this
     */
    public function setAttributes(array $attributes): self
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * Set one attribute
     * @param string $name
     * @param mixed $value
     * @return self
     */
    public function setAttribute(string $name, $value): self
    {
        $this->attributes[$name] = $value;
        return $this;
    }


    /**
     *
     * @param bool $searchIgnoreFilters
     * @return $this
     */
    public function setSearchIgnoreFilters(bool $searchIgnoreFilters): self
    {
        $this->searchIgnoreFilters = $searchIgnoreFilters;
        return $this;
    }

    /**
     *
     * @param array<string> $searchIgnoreKeepFields
     * @return $this
     */
    public function setSearchIgnoreKeepFields(array $searchIgnoreKeepFields): self
    {
        $this->searchIgnoreKeepFields = $searchIgnoreKeepFields;
        return $this;
    }


    /**
     * Return the queries parameters
     * @return array<string, mixed>
     */
    public function getParams(): array
    {
        $params = $this->params;
        if ($this->searchIgnoreFilters === false) {
            return $params;
        }

        if (array_key_exists('search', $params)) {
            $params = Arr::only($params, array_merge($this->searchIgnoreKeepFields, ['search']));

            return $params;
        }

        return $params;
    }

    /**
     * Set the queries parameters
     * @param array<string, mixed> $params
     * @return $this
     */
    public function setParams(array $params): self
    {
        $this->params = $params;
        return $this;
    }

    /**
     * Return parameter value or null
     * @param string $param
     * @return mixed|null
     */
    public function getParam(string $param)
    {
        return $this->params[$param] ?? null;
    }

    /**
     * Add select field
     * @param string $field
     * @param string $title
     * @param array<mixed> $values
     * @param string $default
     * @param array<string, mixed> $extras
     * @return self
     */
    public function addSelectField(
        string $field,
        string $title,
        array $values,
        string $default = '',
        array $extras = []
    ): self {
        return $this->addListField(
            $field,
            $title,
            FilterFieldType::SELECT,
            $values,
            $default,
            $extras
        );
    }

    /**
     * Add text field
     * @param string $field
     * @param string $title
     * @param string $default
     * @param array<string, mixed> $extras
     * @return self
     */
    public function addTextField(
        string $field,
        string $title,
        string $default = '',
        array $extras = []
    ): self {
        $this->addCommonField(
            $field,
            $title,
            FilterFieldType::TEXT,
            $default,
            $extras
        );

        return $this;
    }

    /**
     * Add date field
     * @param string $field
     * @param string $title
     * @param string $default
     * @param array<string, mixed> $extras
     * @return self
     */
    public function addDateField(
        string $field,
        string $title,
        string $default = '',
        array $extras = []
    ): self {
        $this->addCommonField(
            $field,
            $title,
            FilterFieldType::DATE,
            $default,
            $extras
        );

        return $this;
    }

    /**
     * Add hidden field
     * @param string $field
     * @param string $title
     * @param string $default
     * @param array<string, mixed> $extras
     * @return self
     */
    public function addHiddenField(
        string $field,
        string $title,
        string $default = '',
        array $extras = []
    ): self {
        $this->addCommonField(
            $field,
            $title,
            FilterFieldType::HIDDEN,
            $default,
            $extras
        );

        return $this;
    }

    /**
     * Configure the filter
     * @return self
     */
    public function configure(): self
    {
        return $this;
    }

    /**
     * Return the filter fields
     * @return array<string, array<string, mixed>>
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Render the filter
     * @return string
     */
    public function render(): string
    {
        if (empty($this->fields)) {
            return '';
        }

        $attributeStr = '';
        if (count($this->attributes) > 0) {
            $attributeStr = Str::toAttribute($this->attributes);
        }

        $str = sprintf(
            '<div class="text-right"><form action="" method="GET" %s data-form-type="filter">',
            $attributeStr
        );
        foreach ($this->fields as $field => $data) {
            $renderMethodName = 'render' . $data['type'];
            $render = $this->{$renderMethodName}($field) . ' &nbsp;&nbsp;';
            $str .= $render;
        }
        $str .= '<button type="submit" class="btn btn-xs btn-primary">Valider</button></form><hr /></div>';

        return $str;
    }

    /**
     * Render filter for form
     * @return string
     */
    public function form(): string
    {
        if (empty($this->fields)) {
            return '';
        }

        $attributeStr = '';
        if (count($this->attributes) > 0) {
            $attributeStr = Str::toAttribute($this->attributes);
        }

        $str = '';
        foreach ($this->fields as $field => $data) {
            $renderMethodName = 'render' . $data['type'];
            $render = $this->{$renderMethodName}($field, true);
            $str .= $render;
        }

        return $str;
    }

    /**
     * Add common field
     * @param string $field
     * @param string $title
     * @param string $type
     * @param string $default
     * @param array<string, mixed> $extras
     * @return $this
     */
    protected function addCommonField(
        string $field,
        string $title,
        string $type,
        string $default = '',
        array $extras = []
    ): self {
        $this->fields[$field] = [
          'type' => $type,
          'title' => $title,
          'value' => $default,
          'extras' => $extras,
        ];

        return $this;
    }

    /**
     * Add list field
     * @param string $field
     * @param string $title
     * @param string $type
     * @param array<mixed> $values
     * @param string $default
     * @param array<string, mixed> $extras
     * @return $this
     */
    protected function addListField(
        string $field,
        string $title,
        string $type,
        array $values = [],
        string $default = '',
        array $extras = []
    ): self {
        $this->fields[$field] = [
          'type' => $type,
          'title' => $title,
          'values' => $values,
          'value' => $default,
          'extras' => $extras,
        ];

        return $this;
    }

    /**
     * Render for text field
     * @param string $field
     * @param bool $isForm
     * @return string
     */
    protected function renderT(string $field, bool $isForm = false): string
    {
        return $this->renderTextField($field, 'text', $isForm);
    }

    /**
     * Render for hidden field
     * @param string $field
     * @param bool $isForm
     * @return string
     */
    protected function renderH(string $field, bool $isForm = false): string
    {
        return $this->renderTextField($field, 'hidden', $isForm);
    }

    /**
     * Render for select field
     * @param string $field
     * @param bool $isForm
     * @return string
     */
    protected function renderS(string $field, bool $isForm = false): string
    {
        $data = $this->fields[$field] ?? [];
        $values = $data['values'] ?? [];
        if (empty($data) || empty($values)) {
            return '';
        }

        if ($isForm) {
            $str = '<div class="form-group row">';
        } else {
            $str = '';
        }
        $title = $data['title'] ?? '';
        $label = str_replace(['[', ']'], '', $field);
        if (!empty($title)) {
            if ($isForm) {
                $str .= <<<E
                    <label for="$label" class="col-md-3 col-form-label">
                        $title:
                    </label>
                E;
            } else {
                $str .= sprintf('<label for="%s">%s:</label> &nbsp;&nbsp;', $label, $title);
            }
        }
        $extras = $data['extras'];
        $default = $data['value'];
        $keyField = $extras['key_field'] ?? 'id';
        $newLine = $extras['new_line'] ?? false;
        $formatFunction = $extras['format_function'] ?? null;
        unset(
            $extras['key_field'],
            $extras['format_function'],
            $extras['new_line']
        );
        $attributes = $extras;
        $attributes['name'] = $field;
        $attributes['id'] = $label;

        if (isset($attributes['required']) && !$attributes['required']) {
            unset($attributes['required']);
        }

        if ($isForm) {
            if (!isset($attributes['class'])) {
                $attributes['class'] = 'form-control form-control-sm';
            } else {
                if (strpos($attributes['class'], 'form-control') === false) {
                    $attributes['class'] .= ' form-control';
                }

                if (strpos($attributes['class'], 'form-control-sm') === false) {
                    $attributes['class'] .= ' form-control-sm';
                }
            }
        }

        $normalizedField = str_replace(['[', ']'], '', $field);
        $value = $this->params[$normalizedField] ?? $default;
        if (!is_array($value)) {
            $value = (string) $value;
        }

        if ($isForm) {
            $colWidth = 9;
            if (empty($title)) {
                $colWidth = 12;
            }
            $str .= sprintf('<div class="col-md-%d">', $colWidth);
        }
        $str .= sprintf('<select %s>', Str::toAttribute($attributes));
        if (!isset($attributes['required'])) {
            $str .= sprintf('<option value="">%s</option>', '-- Tout --');
        }
        foreach ($values as $key => $option) {
            if (is_numeric($key) && is_object($option)) {
                $key = $option->{$keyField};
                if ($formatFunction !== null) {
                    $option = call_user_func_array($formatFunction, [$option]);
                } else {
                    $option = Str::stringify($option);
                }
            }
            $selected = (is_string($value) && $key == $value) || (is_array($value) && in_array($key, $value));
            $str .= sprintf('<option value="%s" %s>%s</option>', $key, $selected ? 'selected' : '', $option);
        }
        $str .= '</select>';
        if ($isForm) {
            $str .= '</div>';
        }

        if ($newLine) {
            $str .= '<br /><br />';
        }
        if ($isForm) {
            $str .= '</div>';
        }

        return $str;
    }

    /**
     * Render for radio field
     * @param string $field
     * @param bool $isForm
     * @return string
     */
    protected function renderR(string $field, bool $isForm = false): string
    {
        return '';
    }

    /**
     * Render for checkbox field
     * @param string $field
     * @param bool $isForm
     * @return string
     */
    protected function renderC(string $field, bool $isForm = false): string
    {
        return '';
    }

    /**
     * Render for date field
     * @param string $field
     * @param bool $isForm
     * @return string
     */
    protected function renderD(string $field, bool $isForm = false): string
    {
        return $this->renderTextField($field, 'date', $isForm);
    }

    /**
     * Render common text field
     * @param string $field
     * @param string $type
     * @param bool $isForm
     * @return string
     */
    protected function renderTextField(string $field, string $type, bool $isForm = false): string
    {
        $data = $this->fields[$field] ?? [];
        if (empty($data)) {
            return '';
        }
        if ($isForm) {
            $str = '<div class="form-group row">';
        } else {
            $str = '';
        }
        $title = $data['title'] ?? '';
        $label = str_replace(['[', ']'], '', $field);
        if (!empty($title)) {
            if ($isForm) {
                $str .= <<<E
                    <label for="$label" class="col-md-3 col-form-label">
                        $title:
                    </label>
                E;
            } else {
                $str .= sprintf('<label for="%s">%s:</label> &nbsp;&nbsp;', $label, $title);
            }
        }

        $extras = $data['extras'];
        $newLine = $extras['new_line'] ?? false;
        $attributes = $extras;
        $default = $data['value'];
        $attributes['name'] = $field;
        $attributes['type'] = $type;
        $attributes['id'] = $label;

        unset($extras['new_line']);

        if (isset($attributes['required']) && !$attributes['required']) {
            unset($attributes['required']);
        }

        if ($isForm) {
            if (!isset($attributes['class'])) {
                $attributes['class'] = 'form-control form-control-sm';
            } else {
                if (strpos($attributes['class'], 'form-control') === false) {
                    $attributes['class'] .= ' form-control';
                }

                if (strpos($attributes['class'], 'form-control-sm') === false) {
                    $attributes['class'] .= ' form-control-sm';
                }
            }
        }

        $value = $this->params[$field] ?? $default;
        $attributes['value'] = $value;

        if (!empty($attributes['value']) && $type === 'date') {
            $attributes['value'] = date('Y-m-d', strtotime($attributes['value']));
        }

        if ($isForm) {
            $colWidth = 9;
            if (empty($title)) {
                $colWidth = 12;
            }
            $str .= sprintf('<div class="col-md-%d">', $colWidth);
        }

        $str .= sprintf('<input %s />', Str::toAttribute($attributes));
        if ($newLine) {
            $str .= '<br /><br />';
        }
        if ($isForm) {
            $str .= '</div>';
            $str .= '</div>';
        }

        return $str;
    }
}
