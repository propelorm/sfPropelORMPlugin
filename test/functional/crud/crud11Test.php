<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$app = 'crud';
$fixtures = 'fixtures/fixtures.yml';
if (!include(dirname(__FILE__).'/../../bootstrap/functional.php'))
{
  return;
}

require_once(dirname(__FILE__).'/crudBrowser.class.php');
require_once(dirname(__FILE__).'/crudBrowserAdmin15.class.php');

$b = new CrudBrowserAdmin15();
$b->browse(array('with-show', 'route-prefix=acme_article', 'theme=admin15', 'generate-in-cache'), array(
  'urlPrefix' => 'more_articles',
  'moduleName' => 'article_backend',
));
