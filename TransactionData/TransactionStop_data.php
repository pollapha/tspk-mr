<?php
ini_set('post_max_size', '2024M');
ini_set('upload_max_filesize', '2024M');
ini_set('memory_limit', '2024M');
ini_set('max_execution_time', 300);

if (!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if (!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'TransactionStop'})) {
    echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
    exit();
} else if ($_SESSION['xxxRole']->{'TransactionStop'}[0] == 0) {
    echo "{ch:9,data:'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้'}";
    exit();
}

if (!isset($_REQUEST['type'])) {
    echo json_encode(array('ch' => 2, 'data' => 'ข้อมูลไม่ถูกต้อง'));
    exit();
}
$cBy = $_SESSION['xxxID'];
$fName = $_SESSION['xxxFName'];
$type  = intval($_REQUEST['type']);


include('../common/common.php');
include('../php/connection.php');


$entry_project = $_SESSION['xxxEntryProject'];

$where = [];
$exlode = explode(' | ', $entry_project);
foreach ($exlode as $Customer) {
    $sql = "SELECT 
		BIN_TO_UUID(Customer_ID,TRUE) AS Customer_ID
	FROM 
		tbl_customer_master 
	WHERE 
		Customer_Code = '$Customer';";
    $re1 = sqlError($mysqli, __LINE__, $sql, 1);
    $Customer_ID = $re1->fetch_array(MYSQLI_ASSOC)['Customer_ID'];

    $where[] = "tts.Customer_ID = uuid_to_bin('$Customer_ID',true)";
    $sqlWhere = join(' OR ', $where);
}

if ($type <= 10) //data
{
    if ($type == 1) {
        $dataParams = array(
            'obj',
            'obj=>Start_Date:s:5',
            'obj=>Stop_Date:s:5',
            'obj=>Customer_Code:s:0',
        );
        $chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
        if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

        $data = ['Start_Date' => $Start_Date, 'Stop_Date' => $Stop_Date, 'Customer_Code' => $Customer_Code, 'sqlWhere' => $sqlWhere];
        $sql = getData($mysqli, $data);
        $re1 = sqlError($mysqli, __LINE__, $sql, 1);
        closeDBT($mysqli, 1, jsonRow($re1, true, 0));
    } else if ($type == 2) {

        $dataParams = array(
            'obj',
            'obj=>Start_Date:s:5',
            'obj=>Stop_Date:s:5',
            'obj=>Customer_Code:s:0',
        );
        $chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
        if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

        $data = ['Start_Date' => $Start_Date, 'Stop_Date' => $Stop_Date, 'Customer_Code' => $Customer_Code, 'sqlWhere' => $sqlWhere];

        $mysqli->autocommit(FALSE);
        try {

            $sql = getData($mysqli, $data);
            $re = sqlError($mysqli, __LINE__, $sql, 1);
            if ($re->num_rows === 0) {
                throw new Exception('ไม่พบข้อมูล ' . __LINE__);
            }
            $dataArray = array();
            while ($row = $re->fetch_assoc()) {
                $dataArray[] = $row;
            }
            include('excel/excel_transaction_billing.php');

            $mysqli->commit();

            closeDBT($mysqli, 1, $filename);
        } catch (Exception $e) {
            $mysqli->rollback();
            closeDBT($mysqli, 2, $e->getMessage());
        }
    } else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 10 && $type <= 20) //insert
{
    if ($_SESSION['xxxRole']->{'TransactionStop'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
    if ($type == 11) {
    } else if ($type == 12) {
    } else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 20 && $type <= 30) //update
{
    if ($_SESSION['xxxRole']->{'TransactionStop'}[2] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
    if ($type == 21) {
    } else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 30 && $type <= 40) //delete
{
    if ($_SESSION['xxxRole']->{'TransactionStop'}[3] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
    if ($type == 31) {
    } else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 40 && $type <= 50) //save
{
    if ($_SESSION['xxxRole']->{'TransactionStop'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
    if ($type == 41) {
    } else closeDBT($mysqli, 2, 'TYPE ERROR');
} else closeDBT($mysqli, 2, 'TYPE ERROR');

function getData($mysqli, $data)
{
    $where = [];
    $where[] = "DATE(tts.truckNo_Date) between DATE('$data[Start_Date]') and DATE('$data[Stop_Date]')";
    $sqlWhere = join(' and ', $where);

    $sqlWhere1 = $data['sqlWhere'];
    $Customer_Code = $data['Customer_Code'];

    if ($Customer_Code != '') {
        $sql = "SELECT 
			BIN_TO_UUID(Customer_ID,TRUE) AS Customer_ID
		FROM 
			tbl_customer_master 
		WHERE 
			Customer_Code = '$Customer_Code';";
        $re1 = sqlError($mysqli, __LINE__, $sql, 1);
        $Customer_ID = $re1->fetch_array(MYSQLI_ASSOC)['Customer_ID'];

        $sqlWhere1 = "tts.Customer_ID = uuid_to_bin('$Customer_ID',true)";
    }

    $sql = "SELECT 
    tts.tran_status,
    tts.truck_Control_No,
	SUBSTRING(tts.truck_Control_No, 1, 13)as truck_Control_No_show,
    DATE_FORMAT(tts.truckNo_Date, '%d-%m-%Y') AS truckNo_Date,
    -- tts.Route_Code,
    if(trm.route_special = 'Y' OR trm.route_special = 'N', tts.Route_Code, CONCAT(tts.Route_Code,' ', trm.route_special)) as Route_Code,
    DATE_FORMAT(tts.start_Date, '%Y-%m-%d %H:%i') AS start_Date,
    DATE_FORMAT(tts.start_Date, '%H:%i') AS start_time,
    DATE_FORMAT(tts.actual_start_Date, '%Y-%m-%d') AS actual_start_Date,
    DATE_FORMAT(tts.actual_start_Date, '%H:%i') AS actual_start_Time,
    CONCAT(DATE(ttl.pus_Date),
            ' ',
            DATE_FORMAT(ttl.planin_time, '%H:%i')) AS Delivery_time,
    tts.stop_Date,
    tts.trip_Number,
    tts.total_Stop,
    tts.mile_Start,
    tts.mile_End,
    DATE_FORMAT(CONVERT(ttl.planin_time,DATE), '%d-%m-%Y') AS planin_date,
    DATE_FORMAT(ttl.planin_time, '%H:%i') AS planin_time,
    ttl.planout_time,
    tts.Truck_Number,
    tts.Truck_Type,
    tdm.Driver_Name,
    tts.tran_status,
    ttl.status,
    ttl.pus_No,
    if(ttl.Status_Pickup = 'DELIVERY','',SUBSTRING(ttl.pus_No, 1, 13)) as pus_No_show,
	-- SUBSTRING(ttl.pus_No, 1, 13)as pus_No_show,
    DATE_FORMAT(ttl.pus_Date, '%Y-%m-%d') AS pus_Date,
    tsm.Supplier_Name_Short,
    tsm.Supplier_Name,
    ttl.sequence_Stop,
    ttl.status,
    ttl.Status_Pickup,
    tstop.Refer_ID,
    torder.Part_No,
    torder.Part_Name,
    torder.PO_No,
    tstop.Plan_Qty,
    if(ttl.Status_Pickup = 'DELIVERY',tranIn.Actual_Qty,tstop.Actual_Qty) AS Actual_Qty,
    if(ttl.Status_Pickup = 'DELIVERY',tranIn.Package_Qty,tstop.Package_Qty) AS Package_Qty,
    CONVERT(if(ttl.Status_Pickup = 'DELIVERY',line_CBM,tstop.CBM), CHAR) AS CBM,
    ROUND((tpm.Mass_Per_Pallet * tstop.Package_Qty),
            2) AS WT,
    tstop.SNP_Per_Pallet,
    tpm.Project,
    tpack.Packaging,
    tpack.Package_Type,
    tdm.Driver_Name,
    tdm.Phone,
    (SELECT 
            user_fName
        FROM
            tbl_user
        WHERE
            user_id = ttl.Created_By_ID) AS Created_By_ID,
    DATE_FORMAT(ttl.Creation_DateTime, '%d-%m-%Y %H:%i:%s') AS Creation_DateTime,
    DATE_FORMAT(ttl.Creation_DateTime, '%d-%m-%Y') AS Creation_Date,
    DATE_FORMAT(ttl.Creation_DateTime, '%H:%i:%s') AS Creation_Time,
    (SELECT 
            user_fName
        FROM
            tbl_user
        WHERE
            user_id = ttl.Updated_By_ID) AS Updated_By_ID,
    DATE_FORMAT(ttl.Last_Updated_DateTime, '%d-%m-%Y %H:%i:%s') AS Last_Updated_Time,
    DATE_FORMAT(ttl.Last_Updated_DateTime, '%d-%m-%Y') AS Last_Updated_Date,
    DATE_FORMAT(ttl.Last_Updated_DateTime, '%H:%i:%s') AS Last_Updated_Time,
    t1.Customer_Code,
    trm.route_special
FROM
    tbl_transaction tts
        INNER JOIN
    tbl_customer_master t1 ON tts.Customer_ID = t1.Customer_ID
        LEFT JOIN
    tbl_transaction_line ttl ON tts.transaction_ID = ttl.transaction_ID
        LEFT JOIN
    tbl_transaction_stop tstop ON ttl.transaction_Line_ID = tstop.transaction_Line_ID
        LEFT JOIN
    tbl_order torder ON tstop.Order_ID = torder.Order_ID
        LEFT JOIN
    tbl_driver_master tdm ON tts.Driver_ID = tdm.Driver_ID
        LEFT JOIN
    tbl_supplier_master tsm ON ttl.Supplier_ID = tsm.Supplier_ID
        LEFT JOIN
    tbl_package_master tpack ON tstop.Package_ID = tpack.Package_ID
        LEFT JOIN
    tbl_part_master tpm ON tstop.Part_ID = tpm.Part_ID AND torder.Supplier_ID = tsm.Supplier_ID
        LEFT JOIN
    tbl_route_master trm ON ttl.Route_ID = trm.Route_ID,
    LATERAL
 (
   SELECT 
    tts2.truck_Control_No,
	CONVERT(SUM(tstop2.Actual_Qty),UNSIGNED) AS Actual_Qty,
    CONVERT(SUM(tstop2.Package_Qty),UNSIGNED) AS Package_Qty
FROM
    tbl_transaction tts2
        LEFT JOIN
    tbl_transaction_line ttl2 ON tts2.transaction_ID = ttl2.transaction_ID
        LEFT JOIN
    tbl_transaction_stop tstop2 ON ttl2.transaction_Line_ID = tstop2.transaction_Line_ID
WHERE tts2.truck_Control_No = tts.truck_Control_No
GROUP BY tts2.transaction_ID) 
   AS tranIn
WHERE
    $sqlWhere
    AND ttl.status != 'PENDING'
    AND ttl.status != 'CANCEL'
    AND ttl.Pick != 'N'
    AND ($sqlWhere1)
ORDER BY tts.truckNo_Date ASC, t1.Customer_Code, tts.truck_Control_No ASC, ttl.sequence_Stop ASC, ttl.pus_No, torder.PO_No, tpm.Part_No;";
    //exit($sql);
    return $sql;
}

$mysqli->close();
exit();
