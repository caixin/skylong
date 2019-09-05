<ul class="nav nav-tabs">
    <?php foreach (Ettm_lottery_cheat_model::$typeList as $key => $val) : ?>
        <li class="<?= $key == $type ? 'active' : '' ?>"><a href="<?=site_url($this->router->class.'/'.Ettm_lottery_cheat_model::$typeUrl[$key])?>"><?= $val ?></a></li>
    <?php endforeach; ?>
</ul>