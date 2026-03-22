<?php
// ============================================
// DATABASE CONNECTION
// ============================================
include 'db.php';

// ============================================
// SEARCH LOGIC
// ?? = null coalescing operator
// Gets 'search' value if it exists, otherwise empty string
// ============================================
$search = $_GET['search'] ?? '';
$like = "%$search%";  // % = wildcard for partial matching

// ============================================
// QUERY BUILDER
// Ternary operator: (condition) ? true : false
// If there is a search, use LIKE. If not, SELECT ALL
// ============================================
$sql = $search ? "SELECT * FROM flags WHERE country_name LIKE ?" : "SELECT * FROM flags";

$stmt = $conn->prepare($sql);           // Prepare SQL for safety against SQL injection
if ($search) $stmt->bind_param("s", $like);  // "s" = string parameter
$stmt->execute();                       // Execute query
$flags = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);  // Fetch all as array
?>
<!DOCTYPE html>
<html>
<head>
<title>Flag Carousel</title>
<style>
/* ============================================
   CSS RESET - Remove default margins/paddings
   ============================================ */
* { margin: 0; padding: 0; box-sizing: border-box; }

/* ============================================
   BODY - Center all content
   flex-direction: column = vertical layout
   align-items: center = center horizontally
   ============================================ */
body {
    font-family: Arial, sans-serif;
    background: #f0f2f5;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px;
}

/* ============================================
   SEARCH BOX
   ============================================ */
.search-box { margin-bottom: 20px; }

.search-box input {
    padding: 10px;
    width: 220px;
    border-radius: 5px;
    border: 1px solid #ccc;
}

.search-box button {
    padding: 10px 15px;
    border: none;
    background: #007bff;
    color: white;
    border-radius: 5px;
    cursor: pointer;
}

.search-box a button { background: #6c757d; }

/* ============================================
   CONTAINER - Fixed size for stability
   position: relative = for absolute nav buttons
   ============================================ */
.container {
    width: 500px;
    height: 400px;
    background: #fff;
    padding: 20px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

/* ============================================
   SLIDE - Each flag item
   display: none = hidden by default
   .active class = show current slide
   ============================================ */
.slide {
    display: none;
    text-align: center;
    width: 100%;
}

.slide.active { display: block; }

/* ============================================
   FLAG IMAGE - NO DEFORMATION
   object-fit: contain = entire image visible
   max-height = limit height to prevent oversizing
   width: auto = maintain aspect ratio
   ============================================ */
.slide img {
    width: auto;
    max-width: 100%;
    height: auto;
    max-height: 220px;
    object-fit: contain;
}

.desc { margin-top: 12px; }
.desc h3 { margin: 8px 0 5px; }
.desc p { font-size: 14px; color: #555; }

/* ============================================
   NAV BUTTONS (< >)
   position: absolute = inside the container
   top: 50% + transform = center vertically
   ============================================ */
.prev, .next {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0,0,0,0.7);
    color: white;
    border: none;
    padding: 12px;
    cursor: pointer;
    border-radius: 50%;
    font-size: 16px;
}

.prev { left: 10px; }
.next { right: 10px; }
.prev:hover, .next:hover { background: black; }

/* ============================================
   NO RESULT MESSAGE
   ============================================ */
.no-result {
    text-align: center;
    color: #666;
    font-size: 16px;
}
</style>
</head>
<body>

<!-- ============================================
     SEARCH FORM
     method="GET" = data in URL (can be bookmarked)
     htmlspecialchars() = prevent XSS attacks
     ============================================ -->
<div class="search-box">
    <form method="GET">
        <input type="text" name="search" placeholder="Search country..." 
               value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Search</button>
        <a href="index.php"><button type="button">Clear</button></a>
    </form>
</div>

<!-- ============================================
     CAROUSEL CONTAINER
     ============================================ -->
<div class="container">

<?php 
// ============================================
// CHECK IF THERE ARE RESULTS
// empty() = true if array has no items
// ============================================
if (empty($flags)): 
?>
    <div class="no-result">No flags found.</div>
<?php else: ?>
    
    <?php 
    // ============================================
    // LOOP THROUGH ALL FLAGS
    // foreach($array as $index => $value)
    // $i === 0 ? 'active' : '' = ternary, only first slide is active
    // ============================================
    foreach ($flags as $i => $row): 
    ?>
    <div class="slide <?= $i === 0 ? 'active' : '' ?>">
        <!-- image column in database = path to flag image -->
        <img src="<?= $row['image'] ?>" alt="<?= $row['country_name'] ?>">
        <div class="desc">
            <h3><?= $row['country_name'] ?></h3>
            <p><?= $row['description'] ?></p>
        </div>
    </div>
    <?php endforeach; ?>
    
    <?php 
    // ============================================
    // NAV BUTTONS - SHOW ONLY IF:
    // 1. No search (empty($search))
    // 2. More than 1 flag (count($flags) > 1)
    // ============================================
    if (empty($search) && count($flags) > 1): 
    ?>
    <button class="prev" onclick="changeSlide(-1)">❮</button>
    <button class="next" onclick="changeSlide(1)">❯</button>
    <?php endif; ?>
    
<?php endif; ?>
</div>

<script>
// ============================================
// JAVASCRIPT CAROUSEL
// ============================================

// Select all slides, store in array
const slides = document.querySelectorAll('.slide');
let currentIndex = 0;

/*
 * showSlide() - Display specific slide
 * i = index of slide to show
 * Remove 'active' from all, add 'active' to current
 */
function showSlide(i) {
    slides.forEach(slide => slide.classList.remove('active'));
    slides[i].classList.add('active');
}

/*
 * changeSlide() - Move to next or previous slide
 * step = +1 for next, -1 for previous
 * Modulo (%) = loop back to start or end
 */
function changeSlide(step) {
    // (current + step + total) % total = circular navigation
    currentIndex = (currentIndex + step + slides.length) % slides.length;
    showSlide(currentIndex);
}
</script>

</body>
</html>