<?php
require 'db_configuration.php';

$status = session_status();
if ($status == PHP_SESSION_NONE) {
  session_start();
}

if (!(isset($_SESSION['email']))) {
	header('Location: login.php');
}

include 'show-navbar.php';
$connection = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, DATABASE_DATABASE);

if ($connection === false) {
	die("Failed to connect to database: " . mysqli_connect_error());
}
if (isset($_POST['action'])) {
	$action = $_POST['action'];
} else {
	$action = '';
}

if ($action == 'edit' || $action == 'add' || $action == 'admin_edit') {
    // Validate and sanitize form input
    $sponsor1_name = isset($_POST['sponsor1-name']) ? htmlspecialchars($_POST['sponsor1-name']) : '';
    $sponsor1_email = isset($_POST['sponsor1-email']) ? filter_var($_POST['sponsor1-email'], FILTER_SANITIZE_EMAIL) : '';
    $sponsor1_phone = isset($_POST['sponsor1-phone']) ? htmlspecialchars($_POST['sponsor1-phone']) : '';
    $sponsor2_name = isset($_POST['sponsor2-name']) ? htmlspecialchars($_POST['sponsor2-name']) : '';
    $sponsor2_email = isset($_POST['sponsor2-email']) ? filter_var($_POST['sponsor2-email'], FILTER_SANITIZE_EMAIL) : '';
    $sponsor2_phone = isset($_POST['sponsor2-phone']) ? htmlspecialchars($_POST['sponsor2-phone']) : '';
    $student_name = isset($_POST['students-name']) ? htmlspecialchars($_POST['students-name']) : '';
    $student_email = isset($_POST['students-email']) ? filter_var($_POST['students-email'], FILTER_SANITIZE_EMAIL) : '';
    $student_phone = isset($_POST['students-phone']) ? htmlspecialchars($_POST['students-phone']) : '';
    $class_id = isset($_POST['class']) ? htmlspecialchars($_POST['class']) : '';
    $cause = isset($_POST['cause']) ? htmlspecialchars($_POST['cause']) : '';

    $timestamp = date("Y-m-d H:i:s");
} else {
	$User_Id = $_SESSION['User_Id'];
    $sql = "SELECT * FROM registrations NATURAL JOIN classes NATURAL JOIN user_registrations WHERE User_Id = $User_Id;";
    $row = mysqli_fetch_array(mysqli_query($connection, $sql));

		$action = '';
        $Reg_Id = $row['Reg_Id'];
		$sponsor1_name = $row['Sponsor1_Name'];
		$sponsor1_email = $row['Sponsor1_Email'];
		$sponsor1_phone = $row['Sponsor1_Phone_Number'];
		$sponsor2_name = $row['Sponsor2_Name'];
		$sponsor2_email = $row['Sponsor2_Email'];
		$sponsor2_phone = $row['Sponsor2_Phone_Number'];
		$student_name = $row['Student_Name'];
		$student_email = $row['Student_Email'];
		$student_phone = $row['Student_Phone_Number'];
		$class_id = $row['Class_Id'];
		$cause = $row['Cause'];

}

// FIXME: Hardcoded in relation to database
// Correct method should pull the available classes from the database,
// Allow the user to select one using the interface, and then POST from there.

// switch ($class_id){
// 	case 2:
// 		$class = "Python 101";
// 		break;
// 	case 4:
// 		$class = "Python 201";
// 		break;
// 	case 1:
// 		$class = "Java 101";
// 		break;
// 	case 3:
// 		$class = "Java 201";
// }

// Pull the available classes from the database
$class_query = "SELECT Class_Id, Class_Name FROM classes";
$class_result = mysqli_query($connection, $class_query);

if (!$class_result) {
    die("Query failed: " . mysqli_error($connection));
}

$classes = [];
while ($class_row = mysqli_fetch_assoc($class_result)) {
    $classes[$class_row['Class_Id']] = $class_row['Class_Name'];
}

// Get the class name using the class_id
$class = isset($classes[$class_id]) ? $classes[$class_id] : 'Unknown Class';

switch ($cause){ // FIXME: Hardcoded in.
	case "lib":
		$cause = "Library";
		break;
	case "Dig_class":
		$cause = "Digital Classroom";
		break;
	case "Other":
		$cause = "No Preference";
}

if ($action == 'add') {
	$sql = "INSERT INTO registrations VALUES (
		NULL,
		'$sponsor1_name',
		'$sponsor1_email',
		'$sponsor1_phone',
		'$sponsor2_name',
		'$sponsor2_email',
		'$sponsor2_phone',
		'$student_name',
		'$student_email',
		'$student_phone',
		'$class_id',
		'$cause',
		'$timestamp',
		'$timestamp');";

} elseif($action == "edit") {
	$Reg_Id = $_POST['Reg_Id'];
	$sql = "UPDATE registrations SET
			Sponsor1_Name = '$sponsor1_name',
			Sponsor1_Email = '$sponsor1_email',
			Sponsor1_Phone_Number = '$sponsor1_phone',
			Sponsor2_Name = '$sponsor2_name',
			Sponsor2_Email = '$sponsor2_email',
			Sponsor2_Phone_Number = '$sponsor2_phone',
			Student_Name = '$student_name',
			Student_Email = '$student_email',
			Student_Phone_Number = '$student_phone',
			Class_Id = '$class_id',
			Cause = '$cause',
			Modified_Time = '$timestamp'
			WHERE Reg_Id = '$Reg_Id';";

} elseif($action == "admin_edit") {
	$Reg_Id = $_POST['Reg_Id'];
	$sql = "UPDATE registrations SET
			Sponsor1_Name = '$sponsor1_name',
			Sponsor1_Email = '$sponsor1_email',
			Sponsor1_Phone_Number = '$sponsor1_phone',
			Sponsor2_Name = '$sponsor2_name',
			Sponsor2_Email = '$sponsor2_email',
			Sponsor2_Phone_Number = '$sponsor2_phone',
			Student_Name = '$student_name',
			Student_Email = '$student_email',
			Student_Phone_Number = '$student_phone',
			Class_Id = '$class_id',
			Cause = '$cause',
			Modified_Time = '$timestamp'
			WHERE Reg_Id = '$Reg_Id';";

}

if (!mysqli_query($connection, $sql)) {
	echo("Error description: " . mysqli_error($connection));
  }
if ($action == 'add') {
	$Reg_Id = mysqli_insert_id($connection);
	$User_Id = $_SESSION['User_Id'];
	$sql = 'INSERT INTO user_registrations VALUES (' . $User_Id . ', ' . $Reg_Id .');';
	if (!mysqli_query($connection, $sql)) {
		echo("Error description: " . mysqli_error($connection));
	 }
}
mysqli_close($connection);

echo "<!DOCTYPE html>
<!DOCTYPE html>
  <head>
    <title>Learn and Help</title>
		<link rel=\"icon\" href=\"images/icon_logo.png\" type=\"image/icon type\">
    <link href=\"https://fonts.googleapis.com/css2?family=Roboto:wght@300;900&display=swap\" rel=\"stylesheet\">
    <link href=\"css/main.css\" rel=\"stylesheet\">
  </head>
  <body>";
		show_navbar();
	echo  "<header class=\"inverse\">
      <div class=\"container\">
        <h1> <span class=\"accent-text\">Registration Submitted</span></h1>
      </div>
  		</header>
		<h3> Registration Details </h3>
    <div id=\"container_2\">
		<form action=\"registration_edit.php\" method = \"post\">
				<input type='hidden' name='Reg_Id' value=$Reg_Id>
			<!---sponsor1 Section -->
			<label id=\"name-label\"><b>Sponsor 1's Name:</b> $sponsor1_name</label><br>
			<input type=\"hidden\" id=\"action\" name=\"action\" value=\"edit\">
			<label id=\"sponsor1-email-label\"> <b>Sponsor 1's Email:</b> $sponsor1_email</label><br>
			<label id=\"sponsor1-number-label\"><b>Sponsor 1's Phone Number:</b> $sponsor1_phone</label><br>
			<input type=\"hidden\" id=\"sponsor1-name\" name=\"sponsor1-name\" class=\"form\" value=\"$sponsor1_name\"><!--name--->
			<input type=\"hidden\" id=\"sponsor1-email\" name=\"sponsor1-email\" class=\"form\" value=\"$sponsor1_email\"><br><!---email-->
			<input type=\"hidden\" id=\"sponsor1-phone\" name=\"sponsor1-phone\" value=\"$sponsor1_phone\">

			<br>
			<!---Sponsor2 Section -->
			<label id=\"sponsor2-name-label\"><b>Sponsor 2's Name:</b> $sponsor2_name</label><br>

			<label id=\"sponsor2-email-label\"> <b>Sponsor 2's Email:</b> $sponsor2_email</label><br>

			<label id=\"sponsor2-number-label\"><b>Sponsor 2's Phone Number:</b> $sponsor2_phone</label><br>
			<input type=\"hidden\" id=\"sponsor2-name\" name=\"sponsor2-name\" class=\"form\" value=\"$sponsor2_name\"><!--name--->
			<input type=\"hidden\" id=\"sponsor2-email\" name=\"sponsor2-email\" class=\"form\" value=\"$sponsor2_email\"><br><!---email-->
			<input type=\"hidden\" id=\"sponsor2-phone\" name=\"sponsor2-phone\" value=\"$sponsor2_phone\">

			<br>
			<!---Student Section -->
			<label id=\"students-name-label\"><b>Student's Name:</b> $student_name</label><br>

			<label id=\"students-email-label\"><b>Student's Email:</b> $student_email</label><br>

			<label id=\"students-number-label\"><b>Student's Phone Number:</b> $student_phone</label><br>
			<input type=\"hidden\" id=\"students-name\" name=\"students-name\" class=\"form\" value=\"$student_name\"><!--name--->
			<input type=\"hidden\" id=\"students-email\" name=\"students-email\" class=\"form\" value=\"$student_email\"><br><!---email-->
			<input type=\"hidden\" id=\"students-phone\" name=\"students-phone\" value=\"$student_phone\">

			<br>
			<label id=\"class\"><b>Selected Class:</b> $class</label><br>
			<input type=\"hidden\" id=\"class\" name=\"class\" value=\"$class\">
			<!--dropdown--->
			<p><b>Cause:</b> $cause</p><br>
			<input type=\"hidden\" id=\"cause\" name=\"cause\" value=\"$cause\">
			<br>
			<input type='hidden' name='action' value='edit'>
			<input type=\"submit\" id=\"submit-registration\" name=\"submit\" value=\"Edit\"></a>
		</form>
		<br><br>
	</div>";
	if (TRUE){ //make bool to determine if past registarions exist
		echo "
			<h3> Past Registration Details </h3>
			<div id=\"container_3\">";
			if (True) { //if next row exists for student in registration table for previous years
				echo " 
					<div id='accordion-container'>
					<button class='accordion' >Section 1</button>
					<div class='panel' >
						<p>Content for section 1...</p>
					</div>
					<br>

					<button class='accordion'>Section 2</button>
					<div class='panel'>
						<p>Content for section 2...</p>
					</div>
					<br>

					<button class='accordion'>Section 3</button>
					<div class='panel'>
						<p>Content for section 3...</p>
					</div>
					</div>
					<br>
					";
			}
			echo "
				<script>
				var acc = document.getElementsByClassName('accordion');
				for (var i = 0; i < acc.length; i++) {
					acc[i].addEventListener('click', function() {
						this.classList.toggle('active');
						var panel = this.nextElementSibling;
						if (panel.style.display === 'block') {
							panel.style.display = 'none';
						} else {
							panel.style.display = 'block';
						}
					});
				}
			</script>
			</div>
			<br><br>";
	}

echo "		
  </body>
</html>
";
?>