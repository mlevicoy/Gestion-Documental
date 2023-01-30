<?php

//Leemos el archivo
$file = fopen("informes.txt", "r") or exit("No funciona la wa");
while(!feof($file)){
    echo fgets($file)."<br>";
}
fclose($file);

?>