<?php
/**
 * @author Julien Muetton
 * @see http://www.symfony-project.org/blog/2009/06/10/new-in-symfony-1-3-project-creation-customization
 */
$fs = new sfFilesystem();

$fs->mkdirs(sfConfig::get('sf_plugins_dir').'/sfPropelORMPlugin');

$root_dir = realpath(sfConfig::get('sf_root_dir').'/../');
$plugin_dir = realpath(sfConfig::get('sf_plugins_dir').'/sfPropelORMPlugin');

$finder = sfFinder::type('any')->ignore_version_control(false)->discard('mockproject')->prune('mockproject');
$fs->mirror($root_dir, $plugin_dir, $finder);

$fs->execute(sprintf('cd %s && git submodule update --init --recursive', $plugin_dir));

include dirname(__FILE__).'/../../config/installer.php';

$fs->mkdirs($task_dir = sfConfig::get('sf_lib_dir').'/task');
$fs->copy(dirname(__FILE__).'/PropelInstallTask.class.php', $task_dir.'/PropelInstallTask.class.php');

$this->runTask('cache:clear');
