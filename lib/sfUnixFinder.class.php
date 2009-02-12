<?php

if (DIRECTORY_SEPARATOR != '/')
{
  throw new Exception('Cannot use sfUnixFinder on a non UNIX operating system.');
}

abstract class sfUnixFinderCommandBase
{
  public function __construct()
  {
    $this->initialize();
  }

  abstract protected function initialize();

  public function render()
  {
    return $this->command.(strlen($this->parameter)?(' '.$this->parameter):'');
  }
}

class sfUnixFinderCommandOption extends sfUnixFinderCommandBase
{
  protected
    $command   = null,
    $parameter = '';

  public function __construct($command, $parameter='')
  {
    $this->command   = $command;
    $this->parameter = $parameter;

    parent::__construct();
  }

  protected function initialize()
  {
  }
}

class sfUnixFinderCommandSchema extends sfUnixFinderCommandBase
{
  protected
    $commands = array();

  protected function initialize()
  {
  }

  public function add(sfUnixFinderCommandBase $command)
  {
    $this->commands[] = $command;
  }

  public function render()
  {
    $commandLine = '';

    foreach ($this->commands as $command)
    {
      $commandLine .= isset($commandLine[0]) ? ' ' : '';

      if ($command instanceof sfUnixFinderCommandSchema)
      {
        $commandLine .= '( '.$command->render().' )';
      }
      else
      {
        $commandLine .= $command->render();
      }
    }

    return $commandLine;
  }
}

class sfUnixFinder
{
  protected
    $schema     = null,
    $path       = null,
    $criterions = array();

  static public function create($path=null)
  {
    return new self($path);
  }

  static public function isPath($path)
  {
    return file_exists($path)&&is_dir($path);
  }

  static public function _which()
  {
    return sfConfig::get('app_sfUnixFinderPlugin_which', 'find');
  }

  public function __construct($path=null)
  {
    if (!self::isPath($path))
    {
      throw new InvalidArgumentException('Invalid path given.');
    }

    $this->path = $path;
    $this->schema = new sfUnixFinderCommandSchema();
  }

  public function execute()
  {
    exec(self::_which().' '.$this->path.' '.$this->schema->render(), $result);
    return $result;
  }

  public function __call($command, $parameters)
  {
    try
    {
      $command = isset($parameters[1]) ? $parameters[1].$command : '-'.$command;
      $this->schema->add(new sfUnixFinderCommandOption($command, $parameters[0]));
    }
    catch (Exception $e)
    {
      throw $e;
    }

    return $this;
  }
}

