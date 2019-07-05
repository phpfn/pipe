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
 * Trait FunctionalOperatorsTrait
 */
trait OperatorsTrait
{
    /**
     * @param mixed ...$arguments
     * @return \Serafim\Pipe\Pipe|$this
     */
    public function array(...$arguments): self
    {
        $self = clone $this;
        $self->value = $this->applyArguments($arguments);

        return $self;
    }

    /**
     * @param mixed ...$arguments
     * @return \Serafim\Pipe\Pipe|$this
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
     * @param int|string|mixed $status
     * @return void
     */
    public function die($status = 255): void
    {
        die($this->applyArgument($status));
    }

    /**
     * @param int|string|mixed $status
     * @return void
     */
    public function exit($status = 255): void
    {
        exit($this->applyArgument($status));
    }

    /**
     * @param mixed|null $value
     * @return \Serafim\Pipe\Pipe|$this
     */
    public function empty($value = null): self
    {
        $self = clone $this;

        $self->value = empty($this->applyArgument(\func_num_args() === 0 ? $self->value : $value));

        return $self;
    }

    /**
     * @param mixed ...$arguments
     * @return \Serafim\Pipe\Pipe|$this
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
     * @param mixed ...$arguments
     * @return \Serafim\Pipe\Pipe|$this
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
     * @param mixed $code
     * @return \Serafim\Pipe\Pipe|$this
     */
    public function eval($code): self
    {
        $self = clone $this;
        $self->value = eval($this->applyArgument($code));

        return $self;
    }
}
