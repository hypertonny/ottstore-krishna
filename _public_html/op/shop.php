<?php include("./auth.php"); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <title>Readymade Account</title>
</head>

<body>
<?php 
$sql = $conn->query("SELECT * FROM categorys");

while($data = $sql->fetch_assoc()){ 
$cat_id = $data['id'];
$sql2 = $conn->query("SELECT * FROM sell where category_id='$cat_id' and status='1'");
$count = mysqli_num_rows($sql2);
?>                                         

    <div class="card m-3 shadow">
        <div class="card-body d-flex flex-column p-2">
            <div class="card-text space-y-4">
                <div class="d-flex align-items-center mb-0">
                    <img src="<?php echo $data['logo_url'];?>" class=" rounded-circle " style="margin-right:20px; font-size:20px;" height="55" width="55">
                       
                    <div class="flex-grow-1">
                        <p class="mb-0" style="font-size: 15px; font-weight: bold;"><?php echo $data['name'];?></p>
                        <p class="text-LG mb-2" style="font-size: 15px;font-weight: bold; color: blueviolet;">Stocks : <?php echo $count;?></p>
                    </div>
                    <div class="mb-2">
                        <span class="badge rounded-pill bg-warning text-dark" style="font-size: 20px; font-weight: bold;">₹<?php echo $data['amount'];?></span>
                    </div>
                </div>
                <div class=" mx-auto mb-3 px-3 pt-3 m-3 align-items-center rounded " style="background-color: #f3f5f7;">
                    <p><?php echo $data['info'];?>
                </div>
                
                </div>
<?php
if($count > 0){ 
?>           
                <div>
                    <a href="check.php?id=<?php echo $cat_id;?>"><button class="btn btn-primary w-100 ">BUY NOW</button></a>
                </div>
<?php
}else{
?>
    <div>
        <button class="btn btn-danger w-100 ">OUT OF STOCK</button>
     </div>
<?php
}
?>                    
            </div>
        </div>
    </div>
<?php } ?>    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>