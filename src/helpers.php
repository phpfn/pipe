<?php

/**
 * This file is part of Pipe package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

use Serafim\Pipe\Pipe;
use Serafim\Pipe\PipeInterface;

if (! \function_exists('pipe')) {
    /**
     * @param mixed|null $value
     * @param string|null $namespace
     * @return Pipe|PipeInterface
     */
    function pipe($value = null, string $namespace = null): PipeInterface
    {
        return new Pipe($value, $namespace);
    }
}
