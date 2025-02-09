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
        .form-container {
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
    <div class="form-container">
        <form id="downloadForm">
            <div class="input-row">
                <div class="input-field">
                    <input type="text" id="deviceIdentifier" name="deviceIdentifier" placeholder="Name the page" required>
                </div>
                <div class="input-field">
                    <input type="password" id="passWord" name="passWord" placeholder="Set password" required>
                </div>
            </div>
            <textarea id="pageContent" name="pageContent" class="html-content" placeholder="put here page content"></textarea>
            <div class="websocketOption">
                <input type="checkbox" id="embedWebsocket">
                <label for="embedWebsocket">embed in webSocket</label>
            </div>    
            <br>
            <button type="button" onclick="runIt()">Run</button>
            <button type="button" onclick="saveFile()">Download</button>
            <button type="button" onclick="copyLink()">Copy Link</button>
            <textarea id="output" class="output-field" readonly placeholder="Output will appear here"></textarea>
        </form>
    </div>
    <script>
        var pageContent;
        var pageContentInWebSocket = '';
        var blobPageContent;
        var blobPageContentInWebSocket;
        var url;
        var deviceIdentifier;
        var port = 8443;
        var passWord;
        function readVariables() {
            deviceIdentifier = document.getElementById('deviceIdentifier').value;
            passWord = document.getElementById('passWord').value;
            pageContent = document.getElementById('pageContent').value || 'hi from device';
        }
        function generateTemplate() {
            readVariables();
            pageContentInWebSocket = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebSocket Client As Server</title>
</head>
<body>
    <button type="button" onclick="openIt()">Open</button>
    <div id="info"></div>
    <div id="status">Not Connected</div>
    <div id="messages"></div>
    <script>
        // var page
        function openIt() {
            blobPageContent = new Blob([\`${pageContent}\`], { type: 'text/html' });
            url = window.URL.createObjectURL(blobPageContent);
            // console.log( \  $ { p a g e C o ntent}\);
            window.open(url, "_blank");
            // window.open("https://myremotedevice.ya-niv.com/?type=client&deviceIdentifier=${this.deviceIdentifier}&passWord=${this.passWord}", "_blank");
        }
        class WebSocketClient {
            constructor() {
                this.deviceIdentifier = '${deviceIdentifier}';
                this.passWord = '${passWord}';
                this.port = 8443;
                this.connection = null;
                this.connected = false;
                this.info = document.getElementById('info');
                this.statusElement = document.getElementById('status');
                this.messagesElement = document.getElementById('messages');
            }
            connect() {
                const wsUrl = \`wss://myRemoteDevice.ya-niv.com:\${this.port}/?type=device&deviceIdentifier=\${this.deviceIdentifier}&passWord=\${this.passWord}\`;
                try {
                    this.connection = new WebSocket(wsUrl);
                    this.setupEventListeners();
                } catch (error) {
                    this.updateStatus(\`Connection error: \${error.message}\`);
                }
            }
            setupEventListeners() {
                this.connection.onopen = () => {
                    this.connected = true;
                    this.info.textContent = "Keep this tab open to run server";
                    this.updateStatus('Connected to WebSocket server');
                };
                this.connection.onmessage = (event) => {
                    try {
                        const msg = event.data;
                        this.logMessage(\`Received message: \${msg}\`);
                        const parsedMsg = JSON.parse(msg);
                        if (!parsedMsg) {
                            this.logMessage("Received invalid JSON");
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
                                    page:  'ggg' //$ {pageContent} 
                                }
                            };
                            this.sendMessage(response);
                        }
                    } catch (error) {
                        this.logMessage(\`Error processing message: \${error.message}\`);
                    }
                };
                this.connection.onclose = () => {
                    this.connected = false;
                    this.updateStatus('Disconnected from server');
                    // Attempt to reconnect after 5 seconds
                    setTimeout(() => this.connect(), 5000);
                };
                this.connection.onerror = (error) => {
                    this.updateStatus(\`WebSocket error: \${error.message}\`);
                };
            }
            sendMessage(data) {
                if (this.connected && this.connection) {
                    try {
                        this.connection.send(JSON.stringify(data));
                        this.logMessage(\`Sent message: \${JSON.stringify(data)}\`);
                    } catch (error) {
                        this.logMessage(\`Error sending message: \${error.message}\`);
                    }
                } else {
                    this.logMessage('Cannot send message: Not connected');
                }
            }
            updateStatus(status) {
                this.statusElement.textContent = status;
                this.logMessage(status);
            }
            logMessage(message) {
                const messageElement = document.createElement('div');
                messageElement.textContent = \`\${new Date().toISOString()}: \${message}\`;
                this.messagesElement.insertBefore(messageElement, this.messagesElement.firstChild);
            }
        }
        // Initialize and connect
        const client = new WebSocketClient();
        client.connect();
    <\/script>
</body>
</html>`;
            blobPageContent = new Blob([pageContent], { type: 'text/html' });
            blobPageContentInWebSocket = new Blob([pageContentInWebSocket], { type: 'text/html' });
            urlPageContent = window.URL.createObjectURL(blobPageContent);
            urlPageContentInWebSocket = window.URL.createObjectURL(blobPageContentInWebSocket);
        }
        function runIt() {
            generateTemplate();
            // window.open("https://example.com", "_blank");
            // window.open(urlPageContentInWebSocket, "_blank");
            // window.open(urlPageContent, "_blank");
            const url = `https://myRemoteDevice.ya-niv.com/?type=device&deviceIdentifier=${encodeURIComponent(this.deviceIdentifier)}&passWord=${encodeURIComponent(this.passWord)}`;
            window.open(url, "_blank");
            // window.open(`https://myRemoteDevice.ya-niv.com:\${this.port}/?type=device&deviceIdentifier=\${this.deviceIdentifier}&passWord=\${this.passWord}`, "_blank");

        }
        // function openIt() {
        //     readVariables();
        //     window.open(`https://myremotedevice.ya-niv.com/?type=client&deviceIdentifier=${this.deviceIdentifier}&passWord=${this.passWord}", "_blank"`);
        // }
        function saveFile() {
            generateTemplate();
            const a = document.createElement('a');
            a.href = url;
            a.download = `webSocket_client_${deviceIdentifier}.html`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
            document.getElementById('output').value = `WebSocket client file generated successfully for device: ${deviceIdentifier}\n`;
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