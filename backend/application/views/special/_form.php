<?php defined('BASEPATH') || exit('No direct script access allow_naved'); ?>
<div class="box box-<?= validation_errors() ? 'danger' : 'success' ?>">
    <!-- /.box-header -->
    <div class="box-body">
        <form method="post" role="form" action="">
            <div class="form-group <?= form_error('lottery_id') ? 'has-error' : '' ?>">
                <label>彩种</label>
                <select name="lottery_id" class="form-control">
                    <?php foreach ($lottery as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['lottery_id']) && $row['lottery_id'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('lottery_id', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('type') ? 'has-error' : '' ?>">
                <label>玩法</label>
                <select name="type" class="form-control">
                    <?php foreach (Ettm_special_model::$typeList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['type']) && $row['type'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('type', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('key_word') ? 'has-error' : '' ?>">
                <label>Keyword</label>
                <input type="text" name="key_word" class="form-control" placeholder="Enter ..." value="<?= isset($row['key_word']) ? $row['key_word'] : '' ?>">
                <?= form_error('key_word', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('pic_icon') ? 'has-error' : '' ?>">
                <label>图片URL</label>
                <input type="text" name="pic_icon" class="form-control" placeholder="Enter ..." value="<?= isset($row['pic_icon']) ? $row['pic_icon'] : '' ?>">
                <?= $action == 'edit' ? "<img src=\"$row[pic_icon]\" width=\"150\">" : '' ?>
                <?= form_error('pic_icon', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('jump_url') ? 'has-error' : '' ?>">
                <label>跳转链接</label>
                <input type="text" name="jump_url" class="form-control" placeholder="Enter ..." value="<?= isset($row['jump_url']) ? $row['jump_url'] : '' ?>">
                <?= form_error('jump_url', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('commission') ? 'has-error' : '' ?>">
                <label>主帐户抽水(%)</label>
                <input type="number" name="commission" class="form-control" placeholder="Enter ..." value="<?= isset($row['commission']) ? $row['commission'] : '' ?>" step="0.01">
                <?= form_error('commission', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('commission1') ? 'has-error' : '' ?>">
                <label>牛牛帐户抽水(%)</label>
                <input type="number" name="commission1" class="form-control" placeholder="Enter ..." value="<?= isset($row['commission1']) ? $row['commission1'] : '' ?>" step="0.01">
                <?= form_error('commission1', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('banker_limit') ? 'has-error' : '' ?>">
                <label>庄家额度上限</label>
                <input type="number" name="banker_limit" class="form-control" placeholder="Enter ..." value="<?= isset($row['banker_limit']) ? $row['banker_limit'] : '' ?>">
                <?= form_error('banker_limit', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('player_limit') ? 'has-error' : '' ?>">
                <label>闲家下注限额</label>
                <input type="number" name="player_limit" class="form-control" placeholder="Enter ..." value="<?= isset($row['player_limit']) ? $row['player_limit'] : '' ?>">
                <?= form_error('player_limit', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('sort') ? 'has-error' : '' ?>">
                <label>排序</label>
                <input type="number" name="sort" class="form-control" placeholder="Enter ..." value="<?= isset($row['sort']) ? $row['sort'] : '' ?>">
                <?= form_error('sort', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('status') ? 'has-error' : '' ?>">
                <label>状态</label>
                <select name="status" class="form-control">
                    <?php foreach (Ettm_lottery_model::$statusList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['status']) && $row['status'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('status', '<span class="help-block">', '</span>') ?>
            </div>
            <button type="submit" class="btn btn-primary">保存</button>
        </form>
    </div>
    <!-- /.box-body -->
</div>
<!-- /.box -->