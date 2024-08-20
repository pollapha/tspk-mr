<?php
include('../../php/connection.php');

$sql  = "DELETE FROM 
    tbl_label_file
LIMIT 1;";
sqlError($mysqli, __LINE__, $sql, 1);
if ($mysqli->affected_rows == 0) {
    // throw new Exception('ไม่สามารถลบข้อมูลได้' . __LINE__);
    echo ('cannot be deleted');
    exit();
} else {
    echo ('path delete');
}
