<?php defined('BASEPATH') || exit('No direct script access allow_naved'); ?>
<div class="box box-<?= validation_errors() ? 'danger' : 'success' ?>">
    <!-- /.box-header -->
    <div class="box-body">
        <form method="post" role="form" action="">
            <div class="form-group <?= form_error('name') ? 'has-error' : '' ?>">
                <label>角色名称</label>
                <input type="text" name="name" class="form-control" placeholder="Enter ..." value="<?= isset($row['name']) ? $row['name'] : '' ?>">
                <?= form_error('name', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('allow_operator') ? 'has-error' : '' ?>">
                <label>运营商权限 <span style="color:red;">【後台帳號可复选】【代理帳號權限不可複選】</span></label>
                <select name="allow_operator[]" class="form-control select2" multiple="multiple">
                    <?php foreach ($operator as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['allow_operator']) && in_array($key, $row['allow_operator']) ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('allow_operator', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group">
                <label>导航权限</label>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <?php foreach ($nav as $nav1) : ?>
                            <tr style="background:#BABABA;">
                                <th colspan="2">
                                    <label><input type="checkbox" id="nav1_<?= $nav1['id'] ?>" name="allow_nav[<?= $nav1['id'] ?>]" value="<?= $nav1['url'] ?>" onclick="checkall(<?= $nav1['id'] ?>,'nav1')" nav1="<?= $nav1['id'] ?>" <?= isset($row['allow_nav']) && in_array($nav1['url'], $row['allow_nav']) ? 'checked' : '' ?>> <?= $nav1['name'] ?></label>
                                </th>
                            </tr>
                            <?php foreach ($nav1['sub'] as $nav2) : ?>
                                <tr>
                                    <th style="background:#C7C400">
                                        <label><input type="checkbox" id="nav2_<?= $nav2['id'] ?>" name="allow_nav[<?= $nav2['id'] ?>]" value="<?= $nav2['url'] ?>" onclick="cancelcheckall(<?= $nav2['id'] ?>,'nav2')" onchange="group_check(<?= $nav1['id'] ?>,'nav1')" nav1="<?= $nav1['id'] ?>" nav2="<?= $nav2['id'] ?>" <?= isset($row['allow_nav']) && in_array($nav2['url'], $row['allow_nav']) ? 'checked' : '' ?>> <?= $nav2['name'] ?></label>
                                    </th>
                                    <td>
                                        <?php foreach ($nav2['sub'] as $nav3) : ?>
                                            <label><input type="checkbox" id="nav3_<?= $nav3['id'] ?>" name="allow_nav[<?= $nav3['id'] ?>]" value="<?= $nav3['url'] ?>" onchange="group_check(<?= $nav2['id'] ?>,'nav2')" nav1="<?= $nav1['id'] ?>" nav2="<?= $nav2['id'] ?>" <?= isset($row['allow_nav']) && in_array($nav3['url'], $row['allow_nav']) ? 'checked' : '' ?>> <?= $nav3['name'] ?></label>
                                        <?php endforeach; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </table>
                </div>
                <!-- /.box-body -->
            </div>
            <button type="submit" class="btn btn-primary">保存</button>
        </form>
    </div>
    <!-- /.box-body -->
</div>
<!-- /.box -->
<script>
    $('.select2').select2();

    function checkall(k, attr) {
        $('input:checkbox[' + attr + '="' + k + '"]').each(function() {
            $(this).prop('checked', $('#' + attr + '_' + k).prop('checked'));
        });
    }

    function cancelcheckall(k, attr) {
        $('input:checkbox[' + attr + '="' + k + '"]').each(function() {
            if ($('#' + attr + '_' + k).prop('checked') == false) {
                $(this).prop('checked', false);
            }
        });
    }

    function group_check(k, attr) {
        var prop = false;
        $('input:checkbox[' + attr + '="' + k + '"]').each(function() {
            if ($(this).prop('checked')) prop = true;
        });

        $('#' + attr + '_' + k).prop('checked', prop).change();
    }
</script>