<?php
include("auth.php");
error_reporting(E_ALL);
ini_set('display_errors', 1);

$return = []; // Initialize the return variable

if (isset($_GET['utr']) && isset($_GET['product_id'])) {
    $utr = $_GET['utr'];
    $product_id = $_GET['product_id'];

    $sql = mysqli_query($conn, "SELECT * FROM categorys WHERE id='" . mysqli_real_escape_string($conn, $product_id) . "'");
    if (mysqli_num_rows($sql) == 1) {
        $pattern = "/^[a-zA-Z0-9 ]+$/";

        if (preg_match($pattern, $utr)) {
            if (strlen($utr) > 12) {
                $return = [
                    'status' => 500,
                    'message' => 'Utr Not More Than 12 Digits'
                ];
            } else {
                if ($utr[0] === '0') {
                    $return = [
                        'status' => 500,
                        'message' => 'Invalid Utr Enter'
                    ];
                } else {
                    $sql2 = mysqli_query($conn, "SELECT * FROM sold WHERE txn_id='" . mysqli_real_escape_string($conn, $utr) . "'");
                    if (mysqli_num_rows($sql2) == 0) {
                        $product_data = mysqli_fetch_assoc($sql);
                        $token = "d8de9ffa6a9c4d4a964a85a963b7b368"; // Enter your token
                        $url = "https://payments-tesseract.bharatpe.in/api/v1/merchant/transactions?module=PAYMENT_QR&merchantId=50153894";

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, ["token: $token"]);
                        $output = curl_exec($ch);
                        curl_close($ch);

                        // Decode the JSON response
                        $response = json_decode($output);

                        if ($response === null && json_last_error() !== JSON_ERROR_NONE) {
                            // Handle JSON decode error
                            $return = [
                                'status' => 500,
                                'message' => 'Failed to parse API response: ' . json_last_error_msg(),
                                'res' => $output
                            ];
                        } elseif (!isset($response->data->transactions)) {
                            // Handle missing transactions data
                            $return = [
                                'status' => 500,
                                'message' => 'API response does not contain transactions data',
                                'res' => $output
                            ];
                        } else {
                            $transactions = $response->data->transactions;
                            $payment_found = false;

                            foreach ($transactions as $transaction) {
                                if ($transaction->bankReferenceNo == $utr) {
                                    $payment_found = true;
                                    $amount = $transaction->amount;

                                    if ($amount >= $product_data['amount']) {
                                        $total_ac = floor($amount / $product_data['amount']); // Use floor for integer value
                                        $output = mysqli_query($conn, "SELECT * FROM sell WHERE category_id='" . mysqli_real_escape_string($conn, $product_id) . "' AND status='1' LIMIT $total_ac");

                                        while ($ac_data = mysqli_fetch_assoc($output)) {
                                            $username = $ac_data['username'];
                                            $password = $ac_data['password'];

                                            mysqli_query($conn, "INSERT INTO sold (txn_id, username, password) VALUES ('" . mysqli_real_escape_string($conn, $utr) . "','" . mysqli_real_escape_string($conn, $username) . "','" . mysqli_real_escape_string($conn, $password) . "')");
                                            mysqli_query($conn, "UPDATE sell SET status='2' WHERE username='" . mysqli_real_escape_string($conn, $username) . "'");
                                        }
                                        $return = [
                                            'status' => 200,
                                            'message' => 'Payment Success',
                                            'id' => $utr
                                        ];
                                    } else {
                                        $return = [
                                            'status' => 500,
                                            'message' => 'Invalid Amount Pay'
                                        ];
                                    }
                                    break; // Break the loop if a matching transaction is found
                                }
                            }

                            if (!$payment_found) {
                                $return = [
                                    'status' => 500,
                                    'message' => 'Payment Not Found',
                                    'res' => $output
                                ];
                            }
                        }
                    } else {
                        $return = [
                            'status' => 500,
                            'message' => 'Utr Already Used'
                        ];
                    }
                }
            }
        } else {
            $return = [
                'status' => 500,
                'message' => 'Invalid Utr Format'
            ];
        }
    } else {
        $return = [
            'status' => 500,
            'message' => 'Invalid Product'
        ];
    }
} else {
    $return = [
        'status' => 500,
        'message' => 'Invalid Action'
    ];
}

echo json_encode($return);
?>
