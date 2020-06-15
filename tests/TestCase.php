<?php
/**
 * This file is part of Pipe package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Serafim\Pipe\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Serafim\Pipe\Pipe;
use Serafim\Pipe\PipeInterface;

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
        return pipe($value, $namespace);

        return new Pipe($value, $namespace);
    }
}
