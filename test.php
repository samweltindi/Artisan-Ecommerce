<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ArtisanOrders - Handmade. Kenyan. Authentic.</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
    background: #8CF0A8;
    color: #222;
  }

  header, nav, main, footer {
    max-width: 1500px;
    margin: 0 auto;
    padding: 0 20px;
  }

  /* ===== Header ===== */
  header {
    background: #fff;
    padding: 18px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
  }

  header h1 {
    font-size: 22px;
    letter-spacing: 0.5px;
  }

  header h1 span {
    font-size: 13px;
    display: block;
    color: #555;
    font-weight: normal;
    letter-spacing: 0.5px;
  }

  header form {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    max-width: 500px;
  }

  header input[type="text"] {
    flex: 1;
    padding: 10px 16px;
    border: 1px solid #ccc;
    border-radius: 22px;
    font-size: 14px;
    outline: none;
  }

  header input[type="text"]:focus {
    border-color: #0E4D3C;
  }

  header button {
    background: #0E4D3C;
    color: #fff;
    border: none;
    padding: 10px 24px;
    border-radius: 4px;
    font-weight: 600;
    letter-spacing: 0.5px;
    cursor: pointer;
    font-size: 14px;
  }

  header button:hover {
    background: #0a3a2d;
  }

  header aside {
    display: flex;
    align-items: center;
    gap: 18px;
  }

  header aside > span {
    font-size: 30px;
  }

  header aside p {
    font-size: 14px;
    line-height: 1.5;
  }

  header aside a {
    color: #333;
    text-decoration: none;
  }

  header aside a:hover {
    text-decoration: underline;
  }

  header aside p a:last-child {
    color: #999;
  }

  header aside p a:first-child {
    font-weight: 600;
  }

  header aside strong {
    font-size: 16px;
  }

  /* ===== Category bar ===== */
  nav {
    padding: 20px;
  }

  nav div {
    background: #fff;
    border-radius: 6px;
    padding: 18px 24px;
    display: flex;
    align-items: center;
    gap: 30px;
    flex-wrap: wrap;
  }

  nav button {
    border: 1px solid #999;
    padding: 8px 20px;
    border-radius: 4px;
    background: #fff;
    font-size: 14px;
    cursor: pointer;
    color: #333;
  }

  nav ul {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    list-style: none;
  }

  nav a {
    text-decoration: none;
    color: #444;
    font-weight: 600;
    font-size: 15px;
  }

  nav a:hover {
    color: #0E4D3C;
  }

  /* ===== Product grid ===== */
  section {
    background: #fff;
    border-radius: 6px;
    padding: 30px;
    margin: 0 20px 20px;
  }

  section ul {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 20px;
    list-style: none;
  }

  section li {
    background: #d9d9d9;
    border-radius: 6px;
    padding: 16px;
    display: flex;
    flex-direction: column;
  }

  section li div {
    background: #fff;
    aspect-ratio: 1 / 1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 44px;
    margin-bottom: 12px;
    border-radius: 4px;
  }

  section li h3 {
    font-size: 15px;
    color: #333;
    margin-bottom: 4px;
  }

  section li p {
    font-size: 14px;
    color: #222;
    margin-bottom: 2px;
  }

  section li p:nth-of-type(2) {
    font-size: 13px;
    color: #555;
    margin-bottom: 12px;
  }

  section li button {
    margin-top: auto;
    background: #0E4D3C;
    color: #fff;
    border: none;
    padding: 10px;
    border-radius: 4px;
    font-size: 14px;
    cursor: pointer;
  }

  section li button:hover {
    background: #0a3a2d;
  }

  /* ===== About section ===== */
  article {
    background: #ece7e7;
    border-radius: 6px;
    padding: 30px;
    margin: 0 20px 30px;
  }

  article h2 {
    font-size: 20px;
    margin-bottom: 10px;
  }

  article h3 {
    font-size: 16px;
    margin-bottom: 10px;
  }

  article p {
    color: #555;
    line-height: 1.6;
    margin-bottom: 14px;
    font-size: 15px;
  }

  /* ===== Footer ===== */
  footer {
    background: #6b6b8f;
    color: #fff;
    padding: 24px 20px 0;
  }

  footer > div {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 16px;
  }

  footer h4 {
    font-size: 16px;
    margin-bottom: 8px;
  }

  footer p {
    font-size: 14px;
    line-height: 1.6;
  }

  footer span {
    display: flex;
    gap: 14px;
    font-size: 20px;
    align-items: center;
  }

  footer p:last-child {
    text-align: center;
    font-weight: 700;
    font-size: 15px;
    padding: 10px 0 20px;
    border-top: 1px solid rgba(255,255,255,0.2);
  }

  /* ===== Responsive ===== */
  @media (max-width: 900px) {
    section ul { grid-template-columns: repeat(3, 1fr); }
    header form { order: 3; max-width: 100%; width: 100%; }
  }

  @media (max-width: 600px) {
    section ul { grid-template-columns: repeat(2, 1fr); }
    header { flex-direction: column; align-items: flex-start; }
    header aside { width: 100%; justify-content: space-between; }
    footer > div { text-align: center; justify-content: center; }
  }
</style>
</head>
<body>

<header>
  <div style="display:flex; align-items:center; gap:12px;">
    <span style="font-size:40px;">🧺</span>
    <h1>ArtisanOrders<span>Handmade. Kenyan. Authentic.</span></h1>
  </div>

  <form onsubmit="return false;">
    <input type="text" placeholder="Search products...">
    <button type="submit">SEARCH</button>
  </form>

  <aside>
    <span>👤</span>
    <p>
      <a href="#">Login</a> | <a href="#">Register</a><br>
      <strong><a href="#">Become a seller</a></strong> | <a href="#">Login to seller</a>
    </p>
    <p style="display:flex; align-items:center; gap:8px; font-weight:600;">
      <span style="font-size:28px;">🛒</span> Cart
    </p>
  </aside>
</header>

<nav>
  <div>
    <button>Categories</button>
    <ul>
      <li><a href="#">Jewelry</a></li>
      <li><a href="#">Clothing</a></li>
      <li><a href="#">Furniture</a></li>
      <li><a href="#">Handcrafts</a></li>
    </ul>
  </div>
</nav>

<main>
  <section>
    <ul id="products"></ul>
  </section>

  <article>
    <h2>ArtisanOrders - Online Shopping and Ecommerce marketplace in Kenya</h2>
    <h3>Welcome to ArtisanOrders</h3>
    <p>ArtisanOrders is an online shopping site and an ecommerce store which offers local handmade artisan products to buyers in Kenya. Discover a diverse range of products from our trusted sellers and suppliers, ensuring quality and authenticity in every purchase. With our user-friendly platform, you can explore, compare and buy with ease.</p>
    <p>The platform: ArtisanOrders platform offers visibility to handmade and authenticated local made goods products like jewelry, wood carvings and cultural clothings. Buyers and sellers are able to buy and sell online with ease.</p>
    <p>ArtisanOrders is not just a marketplace. It is a collaborative ecosystem. We've forged strong partnerships with local artisans and small scale manufacturers to bring you the best prices and authentic products.</p>
  </article>
</main>

<footer>
  <div>
    <div>
      <h4>Contact us</h4>
      <p>Email: Samweltindi07@gmail.com<br>Phone: 0742086326</p>
    </div>
    <div>
      <h4>Follow us</h4>
      <span>📘 | ✖️ | 📞 | 📷</span>
    </div>
  </div>
  <p>Copyright @2026 ArtisanOrders, All rights reserved.</p>
</footer>
</body>
</html>
