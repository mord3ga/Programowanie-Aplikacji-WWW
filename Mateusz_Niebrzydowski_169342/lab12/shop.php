<?php
require_once('cart.php');

global $link;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_action']) && $_POST['cart_action'] === 'add') {
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    if ($product_id > 0) {
        addToCart($product_id, $quantity);
        $redirect_url = 'index.php?page=shop&added=1';
        if (isset($_GET['category']) && $_GET['category'] > 0) {
            $redirect_url .= '&category=' . (int)$_GET['category'];
        }
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $redirect_url .= '&search=' . urlencode($_GET['search']);
        }
        header('Location: ' . $redirect_url);
        exit();
    }
}

$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search_query = isset($_GET['search']) ? mysqli_real_escape_string($link, trim($_GET['search'])) : '';

$sql = "SELECT p.*, c.nazwa as kategoria_nazwa 
        FROM products p 
        LEFT JOIN categories c ON p.kategoria = c.id 
        WHERE p.status_dostepnosci = 1 AND p.stan > 0";

if ($category_filter > 0) {
    $sql .= " AND p.kategoria = $category_filter";
}

$sql .= " ORDER BY p.id DESC";

$result = mysqli_query($link, $sql);

$categories_query = "SELECT * FROM categories ORDER BY nazwa ASC";
$categories_result = mysqli_query($link, $categories_query);
?>

<?php if (isset($_GET['added']) && $_GET['added'] == 1): ?>
    <div class="shop-message success">
        Produkt został dodany do koszyka!
    </div>
<?php endif; ?>

<div class="shop-filters">
    <form method="get" action="index.php" class="filter-form">
        <input type="hidden" name="page" value="shop">

        <div class="filter-group">
            <label for="category">📁 Kategoria:</label>
            <select id="category" name="category" class="filter-input">
                <option value="0">-- Wszystkie kategorie --</option>
                <?php
                if ($categories_result && mysqli_num_rows($categories_result) > 0) {
                    while ($category = mysqli_fetch_assoc($categories_result)) {
                        $selected = ($category_filter == $category['id']) ? 'selected' : '';
                        echo '<option value="' . $category['id'] . '" ' . $selected . '>' . htmlspecialchars($category['nazwa']) . '</option>';
                    }
                }
                ?>
            </select>
        </div>
        
        <button type="submit" class="filter-btn">Filtruj</button>
        <?php if ($category_filter > 0 || !empty($search_query)): ?>
            <a href="index.php?page=shop" class="filter-reset">Wyczyść filtry</a>
        <?php endif; ?>
    </form>
</div>

<div class="products-grid">
    <?php
    if ($result && mysqli_num_rows($result) > 0) {
        while ($product = mysqli_fetch_assoc($result)) {
            $vat_procent = (float)$product['vat'];
            $price_brutto = $product['cena_netto'] * (1 + $vat_procent / 100);

            $image_html = '';
            if (!empty($product['zdjecie'])) {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime_type = $finfo->buffer($product['zdjecie']);
                
                if (empty($mime_type) || substr($mime_type, 0, 6) !== 'image/') {
                    $mime_type = 'image/jpeg';
                }
                
                $image_data = base64_encode($product['zdjecie']);
                $image_html = '<img src="data:' . htmlspecialchars($mime_type) . ';base64,' . $image_data . '" alt="' . htmlspecialchars($product['tytul']) . '" class="product-image">';
            } else {
                $image_html = '<div class="product-image-placeholder">📚</div>';
            }
            
            echo '
            <div class="product-card">
                <div class="product-image-container">
                    ' . $image_html . '
                </div>
                <div class="product-info">
                    <h3 class="product-title">' . htmlspecialchars($product['tytul']) . '</h3>
                    <p class="product-author">✍️ ' . htmlspecialchars($product['autor']) . '</p>';
            
            if (!empty($product['kategoria_nazwa'])) {
                echo '<p class="product-category">📁 ' . htmlspecialchars($product['kategoria_nazwa']) . '</p>';
            }
            
            echo '
                    <p class="product-description">' . htmlspecialchars(mb_substr($product['opis'], 0, 150)) . '...</p>
                    <div class="product-footer">
                        <div class="product-price">
                            <span class="price-label">Cena:</span>
                            <span class="price-value">' . number_format($price_brutto, 2, ',', ' ') . ' zł</span>
                        </div>
                        <div class="product-stock">
                            <span class="stock-icon">📦</span>
                            <span class="stock-text">' . $product['stan'] . ' szt.</span>
                        </div>
                    </div>
                    <form method="post" class="add-to-cart-form">
                        <input type="hidden" name="cart_action" value="add">
                        <input type="hidden" name="product_id" value="' . $product['id'] . '">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="add-to-cart-btn">
                            🛒 Dodaj do koszyka
                        </button>
                    </form>
                </div>
            </div>';
        }
    } else {
        echo '
        <div class="no-products">
            <p>😔 Nie znaleziono produktów spełniających kryteria wyszukiwania.</p>
            <a href="index.php?page=shop" class="back-to-shop">← Powrót do wszystkich produktów</a>
        </div>';
    }
    ?>
</div>
