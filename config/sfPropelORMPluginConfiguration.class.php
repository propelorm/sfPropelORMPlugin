<?php

/**
 * sfPropelPlugin configuration.
 *
 * @package    sfPropelPlugin
 * @subpackage config
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfPropelPluginConfiguration.class.php 23739 2009-11-09 23:32:46Z Kris.Wallsmith $
 */
class sfPropelORMPluginConfiguration extends sfPluginConfiguration
{
  /**
   * @see sfPluginConfiguration
   */
  public function initialize()
  {
    sfConfig::set('sf_orm', 'propel');

    if (!sfConfig::get('sf_admin_module_web_dir'))
    {
      sfConfig::set('sf_admin_module_web_dir', '/sfPropelORMPlugin');
    }

    if (false === sfConfig::get('sf_phing_path', false))
    {
      sfConfig::set('sf_phing_path', realpath(dirname(__FILE__).'/../lib/vendor/phing'));
    }

    if (false === sfConfig::get('sf_propel_path', false))
    {
      sfConfig::set('sf_propel_path', realpath(dirname(__FILE__).'/../lib/vendor/propel'));
    }

    sfToolkit::addIncludePath(array(
      sfConfig::get('sf_root_dir'),
      sfConfig::get('sf_phing_path'),
      sfConfig::get('sf_propel_path'),
      sfConfig::get('sf_phing_path').'/../',
      sfConfig::get('sf_phing_path').'/classes',
    ));

    require_once 'runtime/lib/Propel.php';

    if (!Propel::isInit())
    {
      if (sfConfig::get('sf_debug') && sfConfig::get('sf_logging_enabled'))
      {
        Propel::setLogger(new sfPropelLogger($this->dispatcher));
      }

      $propelConfiguration = new PropelConfiguration();
      Propel::setConfiguration($propelConfiguration);

      $this->dispatcher->notify(new sfEvent($propelConfiguration, 'propel.configure'));

      Propel::initialize();
    }

    $this->dispatcher->connect('user.change_culture', array('sfPropel', 'listenToChangeCultureEvent'));

    if (sfConfig::get('sf_web_debug'))
    {
      $this->dispatcher->connect('debug.web.load_panels', array('sfWebDebugPanelPropel', 'listenToAddPanelEvent'));

      $modules = sfConfig::get('sf_enabled_modules', array());
      $modules[] = 'sfPropelORMExplain';

      sfConfig::set('sf_enabled_modules', $modules);
    }

    if (sfConfig::get('sf_test'))
    {
      $this->dispatcher->connect('context.load_factories', array($this, 'clearAllInstancePools'));
    }
  }

  /**
   * Clears all instance pools.
   *
   * This method is used to clear Propel's static instance pools between
   * requests performed in functional tests.
   */
  public function clearAllInstancePools()
  {
    $finder = sfFinder::type('file')->name('*TableMap.php');
    foreach ($finder->in($this->configuration->getModelDirs()) as $file)
    {
      $omClass = basename($file, 'TableMap.php');
      if (class_exists($omClass) && is_subclass_of($omClass, 'BaseObject'))
      {
        $peer = constant($omClass.'::PEER');
        call_user_func(array($peer, 'clearInstancePool'));
      }
    }
  }
}
