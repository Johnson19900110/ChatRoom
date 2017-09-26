<?php require('./config/config.php');?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport"
          content="initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="format-detection" content="telephone=no">
    <title>聊天室</title>
    <link rel="stylesheet" href="./css/frozen.css">
    <link rel="stylesheet" href="./css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/chat.css">
</head>
<body ontouchstart="">
<header class="ui-header ui-header-positive ui-border-b">
    <h1>聊天室</h1>
</header>


<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">
                    请输入你的名字
                </h4>
            </div>
            <div class="modal-body">
                <input id="nickname" type="text" class="form-control"/>
            </div>
            <div class="modal-footer">
                <button type="button" id="set-name" class="btn btn-primary">
                    确定
                </button>
            </div>
        </div>
    </div>
</div>

<div class="ui-container" id="content">
    <div id="chat-list">
        <ul class="ui-list-text border-list ui-border-tb" id="chat-list2">
        </ul>
    </div>
</div>

<footer class="footer">
    <section class="ui-input-wrap ui-border-t">
        <div class="ui-input ui-border-radius">
            <input type="text" name="" value="" placeholder="我也说一句..." id="input">
        </div>
        <button class="ui-btn" id="submit">发送</button>
    </section>
</footer>

<script src="./js/jquery-1.11.3.min.js"></script>
<script src="./js/zepto.min.js"></script>
<script src="./js/frozen.js"></script>
<script src="./js/bootstrap.min.js"></script>
<script>
    $(function () {
        //change the margin of content
        $("#content").css('margin-bottom', $("footer").height());

        let username = '';
        $('#set-name').click(function () {
           username = $('#nickname').val();
           if(username) {
               sessionStorage.setItem('username', username);
               $('#myModal').modal('hide');
               webSocket.send(JSON.stringify({
                   'message': username,
                   'type': 'init'
               }));
           }else {
               alert('请输入你的名字');
           }
        });

        function setName() {
            $('#myModal').modal({
                keyboard: false
            });
        }

        // websocket
        let address = 'ws://<?php echo CLIENT_CONNECT_ADDR . ':' . CLIENT_CONNECT_PORT ?>';
        let webSocket = new WebSocket(address);
        webSocket.onerror = function (event) {
            alert('服务器连接错误，请稍后重试');
        };
        webSocket.onopen = function (event) {
            if(!sessionStorage.getItem('username')) {
                setName();
            }else {
                username = sessionStorage.getItem('username')
                webSocket.send(JSON.stringify({
                    'message': username,
                    'type': 'init'
                }));
            }
        };
        webSocket.onmessage = function (event) {
            console.log(event);
            let data = JSON.parse(event.data);
            if (data.type == 1) {
                $('#chat-list2').append('<li class="ui-border-tb"><span class="username">系统消息:</span><span class="message">' + data.message + '</span></li>');
            } else if (data.type == 2) {
                $('#chat-list2').append('<li class="ui-border-tb"><span class="username">' + data.username + ':</span><span class="message">' + data.message + '</span></li>');
            }

        };
        webSocket.onclose = function (event) {
            alert('散了吧，服务器都关了');
        };


        //触发发送消息事件
        $("#submit").click(function () {
            let message = $("#input").val();
            $("#input").val('');
            if ( !username ) {
                setName();
                return false;
            }
            if (message) {
                webSocket.send(JSON.stringify({
                    'message': message,
                    'type': 'chat'
                }));
            }
        });
        //回车发送消息
        $("#input").keypress(function () {
            if (event.keyCode == 13) {
                let message = $("#input").val();
                $("#input").val('');
                console.log(username);
                if ( !username ) {
                    setName();
                    return false;
                }
                if (message){
                    webSocket.send(JSON.stringify({
                        'message': message,
                        'type': 'chat'
                    }));
                }
            }
        });

        
    });
</script>
</body>
</html>


