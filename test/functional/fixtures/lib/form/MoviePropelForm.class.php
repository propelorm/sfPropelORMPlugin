<?php

/**
 * MoviePropel form.
 *
 * @package    propel
 * @subpackage form
 * @author     Your name here
 */
class MoviePropelForm extends BaseMoviePropelForm
{
  public function configure()
  {
    $this->embedI18n(array('en', 'fr'));
    $this->embedRelation('ToyPropel');
  }
}
