<?php
require 'db.php';

// We expect POST from JS-created form
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    die('Invalid request method');
}

$property_id = isset($_POST['property_id']) ? (int)$_POST['property_id'] : 0;
$guest_name = isset($_POST['guest_name']) ? trim($_POST['guest_name']) : '';
$guest_email = isset($_POST['guest_email']) ? trim($_POST['guest_email']) : '';
$guest_phone = isset($_POST['guest_phone']) ? trim($_POST['guest_phone']) : '';
$check_in = isset($_POST['check_in']) ? $_POST['check_in'] : null;
$check_out = isset($_POST['check_out']) ? $_POST['check_out'] : null;
$total_amount = isset($_POST['total_amount']) ? (float)$_POST['total_amount'] : 0.00;

// basic validation
if($property_id <= 0 || !$guest_name || !$guest_email || !$check_in || !$check_out){
    die('Missing required fields');
}

// Insert booking securely
$stmt = $mysqli->prepare("INSERT INTO bookings (property_id, guest_name, guest_email, guest_phone, check_in, check_out, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?)");
if(!$stmt){
    die("Prepare failed: " . $mysqli->error);
}
$stmt->bind_param("isssssd", $property_id, $guest_name, $guest_email, $guest_phone, $check_in, $check_out, $total_amount);
$ok = $stmt->execute();
$booking_id = $stmt->insert_id;
$stmt->close();

if(!$ok){
    die("Booking failed: " . $mysqli->error);
}

// Use JS redirect (not PHP header) as requested
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title>Redirecting...</title>
</head>
<body>
  <p>Booking created. Redirecting…</p>
  <script>
    // redirect to confirmation with booking id
    window.location.href = 'confirmation.php?id=<?php echo $booking_id; ?>';
  </script>
</body>
</html>
