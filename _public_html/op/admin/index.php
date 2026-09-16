<?php 
session_start(); 
if(isset($_SESSION['auth'])){
	header('location: ./dashboard.php');
	return;
}
?>
<!DOCTYPE html>
<html lang="en">
<?php include("../auth.php"); ?>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
    <title>Login</title>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
</head>
<body data-bs-theme="dark">
    <div class="container">
        <div class="fixed-bottom m-3">
  <a href="https://telegram.dog/getallscripts" target="_blank"><button type="button" class="btn btn-primary">
    <i class="fas fa-question-circle"></i> Help & Support
  </button></a>
</div>
<?php
if(isset($_POST['username'])){
$username = $conn->real_escape_string(trim($_POST['username']));
$sql = $conn->query("SELECT * FROM login WHERE username='$username'");
if($sql->num_rows > 0){
$_SESSION["auth"] = true;
header('location: ./dashboard.php');
}else{
echo "<script>alert('Invalid Login Key')</script>";
}
} else { 
?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="text-center">Login</h3>
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="form-group m-3">
                        <label for="username">Access Code:</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block m-3">Login</button>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php } ?>
</body>

</html>

