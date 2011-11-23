<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define('SF_DIR', dirname(__FILE__).'/../../../../lib/vendor/symfony/');

require_once SF_DIR . 'lib/vendor/lime/lime.php';
require_once SF_DIR . 'lib/util/sfToolkit.class.php';
require_once SF_DIR . 'lib/util/sfFinder.class.php';

if ($files = glob(sys_get_temp_dir().DIRECTORY_SEPARATOR.'/sf_autoload_unit_*'))
{
  foreach ($files as $file)
  {
    unlink($file);
  }
}

$h = new lime_harness(new lime_output_color);
$h->base_dir = realpath(dirname(__FILE__).'/..');

$h->register(sfFinder::type('file')->prune('fixtures')->name('*Test.php')->in(array(
  $h->base_dir.'/unit',
  $h->base_dir.'/functional',
)));

$code = $h->run();

file_put_contents('junit.xml', $h->to_xml());

exit($code ? 0 : 1);
