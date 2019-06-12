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
 * Class Snake
 */
class Snake implements ResolverInterface
{
    /**
     * @var array|string[]
     */
    private $memoize = [];

    /**
     * @var string
     */
    private const DELIMITER = '_';

    /**
     * @param string $function
     * @return string
     */
    public function resolve(string $function): string
    {
        $key = $function;

        if (isset($this->memoize[$key])) {
            return $this->memoize[$key];
        }

        if (! \ctype_lower($function)) {
            $function = $this->format($function);
        }

        return $this->memoize[$key] = $function;
    }

    /**
     * @param string $fn
     * @return string
     */
    private function format(string $fn): string
    {
        $fn = \preg_replace('/\s+/u', '', \ucwords($fn));

        return \strtolower(\preg_replace('/(.)(?=[A-Z])/u', '$1' . self::DELIMITER, $fn));
    }
}
