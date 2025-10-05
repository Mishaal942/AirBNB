<?php
require 'db.php';

// read filters from GET
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$type = isset($_GET['type']) ? trim($_GET['type']) : '';
$min_price = isset($_GET['min_price']) && is_numeric($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? (float)$_GET['max_price'] : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'rating_desc';

// build base query
$sql = "SELECT id, title, slug, city, price_per_night, rating, property_type FROM properties WHERE 1=1";
$params = [];
$types = "";

if($q !== ''){
  $sql .= " AND (title LIKE ? OR city LIKE ? OR address LIKE ?)";
  $like = "%{$q}%";
  $params[] = $like; $params[] = $like; $params[] = $like;
  $types .= "sss";
}

if($type !== ''){
  $sql .= " AND property_type = ?";
  $params[] = $type;
  $types .= "s";
}

if($min_price > 0){
  $sql .= " AND price_per_night >= ?";
  $params[] = $min_price;
  $types .= "d";
}
if($max_price > 0){
  $sql .= " AND price_per_night <= ?";
  $params[] = $max_price;
  $types .= "d";
}

// sort
if($sort === 'price_asc'){
  $sql .= " ORDER BY price_per_night ASC";
} elseif($sort === 'price_desc'){
  $sql .= " ORDER BY price_per_night DESC";
} else {
  $sql .= " ORDER BY rating DESC";
}

$stmt = $mysqli->prepare($sql);
if($params){
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();
$listings = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Search results — AirClone</title>
<style>
:root{--accent:#ff585d;--muted:#6b7280;--bg:#f7fafc;--card:#fff;--radius:12px;}
*{box-sizing:border-box;font-family:Inter,system-ui,Arial;}
body{margin:0;background:var(--bg);color:#0f172a;}
.container{max-width:1200px;margin:20px auto;padding:0 16px;}
.header{display:flex;align-items:center;justify-content:space-between;padding:18px 0;}
.results{display:grid;grid-template-columns:1fr 350px;gap:18px;}
.listings{display:grid;gap:14px;}
.card{background:var(--card);border-radius:12px;overflow:hidden;display:flex;gap:12px;padding:12px;align-items:center;box-shadow:0 6px 16px rgba(2,6,23,0.04);}
.card img{width:160px;height:100px;object-fit:cover;border-radius:8px;}
.side{background:var(--card);padding:14px;border-radius:12px;box-shadow:0 6px 16px rgba(2,6,23,0.04);}
.filter-row{display:flex;gap:8px;margin-bottom:10px;}
.btn{background:var(--accent);color:#fff;padding:10px 12px;border-radius:10px;border:none;cursor:pointer;}
.small{font-size:13px;color:var(--muted);}
@media(max-width:900px){ .results{grid-template-columns:1fr;} .side{order:2;} }
</style>
</head>
<body>
<div class="container">
  <div class="header">
    <div>
      <h2>Search results</h2>
      <div class="small">Showing <?php echo count($listings); ?> stays</div>
    </div>
    <div>
      <a href="index.php" style="text-decoration:none;color:var(--muted)">← New search</a>
    </div>
  </div>

  <div class="results">
    <div>
      <div style="display:flex;gap:10px;align-items:center;margin-bottom:12px;">
        <form method="get" action="listings.php" style="display:flex;gap:8px;align-items:center;">
          <input name="q" placeholder="Destination" value="<?php echo htmlspecialchars($q); ?>" style="padding:8px;border-radius:8px;border:1px solid #e6e9ef;">
          <select name="type" style="padding:8px;border-radius:8px;border:1px solid #e6e9ef;">
            <option value="">Any type</option>
            <option <?php if($type=='Apartment') echo 'selected'; ?>>Apartment</option>
            <option <?php if($type=='House') echo 'selected'; ?>>House</option>
            <option <?php if($type=='Villa') echo 'selected'; ?>>Villa</option>
            <option <?php if($type=='Room') echo 'selected'; ?>>Room</option>
            <option <?php if($type=='Studio') echo 'selected'; ?>>Studio</option>
          </select>
          <select name="sort" style="padding:8px;border-radius:8px;border:1px solid #e6e9ef;">
            <option value="rating_desc" <?php if($sort=='rating_desc') echo 'selected'; ?>>Best rated</option>
            <option value="price_asc" <?php if($sort=='price_asc') echo 'selected'; ?>>Price: low to high</option>
            <option value="price_desc" <?php if($sort=='price_desc') echo 'selected'; ?>>Price: high to low</option>
          </select>
          <button class="btn" type="submit">Apply</button>
        </form>
      </div>

      <div class="listings">
        <?php if(empty($listings)): ?>
          <div style="background:#fff;padding:18px;border-radius:12px;">No results found. Try widening filters.</div>
        <?php endif; ?>

        <?php foreach($listings as $p): ?>
          <?php
            $imgStmt = $mysqli->prepare("SELECT image_url FROM property_images WHERE property_id=? AND is_hero=1 LIMIT 1");
            $imgStmt->bind_param("i", $p['id']);
            $imgStmt->execute();
            $rimg = $imgStmt->get_result()->fetch_assoc();
            $imgStmt->close();
            $img = $rimg['image_url'] ?? '';
          ?>
          <div class="card">
            <img src="<?php echo htmlspecialchars($img); ?>" alt="">
            <div style="flex:1;">
              <div style="display:flex;justify-content:space-between;align-items:start;">
                <div>
                  <a href="property.php?id=<?php echo $p['id']; ?>" style="text-decoration:none;color:#0f172a;font-weight:700;"><?php echo htmlspecialchars($p['title']); ?></a>
                  <div class="small"><?php echo htmlspecialchars($p['city']); ?> · <?php echo htmlspecialchars($p['property_type']); ?></div>
                </div>
                <div style="text-align:right">
                  <div style="font-weight:700">₨<?php echo number_format($p['price_per_night']); ?></div>
                  <div class="small">Rating: <?php echo $p['rating']; ?></div>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <aside class="side">
      <h3 style="margin-top:0">Filters</h3>
      <form method="get" action="listings.php">
        <div class="filter-row">
          <input name="min_price" placeholder="Min price" value="<?php echo htmlspecialchars($min_price); ?>" style="padding:8px;border-radius:8px;border:1px solid #e6e9ef;width:100%;">
          <input name="max_price" placeholder="Max price" value="<?php echo htmlspecialchars($max_price); ?>" style="padding:8px;border-radius:8px;border:1px solid #e6e9ef;width:100%;">
        </div>
        <div style="margin-bottom:10px;">
          <label class="small">Amenities (sample)</label>
          <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:8px;">
            <label style="font-size:13px;"><input type="checkbox" disabled> WiFi</label>
            <label style="font-size:13px;"><input type="checkbox" disabled> Pool</label>
            <label style="font-size:13px;"><input type="checkbox" disabled> Kitchen</label>
          </div>
        </div>
        <button class="btn" type="submit">Apply filters</button>
      </form>
    </aside>
  </div>
</div>
</body>
</html>
