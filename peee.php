<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'Mimansha10');
define('DB_DATABASE', 'mypage');
 
// creating the database connection
$conn = mysql_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD) or die(mysql_error());
$db = mysql_select_db(DB_DATABASE) or die(mysql_error());
 
// lets find out how many rows are in the MySQL table
$sql = "SELECT COUNT(*) FROM wp_posts";
$result = mysql_query($sql, $conn) or die(mysql_error());
$r = mysql_fetch_row($result);
$numrows = $r[0];
 
// number of rows to show per page
$rowsperpage = 10;
 
// find out total pages
$totalpages = ceil($numrows / $rowsperpage);
 
// get the current page or set a default
if (isset($_GET['currentpage']) && is_numeric($_GET['currentpage'])) {
$currentpage = (int) $_GET['currentpage'];
} else {
$currentpage = 1;  // default page number
}
 
// if current page is greater than total pages
if ($currentpage > $totalpages) {
// set current page to last page
$currentpage = $totalpages;
}
// if current page is less than first page
if ($currentpage < 1) {
// set current page to first page
$currentpage = 1;
}
 
// the offset of the list, based on current page
$offset = ($currentpage - 1) * $rowsperpage;
 
// get the info from the MySQL database
$sql = "SELECT post_title, post_date FROM wp_posts ORDER BY ID DESC LIMIT $offset, $rowsperpage";
$result = mysql_query($sql, $conn) or die(mysql_error());
 
while ($row = mysql_fetch_assoc($result)){
$output[]=$row;
}
print(json_encode($output));
 
 
?>