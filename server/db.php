<?php
include __DIR__.'/config.db.php';

function connect(){
    $con = mysqli_connect(CONST_DB_HOST,CONST_DB_USERNAME,CONST_DB_PASSWORD,CONST_DB_NAME);

    if (mysqli_connect_errno()) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    return $con;
}
function disconnect($con){
    mysqli_close($con);
}
?>