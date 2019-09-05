<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?= lists_message() ?>
<div class="box">
    <!-- /.box-header -->
    <div class="box-body table-responsive no-padding">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>编号</th>
                    <th>IOS装置UDID</th>
                    <th>绑定VIP包</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vip_info_ios as $key => $row) : ?>
                    <tr>
                        <td><?= $key + 1 ?></td>
                        <td><?= $row['udid'] ?></td>
                        <td>
                            <button type="button" id="binding1_<?= $key ?>" class="btn <?= $row['binding'] == 1 ? 'btn-info' : 'btn-default' ?>" onclick="binding_row(<?= $key ?>,1)"><?= user_model::$whetherList[1] ?></button>
                            <button type="button" id="binding0_<?= $key ?>" class="btn <?= $row['binding'] == 0 ? '' : 'btn-default' ?>" onclick="binding_row(<?= $key ?>,0)"><?= user_model::$whetherList[0] ?></button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <!-- /.box-body -->
</div>
<script>
    function binding_row(key, binding) {
        if (binding == 1) {
            $('#binding1_' + key).removeClass('btn-default').addClass('btn-info');
            $('#binding0_' + key).addClass('btn-default');
        } else {
            $('#binding1_' + key).removeClass('btn-info').addClass('btn-default');
            $('#binding0_' + key).removeClass('btn-default');
        }
        $.post('<?= site_url($this->cur_url . "/$id") ?>', {
            'key': key,
            'binding': binding
        });
    }
</script>