<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormActivable represents link capable of adding a widget schema to a form
 *
 * @package    symfony
 * @subpackage widget
 * @author     Francois Zaninotto
 */
class sfWidgetFormSchemaOptional extends sfWidgetFormSchemaDecoratorEscaped
{

  /**
   * Constructor.
   *
   * @param sfWidgetFormSchema $widget     A sfWidgetFormSchema instance
   * @param string             $decorator  A decorator string
   *
   * @see sfWidgetFormSchema
   */

  private $widgetJsString = '';

  public function __construct(sfWidgetFormSchema $widget, $decorator, $options = array())
  {
    parent::__construct($widget, $decorator);
    $this->addOption('add_link', 'Add new');
    $this->addOption('max_additions', 0);
    $this->options = array_merge($this->options, $options);
  }

  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $widgetString = $this->widget->render($name, $value, $attributes, $errors);
    preg_match_all('@<[ \n\t]*(?:script(?:"[^"]*"|\'[^\']*\'|[^\'">])*)>(?P<js>.*?)<(?:[ \n\t]*/[ \n\t]*script[ \n\t]*)>@is', $widgetString, $javascripts );
    if($javascripts['js'] ){
      foreach($javascripts['js'] as $js){
      	$this->widgetJsString .= $js.';';
      }
    }
    $widgetStringWithoutJs = preg_replace('@<[ \n\t]*(?:script(?:"[^"]*"|\'[^\']*\'|[^\'">])*)>(?:.*?)<(?:[ \n\t]*/[ \n\t]*script[ \n\t]*)>@is', '', $widgetString );
    $this->widgetJsString = $this->escape($this->widgetJsString);
    return strtr($this->getDecorator($name), array('%content%' => $this->escape($widgetStringWithoutJs)));
  }

  protected function getDecorator($name)
  {
    $strippedName = substr($name, strrpos($name, '[') + 1, strrpos($name, ']') - strrpos($name, '[') - 1);
    $decorator = $this->escape($this->decorator);
    $decorator = "
<script type=\"text/javascript\">
/* <![CDATA[ */
var added{$strippedName} = 0;
function add{$strippedName}Widget()
{
  added{$strippedName} += 1;
  var content = \"{$decorator}\";
  var spanTag = document.createElement(\"span\");
  spanTag.innerHTML = content.replace(/([_\[]){$strippedName}([_\]])/g, '\$1{$strippedName}' +  + added{$strippedName} + '\$2');
  document.getElementById('add_{$strippedName}').appendChild(spanTag);
  document.getElementById('add_{$strippedName}').style.display='block';";
    if ($this->getOption('max_additions') > 0) {
        $decorator .= "
  if (added{$strippedName} == {$this->getOption('max_additions')})
  {
    document.getElementById('add_{$strippedName}_link').style.display='none';
  }";
    }

  if ($this->widgetJsString) {
    $decorator .= "
    var globalEval = function globalEval(src) {
      if (window.execScript) {
        window.execScript(src);
        return;
      }
      var fn = function() {
        window.eval.call(window,src);
      };
      fn();
    };
    globalEval({$strippedName}Js.replace(/([_\[]){$strippedName}([_\]])/g, '\$1{$strippedName}' +  + added{$strippedName} + '\$2'))";
  }
    
  $decorator .= "
}
/* ]]> */
</script>
<div id=\"add_{$strippedName}\" style=\"display:none\">
</div>
<a href=\"#\" id = \"add_{$strippedName}_link\" onclick=\"add{$strippedName}Widget();return false;\">
  {$this->getOption('add_link')}
</a>";

    return $decorator;
  }
}
