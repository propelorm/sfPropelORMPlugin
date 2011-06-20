# Installing sfPropel15Plugin With Propel 1.6

This version of sfPropelPlugin doesn't come bundled with the required dependencies. To install the plugin on a symfony application, follow these steps:

    > cd plugins/
    > git clone git://github.com/fzaninotto/sfPropel15Plugin.git -b 1.6
    > cd sfPropel15Plugin/
    > cd lib/
    > mkdir vendor
    > cd vendor/
    > svn checkout http://phing.mirror.svn.symfony-project.com/tags/2.3.3/classes/phing phing
    > svn checkout http://svn.propelorm.org/branches/1.6/runtime/lib propel
    > svn checkout http://svn.propelorm.org/branches/1.6/generator propel-generator
    > cd ../../..

Now the classic install continues.

Right after the installation of the plugin, you should update plugin assets:

    > ./symfony plugin:publish-assets

Disable the core Propel plugin and enable the `sfPropel15Plugin` instead:

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        $this->enablePlugins('sfPropel15Plugin');
      }
    }

Change the path of the symfony behaviors in the `config/propel.ini` file of your project:

    [ini]
    propel.behavior.symfony.class                  = plugins.sfPropel15Plugin.lib.behavior.SfPropelBehaviorSymfony
    propel.behavior.symfony_i18n.class             = plugins.sfPropel15Plugin.lib.behavior.SfPropelBehaviorI18n
    propel.behavior.symfony_i18n_translation.class = plugins.sfPropel15Plugin.lib.behavior.SfPropelBehaviorI18nTranslation
    propel.behavior.symfony_behaviors.class        = plugins.sfPropel15Plugin.lib.behavior.SfPropelBehaviorSymfonyBehaviors
    propel.behavior.symfony_timestampable.class    = plugins.sfPropel15Plugin.lib.behavior.SfPropelBehaviorTimestampable

You're done.
