<?php 
include("./auth.php");
if(isset($_GET['id'])){
$id = $_GET['id'];
$sql = $conn->query("SELECT * FROM categorys WHERE id='$id'");
if($sql->num_rows > 0){
$data = $sql->fetch_assoc();
echo $data['total_stock'];
} else{
echo "0";
}
}else{
echo "0";
}
?>