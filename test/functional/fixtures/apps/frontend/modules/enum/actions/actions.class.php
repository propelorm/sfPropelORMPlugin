<?php

/**
 * choice actions.
 *
 * @package    ##PROJECT_NAME##
 * @subpackage choice
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 16987 2009-04-04 14:16:46Z fabien $
 */
class enumActions extends sfActions
{
  public function executeEnum($request)
  {
    $this->form = new EnumSampleForm();
    $this->form->setDefault('enum_values','three space');
  }
}
