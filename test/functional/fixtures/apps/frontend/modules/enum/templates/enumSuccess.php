<form action="<?php echo url_for('enum/enum') ?>" method="post">
  <table>
    <?php echo $form['enum_values']->render(); ?>
    <tr>
      <td colspan="2">
        <input type="submit" value="submit" />
      </td>
    </tr>
  </table>
</form>
