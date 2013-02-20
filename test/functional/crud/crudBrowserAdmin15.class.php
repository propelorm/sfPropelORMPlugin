<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class CrudBrowserAdmin15 extends CrudBrowser
{
  protected $humanizedModuleName;

  public function setup($options, $parameters = array())
  {
    $ret = parent::setup($options, $parameters);

    $this->humanizedModuleName = sfInflector::humanize($this->moduleName);

    return $ret;
  }

  public function browse($options, $parameters = array())
  {
    $options = $this->setup($options, $parameters);

    // list page
    $this->
      info('list page')->
      get('/'.$this->urlPrefix)->
      with('request')->begin()->
        isParameter('module', $this->moduleName)->
        isParameter('action', 'index')->
      end()->
      with('response')->begin()->
        isStatusCode(200)->

        checkElement('h1', $this->humanizedModuleName.' List')->

        checkElement('#sf_admin_content table thead tr th:nth(1) a', 'Id')->
        checkElement('#sf_admin_content table thead tr th:nth(2) a', 'Title')->
        checkElement('#sf_admin_content table thead tr th:nth(3) a', 'Body')->
        checkElement('#sf_admin_content table thead tr th:nth(4) a', 'Online')->
        checkElement('#sf_admin_content table thead tr th:nth(5) a', 'Excerpt')->
        checkElement('#sf_admin_content table thead tr th:nth(6) a', 'Category')->
        checkElement('#sf_admin_content table thead tr th:nth(7) a', 'Created at')->
        checkElement('#sf_admin_content table thead tr th:nth(8) a', 'End date')->
        checkElement('#sf_admin_content table thead tr th:nth(9) a', 'Book')->

        // Regexps are used here because exact matching doesn't ignore preceding and trailing spaces in a value
        checkElement('#sf_admin_content table tbody tr td:nth(1) a', '/1/')->
        checkElement('#sf_admin_content table tbody tr td:nth(2)', '/foo title/')->
        checkElement('#sf_admin_content table tbody tr td:nth(3)', '/bar body/')->
        checkElement('#sf_admin_content table tbody tr td:nth(4) img[src=/sfPropelORMPlugin/images/tick.png]', true)->
        checkElement('#sf_admin_content table tbody tr td:nth(5)', '/foo excerpt/')->
        checkElement('#sf_admin_content table tbody tr td:nth(6)', '/1/')->
        // admin15 theme uses another date format, so we just check if there is time outputted
        checkElement('#sf_admin_content table tbody tr td:nth(7)', '/\d{1,2}\:\d{2}/')->
        checkElement('#sf_admin_content table tbody tr td:nth(8)', '/^\s*$/m')-> // spaces only
        checkElement('#sf_admin_content table tbody tr td:nth(9)', '/^\s*$/m')-> // spaces only
        checkElement(sprintf('a[href*="/%s/new"]', $this->urlPrefix))->
        checkElement(sprintf('tbody a[href*="/%s/1%s"]', $this->urlPrefix, in_array('with-show', $options) ? '' : '/edit'))->
        checkElement(sprintf('tbody a[href*="/%s/2%s"]', $this->urlPrefix, in_array('with-show', $options) ? '' : '/edit'))->
        checkElement('#sf_admin_content table tbody tr td .sf_admin_td_actions .sf_admin_action_moveup', 2)-> // two buttons - one for each row
        checkElement('#sf_admin_content table tbody tr td .sf_admin_td_actions .sf_admin_action_movedown', 2)-> // two buttons - one for each row
        checkElement('#sf_admin_content table tbody tr td .sf_admin_td_actions .sf_admin_action_movedown', 2)-> // two buttons - one for each row
        checkElement(sprintf('#sf_admin_content table tbody tr td .sf_admin_td_actions .sf_admin_action_movedown a[href^=/index.php/%s/]', $this->urlPrefix), 1)-> // the second button is disabled
        checkElement(sprintf('#sf_admin_content table tbody tr td .sf_admin_td_actions .sf_admin_action_moveup a[href^=/index.php/%s/]', $this->urlPrefix), 1)-> // the second button is disabled
      end()->
      click('Move up')->
      with('response')->begin()->
        isRedirected(true)-> // redirect after action
      end()
    ;

    $this->teardown();

    return $this;
  }
}
