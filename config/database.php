<?php
$conn=mysqli_connect(hostname:"localhost",username: "root", password:"",database:"commercial");
if(!$conn){
    die("connection failed". mysqli_connect_error());
}
?>