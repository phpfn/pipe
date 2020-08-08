<?php
/**
 * This file is part of Pipe package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Serafim\Pipe;

/**
 * Trait RendererTrait
 */
trait RendererTrait
{
    /**
     * @param mixed $value
     * @return string
     */
    protected function render($value): string
    {
        switch (true) {
            case $value === null:
                return 'null';

            case \is_bool($value):
                return $value ? 'true' : 'false';

            case $this->canRenderAsString($value):
                return (string)$value;

            case \is_array($value):
                return '[' . $this->splitIterableValues($value) . ']';

            case $value instanceof \Traversable:
                return \get_class($value) . ' { ' . $this->splitIterableValues($value) . ' }';

            default:
                if (\function_exists('\\json_encode')) {
                    return $this->renderAsJson($value);
                }

                return $this->renderFallback($value);
        }
    }

    /**
     * @param mixed $value
     * @return bool
     */
    private function canRenderAsString($value): bool
    {
        if (\is_scalar($value)) {
            return true;
        }

        return \is_object($value) && \method_exists($value, '__toString');
    }

    /**
     * @param iterable $values
     * @return string
     */
    private function splitIterableValues(iterable $values): string
    {
        $result = [];

        foreach ($values as $i) {
            $result[] = $this->render($i);
        }

        return \implode(', ', $result);
    }

    /**
     * @param mixed $value
     * @return string
     */
    private function renderAsJson($value): string
    {
        $result = \json_encode($value);
        if (\json_last_error() !== \JSON_ERROR_NONE) {
            return $this->renderFallback($value);
        }

        return $result;
    }

    /**
     * @param mixed $value
     * @return string
     */
    private function renderFallback($value): string
    {
        \ob_start();
        \var_dump($value);

        return (string)\ob_get_clean();
    }
}
