<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<h2>WebSocket Test 下注</h2>
<button id="btn-connect" class="btn">Connect</button>
<button id="btn-disConnect" class="btn">Disconnect</button>
<hr>
<div class="input-group">
    <input name="chatroom" id="chatroom" class="form-control" size="200" />
    <div class="input-group-btn">
        <button id="btn-sent" class="btn">Sent</button>
    </div>
</div>
<hr>
<div>"type":"getUserInfoByUid","gameType":2,"data":[]</div>
<div>"type":"bet","gameType":2,"qishu":734340,"data":{"allin":0,"dobule":0,"price":25,"bet":25,"groupIndex":4}</div>
<div>"type":"nextGame","gameType":2,"data":[]</div>
<div>"type":"getUserList","gameType":2</div>
<div>"type":"pai","gameType":2</div>
<div id="text"></div>
<div id="output"></div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script>
    if (window.WebSocket) {
        console.log("WebSocket supported");
        //通过WebSocket进行操作
    }else{
        //不支持WebSocket，给出相应的提示和处理策略
        alert("Consider updating your browser for a richer experience");
    }

    var output, message;
    var wsUri = "ws://<?=$ip?>:<?=$port?>";

    function init(){
        output = $("#output");

        $("#btn-connect").click(function(){
            testWebSocket();
        })
        $("#btn-disConnect").click(function(){
            websocket.close();
        })
        $("#btn-sent").click(function(){
            message = $("#chatroom").val();
            var myJSON = JSON.stringify(eval("({" + message + ",\"uid\":\""+readCookie("cookie")+"\"})"));
            doSend(myJSON);
        });
    }
    function testWebSocket(message){
        websocket = new WebSocket(wsUri);

        //判斷 wsUri 是否有順利連線
        websocket.onopen = function(e){
            $("#chatroom").prop('disabled', false);
            onOpen()
        }
        //結束連線
        websocket.onclose  = function(e){
            $("#chatroom").prop('disabled', true);
            onClose(e)
        }
        //傳輸內容
        websocket.onmessage = function(e){
            onMessage(e)
        }
        //連線過後，若傳輸內容有錯誤發生，印出錯誤內容
        websocket.onerreor  = function(e){
            onError(e);
        }
    }

    function onOpen(message){
        writeToScreen("Connect");
    }
    function onClose(e){
        console.log(e);
        writeToScreen("Disconnect");
    }
    function onMessage(e){
        writeToScreen("Received:" + e.data);
    }
    function onError(e){
        writeToScreen("Error" + e.data);
    }
    function doSend(msg){
        writeToScreen("Sent:" + msg);
        //send
        websocket.send(msg);
    }
    function writeToScreen(message){
        var pre = $("#text");
        output.append('<p>' + message +'</p>');
    }
    window.addEventListener("load", init, false);
    
    function readCookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

</script>
</body>
</html>
