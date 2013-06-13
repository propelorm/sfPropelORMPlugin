[?php

/**
 * <?php echo $this->table->getClassname() ?> filter form base class.
 *
 * @package    ##PROJECT_NAME##
 * @subpackage filter
 * @author     ##AUTHOR_NAME##
 */
abstract class Base<?php echo $this->table->getClassname() ?>FormFilter extends BaseFormFilterPropel
{
  public function setup()
  {
    $this->setWidgets(array(
<?php foreach ($this->table->getColumns() as $column): ?>
<?php if ($column->isPrimaryKey()) continue ?>
      '<?php echo $this->translateColumnName($column) ?>'<?php echo str_repeat(' ', $this->getColumnNameMaxLength() - strlen($column->getName())) ?> => new <?php echo $this->getWidgetClassForColumn($column) ?>(<?php echo $this->getWidgetOptionsForColumn($column) ?>),
<?php endforeach; ?>
<?php foreach ($this->getManyToManyTables() as $tables): ?>
      '<?php echo $this->underscore($tables['middleTable']->getClassname()) ?>_list'<?php echo str_repeat(' ', $this->getColumnNameMaxLength() - strlen($this->underscore($tables['middleTable']->getClassname()).'_list')) ?> => new sfWidgetFormPropelChoice(array('model' => '<?php echo $tables['relatedTable']->getClassname() ?>', 'add_empty' => true)),
<?php endforeach; ?>
    ));

    $this->setValidators(array(
<?php foreach ($this->table->getColumns() as $column): ?>
<?php if ($column->isPrimaryKey()) continue ?>
      '<?php echo $this->translateColumnName($column) ?>'<?php echo str_repeat(' ', $this->getColumnNameMaxLength() - strlen($column->getName())) ?> => <?php echo $this->getValidatorForColumn($column) ?>,
<?php endforeach; ?>
<?php foreach ($this->getManyToManyTables() as $tables): ?>
      '<?php echo $this->underscore($tables['middleTable']->getClassname()) ?>_list'<?php echo str_repeat(' ', $this->getColumnNameMaxLength() - strlen($this->underscore($tables['middleTable']->getClassname()).'_list')) ?> => new sfValidatorPropelChoice(array('model' => '<?php echo $tables['relatedTable']->getClassname() ?>', 'required' => false)),
<?php endforeach; ?>
    ));

    $this->widgetSchema->setNameFormat('<?php echo $this->underscore($this->table->getClassname()) ?>_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

<?php foreach ($this->getManyToManyTables() as $tables):
    $middleTablePeerName = $tables['middleTable']->getPeerClassname();
    $columnPeerName = call_user_func_array(array($middleTablePeerName, 'translateFieldName'), array($tables['column']->getPhpName(), BasePeer::TYPE_PHPNAME, BasePeer::TYPE_RAW_COLNAME));
    $relatedColumnPeerName = call_user_func_array(array($middleTablePeerName, 'translateFieldName'), array($tables['relatedColumn']->getPhpName(), BasePeer::TYPE_PHPNAME, BasePeer::TYPE_RAW_COLNAME));
    $pkPeerName = call_user_func_array(array($this->table->getPeerClassname(), 'translateFieldName'), array($this->getPrimaryKey()->getPhpName(), BasePeer::TYPE_PHPNAME, BasePeer::TYPE_RAW_COLNAME));
  ?>
  public function add<?php echo $tables['middleTable']->getPhpName() ?>ListColumnCriteria(Criteria $criteria, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $criteria->addJoin(<?php echo $this->table->getPeerClassname() ?>::<?php echo $pkPeerName ?>, <?php echo $middleTablePeerName ?>::<?php echo $columnPeerName ?>);

    $value = array_pop($values);
    $criterion = $criteria->getNewCriterion(<?php echo $middleTablePeerName ?>::<?php echo $relatedColumnPeerName ?>, $value);

    foreach ($values as $value)
    {
      $criterion->addOr($criteria->getNewCriterion(<?php echo $middleTablePeerName ?>::<?php echo $relatedColumnPeerName ?>, $value));
    }

    $criteria->add($criterion);
  }

<?php endforeach; ?>
  public function getModelName()
  {
    return '<?php echo $this->table->getClassname() ?>';
  }

  public function getFields()
  {
    return array(
<?php foreach ($this->table->getColumns() as $column): ?>
      '<?php echo $this->translateColumnName($column) ?>'<?php echo str_repeat(' ', $this->getColumnNameMaxLength() - strlen($column->getName())) ?> => '<?php echo $this->getType($column) ?>',
<?php endforeach; ?>
<?php foreach ($this->getManyToManyTables() as $tables): ?>
      '<?php echo $this->underscore($tables['middleTable']->getClassname()) ?>_list'<?php echo str_repeat(' ', $this->getColumnNameMaxLength() - strlen($this->underscore($tables['middleTable']->getClassname()).'_list')) ?> => 'ManyKey',
<?php endforeach; ?>
    );
  }
}
