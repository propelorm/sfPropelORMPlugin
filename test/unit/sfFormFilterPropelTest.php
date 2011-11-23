<?php

require_once(dirname(__FILE__).'/../bootstrap/unit.php');

class BaseForm extends sfFormSymfony {}

class MockMerged extends BaseObject {
  const PEER = 'MockMergedPeer';
}
class MockEmbedded extends BaseObject {
  const PEER = 'MockEmbeddedPeer';
}
class MockModel extends BaseObject {
  const PEER = 'MockModelPeer';
}

class MockPeer {
  static public function translateFieldName($name, $fromType, $toType)
  {
    $inflector = new sfInflector();
    return ucfirst($inflector->camelize($name));
  }
}
class MockMergedPeer extends MockPeer {}
class MockEmbeddedPeer extends MockPeer {}
class MockModelPeer extends MockPeer {}

class MockMergedQuery extends ModelCriteria {
  public static $my_filter_counter = 0;
  public static $merged_counter = 0;
  const PEER = 'MockMergedPeer';

  public function __construct($dbName = 'propel', $modelName = 'MockMerged', $modelAlias = null)
  {
    parent::__construct($dbName, $modelName, $modelAlias);
  }
}
class MockEmbeddedQuery extends ModelCriteria {
  public static $my_filter_counter = 0;
  public static $embedded_counter = 0;
  const PEER = 'MockEmbeddedPeer';
  public function __construct($dbName = 'propel', $modelName = 'MockEmbedded', $modelAlias = null)
  {
    parent::__construct($dbName, $modelName, $modelAlias);
  }
}
class MockModelQuery extends ModelCriteria {

  const PEER = 'MockModelPeer';

  public function __construct($dbName = 'propel', $modelName = 'MockModel', $modelAlias = null)
  {
    parent::__construct($dbName, $modelName, $modelAlias);
  }
}

class MockTableMap extends TableMap {
  public function getClassname() {
    $matches = array();
    preg_match('#^(\w+)TableMap#', get_class($this), $matches);
    return $matches[1];
  }
  public function getName() {return $this->getClassname();}
}
class MockMergedTableMap extends MockTableMap {}
class MockEmbeddedTableMap extends MockTableMap {}
class MockModelTableMap extends MockTableMap {}



abstract class MockFilter extends sfFormFilterPropel {
  public static $calls = array(
    'MockFilterMerged' => array(),
    'MockFilterEmbedded' => array(),
    'MockModelFilter' => array(),);

  protected function addTextCriteria(Criteria $criteria, $field, $values)
  {
    if (!isset(self::$calls[get_class($this)][$field]))
    {
      self::$calls[get_class($this)][$field] = 1;
    }
    else
    {
      self::$calls[get_class($this)][$field] += 1;
    }

    return parent::addTextCriteria($criteria, $field, $values);
  }
}
class MockFilterMerged extends MockFilter
{
  public function configure()
  {
    $this->setValidators(array(
      'merged_filter'   => new sfValidatorPass(array('required' => false)),
    ));
  }

  public function getModelName() { return 'MockMerged'; }

  public function getFields()
  {
    return array(
        'my_filter'     => 'Text',
        'merged_filter' => 'Text'
      );
  }
}
class MockFilterEmbedded extends MockFilter
{
  public function configure()
  {
    $this->setValidators(array(
      'embedded_filter'   => new sfValidatorPass(array('required' => false)),
    ));
  }

  public function getModelName() { return 'MockEmbedded'; }

  public function getFields()
  {
    return array(
        'my_filter'       => 'Text',
        'embedded_filter' => 'Text'
      );
  }
}
class MockModelFilter extends MockFilter
{
  public function getModelName() { return 'MockModel'; }

  public function configure()
  {
    $this->setValidators(array(
      'my_filter'   => new sfValidatorPass(array('required' => false)),
    ));
  }

  public function getFields()
  {
    return array(
        'my_filter'       => 'Text',
      );
  }
}

class sfFormFilterPropelTest extends lime_test
{
  public function assert_embedded_form_count($filter, $expected, $message = null)
  {
    $message = is_null($message)
                ? sprintf('%d embedded forms expected.', $expected)
                : $message;

    $this->is(count($filter->getEmbeddedForms()), $expected, $message);

    return $this;
  }

  public function assert_merged_form_count($filter, $expected, $message = null)
  {
    $message = is_null($message)
                ? sprintf('%d merged forms expected.', $expected)
                : $message;

    $this->is(count($filter->getMergedForms()), $expected, $message);
    return $this;
  }

  public function assert_bind_error($filter, $values, $error_message, $message = null)
  {
    $message = is_null($message)
                ? 'Error validating data.'
                : $message;

    try
    {
      $filter->buildCriteria($values);
      $this->fail($message);
    }
    catch(Exception $e)
    {
      $this->is($e->getMessage(), $error_message, $message);
    }

    return $this;
  }

  public function assert_bind_ok($filter, $values, $message = null)
  {
    $message = is_null($message)
                ? 'Data validated.'
                : $message;
    try
    {
      $filter->buildCriteria($values);
      $this->pass($message);
    }
    catch(Exception $e)
    {
      $this->fail($message);
      $this->set_last_test_errors(array(
        sprintf(' exception: %s', get_class($e)),
        sprintf('   message: %s', $e->getMessage())));
    }

    return $this;
  }

  public function reset_counters()
  {
    MockFilter::$calls = array(
         'MockFilterMerged' => array(),
         'MockFilterEmbedded' => array(),
         'MockModelFilter' => array(),);
  }
}

$t = new sfFormFilterPropelTest(15);
$map = new MockModelTableMap();
$t->is($map->getClassname(), 'MockModel', 'TableMap getTableName is ok');

$filter = new MockModelFilter();
$t->diag('check merged forms are accessible');
$t->assert_embedded_form_count($filter, 0);
$t->assert_merged_form_count($filter, 0);

$filter = new MockModelFilter();
$t->assert_bind_ok($filter, array('my_filter' => array('text' => '')), 'Validate values');
$filter = new MockModelFilter();
$t->assert_bind_ok($filter, array('my_filter'     => array('text' => ''),
                                  'merged_filter' => array('text' => 'merged')), 'Allow extra parameters');

$filter = new MockModelFilter();
$filter->mergeForm(new MockFilterMerged());
$filter->embedForm('embedded', new MockFilterEmbedded());
$t->assert_embedded_form_count($filter, 1);
$t->assert_merged_form_count($filter, 1);

$t->reset_counters();
$t->assert_bind_ok($filter, array('my_filter'       => array('text' => ''),
                                  'merged_filter'   => array('text' => 'merged'),
                                  'embedded' => array(
                                        'embedded_filter' => array('text' => 'embedded'))), 'Can validate merged and embedded forms.');

$t->is(MockFilter::$calls['MockModelFilter']['my_filter'], 1, '1 call for Model MyFilter');
$t->ok(!isset(MockFilter::$calls['MockModelFilter']['embedded_filter']), 'no call for Model EmbeddedFilter');
$t->ok(!isset(MockFilter::$calls['MockModelFilter']['merged_filter']), 'no call for Model MergedFilter');
$t->ok(!isset(MockFilter::$calls['MockFilterEmbedded']['my_filter']), 'no call for MockEmbedded MyFilter');
$t->is(MockFilter::$calls['MockFilterEmbedded']['embedded_filter'], 1, '1 call for MockEmbedded EmbeddedFilter');
$t->ok(!isset(MockFilter::$calls['MockFilterMerged']['my_filter']), 'no call for MockMerged MyFilter');
$t->is(MockFilter::$calls['MockFilterMerged']['merged_filter'], 1, '1 call for MockMerged MergedFilter');
