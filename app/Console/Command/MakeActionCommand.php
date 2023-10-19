<?php

/**
 * Platine Framework
 *
 * Platine Framework is a lightweight, high-performance, simple and elegant
 * PHP Web framework
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2020 Platine Framework
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types=1);

namespace Platine\App\Console\Command;

use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Framework\Console\Command\MakeActionCommand as BaseMakeActionCommand;

/**
 * @class MakeActionCommand
 * @package Platine\App\Console\Command
 */
class MakeActionCommand extends BaseMakeActionCommand
{
    /**
     * Create new instance
     * @param Application $application
     * @param Filesystem $filesystem
     */
    public function __construct(
        Application $application,
        Filesystem $filesystem
    ) {
        parent::__construct($application, $filesystem);
        $this->setName('make:app-action')
               ->setDescription('Command to generate new request handler class');
    }

    /**
     * {@inheritdoc}
     */
    public function getClassTemplate(): string
    {
        return <<<EOF
        <?php
        
        declare(strict_types=1);
        
        namespace %namespace%;
        
        use Platine\App\Http\Action\BaseAction;
        use Platine\Http\ResponseInterface;
        %uses%

        /**
        * @class %classname%
        * @package %namespace%
        */
        class %classname% extends BaseAction
        {
            
            %properties%
        
            %constructor%
        
            /**
            * {@inheritdoc}
            */
            public function respond(): ResponseInterface
            {
            }
        }
        
        EOF;
    }

    /**
     * Return the constructor body template for the given class
     * @param string $className
     * @param array<string, string> $info
     * @param bool $isLast
     * @return string
     */
    protected function getConstructorBodyTemplate(string $className, array $info, bool $isLast = false): string
    {
        $name = $info['name'];
        if ($name === 'actionHelper') {
            return <<<EOF
            parent::__construct(\$actionHelper);
                    
            EOF;
        }
        if ($isLast) {
            return <<<EOF
            \$this->$name = \$$name;
            EOF;
        }

        return <<<EOF
        \$this->$name = \$$name;
                
        EOF;
    }
}
