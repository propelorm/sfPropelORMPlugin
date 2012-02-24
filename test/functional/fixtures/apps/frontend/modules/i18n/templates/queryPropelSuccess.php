<ul id="movies">
  <?php foreach ($moviesPropel as $movie): ?>
    <li class="propel_it"><?php echo $movie->getTitle() ?></li>
  <?php endforeach; ?>
</ul>
