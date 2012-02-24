<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$app = 'frontend';
$fixtures = 'fixtures/fixtures.yml';
if (!include(dirname(__FILE__).'/../bootstrap/functional.php'))
{
  return;
}

$b = new sfTestBrowser(new sfBrowser());
$b->setTester('propel', 'sfTesterPropel');

// en
$b->
  get('/i18n/default')->
  with('request')->begin()->
    isParameter('module', 'i18n')->
    isParameter('action', 'default')->
  end()->
  with('response')->begin()->
    isStatusCode(200)->
    checkElement('#movies .toString:first', '')->
    checkElement('#movies .default:first', '')->
    checkElement('#movies .it:first', 'La Vita è bella')->
    checkElement('#movies .fr:first', 'La Vie est belle')->
  end()
;

// it
$b->
  get('/i18n/queryPropel')->
  with('request')->begin()->
    isParameter('module', 'i18n')->
    isParameter('action', 'queryPropel')->
  end()->
  with('response')->begin()->
    isStatusCode(200)->
    checkElement('#movies .propel_it:first', 'La Vita è bella')->
  end()
;

// fr
$b->
  get('/i18n/index')->
  with('request')->begin()->
    isParameter('module', 'i18n')->
    isParameter('action', 'index')->
  end()->
  with('response')->begin()->
    isStatusCode(200)->
    checkElement('#movies .toString:first', 'La Vie est belle')->
    checkElement('#movies .default:first', 'La Vie est belle')->
    checkElement('#movies .it:first', 'La Vita è bella')->
    checkElement('#movies .fr:first', 'La Vie est belle')->
  end()
;

// still fr
$b->
  get('/i18n/default')->
  with('request')->begin()->
    isParameter('module', 'i18n')->
    isParameter('action', 'default')->
  end()->
  with('response')->begin()->
    isStatusCode(200)->
    checkElement('#movies .toString:first', 'La Vie est belle')->
    checkElement('#movies .default:first', 'La Vie est belle')->
    checkElement('#movies .it:first', 'La Vita è bella')->
    checkElement('#movies .fr:first', 'La Vie est belle')->
  end()
;

// SfPropelBehaviorI18n (part of sfPropelORMPlugin)
$b->
  get('/i18n/movie')->
  with('request')->begin()->
    isParameter('module', 'i18n')->
    isParameter('action', 'movie')->
  end()->
  with('response')->begin()->
    isStatusCode(200)->
    checkElement('#movie_fr_id', false)->
    checkElement('#movie_fr_culture', false)->
  end()->

  click('submit', array('movie' => array('director' => 'Robert Aldrich', 'en' => array('title' => 'The Dirty Dozen'), 'fr' => array('title' => 'Les Douze Salopards'))))->
  with('response')->begin()->
    isRedirected()->
    followRedirect()->
  end()->
  with('response')->begin()->
    checkElement('input[value="Robert Aldrich"]')->
    checkElement('input[value="The Dirty Dozen"]')->
    checkElement('input[value="Les Douze Salopards"]')->
    checkElement('#movie_fr_id', true)->
    checkElement('#movie_fr_culture', true)->
  end()->

  with('propel')->begin()->
    check('Movie', array(), 2)->
    check('Movie', array('director' => 'Robert Aldrich', 'id' => 2))->
    check('MovieI18N', array(), 4)->
    check('MovieI18N', array('id' => 2), 2)->
    check('MovieI18N', array('culture' => 'fr', 'id' => 2, 'title' => 'Les Douze Salopards'))->
    check('MovieI18N', array('culture' => 'en', 'id' => 2, 'title' => 'The Dirty Dozen'))->
  end()->

  click('submit', array('movie' => array('director' => 'Robert Aldrich (1)', 'en' => array('title' => 'The Dirty Dozen (1)'), 'fr' => array('title' => 'Les Douze Salopards (1)'))))->
  with('response')->begin()->
    isRedirected()->
    followRedirect()->
  end()->
  with('response')->begin()->
    checkElement('input[value="Robert Aldrich (1)"]')->
    checkElement('input[value="The Dirty Dozen (1)"]')->
    checkElement('input[value="Les Douze Salopards (1)"]')->
  end()->

  with('propel')->begin()->
    check('Movie', array(), 2)->
    check('Movie', array('director' => 'Robert Aldrich (1)', 'id' => 2))->
    check('MovieI18N', array(), 4)->
    check('MovieI18N', array('id' => 2), 2)->
    check('MovieI18N', array('culture' => 'fr', 'id' => 2, 'title' => 'Les Douze Salopards (1)'))->
    check('MovieI18N', array('culture' => 'en', 'id' => 2, 'title' => 'The Dirty Dozen (1)'))->
  end()->
  // Bug #7486
  click('submit')->

  with('form')->begin()->
    hasErrors(false)->
  end()->

  get('/i18n/movie')->
  click('submit', array('movie' => array('director' => 'Robert Aldrich', 'en' => array('title' => 'The Dirty Dozen (1)'), 'fr' => array('title' => 'Les Douze Salopards (1)'))))->

  with('form')->begin()->
    hasErrors(2)->
  end()->

  click('submit', array('movie' => array('director' => 'Robert Aldrich', 'en' => array('title' => 'The Dirty Dozen'), 'fr' => array('title' => 'Les Douze Salopards'))))->

  with('form')->begin()->
    hasErrors(false)->
  end()->
  with('response')->begin()->
    isRedirected()->
    followRedirect()->
  end()->
  with('response')->begin()->
    checkElement('input[value="Robert Aldrich"]')->
    checkElement('input[value="The Dirty Dozen"]')->
    checkElement('input[value="Les Douze Salopards"]')->
  end()
  // END: Bug #7486
;

  // Symfony integration with BehaviorI18n (part of Propel)
  $b->
  get('/i18n/moviePropel')->
  with('request')->begin()->
    isParameter('module', 'i18n')->
    isParameter('action', 'moviePropel')->
  end()->
  with('response')->begin()->
    isStatusCode(200)->
    checkElement('#movie_propel_fr_id', false)->
    checkElement('#movie_propel_fr_locale', false)->
  end()->

  click('submit', array('movie_propel' => array('director' => 'Robert Aldrich', 'en' => array('title' => 'The Dirty Dozen'), 'fr' => array('title' => 'Les Douze Salopards'))))->
  with('response')->begin()->
    isRedirected()->
    followRedirect()->
  end()->
  with('response')->begin()->
    checkElement('input[value="Robert Aldrich"]')->
    checkElement('input[value="The Dirty Dozen"]')->
    checkElement('input[value="Les Douze Salopards"]')->
    checkElement('#movie_propel_fr_id', true)->
    checkElement('#movie_propel_fr_locale', true)->
  end()->

  with('propel')->begin()->
    check('MoviePropel', array(), 2)->
    check('MoviePropel', array('director' => 'Robert Aldrich', 'id' => 2))->
    check('MoviePropelI18N', array(), 4)->
    check('MoviePropelI18N', array('id' => 2), 2)->
    check('MoviePropelI18N', array('locale' => 'fr', 'id' => 2, 'title' => 'Les Douze Salopards'))->
    check('MoviePropelI18N', array('locale' => 'en', 'id' => 2, 'title' => 'The Dirty Dozen'))->
  end()->

  click('submit', array('movie_propel' => array('director' => 'Robert Aldrich (1)', 'en' => array('title' => 'The Dirty Dozen (1)'), 'fr' => array('title' => 'Les Douze Salopards (1)'))))->
  with('response')->begin()->
    isRedirected()->
    followRedirect()->
  end()->
  with('response')->begin()->
    checkElement('input[value="Robert Aldrich (1)"]')->
    checkElement('input[value="The Dirty Dozen (1)"]')->
    checkElement('input[value="Les Douze Salopards (1)"]')->
  end()->

  with('propel')->begin()->
    check('MoviePropel', array(), 2)->
    check('MoviePropel', array('director' => 'Robert Aldrich (1)', 'id' => 2))->
    check('MoviePropelI18N', array(), 4)->
    check('MoviePropelI18N', array('id' => 2), 2)->
    check('MoviePropelI18N', array('locale' => 'fr', 'id' => 2, 'title' => 'Les Douze Salopards (1)'))->
    check('MoviePropelI18N', array('locale' => 'en', 'id' => 2, 'title' => 'The Dirty Dozen (1)'))->
  end();
  
  

// https://github.com/propelorm/sfPropelORMPlugin/issues/38
// SfPropelBehaviorI18n (part of sfPropelORMPlugin)
$b->
  get('/i18n/movie')->
  with('request')->begin()->
    isParameter('module', 'i18n')->
    isParameter('action', 'movie')->
  end()->
  click('submit', array('movie' => array('director' => 'James McTeigue', 'en' => array('title' => 'V For Vendetta'), 'fr' => array('title' => 'V Pour Vendetta'), 'Toy' => array('newToy1' => array('ref' => '04212', 'en' => array('name' => 'V mask'), 'fr' => array('name' => 'masque de V'))))))->
  
  with('response')->begin()->
    isRedirected()->
    followRedirect()->
  end()->
  
  with('response')->begin()->
    checkElement('input[value="James McTeigue"]')->
    checkElement('input[value="V For Vendetta"]')->
    checkElement('input[value="V Pour Vendetta"]')->
    checkElement('input[name="movie[Toy][1][ref]"][value="04212"]')->
    checkElement('input[name="movie[Toy][1][en][name]"][value="V mask"]')->
    checkElement('input[name="movie[Toy][1][fr][name]"][value="masque de V"]')->
  end()->
  with('propel')->begin()->
    check('Movie', array(), 4)->
    check('Movie', array('director' => 'James McTeigue', 'id' => 4))->
    check('MovieI18N', array(), 8)->
    check('MovieI18N', array('id' => 4), 2)->
    check('MovieI18N', array('culture' => 'fr', 'id' => 4, 'title' => 'V Pour Vendetta'))->
    check('MovieI18N', array('culture' => 'en', 'id' => 4, 'title' => 'V For Vendetta'))->
    check('Toy', array(),1)->
    check('Toy', array('ref' => '04212'))->
    check('ToyI18N', array(), 2)->
    check('ToyI18N', array('id' => 1), 2)->
    check('ToyI18N', array('culture' => 'fr', 'id' => 1, 'name' => 'masque de V'))->
    check('ToyI18N', array('culture' => 'en', 'id' => 1, 'name' => 'V mask'))->
  end();
  // https://github.com/propelorm/sfPropelORMPlugin/issues/38
  // Symfony integration with BehaviorI18n (part of Propel)
  $b->
  get('/i18n/moviePropel')->
  with('request')->begin()->
    isParameter('module', 'i18n')->
    isParameter('action', 'moviePropel')->
  end()->
  click('submit', array('movie_propel' => array('director' => 'James McTeigue (1)', 'en' => array('title' => 'V For Vendetta (1)'), 'fr' => array('title' => 'V Pour Vendetta (1)'), 'ToyPropel' => array('newToyPropel1' => array('ref' => '04212', 'en' => array('name' => 'V mask'), 'fr' => array('name' => 'masque de V'))))))->
  
  with('response')->begin()->
    isRedirected()->
    followRedirect()->
  end()->
  
  with('response')->begin()->
    checkElement('input[value="James McTeigue (1)"]')->
    checkElement('input[value="V For Vendetta (1)"]')->
    checkElement('input[value="V Pour Vendetta (1)"]')->
    checkElement('input[name="movie_propel[ToyPropel][1][ref]"][value="04212"]')->
    checkElement('input[name="movie_propel[ToyPropel][1][en][name]"][value="V mask"]')->
    checkElement('input[name="movie_propel[ToyPropel][1][fr][name]"][value="masque de V"]')->
  end()->
  with('propel')->begin()->
    check('MoviePropel', array(), 3)->
    check('MoviePropel', array('director' => 'James McTeigue (1)', 'id' => 3))->
    check('MoviePropelI18N', array(), 6)->
    check('MoviePropelI18N', array('id' => 3), 2)->
    check('MoviePropelI18N', array('locale' => 'fr', 'id' => 3, 'title' => 'V Pour Vendetta (1)'))->
    check('MoviePropelI18N', array('locale' => 'en', 'id' => 3, 'title' => 'V For Vendetta (1)'))->
    check('ToyPropel', array(),1)->
    check('Toy', array('ref' => '04212'))->
    check('ToyPropelI18N', array(), 2)->
    check('ToyPropelI18N', array('id' => 1), 2)->
    check('ToyPropelI18N', array('locale' => 'fr', 'id' => 1, 'name' => 'masque de V'))->
    check('ToyPropelI18N', array('locale' => 'en', 'id' => 1, 'name' => 'V mask'))->
  end()
  // END: https://github.com/propelorm/sfPropelORMPlugin/issues/38
;


$b->getAndCheck('i18n', 'products')
  ->with('response')->begin()
    ->checkElement('ul#products li.toString', 'PRIMARY STRING')
  ->end()
;

  