<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?= lists_message() ?>
<div class="box">
    <div class="box-header">
        <form method="post" action="">
            <div class="col-xs-1" style="width:auto;">
                <label>营运商</label>
                <select name="operator_id" class="form-control">
                    <?php foreach ($operator as $key => $val) : ?>
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
    <!-- Custom Tabs -->
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <?php foreach ($result as $key => $row) : ?>
                <li class="<?= $groupid == $key ? 'active' : '' ?>"><a href="#tab_<?= $key ?>" data-toggle="tab"><?= sysconfig_model::$groupList[$key] ?></a></li>
            <?php endforeach; ?>
                <li>
                    <?php if ($this->session->userdata('roleid') == 1 || in_array("{$this->router->class}/create", $this->allow_url)) : ?>
                        <button type="button" class="btn btn-primary" onclick="add()">添加</button>
                    <?php endif; ?>
                </li>
        </ul>
        <div class="tab-content">
            <?php foreach ($result as $key => $data) : ?>
                <div class="tab-pane <?= $groupid == $key ? 'active' : '' ?>" id="tab_<?= $key ?>">
                    <div class="box box-success">
                        <div class="box-body table-responsive no-padding">
                            <form method="post" role="form" action="<?= site_url("{$this->router->class}/edit") ?>">
                                <input type="hidden" name="operator_id" value="<?= $where['operator_id'] ?>">
                                <input type="hidden" name="groupid" value="<?= $key ?>">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th width="100">运营商</th>
                                            <th width="200">参数名称</th>
                                            <th>参数值</th>
                                            <th width="100">排序</th>
                                            <th width="100">操作</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data as $row) : ?>
                                            <tr>
                                                <td><?= $row['operator_id'] == 0 ? '共用':$operator[$row['operator_id']] ?></td>
                                                <td><?= $row['info'] ?></td>
                                                <?php if ($this->session->userdata('roleid') == 1 || in_array("{$this->router->class}/edit", $this->allow_url)) : ?>
                                                    <td>
                                                        <?php if ($row['type'] == 'boolean') : ?>
                                                            <label><input type="radio" name="varname[<?= $row['id'] ?>]" value="Y" <?= $row['value'] == 'Y' ? 'checked' : '' ?>> 是</label>
                                                            <label><input type="radio" name="varname[<?= $row['id'] ?>]" value="N" <?= $row['value'] == 'N' ? 'checked' : '' ?>> 否</label>
                                                        <?php elseif ($row['type'] == 'textarea') : ?>
                                                            <textarea name="varname[<?= $row['id'] ?>]" class="form-control" rows="5"><?= $row['value'] ?></textarea>
                                                        <?php else : ?>
                                                            <input type="text" name="varname[<?= $row['id'] ?>]" class="form-control" value="<?= $row['value'] ?>">
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><input type="number" name="sort[<?= $row['id'] ?>]" class="form-control" value="<?= $row['sort'] ?>"></td>
                                                <?php else : ?>
                                                    <td><?= $row['value'] ?></td>
                                                    <td><?= $row['sort'] ?></td>
                                                <?php endif; ?>
                                                <td>
                                                    <?php if ($this->session->userdata('roleid') == 1 || in_array("{$this->router->class}/delete", $this->allow_url)) : ?>
                                                        <button type="button" class="btn btn-primary" onclick="delete_row('<?= $row['id'] ?>')">删除</button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <?php if ($this->session->userdata('roleid') == 1 || in_array("{$this->router->class}/edit", $this->allow_url)) : ?>
                                        <tfoot>
                                            <tr>
                                                <td></td>
                                                <td colspan="3"><button type="submit" class="btn btn-primary">保存</button></td>
                                            </tr>
                                        </tfoot>
                                    <?php endif; ?>
                                </table>
                            </form>
                        </div>
                        <!-- /.box-body -->
                    </div>
                    <!-- /.box -->
                </div>
                <!-- /.tab-pane -->
            <?php endforeach; ?>
        </div>
        <!-- /.tab-content -->
    </div>
    <!-- nav-tabs-custom -->
</div>
<script>
    //添加
    function add(id) {
        layer.open({
            type: 2,
            shadeClose: false,
            title: false,
            closeBtn: [0, true],
            shade: [0.8, '#000'],
            border: [1],
            offset: ['20px', ''],
            area: ['50%', '90%'],
            content: '<?= site_url("{$this->router->class}/create") ?>/' + id
        });
    }
    //刪除
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
</script>