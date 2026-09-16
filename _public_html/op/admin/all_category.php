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
    <title>Show Category</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
<link href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js" integrity="sha512-AA1Bzp5Q0K1KanKKmvN/4d3IRKVlv9PYgwFPvm32nPO6QS8yH1HO7LbgB1pgiOxPtfeg5zEn2ba64MUcqJx6CA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</head>
<?php include("../auth.php"); ?>
<body data-bs-theme="dark">
    <div class="container">
        <div class="fixed-bottom m-3">
  <a href="https://telegram.dog/getallscripts" target="_blank"><button type="button" class="btn btn-primary">
    <i class="fas fa-question-circle"></i> Help & Support
  </button></a>
</div>
<?php
if(isset($_GET['delete'])){
$id = $_GET['delete'];
$conn->query("DELETE FROM categorys WHERE id='$id'");
}elseif($_POST['name']){ 
    $id = $conn->real_escape_string($_POST['id']);
    $name = $conn->real_escape_string(trim($_POST['name']));
    $info = $_POST['info'];
    $amount = $conn->real_escape_string(trim($_POST['amount']));
    $logo_url = $conn->real_escape_string(trim($_POST['logo_url']));
    if($name != "" && $id !="" && $amount != "" && $logo_url != "") {      
    $sql4 = $conn->query("UPDATE categorys SET name='$name', info='$info', amount='$amount', logo_url='$logo_url' WHERE id='$id'");
    if($sql4){
    echo '<script>               
                swal("Success", "Details Added Successfully", "success");
              </script>';
    }else{
   echo '<script>
            swal("Warning", "Error", "warning");
              </script>';}
    } else {
        echo '<script>
          swal("Warning", "Please fill all the fields first", "warning");
          </script>';
    }
}elseif($_GET['edit']){ 
$edit = $_GET['edit'];
$sql2 = $conn->query("SELECT * FROM categorys WHERE id='$edit'");
$data2 = $sql2->fetch_assoc();
echo '<form action="" method="post">            
    <div class="row gx-5 gy-3 my-3">
        <div class="col-md-6 col-lg-12">
            <label for="Category" class="form-label">Category Name :</label>
            <input type="hidden" name="id" value="'.$data2['id'].'" class="form-control form-control-sm" required="">
            <input type="text" name="name" value="'.$data2['name'].'" class="form-control form-control-sm" required="">
        </div>
        <div class="col-md-6 col-lg-12">
            <label for="Category-info" class="form-label">Category Info :</label>
            <input type="text" value="'.htmlspecialchars($data2["info"], ENT_QUOTES, "UTF-8").'" name="info" class="form-control form-control-sm" required="">
        </div>
        <div class="col-md-6 col-lg-12">
            <label for="Category-amount" class="form-label">Amount :</label>
            <input type="number" value="'.$data2['amount'].'" name="amount" class="form-control form-control-sm" required="">
        </div>
        <div class="col-md-6 col-lg-12">
            <label for="logo_url" class="form-label">Logo Url :</label>
            <input type="text" value="'.$data2['logo_url'].'" name="logo_url" class="form-control form-control-sm" required="">
        </div>
    </div><br><br>
    <div class="d-grid gap-2 d-md-block">
        <button type="submit" class="btn btn-primary btn-lg">Update Category</button>                
    </div>
</form>';
}else {
$sql = $conn->query("SELECT * FROM categorys");
while($data = $sql->fetch_assoc()){ ?>                                         <div class="container d-flex justify-content-center m-3 w-100">
        <table id="example" class="table table-striped table-bordered" width="100%">
            <thead>
                <tr>                    
                    <th>Category Name</th>
                    <th>Category Info</th>
                    <th>Amount</th>
                    <th>Total Stock</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                <td><?php echo $data['name'];?></td>
                <td><?php echo $data['info'];?></td>
                <td><?php echo $data['amount'];?></td>
                <td><?php echo $data['total_stock'];?></td>
                <td>
                <a href="?edit=<?php echo $data['id'];?>" class="btn btn-primary">Edit </a>              
                <a href="?delete=<?php echo $data['id'];?>" class="btn btn-warning">Delete</a></td>
                </tr>
                </tbody>
        </table>
    </div>                       
<?php } } ?>
</body>

</html>

