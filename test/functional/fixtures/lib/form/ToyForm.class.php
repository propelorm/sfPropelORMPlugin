<?php

/**
 * Toy form.
 *
 * @package    propel
 * @subpackage form
 * @author     Your name here
 */
class ToyForm extends BaseToyForm
{
  public function configure()
  {
    $this->embedI18n(array('fr', 'en'));
  }
}
