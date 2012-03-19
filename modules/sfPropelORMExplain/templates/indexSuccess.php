<h2>Explanation</h2>

<table>
  <tr>
    <?php foreach($results[0] as $label =>$val): ?>
        <th><?php echo $label; ?></th>
    <?php endforeach; ?>
  </tr>
  <?php foreach($results as $row): ?>
  <tr>
    <?php foreach($row as $item): ?>
        <td><?php echo $item; ?></td>
    <?php endforeach; ?>
  </tr>
  <?php endforeach; ?>
</table>
