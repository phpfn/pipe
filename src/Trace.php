<?php

/**
 * This file is part of phpfn package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fun\Pipe;

/**
 * Class Trace
 */
final class Trace
{
    /**
     * @var bool|null
     */
    private static $redefined;

    /**
     * @var array
     */
    private $backtrace;

    /**
     * @var string
     */
    private $directory;

    /**
     * Trace constructor.
     *
     * @param string $directory
     */
    public function __construct(string $directory)
    {
        $this->directory = $directory;
        $this->backtrace = \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS);
    }

    /**
     * @param string $directory
     * @return string
     */
    public static function getNamespace(string $directory): string
    {
        return (new self($directory))->getExecutionNamespace();
    }

    /**
     * Returns boolean true if the global pipe function has been redefined.
     *
     * @return bool
     */
    private function isPipeRedefined(): bool
    {
        if (self::$redefined === null) {
            $file = (new \ReflectionFunction('\\pipe'))
                ->getFileName();

            self::$redefined = \strpos($file, $this->directory) !== 0;
        }

        return self::$redefined;
    }

    /**
     * @return string
     */
    public function getExecutionNamespace(): string
    {
        for ($i = 0, $len = \count($this->backtrace); $i < $len; ++$i) {
            if (isset($this->backtrace[$i + 1]) && $this->isExternalCallee($this->backtrace[$i])) {
                return $this->getContentNamespace($this->backtrace[$i + 1]);
            }
        }

        return '';
    }

    /**
     * @param array $context
     * @return bool
     */
    private function isExternalCallee(array $context): bool
    {
        if (\strpos($context['file'] ?? '', $this->directory) !== 0) {
            return true;
        }

        $isPipeHelperFunction = $context['function'] ?? '' === 'pipe' &&
            ! $this->isPipeRedefined();

        return ! $isPipeHelperFunction;
    }

    /**
     * @param array $trace
     * @return string
     */
    private function getContentNamespace(array $trace): string
    {
        return \dirname(\str_replace('\\', DIRECTORY_SEPARATOR, $this->getContext($trace)));
    }

    /**
     * @param array $trace
     * @return string
     */
    private function getContext(array $trace): string
    {
        return $trace['class'] ?? $trace['function'] ?? '';
    }
}
