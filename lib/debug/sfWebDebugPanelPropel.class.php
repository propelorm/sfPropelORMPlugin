<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */



/**
 * sfWebDebugPanelPropel adds a panel to the web debug toolbar with Propel information.
 *
 * @package    symfony
 * @subpackage debug
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfWebDebugPanelPropel.class.php 24993 2009-12-06 20:56:49Z Kris.Wallsmith $
 */
class sfWebDebugPanelPropel extends sfWebDebugPanel
{
  /**
   * Get the title/icon for the panel
   *
   * @return string $html
   */
  public function getTitle()
  {
    if ($sqlLogs = $this->getSqlLogs())
    {
      return '<img src="'.$this->webDebug->getOption('image_root_path').'/database.png" alt="SQL queries" /> '.count($sqlLogs);
    }
  }

  /**
   * Get the verbal title of the panel
   *
   * @return string $title
   */
  public function getPanelTitle()
  {
    return 'SQL queries';
  }

  /**
   * Get the html content of the panel
   *
   * @return string $html
   */
  public function getPanelContent()
  {
    return '
      <div id="sfWebDebugDatabaseLogs">
        <h3>Propel Version: '.Propel::VERSION.'</h3>
        <ol>'.implode("\n", $this->getSqlLogs()).'</ol>
      </div>
      <script type="text/javascript">
        function sfWebDebbugGetXMLHttpRequest() {
        	var xhr = null;

        	if (window.XMLHttpRequest || window.ActiveXObject) {
        		if (window.ActiveXObject) {
        			try {
        				xhr = new ActiveXObject("Msxml2.XMLHTTP");
        			} catch(e) {
        				xhr = new ActiveXObject("Microsoft.XMLHTTP");
        			}
        		} else {
        			xhr = new XMLHttpRequest();
        		}
        	} else {
        		alert("Your browser do no support XMLHTTPRequest");
        		return null;
        	}

        	return xhr;
        }

        function sfWebDebbugDoExplain(url, args, area)
        {
           var xhr = sfWebDebbugGetXMLHttpRequest();

           xhr.onreadystatechange = function() {
        	  if (xhr.readyState == 4 && (xhr.status == 200 || xhr.status == 0)) {
        		document.getElementById(area).innerHTML = xhr.responseText;
        	  }
           };

           xhr.open("POST", url, true);
           xhr.send(args)
        }
      </script>
    ';
  }

  /**
   * Listens to debug.web.load_panels and adds this panel.
   */
  static public function listenToAddPanelEvent(sfEvent $event)
  {
    $event->getSubject()->setPanel('db', new self($event->getSubject()));
  }

  /**
   * Builds the sql logs and returns them as an array.
   *
   * @return array
   */
  protected function getSqlLogs()
  {
    $config    = $this->getPropelConfiguration();
    $outerGlue = $config->getParameter('debugpdo.logging.outerglue', ' | ');
    $innerGlue = $config->getParameter('debugpdo.logging.innerglue', ': ');
    $flagSlow  = $config->getParameter('debugpdo.logging.details.slow.enabled', false);
    $threshold = $config->getParameter('debugpdo.logging.details.slow.threshold', DebugPDO::DEFAULT_SLOW_THRESHOLD);

    $html = array();
    foreach ($this->webDebug->getLogger()->getLogs() as $j => $log)
    {
      if ('sfPropelLogger' != $log['type'])
      {
        continue;
      }

      $details = array();
      $explainlink = '';
      $slowQuery = false;

      $parts = explode($outerGlue, $log['message']);
      foreach ($parts as $i => $part)
      {
        // is this a key-glue-value fragment ?
        if (preg_match('/^(\w+)'.preg_quote($innerGlue, '/').'(.*)/', $part, $match))
        {
          $details[] = $part;
          unset($parts[$i]);

          // check for slow query
          if ('time' == $match[1])
          {
            if ($flagSlow && (float) $match[2] > $threshold)
            {
              $slowQuery = true;
              if ($this->getStatus() > sfLogger::NOTICE)
              {
                $this->setStatus(sfLogger::NOTICE);
              }
            }
          }

          // Find connection for explain
          if ('connection' == $match[1])
          {
              $connection = $match[2];
          }
        }
      }
      // all stuff that has not been eaten by the loop should be the query string
      $query = join($outerGlue, $parts);

      if(isset($connection))
      {
        $explainlink = ', <a onclick="
        var formData = new FormData();
        formData.append(\'connection\', \''.$connection.'\');
        formData.append(\'base64_query\', \''.base64_encode($query).'\');

        sfWebDebbugDoExplain(
        \''.sfContext::getInstance()->getRouting()->generate('propel_debug_pannel').'\'
        , formData
        , \'explain_'.$j.'\')">Explain the query</a>';
      }

      if ($query == "SET NAMES 'utf8'")
      {
        // This is the initialization query that occurs on every request.
        // It is not significant, so prune it.
        continue;
      }
      $query = $this->formatSql(htmlspecialchars($query, ENT_QUOTES, sfConfig::get('sf_charset')));
      $backtrace = isset($log['debug_backtrace']) && count($log['debug_backtrace']) ? '&nbsp;'.$this->getToggleableDebugStack($log['debug_backtrace']) : '';

      $html[] = sprintf('
        <li class="%s">
          <p class="sfWebDebugDatabaseQuery">%s</p>
          <div class="sfWebDebugDatabaseLogInfo">%s%s%s</div>
          <div class="sfWebDebugDatabaseExplain" id="explain_'.$j.'"></div>
        </li>',
        $slowQuery ? 'sfWebDebugWarning' : '',
        $query,
        implode(', ', $details),
        $explainlink,
        $backtrace
      );
    }

    return $html;
  }

  /**
   * Returns the current PropelConfiguration.
   *
   * @return PropelConfiguration
   */
  protected function getPropelConfiguration()
  {
    return Propel::getConfiguration(PropelConfiguration::TYPE_OBJECT);
  }
}
