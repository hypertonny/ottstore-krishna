<?php
include("./auth.php");
error_reporting(0);

if (isset($_GET['q'])) {
    $oid = $_GET['q'];
    $mid = "rubkXU18216743861182";//enter your paytm merchant id
    $url = "https://www.codertg.xyz/api/paytm/?MERCHANT_KEY={$mid}&TRANSACTION={$oid}";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    $transactions = json_decode($output);
    if($transactions->STATUS == "TXN_SUCCESS") {
        $id = $transactions->BANKTXNID;
        $amount = floatval($transactions->TXNAMOUNT);
        $result = mysqli_query($conn, "SELECT * FROM sold WHERE txn_id='$id'");
        if (mysqli_num_rows($result) == 0) {
            $cat_id = floatval(substr($oid, -2));
            $result2 = mysqli_query($conn, "SELECT * FROM categorys WHERE id='$cat_id'");
            $cat_data = mysqli_fetch_assoc($result2);            
            if ($amount >= $cat_data['amount']) {
                $total_ac = $amount / $cat_data['amount'];
                $output = mysqli_query($conn, "SELECT * FROM sell WHERE category_id='$cat_id' AND status='1' LIMIT $total_ac");
                while ($ac_data = mysqli_fetch_assoc($output)) {
                    $username = $ac_data['username'];
                    $password = $ac_data['password'];
                    mysqli_query($conn, "INSERT INTO sold (txn_id, username, password) VALUES ('$id','$username','$password')");
                    mysqli_query($conn, "UPDATE sell SET status='2' WHERE username='$username'");
                }
                $stock = $cat_data['total_stock'] - $total_ac;
                mysqli_query($conn, "UPDATE categorys SET total_stock='$stock' WHERE id='$cat_id'");
                echo json_encode(["status" => true, "id" => $id]);
            } else {
                echo json_encode(["status" => false, "id" => null]);
            }
        } else {
            echo json_encode(["status" => false, "id" => null]);
        }
    } else {
        echo json_encode(["status" => false, "id" => null]);
    }
} else {
    echo json_encode(["status" => false, "id" => null]);
}
?>
