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
use Serafim\Pipe\Resolver\AsIs;
use Serafim\Pipe\Resolver\Join;
use Serafim\Pipe\Resolver\ResolverInterface;
use Serafim\Pipe\Resolver\Snake;
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
 *
 *  $snakeCase = pipe($camelCase)
 *      ->ucWords(_)
 *      ->pregReplace('/\s+/u', '', _)
 *      ->pregReplace('/(.)(?=[A-Z])/u', '$1_', _)
 *      ->strToLower(_)
 *      ->varDump;
 *  // expected output: "hello_world"
 *
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
    private const ERROR_NOT_FOUND = 'Function "%s" not found';

    /**
     * @var string[]|ResolverInterface[]
     */
    private const DEFAULT_RESOLVERS = [
        AsIs::class,
        Snake::class,
        Join::class,
    ];

    /**
     * A set of keys for functions for quicker recall in format:
     * <code>
     *  [
     *      'vardump' => 'var_dump',
     *      'strreplace' => 'str_replace'
     *  ]
     * </code>
     *
     * @var array|string[]
     */
    private static $memoized = [];

    /**
     * Original value with which we interact.
     *
     * @var mixed
     */
    protected $value;

    /**
     * List of objects that define a set of renaming algorithms.
     *
     * @var array|ResolverInterface[]
     */
    private $resolvers;

    /**
     * The namespace that we are currently using for function prefixes.
     *
     * @var string
     */
    private $namespace = '';

    /**
     * Pipe constructor.
     *
     * @param mixed $value
     */
    public function __construct($value = null)
    {
        $this->value = $this->resolveValue($value);
        $this->resolvers = $this->bootDefaultResolvers();
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
     * Constructor for default function name resolvers.
     *
     * @return array|ResolverInterface[]
     */
    private function bootDefaultResolvers(): array
    {
        $result = [];

        foreach (self::DEFAULT_RESOLVERS as $class) {
            $result[] = new $class();
        }

        return $result;
    }

    /**
     * @return array
     */
    public function __debugInfo(): array
    {
        return [
            'value' => $this->value,
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
     * @return PipeInterface|Pipe|$this
     */
    public function use(string $namespace): PipeInterface
    {
        $self = clone $this;

        $self->namespace = \trim($namespace, '\\');

        return $self;
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
     * @return Pipe|$this
     */
    private function exec(string $function, array $arguments): self
    {
        if (($normalized = $this->resolveMemoizedName($function)) === null) {
            throw $this->functionNotFound($function);
        }

        $self = clone $this;
        $self->value = $normalized(...$arguments);

        return $self;
    }

    /**
     * Getting the normal name of the function that we should call in the
     * future with the intermediate memoization of the original name.
     *
     * @param string $function
     * @return string|null
     */
    private function resolveMemoizedName(string $function): ?string
    {
        $lower = \strtolower($function);

        return self::$memoized[$lower] ?? self::$memoized[$lower] = $this->resolveName($function);
    }

    /**
     * Getting the normal name of the function that we should call in the future.
     *
     * @param string $function
     * @return string|null
     */
    private function resolveName(string $function): ?string
    {
        return $this->lookup($function, function (string $name): ?string {
            $function = $this->namespaced($name);

            return \function_exists($function) ? $function : null;
        });
    }

    /**
     * Lookup method to a suitable function name.
     *
     * @param string $function
     * @param \Closure $then
     * @return string|null
     */
    private function lookup(string $function, \Closure $then): ?string
    {
        foreach ($this->resolvers as $resolver) {
            if (\is_string($name = $then($resolver->resolve($function)))) {
                return $name;
            }
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
        return $this->namespace . '\\' . $name;
    }

    /**
     * @param string $function
     * @return \Serafim\Pipe\Exception\FunctionNotFoundException
     */
    private function functionNotFound(string $function): FunctionNotFoundException
    {
        $message = \sprintf(self::ERROR_NOT_FOUND, $this->namespaced($function));

        return new FunctionNotFoundException($message);
    }

    /**
     * @noinspection MagicMethodsValidityInspection
     *
     * @param string $function
     * @return \Serafim\Pipe\PipeInterface|Pipe|$this
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
        return $this->resolveMemoizedName($function) !== null;
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
