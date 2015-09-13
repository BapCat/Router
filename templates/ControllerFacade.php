<<?= '?php' ?>

class <?= $name ?> extends \BapCat\Facade\Facade {
  protected static $_binding = \<?= $controller ?>::class;
}
