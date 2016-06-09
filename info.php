<?php 
$servername = "here.heresmydesktop.com";
$username = "mygps";
$password = "Tyxu150@";

// Create connection
$conn = mysql_connect($servername, $username, $password);

// Check connection
if (!$conn) {
    die("Connection failed: ");
}
echo "Database Connected successfully";


echo phpinfo(); 
?>