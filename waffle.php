<?php
session_start();

/* =========================
   WAFFLE PRODUCTS
========================= */

$products = [
    1 => [
        "name" => "Chocolate Waffle",
        "description" => "Delicious waffle with chocolate sauce.",
        "price" => 650,
        "image" => "https://images.unsplash.com/photo-1562376552-0d160a2f238d?auto=format&fit=crop&w=800&q=80"
    ],

    2 => [
        "name" => "Nutella Waffle",
        "description" => "Nutella, banana and crispy waffle.",
        "price" => 850,
        "image" => "images/Nutella Waffle.jfif"
    ],

    3 => [
        "name" => "Strawberry Waffle",
        "description" => "Fresh strawberries and cream.",
        "price" => 750,
        "image" => "https://images.unsplash.com/photo-1484723091739-30a097e8f929?auto=format&fit=crop&w=800&q=80"
    ],

    4 => [
        "name" => "Oreo Waffle",
        "description" => "Oreo crumbs and chocolate topping.",
        "price" => 900,
        "image" => "images/Oreo Waffle.jfif"
    ],

    5 => [
        "name" => "Classic Waffle",
        "description" => "Classic golden waffle with syrup.",
        "price" => 550,
        "image" => "https://images.unsplash.com/photo-1562376552-0d160a2f238d?auto=format&fit=crop&w=800&q=80"
    ],

    6 => [
        "name" => "Ice Cream Waffle",
        "description" => "Waffle with delicious ice cream.",
        "price" => 950,
        "image" => "https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=800&q=80"
    ]
];


/* =========================
   CART
========================= */

if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

$message = "";


/* ADD TO CART */

if (isset($_POST["add_to_cart"])) {

    $id = (int) $_POST["product_id"];

    $quantity = (int) $_POST["quantity"];

    if (
        isset($products[$id]) &&
        $quantity >= 1 &&
        $quantity <= 20
    ) {

        $current = $_SESSION["cart"][$id] ?? 0;

        $_SESSION["cart"][$id] = min(
            20,
            $current + $quantity
        );

        $message = "Product added to cart successfully!";
    }

}


/* UPDATE CART */

if (isset($_POST["update_cart"])) {

    $id = (int) $_POST["product_id"];

    $quantity = (int) $_POST["quantity"];

    if (isset($products[$id])) {

        if ($quantity >= 1 && $quantity <= 20) {

            $_SESSION["cart"][$id] = $quantity;

        } else {

            unset($_SESSION["cart"][$id]);

        }

    }

}


/* REMOVE PRODUCT */

if (isset($_POST["remove_product"])) {

    $id = (int) $_POST["product_id"];

    unset($_SESSION["cart"][$id]);

}


/* CLEAR CART */

if (isset($_POST["clear_cart"])) {

    $_SESSION["cart"] = [];

}


/* =========================
   CONTACT FORM
========================= */

if (isset($_POST["contact_submit"])) {

    $name = trim($_POST["name"] ?? "");

    $phone = trim($_POST["phone"] ?? "");

    $contactMessage = trim($_POST["message"] ?? "");

    if (
        $name !== "" &&
        $phone !== "" &&
        $contactMessage !== ""
    ) {

        if (
            preg_match('/^[0-9+\-\s]{7,20}$/', $phone)
        ) {

            $message = "Thank you! Your message has been received.";

        } else {

            $message = "Please enter a valid phone number.";

        }

    } else {

        $message = "Please fill all contact fields.";

    }

}


/* =========================
   CHECKOUT
========================= */

if (isset($_POST["checkout"])) {

    $name = trim($_POST["customer_name"] ?? "");

    $phone = trim($_POST["customer_phone"] ?? "");

    $address = trim($_POST["customer_address"] ?? "");

    if (
        $name !== "" &&
        $phone !== "" &&
        $address !== "" &&
        !empty($_SESSION["cart"])
    ) {

        if (
            preg_match('/^[0-9+\-\s]{7,20}$/', $phone)
        ) {

            $_SESSION["last_order"] = [
                "name" => $name,
                "phone" => $phone,
                "address" => $address,
                "cart" => $_SESSION["cart"]
            ];

            $_SESSION["cart"] = [];

            $message = "Your demo order has been placed successfully!";

        } else {

            $message = "Please enter a valid phone number.";

        }

    } else {

        $message = "Please fill all checkout fields.";

    }

}


/* =========================
   CART TOTAL
========================= */

$total = 0;

$cartCount = 0;

foreach ($_SESSION["cart"] as $id => $quantity) {

    if (isset($products[$id])) {

        $total += $products[$id]["price"] * $quantity;

        $cartCount += $quantity;

    }

}


/* =========================
   HELPER
========================= */

function money($amount) {

    return "Rs. " . number_format($amount);

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1">

<title>Waffle Spot | Delicious Waffles</title>

<!-- Bootstrap -->

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<!-- Google Font -->

<link
href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"
rel="stylesheet">

<style>

/* =========================
   GENERAL
========================= */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
    scroll-padding-top: 80px;
}

body {
    font-family: 'Poppins', sans-serif;
    background: #fffaf3;
    color: #352016;
}

section {
    padding: 80px 0;
}

.section-title {
    text-align: center;
    margin-bottom: 45px;
}

.section-title h2 {
    font-size: 38px;
    font-weight: 800;
}

.section-title span {
    color: #d97724;
}

.section-title p {
    color: #827268;
    margin-top: 10px;
}

/* =========================
   NAVBAR
========================= */

.navbar {
    background: #301b11;
    padding: 15px 0;
}

.navbar-brand {
    color: white !important;
    font-size: 25px;
    font-weight: 800;
}

.navbar-brand span {
    color: #f5a04b;
}

.nav-link {
    color: white !important;
    margin: 0 8px;
    font-weight: 500;
}

.nav-link:hover {
    color: #f5a04b !important;
}

/* =========================
   BUTTONS
========================= */

.btn-waffle {
    background: #e88a32;
    color: #28170d;
    border: none;
    padding: 12px 25px;
    border-radius: 30px;
    font-weight: 700;
    transition: .3s;
}

.btn-waffle:hover {
    background: #ffb45f;
    color: #28170d;
    transform: translateY(-2px);
}

/* =========================
   HERO
========================= */

.hero {
    min-height: 680px;
    display: flex;
    align-items: center;

    background:
        linear-gradient(
            rgba(35, 19, 10, .75),
            rgba(35, 19, 10, .75)
        ),
        url("https://images.unsplash.com/photo-1562376552-0d160a2f238d?auto=format&fit=crop&w=1800&q=85");

    background-size: cover;
    background-position: center;
    color: white;
}

.hero-content {
    max-width: 700px;
}

.hero h1 {
    font-size: clamp(42px, 7vw, 75px);
    font-weight: 800;
    line-height: 1.1;
}

.hero h1 span {
    color: #ffad56;
}

.hero p {
    font-size: 18px;
    color: #f0dfd0;
    margin: 25px 0;
}

/* =========================
   PRODUCT CARDS
========================= */

.product-card {
    height: 100%;
    background: white;
    border: none;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(40, 20, 10, .08);
    transition: .3s;
}

.product-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 35px rgba(40, 20, 10, .15);
}

.product-card img {
    width: 100%;
    height: 230px;
    object-fit: cover;
}

.product-card .card-body {
    padding: 25px;
}

.product-card h5 {
    font-weight: 700;
}

.product-card p {
    color: #817267;
    min-height: 48px;
}

.product-price {
    color: #d97724;
    font-size: 20px;
    font-weight: 800;
}

/* =========================
   PRICE LIST
========================= */

.price-section {
    background: #f5eadc;
}

.price-table {
    background: white;
    border-radius: 15px;
    overflow: hidden;
}

.price-table th {
    background: #301b11;
    color: white;
    padding: 18px;
}

.price-table td {
    padding: 18px;
}

/* =========================
   ABOUT
========================= */

.about-section {
    background: white;
}

.about-section h2 {
    font-size: 40px;
    font-weight: 800;
}

.about-section p {
    line-height: 1.9;
    color: #817267;
}

.about-img {
    width: 100%;
    height: 400px;
    object-fit: cover;
    border-radius: 20px;
}

/* =========================
   CART
========================= */

.cart-section {
    background: #f5eadc;
}

.cart-box {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(40, 20, 10, .08);
}

.cart-total {
    font-size: 25px;
    font-weight: 800;
    color: #d97724;
}

/* =========================
   CONTACT
========================= */

.contact-section {
    background: #301b11;
    color: white;
}

.contact-section p {
    color: #d7c5b6;
}

.contact-form {
    max-width: 700px;
    margin: auto;
    background: white;
    color: #352016;
    padding: 30px;
    border-radius: 18px;
}

.form-control {
    padding: 13px;
    border-radius: 10px;
}

.form-control:focus {
    border-color: #e88a32;
    box-shadow: 0 0 0 3px rgba(232, 138, 50, .15);
}

/* =========================
   FOOTER
========================= */

footer {
    background: #1d100a;
    color: white;
    text-align: center;
    padding: 25px;
}

footer span {
    color: #f5a04b;
}

/* =========================
   RESPONSIVE
========================= */

@media(max-width: 768px) {

    section {
        padding: 55px 0;
    }

    .hero {
        min-height: 580px;
    }

    .hero h1 {
        font-size: 43px;
    }

    .section-title h2 {
        font-size: 30px;
    }

    .about-img {
        height: 280px;
    }

    .contact-form {
        padding: 20px;
    }

    .price-table td,
    .price-table th {
        padding: 12px;
        font-size: 13px;
    }

}

</style>

</head>

<body>

<!-- =========================
     NAVBAR
========================= -->

<nav class="navbar navbar-expand-lg navbar-dark sticky-top">

<div class="container">

<a class="navbar-brand" href="#home">
🧇 Waffle <span>Spot</span>
</a>

<button
class="navbar-toggler"
data-bs-toggle="collapse"
data-bs-target="#navbarMenu">

<span class="navbar-toggler-icon"></span>

</button>

<div class="collapse navbar-collapse" id="navbarMenu">

<ul class="navbar-nav ms-auto align-items-lg-center">

<li class="nav-item">
<a class="nav-link" href="#home">Home</a>
</li>

<li class="nav-item">
<a class="nav-link" href="#menu">Menu</a>
</li>

<li class="nav-item">
<a class="nav-link" href="#prices">Price List</a>
</li>

<li class="nav-item">
<a class="nav-link" href="#about">About Us</a>
</li>

<li class="nav-item">
<a class="nav-link" href="#contact">Contact</a>
</li>

<li class="nav-item">
<a class="nav-link" href="#cart">
🛒 Cart (<?= $cartCount ?>)
</a>
</li>

</ul>

</div>

</div>

</nav>


<!-- =========================
     MESSAGE
========================= -->

<?php if ($message !== ""): ?>

<div class="container mt-3">

<div class="alert alert-info alert-dismissible fade show">

<?= htmlspecialchars($message) ?>

<button
type="button"
class="btn-close"
data-bs-dismiss="alert">
</button>

</div>

</div>

<?php endif; ?>


<!-- =========================
     HOME
========================= -->

<section class="hero" id="home">

<div class="container">

<div class="hero-content">

<p class="text-warning fw-bold">
FRESH • CRISPY • DELICIOUS
</p>

<h1>
Happiness is<br>
<span>Waffle Shaped!</span>
</h1>

<p>
Enjoy delicious waffles with chocolate,
strawberry, Nutella and ice cream.
Freshly made just for you.
</p>

<a href="#menu"
class="btn btn-waffle btn-lg">

Explore Our Menu

</a>

<a href="#prices"
class="btn btn-outline-light btn-lg ms-2">

View Prices

</a>

</div>

</div>

</section>


<!-- =========================
     MENU
========================= -->

<section id="menu">

<div class="container">

<div class="section-title">

<h2>Our <span>Waffle Menu</span></h2>

<p>
Choose your favorite delicious waffle.
</p>

</div>

<div class="row g-4">

<?php foreach ($products as $id => $product): ?>

<div class="col-sm-6 col-lg-4">

<div class="card product-card">

<img
src="<?= htmlspecialchars($product["image"]) ?>"
class="card-img-top"
alt="<?= htmlspecialchars($product["name"]) ?>">

<div class="card-body">

<h5>
<?= htmlspecialchars($product["name"]) ?>
</h5>

<p>
<?= htmlspecialchars($product["description"]) ?>
</p>

<div class="product-price mb-3">

<?= money($product["price"]) ?>

</div>

<form method="POST">

<input
type="hidden"
name="product_id"
value="<?= $id ?>">

<label class="form-label">
Quantity
</label>

<input
type="number"
name="quantity"
value="1"
min="1"
max="20"
class="form-control mb-3"
required>

<button
type="submit"
name="add_to_cart"
class="btn btn-waffle w-100">

Add to Cart

</button>

</form>

</div>

</div>

</div>

<?php endforeach; ?>

</div>

</div>

</section>


<!-- =========================
     PRICE LIST
========================= -->

<section class="price-section" id="prices">

<div class="container">

<div class="section-title">

<h2>Our <span>Price List</span></h2>

<p>
All prices are in Pakistani Rupees.
</p>

</div>

<div class="table-responsive">

<table class="table price-table table-hover">

<thead>

<tr>

<th>Waffle Name</th>

<th>Description</th>

<th>Price (PKR)</th>

</tr>

</thead>

<tbody>

<?php foreach ($products as $product): ?>

<tr>

<td class="fw-bold">

<?= htmlspecialchars($product["name"]) ?>

</td>

<td>

<?= htmlspecialchars($product["description"]) ?>

</td>

<td class="fw-bold text-warning">

<?= money($product["price"]) ?>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>

</section>


<!-- =========================
     ABOUT
========================= -->

<section class="about-section" id="about">

<div class="container">

<div class="row align-items-center g-5">

<div class="col-lg-6">

<img
src="images/logo.jfif"
class="about-img"
alt="Waffle Spot">

</div>

<div class="col-lg-6">

<p class="text-warning fw-bold">
ABOUT WAFFLE SPOT
</p>

<h2>
Sweet Moments<br>
Every Day
</h2>

<p>

Welcome to Waffle Spot!

We prepare delicious waffles using
quality ingredients.

Our menu includes chocolate,
Nutella, strawberry, Oreo
and classic waffles.

Our goal is to provide customers
with fresh desserts and
a wonderful experience.

</p>

<a href="#contact"
class="btn btn-waffle">

Contact Us

</a>

</div>

</div>

</div>

</section>


<!-- =========================
     CART
========================= -->

<section class="cart-section" id="cart">

<div class="container">

<div class="section-title">

<h2>Your <span>Shopping Cart</span></h2>

<p>
Manage your orders before checkout.
</p>

</div>

<div class="cart-box">

<?php if (empty($_SESSION["cart"])): ?>

<div class="alert alert-warning">

Your cart is empty!

</div>

<a href="#menu"
class="btn btn-waffle">

Explore Menu

</a>

<?php else: ?>

<div class="table-responsive">

<table class="table table-bordered align-middle">

<thead class="table-dark">

<tr>

<th>Product</th>

<th>Price</th>

<th>Quantity</th>

<th>Subtotal</th>

<th>Action</th>

</tr>

</thead>

<tbody>

<?php foreach ($_SESSION["cart"] as $id => $quantity): ?>

<?php if (isset($products[$id])): ?>

<tr>

<td>

<?= htmlspecialchars($products[$id]["name"]) ?>

</td>

<td>

<?= money($products[$id]["price"]) ?>

</td>

<td>

<form method="POST"
class="d-flex gap-2">

<input
type="hidden"
name="product_id"
value="<?= $id ?>">

<input
type="number"
name="quantity"
value="<?= $quantity ?>"
min="1"
max="20"
class="form-control"
style="width:80px">

<button
type="submit"
name="update_cart"
class="btn btn-primary btn-sm">

Update

</button>

</form>

</td>

<td>

<?= money(
$products[$id]["price"] * $quantity
) ?>

</td>

<td>

<form method="POST">

<input
type="hidden"
name="product_id"
value="<?= $id ?>">

<button
type="submit"
name="remove_product"
class="btn btn-danger btn-sm">

Remove

</button>

</form>

</td>

</tr>

<?php endif; ?>

<?php endforeach; ?>

</tbody>

</table>

</div>

<div class="text-end">

<h3 class="cart-total">

Total: <?= money($total) ?>

</h3>

</div>

<form method="POST"
class="mt-4">

<button
type="submit"
name="clear_cart"
class="btn btn-danger">

Clear Cart

</button>

</form>

<hr>

<h3 class="mb-3">
Customer Checkout
</h3>

<form method="POST">

<label class="form-label">
Customer Name
</label>

<input
type="text"
name="customer_name"
class="form-control mb-3"
maxlength="100"
required>

<label class="form-label">
Phone Number
</label>

<input
type="tel"
name="customer_phone"
class="form-control mb-3"
maxlength="20"
required>

<label class="form-label">
Delivery Address
</label>

<textarea
name="customer_address"
class="form-control mb-3"
maxlength="1000"
required></textarea>

<button
type="submit"
name="checkout"
class="btn btn-waffle">

Place Order

</button>

</form>

<?php endif; ?>

</div>

</div>

</section>


<!-- =========================
     CONTACT
========================= -->

<section class="contact-section" id="contact">

<div class="container">

<div class="section-title">

<h2>
Contact <span>Us</span>
</h2>

<p>
Have a question? Send us a message.
</p>

</div>

<form method="POST"
class="contact-form">

<label class="form-label">
Customer Name
</label>

<input
type="text"
name="name"
class="form-control mb-3"
maxlength="100"
required>

<label class="form-label">
Phone Number
</label>

<input
type="tel"
name="phone"
class="form-control mb-3"
maxlength="20"
required>

<label class="form-label">
Message
</label>

<textarea
name="message"
class="form-control mb-3"
rows="5"
maxlength="2000"
required></textarea>

<button
type="submit"
name="contact_submit"
class="btn btn-waffle">

Send Message

</button>

</form>

</div>

</section>


<!-- =========================
     FOOTER
========================= -->

<footer>

<div class="container">

<h4>
🧇 Waffle <span>Spot</span>
</h4>

<p>
Fresh Waffles • Sweet Moments
</p>

<p>
© <?= date("Y") ?> Waffle Spot
</p>

</div>

</footer>


<!-- BOOTSTRAP -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


<!-- JAVASCRIPT -->

<script>

/* =========================
   CONTACT FORM
========================= */

const contactForm = document.querySelector(".contact-form");

if (contactForm) {

    contactForm.addEventListener("submit", function(event) {

        const phone = contactForm.querySelector(
            'input[name="phone"]'
        );

        if (phone && phone.value.trim().length < 7) {

            event.preventDefault();

            alert("Please enter a valid phone number.");

        }

    });

}


/* =========================
   CHECKOUT FORM
========================= */

const checkoutForm = document.querySelector(
    'form input[name="customer_phone"]'
);

if (checkoutForm) {

    checkoutForm.addEventListener("input", function() {

        this.value = this.value.slice(0, 20);

    });

}


/* =========================
   NAVBAR
========================= */

document.querySelectorAll(".navbar-nav .nav-link").forEach(
    function(link) {

        link.addEventListener("click", function() {

            const menu = document.querySelector(".navbar-collapse");

            if (menu.classList.contains("show")) {

                bootstrap.Collapse
                    .getOrCreateInstance(menu)
                    .hide();

            }

        });

    }
);


/* =========================
   SCROLL
========================= */

window.addEventListener("scroll", function() {

    const navbar = document.querySelector(".navbar");

    if (window.scrollY > 50) {

        navbar.style.boxShadow =
            "0 5px 20px rgba(0,0,0,0.2)";

    } else {

        navbar.style.boxShadow = "none";

    }

});

</script>

</body>

</html>