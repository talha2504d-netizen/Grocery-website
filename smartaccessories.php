<?php
session_start();

/* =========================
   SMART ACCESSORIES STORE
========================= */

$products = [
    1 => [
        "name" => "Smart Watch",
        "category" => "Smart Watches",
        "price" => 3500,
        "image" => "https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600",
        "description" => "Stylish smart watch with modern features."
    ],
    2 => [
        "name" => "Wireless Earbuds",
        "category" => "Earbuds",
        "price" => 2200,
        "image" => "https://images.unsplash.com/photo-1606220945770-b5b6c2c55bf1?w=600",
        "description" => "Enjoy high quality wireless sound."
    ],
    3 => [
        "name" => "Headphones",
        "category" => "Audio",
        "price" => 4500,
        "image" => "https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600",
        "description" => "Premium headphones with clear sound."
    ],
    4 => [
        "name" => "Power Bank",
        "category" => "Mobile Accessories",
        "price" => 2800,
        "image" => "./pictures/images.jfif",
        "description" => "Portable charging for your devices."
    ],
    5 => [
        "name" => "Bluetooth Speaker",
        "category" => "Audio",
        "price" => 3200,
        "image" => "https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=600",
        "description" => "Enjoy music anywhere with powerful sound."
    ],
    6 => [
        "name" => "Gaming Mouse",
        "category" => "Gaming",
        "price" => 1800,
        "image" => "https://images.unsplash.com/photo-1527814050087-3793815479db?w=600",
        "description" => "Comfortable mouse for gaming and work."
    ],
    7 => [
        "name" => "VR Headset",
        "category" => "Gaming",
        "price" => 8500,
        "image" => "https://images.unsplash.com/photo-1622979135225-d2ba269cf1ac?w=600",
        "description" => "Experience an immersive virtual world."
    ],
    8 => [
        "name" => "Mobile Holder",
        "category" => "Mobile Accessories",
        "price" => 950,
        "image" => "https://images.unsplash.com/photo-1583394838336-acd977736f90?w=600",
        "description" => "Convenient holder for your smartphone."
    ]
];

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (!isset($_SESSION['orders'])) {
    $_SESSION['orders'] = [];
}

$success = "";
$error = "";

/* ADD TO CART */
if (isset($_GET['add'])) {
    $id = (int)$_GET['add'];

    if (isset($products[$id])) {
        $_SESSION['cart'][$id] =
            ($_SESSION['cart'][$id] ?? 0) + 1;
        $success = "Product added to cart successfully!";
    }
}

/* UPDATE CART */
if (isset($_POST['update_cart'])) {
    foreach ($_POST['qty'] as $id => $qty) {
        $id = (int)$id;
        $qty = max(1, min(20, (int)$qty));

        if (isset($products[$id])) {
            $_SESSION['cart'][$id] = $qty;
        }
    }
    $success = "Cart updated successfully!";
}

/* REMOVE PRODUCT */
if (isset($_GET['remove'])) {
    $id = (int)$_GET['remove'];
    unset($_SESSION['cart'][$id]);
    $success = "Product removed from cart!";
}

/* CHECKOUT */
if (isset($_POST['checkout'])) {
    $name = trim($_POST['customer_name'] ?? "");
    $phone = trim($_POST['phone'] ?? "");
    $address = trim($_POST['address'] ?? "");
    $payment = trim($_POST['payment'] ?? "");

    if (empty($_SESSION['cart'])) {
        $error = "Your cart is empty!";
    } elseif ($name == "" || $phone == "" || $address == "" ||
              !in_array($payment, ['COD', 'Bank Transfer'])) {
        $error = "Please complete all checkout fields!";
    } else {
        $total = 0;

        foreach ($_SESSION['cart'] as $id => $qty) {
            $total += $products[$id]['price'] * $qty;
        }

        $orderId = "SA" . rand(10000, 99999);

        $_SESSION['orders'][] = [
            "id" => $orderId,
            "name" => $name,
            "phone" => $phone,
            "address" => $address,
            "payment" => $payment,
            "total" => $total,
            "date" => date("d-m-Y H:i")
        ];

        $_SESSION['cart'] = [];

        $success = "Order placed successfully! Your Order ID is " . $orderId;
    }
}

/* CONTACT FORM */
if (isset($_POST['contact_submit'])) {
    $contactName = trim($_POST['contact_name'] ?? "");
    $contactEmail = trim($_POST['contact_email'] ?? "");
    $message = trim($_POST['message'] ?? "");

    if ($contactName && filter_var($contactEmail, FILTER_VALIDATE_EMAIL) && $message) {
        $success = "Thank you! Your message has been received.";
    } else {
        $error = "Please enter valid contact details and message.";
    }
}

/* CART TOTAL */
$cartTotal = 0;
$cartCount = 0;

foreach ($_SESSION['cart'] as $id => $qty) {
    if (isset($products[$id])) {
        $cartTotal += $products[$id]['price'] * $qty;
        $cartCount += $qty;
    }
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Smart Accessories | Online Store</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    scroll-behavior: smooth;
}

body {
    font-family: Arial, sans-serif;
    background: #f5f7fb;
    color: #222;
}

header {
    background: #101827;
    color: white;
    padding: 18px 7%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 15px;
    position: sticky;
    top: 0;
    z-index: 1000;
}

.logo {
    font-size: 25px;
    font-weight: bold;
    color: #00d4a6;
}

nav {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

nav a {
    color: white;
    text-decoration: none;
    font-size: 15px;
}

nav a:hover {
    color: #00d4a6;
}

.cart-link {
    background: #00b894;
    color: white;
    padding: 10px 17px;
    border-radius: 6px;
    text-decoration: none;
}

.hero {
    min-height: 450px;
    padding: 80px 8%;
    background: linear-gradient(120deg, #101827, #164b55);
    color: white;
    display: flex;
    align-items: center;
}

.hero-content {
    max-width: 600px;
}

.hero h1 {
    font-size: 48px;
    margin-bottom: 20px;
}

.hero h1 span {
    color: #00d4a6;
}

.hero p {
    line-height: 1.8;
    color: #d6e2e8;
    margin-bottom: 25px;
}

.btn {
    display: inline-block;
    padding: 12px 22px;
    background: #00b894;
    color: white;
    text-decoration: none;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 15px;
}

.btn:hover {
    background: #009b7c;
}

section {
    padding: 65px 7%;
}

.section-title {
    text-align: center;
    margin-bottom: 35px;
}

.section-title h2 {
    font-size: 32px;
    margin-bottom: 10px;
}

.section-title p {
    color: #777;
}

.search-box {
    display: block;
    width: 100%;
    max-width: 450px;
    padding: 13px;
    margin: 0 auto 30px;
    border: 1px solid #ddd;
    border-radius: 7px;
    font-size: 15px;
}

.products {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 25px;
}

.product-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,.07);
    transition: .3s;
}

.product-card:hover {
    transform: translateY(-6px);
}

.product-card img {
    width: 100%;
    height: 210px;
    object-fit: cover;
    background: #eee;
}

.product-info {
    padding: 18px;
}

.product-info h3 {
    margin-bottom: 8px;
}

.category {
    color: #00a884;
    font-size: 13px;
    margin-bottom: 8px;
}

.description {
    color: #777;
    font-size: 14px;
    line-height: 1.5;
    min-height: 42px;
    margin-bottom: 10px;
}

.price {
    font-size: 20px;
    font-weight: bold;
    margin: 12px 0;
}

.about {
    background: white;
    text-align: center;
}

.about p {
    max-width: 750px;
    margin: auto;
    color: #666;
    line-height: 1.9;
}

.features {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 20px;
    margin-top: 35px;
}

.feature {
    background: #f5f7fb;
    padding: 25px;
    border-radius: 10px;
}

.feature h3 {
    color: #00a884;
    margin-bottom: 10px;
}

.cart-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    margin-bottom: 20px;
}

.cart-table th,
.cart-table td {
    padding: 14px;
    border-bottom: 1px solid #ddd;
    text-align: left;
}

.cart-table th {
    background: #101827;
    color: white;
}

.cart-table input {
    width: 65px;
    padding: 8px;
}

.remove {
    color: #e63946;
    text-decoration: none;
}

.checkout-box {
    max-width: 600px;
    margin: 25px auto;
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,.05);
}

.checkout-box input,
.checkout-box textarea,
.checkout-box select,
.contact-form input,
.contact-form textarea {
    width: 100%;
    padding: 13px;
    margin: 10px 0;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 15px;
}

.checkout-box textarea,
.contact-form textarea {
    min-height: 100px;
}

.contact {
    background: white;
}

.contact-form {
    max-width: 600px;
    margin: auto;
}

.message {
    max-width: 900px;
    margin: 15px auto;
    padding: 14px;
    background: #d1fae5;
    color: #065f46;
    border-radius: 7px;
}

.error {
    background: #fee2e2;
    color: #991b1b;
}

.orders {
    background: #eef3f6;
}

.order-card {
    background: white;
    padding: 18px;
    margin: 12px auto;
    max-width: 650px;
    border-radius: 8px;
}

footer {
    background: #101827;
    color: white;
    text-align: center;
    padding: 30px;
}

footer span {
    color: #00d4a6;
}

.menu-toggle {
    display: none;
    background: transparent;
    color: white;
    border: 0;
    font-size: 25px;
    cursor: pointer;
}

@media(max-width: 700px) {
    header {
        padding: 15px 5%;
    }

    .menu-toggle {
        display: block;
    }

    nav {
        display: none;
        width: 100%;
        flex-direction: column;
        padding: 15px 0;
    }

    nav.active {
        display: flex;
    }

    .hero {
        min-height: 370px;
        padding: 60px 6%;
    }

    .hero h1 {
        font-size: 35px;
    }

    section {
        padding: 45px 5%;
    }

    .cart-table {
        font-size: 12px;
    }

    .cart-table th,
    .cart-table td {
        padding: 8px 5px;
    }
}
</style>
</head>

<body>

<header>
    <div class="logo">SMART ACCESSORIES</div>

    <button class="menu-toggle" onclick="toggleMenu()">☰</button>

    <nav id="navbar">
        <a href="#home" onclick="closeMenu()">Home</a>
        <a href="#products" onclick="closeMenu()">Products</a>
        <a href="#about" onclick="closeMenu()">About Us</a>
        <a href="#contact" onclick="closeMenu()">Contact</a>
        <a href="#orders" onclick="closeMenu()">My Orders</a>
    </nav>

    <a href="#cart" class="cart-link">Cart (<?php echo $cartCount; ?>)</a>
</header>

<?php if ($success): ?>
    <div class="message"><?php echo e($success); ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="message error"><?php echo e($error); ?></div>
<?php endif; ?>

<!-- HOME -->

<section class="hero" id="home">
    <div class="hero-content">
        <h1>Upgrade Your Life With <span>Smart Accessories</span></h1>

        <p>
            Discover the latest smart watches, wireless earbuds,
            headphones, gaming accessories and much more.
            Quality products at affordable prices.
        </p>

        <a href="#products" class="btn">Shop Now</a>
    </div>
</section>

<!-- PRODUCTS -->

<section id="products">
    <div class="section-title">
        <h2>Our Products</h2>
        <p>Explore our latest smart accessories collection</p>
    </div>

    <input type="text" id="searchInput" class="search-box"
           placeholder="Search your favourite accessories...">

    <div class="products" id="productList">

        <?php foreach ($products as $id => $product): ?>

        <div class="product-card">

            <img src="<?php echo e($product['image']); ?>"
                 alt="<?php echo e($product['name']); ?>">

            <div class="product-info">

                <div class="category">
                    <?php echo e($product['category']); ?>
                </div>

                <h3><?php echo e($product['name']); ?></h3>

                <p class="description">
                    <?php echo e($product['description']); ?>
                </p>

                <div class="price">
                    Rs. <?php echo number_format($product['price']); ?>
                </div>

                <a href="?add=<?php echo $id; ?>#products" class="btn">
                    Add To Cart
                </a>

            </div>
        </div>

        <?php endforeach; ?>

    </div>
</section>

<!-- ABOUT -->

<section class="about" id="about">
    <div class="section-title">
        <h2>About Smart Accessories</h2>
    </div>

    <p>
        Smart Accessories is an online shopping store where you can
        find modern electronic gadgets and accessories. Our mission
        is to provide stylish, useful and affordable products.
        We aim to make online shopping simple and convenient.
    </p>

    <div class="features">
        <div class="feature">
            <h3>Quality Products</h3>
            <p>Modern and useful accessories.</p>
        </div>

        <div class="feature">
            <h3>Affordable Prices</h3>
            <p>Products at reasonable prices.</p>
        </div>

        <div class="feature">
            <h3>Easy Shopping</h3>
            <p>Simple cart and checkout system.</p>
        </div>

        <div class="feature">
            <h3>Customer Support</h3>
            <p>Contact us for assistance.</p>
        </div>
    </div>
</section>

<!-- CART -->

<section id="cart">
    <div class="section-title">
        <h2>Shopping Cart</h2>
        <p>Review your selected products</p>
    </div>

    <?php if (!empty($_SESSION['cart'])): ?>

    <form method="POST" action="#cart">

        <div style="overflow-x:auto;">
        <table class="cart-table">

            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
                <th>Remove</th>
            </tr>

            <?php foreach ($_SESSION['cart'] as $id => $qty): ?>

            <?php if (!isset($products[$id])) continue; ?>

            <tr>
                <td><?php echo e($products[$id]['name']); ?></td>

                <td>
                    Rs. <?php echo number_format($products[$id]['price']); ?>
                </td>

                <td>
                    <input type="number"
                           name="qty[<?php echo $id; ?>]"
                           value="<?php echo (int)$qty; ?>"
                           min="1" max="20">
                </td>

                <td>
                    Rs. <?php echo number_format($products[$id]['price'] * $qty); ?>
                </td>

                <td>
                    <a class="remove"
                       href="?remove=<?php echo $id; ?>#cart">
                       Remove
                    </a>
                </td>
            </tr>

            <?php endforeach; ?>

        </table>
        </div>

        <button type="submit" name="update_cart" class="btn">
            Update Cart
        </button>

    </form>

    <h2 style="margin-top:25px;">
        Total: Rs. <?php echo number_format($cartTotal); ?>
    </h2>

    <?php else: ?>

        <p style="text-align:center;">Your shopping cart is empty!</p>

    <?php endif; ?>
</section>

<!-- CHECKOUT -->

<section id="checkout">
    <div class="section-title">
        <h2>Checkout</h2>
        <p>Complete your order details</p>
    </div>

    <?php if (!empty($_SESSION['cart'])): ?>

    <div class="checkout-box">

        <h3>Customer Information</h3>

        <form method="POST" action="#checkout">

            <input type="text"
                   name="customer_name"
                   placeholder="Your Full Name"
                   maxlength="100"
                   required>

            <input type="tel"
                   name="phone"
                   placeholder="Phone Number"
                   maxlength="25"
                   required>

            <textarea name="address"
                      placeholder="Delivery Address"
                      maxlength="1000"
                      required></textarea>

            <label><b>Select Payment Method</b></label>

            <select name="payment" required>
                <option value="">Choose Payment</option>
                <option value="COD">Cash On Delivery</option>
                <option value="Bank Transfer">Bank Transfer</option>
            </select>

            <h3 style="margin:15px 0;">
                Total: Rs. <?php echo number_format($cartTotal); ?>
            </h3>

            <button type="submit" name="checkout" class="btn">
                Place Order
            </button>

        </form>

        <p style="margin-top:15px;color:#777;font-size:13px;">
            Note: This is a demo checkout. No real payment is processed.
        </p>

    </div>

    <?php else: ?>

        <p style="text-align:center;">
            Add products to your cart before checkout.
        </p>

    <?php endif; ?>
</section>

<!-- ORDERS -->

<section class="orders" id="orders">
    <div class="section-title">
        <h2>My Orders</h2>
        <p>Your recent orders on this session</p>
    </div>

    <?php if (!empty($_SESSION['orders'])): ?>

        <?php foreach (array_reverse($_SESSION['orders']) as $order): ?>

            <div class="order-card">
                <h3>Order #<?php echo e($order['id']); ?></h3>

                <p>Customer: <?php echo e($order['name']); ?></p>
                <p>Phone: <?php echo e($order['phone']); ?></p>
                <p>Address: <?php echo e($order['address']); ?></p>
                <p>Payment: <?php echo e($order['payment']); ?></p>
                <p>Total: Rs. <?php echo number_format($order['total']); ?></p>
                <p>Date: <?php echo e($order['date']); ?></p>
                <p style="color:green;">Status: Order Placed</p>
            </div>

        <?php endforeach; ?>

    <?php else: ?>

        <p style="text-align:center;">No orders placed yet.</p>

    <?php endif; ?>
</section>

<!-- CONTACT -->

<section class="contact" id="contact">

    <div class="section-title">
        <h2>Contact Us</h2>
        <p>We would love to hear from you</p>
    </div>

    <form class="contact-form" method="POST" action="#contact">

        <input type="text"
               name="contact_name"
               placeholder="Your Name"
               maxlength="100"
               required>

        <input type="email"
               name="contact_email"
               placeholder="Your Email"
               maxlength="150"
               required>

        <textarea name="message"
                  placeholder="Write your message..."
                  maxlength="2000"
                  required></textarea>

        <button type="submit" name="contact_submit" class="btn">
            Send Message
        </button>

    </form>

</section>

<footer>
    <h2><span>SMART</span> ACCESSORIES</h2>

    <p style="margin:12px 0;">
        Your Smart World, Our Accessories
    </p>

    <p>© <?php echo date("Y"); ?> Smart Accessories. All Rights Reserved.</p>
</footer>

<script>

/* MOBILE NAVBAR */

function toggleMenu() {
    document.getElementById("navbar").classList.toggle("active");
}

function closeMenu() {
    document.getElementById("navbar").classList.remove("active");
}

/* PRODUCT SEARCH */

document.getElementById("searchInput").addEventListener("input", function() {

    let search = this.value.toLowerCase();

    let cards = document.querySelectorAll(".product-card");

    cards.forEach(function(card) {

        let productName = card.innerText.toLowerCase();

        card.style.display =
            productName.includes(search) ? "block" : "none";

    });

});

</script>

</body>
</html>