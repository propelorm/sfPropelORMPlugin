[?php

/**
 * <?php echo $this->table->getClassname() ?> form base class.
 *
 * @method <?php echo $this->table->getClassname() ?> getObject() Returns the current form's model object
 *
 * @package    ##PROJECT_NAME##
 * @subpackage form
 * @author     ##AUTHOR_NAME##
 */
abstract class Base<?php echo $this->table->getClassname() ?>Form extends BaseFormPropel
{
  public function setup()
  {
    $this->setWidgets(array(
<?php foreach ($this->table->getColumns() as $column): ?>
      '<?php echo $this->translateColumnName($column) ?>'<?php echo str_repeat(' ', $this->getColumnNameMaxLength() - strlen($column->getName())) ?> => new <?php echo $this->getWidgetClassForColumn($column) ?>(<?php echo $this->getWidgetOptionsForColumn($column) ?>),
<?php endforeach; ?>
<?php foreach ($this->getManyToManyTables() as $tables): ?>
      '<?php echo $this->underscore($tables['middleTable']->getClassname()) ?>_list'<?php echo str_repeat(' ', $this->getColumnNameMaxLength() - strlen($this->underscore($tables['middleTable']->getClassname()).'_list')) ?> => new sfWidgetFormPropelChoice(array('multiple' => true, 'model' => '<?php echo $tables['relatedTable']->getClassname() ?>')),
<?php endforeach; ?>
    ));

    $this->setValidators(array(
<?php foreach ($this->table->getColumns() as $column): ?>
      '<?php echo $this->translateColumnName($column) ?>'<?php echo str_repeat(' ', $this->getColumnNameMaxLength() - strlen($column->getName())) ?> => new <?php echo $this->getValidatorClassForColumn($column) ?>(<?php echo $this->getValidatorOptionsForColumn($column) ?>),
<?php endforeach; ?>
<?php foreach ($this->getManyToManyTables() as $tables): ?>
      '<?php echo $this->underscore($tables['middleTable']->getClassname()) ?>_list'<?php echo str_repeat(' ', $this->getColumnNameMaxLength() - strlen($this->underscore($tables['middleTable']->getClassname()).'_list')) ?> => new sfValidatorPropelChoice(array('multiple' => true, 'model' => '<?php echo $tables['relatedTable']->getClassname() ?>', 'required' => false)),
<?php endforeach; ?>
    ));

<?php if ($uniqueColumns = $this->getUniqueColumnNames()): ?>
    $this->validatorSchema->setPostValidator(
<?php if (count($uniqueColumns) > 1): ?>
      new sfValidatorAnd(array(
<?php foreach ($uniqueColumns as $uniqueColumn): ?>
        new sfValidatorPropelUnique(array('model' => '<?php echo $this->table->getClassname() ?>', 'column' => array('<?php echo implode("', '", $uniqueColumn) ?>'))),
<?php endforeach; ?>
      ))
<?php else: ?>
      new sfValidatorPropelUnique(array('model' => '<?php echo $this->table->getClassname() ?>', 'column' => array('<?php echo implode("', '", $uniqueColumns[0]) ?>')))
<?php endif; ?>
    );

<?php endif; ?>
    $this->widgetSchema->setNameFormat('<?php echo $this->underscore($this->table->getClassname()) ?>[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return '<?php echo $this->table->getClassname() ?>';
  }

<?php if ($this->isI18n()): ?>
  public function getI18nModelName()
  {
    return '<?php echo $this->getI18nModel() ?>';
  }

  public function getI18nFormClass()
  {
    return '<?php echo $this->getI18nModel() ?>Form';
  }
<?php endif; ?>

<?php if ($this->getManyToManyTables()): ?>
  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

<?php foreach ($this->getManyToManyTables() as $tables): ?>
    if (isset($this->widgetSchema['<?php echo $this->underscore($tables['middleTable']->getClassname()) ?>_list']))
    {
      $values = array();
      foreach ($this->object-><?php echo $tables['relatedGetter'] ?>() as $obj)
      {
        $values[] = $obj->get<?php echo $tables['relatedColumn']->getPhpName() ?>();
      }

      $this->setDefault('<?php echo $this->underscore($tables['middleTable']->getClassname()) ?>_list', $values);
    }

<?php endforeach; ?>
  }

  protected function doSave($con = null)
  {
    parent::doSave($con);

<?php foreach ($this->getManyToManyTables() as $tables): ?>
    $this->save<?php echo $tables['middleTable']->getPhpName() ?>List($con);
<?php endforeach; ?>
  }

<?php foreach ($this->getManyToManyTables() as $tables): ?>
  public function save<?php echo $tables['middleTable']->getPhpName() ?>List($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['<?php echo $this->underscore($tables['middleTable']->getClassname()) ?>_list']))
    {
      // somebody has unset this widget
      return;
    }

    $rel_ids = $this->getValue('<?php echo $this->underscore($tables['middleTable']->getClassname()) ?>_list');

    // first: get current relations and delete removed ones
    $rels = $this->getObject()->get<?php echo $tables['middleTable']->getPhpName() ?>s();
    foreach ($rels as $rel)
    {
      if (!in_array($rel->get<?php echo $tables['relatedColumn']->getPhpName() ?>(), $rel_ids))
      {
        $rel->delete();
      }
    }

    // second: add new relations
    if (!empty($rel_ids))
    {
      foreach ($rel_ids as $rel_id)
      {
        $ref = <?php echo $tables['middleTable']->getPhpName() ?>Query::create()->findPk(array($this->getObject()->getPrimaryKey(), $rel_id));
        if (empty($ref))
        {
          $ref = new <?php echo $tables['middleTable']->getPhpName() ?>();
          $ref->set<?php echo $this->table->getClassname() ?>Id($this->getObject()->getPrimaryKey())->set<?php echo $tables['relatedTable']->getClassname() ?>Id($rel_id)->save();
        }
      }
    }
  }

<?php endforeach; ?>
<?php endif; ?>
}
