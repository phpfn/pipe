<?php
/**
 * This file is part of Pipe package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Serafim\Pipe\Console;

use Serafim\Pipe\Pipe;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\FileGenerator;
use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Reflection\FunctionReflection;

/**
 * Class BuildAutocompleteCommand
 *
 * @mixin Pipe
 */
class BuildAutocompleteCommand extends Command
{
    /**
     * @var array[]
     */
    private const OPTIONS = [
        ['output', 'out', InputOption::VALUE_OPTIONAL, 'Output directory', __DIR__ . '/../../resources'],
    ];

    /**
     * @var array[]
     */
    private const ARGUMENTS = [
    ];

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'autocomplete';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Build IDE files for autocomplete';
    }

    /**
     * @return void
     */
    public function configure(): void
    {
        foreach (self::OPTIONS as $opt) {
            $this->addOption(...$opt);
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \ReflectionException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $docblock = DocBlockGenerator::fromArray($this->getDocBlock());
        $docblock->setWordWrap(false);

        $generator = FileGenerator::fromArray([
            'classes'  => [
                ClassGenerator::fromArray([
                    'name'          => 'Pipe',
                    'namespacename' => 'Serafim\\Pipe',
                    'docblock'      => $docblock,
                ]),
            ],
            'docblock' => DocBlockGenerator::fromArray([
                'shortDescription' => 'This file was automatically generated',
                'tags'             => [
                    [
                        'name'        => 'license',
                        'description' => 'MIT',
                    ],
                    [
                        'name'        => 'noinspection',
                        'description' => 'ALL',
                    ],
                ],
            ]),
        ]);

        $this->write($input->getOption('output'), $generator->generate());

        // Return exit status
        return 0;
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    private function getDocBlock(): array
    {
        $tags = [
            [
                'name'        => 'noinspection',
                'description' => 'AutoloadingIssuesInspection',
            ],
            [
                'name'        => 'noinspection',
                'description' => 'PhpUndefinedClassInspection',
            ],
        ];

        $properties = $methods = [];

        foreach ($this->functions() as $function) {
            $fn = new FunctionReflection($function);
            $parameters = [];

            foreach ($fn->getParameters() as $parameter) {
                $parameters[] = ParameterGenerator::fromReflection($parameter)->generate();
            }

            foreach ($this->format($function) as $name) {
                if (\in_array($name, $methods, true)) {
                    continue;
                }

                $tags[] = [
                    'name'        => 'method',
                    'description' => 'Pipe|$this ' . ($methods[] = $name) . '(' . \implode(', ', $parameters) . ')',
                ];
            }

            if ($fn->getNumberOfRequiredParameters() < 2) {
                foreach ($this->format($function) as $name) {
                    if (\in_array($name, $properties, true)) {
                        continue;
                    }

                    $tags[] = [
                        'name'        => 'property-read',
                        'description' => 'mixed $' . ($properties[] = $name),
                    ];
                }
            }
        }

        return ['tags' => $tags];
    }

    /**
     * @return iterable|string[]
     */
    private function functions(): iterable
    {
        /** @noinspection PotentialMalwareInspection */
        foreach (\get_defined_functions()['internal'] ?? [] as $function) {
            $function = \trim($function, '\\');

            if (\substr_count($function, '\\') === 0) {
                yield $function;
            }
        }
    }

    /**
     * @param string $function
     * @return iterable|string[]
     */
    private function format(string $function): iterable
    {
        yield $function;
    }

    /**
     * @param string $out
     * @param string $content
     * @return void
     */
    private function write(string $out, string $content): void
    {
        $filename = $out . '/.phpstorm.autocomplete.php';

        \file_put_contents($filename, $content);
    }
}
