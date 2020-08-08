<?php

/**
 * This file is part of phpfn package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fun\Pipe\Tests {

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
