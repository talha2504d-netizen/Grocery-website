<?php
session_start();

/* =========================
   RESTAURANT PRODUCTS
========================= */

$products = [

    1 => [
        "name" => "Beef Burger",
        "price" => 950,
        "image" => "https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=800&q=80",
        "description" => "Delicious beef burger with cheese."
    ],

    2 => [
        "name" => "Chicken Pizza",
        "price" => 1450,
        "image" => "https://images.unsplash.com/photo-1513104890138-7c749659a591?auto=format&fit=crop&w=800&q=80",
        "description" => "Fresh chicken pizza with mozzarella."
    ],

    3 => [
        "name" => "Creamy Pasta",
        "price" => 1100,
        "image" => "https://images.unsplash.com/photo-1473093295043-cdd812d0e601?auto=format&fit=crop&w=800&q=80",
        "description" => "Creamy pasta with herbs."
    ],

    4 => [
        "name" => "Chocolate Waffle",
        "price" => 650,
        "image" => "https://images.unsplash.com/photo-1562376552-0d160a2f238d?auto=format&fit=crop&w=800&q=80",
        "description" => "Chocolate waffle with sweet sauce."
    ],

    5 => [
        "name" => "Chicken Sandwich",
        "price" => 750,
        "image" => "https://images.unsplash.com/photo-1528735602780-2552fd46c7af?auto=format&fit=crop&w=800&q=80",
        "description" => "Fresh chicken sandwich."
    ],

    6 => [
        "name" => "French Fries",
        "price" => 450,
        "image" => "https://images.unsplash.com/photo-1573080496219-bb080dd4f877?auto=format&fit=crop&w=800&q=80",
        "description" => "Crispy golden french fries."
    ]

];


/* =========================
   CART INITIALIZATION
========================= */

if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

$message = "";
$message_type = "success";


/* =========================
   ADD TO CART
========================= */

if (isset($_POST["add_to_cart"])) {

    $id = (int) ($_POST["product_id"] ?? 0);

    if (isset($products[$id])) {

        if (isset($_SESSION["cart"][$id])) {

            if ($_SESSION["cart"][$id]["qty"] < 99) {

                $_SESSION["cart"][$id]["qty"]++;

            } else {

                $message = "Maximum quantity is 99.";
                $message_type = "warning";

            }

        } else {

            $_SESSION["cart"][$id] = [
                "qty" => 1
            ];

        }

        if ($message === "") {
            $message = $products[$id]["name"] . " added to cart!";
        }

    }

}


/* =========================
   UPDATE CART
========================= */

if (isset($_POST["update_cart"])) {

    $id = (int) ($_POST["product_id"] ?? 0);

    $qty = (int) ($_POST["qty"] ?? 0);

    if (isset($_SESSION["cart"][$id])) {

        if ($qty >= 1 && $qty <= 99) {

            $_SESSION["cart"][$id]["qty"] = $qty;

            $message = "Cart updated successfully!";

        } else {

            $message = "Quantity must be between 1 and 99.";

            $message_type = "warning";

        }

    }

}


/* =========================
   REMOVE PRODUCT
========================= */

if (isset($_POST["remove_product"])) {

    $id = (int) ($_POST["product_id"] ?? 0);

    if (isset($_SESSION["cart"][$id])) {

        unset($_SESSION["cart"][$id]);

        $message = "Product removed from cart!";

    }

}


/* =========================
   CONTACT FORM
========================= */

if (isset($_POST["contact_submit"])) {

    $name = trim($_POST["name"] ?? "");

    $email = trim($_POST["email"] ?? "");

    $contact_message = trim($_POST["message"] ?? "");

    if (
        $name !== "" &&
        filter_var($email, FILTER_VALIDATE_EMAIL) &&
        $contact_message !== ""
    ) {

        $message = "Thank you! Your message has been received.";

    } else {

        $message = "Please enter valid contact information.";

        $message_type = "warning";

    }

}


/* =========================
   CHECKOUT
========================= */

if (isset($_POST["place_order"])) {

    $name = trim($_POST["customer_name"] ?? "");

    $phone = trim($_POST["phone"] ?? "");

    $address = trim($_POST["address"] ?? "");

    $payment = $_POST["payment"] ?? "";

    if (empty($_SESSION["cart"])) {

        $message = "Your cart is empty.";

        $message_type = "warning";

    } elseif (
        $name !== "" &&
        $phone !== "" &&
        $address !== "" &&
        in_array($payment, ["cod", "bank"], true)
    ) {

        $_SESSION["cart"] = [];

        $message = "Order received successfully!";

    } else {

        $message = "Please complete all checkout fields.";

        $message_type = "warning";

    }

}


/* =========================
   PAGE NAVIGATION
========================= */

$page = $_GET["page"] ?? "home";

$allowed_pages = [
    "home",
    "about",
    "contact",
    "cart",
    "checkout",
    "pricelist"
];

if (!in_array($page, $allowed_pages, true)) {

    $page = "home";

}


/* =========================
   CART TOTAL
========================= */

$total = 0;

foreach ($_SESSION["cart"] as $id => $item) {

    if (isset($products[$id])) {

        $total += $products[$id]["price"] * $item["qty"];

    }

}


/* =========================
   CART COUNT
========================= */

$cart_count = 0;

foreach ($_SESSION["cart"] as $item) {

    $cart_count += $item["qty"];

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Taste House Restaurant</title>

<!-- Bootstrap CSS -->

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet">

<!-- Google Fonts -->

<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:wght@600;700&display=swap"
      rel="stylesheet">

<!-- jQuery -->

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>


<style>

/* =========================
   GENERAL
========================= */

* {
    box-sizing: border-box;
}

body {
    font-family: 'DM Sans', sans-serif;
    color: #222;
    background: #fff;
}

h1, h2, h3 {
    font-family: 'Playfair Display', serif;
}

:root {
    --green: #174d3b;
    --orange: #ef8b38;
    --cream: #fff9f1;
}


/* =========================
   NAVBAR
========================= */

.navbar {
    background: var(--green);
}

.navbar-brand {
    color: white;
    font-size: 25px;
    font-weight: bold;
}

.navbar-brand:hover {
    color: white;
}

.nav-link {
    color: white !important;
    margin: 0 5px;
    transition: .3s;
}

.nav-link:hover,
.nav-link.active {
    color: var(--orange) !important;
}


/* =========================
   BUTTONS
========================= */

.btn-food {
    background: var(--orange);
    color: white;
    border: none;
    border-radius: 30px;
    padding: 12px 22px;
    font-weight: bold;
    transition: .3s;
}

.btn-food:hover {
    background: #d97625;
    color: white;
    transform: translateY(-2px);
}

.btn-outline-food {
    border: 2px solid var(--green);
    color: var(--green);
    border-radius: 30px;
    padding: 10px 20px;
    transition: .3s;
}

.btn-outline-food:hover {
    background: var(--green);
    color: white;
}


/* =========================
   HERO
========================= */

.hero {
    background: var(--cream);
    padding: 80px 0;
}

.hero h1 {
    font-size: clamp(40px, 6vw, 70px);
    font-weight: bold;
}

.hero h1 span {
    color: var(--orange);
}

.hero p {
    font-size: 18px;
    margin: 20px 0;
}

.hero-img {
    width: 100%;
    height: 400px;
    object-fit: cover;
    border-radius: 25px;
}


/* =========================
   MENU
========================= */

.menu-section {
    padding: 70px 0;
}

.menu-section h2 {
    font-size: 40px;
}

.food-card {
    border: none;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(0,0,0,.08);
    transition: .3s;
}

.food-card:hover {
    transform: translateY(-7px);
}

.food-card img {
    width: 100%;
    height: 220px;
    object-fit: cover;
}

.food-card .card-body {
    padding: 20px;
}

.food-price {
    color: var(--green);
    font-weight: bold;
    font-size: 20px;
}


/* =========================
   PAGE HEADER
========================= */

.page-header {
    background: var(--cream);
    padding: 65px 0;
    text-align: center;
}

.page-header h1 {
    font-size: 50px;
}


/* =========================
   ABOUT
========================= */

.about-img {
    width: 100%;
    height: 350px;
    object-fit: cover;
    border-radius: 20px;
}


/* =========================
   CONTACT
========================= */

.form-card {
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 10px 35px rgba(0,0,0,.08);
}

.form-control {
    padding: 12px;
    border-radius: 10px;
}

.form-control:focus {
    border-color: var(--orange);
    box-shadow: 0 0 0 3px rgba(239,139,56,.15);
}


/* =========================
   PAYMENT
========================= */

.payment-option {
    display: block;
    border: 1px solid #ddd;
    border-radius: 12px;
    padding: 15px;
    margin: 12px 0;
    cursor: pointer;
}


/* =========================
   CART
========================= */

.cart-empty {
    background: var(--cream);
    padding: 40px;
    border-radius: 20px;
}


/* =========================
   PRICE LIST
========================= */

.price-list-section {
    padding: 60px 0;
}

.price-list-table {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(0,0,0,.07);
}

.price-list-table th {
    background: var(--green);
    color: white;
    padding: 16px;
    white-space: nowrap;
}

.price-list-table td {
    padding: 15px;
    vertical-align: middle;
}

.price-list-table tbody tr:hover {
    background: var(--cream);
}

.price-list-image {
    width: 65px;
    height: 55px;
    object-fit: cover;
    border-radius: 10px;
}

.price-list-price {
    color: var(--green);
    font-weight: bold;
    white-space: nowrap;
}

.price-list-description {
    color: #666;
    font-size: 14px;
}


/* =========================
   FOOTER
========================= */

footer {
    background: var(--green);
    color: white;
    padding: 40px 0;
    margin-top: 60px;
}

footer a {
    text-decoration: none;
}


/* =========================
   RESPONSIVE
========================= */

@media (max-width: 768px) {

    .hero {
        padding: 45px 0;
    }

    .hero h1 {
        font-size: 42px;
    }

    .hero-img {
        height: 260px;
        margin-top: 30px;
    }

    .food-card img {
        height: 220px;
    }

    .page-header {
        padding: 45px 0;
    }

    .page-header h1 {
        font-size: 35px;
    }

    .about-img {
        height: 250px;
        margin-bottom: 25px;
    }

    .price-list-table th,
    .price-list-table td {
        padding: 10px;
        font-size: 13px;
    }

    .price-list-image {
        width: 50px;
        height: 45px;
    }

    .form-card {
        padding: 20px;
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

<a class="navbar-brand"
   href="index.php?page=home">

🍽 Taste House

</a>

<button class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#navbarMenu"
        aria-controls="navbarMenu"
        aria-expanded="false"
        aria-label="Toggle navigation">

<span class="navbar-toggler-icon"></span>

</button>

<div class="collapse navbar-collapse"
     id="navbarMenu">

<ul class="navbar-nav ms-auto">

<li class="nav-item">

<a class="nav-link <?= $page === 'home' ? 'active' : '' ?>"
   href="index.php?page=home">

Home

</a>

</li>

<li class="nav-item">

<a class="nav-link <?= $page === 'about' ? 'active' : '' ?>"
   href="index.php?page=about">

About Us

</a>

</li>

<li class="nav-item">

<a class="nav-link"
   href="index.php?page=home#menu">

Menu

</a>

</li>

<li class="nav-item">

<a class="nav-link <?= $page === 'pricelist' ? 'active' : '' ?>"
   href="index.php?page=pricelist">

Price List

</a>

</li>

<li class="nav-item">

<a class="nav-link <?= $page === 'contact' ? 'active' : '' ?>"
   href="index.php?page=contact">

Contact Us

</a>

</li>

<li class="nav-item">

<a class="nav-link <?= $page === 'cart' ? 'active' : '' ?>"
   href="index.php?page=cart">

🛒 Cart (<?= $cart_count ?>)

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

<div class="alert alert-<?= htmlspecialchars($message_type) ?> alert-dismissible fade show">

<?= htmlspecialchars($message) ?>

<button type="button"
        class="btn-close"
        data-bs-dismiss="alert"
        aria-label="Close">

</button>

</div>

</div>

<?php endif; ?>


<!-- =========================
     HOME PAGE
========================= -->

<?php if ($page === "home"): ?>

<section class="hero">

<div class="container">

<div class="row align-items-center">

<div class="col-lg-6">

<p class="text-uppercase fw-bold"
   style="color:#ef8b38">

Fresh Food • Best Taste

</p>

<h1>

Delicious Food

<br>

<span>For Everyone</span>

</h1>

<p>

Welcome to Taste House Restaurant.

Enjoy fresh, delicious and affordable food.

</p>

<a href="index.php?page=home#menu"
   class="btn btn-food">

Explore Menu

</a>

<a href="index.php?page=about"
   class="btn btn-outline-food">

About Us

</a>

<a href="index.php?page=pricelist"
   class="btn btn-outline-food mt-2">

Price List

</a>

</div>

<div class="col-lg-6">

<img
src="https://images.unsplash.com/photo-1555939594-58d7cb561ad1?auto=format&fit=crop&w=1000&q=80"
class="hero-img"
alt="Restaurant food">

</div>

</div>

</div>

</section>


<!-- MENU SECTION -->

<section class="menu-section container" id="menu">

<div class="text-center mb-5">

<h2>Our Popular Menu</h2>

<p>Choose your favorite food.</p>

</div>

<div class="row g-4">

<?php foreach ($products as $id => $product): ?>

<div class="col-sm-6 col-lg-4">

<div class="card food-card h-100">

<img
src="<?= htmlspecialchars($product["image"]) ?>"
class="card-img-top"
alt="<?= htmlspecialchars($product["name"]) ?>">

<div class="card-body">

<h5>

<?= htmlspecialchars($product["name"]) ?>

</h5>

<p class="text-muted">

<?= htmlspecialchars($product["description"]) ?>

</p>

<div class="d-flex justify-content-between align-items-center">

<span class="food-price">

Rs. <?= number_format($product["price"]) ?>

</span>

<form method="POST">

<input type="hidden"
       name="product_id"
       value="<?= $id ?>">

<button type="submit"
        name="add_to_cart"
        class="btn btn-food btn-sm">

Add +

</button>

</form>

</div>

</div>

</div>

</div>

<?php endforeach; ?>

</div>

<div class="text-center mt-4">

<a href="index.php?page=pricelist"
   class="btn btn-outline-food">

View Full Price List

</a>

</div>

</section>

<?php endif; ?>


<!-- =========================
     ABOUT PAGE
========================= -->

<?php if ($page === "about"): ?>

<section class="page-header">

<div class="container">

<h1>About Us</h1>

<p>Welcome to Taste House Restaurant</p>

</div>

</section>

<section class="container py-5">

<div class="row align-items-center">

<div class="col-md-6">

<img
src="https://images.unsplash.com/photo-1559339352-11d035aa65de?auto=format&fit=crop&w=900&q=80"
class="about-img"
alt="Restaurant interior">

</div>

<div class="col-md-6">

<h2>Our Story</h2>

<p>

Taste House Restaurant is a place where delicious
food meets friendly service.

</p>

<p>

We prepare fresh meals using quality ingredients
and serve our customers with love.

</p>

<p>

Our mission is to provide delicious food
at affordable prices.

</p>

<a href="index.php?page=home#menu"
   class="btn btn-food">

Explore Menu

</a>

<a href="index.php?page=pricelist"
   class="btn btn-outline-food">

Price List

</a>

</div>

</div>

</section>

<?php endif; ?>


<!-- =========================
     PRICE LIST PAGE
========================= -->

<?php if ($page === "pricelist"): ?>

<section class="page-header">

<div class="container">

<h1>Our Price List</h1>

<p>Fresh food with affordable prices</p>

</div>

</section>


<section class="price-list-section container">

<div class="text-center mb-4">

<h2>Food Menu & Prices</h2>

<p>

Check our complete menu and choose your favorite food.

All prices are in Pakistani Rupees (PKR).

</p>

</div>


<div class="table-responsive price-list-table">

<table class="table table-bordered table-hover mb-0">

<thead>

<tr>

<th>Image</th>

<th>Food Item</th>

<th>Description</th>

<th>Price</th>

<th>Order</th>

</tr>

</thead>

<tbody>

<?php foreach ($products as $id => $product): ?>

<tr>

<td>

<img
src="<?= htmlspecialchars($product["image"]) ?>"
class="price-list-image"
alt="<?= htmlspecialchars($product["name"]) ?>">

</td>

<td>

<strong>

<?= htmlspecialchars($product["name"]) ?>

</strong>

</td>

<td>

<span class="price-list-description">

<?= htmlspecialchars($product["description"]) ?>

</span>

</td>

<td>

<span class="price-list-price">

Rs. <?= number_format($product["price"]) ?>

</span>

</td>

<td>

<form method="POST">

<input type="hidden"
       name="product_id"
       value="<?= $id ?>">

<button type="submit"
        name="add_to_cart"
        class="btn btn-food btn-sm">

Add To Cart

</button>

</form>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>


<div class="text-center mt-4">

<a href="index.php?page=cart"
   class="btn btn-food">

Go To Cart

</a>

<a href="index.php?page=home#menu"
   class="btn btn-outline-food">

Back To Menu

</a>

</div>

</section>

<?php endif; ?>


<!-- =========================
     CONTACT PAGE
========================= -->

<?php if ($page === "contact"): ?>

<section class="page-header">

<div class="container">

<h1>Contact Us</h1>

<p>We would love to hear from you!</p>

</div>

</section>

<section class="container py-5">

<div class="row">

<div class="col-md-5">

<h2>Get In Touch</h2>

<p>📍 Karachi, Pakistan</p>

<p>📞 +92 300 1234567</p>

<p>✉ hello@tastehouse.example</p>

<p>🕒 12 PM - 11 PM</p>

</div>


<div class="col-md-7">

<form method="POST"
      class="form-card"
      id="contactForm">

<div class="mb-3">

<label class="form-label">

Your Name

</label>

<input
type="text"
name="name"
id="name"
class="form-control"
required>

</div>

<div class="mb-3">

<label class="form-label">

Email Address

</label>

<input
type="email"
name="email"
id="email"
class="form-control"
required>

</div>

<div class="mb-3">

<label class="form-label">

Message

</label>

<textarea
name="message"
id="message"
class="form-control"
rows="5"
required></textarea>

</div>

<button type="submit"
        name="contact_submit"
        class="btn btn-food">

Send Message

</button>

</form>

</div>

</div>

</section>

<?php endif; ?>


<!-- =========================
     CART PAGE
========================= -->

<?php if ($page === "cart"): ?>

<section class="container py-5">

<h1>Your Shopping Cart</h1>

<?php if (empty($_SESSION["cart"])): ?>

<div class="cart-empty text-center mt-4">

<h3>Your Cart Is Empty</h3>

<p>Add some delicious food to your cart.</p>

<a href="index.php?page=home#menu"
   class="btn btn-food">

Explore Menu

</a>

<a href="index.php?page=pricelist"
   class="btn btn-outline-food">

Price List

</a>

</div>

<?php else: ?>

<div class="table-responsive mt-4">

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

<?php foreach ($_SESSION["cart"] as $id => $item): ?>

<?php

if (!isset($products[$id])) {
    continue;
}

$product = $products[$id];

$subtotal = $product["price"] * $item["qty"];

?>

<tr>

<td>

<?= htmlspecialchars($product["name"]) ?>

</td>

<td>

Rs. <?= number_format($product["price"]) ?>

</td>

<td>

<form method="POST" class="d-flex gap-2">

<input type="hidden"
       name="product_id"
       value="<?= $id ?>">

<input type="number"
       name="qty"
       min="1"
       max="99"
       value="<?= $item["qty"] ?>"
       class="form-control"
       style="width:80px">

<button type="submit"
        name="update_cart"
        class="btn btn-secondary btn-sm">

Update

</button>

</form>

</td>

<td>

Rs. <?= number_format($subtotal) ?>

</td>

<td>

<form method="POST">

<input type="hidden"
       name="product_id"
       value="<?= $id ?>">

<button type="submit"
        name="remove_product"
        class="btn btn-danger btn-sm">

Remove

</button>

</form>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>


<div class="text-end">

<h3>

Grand Total:

Rs. <?= number_format($total) ?>

</h3>

<a href="index.php?page=pricelist"
   class="btn btn-outline-food">

Price List

</a>

<a href="index.php?page=checkout"
   class="btn btn-food btn-lg">

Proceed To Checkout

</a>

</div>

<?php endif; ?>

</section>

<?php endif; ?>


<!-- =========================
     CHECKOUT PAGE
========================= -->

<?php if ($page === "checkout"): ?>

<section class="container py-5">

<div class="row justify-content-center">

<div class="col-lg-7">

<h1>Checkout</h1>

<p>Enter your delivery information.</p>

<?php if (empty($_SESSION["cart"])): ?>

<div class="alert alert-warning">

Your cart is empty.

<a href="index.php?page=home#menu"
   class="alert-link">

Explore Menu

</a>

</div>

<?php else: ?>

<form method="POST" class="form-card">

<div class="mb-3">

<label class="form-label">

Full Name

</label>

<input
type="text"
name="customer_name"
class="form-control"
required>

</div>

<div class="mb-3">

<label class="form-label">

Phone Number

</label>

<input
type="tel"
name="phone"
class="form-control"
required>

</div>

<div class="mb-3">

<label class="form-label">

Delivery Address

</label>

<textarea
name="address"
class="form-control"
rows="4"
required></textarea>

</div>

<h4 class="mt-4">

Payment Method

</h4>

<label class="payment-option">

<input
type="radio"
name="payment"
value="cod"
checked>

Cash On Delivery

</label>

<label class="payment-option">

<input
type="radio"
name="payment"
value="bank">

Bank Transfer

</label>

<hr>

<h4>

Total:

Rs. <?= number_format($total) ?>

</h4>

<button
type="submit"
name="place_order"
class="btn btn-food btn-lg w-100 mt-3">

Place Order

</button>

<p class="text-muted mt-3">

Demo checkout. No real payment is processed.

</p>

</form>

<?php endif; ?>

</div>

</div>

</section>

<?php endif; ?>


<!-- =========================
     FOOTER
========================= -->

<footer class="text-center">

<div class="container">

<h3>Taste House Restaurant</h3>

<p>Fresh Food • Best Taste • Happy Moments</p>

<p>Karachi, Pakistan</p>

<div class="mt-3">

<a href="index.php?page=home"
   class="btn btn-outline-light btn-sm">

Home

</a>

<a href="index.php?page=about"
   class="btn btn-outline-light btn-sm">

About Us

</a>

<a href="index.php?page=pricelist"
   class="btn btn-outline-light btn-sm">

Price List

</a>

<a href="index.php?page=contact"
   class="btn btn-outline-light btn-sm">

Contact Us

</a>

<a href="index.php?page=cart"
   class="btn btn-outline-light btn-sm">

Cart

</a>

</div>

<p class="mt-4">

© <?= date("Y") ?> Taste House Restaurant

</p>

</div>

</footer>


<!-- =========================
     JAVASCRIPT
========================= -->

<script>

$(document).ready(function () {

    // Contact form validation

    $("#contactForm").submit(function () {

        let name = $("#name").val().trim();

        let email = $("#email").val().trim();

        let message = $("#message").val().trim();

        if (name === "" || email === "" || message === "") {

            alert("Please fill all fields");

            return false;

        }

    });


    // Hide alert message

    $(".alert").delay(4000).fadeOut(500);


    // Close mobile navbar after clicking a link

    $(".navbar-nav .nav-link").on("click", function () {

        if (window.innerWidth < 992) {

            $("#navbarMenu").collapse("hide");

        }

    });


    // Card hover effect

    $(".food-card").hover(

        function () {

            $(this).addClass("shadow-lg");

        },

        function () {

            $(this).removeClass("shadow-lg");

        }

    );


    // Smooth scrolling for page section links

    $('a[href*="#"]').on("click", function (event) {

        let target = $(this).attr("href");

        if (target && target.includes("#")) {

            let section = target.substring(target.indexOf("#"));

            if ($(section).length) {

                event.preventDefault();

                $("html, body").animate({

                    scrollTop: $(section).offset().top - 80

                }, 500);

            }

        }

    });

});

</script>


<!-- Bootstrap JS -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>