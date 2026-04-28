<?php
// PHP Compiler by Jaivansh - Stable Version

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_PHP_FORGE'])) {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? 'run';
    $code = $input['code'] ?? '';

    if (empty(trim($code))) {
        echo json_encode(['output' => '', 'error' => 'No code provided', 'time' => 0, 'syntax_ok' => false]);
        exit;
    }

    $tmpFile = tempnam(sys_get_temp_dir(), 'php_') . '.php';
    if (strpos(trim($code), '<?') !== 0) {
        $code = "<?php\n" . $code;
    }
    file_put_contents($tmpFile, $code);

    $syntaxOutput = shell_exec('php -l ' . escapeshellarg($tmpFile) . ' 2>&1');
    $syntaxOk = (strpos($syntaxOutput, 'No syntax errors') !== false);

    if ($action === 'syntax') {
        @unlink($tmpFile);
        echo json_encode([
            'syntax_ok' => $syntaxOk,
            'error' => $syntaxOk ? '' : str_replace($tmpFile, 'your code', $syntaxOutput),
            'output' => '',
            'time' => 0
        ]);
        exit;
    }

    $startTime = microtime(true);
    $output = shell_exec('php ' . escapeshellarg($tmpFile) . ' 2>&1');
    $executionTime = round((microtime(true) - $startTime) * 1000, 2);
    @unlink($tmpFile);

    echo json_encode([
        'output' => $output ?: '',
        'error' => $syntaxOk ? '' : str_replace($tmpFile, 'your code', $syntaxOutput),
        'time' => $executionTime,
        'syntax_ok' => $syntaxOk
    ]);
    exit;
}

$phpVersion = phpversion();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>PHP Compiler by Jaivansh</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', monospace;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            font-size: 28px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 5px;
        }

        .header p {
            color: #666;
            font-size: 14px;
        }

        .version-badge {
            display: inline-block;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            margin-top: 10px;
        }

        .main-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 15px 20px;
            font-weight: bold;
            font-size: 16px;
        }

        textarea {
            width: 100%;
            min-height: 500px;
            padding: 20px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.6;
            border: none;
            background: #1e1e2e;
            color: #cdd6f4;
            resize: vertical;
        }

        textarea:focus {
            outline: none;
        }

        .output {
            min-height: 500px;
            padding: 20px;
            background: #f8f9fc;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.6;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .output-empty {
            color: #999;
            text-align: center;
            margin-top: 200px;
        }

        .output-success {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .output-error {
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            color: #991b1b;
        }

        .output-result {
            background: #1e1e2e;
            color: #cdd6f4;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            overflow-x: auto;
        }

        .buttons {
            padding: 15px 20px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        button {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .snippets {
            padding: 10px 20px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .snippet-btn {
            padding: 5px 12px;
            background: #e5e7eb;
            border: none;
            border-radius: 20px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .snippet-btn:hover {
            background: #667eea;
            color: white;
        }

        .status-bar {
            margin-top: 20px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            body { padding: 10px; }
            .main-grid { grid-template-columns: 1fr; gap: 15px; }
            textarea { min-height: 300px; }
            .output { min-height: 300px; }
            .header h1 { font-size: 22px; }
            button { padding: 8px 16px; font-size: 12px; flex: 1; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PHP Compiler by Jaivansh</h1>
            <p>Professional PHP Development Environment</p>
            <div class="version-badge">PHP <?php echo $phpVersion; ?></div>
        </div>

        <div class="main-grid">
            <div class="card">
                <div class="card-header"> Code Editor</div>
                <textarea id="codeEditor">
<?php
echo "Hello, PHP Compiler by Jaivansh!\n";
echo "Welcome to PHP Compiler!\n";
echo "PHP Version: " . PHP_VERSION . "\n";
?></textarea>
                <div class="snippets">
                    <button class="snippet-btn" data-code="hello">Hello World</button>
                    <button class="snippet-btn" data-code="array">Arrays</button>
                    <button class="snippet-btn" data-code="loop">Loops</button>
                    <button class="snippet-btn" data-code="func">Functions</button>
                </div>
                <div class="buttons">
                    <button class="btn-primary" id="checkBtn">Check Syntax</button>
                    <button class="btn-success" id="runBtn">Run Code</button>
                    <button class="btn-secondary" id="clearBtn">Clear Output</button>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Output & Results</div>
                <div class="output" id="outputArea">
                    <div class="output-empty">Click "Run Code" to see output here</div>
                </div>
            </div>
        </div>

        <div class="status-bar">
            <div>Status: <span id="statusText">Ready</span></div>
            <div>PHP Compiler by Jaivansh</div>
        </div>
    </div>

    <script>
        const editor = document.getElementById('codeEditor');
        const outputArea = document.getElementById('outputArea');
        const statusText = document.getElementById('statusText');

        const snippets = {
            hello: `<?php
echo "Hello, PHP Compiler by Jaivansh!\\n";
$name = "Developer";
echo "Welcome, $name!\\n";
echo "PHP Version: " . PHP_VERSION . "\\n";
?>`,
            array: `<?php
$fruits = ["apple", "banana", "cherry", "date"];
echo "Fruits:\\n";
foreach($fruits as $fruit) {
    echo "  - $fruit\\n";
}
?>`,
            loop: `<?php
echo "Numbers 1 to 5:\\n";
for($i = 1; $i <= 5; $i++) {
    echo "  $i\\n";
}
?>`,
            func: `<?php
function greet($name) {
    return "Hello, $name!";
}
echo greet("Jaivansh") . "\\n";
?>`
        };

        function showLoading() {
            outputArea.innerHTML = '<div style="text-align:center;padding:50px"><div class="loading"></div><br>Processing...</div>';
        }

        function escapeHtml(text) {
            return String(text).replace(/[&<>]/g, function(m) {
                if (m === '&') return '&amp;';
                if (m === '<') return '&lt;';
                if (m === '>') return '&gt;';
                return m;
            });
        }

        async function checkSyntax() {
            const code = editor.value;
            if (!code.trim()) {
                outputArea.innerHTML = '<div class="output-error">Please write some code first</div>';
                return;
            }

            showLoading();
            
            try {
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-PHP-Forge': '1'
                    },
                    body: JSON.stringify({ code: code, action: 'syntax' })
                });
                
                const data = await response.json();
                
                if (data.syntax_ok) {
                    outputArea.innerHTML = '<div class="output-success"> No syntax errors found! Your code is valid.</div>';
                    statusText.innerHTML = 'Ready';
                } else {
                    outputArea.innerHTML = '<div class="output-error"> Syntax Error:<br>' + escapeHtml(data.error) + '</div>';
                    statusText.innerHTML = 'Error';
                }
            } catch (error) {
                outputArea.innerHTML = '<div class="output-error">Failed to check syntax: ' + escapeHtml(error.message) + '</div>';
            }
        }

        async function runCode() {
            const code = editor.value;
            if (!code.trim()) {
                outputArea.innerHTML = '<div class="output-error">Please write some code first</div>';
                return;
            }

            showLoading();
            
            try {
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-PHP-Forge': '1'
                    },
                    body: JSON.stringify({ code: code, action: 'run' })
                });
                
                const data = await response.json();
                
                let html = '';
                
                if (data.syntax_ok) {
                    if (data.output) {
                        html += '<div class="output-result"> Output:<br><br>' + escapeHtml(data.output) + '</div>';
                    } else {
                        html += '<div class="output-success"> Code executed successfully!</div>';
                    }
                    html += '<div style="font-size:12px;color:#666;margin-top:10px"> Execution time: ' + data.time + 'ms</div>';
                    statusText.innerHTML = 'Done';
                } else {
                    html += '<div class="output-error"> Error:<br>' + escapeHtml(data.error) + '</div>';
                    statusText.innerHTML = 'Error';
                }
                
                outputArea.innerHTML = html;
            } catch (error) {
                outputArea.innerHTML = '<div class="output-error">Failed to run code: ' + escapeHtml(error.message) + '</div>';
            }
        }

        function clearOutput() {
            outputArea.innerHTML = '<div class="output-empty">Click "Run Code" to see output here</div>';
            statusText.innerHTML = 'Ready';
        }

        document.getElementById('checkBtn').addEventListener('click', checkSyntax);
        document.getElementById('runBtn').addEventListener('click', runCode);
        document.getElementById('clearBtn').addEventListener('click', clearOutput);

        document.querySelectorAll('.snippet-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const code = snippets[btn.dataset.code];
                if (code) {
                    editor.value = code;
                    editor.focus();
                }
            });
        });

        editor.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                runCode();
            }
        });
    </script>
</body>
</html>