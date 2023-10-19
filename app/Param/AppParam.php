<?php

declare(strict_types=1);

namespace Platine\App\Param;

use Platine\Framework\Form\Param\BaseParam;
use Platine\Framework\Http\RequestData;
use Platine\Http\ServerRequestInterface;
use Platine\Stdlib\Helper\Str;

/**
* @class AppParam
* @package Platine\App\Param
*/
class AppParam extends BaseParam
{
    /**
     * The list of fields to ignore
     * @var array<string>
     */
    protected array $ignores = ['from'];

    /**
     * {@inheritdoc}
     */
    public function getDefault(): array
    {
        //TODO
        /** @var ServerRequestInterface $request */
        $request = app(ServerRequestInterface::class);
        $param = new RequestData($request);

        $defaults = [];

        $queries = $param->gets();
        foreach ($queries as $name => $value) {
            $field = Str::camel($name, true);
            if (property_exists($this, $field) && !in_array($name, $this->ignores)) {
                $defaults[$name] = $value;
            }
        }

        return $defaults;
    }
}
