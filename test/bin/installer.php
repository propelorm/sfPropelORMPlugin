<?php
/**
 * @author Julien Muetton
 * @see http://www.symfony-project.org/blog/2009/06/10/new-in-symfony-1-3-project-creation-customization
 */
$fs = new sfFilesystem();
$this->logSection('install', 'default to propel');

$this->logSection('install', 'default to sqlite');
$this->runTask('configure:database', sprintf("'sqlite:%s/propel.db'", sfConfig::get('sf_data_dir')));

$this->logSection('install', 'fix sqlite database permissions');
touch(sfConfig::get('sf_data_dir').'/propel.db');
chmod(sfConfig::get('sf_data_dir'), 0777);
chmod(sfConfig::get('sf_data_dir').'/propel.db', 0777);


$this->logSection('install', 'install propel 1.6');
sfSymfonyPluginManager::disablePlugin('sfPropelPlugin', sfConfig::get('sf_config_dir'));
$fs->execute(sprintf('git clone http://github.com/propelorm/sfPropelORMPlugin.git %s/sfPropelORMPlugin', sfConfig::get('sf_plugins_dir')));
$fs->execute(sprintf('cd %s/sfPropelORMPlugin ; git submodule update --init --recursive ; cd -;', sfConfig::get('sf_plugins_dir')));
sfSymfonyPluginManager::enablePlugin('sfPropelORMPlugin', sfConfig::get('sf_config_dir'));


$this->logSection('install', 'setup propel behavior');
$ini_file = sfConfig::get('sf_config_dir').'/propel.ini';
$content = file_get_contents($ini_file);
preg_replace('#^propel.behavior#', ';\1', $content);
$content .= <<<EOF
propel.behavior.default                        = symfony,symfony_i18n
propel.behavior.symfony.class                  = plugins.sfPropel15Plugin.lib.behavior.SfPropelBehaviorSymfony
propel.behavior.symfony_i18n.class             = plugins.sfPropel15Plugin.lib.behavior.SfPropelBehaviorI18n
propel.behavior.symfony_i18n_translation.class = plugins.sfPropel15Plugin.lib.behavior.SfPropelBehaviorI18nTranslation
propel.behavior.symfony_behaviors.class        = plugins.sfPropel15Plugin.lib.behavior.SfPropelBehaviorSymfonyBehaviors
propel.behavior.symfony_timestampable.class    = plugins.sfPropel15Plugin.lib.behavior.SfPropelBehaviorTimestampable
EOF;

file_put_contents($ini_file, $content);

$this->runTask('cache:clear');
