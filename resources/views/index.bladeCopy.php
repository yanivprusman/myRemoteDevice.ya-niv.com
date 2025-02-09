<?php
// index.php
$type = $_GET['type'] ?? null;
//$type = QueryParams::getString('type'); refactor?
$deviceIdentifier = $_GET['deviceIdentifier'] ?? null;
$passWord = $_GET['passWord'] ?? null;
$embedInWebSocket = $_GET['embedInWebSocket'] ?? null;
if((!$type)||(!$deviceIdentifier)||(!$passWord)||(!$embedInWebSocket)){
    echo "provide query parameters type:$type deviceIdentifier:$deviceIdentifier passWord:$passWord embedInWebSocket:$embedInWebSocket\n";;
    exit;
}
if($embedInWebSocket==='false'){
    echo "not embeded";
}else{
    echo "embeded";

}
exit;
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
        function openTab(page) {
            blob = new Blob([page], { type: 'text/html' });
            url = window.URL.createObjectURL(blob);
            window.open(url, "_blank");
        }
        const ws = new WebSocket('wss://myRemoteDevice.ya-niv.com:8443/wss/?type={{$type}}&deviceIdentifier={{$deviceIdentifier}}&passWord={{$passWord}}');
        ws.onopen = (event) => {
            console.log('searching for {{$deviceIdentifier}}');
            ws.send(JSON.stringify({
                action: 'getPage',
            }));
        };
        ws.onmessage = (event) => {
            const data = JSON.parse(JSON.parse(event.data).msg);
            // const data = JSON.parse(event.data);
            console.log('onmessage');
            console.log(data);
            if (data.msg.action==='respondingToGetPage') {
                console.log('got respondingToGetPage');
                // window.open("https://example.com", "_blank");

                // document.getElementById('debug').innerHTML = data.msg.page; //working good, try upgrading
                document.documentElement.innerHTML = data.msg.page;
                const scripts = document.getElementsByTagName('script');
                for (let script of scripts) {
                    const newScript = document.createElement('script');
                    newScript.text = script.text;
                    if (script.src) newScript.src = script.src;
                    script.parentNode.replaceChild(newScript, script);
                }
                // document.getElementById('content').innerHTML = data.msg.page;
                // document.getElementById('content').replaceWith(
                //     Object.assign(document.createElement('button'), {
                //         innerHTML: "Open Page",
                //         onclick: () => {
                //             openTab(data.msg.page); 
                //         }
                //     })
                // );
            }

        };
        ws.onerror = (error) => {
            console.error('WebSocket error:', error);
            // document.getElementById('content').innerHTML = 'Error ' + error;
        };
        ws.onclose = () => {
            console.log ('something is wrong, onclose');
        };
    </script>
</body>
</html>
