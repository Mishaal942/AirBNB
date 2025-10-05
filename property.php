<?php
require 'db.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if($id <= 0){
  die("Property not found");
}
// fetch property
$stmt = $mysqli->prepare("SELECT * FROM properties WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$prop = $stmt->get_result()->fetch_assoc();
$stmt->close();
if(!$prop) die("Property not found");

// images
$imgStmt = $mysqli->prepare("SELECT image_url FROM property_images WHERE property_id = ? ORDER BY is_hero DESC, id ASC");
$imgStmt->bind_param("i", $id);
$imgStmt->execute();
$imgRes = $imgStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$imgStmt->close();

// amenities
$amenStmt = $mysqli->prepare("SELECT a.name FROM amenities a JOIN property_amenities pa ON pa.amenity_id = a.id WHERE pa.property_id = ?");
$amenStmt->bind_param("i",$id);
$amenStmt->execute();
$amenities = $amenStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$amenStmt->close();

// reviews
$revStmt = $mysqli->prepare("SELECT reviewer_name, rating, comment, created_at FROM reviews WHERE property_id = ? ORDER BY created_at DESC LIMIT 10");
$revStmt->bind_param("i",$id);
$revStmt->execute();
$reviews = $revStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$revStmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" /><meta name="viewport" content="width=device-width,initial-scale=1" />
<title><?php echo htmlspecialchars($prop['title']); ?> — AirClone</title>
<style>
:root{--accent:#ff585d;--muted:#6b7280;--card:#fff;--bg:#f7fafc;}
*{box-sizing:border-box;font-family:Inter,system-ui,Arial;}
body{margin:0;background:var(--bg);color:#0f172a;}
.container{max-width:1000px;margin:20px auto;padding:0 16px;}
.gallery{display:flex;gap:12px;}
.hero{flex:2;border-radius:12px;overflow:hidden;height:360px;}
.side{flex:1;background:var(--card);padding:16px;border-radius:12px;height:360px;box-shadow:0 6px 20px rgba(2,6,23,0.04);}
.thumbs{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-top:12px;}
.thumb{height:80px;border-radius:8px;overflow:hidden;}
.price{font-size:20px;font-weight:700;color:var(--accent);}
.btn{background:var(--accent);color:#fff;padding:12px 14px;border:none;border-radius:10px;cursor:pointer;font-weight:700;width:100%;}
.small{color:var(--muted);font-size:13px;}
.amen-list{display:flex;gap:10px;flex-wrap:wrap;margin-top:8px;}
.chip{background:#fff;padding:8px 10px;border-radius:999px;border:1px solid #eee;font-size:13px;color:var(--muted);}
.reviews{margin-top:18px;}
.review{background:#fff;padding:12px;border-radius:10px;border:1px solid #eee;margin-bottom:8px;}
@media(max-width:900px){ .gallery{flex-direction:column;} .side{height:auto;} .hero{height:260px;} }
</style>
</head>
<body>
<div class="container">
  <a href="index.php" class="small">← Back to home</a>
  <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px;">
    <div>
      <h1 style="margin:6px 0;"><?php echo htmlspecialchars($prop['title']); ?></h1>
      <div class="small"><?php echo htmlspecialchars($prop['city']); ?> · <?php echo htmlspecialchars($prop['property_type']); ?></div>
    </div>
    <div style="text-align:right">
      <div class="price">₨<?php echo number_format($prop['price_per_night']); ?> / night</div>
      <div class="small">Rating: <?php echo $prop['rating']; ?></div>
    </div>
  </div>

  <div class="gallery" style="margin-top:14px;">
    <div class="hero">
      <?php if(!empty($imgRes)): ?>
        <img src="<?php echo htmlspecialchars($imgRes[0]['image_url']); ?>" style="width:100%;height:100%;object-fit:cover;" alt="">
      <?php endif; ?>
    </div>
    <div class="side">
      <div style="font-weight:700;margin-bottom:6px">Book this stay</div>
      <div class="small">Max guests: <?php echo (int)$prop['max_guests']; ?></div>
      <div style="margin-top:14px;">
        <label class="small">Check-in</label>
        <input id="ci" type="date" style="width:100%;padding:8px;border-radius:8px;border:1px solid #e6e9ef;margin-top:6px;">
        <label class="small" style="margin-top:8px;display:block;">Check-out</label>
        <input id="co" type="date" style="width:100%;padding:8px;border-radius:8px;border:1px solid #e6e9ef;margin-top:6px;">
      </div>

      <div style="margin-top:12px;">
        <input id="name" placeholder="Your name" style="width:100%;padding:8px;border-radius:8px;border:1px solid #e6e9ef;margin-top:6px;">
        <input id="email" placeholder="Email" style="width:100%;padding:8px;border-radius:8px;border:1px solid #e6e9ef;margin-top:8px;">
        <input id="phone" placeholder="Phone" style="width:100%;padding:8px;border-radius:8px;border:1px solid #e6e9ef;margin-top:8px;">
      </div>

      <div style="margin-top:12px;">
        <button class="btn" onclick="bookNow(<?php echo $prop['id']; ?>, <?php echo (float)$prop['price_per_night']; ?>)">Reserve</button>
      </div>

      <script>
        function daysBetween(a,b){
          const A = new Date(a), B = new Date(b);
          const diff = B - A;
          if(isNaN(diff)) return 0;
          return Math.max(0, Math.ceil(diff / (1000*60*60*24)));
        }
        function bookNow(propertyId, pricePerNight){
          const checkIn = document.getElementById('ci').value;
          const checkOut = document.getElementById('co').value;
          const name = document.getElementById('name').value.trim();
          const email = document.getElementById('email').value.trim();
          const phone = document.getElementById('phone').value.trim();
          if(!checkIn || !checkOut || !name || !email){
            alert('Please fill required fields');
            return;
          }
          const nights = daysBetween(checkIn, checkOut);
          if(nights <= 0){
            alert('Select valid dates');
            return;
          }
          const total = nights * pricePerNight;
          // use JS to redirect to booking.php with POST via form (so we comply with "JS for redirection")
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = 'booking.php';
          const fields = {
            property_id: propertyId,
            guest_name: name,
            guest_email: email,
            guest_phone: phone,
            check_in: checkIn,
            check_out: checkOut,
            total_amount: total
          };
          for(const k in fields){
            const ip = document.createElement('input');
            ip.type = 'hidden';
            ip.name = k;
            ip.value = fields[k];
            form.appendChild(ip);
          }
          document.body.appendChild(form);
          form.submit();
        }
      </script>
    </div>
  </div>

  <div class="thumbs">
    <?php foreach($imgRes as $im): ?>
      <div class="thumb"><img src="<?php echo htmlspecialchars($im['image_url']); ?>" style="width:100%;height:100%;object-fit:cover;" alt=""></div>
    <?php endforeach; ?>
  </div>

  <section style="margin-top:18px;">
    <h3>About this place</h3>
    <p class="small"><?php echo nl2br(htmlspecialchars($prop['description'])); ?></p>

    <h4 style="margin-top:12px;">Amenities</h4>
    <div class="amen-list">
      <?php foreach($amenities as $a): ?>
        <div class="chip"><?php echo htmlspecialchars($a['name']); ?></div>
      <?php endforeach; ?>
    </div>

    <div class="reviews">
      <h4 style="margin-top:12px;">Reviews</h4>
      <?php if(empty($reviews)) echo '<div class="small">No reviews yet.</div>'; ?>
      <?php foreach($reviews as $r): ?>
        <div class="review">
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <div style="font-weight:700"><?php echo htmlspecialchars($r['reviewer_name']); ?></div>
            <div class="small">Rating: <?php echo (int)$r['rating']; ?></div>
          </div>
          <div class="small" style="margin-top:6px;"><?php echo htmlspecialchars($r['comment']); ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

</div>
</body>
</html>
