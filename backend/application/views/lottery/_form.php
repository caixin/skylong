<?php defined('BASEPATH') || exit('No direct script access allow_naved'); ?>
<div class="box box-<?= validation_errors() ? 'danger' : 'success' ?>">
    <!-- /.box-header -->
    <div class="box-body">
        <form method="post" role="form" action="">
            <div class="form-group <?= form_error('lottery_type_id') ? 'has-error' : '' ?>">
                <label>彩种类别</label>
                <select name="lottery_type_id" class="form-control">
                    <?php foreach ($type as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['lottery_type_id']) && $row['lottery_type_id'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('lottery_type_id', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('name') ? 'has-error' : '' ?>">
                <label>彩种名称</label>
                <input type="text" name="name" class="form-control" placeholder="Enter ..." value="<?= isset($row['name']) ? $row['name'] : '' ?>">
                <?= form_error('name', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('key_word') ? 'has-error' : '' ?>">
                <label>Keyword</label>
                <input type="text" name="key_word" class="form-control" placeholder="Enter ..." value="<?= isset($row['key_word']) ? $row['key_word'] : '' ?>">
                <?= form_error('key_word', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('pic_icon') ? 'has-error' : '' ?>">
                <label>图片URL</label>
                <input type="text" name="pic_icon" class="form-control" placeholder="Enter ..." value="<?= isset($row['pic_icon']) ? $row['pic_icon'] : '' ?>">
                <?= $action == 'edit' ? "<img src=\"$row[pic_icon]\">" : '' ?>
                <?= form_error('pic_icon', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('jump_url') ? 'has-error' : '' ?>">
                <label>跳转链接</label>
                <input type="text" name="jump_url" class="form-control" placeholder="Enter ..." value="<?= isset($row['jump_url']) ? $row['jump_url'] : '' ?>">
                <?= form_error('jump_url', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('day_start') ? 'has-error' : '' ?>">
                <label>开盘时间</label>
                <input type="text" name="day_start" class="form-control timepicker" placeholder="Enter ..." value="<?= isset($row['day_start']) ? $row['day_start'] : '' ?>" autocomplete="off">
                <?= form_error('day_start', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('open_end') ? 'has-error' : '' ?>">
                <label>封盘时间</label>
                <input type="text" name="day_end" class="form-control timepicker" placeholder="Enter ..." value="<?= isset($row['day_end']) ? $row['day_end'] : '' ?>" autocomplete="off">
                <?= form_error('day_end', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('open_start') ? 'has-error' : '' ?>">
                <label>开奖起始时间 <span style="color:red;">【加拿大PC28需每日修改】</span></label>
                <input type="text" name="open_start" class="form-control timepicker" placeholder="Enter ..." value="<?= isset($row['open_start']) ? $row['open_start'] : '' ?>" autocomplete="off">
                <?= form_error('open_start', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('open_end') ? 'has-error' : '' ?>">
                <label>开奖结束时间</label>
                <input type="text" name="open_end" class="form-control timepicker" placeholder="Enter ..." value="<?= isset($row['open_end']) ? $row['open_end'] : '' ?>" autocomplete="off">
                <?= form_error('open_end', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('interval') ? 'has-error' : '' ?>">
                <label>间隔时间 <span style="color:red;">【秒数】</span></label>
                <input type="text" name="interval" class="form-control" placeholder="Enter ..." value="<?= isset($row['interval']) ? $row['interval'] : '' ?>">
                <?= form_error('interval', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('benchmark') ? 'has-error' : '' ?>">
                <label>
                    初始期数 <span style="color:red;">【加拿大PC28需每日修改】</span>
                    <?php if ($action == 'edit') : ?>
                        <input type="hidden" name="update_lottery_time" value="0">
                        <input type="checkbox" name="update_lottery_time" value="1"><span style="color:blue;">计算各期开奖时间</span>
                    <?php endif; ?>
                </label>
                <input type="text" name="benchmark" class="form-control" placeholder="Enter ..." value="<?= isset($row['benchmark']) ? $row['benchmark'] : '' ?>">
                <?= form_error('benchmark', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('benchmark_date') ? 'has-error' : '' ?>">
                <label>初始期数日期 <span style="color:red;">【For 流水号期数使用】</span></label>
                <input type="text" name="benchmark_date" class="form-control datepicker" placeholder="Enter ..." value="<?= isset($row['benchmark_date']) ? $row['benchmark_date'] : '' ?>" autocomplete="off">
                <?= form_error('benchmark_date', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('halftime_start') ? 'has-error' : '' ?>">
                <label>中场休息起始 <span style="color:red;">【For 重庆时时彩使用】</span></label>
                <input type="text" name="halftime_start" class="form-control timepicker" placeholder="Enter ..." value="<?= isset($row['halftime_start']) ? $row['halftime_start'] : '' ?>" autocomplete="off">
                <?= form_error('halftime_start', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('halftime_end') ? 'has-error' : '' ?>">
                <label>中场休息结束 <span style="color:red;">【For 重庆时时彩使用】</span></label>
                <input type="text" name="halftime_end" class="form-control timepicker" placeholder="Enter ..." value="<?= isset($row['halftime_end']) ? $row['halftime_end'] : '' ?>" autocomplete="off">
                <?= form_error('halftime_end', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('adjust') ? 'has-error' : '' ?>">
                <label>调整时间 <span style="color:red;">【经典为封盘时间 官方为提前进入下期】</span></label>
                <input type="text" name="adjust" class="form-control" placeholder="Enter ..." value="<?= isset($row['adjust']) ? $row['adjust'] : '' ?>">
                <?= form_error('adjust', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('digit') ? 'has-error' : '' ?>">
                <label>期数几位数 <span style="color:red;">【日期除外的流水号几位数】</span></label>
                <input type="text" name="digit" class="form-control" placeholder="Enter ..." value="<?= isset($row['digit']) ? $row['digit'] : '' ?>">
                <?= form_error('digit', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('mode') ? 'has-error' : '' ?>">
                <label>玩法模式 <span style="color:red;">【可复选】</span></label>
                <div class="checkbox">
                    <input type="hidden" name="mode[]" value="0">
                    <?php foreach (Ettm_lottery_model::$modeList as $key => $val) : ?>
                        <label><input type="checkbox" name="mode[]" value="<?= $key ?>" <?= isset($row['mode']) && in_array($key, $row['mode']) ? 'checked' : '' ?>> <?= $val ?></label>
                    <?php endforeach; ?>
                </div>
                <?= form_error('mode', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('sort') ? 'has-error' : '' ?>">
                <label>排序</label>
                <input type="text" name="sort" class="form-control" placeholder="Enter ..." value="<?= isset($row['sort']) ? $row['sort'] : '' ?>">
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
            <div class="form-group <?= form_error('is_hot') ? 'has-error' : '' ?>">
                <label>是否为热门彩种</label>
                <div class="radio">
                    <?php foreach (Ettm_lottery_model::$is_hotList as $key => $val) : ?>
                        <label><input type="radio" name="is_hot" value="<?= $key ?>" <?= isset($row['is_hot']) && $row['is_hot'] == $key ? 'checked' : '' ?>> <?= $val ?></label>
                    <?php endforeach; ?>
                </div>
                <?= form_error('is_hot', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('hot_logo') ? 'has-error' : '' ?>">
                <label>是否有HOT的Logo <span style="color:red;">【PC热门彩种专用】</span></label>
                <div class="radio">
                    <?php foreach (Ettm_lottery_model::$hot_logoList as $key => $val) : ?>
                        <label><input type="radio" name="hot_logo" value="<?= $key ?>" <?= isset($row['hot_logo']) && $row['hot_logo'] == $key ? 'checked' : '' ?>> <?= $val ?></label>
                    <?php endforeach; ?>
                </div>
                <?= form_error('hot_logo', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('is_custom') ? 'has-error' : '' ?>">
                <label>是否为自营彩种 <span style="color:red;">【自己开号码】</span></label>
                <div class="radio">
                    <?php foreach (Ettm_lottery_model::$is_customList as $key => $val) : ?>
                        <label><input type="radio" name="is_custom" value="<?= $key ?>" <?= isset($row['is_custom']) && $row['is_custom'] == $key ? 'checked' : '' ?>> <?= $val ?></label>
                    <?php endforeach; ?>
                </div>
                <?= form_error('is_custom', '<span class="help-block">', '</span>') ?>
            </div>
            <button type="submit" class="btn btn-primary">保存</button>
        </form>
    </div>
    <!-- /.box-body -->
</div>
<!-- /.box -->