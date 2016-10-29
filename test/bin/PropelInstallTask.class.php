<?php

class PropelInstallTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'propel';
    $this->name             = 'install';
    $this->briefDescription = '';
    $this->detailedDescription = '';
  }

  protected function execute($arguments = array(), $options = array())
  {
    $this->logSection('install', 'default to sqlite');
    $this->runTask('configure:database', sprintf("'sqlite:%s/propel.db'", sfConfig::get('sf_data_dir')));

    $this->logSection('install', 'fix sqlite database permissions');
    touch(sfConfig::get('sf_data_dir').'/propel.db');
    chmod(sfConfig::get('sf_data_dir'), 0777);
    chmod(sfConfig::get('sf_data_dir').'/propel.db', 0777);

    $this->logSection('install', 'setup propel behavior');
    $ini_file = sfConfig::get('sf_config_dir').'/propel.ini';
    $content = file_get_contents($ini_file);
    preg_replace('#^propel.behavior#', ';\1', $content);
    $content .= <<<EOF
    propel.behavior.default                        = symfony,symfony_i18n
    propel.behavior.symfony.class                  = plugins.sfPropelORMPlugin.lib.behavior.SfPropelBehaviorSymfony
    propel.behavior.symfony_i18n.class             = plugins.sfPropelORMPlugin.lib.behavior.SfPropelBehaviorI18n
    propel.behavior.symfony_i18n_translation.class = plugins.sfPropelORMPlugin.lib.behavior.SfPropelBehaviorI18nTranslation
    propel.behavior.symfony_behaviors.class        = plugins.sfPropelORMPlugin.lib.behavior.SfPropelBehaviorSymfonyBehaviors
    propel.behavior.symfony_timestampable.class    = plugins.sfPropelORMPlugin.lib.behavior.SfPropelBehaviorTimestampable
EOF;

    file_put_contents($ini_file, $content);

    $this->runTask('cache:clear');
  }
}
