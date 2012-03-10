<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define('SF_DIR', dirname(__FILE__).'/../../../../lib/vendor/symfony/');
require_once SF_DIR . 'test/bootstrap/unit.php';
require_once SF_DIR . 'lib/autoload/sfSimpleAutoload.class.php';

$autoload = sfSimpleAutoload::getInstance(sys_get_temp_dir().DIRECTORY_SEPARATOR.sprintf('sf_autoload_unit_propel_%s.data', md5(__FILE__)));
$autoload->addDirectory(realpath(dirname(__FILE__).'/../../lib'));
$autoload->addDirectory(realpath(dirname(__FILE__).'/../../lib/vendor/propel'));
$autoload->addDirectory(realpath(dirname(__FILE__).'/../../lib/vendor/phing/classes'));
$autoload->register();

$_test_dir = realpath(dirname(__FILE__).'/..');

sfToolkit::addIncludePath(dirname(__FILE__).'/../../lib/vendor');
sfToolkit::addIncludePath(dirname(__FILE__).'/../../lib/vendor/propel');
sfToolkit::addIncludePath(dirname(__FILE__).'/../../lib/vendor/phing/classes');
