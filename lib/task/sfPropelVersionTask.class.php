<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Displays the currently installed version of Propel
 *
 * @package    symfony
 * @subpackage propel
 * @author     Christoph Rosse <christoph@rosse.at>
 */
class sfPropelVersionTask extends sfPropelBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->namespace = 'propel';
    $this->name = 'version';
    $this->briefDescription = 'Displays the currently installed version of Propel';
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  { 
    $this->logBlock(Propel::VERSION, 'INFO');
  }
}
