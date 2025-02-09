<?php
// index.php
$type = $_GET['type'] ?? null;
$deviceIdentifier = $_GET['deviceIdentifier'] ?? null;
$passWord = $_GET['passWord'] ?? null;
if((!$type)||(!$deviceIdentifier)||(!$passWord)){
    echo "provide query parameters type:$type deviceIdentifier:$deviceIdentifier passWord:$passWord\n";;
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>{{$deviceIdentifier}}</title>
</head>
<body>
    <div id="debug"></div>
    <div id="content"></div>
    <script>
        const ws = new WebSocket('wss://myRemoteDevice.ya-niv.com:8443/wss/?type={{$type}}&deviceIdentifier={{$deviceIdentifier}}&passWord={{$passWord}}');
        ws.onopen = (event) => {
            console.log('searching for {{$deviceIdentifier}}');
            ws.send(JSON.stringify({
                action: 'getPage',
            }));
        };
        ws.onmessage = (event) => {
            const data = JSON.parse(JSON.parse(event.data).msg);
            console.log('onmessage');
            console.log(data);
            if (data.msg.action==='respondingToGetPage') {
                console.log('got respondingToGetPage');
                document.body.innerHTML = ""; 
                let newDiv = document.createElement("div");
                newDiv.innerHTML = data.msg.page;
                document.body.appendChild(newDiv);
                const scripts = document.body.getElementsByTagName("script");
                Array.from(scripts).forEach(oldScript => {
                    const newScript = document.createElement("script");
                    Array.from(oldScript.attributes).forEach(attr => {
                        newScript.setAttribute(attr.name, attr.value);
                    });
                    newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                    oldScript.parentNode.replaceChild(newScript, oldScript);
                });
            }
        };
        ws.onerror = (error) => {
            console.error('WebSocket error:', error);
        };
        ws.onclose = () => {
            console.log ('something is wrong, onclose');
        };
    </script>
</body>
</html>
