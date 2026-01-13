<?php
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_start();

    $_SESSION = array();

    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-42000, '/');
        unset($_COOKIE[session_name()]);
    }

    session_unset();
    session_destroy();

    header("Location: index.php?page=admin&logout=success");
    exit();
}

session_start();
function FormularzLogowania($message = '', $isError = true) {
    $messageHtml = '';
    if (!empty($message)) {
        $cssClass = $isError ? 'error' : 'success';
        $messageHtml = '<p class="message ' . $cssClass . '">' . htmlspecialchars($message) . '</p>';
    }

    echo '
    <div class="login-container">
        <h2>🔐</h2>
        <h2>Panel administratora</h2>
        ' . $messageHtml . '
        <form method="post" action="index.php?page=admin">
            <label for="login_user">Login:</label>
            <input type="text" id="login_user" name="login_user" required autofocus>
            
            <label for="login_pass">Hasło:</label>
            <input type="password" id="login_pass" name="login_pass" required>
            
            <input type="submit" value="Zaloguj się">
        </form>
        <div class="back-link">
            <a href="index.php">← Powrót do strony głównej</a>
        </div>
    </div>';
}
function SprawdzLogowanie($admin_login, $admin_pass) {
    if (isset($_POST['login_user']) && isset($_POST['login_pass'])) {
        $user_login = $_POST['login_user'];
        $user_pass = $_POST['login_pass'];
        
        if ($user_login === $admin_login && $user_pass === $admin_pass) {
            $_SESSION['zalogowany'] = true;
            $_SESSION['login'] = $user_login;
            return true;
        } else {
            return false;
        }
    }
    return false;
}
function PanelAdministratora() {
    if (!isset($_SESSION['zalogowany']) || $_SESSION['zalogowany'] !== true) {
        return false;
    }

    echo '
    <div class="admin-panel">
        <div class="admin-header">
            <h1>Panel administratora</h1>
            <div class="header-right">
                <a href="/bookstore/index.php?page=admin&action=logout" class="logout-btn">Wyloguj się</a>
                <div class="user-info">
                    Zalogowany jako: <strong>' . htmlspecialchars($_SESSION['login']) . '</strong>
                </div>
            </div>
        </div>
        
        <div class="admin-menu">
            <div class="admin-menu-item">
                <h3>📚 Zarządzanie książkami</h3>
                <p>Dodawaj, edytuj i usuwaj książki z bazy danych</p>
                <a href="../index.php?page=admin&action=books">Otwórz</a>
            </div>
            
            <div class="admin-menu-item">
                <h3>📝 Zarządzanie treścią</h3>
                <p>Edytuj zawartość podstron serwisu</p>
                <a href="index.php?page=admin&action=pages">Otwórz</a>
            </div>
            
            <div class="admin-menu-item">
                <h3>📁 Zarządzanie kategoriami</h3>
                <p>Zarządzaj kategoriami i podkategoriami produktów</p>
                <a href="index.php?page=admin&action=categories">Otwórz</a>
            </div>
        </div>
        
        <div class="back-link">
            <a href="index.php">← Powrót do strony głównej</a>
        </div>
    </div>';

    return true;
}
function ListaPodstron() {
    global $link;

    if (!isset($_SESSION['zalogowany']) || $_SESSION['zalogowany'] !== true) {
        header("Location: index.php?page=admin");
        exit();
    }
    
    echo '
    <div class="admin-container">
        <div class="admin-header">
            <h1>📄 Lista podstron</h1>
            <div class="header-right">
                <a href="index.php?page=admin&action=logout" class="logout-btn">Wyloguj się</a>
                <div class="user-info">Zalogowany jako: <strong>' . htmlspecialchars($_SESSION['login']) . '</strong></div>
            </div>
        </div>
        
        <a href="index.php?page=admin&action=add" class="btn-add">+ Dodaj nową podstronę</a>
        
        <table class="pages-table">
            <thead>
                <tr>
                    <th class="col-id">ID</th>
                    <th>Tytuł podstrony</th>
                    <th style="width: 150px;">Alias</th>
                    <th class="col-actions">Akcje</th>
                </tr>
            </thead>
            <tbody>';

    $query = "SELECT * FROM page_list ORDER BY id ASC";
    $result = mysqli_query($link, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $counter = 0;
        $max_iterations = 100;
        
        while ($row = mysqli_fetch_array($result)) {
            $counter++;
            if ($counter > $max_iterations) {
                echo '<tr><td colspan="4" class="message error">⚠️ Osiągnięto limit podstron (' . $max_iterations . '). Przerwano wyświetlanie.</td></tr>';
                break;
            }
            
            $id = $row['id'];

            if (isset($row['page_title']) && !empty($row['page_title'])) {
                $title = htmlspecialchars($row['page_title']);
            } else {
                $title = 'Strona ID: ' . $id;
            }

            $alias = isset($row['alias']) && !empty($row['alias']) ? htmlspecialchars($row['alias']) : '<span style="color: #999;">brak</span>';
            
            echo '
                    <tr>
                        <td><strong>#' . $id . '</strong></td>
                        <td>' . $title . '</td>
                        <td>' . $alias . '</td>
                        <td>
                            <a href="index.php?page=admin&action=edit&id=' . $id . '" class="btn btn-edit">✏️ Edytuj</a>
                            <a href="index.php?page=admin&action=delete&id=' . $id . '" class="btn btn-delete" onclick="return confirm(\'Czy na pewno chcesz usunąć podstronę: ' . addslashes($title) . '?\')">🗑️ Usuń</a>
                        </td>
                    </tr>';
        }
    } else {
        echo '
                    <tr>
                        <td colspan="4" class="no-pages">
                            Brak podstron w bazie danych.
                        </td>
                    </tr>';
    }
    
    echo '
            </tbody>
        </table>
        
        <div class="back-link">
            <a href="index.php?page=admin">← Powrót do panelu głównego</a> | 
            <a href="index.php">← Powrót do strony głównej</a>
        </div>
    </div>';
}

function WyswietlFormularzPodstrony($title = '', $content = '', $active = 1, $alias = '', $id = 0, $isEdit = false) {
    $pageTitle = $isEdit ? '✏️ Edycja podstrony' : '➕ Dodaj nową podstronę';
    $buttonText = $isEdit ? '💾 Zapisz zmiany' : '➕ Dodaj podstronę';
    $formAction = $isEdit ? "index.php?page=admin&action=edit&id=$id" : "index.php?page=admin&action=add";
    
    echo '
    <div class="admin-container">
        <div class="admin-header">
            <h1>' . $pageTitle . '</h1>
            <div class="header-right">
                <a href="/bookstore/index.php?page=admin&action=logout" class="logout-btn">Wyloguj się</a>
                <div class="user-info">Zalogowany jako: <strong>' . htmlspecialchars($_SESSION['login']) . '</strong></div>
            </div>
        </div>
        
        <form method="post" action="' . $formAction . '" class="edit-form">
            <div class="form-group">
                <label for="page_title">Tytuł podstrony (wyświetlany w menu):</label>
                <input type="text" id="page_title" name="page_title" value="' . htmlspecialchars($title) . '" required class="form-input">
            </div>
            
            <div class="form-group">
                <label for="page_alias">Alias (opcjonalne):</label>
                <input type="text" id="page_alias" name="page_alias" value="' . htmlspecialchars($alias) . '" class="form-input">
            </div>
            
            <div class="form-group">
                <label for="page_content">Treść strony:</label>
                <textarea id="page_content" name="page_content" rows="15" required class="form-textarea">' . htmlspecialchars($content) . '</textarea>
            </div>
            
            <div class="form-group checkbox-group">
                <label class="checkbox-label">
                    <input type="checkbox" id="page_active" name="page_active" value="1" ' . ($active ? 'checked' : '') . ' class="form-checkbox">
                    <span>Strona aktywna</span>
                </label>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="save_page" class="btn btn-save">' . $buttonText . '</button>
                <a href="index.php?page=admin&action=pages" class="btn btn-cancel">❌ Anuluj</a>
            </div>
        </form>
        
        <div class="back-link">
            <a href="index.php?page=admin&action=pages">← Powrót do listy podstron</a> | 
            <a href="index.php?page=admin">← Powrót do panelu głównego</a>
        </div>
    </div>';
}
function EdytujPodstrone() {
    global $link;

    if (!isset($_SESSION['zalogowany']) || $_SESSION['zalogowany'] !== true) {
        header("Location: index.php?page=admin");
        exit();
    }

    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($id <= 0) {
        echo '<div class="admin-container"><p class="message error">Nieprawidłowe ID podstrony.</p></div>';
        return;
    }

    if (isset($_POST['save_page'])) {
        $title = mysqli_real_escape_string($link, $_POST['page_title']);
        $content = mysqli_real_escape_string($link, $_POST['page_content']);
        $alias = mysqli_real_escape_string($link, trim($_POST['page_alias']));
        $active = isset($_POST['page_active']) ? 1 : 0;
        
        $query = "UPDATE page_list SET 
                  page_title = '$title', 
                  page_content = '$content', 
                  alias = " . (!empty($alias) ? "'$alias'" : "NULL") . ",
                  status = $active 
                  WHERE id = $id LIMIT 1";
        
        if (mysqli_query($link, $query)) {
            echo '<div class="admin-container">
                    <p class="message success">✅ Podstrona została zaktualizowana!</p>
                    <div class="back-link">
                        <a href="index.php?page=admin&action=pages">← Powrót do listy podstron</a>
                    </div>
                  </div>';
            return;
        } else {
            echo '<div class="admin-container"><p class="message error">❌ Błąd podczas zapisywania: ' . mysqli_error($link) . '</p></div>';
        }
    }

    $query = "SELECT * FROM page_list WHERE id = $id LIMIT 1";
    $result = mysqli_query($link, $query);
    
    if (!$result || mysqli_num_rows($result) == 0) {
        echo '<div class="admin-container"><p class="message error">Nie znaleziono podstrony o ID: ' . $id . '</p></div>';
        return;
    }
    
    $row = mysqli_fetch_assoc($result);
    $title = $row['page_title'];
    $content = $row['page_content'];
    $alias = isset($row['alias']) ? $row['alias'] : '';
    $active = isset($row['status']) ? (int)$row['status'] : 1;

    WyswietlFormularzPodstrony($title, $content, $active, $alias, $id, true);
}
function DodajNowaPodstrone() {
    global $link;

    if (!isset($_SESSION['zalogowany']) || $_SESSION['zalogowany'] !== true) {
        header("Location: index.php?page=admin");
        exit();
    }

    if (isset($_POST['save_page'])) {
        $title = mysqli_real_escape_string($link, $_POST['page_title']);
        $content = mysqli_real_escape_string($link, $_POST['page_content']);
        $alias = mysqli_real_escape_string($link, trim($_POST['page_alias']));
        $active = isset($_POST['page_active']) ? 1 : 0;

        $query = "INSERT INTO page_list (page_title, page_content, alias, status) 
                  VALUES ('$title', '$content', " . (!empty($alias) ? "'$alias'" : "NULL") . ", $active)";
        
        if (mysqli_query($link, $query)) {
            $new_id = mysqli_insert_id($link);
            echo '<div class="admin-container">
                    <p class="message success">✅ Nowa podstrona została dodana! (ID: ' . $new_id . ')</p>
                    <div class="back-link">
                        <a href="index.php?page=admin&action=pages">← Powrót do listy podstron</a> | 
                        <a href="index.php?page=admin&action=add">+ Dodaj kolejną</a>
                    </div>
                  </div>';
            return;
        } else {
            echo '<div class="admin-container"><p class="message error">❌ Błąd podczas dodawania: ' . mysqli_error($link) . '</p></div>';
        }
    }
    WyswietlFormularzPodstrony('', '', 1, '', 0, false);
}

function UsunPodstrone() {
    global $link;

    if (!isset($_SESSION['zalogowany']) || $_SESSION['zalogowany'] !== true) {
        header("Location: index.php?page=admin");
        exit();
    }

    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($id <= 0) {
        echo '<div class="admin-container">
                <p class="message error">❌ Nieprawidłowe ID podstrony.</p>
                <div class="back-link">
                    <a href="index.php?page=admin&action=pages">← Powrót do listy podstron</a>
                </div>
              </div>';
        return;
    }

    $query_check = "SELECT page_title FROM page_list WHERE id = $id LIMIT 1";
    $result_check = mysqli_query($link, $query_check);
    
    if (!$result_check || mysqli_num_rows($result_check) == 0) {
        echo '<div class="admin-container">
                <p class="message error">❌ Nie znaleziono podstrony o ID: ' . $id . '</p>
                <div class="back-link">
                    <a href="index.php?page=admin&action=pages">← Powrót do listy podstron</a>
                </div>
              </div>';
        return;
    }
    
    $row = mysqli_fetch_assoc($result_check);
    $page_title = htmlspecialchars($row['page_title']);

    $query_delete = "DELETE FROM page_list WHERE id = $id LIMIT 1";
    
    if (mysqli_query($link, $query_delete)) {
        echo '<div class="admin-container">
                <div class="admin-header">
                    <h1>🗑️ Usuwanie Podstrony</h1>
                    <div class="header-right">
                        <a href="/bookstore/index.php?page=admin&action=logout" class="logout-btn">Wyloguj się</a>
                        <div class="user-info">Zalogowany jako: <strong>' . htmlspecialchars($_SESSION['login']) . '</strong></div>
                    </div>
                </div>
                
                <p class="message success">✅ Podstrona "<strong>' . $page_title . '</strong>" (ID: ' . $id . ') została pomyślnie usunięta!</p>
                
                <div class="back-link">
                    <a href="index.php?page=admin&action=pages">← Powrót do listy podstron</a> | 
                    <a href="index.php?page=admin">← Powrót do panelu głównego</a>
                </div>
              </div>';
    } else {
        echo '<div class="admin-container">
                <p class="message error">❌ Błąd podczas usuwania podstrony: ' . mysqli_error($link) . '</p>
                <div class="back-link">
                    <a href="index.php?page=admin&action=pages">← Powrót do listy podstron</a>
                </div>
              </div>';
    }
}
function CzyJestPotomkiem($potential_descendant_id, $ancestor_id) {
    global $link;
    
    if ($potential_descendant_id == $ancestor_id) {
        return true;
    }
    
    $current_id = $potential_descendant_id;
    $depth = 0;
    $max_depth = 20;
    
    while ($current_id > 0 && $depth < $max_depth) {
        $query = "SELECT matka FROM categories WHERE id = $current_id LIMIT 1";
        $result = mysqli_query($link, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $current_id = $row['matka'];
            
            if ($current_id == $ancestor_id) {
                return true;
            }
        } else {
            break;
        }
        
        $depth++;
    }
    
    return false;
}
function BudujOpcjeSelectRekurencyjnie($parent_id, $level = 0, &$counter = 0, $selected_id = 0, $exclude_id = 0, $max_depth = 10) {
    global $link;

    if ($level > $max_depth) {
        return;
    }

    if ($counter > 100) {
        return;
    }

    $query = "SELECT * FROM categories WHERE matka = $parent_id ORDER BY nazwa ASC";
    $result = mysqli_query($link, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($kategoria = mysqli_fetch_assoc($result)) {
            $counter++;
            
            if ($counter > 100) {
                break;
            }
            
            $id = $kategoria['id'];
            $nazwa = htmlspecialchars($kategoria['nazwa']);

            if ($exclude_id > 0 && ($id == $exclude_id || CzyJestPotomkiem($id, $exclude_id))) {
                continue;
            }

            $indent = str_repeat('— ', $level);
            $selected = ($id == $selected_id) ? 'selected' : '';
            
            echo '<option value="' . $id . '" ' . $selected . '>' . $indent . $nazwa . '</option>';

            BudujOpcjeSelectRekurencyjnie($id, $level + 1, $counter, $selected_id, $exclude_id, $max_depth);
        }
    }
}
function WyswietlKategorieRekurencyjnie($parent_id, $level = 0, &$counter = 0, $max_depth = 10) {
    global $link;

    if ($level > $max_depth) {
        echo '<div style="padding: 10px; color: red;">⚠️ Osiągnięto maksymalną głębokość zagnieżdżenia (' . $max_depth . ').</div>';
        return;
    }

    if ($counter > 100) {
        echo '<div style="padding: 10px; color: red;">⚠️ Osiągnięto limit kategorii (100).</div>';
        return;
    }

    $query = "SELECT * FROM categories WHERE matka = $parent_id ORDER BY nazwa ASC";
    $result = mysqli_query($link, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $padding_left = $level * 30;

        $icons = ['📁', '📂', '📄', '📃', '📋', '📝', '🗂️', '🗃️', '📑', '📰', '📄'];
        $icon = isset($icons[$level]) ? $icons[$level] : '📄';

        $css_class = ($level == 0) ? 'parent' : 'child level-' . $level;
        
        while ($kategoria = mysqli_fetch_assoc($result)) {
            $counter++;

            if ($counter > 100) {
                echo '<div style="padding: 10px; color: red;">⚠️ Osiągnięto limit kategorii (100).</div>';
                break;
            }
            
            $id = $kategoria['id'];
            $nazwa = htmlspecialchars($kategoria['nazwa']);
            
            echo '
            <div class="category-item ' . $css_class . '" style="padding-left: ' . (20 + $padding_left) . 'px;">
                <span class="category-icon">' . $icon . '</span>
                <span class="category-name">' . $nazwa . '</span>
                <span class="category-id">(ID: ' . $id . ', Poziom: ' . $level . ')</span>
                <div class="category-actions">
                    <a href="index.php?page=admin&action=edit_category&id=' . $id . '" class="btn btn-edit btn-sm">✏️ Edytuj</a>
                    <a href="index.php?page=admin&action=delete_category&id=' . $id . '" class="btn btn-delete btn-sm" onclick="return confirm(\'Czy na pewno chcesz usunąć kategorię: ' . addslashes($nazwa) . '?\')">🗑️ Usuń</a>
                </div>
            </div>';

            WyswietlKategorieRekurencyjnie($id, $level + 1, $counter, $max_depth);
        }
    }
}
function ListaKategorii() {
    global $link;

    if (!isset($_SESSION['zalogowany']) || $_SESSION['zalogowany'] !== true) {
        header("Location: index.php?page=admin");
        exit();
    }
    
    echo '
    <div class="admin-container">
        <div class="admin-header">
            <h1>📁 Lista kategorii</h1>
            <div class="header-right">
                <a href="index.php?page=admin&action=logout" class="logout-btn">Wyloguj się</a>
                <div class="user-info">Zalogowany jako: <strong>' . htmlspecialchars($_SESSION['login']) . '</strong></div>
            </div>
        </div>
        
        <a href="index.php?page=admin&action=add_category" class="btn-add">+ Dodaj nową kategorię</a>
        
        <div class="category-tree">';

    $check_query = "SELECT COUNT(*) as total FROM categories";
    $check_result = mysqli_query($link, $check_query);
    $check_row = mysqli_fetch_assoc($check_result);
    
    if ($check_row['total'] > 0) {
        $counter = 0;
        WyswietlKategorieRekurencyjnie(0, 0, $counter, 10);
    } else {
        echo '<p class="no-pages">Brak kategorii w bazie danych.</p>';
    }
    
    echo '
        </div>
        
        <div class="back-link">
            <a href="index.php?page=admin">← Powrót do panelu głównego</a>
        </div>
    </div>';
}
function DodajKategorie() {
    global $link;

    if (!isset($_SESSION['zalogowany']) || $_SESSION['zalogowany'] !== true) {
        header("Location: index.php?page=admin");
        exit();
    }

    if (isset($_POST['save_category'])) {
        $nazwa = mysqli_real_escape_string($link, $_POST['category_name']);
        $matka = (int)$_POST['category_parent'];

        $query = "INSERT INTO categories (nazwa, matka) VALUES ('$nazwa', $matka)";
        
        if (mysqli_query($link, $query)) {
            $new_id = mysqli_insert_id($link);
            echo '<div class="admin-container">
                    <p class="message success">✅ Kategoria "' . htmlspecialchars($nazwa) . '" została dodana! (ID: ' . $new_id . ')</p>
                    <div class="back-link">
                        <a href="index.php?page=admin&action=categories">← Powrót do listy kategorii</a> | 
                        <a href="index.php?page=admin&action=add_category">+ Dodaj kolejną</a>
                    </div>
                  </div>';
            return;
        } else {
            echo '<div class="admin-container"><p class="message error">❌ Błąd podczas dodawania: ' . mysqli_error($link) . '</p></div>';
        }
    }
    
    echo '
    <div class="admin-container">
        <div class="admin-header">
            <h1>➕ Dodaj nową kategorię</h1>
            <div class="header-right">
                <a href="index.php?page=admin&action=logout" class="logout-btn">Wyloguj się</a>
                <div class="user-info">Zalogowany jako: <strong>' . htmlspecialchars($_SESSION['login']) . '</strong></div>
            </div>
        </div>
        
        <form method="post" action="index.php?page=admin&action=add_category" class="edit-form">
            <div class="form-group">
                <label for="category_name">Nazwa kategorii:</label>
                <input type="text" id="category_name" name="category_name" required class="form-input">
            </div>
            
            <div class="form-group">
                <label for="category_parent">Kategoria nadrzędna (matka):</label>
                <select id="category_parent" name="category_parent" class="form-input">
                    <option value="0">-- Kategoria główna (brak matki) --</option>';

    $counter_select = 0;
    BudujOpcjeSelectRekurencyjnie(0, 0, $counter_select, 0, 0, 10);
    
    echo '
                </select>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="save_category" class="btn btn-save">➕ Dodaj kategorię</button>
                <a href="index.php?page=admin&action=categories" class="btn btn-cancel">❌ Anuluj</a>
            </div>
        </form>
        
        <div class="back-link">
            <a href="index.php?page=admin&action=categories">← Powrót do listy kategorii</a>
        </div>
    </div>';
}
function EdytujKategorie() {
    global $link;

    if (!isset($_SESSION['zalogowany']) || $_SESSION['zalogowany'] !== true) {
        header("Location: index.php?page=admin");
        exit();
    }
    
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($id <= 0) {
        echo '<div class="admin-container"><p class="message error">Nieprawidłowe ID kategorii.</p></div>';
        return;
    }

    if (isset($_POST['save_category'])) {
        $nazwa = mysqli_real_escape_string($link, $_POST['category_name']);
        $matka = (int)$_POST['category_parent'];

        if ($matka == $id) {
            echo '<div class="admin-container"><p class="message error">❌ Kategoria nie może być matką samej siebie!</p></div>';
        } else {
            $is_descendant = false;
            $check_id = $matka;
            $depth_counter = 0;
            $max_depth_check = 20;
            
            while ($check_id > 0 && $depth_counter < $max_depth_check) {
                if ($check_id == $id) {
                    $is_descendant = true;
                    break;
                }

                $check_query = "SELECT matka FROM categories WHERE id = $check_id LIMIT 1";
                $check_result = mysqli_query($link, $check_query);
                
                if ($check_result && mysqli_num_rows($check_result) > 0) {
                    $check_row = mysqli_fetch_assoc($check_result);
                    $check_id = $check_row['matka'];
                } else {
                    break;
                }
                
                $depth_counter++;
            }
            
            if ($is_descendant) {
                echo '<div class="admin-container"><p class="message error">❌ Nie można ustawić jako matki potomka tej kategorii! Spowodowałoby to cykliczną zależność.</p></div>';
            } else {
                $query = "UPDATE categories SET nazwa = '$nazwa', matka = $matka WHERE id = $id LIMIT 1";
                
                if (mysqli_query($link, $query)) {
                    echo '<div class="admin-container">
                            <p class="message success">✅ Kategoria została zaktualizowana!</p>
                            <div class="back-link">
                                <a href="index.php?page=admin&action=categories">← Powrót do listy kategorii</a>
                            </div>
                          </div>';
                    return;
                } else {
                    echo '<div class="admin-container"><p class="message error">❌ Błąd: ' . mysqli_error($link) . '</p></div>';
                }
            }
        }
    }

    $query = "SELECT * FROM categories WHERE id = $id LIMIT 1";
    $result = mysqli_query($link, $query);
    
    if (!$result || mysqli_num_rows($result) == 0) {
        echo '<div class="admin-container"><p class="message error">Nie znaleziono kategorii o ID: ' . $id . '</p></div>';
        return;
    }
    
    $row = mysqli_fetch_assoc($result);
    $nazwa = $row['nazwa'];
    $matka = $row['matka'];
    
    echo '
    <div class="admin-container">
        <div class="admin-header">
            <h1>✏️ Edycja kategorii</h1>
            <div class="header-right">
                <a href="index.php?page=admin&action=logout" class="logout-btn">Wyloguj się</a>
                <div class="user-info">Zalogowany jako: <strong>' . htmlspecialchars($_SESSION['login']) . '</strong></div>
            </div>
        </div>
        
        <form method="post" action="index.php?page=admin&action=edit_category&id=' . $id . '" class="edit-form">
            <div class="form-group">
                <label for="category_name">Nazwa kategorii:</label>
                <input type="text" id="category_name" name="category_name" value="' . htmlspecialchars($nazwa) . '" required class="form-input">
            </div>
            
            <div class="form-group">
                <label for="category_parent">Kategoria nadrzędna (matka):</label>
                <select id="category_parent" name="category_parent" class="form-input">
                    <option value="0" ' . ($matka == 0 ? 'selected' : '') . '>-- Kategoria główna (brak matki) --</option>';

    $counter_select = 0;
    BudujOpcjeSelectRekurencyjnie(0, 0, $counter_select, $matka, $id, 10);
    
    echo '
                </select>
                <small style="display: block; margin-top: 5px; color: #666;">Wybierz kategorię nadrzędną. Kategoria nie może być matką samej siebie ani swoich potomków.</small>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="save_category" class="btn btn-save">💾 Zapisz zmiany</button>
                <a href="index.php?page=admin&action=categories" class="btn btn-cancel">❌ Anuluj</a>
            </div>
        </form>
        
        <div class="back-link">
            <a href="index.php?page=admin&action=categories">← Powrót do listy kategorii</a>
        </div>
    </div>';
}

function UsunKategorie() {
    global $link;

    if (!isset($_SESSION['zalogowany']) || $_SESSION['zalogowany'] !== true) {
        header("Location: index.php?page=admin");
        exit();
    }
    
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($id <= 0) {
        echo '<div class="admin-container">
                <p class="message error">❌ Nieprawidłowe ID kategorii.</p>
                <div class="back-link">
                    <a href="index.php?page=admin&action=categories">← Powrót do listy kategorii</a>
                </div>
              </div>';
        return;
    }

    $check_children = "SELECT COUNT(*) as count FROM categories WHERE matka = $id";
    $result_children = mysqli_query($link, $check_children);
    $row_children = mysqli_fetch_assoc($result_children);
    
    if ($row_children['count'] > 0) {
        echo '<div class="admin-container">
                <p class="message error">❌ Nie można usunąć kategorii, która ma podkategorie! Najpierw usuń lub przenieś podkategorie.</p>
                <div class="back-link">
                    <a href="index.php?page=admin&action=categories">← Powrót do listy kategorii</a>
                </div>
              </div>';
        return;
    }

    $query_check = "SELECT nazwa FROM categories WHERE id = $id LIMIT 1";
    $result_check = mysqli_query($link, $query_check);
    
    if (!$result_check || mysqli_num_rows($result_check) == 0) {
        echo '<div class="admin-container">
                <p class="message error">❌ Nie znaleziono kategorii o ID: ' . $id . '</p>
                <div class="back-link">
                    <a href="index.php?page=admin&action=categories">← Powrót do listy kategorii</a>
                </div>
              </div>';
        return;
    }
    
    $row = mysqli_fetch_assoc($result_check);
    $nazwa = htmlspecialchars($row['nazwa']);

    $query_delete = "DELETE FROM categories WHERE id = $id LIMIT 1";
    
    if (mysqli_query($link, $query_delete)) {
        echo '<div class="admin-container">
                <div class="admin-header">
                    <h1>🗑️ Usuwanie kategorii</h1>
                    <div class="header-right">
                        <a href="index.php?page=admin&action=logout" class="logout-btn">Wyloguj się</a>
                        <div class="user-info">Zalogowany jako: <strong>' . htmlspecialchars($_SESSION['login']) . '</strong></div>
                    </div>
                </div>
                
                <p class="message success">✅ Kategoria "' . $nazwa . '" (ID: ' . $id . ') została usunięta!</p>
                
                <div class="back-link">
                    <a href="index.php?page=admin&action=categories">← Powrót do listy kategorii</a>
                </div>
              </div>';
    } else {
        echo '<div class="admin-container">
                <p class="message error">❌ Błąd podczas usuwania: ' . mysqli_error($link) . '</p>
                <div class="back-link">
                    <a href="index.php?page=admin&action=categories">← Powrót do listy kategorii</a>
                </div>
              </div>';
    }
}

if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    FormularzLogowania('Zostałeś pomyślnie wylogowany.', false);
    exit();
}

if (isset($_SESSION['zalogowany']) && $_SESSION['zalogowany'] === true) {
    
    if (isset($_GET['action'])) {
        $action = $_GET['action'];
        
        switch ($action) {
            case 'pages':
                ListaPodstron();
                break;
            
            case 'edit':
                EdytujPodstrone();
                break;
            
            case 'delete':
                UsunPodstrone();
                break;
            
            case 'add':
                DodajNowaPodstrone();
                break;
            
            case 'categories':
                ListaKategorii();
                break;
            
            case 'add_category':
                DodajKategorie();
                break;
            
            case 'edit_category':
                EdytujKategorie();
                break;
            
            case 'delete_category':
                UsunKategorie();
                break;
            
            default:
                PanelAdministratora();
                break;
        }
    } else {
        PanelAdministratora();
    }
    
} else {
    if (isset($_POST['login_user']) && isset($_POST['login_pass'])) {
        if (SprawdzLogowanie($admin_login, $admin_pass)) {
            PanelAdministratora();
        } else {
            FormularzLogowania('Błędny login lub hasło! Spróbuj ponownie.');
        }
    } else {
        FormularzLogowania();
    }
}
