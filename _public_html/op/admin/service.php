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
    <title>Add Service</title>
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
<?php if(isset($_POST['username'])){
    $cat_id = $conn->real_escape_string(trim($_POST['cat_id']));
    $username = $conn->real_escape_string(trim($_POST['username']));
    $password = $conn->real_escape_string(trim($_POST['password']));
    if($cat_id != "" && $username != "" && $password != "") {
    $category = mysqli_query($conn,"SELECT * FROM  categorys WHERE id='$cat_id'");
    $cat_info = mysqli_fetch_assoc($category);
    if(mysqli_num_rows($category) > 0){
    $sell = mysqli_query($conn,"SELECT * FROM  sell WHERE username='$username' AND password='$password'");
    if(mysqli_num_rows($sell) == 0){
    $stock = $cat_info['total_stock'];
    $cat_add = mysqli_query($conn,"INSERT INTO sell (category_id, username, password, status) VALUES ('$cat_id', '$username', '$password', 1)");    
     if($cat_add){
        $t_stock =  $stock += 1;   
        mysqli_query($conn,"UPDATE categorys SET total_stock='$t_stock' WHERE id='$cat_id'");   
            echo '<script>               
                swal("Success", "Details Added Successfully", "success");
              </script>';
        } else {
            echo '<script>
            swal("Warning", "Error", "warning");
              </script>';
        }
        } else{
        echo '<script>
            swal("Warning", "Already Account Exists", "warning");
              </script>';
        }
        } else {
           echo '<script>
            swal("Warning", "Invalid Category", "warning");
              </script>';
    }
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
                    <label for="categroy">Select Category</label>
                   <?php
              $query = "SELECT * FROM categorys";
            $statement = mysqli_query($conn,$query);
                                            ?>  
                  <select name="cat_id" class="form-control mb-3">
                      <?php
                                                   while($row=mysqli_fetch_array($statement))
 
                                                    {
                                                        ?>
                                                            <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                                                        <?php
                                                    }
                                                ?>
                  </select>
                </div>
                <div class="col-md-6 col-lg-12">
                    <label for="Category-info" class="form-label">UserName :</label>
                    <input type="text" name="username"  class="form-control form-control-sm" required="">
                </div>
                 <div class="col-md-6 col-lg-12">
                    <label for="Category-amount" class="form-label">Password :</label>
                    <input type="text" name="password"  class="form-control form-control-sm" required="">
                </div>                
            <br><br>
            <div class="d-grid gap-2 d-md-block">
                <button type="submit" class="btn btn-primary btn-lg">Add Service</button>                
            </div>
        </form>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php } ?>
</body>
</html>

