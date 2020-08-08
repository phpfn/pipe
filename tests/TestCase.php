<?php

/**
 * This file is part of phpfn package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fun\Pipe\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Fun\Pipe\Pipe;
use Fun\Pipe\PipeInterface;

/**
 * Class TestCase
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * @param mixed|null $value
     * @param string|null $namespace
     * @return PipeInterface
     */
    protected function pipe($value = null, string $namespace = null): PipeInterface
    {
        return new Pipe($value, $namespace);
    }
}
