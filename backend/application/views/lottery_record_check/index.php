<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?= lists_message() ?>
<div class="box">
    <div class="box-header">
        <div class="col-xs-1" style="width:auto;float:right;">
            <label>&nbsp;</label>
            <button type="button" class="form-control btn btn-primary" onclick="history_unopen()">
                历史漏期：
                <span id="history_unopen_count" style="background: red;border-radius: 20%;">0</span>
            </button>
        </div>
    </div>
    <!-- /.box-header -->
    <div class="box-body table-responsive no-padding">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>彩种ID</th>
                    <th>彩种名称</th>
                    <th>当前爬虫期数</th>
                    <th>官方开奖时间</th>
                    <th>实际开奖时间</th>
                    <th>报警秒数设置</th>
                    <th>下期倒数</th>
                    <th>开奖号码</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row) : ?>
                <tr id="<?= $row['lottery_id'] ?>">
                    <td>
                        <span class="lottery_id"><?= $row['lottery_id'] ?></span>
                    </td>
                    <td>
                        <span class="lottery_name"><?= $row['lottery_name'] ?></span>
                    </td>
                    <td>
                        <span class="qishu">--</span>
                    </td>
                    <td>
                        <span class="lottery_time">--</span>
                    </td>
                    <td>
                        <span class="update_time">--</span>
                    </td>
                    <td>
                        <span class="alarm" style="color: green;cursor: pointer;" onclick="alarm_edit(<?= $row['lottery_id'] ?>)"><?= $row['alarm'] ?></span>
                    </td>
                    <td>
                        <span class="count_down" style="font-size: 20px;">--</span>
                    </td>
                    <td>
                        <span class="numbers">--</span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
    //編輯報警秒數
    alarm_edit = function(lottery_id) {
        var $alarm = $('#' + lottery_id).find("span.alarm"),
            alarm = $alarm.html(),
            new_alarm = prompt("请输入新的报警秒数", $alarm.html());
        if (new_alarm) {
            $.post('<?= site_url("{$this->router->class}/alarm_edit") ?>/' + lottery_id, {
                'alarm': alarm,
                'new_alarm': new_alarm,
            }, function(data) {
                if (data == 'done') {
                    $alarm.html(new_alarm);
                    alert('修改完成!');
                } else {
                    alert(data);
                }
            });
        }
    }

    //歷史漏期
    history_unopen = function() {
        layer.open({
            type: 2,
            shadeClose: false,
            title: false,
            closeBtn: [0, true],
            shade: [0.8, '#000'],
            border: [1],
            offset: ['20px', ''],
            area: ['50%', '90%'],
            content: '<?= site_url("{$this->router->class}/history_unopen") ?>'
        });
    }

    //歷史漏期統計
    history_unopen_count = function() {
        $.post('<?= site_url("{$this->router->class}/history_unopen") ?>', function(data) {
            $('#history_unopen_count').html(data);
            //定時更新
            setTimeout(function() {
                history_unopen_count();
            }, 60 * 1000);
        });
    }

    /**
     * 秒數轉為文字
     * @param   Number  sec 秒數
     * @return  String          轉換的文字字串
     *
     * example:
     * secToStr(3601) => "60分1秒"
     * secToStr(123)  => "2分3秒"
     * secToStr(61)   => "1分1秒"
     * secToStr(60)   => "1分鐘"
     * secToStr(6)     => "6秒"
     */
    secToStr = function(sec) {
        sec *= 1;
        isNaN(sec) && (sec = 0);

        sec = Math.abs(sec);

        if (sec == 0) return '0秒';

        var str = '';
        if (sec >= 60) {
            str = Math.floor(sec / 60) + '分';
            sec %= 60;
        }
        if (sec == 0) str += '鐘';
        else str += sec + '秒';

        return str;
    };

    /**
     * 取伺服器時間、轉換倒數秒數
     */
    serverTime = {
        timeOffset: 0,
        /**
         * 設定伺服器時間
         * @return {Data Object} 伺服器跟本機的時間差
         */
        set: function(time) {
            return this.timeOffset = new Date(Date.now() - new Date(time.replace(/-/g, '/')).getTime());
        },
        /**
         * 取得伺服器時間
         * @return {Data Object} 伺服器時間物件
         */
        get: function() {
            return new Date(Date.now() - this.timeOffset.getTime());
        },
        /**
         * 計算傳入的時間跟伺服器差多久
         * @param  {Data|String} time 時間
         * @return {Data Object}      時間差物件
         */
        timeDiff: function(time) {
            if (!(time instanceof Date)) {
                time = new Date(time.replace(/-/g, '/'));
            }
            if (isNaN(time.getTime())) {
                console.error('Invalid Date');
                return false;
            }
            return new Date(time - this.get());
        }
    };

    /**
     * 聲音播放 Class
     */
    Player = function() {
        this.alarm = new Audio(); // 未開獎
        this.alarm.src = '<?= base_url("static/alarm.mp3") ?>';
        this.alarm.currentTime = 0.2;
        return this.alarm;
    };
    var alertSound = new Player();

    /**
     * 取得彩種資料物件
     * 每個彩種會產生一個物件，然後就一直跑一直跑一直跑 ......
     */
    check = function(lottery_id) {
        this.$ = $('#' + lottery_id); // 該彩種的 jQuery DOM
        this.lottery_id = lottery_id; // 彩種ID

        // 程式開始
        this.getQishuInfo.call(this);
    };
    check.fn = check.prototype;

    /**
     * 彩種開獎資料
     */
    check.fn.getQishuInfo = function() {
        // 取消未執行完的 ajax，避免中猴
        if (this.hasOwnProperty('getQishuInfoXhr') && this.getQishuInfoXhr.hasOwnProperty('abort')) {
            this.getQishuInfoXhr.abort();
        }
        // 清除未開獎定時 ( 避免重複 call )
        if (this.hasOwnProperty('getQishuInfoTimeout')) {
            clearTimeout(this.getQishuInfoTimeout);
        }

        var that = this;

        // 取得彩種當下開獎資料
        this.getQishuInfoXhr = $.ajax({
            type: 'POST',
            dataType: 'json',
            async: true,
            url: '<?= site_url("{$this->router->class}/qishu_info") ?>/' + this.lottery_id,
            success: function(data) {
                /**
                 * 使用 abort() 取消未執行完的 ajax 會直接執行 success
                 * 所以 data 若是沒資料時就直接結束
                 */
                if (!data) {
                    return;
                }

                that.$.find("span.qishu").html(data.qishu); // 顯示當前爬蟲期數
                that.$.find("span.lottery_time").html(data.lottery_time); // 顯示官方開獎時間
                that.getCountDown(data.count_down); // 下期開獎倒數

                if (data.status != 1) { // 尚未取得開獎號碼
                    // 10秒後再次確認是否已取得獎號
                    that.getQishuInfoTimeout = setTimeout(function() {
                        that.getQishuInfo.call(that);
                    }, 10 * 1000);

                    that.$.find("span.update_time").html('尚未开奖'); // 顯示實際開獎時間
                    that.$.find("span.numbers").html('开奖中 ...'); // 顯示開獎號碼

                    // 計算已過幾秒，仍未取得開獎資料
                    var diffTime = Math.floor((serverTime.get() - new Date(data.lottery_time)) / 1000);
                    // 開獎時間過了報警秒數設置的時間卻仍未取到開獎資料
                    if (diffTime >= that.$.find("span.alarm").html()) {
                        that.$.find("span.numbers").append('<br/><span style="color: red;">★过' + secToStr(diffTime) + '未取得</span>'); // 顯示警告訊息
                        alertSound.play(); // 發出報警音效
                    }
                } else { // 取得開獎號碼
                    that.$.find("span.update_time").html(data.update_time); // 顯示實際開獎時間
                    that.$.find("span.numbers").html(data.numbers); // 顯示開獎號碼
                }
            }
        });
    };

    /**
     * 下期開獎時間倒數
     * @param  {String} count_down 下期開獎時間 ex:2019-08-31 12:00:00
     */
    check.fn.getCountDown = function(count_down) {
        // 清除上一次倒數的定時
        if (this.hasOwnProperty('getCountDownTimeout')) {
            clearTimeout(this.getCountDownTimeout);
        }

        // 計算還有幾分幾秒開獎
        var nextCountDown = serverTime.timeDiff(count_down);
        var sec = nextCountDown.getTime() / 1000;

        if (sec < 0) { // 到了開獎時間
            this.getQishuInfo(); // 下一期開獎
        } else { // 下一期尚未開獎，顯示剩餘時間
            // 顯示倒數時間
            this.$.find("span.count_down").css({
                'color': '',
                'opacity': ''
            }).html(
                ("0" + Math.floor(sec / 3600)).slice(-2) + ":" +
                ("0" + Math.floor(sec % 3600 / 60)).slice(-2) + ":" +
                ("0" + Math.floor(sec % 60)).slice(-2)
            );
            // 小於 10 秒時，倒數時間閃爍
            if (sec <= 10) {
                this.$.find("span.count_down").css({
                    'color': 'red',
                    'opacity': 0
                }).animate({
                    'opacity': 1
                }, 500);
            }

            /**
             * 延遲 x 秒後再跑一次倒數流程
             * 因為 JavaScript 是單執行續，有可能跑到這裡的時候已經花了 0.5 秒
             * 如果延遲時間固定設為 1 秒的話，會造成時間差
             */
            var that = this;
            this.getCountDownTimeout = setTimeout(
                function() {
                    that.getCountDown.call(that, count_down);
                },
                nextCountDown.getMilliseconds()
            );
        }
    };

    /**
     * 撈出所有彩種資料，一個彩種建立一項物件去跑
     */
    $.ajax({
        url: '<?= site_url("{$this->router->class}/index") ?>',
        dataType: 'json',
        async: 'true',
        success: function(resData) {
            // 設定伺服器時間
            var timeOffset = serverTime.set(resData.server_time);
            // 指定定 server 時間 ( debug 用 )
            // serverTime.set('2015/04/15 14:39:15');

            // 產生各彩種資料
            for (var i in resData.list) {
                // 建立倒數物件
                new check(resData.list[i]['lottery_id']);
            }
            history_unopen_count();
        }
    });
</script>