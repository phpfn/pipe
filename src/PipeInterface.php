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
     * @return \Serafim\Pipe\PipeInterface
     */
    public function use(string $namespace): self;

    /**
     * @param string $name
     * @param array $arguments
     * @return \Serafim\Pipe\PipeInterface
     */
    public function __call(string $name, array $arguments = []): self;

    /**
     * @param string $name
     * @return \Serafim\Pipe\PipeInterface
     */
    public function __get(string $name): self;
}
