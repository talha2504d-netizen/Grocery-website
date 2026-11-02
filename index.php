<?php
/* ============================================================
   GROCERY STORE - Single File PHP Application (LIGHT GREEN)
   ============================================================
   SQL SCHEMA (auto-created on first run):

   CREATE DATABASE IF NOT EXISTS grocery_store;
   USE grocery_store;

   CREATE TABLE IF NOT EXISTS users (
     id INT AUTO_INCREMENT PRIMARY KEY,
     name VARCHAR(100) NOT NULL,
     email VARCHAR(100) UNIQUE NOT NULL,
     password VARCHAR(255) NOT NULL,
     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );

   CREATE TABLE IF NOT EXISTS products (
     id INT AUTO_INCREMENT PRIMARY KEY,
     name VARCHAR(100) NOT NULL,
     category VARCHAR(50) NOT NULL,
     price DECIMAL(10,2) NOT NULL,
     stock INT DEFAULT 0,
     unit VARCHAR(20) DEFAULT 'pcs',
     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );

   CREATE TABLE IF NOT EXISTS orders (
     id INT AUTO_INCREMENT PRIMARY KEY,
     customer_name VARCHAR(100) NOT NULL,
     phone VARCHAR(20) NOT NULL,
     address TEXT NOT NULL,
     payment_method VARCHAR(30) NOT NULL,
     total_amount DECIMAL(10,2) NOT NULL,
     status VARCHAR(20) DEFAULT 'Pending',
     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );

   CREATE TABLE IF NOT EXISTS order_items (
     id INT AUTO_INCREMENT PRIMARY KEY,
     order_id INT NOT NULL,
     product_id INT NOT NULL,
     quantity INT NOT NULL,
     price DECIMAL(10,2) NOT NULL,
     FOREIGN KEY (order_id) REFERENCES orders(id),
     FOREIGN KEY (product_id) REFERENCES products(id)
   );
   ============================================================ */

session_start();

function cartCount() {
    $c = 0; foreach ($_SESSION['cart'] ?? [] as $q) $c += $q; return $c;
}

/* ---------- DATABASE CONNECTION ---------- */
$DB_HOST = 'sql200.infinityfree.com';
$DB_USER = 'if0_43311822';
$DB_PASS = 'NqUGZiY8aN';
$DB_NAME = 'if0_43311822_grocery_store';

try {
    $pdo = new PDO("mysql:host=$DB_HOST;charset=utf8mb4", $DB_USER, $DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$DB_NAME` CHARACTER SET utf8mb4");
    $pdo->exec("USE `$DB_NAME`");

    /* ---------- USERS TABLE ---------- */
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        category VARCHAR(50) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        stock INT DEFAULT 0,
        unit VARCHAR(20) DEFAULT 'pcs',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_name VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        address TEXT NOT NULL,
        payment_method VARCHAR(30) NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        status VARCHAR(20) DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id),
        FOREIGN KEY (product_id) REFERENCES products(id)
    )");

    /* ---------- SEED PRODUCTS (only first time) ---------- */
    $count = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    if ($count == 0) {
        $items = [
            ['Atta (Flour)', 'Grains & Flour', 1450, 25, '10kg bag'],
            ['Sugar', 'Grains & Flour', 160, 40, '1kg'],
            ['Maida', 'Grains & Flour', 120, 30, '1kg'],
            ['Cornflour', 'Grains & Flour', 180, 20, '500g'],
            ['Bajra', 'Grains & Flour', 220, 15, '1kg'],
            ['Ghee', 'Cooking Essentials', 1750, 18, '1kg'],
            ['Cooking Oil', 'Cooking Essentials', 620, 35, '3L'],
            ['Vinegar Sauce', 'Cooking Essentials', 150, 0, '500ml'],
            ['Baking Custard Powder', 'Grains & Flour', 250, 12, '300g'],
            ['Tea', 'Beverages', 850, 28, '900g'],
            ['Milo', 'Beverages', 950, 22, '400g'],
            ['Horlicks', 'Beverages', 1150, 14, '500g'],
            ['Ovaltine', 'Beverages', 1050, 16, '400g'],
            ['Milk', 'Dairy', 280, 50, '1L'],
            ['Cheese', 'Dairy', 550, 20, '200g'],
            ['Peanut Butter', 'Dairy', 780, 10, '340g'],
            ['Chocolate Spread', 'Dairy', 890, 15, '350g'],
            ['Jam', 'Dairy', 420, 25, '450g'],
            ['Jelly', 'Dairy', 120, 30, '100g'],
            ['Ketchup', 'Sauces & Spices', 320, 32, '800g'],
            ['Mayonnaise', 'Sauces & Spices', 540, 26, '475ml'],
            ['Chilli Sauce', 'Sauces & Spices', 290, 8, '400g'],
            ['Soya Sauce', 'Sauces & Spices', 310, 0, '500ml'],
            ['Spices (Masalay)', 'Sauces & Spices', 95, 60, '100g'],
            ['Black Chickpeas', 'Pulses & Dry Items', 240, 22, '1kg'],
            ['White Chickpeas', 'Pulses & Dry Items', 230, 24, '1kg'],
            ['Dry Fruits Mix', 'Pulses & Dry Items', 1850, 12, '500g'],
            ['Biscuits', 'Snacks', 60, 100, 'pack'],
            ['Cake', 'Snacks', 150, 45, 'pack'],
            ['Toffee', 'Snacks', 5, 200, 'pcs'],
            ['Bubble Gum', 'Snacks', 10, 150, 'pcs'],
            ['Detergent Washing Powder', 'Household', 480, 28, '1kg'],
            ['Dish Washing Liquid (Lemon Max)', 'Household', 250, 33, '500ml'],
            ['Lemon Max Liquid', 'Household', 350, 21, '750ml'],
            ['Mosquito Coil', 'Household', 130, 40, 'pack'],
            ['Scotch Brite', 'Household', 90, 55, 'pcs'],
            ['Steel Wool', 'Household', 45, 48, 'pack'],
            ['Soap', 'Personal Care', 110, 70, 'pcs'],
            ['Shampoo', 'Personal Care', 380, 30, '350ml'],
            ['Tissue Box', 'Personal Care', 160, 42, 'box'],
        ];
        $stmt = $pdo->prepare("INSERT INTO products (name, category, price, stock, unit) VALUES (?,?,?,?,?)");
        foreach ($items as $it) { $stmt->execute($it); }
    }
} catch (PDOException $e) {
    die("<div class='alert alert-danger m-4'>Database Connection Failed: " . $e->getMessage() . "</div>");
}

/* ---------- CART (SESSION BASED) ---------- */
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

$action = $_GET['action'] ?? '';

/* ---------- AJAX ADD TO CART (no page refresh) ---------- */
if ($action === 'add' && isset($_GET['id'])) {
    $pid = (int)$_GET['id'];
    $isAjax = isset($_GET['ajax']);
    $p = $pdo->prepare("SELECT stock FROM products WHERE id=?");
    $p->execute([$pid]);
    $prod = $p->fetch();

    if ($prod && $prod['stock'] > 0) {
        $inCart = $_SESSION['cart'][$pid] ?? 0;
        if ($inCart < $prod['stock']) {
            $_SESSION['cart'][$pid] = $inCart + 1;
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['ok' => true, 'count' => cartCount(), 'msg' => 'Item added to cart!']);
                exit;
            }
            $_SESSION['msg'] = "Item added to cart!";
        } else {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['ok' => false, 'error' => 'Only ' . $prod['stock'] . ' in stock!']);
                exit;
            }
            $_SESSION['err'] = "Only " . $prod['stock'] . " in stock!";
        }
    } else {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'error' => 'This item is out of stock!']);
            exit;
        }
    }
    if (!$isAjax) { header("Location: " . strtok($_SERVER['REQUEST_URI'], '?')); exit; }
}

if ($action === 'inc' && isset($_GET['id'])) {
    $pid = (int)$_GET['id'];
    $p = $pdo->prepare("SELECT stock FROM products WHERE id=?");
    $p->execute([$pid]); $prod = $p->fetch();
    if ($prod && ($_SESSION['cart'][$pid] ?? 0) < $prod['stock']) $_SESSION['cart'][$pid]++;
    header("Location: ?page=cart"); exit;
}

if ($action === 'dec' && isset($_GET['id'])) {
    $pid = (int)$_GET['id'];
    if (isset($_SESSION['cart'][$pid])) {
        $_SESSION['cart'][$pid]--;
        if ($_SESSION['cart'][$pid] <= 0) unset($_SESSION['cart'][$pid]);
    }
    header("Location: ?page=cart"); exit;
}

if ($action === 'remove' && isset($_GET['id'])) {
    unset($_SESSION['cart'][(int)$_GET['id']]);
    header("Location: ?page=cart"); exit;
}

if ($action === 'clear') {
    $_SESSION['cart'] = [];
    header("Location: ?page=cart"); exit;
}

/* ---------- PLACE ORDER (BACKEND) ---------- */
$orderSuccess = null;
if (isset($_POST['place_order'])) {
    $name    = trim($_POST['customer_name'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $payment = $_POST['payment_method'] ?? 'Cash on Delivery';

    if (empty($_SESSION['cart'])) {
        $_SESSION['err'] = "Your cart is empty!";
    } elseif ($name && $phone && $address) {
        try {
            $pdo->beginTransaction();

            $total = 0; $cartDetails = [];
            foreach ($_SESSION['cart'] as $pid => $qty) {
                $ps = $pdo->prepare("SELECT * FROM products WHERE id=?");
                $ps->execute([$pid]); $prod = $ps->fetch();
                if ($prod && $prod['stock'] >= $qty) {
                    $cartDetails[] = ['id'=>$pid, 'qty'=>$qty, 'price'=>$prod['price']];
                    $total += $prod['price'] * $qty;
                }
            }

            $o = $pdo->prepare("INSERT INTO orders (customer_name, phone, address, payment_method, total_amount) VALUES (?,?,?,?,?)");
            $o->execute([$name, $phone, $address, $payment, $total]);
            $orderId = $pdo->lastInsertId();

            $oi = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?,?,?,?)");
            $up = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id=?");
            foreach ($cartDetails as $d) {
                $oi->execute([$orderId, $d['id'], $d['qty'], $d['price']]);
                $up->execute([$d['qty'], $d['id']]);
            }

            $pdo->commit();
            $_SESSION['cart'] = [];
            $orderSuccess = $orderId;
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['err'] = "Order could not be placed, please try again.";
        }
    } else {
        $_SESSION['err'] = "All fields are required!";
    }
}

/* ---------- FETCH PRODUCTS ---------- */
$products = $pdo->query("SELECT * FROM products ORDER BY category, name")->fetchAll(PDO::FETCH_ASSOC);

$page = $_GET['page'] ?? 'home';
$msg  = $_SESSION['msg'] ?? null; unset($_SESSION['msg']);
$err  = $_SESSION['err'] ?? null; unset($_SESSION['err']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Apna Grocery Store</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
    :root {
        --green: #16a34a;
        --green-dark: #15803d;
        --green-light: #f0fdf4;
        --amber: #f59e0b;          /* second accent colour */
        --amber-light: #fef3c7;
        --card-border: #d1fae5;
    }
    body {
        background: var(--green-light);
        color: #1f2937;
        font-family: 'Segoe UI', sans-serif;
    }

    /* ---------- NAVBAR (green gradient) ---------- */
    .navbar {
        background: linear-gradient(90deg, #14532d, var(--green), #22c55e);
        box-shadow: 0 4px 20px rgba(22, 163, 74, .35);
    }
    .navbar-brand { font-weight: 700; color: #fff !important; letter-spacing: .5px; }
    .nav-link { color: #f0fdf4 !important; }
    .nav-link:hover { color: #fff !important; }

    /* ---------- CARDS ---------- */
    .card {
        background: #ffffff;
        border: 1px solid var(--card-border);
        border-radius: 14px;
        color: #1f2937;
        transition: transform .2s, box-shadow .2s;
    }
    .card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(22, 163, 74, .2);
        border-color: var(--green);
    }
    .price-tag { font-size: 1.2rem; font-weight: 700; color: var(--green); }
    .unit-badge { background: #dcfce7; color: var(--green-dark); font-size: .75rem; }
    .qty-badge { background: var(--amber-light); color: #b45309; font-size: .75rem; }
    .product-emoji { font-size: 2.8rem; }
    .out-stock { background: #fee2e2 !important; color: #b91c1c !important; border: none; }
    .text-success { color: var(--green) !important; }

    /* ---------- HERO ---------- */
    .hero {
        background: linear-gradient(135deg, #14532d, var(--green), #86efac);
        color: #fff;
        border-radius: 14px;
        box-shadow: 0 8px 30px rgba(22, 163, 74, .3);
    }

    /* ---------- TABLES ---------- */
    .table { color: #1f2937; background: #fff; }
    .table thead { background: var(--green); color: #fff; }
    .table-hover tbody tr:hover { background: #f0fdf4; }
    .table-light { background: #dcfce7 !important; color: #14532d; }
    .table-striped > tbody > tr:nth-of-type(odd) { background: #f8fffa; }

    /* ---------- BUTTONS ---------- */
    .btn-success {
        background: linear-gradient(90deg, var(--green-dark), var(--green));
        border: none;
    }
    .btn-success:hover { background: var(--green-dark); box-shadow: 0 4px 15px rgba(22,163,74,.4); }
    .btn-outline-secondary { color: var(--green-dark); border-color: var(--green); }
    .btn-outline-secondary:hover { background: var(--green); color: #fff; }
    .btn-outline-danger { color: #b91c1c; border-color: #fecaca; }
    .btn-outline-danger:hover { background: #fee2e2; color: #b91c1c; }

    /* ---------- FORM / PAYMENT ---------- */
    .form-control, .form-control:focus {
        background: #fff; color: #1f2937; border: 1px solid #bbf7d0;
    }
    .form-control::placeholder { color: #9ca3af; }
    .form-control:focus { border-color: var(--green); box-shadow: 0 0 0 .2rem rgba(22,163,74,.15); }
    .form-label { color: var(--green-dark); }
    .payment-card { border: 2px solid #bbf7d0; background: #fff; color: #1f2937; }
    .payment-card:hover { border-color: var(--green); background: var(--green-light); }

    /* ---------- ALERTS ---------- */
    .alert-success { background: #f0fdf4; color: #14532d; border-left: 4px solid var(--green); }
    .alert-danger  { background: #fef2f2; color: #7f1d1d; border-left: 4px solid #ef4444; }
    .alert-info    { background: #fffbeb; color: #78350f; border-left: 4px solid var(--amber); }
    .alert a { color: var(--green-dark); }

    /* ---------- TOAST ---------- */
    #toast {
        position: fixed; bottom: 25px; left: 50%; transform: translateX(-50%) translateY(100px);
        background: var(--green); color: #fff; padding: 12px 28px; border-radius: 30px;
        font-weight: 600; box-shadow: 0 6px 20px rgba(22,163,74,.4);
        opacity: 0; transition: all .3s ease; z-index: 9999; pointer-events: none;
    }
    #toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
    #toast.error { background: #dc2626; }

    footer { background: #14532d; color: #d1fae5; border-top: 2px solid var(--amber); }
    .badge.bg-info { background: var(--amber-light) !important; color: #b45309 !important; }
</style>
</head>
<body>

<!-- ================= NAVBAR ================= -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
  <div class="container">
    <a class="navbar-brand" href="?"><i class="bi bi-cart3"></i> Apna Grocery Store</a>
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="?"><i class="bi bi-shop"></i> Products</a></li>
        <li class="nav-item"><a class="nav-link" href="?page=orders"><i class="bi bi-receipt"></i> My Orders</a></li>
      </ul>
      <a href="?page=cart" class="btn btn-light position-relative">
        <i class="bi bi-cart-fill"></i> Cart
        <span id="cartCount" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" <?= cartCount() > 0 ? '' : 'style="display:none"' ?>>
          <?= cartCount() ?>
        </span>
      </a>
    </div>
  </div>
</nav>

<div id="toast"></div>

<div class="container py-4" style="max-width: 1200px;">

<?php if ($msg): ?><div class="alert alert-success"><i class="bi bi-check-circle"></i> <?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($err) ?></div><?php endif; ?>

<?php
/* ================================================================
   PAGE: HOME - PRODUCTS LIST
   ================================================================ */
if ($page === 'home'):
?>

<div class="hero text-center p-5 mb-4">
  <h1 class="fw-bold">Fresh Grocery, Delivered Home! 🛒</h1>
  <p class="mb-0">Flour, oil, spices, dry fruits and much more — all in one place.</p>
</div>

<?php
$categories = [];
foreach ($products as $p) $categories[$p['category']][] = $p;
$emojis = ['Grains & Flour'=>'🌾','Cooking Essentials'=>'🫕','Beverages'=>'🍵','Dairy'=>'🥛','Sauces & Spices'=>'🌶️','Pulses & Dry Items'=>'🫘','Snacks'=>'🍪','Household'=>'🧼','Personal Care'=>'🧴'];
foreach ($categories as $cat => $items):
?>
<h4 class="mt-4 mb-3 fw-bold text-success">
  <?= $emojis[$cat] ?? '🛍️' ?> <?= htmlspecialchars($cat) ?>
</h4>
<div class="row g-3">
  <?php foreach ($items as $p): ?>
  <div class="col-6 col-md-4 col-lg-3">
    <div class="card h-100 text-center p-3">
      <div class="d-flex justify-content-between align-items-start">
        <span class="badge unit-badge"><?= htmlspecialchars($p['unit']) ?></span>
        <?php if ($p['stock'] > 0): ?>
          <span class="badge qty-badge">Qty: <?= (int)$p['stock'] ?></span>
        <?php endif; ?>
      </div>
      <div class="product-emoji my-2"><?= $emojis[$p['category']] ?? '🛒' ?></div>
      <h6 class="fw-bold"><?= htmlspecialchars($p['name']) ?></h6>
      <div class="price-tag mb-2">Rs. <?= number_format($p['price']) ?></div>
      <?php if ($p['stock'] <= 0): ?>
        <button class="btn out-stock" disabled><i class="bi bi-x-circle"></i> Out of Stock</button>
      <?php else: ?>
        <button class="btn btn-success" onclick="addToCart(<?= $p['id'] ?>, this)">
          <i class="bi bi-cart-plus"></i> Add to Cart
        </button>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endforeach; ?>


<?php
/* ================================================================
   PAGE: CART
   ================================================================ */
elseif ($page === 'cart'):
?>
<h3 class="mb-3 fw-bold"><i class="bi bi-cart-fill"></i> Your Cart</h3>

<?php if (empty($_SESSION['cart'])): ?>
  <div class="alert alert-info">Your cart is empty. <a href="?">Start shopping</a>.</div>
<?php else:
    $total = 0;
?>
<div class="table-responsive">
<table class="table table-hover rounded shadow-sm">
  <thead><tr><th>Item</th><th>Price</th><th>Qty</th><th>Subtotal</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($_SESSION['cart'] as $pid => $qty):
      $ps = $pdo->prepare("SELECT * FROM products WHERE id=?");
      $ps->execute([$pid]); $p = $ps->fetch();
      if (!$p) continue;
      $sub = $p['price'] * $qty; $total += $sub;
  ?>
    <tr>
      <td><?= htmlspecialchars($p['name']) ?></td>
      <td>Rs. <?= number_format($p['price']) ?></td>
      <td>
        <a href="?action=dec&id=<?= $pid ?>" class="btn btn-sm btn-outline-secondary">−</a>
        <strong class="mx-1"><?= $qty ?></strong>
        <a href="?action=inc&id=<?= $pid ?>" class="btn btn-sm btn-outline-secondary">+</a>
      </td>
      <td>Rs. <?= number_format($sub) ?></td>
      <td><a href="?action=remove&id=<?= $pid ?>" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
  <tfoot class="table-light">
    <tr>
      <th colspan="3" class="text-end">Total:</th>
      <th class="text-success fs-5">Rs. <?= number_format($total) ?></th>
      <th><a href="?action=clear" class="btn btn-sm btn-outline-danger">Clear</a></th>
    </tr>
  </tfoot>
</table>
</div>
<div class="text-end">
  <a href="?" class="btn btn-outline-secondary">Continue Shopping</a>
  <a href="?page=checkout" class="btn btn-success btn-lg"><i class="bi bi-bag-check"></i> Checkout / Place Order</a>
</div>
<?php endif; ?>


<?php
/* ================================================================
   PAGE: CHECKOUT + PAYMENT METHOD
   ================================================================ */
elseif ($page === 'checkout'):
if (empty($_SESSION['cart'])) { header("Location: ?page=cart"); exit; }
$total = 0;
foreach ($_SESSION['cart'] as $pid => $qty) {
    $ps = $pdo->prepare("SELECT price FROM products WHERE id=?");
    $ps->execute([$pid]); $total += $ps->fetchColumn() * $qty;
}
?>
<h3 class="mb-3 fw-bold"><i class="bi bi-credit-card"></i> Checkout — Payment Method</h3>
<div class="row justify-content-center">
  <div class="col-md-7">
    <div class="card p-4">
      <h5 class="fw-bold">Order Details</h5>
      <p class="text-muted">Total Amount: <strong class="text-success fs-4">Rs. <?= number_format($total) ?></strong></p>
      <hr style="border-color: #d1fae5;">
      <form method="POST">
        <div class="mb-3">
          <label class="form-label fw-bold">Full Name</label>
          <input type="text" name="customer_name" class="form-control" required placeholder="Enter your full name">
        </div>
        <div class="mb-3">
          <label class="form-label fw-bold">Phone Number</label>
          <input type="tel" name="phone" class="form-control" required placeholder="03xxxxxxxxx">
        </div>
        <div class="mb-3">
          <label class="form-label fw-bold">Delivery Address</label>
          <textarea name="address" class="form-control" rows="2" required placeholder="Enter complete address"></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label fw-bold">Choose Payment Method</label>
          <div class="d-flex flex-wrap gap-2">
            <div class="payment-card p-3 text-center flex-fill">
              <input type="radio" name="payment_method" value="Cash on Delivery" id="cod" checked>
              <label for="cod" class="d-block fw-bold"><i class="bi bi-cash-stack fs-3"></i><br>Cash on Delivery</label>
            </div>
            <div class="payment-card p-3 text-center flex-fill">
              <input type="radio" name="payment_method" value="JazzCash" id="jc">
              <label for="jc" class="d-block fw-bold"><i class="bi bi-phone fs-3"></i><br>JazzCash</label>
            </div>
            <div class="payment-card p-3 text-center flex-fill">
              <input type="radio" name="payment_method" value="EasyPaisa" id="ep">
              <label for="ep" class="d-block fw-bold"><i class="bi bi-wallet2 fs-3"></i><br>EasyPaisa</label>
            </div>
            <div class="payment-card p-3 text-center flex-fill">
              <input type="radio" name="payment_method" value="Debit/Credit Card" id="card">
              <label for="card" class="d-block fw-bold"><i class="bi bi-credit-card-2-front fs-3"></i><br>Card</label>
            </div>
          </div>
        </div>
        <button type="submit" name="place_order" class="btn btn-success btn-lg w-100">
          <i class="bi bi-check-circle"></i> Place Order
        </button>
      </form>
    </div>
  </div>
</div>


<?php
/* ================================================================
   PAGE: ORDER SUCCESS
   ================================================================ */
elseif ($page === 'success' && $orderSuccess):
?>
<div class="text-center py-5">
  <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
  <h2 class="fw-bold mt-3">Thank You! Order Placed 🎉</h2>
  <p class="fs-5">Your Order ID: <strong class="text-success">#<?= $orderSuccess ?></strong></p>
  <p class="text-muted">Delivery within 2-3 working days. Stock has been updated automatically.</p>
  <a href="?" class="btn btn-success btn-lg">Continue Shopping</a>
  <a href="?page=orders" class="btn btn-outline-success btn-lg">View My Orders</a>
</div>


<?php
/* ================================================================
   PAGE: MY ORDERS
   ================================================================ */
elseif ($page === 'orders'):
$orders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<h3 class="mb-3 fw-bold"><i class="bi bi-receipt"></i> All Orders</h3>
<?php if (empty($orders)): ?>
  <div class="alert alert-info">No orders yet.</div>
<?php else: ?>
  <div class="table-responsive">
  <table class="table table-striped rounded shadow-sm">
    <thead><tr><th>Order ID</th><th>Customer</th><th>Phone</th><th>Payment</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
    <tbody>
    <?php foreach ($orders as $o): ?>
      <tr>
        <td>#<?= $o['id'] ?></td>
        <td><?= htmlspecialchars($o['customer_name']) ?></td>
        <td><?= htmlspecialchars($o['phone']) ?></td>
        <td><span class="badge bg-info"><?= htmlspecialchars($o['payment_method']) ?></span></td>
        <td class="fw-bold text-success">Rs. <?= number_format($o['total_amount']) ?></td>
        <td><span class="badge bg-warning text-dark"><?= $o['status'] ?></span></td>
        <td><?= date('d M Y, h:i A', strtotime($o['created_at'])) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
<?php endif; ?>

<?php endif; /* pages end */ ?>

</div>

<footer class="text-center py-3 mt-5">
  <small>© 2026 Apna Grocery Store — Best quality, best prices</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* ---------- AJAX ADD TO CART (no page refresh) ---------- */
function addToCart(id, btn) {
    if (btn.disabled) return;
    btn.disabled = true;
    fetch('?action=add&id=' + id + '&ajax=1')
        .then(r => r.json())
        .then(d => {
            btn.disabled = false;
            if (d.ok) {
                const badge = document.getElementById('cartCount');
                badge.textContent = d.count;
                badge.style.display = d.count > 0 ? '' : 'none';
                showToast(d.msg, false);
            } else {
                showToast(d.error, true);
            }
        })
        .catch(() => { btn.disabled = false; showToast('Something went wrong!', true); });
}

function showToast(msg, isError) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = isError ? 'error show' : 'show';
    clearTimeout(t._timer);
    t._timer = setTimeout(() => t.classList.remove('show'), 2500);
}
</script>
</body>
</html>