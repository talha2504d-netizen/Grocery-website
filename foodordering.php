<?php
session_start();

/* =====================================================
   FOOD ORDERING WEBSITE
   FRONTEND + PHP BACKEND + ADMIN PANEL
   Single file: index.php
===================================================== */

/* DATABASE CONFIGURATION */
$host = "localhost";
$dbname = "food_ordering";
$dbuser = "root";
$dbpass = ""; // MySQL password yahan enter karein

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $dbuser,
        $dbpass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

/* CREATE TABLES */
$pdo->exec("
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    address TEXT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) DEFAULT 'Cash on Delivery',
    status VARCHAR(50) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$pdo->exec("
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_name VARCHAR(150) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
)");

/* FOOD MENU */
$foods = [
    1 => ["name"=>"Beef Burger", "price"=>950, "emoji"=>"🍔"],
    2 => ["name"=>"Chicken Burger", "price"=>750, "emoji"=>"🍔"],
    3 => ["name"=>"Chicken Pizza", "price"=>1450, "emoji"=>"🍕"],
    4 => ["name"=>"Creamy Pasta", "price"=>1100, "emoji"=>"🍝"],
    5 => ["name"=>"French Fries", "price"=>450, "emoji"=>"🍟"],
    6 => ["name"=>"Chicken Sandwich", "price"=>750, "emoji"=>"🥪"],
    7 => ["name"=>"Chocolate Waffle", "price"=>650, "emoji"=>"🧇"],
    8 => ["name"=>"Cold Drink", "price"=>200, "emoji"=>"🥤"]
];

$statuses = [
    "Pending",
    "Preparing",
    "Out for Delivery",
    "Delivered",
    "Cancelled"
];

/* HELPER */
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function go($url = "index.php") {
    header("Location: $url");
    exit;
}

/* ADMIN CREDENTIALS */
$adminUsername = "admin";
$adminPassword = "admin123";

/* INITIALIZE CART */
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$message = "";

/* =====================================================
   ADMIN LOGIN
===================================================== */

if (isset($_POST['admin_login'])) {

    $username = $_POST['username'] ?? "";
    $password = $_POST['password'] ?? "";

    if (
        hash_equals($adminUsername, $username) &&
        hash_equals($adminPassword, $password)
    ) {
        session_regenerate_id(true);
        $_SESSION['admin'] = true;
        go("?page=admin");
    } else {
        $message = "Invalid admin username or password.";
    }
}

/* ADMIN LOGOUT */
if (isset($_GET['admin_logout'])) {
    unset($_SESSION['admin']);
    session_regenerate_id(true);
    go();
}

/* =====================================================
   ADMIN UPDATE ORDER STATUS
===================================================== */

if (isset($_POST['update_status']) && !empty($_SESSION['admin'])) {

    $orderId = (int)($_POST['order_id'] ?? 0);
    $newStatus = $_POST['status'] ?? "";

    if (in_array($newStatus, $statuses, true)) {

        $stmt = $pdo->prepare(
            "UPDATE orders SET status=? WHERE id=?"
        );

        $stmt->execute([$newStatus, $orderId]);

        $_SESSION['notice'] = "Order status updated!";
    }

    go("?page=admin");
}

/* =====================================================
   ADD FOOD TO CART
===================================================== */

if (isset($_GET['add'])) {

    $id = (int)$_GET['add'];

    if (isset($foods[$id])) {
        $_SESSION['cart'][$id] =
            min(50, ($_SESSION['cart'][$id] ?? 0) + 1);
    }

    go("?page=menu");
}

/* REMOVE FOOD */
if (isset($_GET['remove'])) {

    $id = (int)$_GET['remove'];

    unset($_SESSION['cart'][$id]);

    go("?page=cart");
}

/* UPDATE CART */
if (isset($_POST['update_cart'])) {

    foreach ($_POST['qty'] ?? [] as $id => $qty) {

        $id = (int)$id;
        $qty = max(1, min(50, (int)$qty));

        if (isset($foods[$id])) {
            $_SESSION['cart'][$id] = $qty;
        }
    }

    go("?page=cart");
}

/* =====================================================
   CALCULATE CART TOTAL
===================================================== */

$cartTotal = 0;
$cartCount = 0;

foreach ($_SESSION['cart'] as $id => $qty) {

    if (isset($foods[$id])) {

        $cartTotal += $foods[$id]['price'] * $qty;
        $cartCount += $qty;
    }
}

/* =====================================================
   CHECKOUT / PLACE ORDER
===================================================== */

if (isset($_POST['place_order'])) {

    $name = trim($_POST['customer_name'] ?? "");
    $phone = trim($_POST['phone'] ?? "");
    $address = trim($_POST['address'] ?? "");

    if (empty($_SESSION['cart'])) {

        $message = "Your cart is empty.";

    } elseif ($name === "" || $phone === "" || $address === "") {

        $message = "Please fill all checkout fields.";

    } else {

        try {

            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                INSERT INTO orders
                (customer_name, phone, address, total, payment_method, status)
                VALUES (?, ?, ?, ?, 'Cash on Delivery', 'Pending')
            ");

            $stmt->execute([
                $name,
                $phone,
                $address,
                $cartTotal
            ]);

            $orderId = $pdo->lastInsertId();

            $itemStmt = $pdo->prepare("
                INSERT INTO order_items
                (order_id, product_name, price, quantity, subtotal)
                VALUES (?, ?, ?, ?, ?)
            ");

            foreach ($_SESSION['cart'] as $id => $qty) {

                if (!isset($foods[$id])) {
                    continue;
                }

                $food = $foods[$id];
                $subtotal = $food['price'] * $qty;

                $itemStmt->execute([
                    $orderId,
                    $food['name'],
                    $food['price'],
                    $qty,
                    $subtotal
                ]);
            }

            $pdo->commit();

            $_SESSION['cart'] = [];

            go("?page=success&id=" . $orderId);

        } catch (Exception $ex) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $message = "Unable to place order. Please try again.";
        }
    }
}

/* =====================================================
   ROUTING
===================================================== */

$page = $_GET['page'] ?? 'home';

$allowedPages = [
    'home',
    'menu',
    'cart',
    'checkout',
    'track',
    'success',
    'admin',
    'adminlogin'
];

if (!in_array($page, $allowedPages, true)) {
    $page = 'home';
}

/* ADMIN ACCESS PROTECTION */
if ($page === 'admin' && empty($_SESSION['admin'])) {
    $page = 'adminlogin';
}

/* =====================================================
   ORDER TRACKING
===================================================== */

$trackedOrder = null;
$trackedItems = [];

if ($page === 'track' && isset($_GET['id'])) {

    $trackId = (int)$_GET['id'];

    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id=?");
    $stmt->execute([$trackId]);

    $trackedOrder = $stmt->fetch();

    if ($trackedOrder) {

        $stmt = $pdo->prepare(
            "SELECT * FROM order_items WHERE order_id=?"
        );

        $stmt->execute([$trackId]);
        $trackedItems = $stmt->fetchAll();
    }
}

/* =====================================================
   SUCCESS PAGE ORDER
===================================================== */

$successOrder = null;

if ($page === 'success') {

    $id = (int)($_GET['id'] ?? 0);

    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id=?");
    $stmt->execute([$id]);

    $successOrder = $stmt->fetch();
}

/* =====================================================
   ADMIN ORDER DATA
===================================================== */

$adminOrders = [];

if ($page === 'admin' && !empty($_SESSION['admin'])) {

    $adminOrders = $pdo->query(
        "SELECT * FROM orders ORDER BY created_at DESC"
    )->fetchAll();
}

/* FLASH MESSAGE */
$notice = $_SESSION['notice'] ?? "";
unset($_SESSION['notice']);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Foodie Restaurant</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
rel="stylesheet">

<style>

body {
    background: #fffaf5;
    font-family: Arial, sans-serif;
}

.navbar {
    background: #a82020;
}

.navbar-brand {
    font-weight: bold;
    font-size: 24px;
}

.hero {
    background: linear-gradient(135deg, #7d1010, #e25822);
    color: white;
    padding: 80px 20px;
    text-align: center;
}

.hero h1 {
    font-size: 45px;
    font-weight: bold;
}

.section {
    padding: 50px 0;
}

.section-title {
    font-weight: bold;
    color: #a82020;
}

.food-card {
    border: none;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,.08);
    transition: .3s;
}

.food-card:hover {
    transform: translateY(-5px);
}

.food-emoji {
    background: #fff0e5;
    text-align: center;
    font-size: 70px;
    padding: 25px;
}

.food-price {
    color: #a82020;
    font-size: 20px;
    font-weight: bold;
}

.btn-food {
    background: #a82020;
    color: white;
}

.btn-food:hover {
    background: #7d1010;
    color: white;
}

.content-box {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,.06);
}

footer {
    background: #222;
    color: white;
    padding: 25px;
    text-align: center;
}

.admin-header {
    background: #222;
    color: white;
    padding: 25px;
    border-radius: 12px;
}

.status-badge {
    font-weight: bold;
}

</style>

</head>

<body>

<!-- NAVBAR -->

<nav class="navbar navbar-expand-lg navbar-dark sticky-top">

<div class="container">

<a class="navbar-brand" href="index.php">
🍽️ Foodie Restaurant
</a>

<button class="navbar-toggler"
data-bs-toggle="collapse"
data-bs-target="#navbarMenu">

<span class="navbar-toggler-icon"></span>

</button>

<div class="collapse navbar-collapse" id="navbarMenu">

<ul class="navbar-nav ms-auto">

<li class="nav-item">
<a class="nav-link" href="?page=home">Home</a>
</li>

<li class="nav-item">
<a class="nav-link" href="?page=menu">Menu</a>
</li>

<li class="nav-item">
<a class="nav-link" href="?page=cart">
Cart (<?= $cartCount ?>)
</a>
</li>

<li class="nav-item">
<a class="nav-link" href="?page=checkout">Checkout</a>
</li>

<li class="nav-item">
<a class="nav-link" href="?page=track">Track Order</a>
</li>

<li class="nav-item">
<a class="nav-link" href="?page=adminlogin">Admin</a>
</li>

</ul>

</div>
</div>

</nav>

<div class="container mt-3">

<?php if ($message): ?>

<div class="alert alert-warning">
<?= e($message) ?>
</div>

<?php endif; ?>

<?php if ($notice): ?>

<div class="alert alert-success">
<?= e($notice) ?>
</div>

<?php endif; ?>

</div>

<!-- =====================================================
     HOME
===================================================== -->

<?php if ($page === 'home'): ?>

<section class="hero">

<div class="container">

<h1>Delicious Food Delivered</h1>

<p class="lead">
Fresh food, fast delivery, great taste!
</p>

<a href="?page=menu" class="btn btn-light btn-lg">
Explore Menu
</a>

</div>

</section>

<section class="section">

<div class="container text-center">

<h2 class="section-title">Welcome to Foodie Restaurant</h2>

<p>
Order your favorite meals online and enjoy delicious food
delivered directly to your doorstep.
</p>

<a href="?page=menu" class="btn btn-food btn-lg mt-3">
Order Now
</a>

</div>

</section>

<?php endif; ?>

<!-- =====================================================
     MENU
===================================================== -->

<?php if ($page === 'menu' || $page === 'home'): ?>

<section class="section" id="menu">

<div class="container">

<h2 class="section-title text-center mb-4">
Our Food Menu
</h2>

<div class="row g-4">

<?php foreach ($foods as $id => $food): ?>

<div class="col-md-3 col-sm-6">

<div class="card food-card h-100">

<div class="food-emoji">
<?= $food['emoji'] ?>
</div>

<div class="card-body text-center">

<h5>
<?= e($food['name']) ?>
</h5>

<p class="food-price">
Rs. <?= number_format($food['price']) ?>
</p>

<a href="?add=<?= $id ?>"
class="btn btn-food w-100">

<i class="bi bi-cart-plus"></i>
Add to Cart

</a>

</div>

</div>

</div>

<?php endforeach; ?>

</div>

</div>

</section>

<?php endif; ?>

<!-- =====================================================
     CART
===================================================== -->

<?php if ($page === 'cart'): ?>

<section class="section">

<div class="container">

<h2 class="section-title mb-4">Shopping Cart</h2>

<div class="content-box">

<?php if (!empty($_SESSION['cart'])): ?>

<form method="POST">

<div class="table-responsive">

<table class="table align-middle">

<thead>
<tr>
<th>Food</th>
<th>Price</th>
<th>Quantity</th>
<th>Subtotal</th>
<th>Remove</th>
</tr>
</thead>

<tbody>

<?php foreach ($_SESSION['cart'] as $id => $qty): ?>

<?php if (isset($foods[$id])): ?>

<tr>

<td><?= e($foods[$id]['name']) ?></td>

<td>Rs. <?= number_format($foods[$id]['price']) ?></td>

<td>

<input type="number"
name="qty[<?= $id ?>]"
value="<?= $qty ?>"
min="1"
max="50"
class="form-control"
style="width:85px">

</td>

<td>
Rs. <?= number_format($foods[$id]['price'] * $qty) ?>
</td>

<td>

<a href="?remove=<?= $id ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Remove item?')">

<i class="bi bi-trash"></i>

</a>

</td>

</tr>

<?php endif; ?>

<?php endforeach; ?>

</tbody>

</table>

</div>

<button name="update_cart" class="btn btn-secondary">
Update Cart
</button>

</form>

<h4 class="text-end mt-3">
Total: Rs. <?= number_format($cartTotal) ?>
</h4>

<a href="?page=checkout" class="btn btn-food float-end">
Proceed to Checkout
</a>

<div class="clearfix"></div>

<?php else: ?>

<h4>Your cart is empty.</h4>

<a href="?page=menu" class="btn btn-food">
Browse Menu
</a>

<?php endif; ?>

</div>

</div>

</section>

<?php endif; ?>

<!-- =====================================================
     CHECKOUT
===================================================== -->

<?php if ($page === 'checkout'): ?>

<section class="section">

<div class="container">

<h2 class="section-title text-center mb-4">
Checkout
</h2>

<div class="row justify-content-center">

<div class="col-md-7">

<div class="content-box">

<form method="POST">

<div class="mb-3">

<label class="form-label">Customer Name</label>

<input type="text"
name="customer_name"
class="form-control"
required>

</div>

<div class="mb-3">

<label class="form-label">Phone Number</label>

<input type="tel"
name="phone"
class="form-control"
required>

</div>

<div class="mb-3">

<label class="form-label">Delivery Address</label>

<textarea name="address"
class="form-control"
rows="3"
required></textarea>

</div>

<div class="mb-3">

<label class="form-label">Payment Method</label>

<input class="form-control"
value="Cash on Delivery"
readonly>

</div>

<h4>
Order Total: Rs. <?= number_format($cartTotal) ?>
</h4>

<button type="submit"
name="place_order"
class="btn btn-food btn-lg w-100"
<?= empty($_SESSION['cart']) ? 'disabled' : '' ?>>

Place Order

</button>

</form>

</div>

</div>

</div>

</div>

</section>

<?php endif; ?>

<!-- =====================================================
     SUCCESS
===================================================== -->

<?php if ($page === 'success'): ?>

<section class="section">

<div class="container">

<div class="content-box text-center">

<?php if ($successOrder): ?>

<h1 class="text-success">🎉 Order Placed!</h1>

<h4>Your Order ID: #<?= $successOrder['id'] ?></h4>

<p>
Status:
<strong><?= e($successOrder['status']) ?></strong>
</p>

<p>
Total:
Rs. <?= number_format($successOrder['total']) ?>
</p>

<a href="?page=track&id=<?= $successOrder['id'] ?>"
class="btn btn-food">

Track Order

</a>

<?php else: ?>

<h3>Order not found.</h3>

<?php endif; ?>

</div>

</div>

</section>

<?php endif; ?>

<!-- =====================================================
     TRACK ORDER
===================================================== -->

<?php if ($page === 'track'): ?>

<section class="section">

<div class="container">

<h2 class="section-title text-center mb-4">
Track Your Order
</h2>

<div class="row justify-content-center">

<div class="col-md-6">

<div class="content-box">

<form method="GET">

<input type="hidden" name="page" value="track">

<label class="form-label">Enter Order ID</label>

<div class="input-group">

<input type="number"
name="id"
class="form-control"
required
placeholder="Enter Order ID">

<button class="btn btn-food">
Track
</button>

</div>

</form>

<?php if (isset($_GET['id'])): ?>

<hr>

<?php if ($trackedOrder): ?>

<h4>Order #<?= $trackedOrder['id'] ?></h4>

<p>
Customer: <?= e($trackedOrder['customer_name']) ?>
</p>

<p>
Status:
<strong><?= e($trackedOrder['status']) ?></strong>
</p>

<p>
Total:
Rs. <?= number_format($trackedOrder['total']) ?>
</p>

<h5>Items</h5>

<ul>

<?php foreach ($trackedItems as $item): ?>

<li>
<?= e($item['product_name']) ?>
× <?= $item['quantity'] ?>
— Rs. <?= number_format($item['subtotal']) ?>
</li>

<?php endforeach; ?>

</ul>

<?php else: ?>

<div class="alert alert-danger">
Order ID not found.
</div>

<?php endif; ?>

<?php endif; ?>

</div>

</div>

</div>

</div>

</section>

<?php endif; ?>

<!-- =====================================================
     ADMIN LOGIN
===================================================== -->

<?php if ($page === 'adminlogin'): ?>

<section class="section">

<div class="container">

<div class="row justify-content-center">

<div class="col-md-5">

<div class="content-box">

<h2 class="section-title text-center">
Admin Login
</h2>

<form method="POST">

<div class="mb-3">

<label class="form-label">Username</label>

<input type="text"
name="username"
class="form-control"
required>

</div>

<div class="mb-3">

<label class="form-label">Password</label>

<input type="password"
name="password"
class="form-control"
required>

</div>

<button name="admin_login"
class="btn btn-food w-100">

Login to Admin Panel

</button>

</form>

<hr>

<p class="text-muted small">
Demo login: admin / admin123
</p>

</div>

</div>

</div>

</div>

</section>

<?php endif; ?>

<!-- =====================================================
     ADMIN DASHBOARD
===================================================== -->

<?php if ($page === 'admin' && !empty($_SESSION['admin'])): ?>

<section class="section">

<div class="container">

<div class="admin-header mb-4">

<div class="d-flex justify-content-between align-items-center flex-wrap">

<div>

<h2>Admin Dashboard</h2>

<p class="mb-0">
Manage customer orders and delivery status
</p>

</div>

<a href="?admin_logout=1"
class="btn btn-danger">

Admin Logout

</a>

</div>

</div>

<?php

$totalOrders = $pdo->query(
    "SELECT COUNT(*) FROM orders"
)->fetchColumn();

$totalSales = $pdo->query(
    "SELECT COALESCE(SUM(total),0) FROM orders WHERE status != 'Cancelled'"
)->fetchColumn();

$pendingOrders = $pdo->query(
    "SELECT COUNT(*) FROM orders WHERE status='Pending'"
)->fetchColumn();

?>

<div class="row g-3 mb-4">

<div class="col-md-4">

<div class="content-box">

<h6>Total Orders</h6>

<h2><?= $totalOrders ?></h2>

</div>

</div>

<div class="col-md-4">

<div class="content-box">

<h6>Pending Orders</h6>

<h2><?= $pendingOrders ?></h2>

</div>

</div>

<div class="col-md-4">

<div class="content-box">

<h6>Total Sales</h6>

<h2>Rs. <?= number_format($totalSales) ?></h2>

</div>

</div>

</div>

<div class="content-box">

<h4 class="mb-3">All Customer Orders</h4>

<div class="table-responsive">

<table class="table table-bordered table-hover align-middle">

<thead class="table-dark">

<tr>
<th>Order</th>
<th>Customer</th>
<th>Phone</th>
<th>Address</th>
<th>Total</th>
<th>Date</th>
<th>Status</th>
<th>Update</th>
<th>Items</th>
</tr>

</thead>

<tbody>

<?php if ($adminOrders): ?>

<?php foreach ($adminOrders as $order): ?>

<tr>

<td>
#<?= $order['id'] ?>
</td>

<td>
<?= e($order['customer_name']) ?>
</td>

<td>
<?= e($order['phone']) ?>
</td>

<td>
<?= e($order['address']) ?>
</td>

<td>
Rs. <?= number_format($order['total']) ?>
</td>

<td>
<?= e($order['created_at']) ?>
</td>

<td>

<span class="badge bg-primary status-badge">
<?= e($order['status']) ?>
</span>

</td>

<td>

<form method="POST">

<input type="hidden"
name="order_id"
value="<?= $order['id'] ?>">

<select name="status"
class="form-select mb-2">

<?php foreach ($statuses as $status): ?>

<option value="<?= e($status) ?>"
<?= $order['status'] === $status ? 'selected' : '' ?>>

<?= e($status) ?>

</option>

<?php endforeach; ?>

</select>

<button name="update_status"
class="btn btn-success btn-sm">

Update

</button>

</form>

</td>

<td>

<button class="btn btn-info btn-sm"
data-bs-toggle="collapse"
data-bs-target="#items<?= $order['id'] ?>">

View Items

</button>

</td>

</tr>

<tr class="collapse"
id="items<?= $order['id'] ?>">

<td colspan="9">

<strong>Order Items:</strong>

<ul>

<?php

$itemStmt = $pdo->prepare(
    "SELECT * FROM order_items WHERE order_id=?"
);

$itemStmt->execute([$order['id']]);

$orderItems = $itemStmt->fetchAll();

foreach ($orderItems as $item):

?>

<li>

<?= e($item['product_name']) ?>

× <?= $item['quantity'] ?>

— Rs. <?= number_format($item['subtotal']) ?>

</li>

<?php endforeach; ?>

</ul>

</td>

</tr>

<?php endforeach; ?>

<?php else: ?>

<tr>

<td colspan="9" class="text-center">
No orders available yet.
</td>

</tr>

<?php endif; ?>

</tbody>

</table>

</div>

</div>

</div>

</section>

<?php endif; ?>

<!-- FOOTER -->

<footer>

<h4>🍽️ Foodie Restaurant</h4>

<p>Fresh Food • Fast Delivery • Great Taste</p>

<p>
&copy; <?= date('Y') ?> Foodie Restaurant
</p>

</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(function(link) {

    link.addEventListener('click', function(event) {

        const target = document.querySelector(this.getAttribute('href'));

        if (target) {
            event.preventDefault();

            target.scrollIntoView({
                behavior: 'smooth'
            });
        }

    });

});

// Checkout phone number validation
document.querySelectorAll('input[name="phone"]').forEach(function(input) {

    input.addEventListener('input', function() {

        this.value = this.value.replace(/[^0-9+]/g, '');

    });

});

</script>

</body>
</html>
