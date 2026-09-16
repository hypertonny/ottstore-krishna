<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTT BUY</title>

    <link href="https://cdn.datatables.net/1.10.12/css/jquery.dataTables.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.css" rel="stylesheet" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.4.2/js/buttons.html5.min.js"></script>
</head>

<?php include("./auth.php"); ?> 
<body>
<?php

if(isset($_GET['id'])){
$id = $_GET['id'];
$sql = mysqli_query($conn, "SELECT * FROM  sold WHERE txn_id='$id'");
if(mysqli_num_rows($sql) > 0){
?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="text-center" style="color: blue; font-weight: bold;">OTT BUY</h4>
                    </div>
                    <div class="card-body">
                        <table id="example" class="table table-striped table-bordered" width="100%">
                            <thead>
                                <tr>
                                    <th>Email/Username</th>
                                    <th>Password</th>
                                </tr>
                            </thead>
                             <tbody>            
            <?php while($ac_data =  mysqli_fetch_assoc($sql)){
            $username = $ac_data['username'];
            $password = $ac_data['password']; ?>
            <tr>
                <td><?php echo $username;?></td>
                <td><?php echo $password;?></td>                </tr>  
                <?php } ?>
                          
                </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary" id="exportPdf">Export to PDF</button>
                        <button class="btn btn-success" id="exportExcel">Export to Excel</button>
                        <button class="btn btn-info p-2" id="exportCsv">Export to CSV</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            $('#example').DataTable();
        });

        // DataTables Buttons
        var table = $('#example').DataTable({
            dom: 'Bfrtip',
            buttons: [{
                extend: 'pdf',
                title: 'OTT BUY',
                filename: 'ottbuy'
            }, {
                extend: 'excel',
                title: 'OTT BUY',
                filename: 'ottbuy'
            }, {
                extend: 'csv',
                filename: 'ottbuy'
            }]
        });

        // Trigger export buttons
        $('#exportPdf').click(function () {
            table.button('.buttons-pdf').trigger();
        });

        $('#exportExcel').click(function () {
            table.button('.buttons-excel').trigger();
        });

        $('#exportCsv').click(function () {
            table.button('.buttons-csv').trigger();
        });
    </script>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<?php }else{
echo '<script>alert("INVALID ID");</script>';}
} else{
echo '<script>alert("INVALID ID");</script>';
} ?>
</body>

</html>
