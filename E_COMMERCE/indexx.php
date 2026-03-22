<?php
$conn = mysqli_connect("localhost", "root", "", "eazy_shop");
if (!$conn) { die("Connection failed: " . mysqli_connect_error()); }

// --- ORDER PROCESSING LOGIC ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['place_order'])) {
    $p_name = mysqli_real_escape_string($conn, $_POST['p_name']);
    $qty = (int)$_POST['qty'];
    $price = (float)$_POST['p_price'];
    $total = $qty * $price;
    $c_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $c_phone = mysqli_real_escape_string($conn, $_POST['contact']);
    $c_address = mysqli_real_escape_string($conn, $_POST['address']);

    $sql = "INSERT INTO orders (product_name, quantity, total_price, customer_name, contact_number, address) 
            VALUES ('$p_name', '$qty', '$total', '$c_name', '$c_phone', '$c_address')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Order Placed Successfully!'); window.location.href='indexx.php';</script>";
    }
}

$result = mysqli_query($conn, "SELECT * FROM product");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eazy Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        nav { background: white; padding: 15px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        nav h5 { margin: 0; font-weight: bold; color: #0e1097; }
        
        .row-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; padding: 20px; max-width: 1200px; margin: 0 auto; }
        .product-card { background: white; border-radius: 10px; border: 1px solid #ddd; overflow: hidden; display: flex; flex-direction: column; transition: 0.3s; }
        .product-card:hover { box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .product-card img { width: 100%; height: 180px; object-fit: contain; padding: 10px; }
        .product-info { padding: 15px; text-align: center; }
        .product-name { font-size: 14px; font-weight: bold; margin-bottom: 5px; height: 40px; overflow: hidden; }
        .product-price { color: #ee4d2d; font-size: 18px; font-weight: bold; margin-bottom: 10px; }
        .btn-buy { background-color: #ee4d2d; color: white; width: 100%; border: none; padding: 8px; border-radius: 5px; font-weight: bold; }
        
        /* Modal Image Styling */
        #modal-img { width: 150px; height: 150px; object-fit: contain; margin-bottom: 15px; border-radius: 8px; border: 1px solid #eee; }
    </style>
</head>
<body>

<nav><h5>Eazy Shop</h5></nav>

<div class="row-grid">
    <?php while($product = mysqli_fetch_assoc($result)): ?>
        <div class="product-card">
            <img src="<?php echo $product['image_url']; ?>" alt="Product">
            <div class="product-info">
                <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                <div class="product-price">₱<?php echo number_format($product['price'], 2); ?></div>
                
                <button class="btn btn-buy" 
                        data-bs-toggle="modal" 
                        data-bs-target="#buyModal" 
                        data-name="<?php echo htmlspecialchars($product['name']); ?>" 
                        data-price="<?php echo $product['price']; ?>"
                        data-img="<?php echo $product['image_url']; ?>">
                    Buy Now
                </button>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<div class="modal fade" id="buyModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST">
          <div class="modal-header">
            <h5 class="modal-title">Confirm Purchase</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body text-center">
            
            <img id="modal-img" src="" alt="Product Image">
            
            <h5 id="modal-p-name" class="fw-bold mb-1"></h5>
            <p class="text-muted">Unit Price: ₱<span id="modal-p-price"></span></p>
            <hr>

            <input type="hidden" name="p_name" id="hidden-name">
            <input type="hidden" name="p_price" id="hidden-price">

            <div class="text-start">
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label fw-bold">Quantity</label>
                        <input type="number" name="qty" id="qty" class="form-control" value="1" min="1" required oninput="calculateTotal()">
                    </div>
                    <div class="col-6 text-end">
                        <label class="form-label fw-bold text-danger">Total Price</label>
                        <h4 class="fw-bold">₱<span id="modal-total">0.00</span></h4>
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" placeholder="Enter your full name" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Contact Number</label>
                    <input type="text" name="contact" class="form-control" placeholder="09xxxxxxxxx" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Shipping Address</label>
                    <textarea name="address" class="form-control" rows="2" placeholder="Street, Brgy, City, Province" required></textarea>
                </div>
            </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="place_order" class="btn btn-success px-4">Place Order</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    let currentPrice = 0;
    const buyModal = document.getElementById('buyModal');

    buyModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const name = button.getAttribute('data-name');
        const img = button.getAttribute('data-img');
        currentPrice = parseFloat(button.getAttribute('data-price'));

        // Inject Data to Modal Display
        document.getElementById('modal-img').src = img;
        document.getElementById('modal-p-name').textContent = name;
        document.getElementById('modal-p-price').textContent = currentPrice.toFixed(2);
        
        // Inject Data to Hidden Inputs (for PHP POST)
        document.getElementById('hidden-name').value = name;
        document.getElementById('hidden-price').value = currentPrice;

        document.getElementById('qty').value = 1;
        calculateTotal();
    });

    function calculateTotal() {
        const qty = document.getElementById('qty').value;
        const total = currentPrice * qty;
        document.getElementById('modal-total').textContent = total.toLocaleString(undefined, {minimumFractionDigits: 2});
    }
</script>

</body>
</html>