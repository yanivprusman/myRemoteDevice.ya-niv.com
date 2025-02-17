<!-- resources/views/webSocket-generator.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web page Generator</title>
    <style>
        .websocketOption {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 5px;
        }
        .form-style {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }
        .input-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .input-field {
            flex: 1;
        }
        .html-content {
            width: 100%;
            min-height: 300px;
            margin-bottom: 20px;
            font-family: monospace;
        }
        .output-field {
            width: 100%;
            min-height: 150px;
            margin-top: 20px;
            background-color: #f5f5f5;
            padding: 10px;
            border: 1px solid #ddd;
            font-family: monospace;
        }
        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 10px;
        }
        button:hover {
            background-color: #45a049;
        }
        input, textarea {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 100%;
            box-sizing: border-box;
        }
    </style>
</head>
<body>
    <div class="form-style">
        <form>
            <div class="input-row">
                <div class="input-field">
                    <input type="text" id="deviceIdentifier" name="deviceIdentifier" placeholder="Name the page" required>
                </div>
                <div class="input-field">
                    <input type="password" id="passWord" name="passWord" placeholder="Set password" required>
                </div>
            </div>
            <textarea id="pageContent" name="pageContent" class="html-content" placeholder="put here page content"></textarea>
            <button type="button" onclick="createServer()">Create server</button>
            <button type="button" onclick="saveFile()">Download for serving later</button>
            <textarea id="output" class="output-field" readonly placeholder="Output will appear here"></textarea>
        </form>
    </div>
    <script>
        var pageContent;
        var deviceServerContent;
        var deviceIdentifier;
        var port = 8443;
        var passWord;
        var url;
        function readVariables() {
            deviceIdentifier = document.getElementById('deviceIdentifier').value;
            passWord = document.getElementById('passWord').value;
            pageContent = document.getElementById('pageContent').value || 'hi from device';
        }
        function createDeviceServerContent(){
            readVariables();
            pageContent = pageContent.replace(/<(\/?)script/g, "<$1${'script'}");
            console.log(pageContent);
            
            deviceServerContent = `
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebSocket Device Server</title>
    <style>
        #content {
            display: none;
            margin-top: 10px;
            padding: 10px;
            border: 1px solid #ccc;
        }
        .toggle-button {
            cursor: pointer;
            color: blue;
            text-decoration: underline;
        }
    </style>
</head>
<body> 
    Keep this tab open to run server<br> 
    <label for="link">The link to open the page:</label>
    <input type="text" id="link" readonly><br>
    <button type="button" onclick="copyLink()">Copy Link</button>
    <button type="button" onclick="openIt()">Open client in new tab</button>
    <${'script'}>
        let link = "https://myremotedevice.ya-niv.com/?type=client&deviceIdentifier=${deviceIdentifier}&passWord=${passWord}";        
        let inputField = document.getElementById("link");
        inputField.value = link;
        inputField.size = link.length;
        function copyLink() {
            const link = "https://myremotedevice.ya-niv.com/?type=client&deviceIdentifier=${deviceIdentifier}&passWord=${passWord}";          
            navigator.clipboard.writeText(link);
        }
        function openIt() {
            window.open("https://myremotedevice.ya-niv.com/?type=client&deviceIdentifier=${this.deviceIdentifier}&passWord=${this.passWord}", "_blank");
        }
        function toggleContent() {
            let content = document.getElementById("content");
            content.style.display = (content.style.display === "none" || content.style.display === "") 
                ? "block" 
                : "none";
        }
    </${'script'}><br>
    <div class="toggle-button" onclick="toggleContent()">Page Content</div>
    <div id="content">
        \`${pageContent}\`
    </div>
    <script>
        // window.open("https://myremotedevice.ya-niv.com/?type=client&deviceIdentifier=${this.deviceIdentifier}&passWord=${this.passWord}", "_blank");
        class WebSocketClient {
            constructor() {
                this.deviceIdentifier = '${deviceIdentifier}';
                this.passWord = '${passWord}';
                this.port = 8443;
                this.connection = null;
                this.connected = false;
            }
            connect() {
            
                const wsUrl = "wss://myRemoteDevice.ya-niv.com:${this.port}/?type=device&deviceIdentifier=${this.deviceIdentifier}&passWord=${this.passWord}";
                try {
                    this.connection = new WebSocket(wsUrl);
                    this.setupEventListeners();
                } catch (error) {
                    this.updateStatus("Connection error: {error.message}");
                }
            }
            setupEventListeners() {
                this.connection.onopen = () => {
                    this.connected = true;
                };
                this.connection.onmessage = (event) => {
                    try {
                        const msg = event.data;
                        const parsedMsg = JSON.parse(msg);
                        if (!parsedMsg) {
                            return;
                        }
                        const data = {
                            msg: JSON.parse(parsedMsg.msg),
                            resourceId: parsedMsg.resourceId
                        };
                        if (data.msg.action === 'getPage') {
                            const response = {
                                msg: {
                                    resourceId: data.resourceId,
                                    action: 'respondingToGetPage',
                                    page: \`${pageContent}\`
                                }
                            };
                            this.sendMessage(response);
                        }
                    } catch (error) {
                    }
                };
                this.connection.onclose = () => {
                    this.connected = false;
                    setTimeout(() => this.connect(), 5000);
                };
                this.connection.onerror = (error) => {
                };
            }
            sendMessage(data) {
                if (this.connected && this.connection) {
                    try {
                        this.connection.send(JSON.stringify(data));
                    } catch (error) {
                    }
                } else {
                }
            }
            updateStatus(status) {
            }
        }
        // Initialize and connect
        const client = new WebSocketClient();
        client.connect();
    <\/script>
</body>
</html>`;
        }
        function createUrl(){
            createDeviceServerContent();
            blobDeviceServerContent = new Blob([deviceServerContent], { type: 'text/html' });
            url = window.URL.createObjectURL(blobDeviceServerContent);
        }
        function createServer() {
            createUrl();
            window.open(url, "_blank");
        }
        // function openIt() {
        //     readVariables();
        //     window.open(`https://myremotedevice.ya-niv.com/?type=client&deviceIdentifier=${this.deviceIdentifier}&passWord=${this.passWord}", "_blank"`);
        // }
        function saveFile() {
            createUrl();
            const a = document.createElement('a');
            a.download = `webSocket_device_server_${deviceIdentifier}.html`;
            a.href = url;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }
        function copyLink() {
            const deviceIdentifier = document.getElementById('deviceIdentifier').value;
            const passWord = document.getElementById('passWord').value;
            const link = `https://myremotedevice.ya-niv.com/?type=client&deviceIdentifier=${deviceIdentifier}&passWord=${passWord}`;
            
            navigator.clipboard.writeText(link)
                .then(() => {
                    document.getElementById('output').value = 'Link copied to clipboard!';
                })
                .catch(err => {
                    document.getElementById('output').value = 'Failed to copy link: ' + err;
                });
        }

        function openInNewTab() {
            const deviceIdentifier = document.getElementById('deviceIdentifier').value;
            const passWord = document.getElementById('passWord').value;
            const link = `https://myremotedevice.ya-niv.com/?type=client&deviceIdentifier=${deviceIdentifier}&passWord=${passWord}`;
            
            window.open(link, '_blank');
        }
    </script>
</body>
</html>