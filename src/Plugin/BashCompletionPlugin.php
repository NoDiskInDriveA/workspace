<?php

namespace my127\Workspace\Plugin;

use my127\Console\Application\Application;
use my127\Console\Application\Event\BeforeActionEvent;
use my127\Console\Application\Event\InvalidUsageEvent;
use my127\Console\Application\Executor;
use my127\Console\Application\Plugin\Plugin;
use my127\Console\Application\Section\Section;
use my127\Console\Usage\Exception\NoSuchOptionException;
use my127\Console\Usage\Model\BooleanOptionValue;
use my127\Console\Usage\Model\OptionDefinition;
use my127\Console\Usage\Model\OptionDefinitionCollection;
use my127\Console\Usage\Parser\OptionDefinitionParser;

class BashCompletionPlugin implements Plugin
{
    /**
     * @var Section
     */
    private $root;

    /**
     * @var OptionDefinitionParser
     */
    private $optionDefinitionParser;

    public function __construct(OptionDefinitionParser $optionDefinitionParser)
    {
        $this->optionDefinitionParser = $optionDefinitionParser;
    }

    public function setup(Application $application): void
    {
        $this->root = $application->getRootSection();

        $application
            ->option('-c, --complete    Output current bash completion')
            ->on(
                Executor::EVENT_BEFORE_ACTION,
                function (BeforeActionEvent $e) {
                    try {
                        if (($input = $e->getInput())->getOption('complete')->equals(BooleanOptionValue::create(true))) {
                            $this->displayJson($this->root->get(implode(' ', $input->getCommand())));
                            echo $e->getInput()->toJSON();
                            $e->preventAction();
                        }
                    } catch (NoSuchOptionException $e) {
                        // Ignore actions that does not provide help.
                    }
                }
            )
            ->on(
                Executor::EVENT_INVALID_USAGE,
                function (InvalidUsageEvent $e) {
                    if (!$e->getInputSequence()->hasOption(new OptionDefinition(BooleanOptionValue::create(false), OptionDefinition::TYPE_BOOL, 'c', 'complete'))) {
                        return;
                    }
                    $e->stopPropagation();
                    var_dump($e->getInputSequence());
                    $argv  = $e->getInputSequence();
                    $parts = [];

                    while ($positional = $argv->pop()) {
                        $parts[] = $positional;
                    }

                    $name    = implode(' ', $parts);
                    $section = $this->root->contains($name)?$this->root->get($name):$this->root;

                    $this->displayJson($section);
                }
            );
        ;
    }

    private function displayJson(Section $section): void
    {
        $output = [];

        if (!$this->isRoot($section)) {
            $output += array_map(function(OptionDefinition $option) {
                $suffix = $option->getType() === OptionDefinition::TYPE_BOOL ? '' : '=';
                return join(' ', array_filter([
                    $option->getShortName() ? '-'.$option->getShortName(): null,
                    $option->getLongName() ? ('--'.$option->getLongName().$suffix) : null,
                ]));
            }, iterator_to_array($this->getOptionCollection($section->getOptions())->getIterator()));
        }

        if (!empty($section->getChildren())) {
            $output += array_map(function(Section $subSection) {
                $name = $subSection->getName();
                $subcommand = trim(substr($name, strrpos($name, ' ')));
                return $subcommand;
            }, $section->getChildren());
        }

        $output += array_map(function(OptionDefinition $option) {
            $suffix = $option->getType() === OptionDefinition::TYPE_BOOL ? '' : '=';
            return join(' ', array_filter([
                $option->getShortName() ? '-'.$option->getShortName(): null,
                $option->getLongName() ? ('--'.$option->getLongName().$suffix) : null,
            ]));
        }, iterator_to_array($this->getOptionCollection($this->root->getOptions())->getIterator()));

        echo join(' ', $output);
    }

    private function isRoot($section): bool
    {
        return $section === $this->root;
    }

    private function getOptionCollection(array $options): OptionDefinitionCollection
    {
        $collection = new OptionDefinitionCollection();

        foreach ($options as $option) {
            $collection->add($this->optionDefinitionParser->parse($option));
        }

        return $collection;
    }
}
