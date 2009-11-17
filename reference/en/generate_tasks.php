<?php

require_once 'lib/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

$dispatcher = new sfEventDispatcher();
$logger = new sfCommandLogger($dispatcher);

class ProjectConfiguration extends sfProjectConfiguration
{
  protected $plugins = array('sfPropelPlugin', 'sfDoctrinePlugin');
}

class sfSymfony2CommandApplication extends sfSymfonyCommandApplication
{
  public function configure()
  {
    $this->loadTasks(new ProjectConfiguration(getcwd(), $this->dispatcher));
  }

  public function registerTask(sfTask $task)
  {
    try
    {
      parent::registerTask($task);
    }
    catch (Exception $e)
    {
    }
  }
}

class sfMarkdownFormatter extends sfFormatter
{
  public function format($text = '', $parameters = array(), $stream = STDOUT)
  {
    if (in_array($parameters, array('INFO', 'COMMENT')))
    {
      return sprintf('`%s`', $text);
    }

    return $text;
  }
}

$app = new sfSymfony2CommandApplication($dispatcher, new sfMarkdownFormatter());

// restrict to a namespace?
$namespace = '';

$tasks = array();
foreach ($app->getTasks() as $name => $task)
{
  if ($namespace && $namespace != $task->getNamespace())
  {
    continue;
  }

  if ($name != $task->getFullName())
  {
    // it is an alias
    continue;
  }

  if (!$task->getNamespace())
  {
    $name = '_default:'.$name;
  }

  $tasks[$name] = $task;
}

ksort($tasks);

$content = '';
$toc = " * Global tasks\n";
$currentNamespace = '';
foreach ($tasks as $name => $task)
{
  if (!$namespace && $currentNamespace != $task->getNamespace())
  {
    $currentNamespace = $task->getNamespace();
    $content .= sprintf("`%s`\n%s\n\n", $currentNamespace, str_repeat('-', 2 + strlen($currentNamespace)));
    $toc .= sprintf(" * [`%s`](#chapter_16_%s)\n", $currentNamespace, strtolower($currentNamespace));
  }

  $brief = strtolower(substr($task->getBriefDescription(), 0, 1)).substr($task->getBriefDescription(), 1);

  $name = ($currentNamespace ? $currentNamespace.'::' : '').$task->getName();
  $toc .= sprintf("   * [`%s%s`](#chapter_16_sub_%s)\n", $currentNamespace ? $currentNamespace.'::' : '', $task->getName(), strtolower(($currentNamespace ? $currentNamespace.'_' : '').str_replace(array('-', '.'), '_', $task->getName())));
  $description = $task->getDetailedDescription();
  $description = preg_replace('/^  `?(.+?)`?$/m', '    $1', $description);

  $synopsis = sprintf($task->getSynopsis(), '$ php symfony');

  $aliases = '';
  if ($task->getAliases())
  {
    $aliases = '*Alias(es)*: `'.implode(', ', $task->getAliases()).'`';
  }

  $arguments = '';
  if ($task->getArguments())
  {
    $arguments  = "| Argument | Default | Description\n";
    $arguments .= "| -------- | ------- | -----------\n";
    foreach ($task->getArguments() as $argument)
    {
      $default = !is_null($argument->getDefault()) && (!is_array($argument->getDefault()) || count($argument->getDefault())) ? sprintf('%s', is_array($argument->getDefault()) ? str_replace("\n", '', print_r($argument->getDefault(), true)) : $argument->getDefault()) : '-';
      $arguments .= sprintf("| `%s` | `%s` | %s\n", $argument->getName(), $default, $argument->getHelp());
    }
  }

  $options = '';
  if ($task->getOptions())
  {
    $options  = "| Option (Shortcut) | Default | Description\n";
    $options .= "| ----------------- | ------- | -----------\n";
    foreach ($task->getOptions() as $option)
    {
      $default = $option->acceptParameter() && !is_null($option->getDefault()) && (!is_array($option->getDefault()) || count($option->getDefault())) ? sprintf('%s', is_array($option->getDefault()) ? str_replace("\n", '', print_r($option->getDefault(), true)) : $option->getDefault()) : '-';
      $multiple = $option->isArray() ? ' (multiple values allowed)' : '';

      $options .= sprintf("| `%s%s` | `%s` | %s\n", '--'.$option->getName(), $option->getShortcut() ? sprintf('`<br />`(-%s)', $option->getShortcut()) : '', $default, $option->getHelp().$multiple);
    }
  }

  $content .= <<<EOF
### ~`$name`~

The `$name` task $brief:

    $synopsis

$aliases

$arguments

$options

$description


EOF;
}

echo <<<EOF
Tasks
=====

The symfony framework comes bundled with a command line interface tool.
Built-in tasks allow the developer to perform a lot of fastidious and
recurrent tasks in the life of a project.

If you execute the `symfony` CLI without any arguments, a list of available
tasks is displayed:

    $ php symfony

By passing the `-V` option, you get some information about the version of
symfony and the path of the symfony libraries used by the CLI:

    $ php symfony -V

The CLI tool takes a task name as its first argument:

    $ php symfony list

A task name can be composed of an optional namespace and a name, separated by
a colon (`:`):

    $ php symfony cache:clear

After the task name, arguments and options can be passed:

    $ php symfony cache:clear --type=template

The CLI tool supports both long options and short ones, with or without
values.

The `-t` option is a global option to ask any task to output more debugging
information.

<div class="pagebreak"></div>

Available Tasks
---------------

$toc

<div class="pagebreak"></div>

$content


EOF;
