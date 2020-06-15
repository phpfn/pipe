<?php

/**
 * This file is part of Pipe package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\Pipe\Tests;

use Serafim\Pipe\Pipe;

/**
 * Class TestFunctionPiping
 */
class FunctionPipingTestCase extends TestCase
{
    /**
     * @return array
     */
    public function returnsTrueDataProvider(): array
    {
        return [
            ['class_exists', __CLASS__],
            ['function_exists', 'function_exists'],
            ['is_array', [1, 2, 3]],
            ['is_string', 'string']
        ];
    }

    /**
     * @dataProvider returnsTrueDataProvider
     *
     * @param string $fn
     * @param mixed $argument
     * @return void
     */
    public function testPipingWithoutTrailingBrackets(string $fn, $argument): void
    {
        $pipe = $this->pipe($argument)->$fn;

        $this->assertTrue($pipe());
    }

    /**
     * @dataProvider returnsTrueDataProvider
     *
     * @param string $fn
     * @param mixed $argument
     * @return void
     */
    public function testPipingWithTrailingBrackets(string $fn, $argument): void
    {
        $pipe = $this->pipe($argument)->$fn(_);

        $this->assertTrue($pipe());
    }

    /**
     * @dataProvider returnsTrueDataProvider
     *
     * @param string $fn
     * @param mixed $argument
     * @return void
     */
    public function testPiping(string $fn, $argument): void
    {
        $pipe = $this->pipe($argument)
            ->$fn(_)
            ->intval;

        $this->assertSame(1, $pipe());
    }

    /**
     * @return void
     */
    public function testUndefinedFunction(): void
    {
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Call to undefined function undefinedFunction()');

        $this
            ->pipe()
            ->undefinedFunction()
        ;
    }
}
