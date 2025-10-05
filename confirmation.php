<?php
require 'db.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if($id <= 0) die("Booking not found");

$stmt = $mysqli->prepare("SELECT b.*, p.title, p.city, p.price_per_night FROM bookings b JOIN properties p ON p.id = b.property_id WHERE b.id = ?");
$stmt->bind_param("i",$id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();
if(!$booking) die("Booking not found");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" /><meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Booking Confirmed — AirClone</title>
<style>
:root{--accent:#ff585d;--muted:#6b7280;--card:#fff;--bg:#f7fafc;}
*{box-sizing:border-box;font-family:Inter,system-ui,Arial;}
body{margin:0;background:var(--bg);color:#0f172a;}
.container{max-width:800px;margin:40px auto;padding:0 16px;}
.card{background:#fff;padding:20px;border-radius:12px;box-shadow:0 8px 30px rgba(2,6,23,0.06);}
.kv{display:flex;justify-content:space-between;margin-bottom:10px;}
.btn{background:var(--accent);color:#fff;padding:10px 12px;border:none;border-radius:10px;cursor:pointer;}
.small{color:var(--muted);font-size:14px;}
</style>
</head>
<body>
<div class="container">
  <div class="card">
    <h2>Booking Confirmed 🎉</h2>
    <p class="small">Booking ID: <strong><?php echo $booking['id']; ?></strong></p>

    <div class="kv"><div>Property</div><div><?php echo htmlspecialchars($booking['title']); ?></div></div>
    <div class="kv"><div>City</div><div><?php echo htmlspecialchars($booking['city']); ?></div></div>
    <div class="kv"><div>Guest</div><div><?php echo htmlspecialchars($booking['guest_name']); ?> (<?php echo htmlspecialchars($booking['guest_email']); ?>)</div></div>
    <div class="kv"><div>Phone</div><div><?php echo htmlspecialchars($booking['guest_phone']); ?></div></div>
    <div class="kv"><div>Check-in</div><div><?php echo htmlspecialchars($booking['check_in']); ?></div></div>
    <div class="kv"><div>Check-out</div><div><?php echo htmlspecialchars($booking['check_out']); ?></div></div>
    <div class="kv"><div>Total</div><div>₨<?php echo number_format($booking['total_amount'],2); ?></div></div>

    <div style="margin-top:14px;">
      <a href="property.php?id=<?php echo (int)$booking['property_id']; ?>"><button class="btn">View property</button></a>
      <a href="index.php" style="margin-left:8px;text-decoration:none;"><button style="margin-left:8px;padding:10px 12px;border-radius:10px;">Back to home</button></a>
    </div>
    <p class="small" style="margin-top:12px;color:var(--muted)">A confirmation is shown here. For real app, you may send email confirmations using mail library in future.</p>
  </div>
</div>
</body>
</html>
