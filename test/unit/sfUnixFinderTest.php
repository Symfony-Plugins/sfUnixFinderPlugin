<?php
$app = 'frontend';
$debug = true;
include(dirname(__FILE__).'/../bootstrap/unit.php');
define('FIXTURES_PATH', realpath(dirname(__FILE__).'/../fixtures/'));
sfSimpleAutoload::getInstance()->addDirectory(dirname(__FILE__).'/../../lib/');
sfSimpleAutoload::getInstance()->register();

$t = new lime_test(null, new lime_output_color());

# where is our find binary ?
sfConfig::set('app_sfUnixFinderPlugin_which', $path='/usr/bin/find');
$t->is(sfUnixFinder::_which(), $path, 'custom find binary path');
sfConfig::set('app_sfUnixFinderPlugin_which', null);
$t->is(sfUnixFinder::_which(), 'find', 'default find binary path');

# find with error in initialization path
try
{
  $finder = new sfUnixFinder();

  $t->fail('Constructor needs a valid path.');
}
catch (InvalidArgumentException $e)
{
  $t->pass('Constructor needs a valid path.');
}
catch (Exception $e)
{
  $t->fail('Constructor needs a valid path. (invalid exception type)');
}

# find on fixtures directory
try
{
  $finder = new sfUnixFinder(FIXTURES_PATH);
  $t->pass('Constructor works with a valid path.');

  $result = $finder->name('test')->execute();

  $t->is(count($result), 1, 'simple find, elem count is ok');
}
catch (Exception $e)
{
  $t->fail('Constructor works with a valid path.');
}

