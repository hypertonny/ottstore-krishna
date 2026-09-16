<?php
session_start();
include("../auth.php");
if (!isset($_SESSION['auth'])) {
    header('location: ./index.php');
    exit;
}

include("../auth.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Show Service</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>

    <link href="https://cdn.datatables.net/1.10.12/css/jquery.dataTables.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.4.2/js/buttons.html5.min.js"></script>
</head>

<body data-bs-theme="dark">
    <div class="container">
        <div class="fixed-bottom m-3">
            <a href="https://telegram.dog/getallscripts" target="_blank"><button type="button" class="btn btn-primary">
                    <i class="fas fa-question-circle"></i> Help & Support
                </button></a>
        </div>

        <?php
        if (isset($_POST['cat_id'])) {
            $cat_id = $_POST['cat_id'];
            $sql = mysqli_query($conn, "SELECT * FROM sell WHERE category_id='$cat_id'");
            if(mysqli_num_rows($sql) > 0){
            ?>
            <div class="container d-flex justify-content-center m-3 w-100">
                <table id="example" class="table table-striped table-bordered" width="100%">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Username</th>
                            <th>Password</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php  
                        $i=1;                      
                        while ($service_data = mysqli_fetch_array($sql)) {
                            echo '<tr>';
                            echo '<td>' . $i. '</td>';
                            echo '<td>' . $service_data['username'] . '</td>';
                            echo '<td>' . $service_data['password'] . '</td>';
                            echo '<td>' . (($service_data['status'] == 1) ? 'Available' : 'Sold') . '</td>';

                            echo '</tr>';
                        $i++;
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        <?php }else{
        echo "<script>alert('Not Any Service Found on This Category')</script>";
       } }else { ?>
            <form action="" method="post">
                <div class="row gx-5 gy-3 my-3">
                    <div class="col-md-6 col-lg-12">
                        <label for="category">Select Category</label>
                        <?php
                        $query = "SELECT * FROM categorys";
                        $statement = mysqli_query($conn, $query);
                        ?>
                        <select name="cat_id" class="form-control mb-3">
                            <?php
                            while ($row = mysqli_fetch_array($statement)) {
                            ?>
                                <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                    <div class="d-grid gap-2 d-md-block">
                        <button type="submit" class="btn btn-primary btn-lg">Show Service</button>
                    </div>
            </form>
        <?php } ?>
</body>

</html>
