<?php
// ============================================================
// PHPForge – PHP Compiler by Jaivansh
// Fixes: JSON output buffer, shell_exec check, error handling
// ============================================================

// ---------- API: Run / Syntax Check ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_PHP_FORGE'])) {
    error_reporting(0);
    while (ob_get_level()) ob_end_clean();
    ob_start();
    header('Content-Type: application/json');

    $input  = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? 'run';
    $code   = $input['code']   ?? '';

    if (empty(trim($code))) {
        echo json_encode(['output' => '', 'error' => 'No code provided.', 'time' => 0, 'syntax_ok' => false]);
        exit;
    }

    // Check shell_exec availability
    $disabled = array_map('trim', explode(',', ini_get('disable_functions')));
    if (in_array('shell_exec', $disabled) || !function_exists('shell_exec')) {
        echo json_encode([
            'output'    => '',
            'error'     => 'shell_exec() is disabled on this server. Please enable it in php.ini to run PHP code.',
            'time'      => 0,
            'syntax_ok' => false
        ]);
        exit;
    }

    $tmpFile = tempnam(sys_get_temp_dir(), 'phpforge_') . '.php';
    if (strpos(trim($code), '<?') !== 0) {
        $code = "<?php\n" . $code;
    }
    file_put_contents($tmpFile, $code);

    $syntaxRaw = shell_exec('php -l ' . escapeshellarg($tmpFile) . ' 2>&1');
    $syntaxOk  = ($syntaxRaw !== null && strpos($syntaxRaw, 'No syntax errors') !== false);
    $syntaxMsg = $syntaxOk ? '' : str_replace($tmpFile, 'your code', (string)$syntaxRaw);

    if ($action === 'syntax') {
        @unlink($tmpFile);
        echo json_encode(['syntax_ok' => $syntaxOk, 'error' => $syntaxMsg, 'output' => '', 'time' => 0]);
        exit;
    }

    $start  = microtime(true);
    $output = shell_exec('php ' . escapeshellarg($tmpFile) . ' 2>&1');
    $ms     = round((microtime(true) - $start) * 1000, 2);
    @unlink($tmpFile);

    if ($output === null) {
        echo json_encode([
            'output'    => '',
            'error'     => 'Code execution failed. shell_exec() may be restricted.',
            'time'      => 0,
            'syntax_ok' => false
        ]);
        exit;
    }

    echo json_encode([
        'output'    => $output,
        'error'     => $syntaxMsg,
        'time'      => $ms,
        'syntax_ok' => $syntaxOk
    ]);
    exit;
}

// ---------- Contact Form ----------
$contactSuccess = false;
$contactError   = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_form'])) {
    $name    = htmlspecialchars(trim($_POST['name']    ?? ''));
    $email   = htmlspecialchars(trim($_POST['email']   ?? ''));
    $subject = htmlspecialchars(trim($_POST['subject'] ?? ''));
    $message = htmlspecialchars(trim($_POST['message'] ?? ''));

    if ($name && $email && $subject && $message && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $to      = 'jaivansh162@gmail.com';
        $headers = "From: $email\r\nReply-To: $email\r\n";
        mail($to, "[PHPForge] $subject", "Name: $name\nEmail: $email\n\n$message", $headers);
        $contactSuccess = true;
    } else {
        $contactError = 'Please fill in all fields with a valid email.';
    }
}

// ---------- Page Routing ----------
$phpVersion = phpversion();
$page       = $_GET['page'] ?? 'compiler';
if (!in_array($page, ['compiler', 'about', 'contact', 'privacy'])) $page = 'compiler';

$meta = [
    'compiler' => [
        'title'     => 'PHPForge – Free Online PHP Compiler & IDE by Jaivansh',
        'desc'      => 'Run PHP code instantly in your browser. Free online PHP compiler with syntax checking, code snippets, and real-time output. No setup required.',
        'canonical' => '?page=compiler'
    ],
    'about' => [
        'title'     => 'About PHPForge – PHP Compiler by Jaivansh',
        'desc'      => 'Learn about PHPForge – a free browser-based PHP compiler and IDE built by Jaivansh.',
        'canonical' => '?page=about'
    ],
    'contact' => [
        'title'     => 'Contact Us – PHPForge | PHP Compiler by Jaivansh',
        'desc'      => 'Get in touch with the PHPForge team. Report bugs, suggest features, or ask questions.',
        'canonical' => '?page=contact'
    ],
    'privacy' => [
        'title'     => 'Privacy Policy – PHPForge | PHP Compiler by Jaivansh',
        'desc'      => 'Read the PHPForge privacy policy. Understand how we handle your data and cookies.',
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
<meta name="description"  content="<?php echo $m['desc']; ?>">
<meta name="keywords"     content="PHP compiler, online PHP IDE, run PHP online, PHP sandbox, Jaivansh, PHPForge">
<meta name="robots"       content="index, follow">
<meta name="author"       content="Jaivansh">
<link rel="canonical"     href="<?php echo $m['canonical']; ?>">
<meta property="og:title"       content="<?php echo $m['title']; ?>">
<meta property="og:description" content="<?php echo $m['desc']; ?>">
<meta property="og:type"        content="website">
<meta name="google-adsense-account" content="ca-pub-7390574521339742">
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7390574521339742" crossorigin="anonymous"></script>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebApplication",
  "name": "PHPForge",
  "description": "Free browser-based PHP compiler and IDE by Jaivansh",
  "applicationCategory": "DeveloperApplication",
  "operatingSystem": "Web",
  "author": { "@type": "Person", "name": "Jaivansh" }
}
</script>

<style>
/* ===== RESET ===== */
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

/* ===== BASE ===== */
:root {
    --primary:   #667eea;
    --secondary: #764ba2;
    --grad:      linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --dark:      #1e1e2e;
    --text:      #374151;
    --muted:     #6b7280;
    --border:    #e5e7eb;
    --bg-light:  #f9fafb;
    --white:     #ffffff;
    --success-bg:#d1fae5;
    --success-bd:#10b981;
    --success-tx:#065f46;
    --error-bg:  #fee2e2;
    --error-bd:  #ef4444;
    --error-tx:  #991b1b;
    --radius:    12px;
    --shadow:    0 10px 30px rgba(0,0,0,0.1);
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    background: var(--grad);
    min-height: 100vh;
    color: var(--text);
}

/* ===== NAV ===== */
nav {
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    padding: 13px 28px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 999;
    border-bottom: 1px solid rgba(255,255,255,0.18);
}

.nav-brand {
    color: #fff;
    font-weight: 800;
    font-size: 19px;
    text-decoration: none;
    letter-spacing: -.4px;
    display: flex;
    align-items: center;
    gap: 7px;
}

.nav-links { display: flex; gap: 4px; }

.nav-links a {
    color: rgba(255,255,255,.82);
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    padding: 7px 14px;
    border-radius: 20px;
    transition: background .2s, color .2s;
}

.nav-links a:hover  { color: #fff; background: rgba(255,255,255,.15); }
.nav-links a.active { color: #fff; background: rgba(255,255,255,.22); }

/* ===== COMPILER PAGE ===== */
.compiler-wrap { padding: 20px; }

.page-header {
    background: rgba(255,255,255,.97);
    border-radius: var(--radius);
    padding: 20px;
    margin: 0 auto 20px;
    text-align: center;
    box-shadow: var(--shadow);
    max-width: 1400px;
}

.page-header h1 {
    font-size: 26px;
    font-weight: 800;
    background: var(--grad);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 4px;
}

.page-header p { color: var(--muted); font-size: 13px; }

.version-badge {
    display: inline-block;
    background: var(--grad);
    color: #fff;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    margin-top: 8px;
}

.main-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    max-width: 1400px;
    margin: 0 auto;
}

.card {
    background: var(--white);
    border-radius: var(--radius);
    overflow: hidden;
    box-shadow: var(--shadow);
    display: flex;
    flex-direction: column;
}

.card-header {
    background: var(--grad);
    color: #fff;
    padding: 14px 20px;
    font-weight: 700;
    font-size: 14px;
    letter-spacing: .2px;
}

#codeEditor {
    width: 100%;
    min-height: 480px;
    padding: 20px;
    font-family: 'Courier New', 'Consolas', monospace;
    font-size: 14px;
    line-height: 1.65;
    border: none;
    background: var(--dark);
    color: #cdd6f4;
    resize: vertical;
    flex: 1;
}
#codeEditor:focus { outline: none; }

.output-area {
    min-height: 480px;
    padding: 20px;
    background: var(--bg-light);
    overflow-y: auto;
    font-family: 'Courier New', 'Consolas', monospace;
    font-size: 13.5px;
    line-height: 1.65;
    white-space: pre-wrap;
    word-break: break-word;
    flex: 1;
}

.output-placeholder { color: #aaa; text-align: center; margin-top: 180px; font-size: 14px; }
.output-placeholder span { display: block; font-size: 32px; margin-bottom: 8px; }

.out-success {
    background: var(--success-bg);
    border-left: 4px solid var(--success-bd);
    padding: 13px 16px;
    border-radius: 8px;
    margin-bottom: 12px;
    color: var(--success-tx);
    font-weight: 600;
}

.out-error {
    background: var(--error-bg);
    border-left: 4px solid var(--error-bd);
    padding: 13px 16px;
    border-radius: 8px;
    margin-bottom: 12px;
    color: var(--error-tx);
    font-family: 'Courier New', monospace;
    font-size: 13px;
    white-space: pre-wrap;
}

.out-result {
    background: var(--dark);
    color: #cdd6f4;
    padding: 14px 16px;
    border-radius: 8px;
    margin-bottom: 12px;
    overflow-x: auto;
}

.out-meta {
    font-size: 12px;
    color: var(--muted);
    margin-top: 8px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

/* Snippets & Buttons */
.snippets {
    padding: 10px 16px;
    background: var(--bg-light);
    border-top: 1px solid var(--border);
    display: flex;
    gap: 7px;
    flex-wrap: wrap;
}

.snippet-btn {
    padding: 5px 13px;
    background: #e5e7eb;
    border: none;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: background .2s, color .2s, transform .15s;
}
.snippet-btn:hover { background: var(--primary); color: #fff; transform: translateY(-1px); }

.buttons {
    padding: 14px 16px;
    background: var(--bg-light);
    border-top: 1px solid var(--border);
    display: flex;
    gap: 9px;
    flex-wrap: wrap;
}

.btn {
    padding: 9px 20px;
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: transform .2s, box-shadow .2s, background .2s;
}
.btn:active { transform: scale(.97); }
.btn-primary  { background: var(--grad); color: #fff; }
.btn-primary:hover  { transform: translateY(-2px); box-shadow: 0 5px 18px rgba(102,126,234,.4); }
.btn-success  { background: #10b981; color: #fff; }
.btn-success:hover  { background: #059669; transform: translateY(-2px); }
.btn-neutral  { background: #6b7280; color: #fff; }
.btn-neutral:hover  { background: #4b5563; }

/* Status bar */
.status-bar {
    max-width: 1400px;
    margin: 16px auto 0;
    background: rgba(255,255,255,.94);
    border-radius: 10px;
    padding: 9px 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
    color: var(--muted);
    flex-wrap: wrap;
    gap: 8px;
}

.status-dot {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #10b981;
    margin-right: 6px;
    vertical-align: middle;
}

/* Spinner */
.spinner {
    display: inline-block;
    width: 18px;
    height: 18px;
    border: 3px solid #e5e7eb;
    border-top-color: var(--primary);
    border-radius: 50%;
    animation: spin .8s linear infinite;
    vertical-align: middle;
    margin-right: 8px;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ===== INNER PAGES ===== */
.page-wrap {
    max-width: 860px;
    margin: 0 auto;
    padding: 48px 20px 80px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

.hero-block {
    background: rgba(255,255,255,.97);
    border-radius: 20px;
    padding: 44px 48px;
    margin-bottom: 20px;
    box-shadow: 0 20px 60px rgba(0,0,0,.12);
    text-align: center;
}

.badge {
    display: inline-block;
    background: var(--grad);
    color: #fff;
    padding: 4px 15px;
    border-radius: 30px;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    margin-bottom: 16px;
}

.hero-block h1 {
    font-size: 34px;
    font-weight: 800;
    background: var(--grad);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 12px;
    line-height: 1.22;
}

.hero-block p { font-size: 16px; color: #666; line-height: 1.7; }

.info-card {
    background: rgba(255,255,255,.97);
    border-radius: 14px;
    padding: 32px 38px;
    margin-bottom: 18px;
    box-shadow: 0 8px 28px rgba(0,0,0,.08);
}

.info-card h2 {
    font-size: 18px;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.ico {
    width: 32px;
    height: 32px;
    background: var(--grad);
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    flex-shrink: 0;
}

.info-card p, .info-card li {
    color: #555;
    line-height: 1.8;
    font-size: 14.5px;
}
.info-card ul { padding-left: 20px; margin-top: 8px; }
.info-card li { margin-bottom: 5px; }
.info-card h3 { font-size: 15px; color: var(--dark); margin: 16px 0 6px; font-weight: 600; }

/* Features grid */
.feat-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 13px;
    margin-top: 14px;
}

.feat-item {
    background: linear-gradient(135deg, #f0f0ff, #f8f0ff);
    border-radius: 10px;
    padding: 16px;
    border-left: 3px solid var(--primary);
}
.feat-item h3 { font-size: 12.5px; font-weight: 700; color: var(--primary); margin-bottom: 5px; }
.feat-item p  { font-size: 12.5px; color: #666; line-height: 1.5; }

/* Contact form */
.form-group { margin-bottom: 16px; }

label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    color: #444;
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: .6px;
}

input[type="text"],
input[type="email"],
select,
textarea.msg {
    width: 100%;
    padding: 10px 14px;
    border: 2px solid var(--border);
    border-radius: 9px;
    font-size: 14px;
    font-family: inherit;
    color: var(--dark);
    background: var(--bg-light);
    transition: border-color .2s, box-shadow .2s;
    outline: none;
    appearance: none;
}

input:focus, select:focus, textarea.msg:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(102,126,234,.12);
    background: #fff;
}

textarea.msg { min-height: 130px; resize: vertical; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

.submit-btn {
    width: 100%;
    padding: 12px;
    background: var(--grad);
    color: #fff;
    border: none;
    border-radius: 9px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: transform .2s, box-shadow .2s;
}
.submit-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(102,126,234,.35); }

.form-success {
    background: var(--success-bg);
    border-left: 4px solid var(--success-bd);
    border-radius: 9px;
    padding: 13px 16px;
    color: var(--success-tx);
    font-size: 13.5px;
    font-weight: 600;
    margin-bottom: 16px;
}

.form-error-msg {
    background: var(--error-bg);
    border-left: 4px solid var(--error-bd);
    border-radius: 9px;
    padding: 13px 16px;
    color: var(--error-tx);
    font-size: 13.5px;
    margin-bottom: 16px;
}

/* CTA */
.cta-card {
    background: var(--grad);
    border-radius: 14px;
    padding: 34px 38px;
    text-align: center;
    color: #fff;
    margin-bottom: 18px;
}
.cta-card h2 { font-size: 21px; font-weight: 800; margin-bottom: 8px; }
.cta-card p  { opacity: .9; margin-bottom: 18px; font-size: 14.5px; }
.cta-btn {
    display: inline-block;
    background: #fff;
    color: var(--secondary);
    padding: 10px 26px;
    border-radius: 30px;
    text-decoration: none;
    font-weight: 700;
    font-size: 13.5px;
    transition: transform .2s, box-shadow .2s;
}
.cta-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,.2); }

/* Privacy */
.last-updated {
    display: inline-block;
    background: #f0f0ff;
    border-radius: 7px;
    padding: 8px 14px;
    font-size: 12.5px;
    color: var(--primary);
    font-weight: 600;
    margin-bottom: 18px;
}

/* Footer */
.site-footer {
    text-align: center;
    color: rgba(255,255,255,.7);
    font-size: 12.5px;
    padding: 20px 16px 32px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}
.site-footer a { color: rgba(255,255,255,.9); text-decoration: none; }
.site-footer a:hover { text-decoration: underline; }
.site-footer span { margin: 0 5px; opacity: .4; }

/* ===== RESPONSIVE ===== */
@media (max-width: 820px) {
    .main-grid { grid-template-columns: 1fr; }
    #codeEditor, .output-area { min-height: 300px; }
    .page-header h1 { font-size: 20px; }
    nav { padding: 11px 14px; }
    .nav-brand { font-size: 15px; }
    .nav-links a { font-size: 11px; padding: 6px 10px; }
    .hero-block { padding: 28px 20px; }
    .hero-block h1 { font-size: 24px; }
    .info-card { padding: 24px 20px; }
    .feat-grid { grid-template-columns: 1fr; }
    .form-row { grid-template-columns: 1fr; }
    .cta-card { padding: 26px 20px; }
    .page-wrap { padding: 28px 14px 60px; }
}
</style>
</head>
<body>

<!-- NAV -->
<nav>
    <a href="?page=compiler" class="nav-brand">⚙ PHPForge</a>
    <div class="nav-links">
        <a href="?page=compiler" <?php if($page==='compiler') echo 'class="active"'; ?>>Compiler</a>
        <a href="?page=about"   <?php if($page==='about')    echo 'class="active"'; ?>>About</a>
        <a href="?page=contact" <?php if($page==='contact')  echo 'class="active"'; ?>>Contact</a>
        <a href="?page=privacy" <?php if($page==='privacy')  echo 'class="active"'; ?>>Privacy</a>
    </div>
</nav>

<?php /* ===== COMPILER PAGE ===== */ ?>
<?php if ($page === 'compiler'): ?>

<div class="compiler-wrap">
    <div class="page-header">
        <h1>PHP Compiler by Jaivansh</h1>
        <p>Professional PHP Development Environment — Write, check &amp; run PHP instantly</p>
        <div class="version-badge">PHP <?php echo $phpVersion; ?></div>
    </div>

    <div class="main-grid">
        <!-- Editor -->
        <div class="card">
            <div class="card-header">📝 Code Editor</div>
            <textarea id="codeEditor"><?php
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
                <button class="btn btn-primary" id="checkBtn">✔ Check Syntax</button>
                <button class="btn btn-success" id="runBtn">▶ Run Code</button>
                <button class="btn btn-neutral" id="clearBtn">✕ Clear Output</button>
            </div>
        </div>

        <!-- Output -->
        <div class="card">
            <div class="card-header">📤 Output &amp; Results</div>
            <div class="output-area" id="outputArea">
                <div class="output-placeholder">
                    <span>💻</span>
                    Click <strong>Run Code</strong> to see output here
                </div>
            </div>
        </div>
    </div>

    <div class="status-bar">
        <div><span class="status-dot" id="statusDot"></span>Status: <strong id="statusText">Ready</strong></div>
        <div>PHPForge by Jaivansh &nbsp;·&nbsp; Ctrl+Enter to run</div>
    </div>
</div>

<?php /* ===== ABOUT PAGE ===== */ ?>
<?php elseif ($page === 'about'): ?>

<div class="page-wrap">
    <div class="hero-block">
        <div class="badge">About PHPForge</div>
        <h1>Built for Developers,<br>By a Developer</h1>
        <p>PHPForge is a free, fast, browser-based PHP compiler. No installs, no configuration — just write PHP and run it instantly.</p>
    </div>

    <div class="info-card">
        <h2><span class="ico">💡</span> Our Mission</h2>
        <p>PHPForge was created with one goal: make PHP development accessible to everyone. Whether you're a student learning PHP for the first time, a developer prototyping a quick snippet, or a teacher demonstrating concepts in class — PHPForge gives you a full PHP execution environment right in your browser, completely free.</p>
    </div>

    <div class="info-card">
        <h2><span class="ico">🚀</span> What PHPForge Can Do</h2>
        <div class="feat-grid">
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
                <p>Developer-focused dark code editor with monospace font for comfortable coding.</p>
            </div>
        </div>
    </div>

    <div class="info-card">
        <h2><span class="ico">👨‍💻</span> About the Creator</h2>
        <p>PHPForge was designed and developed by <strong>Jaivansh</strong> — a passionate developer who believes powerful development tools should be free and accessible to everyone. The platform is continuously improved with community feedback. Have a suggestion? <a href="?page=contact" style="color:var(--primary);font-weight:600;">Drop us a message.</a></p>
    </div>

    <div class="cta-card">
        <h2>Ready to Write Some PHP?</h2>
        <p>Jump into the compiler and start coding right now — no account required.</p>
        <a href="?page=compiler" class="cta-btn">Open the Compiler →</a>
    </div>
</div>

<?php /* ===== CONTACT PAGE ===== */ ?>
<?php elseif ($page === 'contact'): ?>

<div class="page-wrap">
    <div class="hero-block">
        <div class="badge">Get In Touch</div>
        <h1>Contact Us</h1>
        <p>Found a bug? Have a feature idea? Just want to say hi? We'd love to hear from you.</p>
    </div>

    <div class="info-card">
        <h2><span class="ico">✉️</span> Send a Message</h2>

        <?php if ($contactSuccess): ?>
            <div class="form-success">✅ Message sent successfully! We'll get back to you soon.</div>
        <?php elseif ($contactError): ?>
            <div class="form-error-msg">⚠️ <?php echo $contactError; ?></div>
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
                    <option value="">Select a topic…</option>
                    <option value="Bug Report">🐛 Bug Report</option>
                    <option value="Feature Request">💡 Feature Request</option>
                    <option value="General Question">❓ General Question</option>
                    <option value="Other">📬 Other</option>
                </select>
            </div>
            <div class="form-group">
                <label for="message">Message</label>
                <textarea class="msg" id="message" name="message" placeholder="Describe your issue or idea in detail…" required></textarea>
            </div>
            <button type="submit" class="submit-btn">Send Message →</button>
        </form>
    </div>

    <div class="info-card">
        <h2><span class="ico">📋</span> Response Times</h2>
        <p>We typically respond within <strong>24–48 hours</strong> on weekdays. For urgent bugs affecting the compiler, please include your PHP code snippet and the error output to help us reproduce the issue faster.</p>
    </div>
</div>

<?php /* ===== PRIVACY PAGE ===== */ ?>
<?php elseif ($page === 'privacy'): ?>

<div class="page-wrap">
    <div class="hero-block">
        <div class="badge">Legal</div>
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
        <p>PHPForge uses <strong>Google AdSense</strong> to display advertisements. Google and its partners may use cookies to serve ads based on your prior visits to this or other websites.</p>
        <ul>
            <li>Google's advertising cookies enable it and partners to serve ads based on your visit history.</li>
            <li>You may opt out via <a href="https://www.google.com/settings/ads" target="_blank" rel="noopener noreferrer" style="color:var(--primary);">Google Ad Settings</a>.</li>
            <li>You can also opt out via the <a href="https://www.aboutads.info/choices/" target="_blank" rel="noopener noreferrer" style="color:var(--primary);">Network Advertising Initiative opt-out page</a>.</li>
        </ul>
    </div>

    <div class="info-card">
        <h2><span class="ico">🍪</span> Cookies</h2>
        <p>PHPForge and third-party partners use cookies to:</p>
        <ul>
            <li>Remember your preferences and settings</li>
            <li>Analyze site traffic and usage patterns</li>
            <li>Serve relevant advertisements</li>
            <li>Prevent abuse and ensure security</li>
        </ul>
        <p style="margin-top:10px;">You can control or disable cookies through your browser settings. Disabling cookies may affect site functionality.</p>
    </div>

    <div class="info-card">
        <h2><span class="ico">🔒</span> Data Security</h2>
        <p>We take reasonable measures to protect your information from unauthorized access. Code submitted to our compiler is executed in an isolated temporary environment and is never stored after execution completes.</p>
    </div>

    <div class="info-card">
        <h2><span class="ico">🔗</span> Third-Party Links</h2>
        <p>Our website may contain links to external sites. We are not responsible for their privacy practices and encourage you to review their policies before providing personal information.</p>
    </div>

    <div class="info-card">
        <h2><span class="ico">👶</span> Children's Privacy</h2>
        <p>PHPForge is not directed at children under 13. We do not knowingly collect personal information from children. If you believe we have inadvertently done so, please contact us immediately.</p>
    </div>

    <div class="info-card">
        <h2><span class="ico">✏️</span> Changes to This Policy</h2>
        <p>We may update this Privacy Policy from time to time. Changes will be reflected on this page with an updated date. We encourage you to review this policy periodically.</p>
    </div>

    <div class="info-card">
        <h2><span class="ico">📬</span> Contact Us</h2>
        <p>Questions about this policy? <a href="?page=contact" style="color:var(--primary);font-weight:600;">Contact us here</a> and we'll respond as promptly as possible.</p>
    </div>
</div>

<?php endif; ?>

<!-- FOOTER -->
<footer class="site-footer">
    <p>
        &copy; <?php echo date('Y'); ?> PHPForge by Jaivansh
        <span>|</span>
        <a href="?page=compiler">Compiler</a>
        <span>·</span>
        <a href="?page=about">About</a>
        <span>·</span>
        <a href="?page=contact">Contact</a>
        <span>·</span>
        <a href="?page=privacy">Privacy Policy</a>
    </p>
</footer>

<?php if ($page === 'compiler'): ?>
<script>
(function () {
    'use strict';

    const editor     = document.getElementById('codeEditor');
    const outputArea = document.getElementById('outputArea');
    const statusText = document.getElementById('statusText');
    const statusDot  = document.getElementById('statusDot');

    // Code snippets
    const PHP_O = '<?php';
    const PHP_C = '?>';
    const SNIPPETS = {
        hello: PHP_O + '\necho "Hello, PHP Compiler by Jaivansh!\\n";\necho "Welcome, Developer!\\n";\necho "PHP Version: " . PHP_VERSION . "\\n";\n' + PHP_C,
        array: PHP_O + '\n$fruits = ["apple", "banana", "cherry", "date"];\necho "Fruits:\\n";\nforeach ($fruits as $fruit) {\n    echo "  - " . $fruit . "\\n";\n}\n' + PHP_C,
        loop:  PHP_O + '\necho "Numbers 1 to 5:\\n";\nfor ($i = 1; $i <= 5; $i++) {\n    echo "  " . $i . "\\n";\n}\n' + PHP_C,
        func:  PHP_O + '\nfunction greet($name) {\n    return "Hello, " . $name . "!";\n}\necho greet("Jaivansh") . "\\n";\n' + PHP_C
    };

    function escHtml(str) {
        return String(str).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    function setStatus(text, color) {
        statusText.textContent = text;
        statusDot.style.background = color || '#10b981';
    }

    function showLoading() {
        outputArea.innerHTML = '<div style="text-align:center;padding:60px 20px;color:#888"><div class="spinner"></div>Processing…</div>';
        setStatus('Running…', '#f59e0b');
    }

    async function callAPI(action) {
        const code = editor.value.trim();
        if (!code) {
            outputArea.innerHTML = '<div class="out-error">⚠️ Please write some code first.</div>';
            return null;
        }

        showLoading();

        let raw;
        try {
            const res = await fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-PHP-Forge': '1' },
                body: JSON.stringify({ code, action })
            });
            raw = await res.text();
        } catch (networkErr) {
            outputArea.innerHTML = '<div class="out-error">❌ Network error: ' + escHtml(networkErr.message) + '</div>';
            setStatus('Error', '#ef4444');
            return null;
        }

        // Try to parse JSON; if it fails show the raw response (likely a PHP error page)
        try {
            return JSON.parse(raw);
        } catch (_) {
            outputArea.innerHTML =
                '<div class="out-error">❌ Server returned an unexpected response (not JSON).<br><br>'
                + 'This usually means <code>shell_exec()</code> is disabled on your server, '
                + 'or PHP has a fatal startup error.<br><br>'
                + '<strong>Server response preview:</strong><br>'
                + escHtml(raw.substring(0, 400))
                + '</div>';
            setStatus('Error', '#ef4444');
            return null;
        }
    }

    async function checkSyntax() {
        const data = await callAPI('syntax');
        if (!data) return;

        if (data.syntax_ok) {
            outputArea.innerHTML = '<div class="out-success">✅ No syntax errors found! Your code is valid PHP.</div>';
            setStatus('Ready', '#10b981');
        } else {
            outputArea.innerHTML = '<div class="out-error">❌ Syntax Error:<br><br>' + escHtml(data.error) + '</div>';
            setStatus('Syntax Error', '#ef4444');
        }
    }

    async function runCode() {
        const data = await callAPI('run');
        if (!data) return;

        let html = '';

        if (!data.syntax_ok) {
            html = '<div class="out-error">❌ Syntax Error:<br><br>' + escHtml(data.error) + '</div>';
            setStatus('Error', '#ef4444');
        } else if (data.error) {
            html = '<div class="out-error">⚠️ Runtime Warning / Error:<br><br>' + escHtml(data.error) + '</div>';
            if (data.output) html += '<div class="out-result">' + escHtml(data.output) + '</div>';
            setStatus('Warning', '#f59e0b');
        } else {
            if (data.output) {
                html += '<div class="out-result">📤 Output:\n\n' + escHtml(data.output) + '</div>';
            } else {
                html += '<div class="out-success">✅ Code executed successfully with no output.</div>';
            }
            setStatus('Done ✓', '#10b981');
        }

        if (data.time !== undefined) {
            html += '<div class="out-meta">⏱ Execution time: ' + data.time + ' ms</div>';
        }

        outputArea.innerHTML = html;
    }

    function clearOutput() {
        outputArea.innerHTML = '<div class="output-placeholder"><span>💻</span>Click <strong>Run Code</strong> to see output here</div>';
        setStatus('Ready', '#10b981');
    }

    // Event listeners
    document.getElementById('checkBtn').addEventListener('click', checkSyntax);
    document.getElementById('runBtn').addEventListener('click', runCode);
    document.getElementById('clearBtn').addEventListener('click', clearOutput);

    document.querySelectorAll('.snippet-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const code = SNIPPETS[btn.dataset.code];
            if (code) { editor.value = code; editor.focus(); }
        });
    });

    // Ctrl+Enter / Cmd+Enter to run
    editor.addEventListener('keydown', e => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            e.preventDefault();
            runCode();
        }
    });
})();
</script>
<?php endif; ?>

</body>
</html>