<?php

/**
 * This file is part of phpfn package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fun\Pipe\Tests;

use Fun\Pipe\PipeInterface;

class NamespacedFunctionsTestCase extends TestCase
{
    /**
     * @return void
     */
    public function testFunctionFromNamespace(): void
    {
        $this->assertSame(
            'Some\\Any\\test_foo',
            (pipe(true, 'Some\\Any')->test_foo)()
        );
    }

    /**
     * @return void
     */
    public function testChainingFunctionFromNamespace(): void
    {
        $this->assertSame(
            'Some\\Any\\test_foo',
            (pipe(true, 'Some\\Any')->test_foo->test_foo)()
        );
    }

    /**
     * @return void
     */
    public function testGlobalFunctionFromNamespace(): void
    {
        $this->assertSame(
            'global_foo',
            (pipe(true, 'Some\\Any')->global_foo)()
        );
    }

    /**
     * @return void
     */
    public function testExportedFunctionFromNamespace(): void
    {
        $this->assertSame(
            'test_foo',
            (pipe(true)->test_foo)()
        );
    }

    /**
     * @return void
     */
    public function testFunctionFromGlobalNamespace(): void
    {
        $this->assertSame(
            'global_foo',
            (pipe(true)->global_foo)()
        );
    }

    /**
     * @return void
     */
    public function testFunctionWithChangedContext(): void
    {
        $context = pipe(true)
            ->use('Some\\Any')
            ->test_foo
        ;

        $this->assertSame('Some\\Any\\test_foo', $context());

        $this->assertSame('test_foo', ($context->test_foo)());
    }

    /**
     * @return void
     */
    public function testFunctionsInsideChangedContext(): void
    {
        $context = pipe(true)
            ->use('Some\\Any', static function (PipeInterface $ctx) {
                return $ctx->test_foo;
            });

        $this->assertSame('Some\\Any\\test_foo', $context());
    }

    /**
     * @return void
     */
    public function testMultipleFunctionsWithChangedContext(): void
    {
        $context = pipe(true)
            ->use('Some\\Any', static function (PipeInterface $ctx) {
                return $ctx
                    ->test_foo
                    ->is_string;
            });

        $this->assertTrue($context());
    }
}
