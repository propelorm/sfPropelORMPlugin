<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define('SF_DIR', dirname(__FILE__).'/../../../../lib/vendor/symfony/');

// we need SQLite for functional tests
if (!extension_loaded('SQLite') && !extension_loaded('pdo_SQLite'))
{
  echo "SQLite extension is required to run unit tests\n";
  return false;
}

if (!isset($root_dir))
{
  $root_dir = realpath(dirname(__FILE__).sprintf('/../%s/fixtures', isset($type) ? $type : 'functional'));
}

require_once $root_dir.'/config/ProjectConfiguration.class.php';
$configuration = ProjectConfiguration::getApplicationConfiguration($app, 'test', isset($debug) ? $debug : true);
sfContext::createInstance($configuration);

// FIXME
// for behavior inclusions
// this a bit dirty, but allow functionnal tests to work.
sfToolkit::addIncludePath(array(
  realpath(dirname(__FILE__).'/../../'),
));

// remove all cache
sf_functional_test_shutdown();
register_shutdown_function('sf_functional_test_shutdown');

$configuration->initializePropel($app);
if (isset($fixtures))
{
  $configuration->loadFixtures($fixtures);
}

function sf_functional_test_shutdown_cleanup()
{
  sfToolkit::clearDirectory(sfConfig::get('sf_cache_dir'));
  sfToolkit::clearDirectory(sfConfig::get('sf_log_dir'));
}

function sf_functional_test_shutdown()
{
  // try/catch needed due to http://bugs.php.net/bug.php?id=33598
  try
  {
    sf_functional_test_shutdown_cleanup();
  }
  catch (Exception $e)
  {
    echo $e.PHP_EOL;
  }
}

return true;
