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
 * @mixin Pipe
 * @mixin PipeInterface
 */
trait OperatorsTrait
{
    /**
     * Creates an array.
     *
     * <code>
     *  pipe()
     *      ->array(1, 2, 3)
     *      ->var_dump  // array(3) { 1, 2, 3 }
     *  ;
     *
     *  pipe(42)
     *      ->array(_, 23)
     *      ->var_dump  // array(2) { 42, 23 }
     *  ;
     * </code>
     *
     * @see https://php.net/manual/en/function.array.php
     * @param mixed ...$arguments
     * @return self|$this
     */
    public function array(...$arguments): self
    {
        $self = clone $this;
        $self->value = $this->applyArguments($arguments);

        return $self;
    }

    /**
     * Assigns a list of variables in one operation.
     *
     * <code>
     *  $a = $b = 0;
     *
     *  pipe([42, 23])->list($a, $b);
     *
     *  var_dump($a, $b); // int(42) int(23)
     * </code>
     *
     * @see https://php.net/manual/en/function.list.php
     * @param mixed ...$arguments
     * @return self|$this
     */
    public function list(&...$arguments): self
    {
        if (\is_array($this->value) || $this->value instanceof \ArrayAccess) {
            foreach ($arguments as $i => &$value) {
                $value = $this->value[$i] ?? null;
            }

            return $this;
        }

        foreach ($arguments as $i => &$value) {
            $value = $this->value;
        }

        return $this;
    }

    /**
     * Terminates execution of the script. Shutdown functions and object
     * destructors will always be executed even if exit is called.
     *
     * <code>
     *  pipe(255)->die; // "Exit with status code 255"
     * </code>
     *
     * @see https://php.net/manual/en/function.die.php
     * @param int|string|resource $status
     * @return void
     */
    public function die($status = 255): void
    {
        die($this->applyArgument($status));
    }

    /**
     * Terminates execution of the script. Shutdown functions and object
     * destructors will always be executed even if exit is called.
     *
     * <code>
     *  pipe(255)->die; // "Exit with status code 255"
     * </code>
     *
     * @see https://php.net/manual/en/function.exit.php
     * @param int|string|resource $status
     * @return void
     */
    public function exit($status = 255): void
    {
        exit($this->applyArgument($status));
    }

    /**
     * Determine whether a variable is considered to be empty. A variable is
     * considered empty if it does not exist or if its value equals "FALSE".
     *
     * <code>
     *  pipe('')
     *      ->empty
     *      ->var_dump // bool(true)
     *  ;
     *
     *  pipe('asdasd')
     *      ->empty
     *      ->var_dump // bool(false)
     *  ;
     * </code>
     *
     * @see https://php.net/manual/en/function.empty.php
     * @param mixed $value
     * @return self|$this
     */
    public function empty($value = null): self
    {
        $self = clone $this;

        $self->value = empty($this->applyArgument(\func_num_args() === 0 ? $self->value : $value));

        return $self;
    }

    /**
     * Determine if a variable is set and is not "NULL".
     *
     * <code>
     *  pipe()
     *      ->isset
     *      ->var_dump // bool(false)
     *  ;
     *
     *
     *  pipe(23)
     *      ->isset
     *      ->var_dump // bool(true)
     *  ;
     *
     *
     *  $some = $any = 42;
     *  pipe($some)
     *      ->isset(_, $any)    // isset($some, $any) === false
     *      ->var_dump          // bool(false)
     *  ;
     * </code>
     *
     * @see https://php.net/manual/en/function.isset.php
     * @param mixed ...$arguments
     * @return self|$this
     */
    public function isset(&...$arguments): self
    {
        $self = clone $this;
        $isset = true;

        foreach ($arguments ?: [$self->value] as $argument) {
            if (! isset($argument)) {
                $isset = false;
                break;
            }
        }

        $self->value = $isset;

        return $self;
    }

    /**
     * Destroys the specified variables.
     *
     * <code>
     *  pipe(23)
     *      ->unset
     *      ->var_dump // null
     *  ;
     *
     *  $a = $b = 23;
     *  pipe($a)
     *      ->unset(_, $b) // unset($a, $b)
     *  ;
     * </code>
     *
     * @see https://php.net/manual/en/function.unset.php
     * @param mixed ...$arguments
     * @return self|$this
     */
    public function unset(&...$arguments): self
    {
        $self = clone $this;

        if (\count($arguments)) {
            foreach ($arguments as &$argument) {
                unset($argument);
            }
        } else {
            $self->value = null;
        }

        return $self;
    }

    /**
     * Evaluates the given code as PHP.
     *
     * <code>
     *  pipe('class Example {}')
     *      ->eval
     *  ;
     * </code>
     *
     * @see https://php.net/manual/en/function.eval.php
     * @param string $code
     * @return self|$this
     */
    public function eval(string $code): self
    {
        $self = clone $this;
        $self->value = eval($this->applyArgument($code));

        return $self;
    }

    /**
     * <code>
     *  pipe(true)
     *      ->if(_, fn(): int => 23)
     *      ->var_dump // int(23)
     *  ;
     *
     *  pipe(false)
     *      ->if(_, fn(): int => 23)
     *      ->var_dump // bool(false)
     *  ;
     *
     *  pipe(42)
     *      ->if(true, fn($ctx) => $ctx->var_dump) // int(42)
     *  ;
     * </code>
     *
     * @param mixed $expr
     * @param \Closure $then
     * @return self|$this
     */
    public function if($expr, \Closure $then): self
    {
        /** @var Pipe $self */
        $self = clone $this;

        if ($this->applyArgument($expr)) {
            $self = $this->passToClosureAndExtract($self, $then);
        }

        return $self;
    }
}
