[Enter code here]<?php
require 'db.php';

// fetch featured (top rated) properties
$stmt = $mysqli->prepare("SELECT id, title, slug, city, price_per_night, rating FROM properties ORDER BY rating DESC LIMIT 6");
$stmt->execute();
$result = $stmt->get_result();
$featured = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>AirClone — Home</title>
<style>
/* INTERNAL CSS - professional, clean Airbnb-like design */
:root{
  --accent:#ff585d;
  --muted:#6b7280;
  --bg:#f7fafc;
  --card:#ffffff;
  --radius:14px;
}
*{box-sizing:border-box;font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial;}
body{margin:0;background:var(--bg);color:#0f172a;}
.header{background:linear-gradient(90deg, #fff 0%, #fff 100%);padding:28px 24px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 2px 10px rgba(15,23,42,0.04);position:sticky;top:0;z-index:10;}
.brand{display:flex;align-items:center;gap:12px;}
.logo{width:42px;height:42px;border-radius:10px;background:var(--accent);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;}
.search-wrap{max-width:1000px;margin:28px auto;padding:0 16px;}
.search-card{background:var(--card);padding:18px;border-radius:12px;box-shadow:0 6px 30px rgba(2,6,23,0.06);display:flex;gap:12px;align-items:center;}
.search-card input, .search-card select{border:1px solid #e6e9ef;padding:12px;border-radius:10px;flex:1;}
.search-card button{background:var(--accent);color:#fff;padding:12px 18px;border:none;border-radius:10px;cursor:pointer;font-weight:600;}
.container{max-width:1200px;margin:22px auto;padding:0 16px;}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:18px;}
.card{background:var(--card);border-radius:12px;overflow:hidden;box-shadow:0 6px 20px rgba(2,6,23,0.04);display:flex;flex-direction:column;}
.card .img{height:160px;background:#eee;display:flex;align-items:center;justify-content:center;}
.card .body{padding:14px;flex:1;}
.card h3{margin:0 0 8px 0;font-size:18px;}
.meta{display:flex;justify-content:space-between;color:var(--muted);font-size:14px;}
.filters{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;}
.filter-pill{background:#fff;border:1px solid #eee;padding:8px 12px;border-radius:999px;font-size:13px;color:var(--muted);}
.footer{padding:30px;text-align:center;color:var(--muted);font-size:14px;}
@media(max-width:640px){ .search-card{flex-direction:column;align-items:stretch;} .card .img{height:180px;} }
</style>
</head>
<body>
  <header class="header">
    <div class="brand">
      <div class="logo">AC</div>
      <div>
        <div style="font-weight:700">AirClone</div>
        <div style="font-size:13px;color:var(--muted)">Rent stays from locals</div>
      </div>
    </div>
    <div style="color:var(--muted)">Welcome — AirClone demo 🌟</div>
  </header>

  <section class="search-wrap">
    <form class="search-card" action="listings.php" method="get">
      <input name="q" placeholder="Destination, city or address" />
      <input name="check_in" type="date" />
      <input name="check_out" type="date" />
      <select name="type">
        <option value="">Any type</option>
        <option>Apartment</option>
        <option>House</option>
        <option>Villa</option>
        <option>Room</option>
        <option>Studio</option>
      </select>
      <button type="submit">Search</button>
    </form>
  </section>

  <main class="container">
    <h2 style="margin:6px 0 14px 0">Featured stays ⭐</h2>
    <div class="grid">
      <?php foreach($featured as $p): ?>
        <div class="card">
          <div class="img">
            <?php
              // fetch hero image
              $imgStmt = $mysqli->prepare("SELECT image_url FROM property_images WHERE property_id=? AND is_hero=1 LIMIT 1");
              $imgStmt->bind_param("i", $p['id']);
              $imgStmt->execute();
              $resImg = $imgStmt->get_result()->fetch_assoc();
              $imgStmt->close();
            ?>
            <img src="<?php echo htmlspecialchars($resImg['image_url'] ?? ''); ?>" style="width:100%;height:100%;object-fit:cover;" alt="">
          </div>
          <div class="body">
            <h3><?php echo htmlspecialchars($p['title']); ?></h3>
            <div class="meta">
              <div><?php echo htmlspecialchars($p['city']); ?></div>
              <div>₨<?php echo number_format($p['price_per_night']); ?>/night</div>
            </div>
            <div style="margin-top:10px;display:flex;justify-content:space-between;align-items:center;">
              <div style="color:var(--muted);font-size:13px">Rating: <?php echo $p['rating']; ?></div>
              <a href="property.php?id=<?php echo $p['id']; ?>" style="text-decoration:none;background:#eef2f7;padding:8px 12px;border-radius:8px;color:#0f172a">View</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <section style="margin-top:28px;">
      <h2 style="margin-bottom:8px">Filters</h2>
      <div class="filters">
        <div class="filter-pill">Free cancellation</div>
        <div class="filter-pill">Wifi</div>
        <div class="filter-pill">Pool</div>
        <div class="filter-pill">Entire place</div>
      </div>
      <p style="color:var(--muted)">Use the search above to find available stays. Click any listing to view details and book. ✅</p>
    </section>
  </main>

  <footer class="footer">Made with ❤️ — AirClone demo</footer>
</body>
</html>
