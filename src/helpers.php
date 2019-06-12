<?php
/**
 * This file is part of Pipe package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

use Serafim\Pipe\Pipe;


if (! defined('_')) {
    define('_', Pipe::PLACEHOLDER);
}


if (! function_exists('pipe')) {
    /**
     * @param mixed $value
     * @return Pipe
     */
    function pipe($value): Pipe
    {
        return new Pipe($value);
    }
}
