<?php $pager->setSurroundCount(2) ?>

<nav aria-label="Page navigation">
  <ul class="pagination m-3 justify-content-center align-items-center">
  <?php if ($pager->hasPrevious()) : ?>
    <li class="page-item page-first" >
      <a class="page-link" href="<?=$pager->getFirst() ?>" aria-label="<?=lang('Pager.first')?>" data-page="1">
        <span aria-hidden="true"><?=lang('Pager.first') ?></span>
      </a>
    </li>
    <li class="page-item page-prev">
      <a class="page-link" href="<?=$pager->getPrevious() ?>" aria-label="<?=lang('Pager.previous')?>"  data-page="<?=($pager->getFirstPageNumber() - 1)?>">
        <span aria-hidden="true"><?=lang('Pager.previous') ?></span>
      </a>
    </li>
  <?php endif ?>
    <?php $pager->links() ?>
  <?php foreach ($pager->links() as $link) : ?>
    <li class="page-item <?=$link['active'] ? 'active' : '' ?>">
      <a class="page-link" href="<?= $link['uri'] ?>" data-page="<?=$link['title']?>">
        <?= $link['title'] ?>
      </a>
    </li>
  <?php endforeach ?>

  <?php if ($pager->hasNext()) : ?>
    <li class="page-item page-next">
      <a class="page-link" href="<?=$pager->getNext() ?>" aria-label="<?=lang('Pager.next') ?>" data-page="<?=($pager->getLastPageNumber() + 1)?>">
        <span aria-hidden="true"><?=lang('Pager.next') ?></span>
      </a>
    </li>
    <li class="page-item page-last">
      <a class="page-link" href="<?=$pager->getLast() ?>" aria-label="<?=lang('Pager.last') ?>" data-page="<?=$pager->getPageCount()?>">
        <span aria-hidden="true"><?=lang('Pager.last') ?></span>
      </a>
    </li>
  <?php endif ?>
  </ul>
</nav>