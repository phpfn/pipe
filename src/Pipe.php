<?php

/**
 * This file is part of Pipe package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Serafim\Pipe;

use Serafim\Pipe\Exception\FunctionNotFoundException;
use Serafim\Placeholder\Placeholder;

/**
 * Object-oriented pipe operator implementation based on PHP Pipe operator.
 *
 * A common PHP OOP pattern is the use of method chaining, or what is
 * also known as "Fluent Expressions". So named for the way one method
 * flows into the next to form a conceptual hyper-expression.
 *
 * However, when using the functional approach this can lead to reduced
 * readability, polluted symbol tables, or static-analysis defying
 * type inconsistency such as in the following example:
 *
 * <code>
 *  $snakeCase = strtolower(
 *       preg_replace('/(.)(?=[A-Z])/u', '$1_',
 *           preg_replace('/\s+/u', '',
 *               ucwords('HelloWorld')
 *           )
 *       )
 *  );
 *
 *  var_dump($snakeCase);
 *  // expected output: "hello_world"
 * </code>
 *
 * The pipe fixes this problem, allows you to
 * chain the execution of pure functions:
 *
 * <code>
 *  $snakeCase = pipe('Hello World')
 *      ->ucwords(_)
 *      ->preg_replace('/\s+/u', '', _)
 *      ->preg_replace('/(.)(?=[A-Z])/u', '$1_', _)
 *      ->strtolower(_)
 *      ->var_dump;
 *  // expected output: "hello_world"
 * </code>
 *
 * @see https://wiki.php.net/rfc/pipe-operator
 * @noinspection PhpUndefinedClassInspection
 */
final class Pipe implements PipeInterface
{
    use RendererTrait;
    use OperatorsTrait;

    /**
     * A constant that defines the format of the error messages
     * to find a suitable function.
     *
     * @var string
     */
    private const ERROR_NOT_FOUND = 'Call to undefined function %s()';

    /**
     * Original value with which we interact.
     *
     * @var mixed
     */
    private $value;

    /**
     * The namespace that we are currently using for function prefixes.
     *
     * @var string|null
     */
    private $use;

    /**
     * The namespace that using for all function prefixes.
     *
     * @var string
     */
    private $namespace;

    /**
     * Pipe constructor.
     *
     * @param mixed $value
     * @param string|null $namespace
     */
    public function __construct($value = null, string $namespace = null)
    {
        $this->namespace = $namespace ?? Trace::getNamespace(__DIR__);

        $this->value = $this->resolveValue($value);
    }

    /**
     * Method to normalize the value to the valid for the job.
     *
     * @param mixed $value
     * @return mixed
     */
    private function resolveValue($value)
    {
        if ($value instanceof self) {
            return $value();
        }

        return $value;
    }

    /**
     * @return array
     */
    public function __debugInfo(): array
    {
        return [
            'namespace' => $this->use ?: $this->namespace,
            'value'     => $this->value,
        ];
    }

    /**
     * @param array<int, mixed> $args
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
     * Method to replace placeholders in the argument list
     * with the original value.
     *
     * <code>
     *  $this->applyArguments([1, _, _]);
     *  // expected output: [1, $this->value, $this->value]
     * </code>
     *
     * @param array $arguments
     * @return array
     */
    private function applyArguments(array $arguments): array
    {
        return Placeholder::map($arguments, function () {
            return $this->value;
        });
    }

    /**
     * Method to specify the current namespace with which
     * we will work further.
     *
     * @param string $namespace
     * @param \Closure|null $context
     * @return PipeInterface|Pipe|object|$this
     */
    public function use(string $namespace, \Closure $context = null): PipeInterface
    {
        $namespace = \trim($namespace, '\\');

        $self = clone $this;
        $self->use = $namespace;

        if ($context !== null) {
            $self->namespace = $namespace;
            $self = $this->passToClosureAndExtract($self, $context);
        }

        return $self;
    }

    /**
     * @param PipeInterface $ctx
     * @param \Closure $expr
     * @return PipeInterface
     */
    private function passToClosureAndExtract(PipeInterface $ctx, \Closure $expr): PipeInterface
    {
        $new = $expr($ctx);

        switch (true) {
            case $new instanceof PipeInterface:
                $ctx->value = $new->value;
                break;

            case $new !== null:
                $ctx->value = $new;
                break;
        }

        return $ctx;
    }

    /**
     * @param string $function
     * @param array $arguments
     * @return PipeInterface|Pipe|$this
     */
    public function __call(string $function, array $arguments = []): PipeInterface
    {
        return $this->exec($function, $this->applyArguments($arguments));
    }

    /**
     * Method to call the desired function.
     *
     * @param string $function
     * @param array $arguments
     * @return Pipe|object|$this
     */
    private function exec(string $function, array $arguments): self
    {
        if (($normalized = $this->resolveName($function)) === null) {
            throw $this->functionNotFound($function);
        }

        $self = clone $this;

        $self->value = $normalized(...$arguments);
        $self->use = null;

        return $self;
    }

    /**
     * Getting the normal name of the function that we should call in the future.
     *
     * @param string $name
     * @return string|null
     */
    private function resolveName(string $name): ?string
    {
        if (\function_exists($function = $this->namespaced($name))) {
            return $function;
        }

        return null;
    }

    /**
     * Method that adds a namespace prefix to the function being called.
     *
     * @param string $name
     * @return string
     */
    private function namespaced(string $name): string
    {
        if ($this->use) {
            return $this->use . '\\' . $name;
        }

        $namespaced = $this->namespace . '\\' . $name;

        return \function_exists($namespaced) ? $namespaced : $name;
    }

    /**
     * @param string $function
     * @return FunctionNotFoundException
     */
    private function functionNotFound(string $function): FunctionNotFoundException
    {
        $message = \vsprintf(self::ERROR_NOT_FOUND, [
            \trim($this->namespaced($function), '\\'),
        ]);

        return new FunctionNotFoundException($message);
    }

    /**
     * @noinspection MagicMethodsValidityInspection
     *
     * @param string $function
     * @return PipeInterface|Pipe|$this
     */
    public function __get(string $function): PipeInterface
    {
        if (\method_exists(OperatorsTrait::class, $function)) {
            return $this->$function($this->value);
        }

        return $this->exec($function, [$this->value]);
    }

    /**
     * @noinspection MagicMethodsValidityInspection
     *
     * @param string $function
     * @return bool
     */
    public function __isset(string $function): bool
    {
        return $this->resolveName($function) !== null;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->render($this->value);
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private function applyArgument($value)
    {
        return Placeholder::match($value) ? $this->value : $value;
    }
}
