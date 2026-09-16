<?php
include ("./auth.php");
$account = $_GET['account'];
$id = $_GET['service'];
if ($account <= 0) {
  echo "minimum account buy requirements is 1";
  return;
}
$sql1 = $conn->query("SELECT * FROM categorys WHERE id='$id'");
if ($sql1->num_rows <= 0) {
  header('location: index.php');
  return;
}
$sql2 = $conn->query("SELECT * FROM sell where category_id='$id' and status='1'");
if ($sql2->num_rows < $account) {
  echo "Stock Not Available ";
  return;
}
$data2 = $sql1->fetch_assoc();

$amount = $data2['amount'] * $account;
$service = $_POST['service'];
$server = (strlen($service) == 2) ? $service : '0' . $service;
$upi_id = PAYTM_UPI_ID;
function generateRandomString($length = 10)
{
  $result = '';
  for ($i = 0; $i < $length; $i++) {
    $result .= rand(0, 9);
  }
  return $result;
}
$oid = "OID" . generateRandomString() . $server;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

  <title>QR Payment</title>
  <style>
    body {
      padding: 20px;
    }

    .pending {
      color: yellow;
    }

    .success {
      color: green;
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow">
          <div class="card-body">
            <h5 class="card-title text-center" style="font-weight: bolder; color: blue;">Scan & Pay</h5>
            <img class="w-100 img-thumbnail"
              src="<?php echo $img_url; ?>"
              alt="Card image cap">
            <p class="mt-3 mb-0" style="font-size: 25px; text-align: center; font-weight: bolder; color: red;">Amount :
              <?php echo $amount; ?> INR</p>
              <input type="hidden" id="product_id" value="<?php echo $id; ?>">
            <!-- <center><input type="number" style="text-align:center; margin-bottom:5px" class='name="utr" placeholder="Enter Your Utr" > </center> -->
            <div class="mb-3">
              <input type="number" class="form-control" style="text-align:center" id="utr" placeholder="Enter Utr">
            </div>
            <center><button type="button" id="buy" class="btn btn-primary">Verify</button></center>
            <center><a type="submit" style="display:none" id="myLink" class="btn btn-primary  mb-2 ">DOWNLOAD NOW</a></center>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script>
    $(document).ready(function () {
      $("#buy").click(function () {
  var utr = $('#utr').val();
  var product = $('#product_id').val();
 if(utr === ''){
  alert('Fill Utr Field');
 }
        $.ajax({
          url: 'bharatpe.php?utr='+ utr +'&product_id=' + product,
          type: 'GET',
          dataType: 'json',
          success: function (response) {
            if (response.status === 200) {
              alert(response.message);
              $('#myLink').css('display', 'block');
              $('#buy').css('display', 'none');
              $('#myLink').attr('href', 'download.php?id=' + response.id);
              return;
            } else {
              alert(response.message);
            }
          },
          error: function () {
            console.log('Error in AJAX request');
          }
        });
      });
       });
  </script>

</body>

</html>