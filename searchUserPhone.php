<?php
$con=mysqli_connect("localhost:3306","root","password","cmpe272");
// Check connection
if (mysqli_connect_errno())
{
echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

$result = mysqli_query($con,"SELECT * FROM Users");

echo "<table border='1'>
<tr>
<th>Id</th>
<th>Firstname</th>
<th>Lastname</th>
<th>Email</th>
<th>Address</th>
<th>HomePhone</th>
<th>CellPhone</th>

</tr>";

while($row = mysqli_fetch_array($result))
{
    if($row['HomePhone'] == $_POST['searchPhone']) {
echo "<tr>";
echo "<td>" . $row['Id'] . "</td>";
echo "<td>" . $row['FirstName'] . "</td>";
echo "<td>" . $row['LastName'] . "</td>";
echo "<td>" . $row['Email'] . "</td>";
echo "<td>" . $row['Address'] . "</td>";
echo "<td>" . $row['HomePhone'] . "</td>";
echo "<td>" . $row['CellPhone'] . "</td>";
echo "</tr>";

}
else {
    echo "";
}
}
echo "</table>";

mysqli_close($con);
?>
