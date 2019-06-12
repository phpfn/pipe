<?php
/**
 * This file is part of Pipe package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Serafim\Pipe\Resolver;

/**
 * Interface ResolverInterface
 */
interface ResolverInterface
{
    /**
     * @param string $function
     * @return string
     */
    public function resolve(string $function): string;
}
