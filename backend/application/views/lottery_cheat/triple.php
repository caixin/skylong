<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?= lists_message() ?>
<div class="box">
    <!-- Custom Tabs -->
    <div class="nav-tabs-custom">
        <?php $this->load->view("{$this->router->class}/_tabs", array('type'=>1)); ?>
        <div class="tab-content">
            <div class="box box-success">
                <div class="box-header">
                    <form method="post" action="">
                        <div class="col-xs-1" style="width:auto;">
                            <label>运营商名称</label>
                            <select name="operator_id" class="form-control">
                                <?php foreach ($operator as $key => $val): ?>
                                    <option value="<?= $key ?>" <?= isset($where['operator_id']) && $where['operator_id'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-xs-1" style="width:auto;">
                            <label>&nbsp;</label>
                            <button type="submit" class="form-control btn btn-primary">查询</button>
                        </div>
                    </form>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="200">彩种名称</th>
                                <th>状态</th>
                                <th width="100">
                                    <?php if ($this->session->userdata('roleid') == 1 || in_array("{$this->router->class}/create", $this->allow_url)) : ?>
                                        <button type="button" class="btn btn-primary" onclick="add()">添加</button>
                                    <?php endif; ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result as $row) : ?>
                                <tr>
                                    <td><?= $lottery[$row['lottery_id']] ?></td>
                                    <td>
                                    <?php if ($this->session->userdata('roleid') == 1 || in_array("{$this->router->class}/edit", $this->allow_url)) : ?>
                                        <button type="button" id="status0_<?= $row['id'] ?>" class="btn <?= $row['status'] == 0 ? 'btn-info' : 'btn-default' ?>" onclick="status_row(<?= $row['id'] ?>,0)"><?= ettm_lottery_cheat_model::$statusList[0] ?></button>
                                        <button type="button" id="status1_<?= $row['id'] ?>" class="btn <?= $row['status'] == 1 ? 'btn-info' : 'btn-default' ?>" onclick="status_row(<?= $row['id'] ?>,1)"><?= ettm_lottery_cheat_model::$statusList[1] ?></button>
                                    <?php else: ?>
                                        <?=ettm_lottery_cheat_model::$statusList[$row['status']]?>
                                    <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($this->session->userdata('roleid') == 1 || in_array("{$this->router->class}/delete", $this->allow_url)) : ?>
                                            <button type="button" class="btn btn-primary" onclick="delete_row(<?= $row['id'] ?>)">删除</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
        <!-- /.tab-content -->
    </div>
    <!-- nav-tabs-custom -->
</div>
<script>
    //添加
    function add() {
        layer.open({
            type: 2,
            shadeClose: false,
            title: false,
            closeBtn: [0, true],
            shade: [0.8, '#000'],
            border: [1],
            offset: ['20px', ''],
            area: ['50%', '90%'],
            content: '<?= site_url("{$this->router->class}/create/$where[operator_id]/1") ?>'
        });
    }
    function delete_row(id) {
        if (confirm('您确定要删除吗?')) {
            $.post('<?= site_url("{$this->router->class}/delete") ?>', {
                'id': id
            }, function(data) {
                if (data == 'done') {
                    location.reload();
                } else {
                    alert('操作失败!');
                }
            });
        }
    }
    //关闭
    function status_row(id, status) {
        if (status == 1) {
            $('#status0_' + id).removeClass('btn-info').addClass('btn-default');
            $('#status1_' + id).removeClass('btn-default').addClass('btn-info');
        } else {
            $('#status0_' + id).removeClass('btn-default').addClass('btn-info');
            $('#status1_' + id).removeClass('btn-info').addClass('btn-default');
        }
        $.post('<?= site_url("{$this->router->class}/edit") ?>/' + id, {
            'status': status
        });
    }
</script>