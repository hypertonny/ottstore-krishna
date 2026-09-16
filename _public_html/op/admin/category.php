<?php
session_start();
if(!isset($_SESSION['auth'])){
	header('location: ./index.php');
	return;
}
include("../auth.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
    <title>Category</title>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js" integrity="sha512-AA1Bzp5Q0K1KanKKmvN/4d3IRKVlv9PYgwFPvm32nPO6QS8yH1HO7LbgB1pgiOxPtfeg5zEn2ba64MUcqJx6CA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</head>
<body data-bs-theme="dark">
    <div class="container">
        <div class="fixed-bottom m-3">
  <a href="https://telegram.dog/getallscripts" target="_blank"><button type="button" class="btn btn-primary">
    <i class="fas fa-question-circle"></i> Help & Support
  </button></a>
</div>
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<?php if(isset($_POST['name'])){
    $name = $conn->real_escape_string(trim($_POST['name']));
    $info = $_POST['info'];
    $amount = $conn->real_escape_string(trim($_POST['amount']));
 //   $stock = $conn->real_escape_string(trim($_POST['stock']));
    $logo_url = $conn->real_escape_string(trim($_POST['logo_url']));
    $total_stock = 0;    
    if($name != "" && $amount != "" && $logo_url != "") {
        $stmt = $conn->prepare("INSERT INTO categorys (name, info, amount, total_stock, logo_url) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiis", $name, $info, $amount, $total_stock, $logo_url);

        if($stmt->execute()){
            echo '<script>               
                swal("Success", "Details Added Successfully", "success");
              </script>';
        } else {
            echo '<script>
            swal("Warning", "Error", "warning");
              </script>';
        }

        $stmt->close();
    } else {
        echo '<script>
          swal("Warning", "Please fill all the fields first", "warning");
          </script>';
    }
}else{
?>
        <form action="" method="post">            
            <div class="row gx-5 gy-3 my-3">
                <div class="col-md-6 col-lg-12">
                    <label for="Category" class="form-label">Category Name :</label>
                    <input type="text" name="name"  class="form-control form-control-sm" required="">
                </div>
                <div class="col-md-6 col-lg-12">
                    <label for="Category-info" class="form-label">Category Info :</label>
                    <input type="text" name="info"  class="form-control form-control-sm" required="">
                </div>
                 <div class="col-md-6 col-lg-12">
                    <label for="Category-amount" class="form-label">Amount :</label>
                    <input type="number" name="amount"  class="form-control form-control-sm" required="">
                </div>
                     <div class="col-md-6 col-lg-12">
                    <label for="logo_url" class="form-label">Logo Url:</label>
                    <input type="text" name="logo_url"  class="form-control form-control-sm" required="">
                </div>
            <br><br>
            <div class="d-grid gap-2 d-md-block">
                <button type="submit" class="btn btn-primary btn-lg">Add Category</button>                
            </div>
        </form>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php } ?>
</body>
</html>

