<?php

/**
 * This file is part of Pipe package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Serafim\Pipe;

/**
 * Interface PipeInterface
 */
interface PipeInterface
{
    /**
     * @param string $namespace
     * @param \Closure|null $context
     * @return PipeInterface
     */
    public function use(string $namespace, \Closure $context = null): self;

    /**
     * @param string $name
     * @param array $arguments
     * @return PipeInterface
     */
    public function __call(string $name, array $arguments = []): self;

    /**
     * @param string $name
     * @return PipeInterface
     */
    public function __get(string $name): self;
}
