<?php
/**
 * This file is part of Pipe package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Serafim\Pipe;

use Serafim\Pipe\Resolver\AsIs;
use Serafim\Pipe\Resolver\Join;
use Serafim\Pipe\Resolver\Snake;
use Serafim\Pipe\Resolver\ResolverInterface;
use Serafim\Pipe\Exception\FunctionNotFoundException;

/**
 * Class Pipe
 *
 * @noinspection PhpUndefinedClassInspection
 * @property-read $value
 */
class Pipe
{
    /**
     * @var string
     */
    public const PLACEHOLDER = "\0$$\0";

    /**
     * @var array|ResolverInterface[]
     */
    private $resolvers;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var string
     */
    private $namespace = '';

    /**
     * Pipe constructor.
     *
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
        $this->resolvers = $this->bootDefaultResolvers();
    }

    /**
     * @return array|ResolverInterface[]
     */
    private function bootDefaultResolvers(): array
    {
        return [
            new AsIs(),
            new Snake(),
            new Join(),
        ];
    }

    /**
     * @return array
     */
    public function __debugInfo(): array
    {
        return ['value' => $this->value];
    }

    /**
     * @param array $args
     * @return mixed
     */
    public function __invoke(...$args)
    {
        if (\count($args) > 0) {
            return ($this->value)($this->applyArguments($args));
        }

        return $this->value;
    }

    /**
     * @param string $namespace
     * @return Pipe|$this
     */
    public function use(string $namespace): self
    {
        $self = clone $this;

        $self->namespace = \trim($namespace, '\\');

        return $self;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return Pipe|$this
     */
    public function __call(string $name, array $arguments = []): self
    {
        return $this->exec($name, $this->applyArguments($arguments));
    }

    /**
     * @param string $function
     * @param \Closure $then
     * @return string
     */
    private function lookup(string $function, \Closure $then): string
    {
        foreach ($this->resolvers as $resolver) {
            if (\is_string($name = $then($resolver->resolve($function)))) {
                return $name;
            }
        }

        $error = \sprintf('Function "%s" not found', $this->namespaced($function));
        throw new FunctionNotFoundException($error);
    }

    /**
     * @param string $name
     * @return string
     */
    private function namespaced(string $name): string
    {
        return $this->namespace . '\\' . $name;
    }

    /**
     * @param array $arguments
     * @return array
     */
    private function applyArguments(array $arguments): array
    {
        return \array_map(function ($value) {
            return $value === static::PLACEHOLDER ? $this->value : $value;
        }, $arguments);
    }

    /**
     * @param string $function
     * @param array $arguments
     * @return Pipe|$this
     */
    private function exec(string $function, array $arguments): self
    {
        $function = $this->lookup($function, function (string $name): ?string {
            $function = $this->namespaced($name);

            return \function_exists($function) ? $function : null;
        });

        $self = clone $this;
        $self->value = $function(...$arguments);

        return $self;
    }

    /**
     * @param string $name
     * @return mixed|$this
     */
    public function __get(string $name)
    {
        if (\strtolower($name) === 'value') {
            return $this->value;
        }

        return $this->exec($name, [$this->value]);
    }
}
