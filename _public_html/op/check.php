<?php
include("./auth.php");
$id = $_GET['id'];
$sql1 = $conn->query("SELECT * FROM categorys WHERE id='$id'");
if($sql1->num_rows <= 0){
	header('location: index.php');
return;		
}
$sql2 = $conn->query("SELECT * FROM sell where category_id='$id' and status='1'");
if($sql2->num_rows <= 0){
	header('location: index.php');
return;		
}
$data2 = $sql1->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <title>OTT BUY</title>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
    
</head>
<body>
    <div class="container  mt-5 ">
        <h5 class="text-center">OTTBUY</h5>
       <form action="payment.php" method="get">
            <input type="hidden" name="service" value="<?php echo $id;?>">                    
            <div class="form-group">
                <label for="service">Selected Service :</label>
                <input type="text" class="form-control" id="" value="<?php echo $data2['name'];?>" readonly>
            </div>
            <div class="form-group">
                <label for="account">How Much Account you Need :</label>
                <input type="number" class="form-control" name="account" id="account" required>
            </div>
            <div class="form-group">
                <label for="amount">Amount Need To Pay :</label>
                <input type="text" class="form-control" id="amount" value="0" readonly>
            </div>
           
            <button type="submit" class="btn btn-primary w-100 mb-2">Pay Now</button>
            <a type="submit" class="btn btn-warning w-100" href="/">Back </a>
    </div>
   </form>
       <script>
            var account = document.getElementById("account");
            var amount = document.getElementById("amount");
            account.oninput = function() {
                amount.value = this.value * <?php echo $data2['amount'];?>;
            }
        </script>
</body>
</html>
