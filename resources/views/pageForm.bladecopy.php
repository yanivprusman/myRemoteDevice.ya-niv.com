<!-- pageForm.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Form</title>
    <style>
        .form-container {
            max-width: {{ $maxWidth ?? '800px' }};
            margin: 20px auto;
            padding: 20px;
        }
        .input-row {
            display: flex;
            gap: {{ $inputGap ?? '20px' }};
            margin-bottom: 20px;
        }
        .input-field {
            flex: 1;
        }
        .html-content {
            width: 100%;
            min-height: {{ $textareaHeight ?? '300px' }};
            margin-bottom: 20px;
        }
        .output-field {
            width: 100%;
            min-height: {{ $outputHeight ?? '150px' }};
            margin-top: 20px;
            background-color: #f5f5f5;
            padding: 10px;
            border: 1px solid #ddd;
        }
        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        input, textarea {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <form id="downloadForm">
            <div class="input-row">
                <div class="input-field">
                    <input type="text" id="deviceName" name="deviceName" placeholder="Device Name" required>
                </div>
                <div class="input-field">
                    <input type="password" id="passWord" name="passWord" placeholder="Password" required>
                </div>
            </div>
            
            <textarea id="htmlContent" name="htmlContent" class="html-content" placeholder="Enter HTML content here"></textarea>
            
            <button type="button" onclick="generateFile()">Download File</button>
            
            <textarea id="output" class="output-field" readonly placeholder="Output will appear here"></textarea>
        </form>
    </div>

    <script>
        function generateFile() {
            const deviceName = document.getElementById('deviceName').value;
            const passWord = document.getElementById('passWord').value;
            const htmlContent = document.getElementById('htmlContent').value;
            const outputField = document.getElementById('output');

            // Create the file content using the input values
            const fileContent = `Device: ${deviceName}\nPassword: ${passWord}\n\nContent:\n${htmlContent}`;
            
            // Create a Blob with the content
            const blob = new Blob([fileContent], { type: 'text/plain' });
            const url = window.URL.createObjectURL(blob);

            // Create a temporary link and trigger download
            const a = document.createElement('a');
            a.href = url;
            a.download = `${deviceName}_config.txt`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);

            // Update output field
            outputField.value = `File generated successfully for device: ${deviceName}`;
        }
    </script>
</body>
</html>