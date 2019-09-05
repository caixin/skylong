<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?= lists_message() ?>
<div class="box">
    <div class="box-header">
        <label for="per_page">显示笔数:</label>
        <input type="test" id="per_page" value="<?= $this->per_page ?>" size="1">
        <h5 class="box-title" style="font-size: 14px;"><b>总计:</b> <?= $total ?></h5>
        <?= $this->pagination->create_links() ?>
    </div>
    <!-- /.box-header -->
    <div class="box-body table-responsive no-padding">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th><?= sort_title('id', '编号', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('code_type', '打码量类型', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('code_amount_need', '需求打码量', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('type', '类型', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('category', '玩法类别', $this->cur_url, $order, $where) ?></th>
                    <th>彩种名称</th>
                    <th><?= sort_title('total_p_value', '投注', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('c_value', '赔付', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('code_amount_use', '有效打码量', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('description', '备注', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('create_time', '时间', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('create_by', '操作人', $this->cur_url, $order, $where) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row) : ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= code_amount_model::$typeList[$row['code_type']] ?></td>
                        <td><?= $row['code_amount_need'] ?></td>
                        <td><?= code_amount_log_model::$typeList[$row['type']] ?></td>
                        <td><?= code_amount_log_model::$categoryList[$row['category']] ?></td>
                        <td><?= $row['lottery_name'] ?></td>
                        <td><?= $row['total_p_value'] ?></td>
                        <td><?= $row['c_value'] ?></td>
                        <td><?= $row['code_amount_use'] ?></td>
                        <td><?= $row['description'] ?></td>
                        <td><?= $row['create_time'] ?></td>
                        <td><?= $row['create_by'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="color:red;">
                    <td>总计</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td><?= $code_amount_use ?></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <!-- /.box-body -->
    <div class="box-footer clearfix">
        <?= $this->pagination->create_links() ?>
    </div>
</div>