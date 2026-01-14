<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}
function addToCart($product_id, $quantity = 1) {
    $product_id = (int)$product_id;
    $quantity = (int)$quantity;
    
    if ($product_id <= 0 || $quantity <= 0) {
        return false;
    }

    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
    
    return true;
}

function removeFromCart($product_id) {
    $product_id = (int)$product_id;
    
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
        return true;
    }
    
    return false;
}

function updateCartQuantity($product_id, $quantity) {
    $product_id = (int)$product_id;
    $quantity = (int)$quantity;
    
    if ($quantity <= 0) {
        return removeFromCart($product_id);
    }
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] = $quantity;
        return true;
    }
    
    return false;
}
function getCart() {
    return isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
}
function getCartCount() {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return 0;
    }
    
    return array_sum($_SESSION['cart']);
}
function getCartUniqueCount() {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return 0;
    }
    
    return count($_SESSION['cart']);
}
function clearCart() {
    $_SESSION['cart'] = array();
    return true;
}

function isInCart($product_id) {
    $product_id = (int)$product_id;
    return isset($_SESSION['cart'][$product_id]);
}
function getCartItemQuantity($product_id) {
    $product_id = (int)$product_id;
    return isset($_SESSION['cart'][$product_id]) ? $_SESSION['cart'][$product_id] : 0;
}
function getCartDetails($link) {
    $cart = getCart();
    
    if (empty($cart)) {
        return array();
    }

    $product_ids = array_keys($cart);
    $ids_string = implode(',', $product_ids);

    $query = "SELECT p.*, c.nazwa as kategoria_nazwa 
              FROM products p 
              LEFT JOIN categories c ON p.kategoria = c.id 
              WHERE p.id IN ($ids_string)";
    
    $result = mysqli_query($link, $query);
    $products = array();
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($product = mysqli_fetch_assoc($result)) {
            $product['quantity'] = $cart[$product['id']];

            $vat_procent = (float)$product['vat'];
            $product['cena_brutto'] = $product['cena_netto'] * (1 + $vat_procent / 100);

            $product['total_price'] = $product['cena_brutto'] * $product['quantity'];
            
            $products[] = $product;
        }
    }
    
    return $products;
}
function getCartTotal($link) {
    $products = getCartDetails($link);
    $total = 0;
    
    foreach ($products as $product) {
        $total += $product['total_price'];
    }
    
    return $total;
}
function handleCartAction() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = isset($_POST['cart_action']) ? $_POST['cart_action'] : '';
        
        switch ($action) {
            case 'add':
                $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
                $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
                
                if ($product_id > 0) {
                    addToCart($product_id, $quantity);
                    header('Location: index.php?page=cart&added=1');
                    exit();
                }
                break;
                
            case 'remove':
                $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
                
                if ($product_id > 0) {
                    removeFromCart($product_id);
                    header('Location: index.php?page=cart&removed=1');
                    exit();
                }
                break;
                
            case 'update':
                $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
                $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
                
                if ($product_id > 0) {
                    if ($quantity > 0) {
                        updateCartQuantity($product_id, $quantity);
                    } else {
                        removeFromCart($product_id);
                    }
                    header('Location: index.php?page=cart&updated=1');
                    exit();
                }
                break;
                
            case 'clear':
                clearCart();
                header('Location: index.php?page=cart&cleared=1');
                exit();
                break;
                
            case 'checkout':
                $cart = getCart();
                if (!empty($cart)) {
                    clearCart();
                    header('Location: index.php?page=cart&order_success=1');
                    exit();
                } else {
                    header('Location: index.php?page=cart&empty=1');
                    exit();
                }
                break;
        }
    }
}
function displayCart($link) {
    handleCartAction();

    $cart_items = getCartDetails($link);
    $cart_total = getCartTotal($link);
    $cart_count = getCartCount();

    $message = '';
    if (isset($_GET['added'])) {
        $message = '<div class="cart-message success">Produkt został dodany do koszyka!</div>';
    } elseif (isset($_GET['removed'])) {
        $message = '<div class="cart-message success">Produkt został usunięty z koszyka!</div>';
    } elseif (isset($_GET['updated'])) {
        $message = '<div class="cart-message success">Koszyk został zaktualizowany!</div>';
    } elseif (isset($_GET['order_success'])) {
        $message = '<div class="cart-message success">Zamówienie zostało złożone!</div>';
    } elseif (isset($_GET['empty'])) {
        $message = '<div class="cart-message error">Koszyk jest pusty! Dodaj produkty przed złożeniem zamówienia.</div>';
    }

    ?>

    <?php echo $message; ?>

    <?php if (empty($cart_items)): ?>
        <div class="empty-cart">
            <div class="empty-cart-icon">🛒</div>
            <h3>Twój koszyk jest pusty</h3>
            <a href="index.php?page=shop" class="btn-continue-shopping">← Przejdź do sklepu</a>
        </div>
    <?php else: ?>
        <div class="cart-container">
            <div class="cart-items">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Produkt</th>
                            <th>Cena</th>
                            <th>Ilość</th>
                            <th>Suma</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td class="cart-product">
                                    <div class="cart-product-info">
                                        <?php if (!empty($item['zdjecie'])): ?>
                                            <?php
                                            $finfo = new finfo(FILEINFO_MIME_TYPE);
                                            $mime_type = $finfo->buffer($item['zdjecie']);
                                            if (empty($mime_type) || substr($mime_type, 0, 6) !== 'image/') {
                                                $mime_type = 'image/jpeg';
                                            }
                                            $image_data = base64_encode($item['zdjecie']);
                                            ?>
                                            <img src="data:<?php echo htmlspecialchars($mime_type); ?>;base64,<?php echo $image_data; ?>" alt="<?php echo htmlspecialchars($item['tytul']); ?>" class="cart-product-image">
                                        <?php else: ?>
                                            <div class="cart-product-placeholder">📚</div>
                                        <?php endif; ?>
                                        <div class="cart-product-details">
                                            <strong><?php echo htmlspecialchars($item['tytul']); ?></strong>
                                            <small>✍️ <?php echo htmlspecialchars($item['autor']); ?></small>
                                            <?php if (!empty($item['kategoria_nazwa'])): ?>
                                                <small>📁 <?php echo htmlspecialchars($item['kategoria_nazwa']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="cart-price">
                                    <?php echo number_format($item['cena_brutto'], 2, ',', ' '); ?> zł
                                </td>
                                <td class="cart-quantity">
                                    <form method="post" action="index.php?page=cart" class="quantity-form">
                                        <input type="hidden" name="cart_action" value="update">
                                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                        <div class="quantity-controls">
                                            <button type="button" class="qty-btn" onclick="changeQuantity(this, -1)">-</button>
                                            <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="0" max="<?php echo $item['stan']; ?>" class="qty-input">
                                            <button type="button" class="qty-btn" onclick="changeQuantity(this, 1)">+</button>
                                        </div>
                                        <small class="stock-info">Dostępne: <?php echo $item['stan']; ?> szt.</small>
                                        <button type="submit" class="btn-update-qty">Aktualizuj</button>
                                    </form>
                                </td>
                                <td class="cart-total">
                                    <strong><?php echo number_format($item['total_price'], 2, ',', ' '); ?> zł</strong>
                                </td>
                                <td class="cart-actions">
                                    <form method="post" action="index.php?page=cart" style="display: inline;">
                                        <input type="hidden" name="cart_action" value="remove">
                                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" class="btn-remove" onclick="return confirm('Czy na pewno chcesz usunąć ten produkt z koszyka?')">🗑️ Usuń</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="cart-summary">
                <h3>Podsumowanie zamówienia</h3>
                <div class="summary-row">
                    <span>Liczba produktów:</span>
                    <strong><?php echo $cart_count; ?> szt.</strong>
                </div>
                <div class="summary-row total">
                    <span>Suma całkowita:</span>
                    <strong class="total-price"><?php echo number_format($cart_total, 2, ',', ' '); ?> zł</strong>
                </div>
                
                <div class="cart-buttons">
                    <a href="index.php?page=shop" class="btn-continue">← Kontynuuj zakupy</a>
                    <form method="post" action="index.php?page=cart" style="width: 100%;">
                        <input type="hidden" name="cart_action" value="checkout">
                        <button type="submit" class="btn-checkout">Złóż zamówienie →</button>
                    </form>
                </div>
                
                <form method="post" action="index.php?page=cart" class="clear-cart-form">
                    <input type="hidden" name="cart_action" value="clear">
                    <button type="submit" class="btn-clear-cart" onclick="return confirm('Czy na pewno chcesz wyczyścić cały koszyk?')">🗑️ Wyczyść koszyk</button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <script>
    function changeQuantity(btn, change) {
        const input = btn.parentElement.querySelector('.qty-input');
        let currentValue = parseInt(input.value);
        let newValue = currentValue + change;
        let max = parseInt(input.max);
        
        if (newValue >= 0 && newValue <= max) {
            input.value = newValue;
        }
    }
    document.querySelectorAll('.qty-input').forEach(input => {
        input.addEventListener('change', function() {
            if (this.value < 0) this.value = 0;
            if (this.value > this.max) this.value = this.max;
        });
    });
    </script>
    <?php
}
?>
