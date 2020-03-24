<?php

$link = file_get_contents("https://api.kawalcorona.com/indonesia/provinsi?fbclid=IwAR2gfnx-6RwdKhNLpH5hM5t3X7I8jd0op8l7sdNuKtSncpa8OwhPXeMuuMc");
$row = json_decode($link, true);

for ($i = 0; $i < count($row); $i++) {
    echo $row[$i]['attributes']['Kasus_Posi'];
    echo "<br>";
}
?>