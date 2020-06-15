<?php

/**
 * This file is part of Pipe package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\Pipe\Tests {

    use Serafim\Pipe\PipeInterface;

    /**
     * Class NamespacedFunctionsTestCase
     */
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
                __NAMESPACE__ . '\\test_foo',
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
                ->test_foo;

            $this->assertSame('Some\\Any\\test_foo', $context());

            $context = $context
                ->test_foo;

            $this->assertSame(__NAMESPACE__ . '\\test_foo', $context());
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

            $this->assertSame(true, $context());
        }
    }

    function test_foo()
    {
        return __FUNCTION__;
    }
}

namespace {
    function global_foo()
    {
        return __FUNCTION__;
    }

    function test_foo()
    {
        return __FUNCTION__;
    }
}

namespace Some\Any {

    function test_foo()
    {
        return __FUNCTION__;
    }
}
