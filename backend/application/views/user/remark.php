<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?= lists_message() ?>
<div class="box">
    <div class="box-header">
        <form method="post" action="">
            <div class="col-xs-4">
                <label>备注</label>
                <input type="text" name="remark" class="form-control" placeholder="请输入..." value="">
            </div>
            <div class="col-xs-1">
                <label>&nbsp;</label>
                <button type="submit" class="form-control btn btn-primary">保存</button>
            </div>
        </form>
    </div>
    <!-- /.box-header -->
    <div class="box-body table-responsive no-padding">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>编号</th>
                    <th>备注</th>
                    <th>操作者</th>
                    <th>时间</th>
                    <th>删除</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $key => $row) : ?>
                    <tr>
                        <td><?= $key + 1 ?></td>
                        <td><?= $row['note'] ?></td>
                        <td><?= $row['create_by'] ?></td>
                        <td><?= $row['create_time'] ?></td>
                        <td>
                            <button type="button" class="btn btn-primary" onclick="if (confirm('您确定要删除吗?')) location.href='<?= site_url($this->cur_url . "/$id/$key") ?>';">删除</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <!-- /.box-body -->
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
            content: '<?= site_url("{$this->router->class}/create") ?>'
        });
    }
    //编辑
    function edit(id) {
        layer.open({
            type: 2,
            shadeClose: false,
            title: false,
            closeBtn: [0, true],
            shade: [0.8, '#000'],
            border: [1],
            offset: ['20px', ''],
            area: ['50%', '90%'],
            content: '<?= site_url("{$this->router->class}/edit") ?>/' + id
        });
    }
    //修改餘額
    function edit_money(id) {
        layer.open({
            type: 2,
            shadeClose: false,
            title: false,
            closeBtn: [0, true],
            shade: [0.8, '#000'],
            border: [1],
            offset: ['20px', ''],
            area: ['50%', '90%'],
            content: '<?= site_url("{$this->router->class}/edit_money") ?>/' + id
        });
    }
    //備註
    function remark(id) {
        layer.open({
            type: 2,
            shadeClose: false,
            title: false,
            closeBtn: [0, true],
            shade: [0.8, '#000'],
            border: [1],
            offset: ['20px', ''],
            area: ['50%', '90%'],
            content: '<?= site_url("{$this->router->class}/remark") ?>/' + id
        });
    }
</script>