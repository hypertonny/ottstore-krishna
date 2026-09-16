<?php
session_start();
if(!isset($_SESSION['auth'])){
	header('location: ./index.php');
	return;
}
include("../auth.php");
$sql = $conn->query("SELECT * FROM categorys");
$total = $sql->num_rows;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
    <title>Dashboard</title>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
</head>
<?php include("../auth.php"); ?>
<body data-bs-theme="dark">
    <div class="container">
        <div class="fixed-bottom m-3">
  <a href="https://telegram.dog/getallscripts" target="_blank"><button type="button" class="btn btn-primary">
    <i class="fas fa-question-circle"></i> Help & Support
  </button></a>
</div>
<div class="row row-cols- row-cols-md-2 row-cols-lg-1">
        <div class="col mb-4">
            <div class="card m-2 p-3 border-3">
                <div class="card-header d-flex justify-content-center align-items-center">
                    <h4>TOTAL CATEGORY : <?php echo $total; ?></h4>                    
                </div>
                <div class="card-body">
                </br>                    
                        <a href="category.php" class="btn btn-primary">Add Category</a>
                      <a href="all_category.php" class="btn btn-primary">Show Category</a></br></br>
                      <a href="service.php" class="btn btn-primary">Add Service  </a>
                      <a href="all_service.php" class="btn btn-primary">All Service  </a></br></br>
                      <a href="all_transaction.php" class="btn btn-primary">All Transcation</a>
                      <a href="logout.php" class="btn btn-primary">Logout</a>
                      
                </div>                
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

