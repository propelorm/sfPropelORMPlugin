<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once 'generator/lib/util/PropelPHPParser.php';

/**
 * A database behavior that adds default symfony behaviors.
 *
 * @package     sfPropelPlugin
 * @subpackage  behavior
 * @author      Kris Wallsmith <kris.wallsmith@symfony-project.com>
 * @version     SVN: $Id: SfPropelBehaviorSymfony.php 23737 2009-11-09 23:23:25Z Kris.Wallsmith $
 */
class SfPropelBehaviorSymfony extends SfPropelBehaviorBase
{
  protected $parameters = array(
    'form'   => 'true',
    'filter' => 'true',
  );

  public function modifyDatabase()
  {
    foreach ($this->getDatabase()->getTables() as $table)
    {
      $behaviors = $table->getBehaviors();

      if (!isset($behaviors['symfony']))
      {
        $behavior = clone $this;
        $table->addBehavior($behavior);
      }

      // symfony behaviors
      if (!isset($behaviors['symfony_behaviors']) && $this->getBuildProperty('propel.builder.addBehaviors'))
      {
        $class = Propel::importClass($this->getBuildProperty('propel.behavior.symfony_behaviors.class'));
        $behavior = new $class();
        $behavior->setName('symfony_behaviors');
        $table->addBehavior($behavior);
      }

      // timestampable
      if (!isset($behaviors['symfony_timestampable']))
      {
        $parameters = array();
        foreach ($table->getColumns() as $column)
        {
          if (!isset($parameters['create_column']) && in_array($column->getName(), array('created_at', 'created_on')))
          {
            $parameters['create_column'] = $column->getName();
          }

          if (!isset($parameters['update_column']) && in_array($column->getName(), array('updated_at', 'updated_on')))
          {
            $parameters['update_column'] = $column->getName();
          }
        }

        if ($parameters)
        {
          $class = Propel::importClass($this->getBuildProperty('propel.behavior.symfony_timestampable.class'));
          $behavior = new $class();
          $behavior->setName('symfony_timestampable');
          $behavior->setParameters($parameters);
          $table->addBehavior($behavior);
        }
      }
    }
  }

  public function staticMethods()
  {
    if ($this->isDisabled())
    {
      return;
    }

    $unices = array();
    foreach ($this->getTable()->getUnices() as $unique)
    {
      $unices[] = sprintf("array('%s')", implode("', '", $unique->getColumns()));
    }
    $unices = implode(', ', array_unique($unices));

    $script = <<<EOF

/**
 * Returns an array of arrays that contain columns in each unique index.
 *
 * @return array
 */
static public function getUniqueColumnNames()
{
  return array({$unices});
}

EOF;
    $behaviors = $this->getTable()->getBehaviors();
    if(isset($behaviors['i18n']))
    {
      $i18nTablePhpName = $behaviors['i18n']->getI18nTable()->getPhpName();
      $script.= <<<EOF

/** 
 * Returns the current assocciated i18n model name 
 * 
 * @return    string model name 
 */ 
public static function getI18nModel() 
{ 
  return '{$i18nTablePhpName}'; 
} 

EOF;
    }    
    return $script;
  }
  
  public function objectFilter(&$script, $builder)
  {
    $behaviors = $this->getTable()->getBehaviors();
    if(isset($behaviors['i18n']))
    {
      $table = $this->getTable();
      $tablePhpName = $this->getTable()->getPhpName();
      $i18nTable = $behaviors['i18n']->getI18nTable();
      $i18nTablePhpName = $behaviors['i18n']->getI18nTable()->getPhpName();
      $pattern = '/foreach \(\$this->coll'.$i18nTablePhpName.'s as \$referrerFK\) \{/';
      $addition = "
        \$referrerFK->set".$tablePhpName."(\$this);";
      $replacement = "\$0$addition";
      $script = preg_replace($pattern, $replacement, $script);
      $pattern = '/protected \$currentLocale = \'.*\';/';
      $replacement = 'protected \$currentLocale = null;';
      $script = preg_replace($pattern, $replacement, $script);            
      $pattern = '/\(\$locale = \'.*\'/';
      $replacement = '(\$locale';
      $script = preg_replace($pattern, $replacement, $script);
      $getLocale .= <<<EOF
                
            
/**
 * Gets the locale for translations
 *
 * @return    string $locale Locale to use for the translation, e.g. 'fr_FR'
 */
public function getLocale()
{
  if(null == \$this->currentLocale)
  {
    return sfPropel::getDefaultCulture();
  }
  else
  {
    return \$this->currentLocale;
  }
}
EOF;
      $parser = new PropelPHPParser($script, true);
      $parser->replaceMethod('getLocale', $getLocale);
      $script = $parser->getCode();
      $setLocale .= <<<EOF
   
   
/**
 * Sets the locale for translations
 *
 * @param     string \$locale Locale to use for the translation, e.g. 'fr_FR'
 *
 * @return    $tablePhpName The current object (for fluent API support)
 */
public function setLocale(\$locale)
{
  if(null == \$this->currentLocale)
  {
    \$this->currentLocale =  sfPropel::getDefaultCulture();
  }
  else
  {
    \$this->currentLocale = \$locale;
  }
  return \$this;
}
EOF;
      $parser = new PropelPHPParser($script, true);
      $parser->replaceMethod('setLocale', $setLocale);
      //$script = $parser->getCode();
    }   
  }
  
  public function queryFilter(&$script, $builder)
  {
    $behaviors = $this->getTable()->getBehaviors();
    if(isset($behaviors['i18n']))
    {
      $pattern = '/(public function .*\()\$locale = \'.*\'(, .*\)[\r\n\t ]+\{)/';
      $replacement = <<<EOF
$1\$locale=null$2
    if(null == \$locale)
    {
        \$locale = sfPropel::getDefaultCulture();
    }
EOF;
      $script = preg_replace($pattern, $replacement, $script);      
    }
  }
}
