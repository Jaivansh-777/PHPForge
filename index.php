<?php
// PHP Compiler by Jaivansh - Stable Version with About, Contact, Privacy Pages

// Handle API/run requests
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

// Handle contact form POST
$contactSuccess = false;
$contactError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_form'])) {
    $name    = htmlspecialchars(trim($_POST['name'] ?? ''));
    $email   = htmlspecialchars(trim($_POST['email'] ?? ''));
    $subject = htmlspecialchars(trim($_POST['subject'] ?? ''));
    $message = htmlspecialchars(trim($_POST['message'] ?? ''));

    if ($name && $email && $subject && $message && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $to = 'jaivansh162@gmail.com';
        $headers = "From: $email\r\nReply-To: $email\r\n";
        mail($to, "[PHPForge] $subject", "Name: $name\nEmail: $email\n\n$message", $headers);
        $contactSuccess = true;
    } else {
        $contactError = 'Please fill in all fields with a valid email.';
    }
}

$phpVersion = phpversion();
$page = $_GET['page'] ?? 'compiler';
if (!in_array($page, ['compiler', 'about', 'contact', 'privacy'])) $page = 'compiler';

// SEO meta per page
$meta = [
    'compiler' => [
        'title' => 'PHPForge – Free Online PHP Compiler & IDE by Jaivansh',
        'desc'  => 'Run PHP code instantly in your browser. Free online PHP compiler with syntax checking, code snippets, and real-time output. No setup required.',
        'canonical' => '?page=compiler'
    ],
    'about' => [
        'title' => 'About PHPForge – PHP Compiler by Jaivansh',
        'desc'  => 'Learn about PHPForge – a free browser-based PHP compiler and IDE built by Jaivansh. Write, run, and debug PHP instantly without any setup.',
        'canonical' => '?page=about'
    ],
    'contact' => [
        'title' => 'Contact Us – PHPForge | PHP Compiler by Jaivansh',
        'desc'  => 'Get in touch with the PHPForge team. Report bugs, suggest features, or ask questions about our free online PHP compiler.',
        'canonical' => '?page=contact'
    ],
    'privacy' => [
        'title' => 'Privacy Policy – PHPForge | PHP Compiler by Jaivansh',
        'desc'  => 'Read the PHPForge privacy policy. Understand how we handle your data, cookies, and use of third-party services including Google Ads.',
        'canonical' => '?page=privacy'
    ]
];

$m = $meta[$page];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title><?php echo $m['title']; ?></title>
    <meta name="description" content="<?php echo $m['desc']; ?>">
    <meta name="keywords" content="PHP compiler, online PHP IDE, run PHP online, PHP sandbox, Jaivansh, PHPForge, PHP editor">
    <meta name="robots" content="index, follow">
    <meta name="author" content="Jaivansh">
    <link rel="canonical" href="<?php echo $m['canonical']; ?>">
    <meta property="og:title" content="<?php echo $m['title']; ?>">
    <meta property="og:description" content="<?php echo $m['desc']; ?>">
    <meta property="og:type" content="website">

    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebApplication",
      "name": "PHPForge",
      "url": "https://yoursite.com",
      "description": "Free browser-based PHP compiler and IDE by Jaivansh",
      "applicationCategory": "DeveloperApplication",
      "operatingSystem": "Web",
      "author": { "@type": "Person", "name": "Jaivansh" }
    }
    </script>

    <style>
        /* ===== RESET & BASE ===== */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', monospace;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        /* ===== NAV ===== */
        nav {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(12px);
            padding: 14px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 200;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }

        .nav-brand {
            color: white;
            font-weight: 800;
            font-size: 20px;
            text-decoration: none;
            letter-spacing: -0.5px;
        }

        .nav-links { display: flex; gap: 6px; }

        .nav-links a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            padding: 7px 14px;
            border-radius: 20px;
            transition: all 0.2s;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        .nav-links a:hover { color: white; background: rgba(255,255,255,0.15); }
        .nav-links a.active { color: white; background: rgba(255,255,255,0.2); }

        /* ===== COMPILER PAGE ===== */
        .compiler-wrap {
            padding: 20px;
        }

        .header {
            background: rgba(255,255,255,0.95);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 28px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 5px;
        }

        .header p { color: #666; font-size: 14px; }

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
            max-width: 1400px;
            margin: 0 auto;
        }

        .card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .card-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 15px 20px;
            font-weight: bold;
            font-size: 16px;
        }

        textarea#codeEditor {
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

        textarea#codeEditor:focus { outline: none; }

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

        .output-empty { color: #999; text-align: center; margin-top: 200px; }

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

        .btn-primary { background: linear-gradient(135deg, #667eea, #764ba2); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102,126,234,0.4); }
        .btn-success { background: #10b981; color: white; }
        .btn-success:hover { background: #059669; transform: translateY(-2px); }
        .btn-secondary { background: #6b7280; color: white; }
        .btn-secondary:hover { background: #4b5563; }

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

        .snippet-btn:hover { background: #667eea; color: white; }

        .status-bar {
            margin-top: 20px;
            background: rgba(255,255,255,0.95);
            border-radius: 10px;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            flex-wrap: wrap;
            gap: 10px;
            max-width: 1400px;
            margin-left: auto;
            margin-right: auto;
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

        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        /* ===== INNER PAGES SHARED ===== */
        .page-wrap {
            max-width: 900px;
            margin: 0 auto;
            padding: 50px 20px 80px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        .page-hero {
            background: rgba(255,255,255,0.97);
            border-radius: 20px;
            padding: 45px 50px;
            margin-bottom: 22px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.12);
            text-align: center;
        }

        .page-badge {
            display: inline-block;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 5px 16px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            margin-bottom: 18px;
        }

        .page-hero h1 {
            font-size: 36px;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 14px;
            line-height: 1.2;
        }

        .page-hero p { font-size: 16px; color: #666; line-height: 1.7; }

        .info-card {
            background: rgba(255,255,255,0.97);
            border-radius: 15px;
            padding: 35px 40px;
            margin-bottom: 18px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }

        .info-card h2 {
            font-size: 20px;
            font-weight: 700;
            color: #1e1e2e;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-card h2 .ico {
            width: 34px;
            height: 34px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .info-card p, .info-card li {
            color: #555;
            line-height: 1.8;
            font-size: 15px;
        }

        .info-card ul { padding-left: 20px; margin-top: 8px; }
        .info-card li { margin-bottom: 6px; }

        .info-card h3 {
            font-size: 16px;
            color: #1e1e2e;
            margin: 18px 0 8px;
            font-weight: 600;
        }

        /* Features grid (about) */
        .features-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-top: 14px;
        }

        .feat-item {
            background: linear-gradient(135deg, #f0f0ff, #f8f0ff);
            border-radius: 10px;
            padding: 18px;
            border-left: 3px solid #667eea;
        }

        .feat-item h3 { font-size: 13px; font-weight: 700; color: #667eea; margin-bottom: 5px; }
        .feat-item p { font-size: 13px; color: #666; line-height: 1.5; }

        /* Contact form */
        .form-group { margin-bottom: 18px; }

        label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: #444;
            margin-bottom: 7px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        input[type="text"],
        input[type="email"],
        select,
        textarea.msg {
            width: 100%;
            padding: 11px 15px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
            color: #1e1e2e;
            background: #f9fafb;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }

        input:focus, select:focus, textarea.msg:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.12);
            background: white;
        }

        textarea.msg { min-height: 140px; resize: vertical; }

        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }

        .submit-btn {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .submit-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(102,126,234,0.35); }

        .form-success {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            border-radius: 10px;
            padding: 14px 18px;
            color: #065f46;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 18px;
        }

        .form-error {
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            border-radius: 10px;
            padding: 14px 18px;
            color: #991b1b;
            font-size: 14px;
            margin-bottom: 18px;
        }

        /* CTA card */
        .cta-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 15px;
            padding: 35px 40px;
            text-align: center;
            color: white;
            margin-bottom: 18px;
        }

        .cta-card h2 { font-size: 22px; font-weight: 800; margin-bottom: 10px; }
        .cta-card p { opacity: 0.9; margin-bottom: 20px; font-size: 15px; }

        .cta-btn {
            display: inline-block;
            background: white;
            color: #764ba2;
            padding: 11px 28px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .cta-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.2); }

        /* Privacy specific */
        .last-updated {
            background: #f0f0ff;
            border-radius: 8px;
            padding: 10px 15px;
            font-size: 13px;
            color: #667eea;
            font-weight: 600;
            margin-bottom: 20px;
            display: inline-block;
        }

        /* Footer */
        .page-footer {
            text-align: center;
            color: rgba(255,255,255,0.7);
            font-size: 13px;
            padding-bottom: 30px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        .page-footer a { color: rgba(255,255,255,0.9); text-decoration: none; }
        .page-footer a:hover { text-decoration: underline; }

        /* Responsive */
        @media (max-width: 768px) {
            body { padding: 0; }
            .compiler-wrap { padding: 10px; }
            .main-grid { grid-template-columns: 1fr; gap: 15px; }
            textarea#codeEditor, .output { min-height: 300px; }
            .header h1 { font-size: 22px; }
            button { padding: 8px 16px; font-size: 12px; }
            nav { padding: 12px 16px; }
            .nav-brand { font-size: 16px; }
            .nav-links a { font-size: 11px; padding: 6px 10px; }
            .page-wrap { padding: 30px 15px 60px; }
            .page-hero { padding: 30px 22px; }
            .page-hero h1 { font-size: 26px; }
            .info-card { padding: 25px 22px; }
            .features-grid { grid-template-columns: 1fr; }
            .form-row { grid-template-columns: 1fr; }
            .cta-card { padding: 28px 22px; }
        }
    </style>
</head>
<body>

<!-- ===== NAV ===== -->
<nav>
    <a href="?page=compiler" class="nav-brand">⚙ PHPForge</a>
    <div class="nav-links">
        <a href="?page=compiler" <?php if($page==='compiler') echo 'class="active"'; ?>>Compiler</a>
        <a href="?page=about"   <?php if($page==='about')    echo 'class="active"'; ?>>About</a>
        <a href="?page=contact" <?php if($page==='contact')  echo 'class="active"'; ?>>Contact</a>
        <a href="?page=privacy" <?php if($page==='privacy')  echo 'class="active"'; ?>>Privacy</a>
    </div>
</nav>

<?php /* ============================================================
   PAGE: COMPILER
   ============================================================ */ ?>
<?php if ($page === 'compiler'): ?>

<div class="compiler-wrap">
    <div class="header" style="max-width:1400px;margin:0 auto 20px;">
        <h1>PHP Compiler by Jaivansh</h1>
        <p>Professional PHP Development Environment</p>
        <div class="version-badge">PHP <?php echo $phpVersion; ?></div>
    </div>

    <div class="main-grid">
        <div class="card">
            <div class="card-header">📝 Code Editor</div>
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
            <div class="card-header">📤 Output &amp; Results</div>
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

<?php /* ============================================================
   PAGE: ABOUT
   ============================================================ */ ?>
<?php elseif ($page === 'about'): ?>

<div class="page-wrap">
    <div class="page-hero">
        <div class="page-badge">About PHPForge</div>
        <h1>Built for Developers,<br>By a Developer</h1>
        <p>PHPForge is a free, fast, browser-based PHP compiler. No installs, no configuration — just write PHP and run it instantly.</p>
    </div>

    <div class="info-card">
        <h2><span class="ico">💡</span> Our Mission</h2>
        <p>PHPForge was created with one goal: make PHP development accessible to everyone. Whether you're a student learning PHP for the first time, a developer prototyping a quick snippet, or a teacher demonstrating concepts in class — PHPForge gives you a full PHP execution environment right in your browser, completely free.</p>
    </div>

    <div class="info-card">
        <h2><span class="ico">🚀</span> What PHPForge Can Do</h2>
        <div class="features-grid">
            <div class="feat-item">
                <h3>Instant Execution</h3>
                <p>Run PHP code in real-time with execution time tracking in milliseconds.</p>
            </div>
            <div class="feat-item">
                <h3>Syntax Checking</h3>
                <p>Validate your PHP syntax before running. Catch errors before they happen.</p>
            </div>
            <div class="feat-item">
                <h3>Code Snippets</h3>
                <p>Prebuilt snippets for arrays, loops, functions and more to get started fast.</p>
            </div>
            <div class="feat-item">
                <h3>Dark Editor</h3>
                <p>A developer-focused dark code editor with monospace font for comfortable coding.</p>
            </div>
        </div>
    </div>

    <div class="info-card">
        <h2><span class="ico">👨‍💻</span> About the Creator</h2>
        <p>PHPForge was designed and developed by <strong>Jaivansh</strong> — a passionate developer who believes powerful development tools should be free and accessible to everyone. The platform is continuously improved with feedback from the developer community. Have a suggestion? <a href="?page=contact" style="color:#667eea;font-weight:600;">Drop us a message.</a></p>
    </div>

    <div class="cta-card">
        <h2>Ready to Write Some PHP?</h2>
        <p>Jump into the compiler and start coding right now — no account required.</p>
        <a href="?page=compiler" class="cta-btn">Open the Compiler →</a>
    </div>
</div>

<?php /* ============================================================
   PAGE: CONTACT
   ============================================================ */ ?>
<?php elseif ($page === 'contact'): ?>

<div class="page-wrap">
    <div class="page-hero">
        <div class="page-badge">Get In Touch</div>
        <h1>Contact Us</h1>
        <p>Found a bug? Have a feature idea? Just want to say hi? We'd love to hear from you.</p>
    </div>

    <div class="info-card">
        <h2><span class="ico">✉️</span> Send a Message</h2>

        <?php if ($contactSuccess): ?>
            <div class="form-success">✅ Message sent successfully! We'll get back to you soon.</div>
        <?php elseif ($contactError): ?>
            <div class="form-error">⚠️ <?php echo $contactError; ?></div>
        <?php endif; ?>

        <form method="POST" action="?page=contact">
            <input type="hidden" name="contact_form" value="1">
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Your Name</label>
                    <input type="text" id="name" name="name" placeholder="Jaivansh" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="you@example.com" required>
                </div>
            </div>
            <div class="form-group">
                <label for="subject">Subject</label>
                <select id="subject" name="subject" required>
                    <option value="">Select a topic...</option>
                    <option value="Bug Report">🐛 Bug Report</option>
                    <option value="Feature Request">💡 Feature Request</option>
                    <option value="General Question">❓ General Question</option>
                    <option value="Other">📬 Other</option>
                </select>
            </div>
            <div class="form-group">
                <label for="message">Message</label>
                <textarea class="msg" id="message" name="message" placeholder="Describe your issue or idea in detail..." required></textarea>
            </div>
            <button type="submit" class="submit-btn">Send Message →</button>
        </form>
    </div>

    <div class="info-card">
        <h2><span class="ico">📋</span> Response Times</h2>
        <p>We typically respond within <strong>24–48 hours</strong> on weekdays. For urgent bugs affecting the compiler, please include your PHP code snippet and the error output to help us reproduce the issue faster.</p>
    </div>
</div>

<?php /* ============================================================
   PAGE: PRIVACY
   ============================================================ */ ?>
<?php elseif ($page === 'privacy'): ?>

<div class="page-wrap">
    <div class="page-hero">
        <div class="page-badge">Legal</div>
        <h1>Privacy Policy</h1>
        <p>Your privacy matters. Here's exactly how PHPForge handles your data.</p>
    </div>

    <div class="info-card">
        <span class="last-updated">📅 Last Updated: <?php echo date('F j, Y'); ?></span>
        <p>This Privacy Policy describes how PHPForge ("we", "us", or "our"), operated by Jaivansh, collects, uses, and shares information when you use our website and PHP compiler service.</p>
    </div>

    <div class="info-card">
        <h2><span class="ico">📦</span> Information We Collect</h2>
        <h3>Code You Submit</h3>
        <p>PHP code you type into the compiler is sent to our server for execution. We do <strong>not</strong> permanently store your code. It is executed in a temporary environment and immediately discarded after producing output.</p>
        <h3>Usage Data</h3>
        <p>We may collect standard server logs including your IP address, browser type, referring URL, and pages visited. This data is used solely for security monitoring and improving the service.</p>
        <h3>Contact Form Data</h3>
        <p>If you use our contact form, we collect your name, email address, and message content to respond to your inquiry. This information is not shared with third parties.</p>
    </div>

    <div class="info-card">
        <h2><span class="ico">📢</span> Google Ads &amp; Advertising</h2>
        <p>PHPForge uses <strong>Google AdSense</strong> to display advertisements. Google and its partners may use cookies to serve ads based on your prior visits to this website or other websites on the internet.</p>
        <ul>
            <li>Google's use of advertising cookies enables it and its partners to serve ads based on your visit to PHPForge and/or other sites on the Internet.</li>
            <li>You may opt out of personalized advertising by visiting <a href="https://www.google.com/settings/ads" target="_blank" rel="noopener noreferrer" style="color:#667eea;">Google Ad Settings</a>.</li>
            <li>You can also opt out via the <a href="https://www.aboutads.info/choices/" target="_blank" rel="noopener noreferrer" style="color:#667eea;">Network Advertising Initiative opt-out page</a>.</li>
        </ul>
        <p style="margin-top:12px;">Third-party vendors, including Google, use cookies to serve ads based on a user's prior visits to our website. These cookies allow Google to serve relevant ads to our users across the internet.</p>
    </div>

    <div class="info-card">
        <h2><span class="ico">🍪</span> Cookies</h2>
        <p>PHPForge and third-party partners (such as Google AdSense) use cookies to:</p>
        <ul>
            <li>Remember your preferences and settings</li>
            <li>Analyze site traffic and usage patterns</li>
            <li>Serve relevant advertisements</li>
            <li>Prevent abuse and ensure security</li>
        </ul>
        <p style="margin-top:12px;">You can control or disable cookies through your browser settings. Note that disabling cookies may affect the functionality of the site and ads you see.</p>
    </div>

    <div class="info-card">
        <h2><span class="ico">🔒</span> Data Security</h2>
        <p>We take reasonable measures to protect your information from unauthorized access, alteration, or destruction. Code submitted to our compiler is executed in an isolated temporary environment and is never stored on our servers after execution completes.</p>
    </div>

    <div class="info-card">
        <h2><span class="ico">🔗</span> Third-Party Links</h2>
        <p>Our website may contain links to external sites. We are not responsible for the privacy practices of those sites and encourage you to review their privacy policies before providing any personal information.</p>
    </div>

    <div class="info-card">
        <h2><span class="ico">👶</span> Children's Privacy</h2>
        <p>PHPForge is not directed at children under the age of 13. We do not knowingly collect personal information from children under 13. If you believe we have inadvertently collected such information, please contact us immediately.</p>
    </div>

    <div class="info-card">
        <h2><span class="ico">✏️</span> Changes to This Policy</h2>
        <p>We may update this Privacy Policy from time to time. Any changes will be reflected on this page with an updated "Last Updated" date. We encourage you to review this policy periodically.</p>
    </div>

    <div class="info-card">
        <h2><span class="ico">📬</span> Contact Us</h2>
        <p>If you have any questions about this Privacy Policy, please <a href="?page=contact" style="color:#667eea;font-weight:600;">contact us here</a>. We'll respond as promptly as possible.</p>
    </div>
</div>

<?php endif; ?>

<!-- ===== FOOTER ===== -->
<div class="page-footer" style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;">
    <p>&copy; <?php echo date('Y'); ?> PHPForge by Jaivansh &nbsp;|&nbsp;
    <a href="?page=compiler">Compiler</a> &nbsp;·&nbsp;
    <a href="?page=about">About</a> &nbsp;·&nbsp;
    <a href="?page=contact">Contact</a> &nbsp;·&nbsp;
    <a href="?page=privacy">Privacy Policy</a></p>
</div>

<?php if ($page === 'compiler'): ?>
<script>
    const editor = document.getElementById('codeEditor');
    const outputArea = document.getElementById('outputArea');
    const statusText = document.getElementById('statusText');

    const snippets = {
        hello: '<?'+'php\necho "Hello, PHP Compiler by Jaivansh!\\n";\n$name = "Developer";\necho "Welcome, {$name}!\\n";\necho "PHP Version: " . PHP_VERSION . "\\n";\n?>',
        array: '<?'+'php\n$fruits = ["apple", "banana", "cherry", "date"];\necho "Fruits:\\n";\nforeach($fruits as $fruit) {\n    echo "  - {$fruit}\\n";\n}\n?>',
        loop:  '<?'+'php\necho "Numbers 1 to 5:\\n";\nfor($i = 1; $i <= 5; $i++) {\n    echo "  {$i}\\n";\n}\n?>',
        func:  '<?'+'php\nfunction greet($name) {\n    return "Hello, {$name}!";\n}\necho greet("Jaivansh") . "\\n";\n?>'
    };

    function showLoading() {
        outputArea.innerHTML = '<div style="text-align:center;padding:50px"><div class="loading"></div><br>Processing...</div>';
    }

    function escapeHtml(text) {
        return String(text).replace(/[&<>]/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;'}[m]));
    }

    async function checkSyntax() {
        const code = editor.value;
        if (!code.trim()) { outputArea.innerHTML = '<div class="output-error">Please write some code first</div>'; return; }
        showLoading();
        try {
            const res = await fetch('', { method:'POST', headers:{'Content-Type':'application/json','X-PHP-Forge':'1'}, body: JSON.stringify({code, action:'syntax'}) });
            const data = await res.json();
            outputArea.innerHTML = data.syntax_ok
                ? '<div class="output-success">✅ No syntax errors found! Your code is valid.</div>'
                : '<div class="output-error">❌ Syntax Error:<br>' + escapeHtml(data.error) + '</div>';
            statusText.textContent = data.syntax_ok ? 'Ready' : 'Error';
        } catch(e) { outputArea.innerHTML = '<div class="output-error">Failed: ' + escapeHtml(e.message) + '</div>'; }
    }

    async function runCode() {
        const code = editor.value;
        if (!code.trim()) { outputArea.innerHTML = '<div class="output-error">Please write some code first</div>'; return; }
        showLoading();
        try {
            const res = await fetch('', { method:'POST', headers:{'Content-Type':'application/json','X-PHP-Forge':'1'}, body: JSON.stringify({code, action:'run'}) });
            const data = await res.json();
            let html = '';
            if (data.syntax_ok) {
                html += data.output
                    ? '<div class="output-result">📤 Output:<br><br>' + escapeHtml(data.output) + '</div>'
                    : '<div class="output-success">✅ Code executed successfully (no output)!</div>';
                html += '<div style="font-size:12px;color:#666;margin-top:10px">⏱ Execution time: ' + data.time + 'ms</div>';
                statusText.textContent = 'Done';
            } else {
                html = '<div class="output-error">❌ Error:<br>' + escapeHtml(data.error) + '</div>';
                statusText.textContent = 'Error';
            }
            outputArea.innerHTML = html;
        } catch(e) { outputArea.innerHTML = '<div class="output-error">Failed: ' + escapeHtml(e.message) + '</div>'; }
    }

    function clearOutput() {
        outputArea.innerHTML = '<div class="output-empty">Click "Run Code" to see output here</div>';
        statusText.textContent = 'Ready';
    }

    document.getElementById('checkBtn').addEventListener('click', checkSyntax);
    document.getElementById('runBtn').addEventListener('click', runCode);
    document.getElementById('clearBtn').addEventListener('click', clearOutput);

    document.querySelectorAll('.snippet-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const code = snippets[btn.dataset.code];
            if (code) { editor.value = code; editor.focus(); }
        });
    });

    editor.addEventListener('keydown', e => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') { e.preventDefault(); runCode(); }
    });
</script>
<?php endif; ?>

</body>
</html>
