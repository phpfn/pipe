<?php

/**
 * This file is part of phpfn package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

class_alias(\Fun\Pipe\Pipe::class, \Serafim\Pipe\Pipe::class);
class_alias(\Fun\Pipe\PipeInterface::class, \Serafim\Pipe\PipeInterface::class);

class_alias(\Fun\Pipe\Exception\FunctionNotFoundException::class, \Serafim\Pipe\Exception\FunctionNotFoundException::class);

class_alias(\Fun\Pipe\Console\BuildAutocompleteCommand::class, \Serafim\Pipe\Console\BuildAutocompleteCommand::class);
