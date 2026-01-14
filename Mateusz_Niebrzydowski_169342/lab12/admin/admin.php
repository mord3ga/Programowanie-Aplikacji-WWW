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
                    <h3>📝 Zarządzanie treścią</h3>
                    <p>Edytuj zawartość podstron serwisu</p>
                <a href="index.php?page=admin&action=pages">Otwórz</a>
                </div>
                
                <div class="admin-menu-item">
                <h3>📁 Zarządzanie kategoriami</h3>
                <p>Zarządzaj kategoriami i podkategoriami produktów</p>
                <a href="index.php?page=admin&action=categories">Otwórz</a>
                </div>
                
                <div class="admin-menu-item">
                <h3>📦 Zarządzanie produktami</h3>
                <p>Dodawaj, edytuj i usuwaj produkty sklepu</p>
                <a href="index.php?page=admin&action=products">Otwórz</a>
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
                <label for="page_title">Tytuł podstrony:</label>
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
function BuiltSelect($parent_id, $level = 0, &$counter = 0, $selected_id = 0, $exclude_id = 0, $max_depth = 10) {
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

            BuiltSelect($id, $level + 1, $counter, $selected_id, $exclude_id, $max_depth);
        }
    }
}
function PokazKategorie($parent_id, $level = 0, &$counter = 0, $max_depth = 10) {
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

            PokazKategorie($id, $level + 1, $counter, $max_depth);
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
        PokazKategorie(0, 0, $counter, 10);
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
    BuiltSelect(0, 0, $counter_select, 0, 0, 10);
    
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
                echo '<div class="admin-container"><p class="message error">❌ Nie można ustawić jako matki potomka tej kategorii!</p></div>';
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
    BuiltSelect(0, 0, $counter_select, $matka, $id, 10);
    
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

    $check_products = "SELECT COUNT(*) as count FROM products WHERE kategoria = $id";
    $result_products = mysqli_query($link, $check_products);
    $row_products = mysqli_fetch_assoc($result_products);
    
    if ($row_products['count'] > 0) {
        echo '<div class="admin-container">
                <div class="admin-header">
                    <h1>🗑️ Usuwanie kategorii</h1>
                    <div class="header-right">
                        <a href="index.php?page=admin&action=logout" class="logout-btn">Wyloguj się</a>
                        <div class="user-info">Zalogowany jako: <strong>' . htmlspecialchars($_SESSION['login']) . '</strong></div>
                    </div>
                </div>
                
                <p class="message error">❌ Nie można usunąć kategorii, która ma przypisane produkty!</p>
                <p class="message error" style="background: #fff3cd; color: #856404; border: 1px solid #ffc107;">
                    <strong>Liczba produktów w tej kategorii: ' . $row_products['count'] . '</strong><br>
                    Najpierw usuń wszystkie produkty z tej kategorii lub przypisz je do innej kategorii.
                </p>
                
                <div class="back-link">
                    <a href="index.php?page=admin&action=categories">← Powrót do listy kategorii</a> | 
                    <a href="index.php?page=admin&action=products">→ Przejdź do zarządzania produktami</a>
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

function PobierzNazweKategorii($category_id) {
    global $link;
    
    if (empty($category_id) || $category_id == 0) {
        return 'Brak kategorii';
    }
    
    $query = "SELECT nazwa FROM categories WHERE id = $category_id LIMIT 1";
    $result = mysqli_query($link, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return htmlspecialchars($row['nazwa']);
    }
    
    return 'Nieznana kategoria';
}
function SprawdzDostepnoscProduktu($product_data) {
    $is_available = true;
    $reasons = [];

    if ($product_data['status_dostepnosci'] != 1) {
        $is_available = false;
        $reasons[] = 'Status: niedostępny';
    }

    if ($product_data['stan'] <= 0) {
        $is_available = false;
        $reasons[] = 'Brak w magazynie';
    }
    
    return [
        'available' => $is_available,
        'reasons' => $reasons
    ];
}
function ListaProduktow() {
    global $link;
    
    if (!isset($_SESSION['zalogowany']) || $_SESSION['zalogowany'] !== true) {
        header("Location: index.php?page=admin");
        exit();
    }
    
    echo '
    <div class="admin-container">
        <div class="admin-header">
            <h1>📦 Lista produktów</h1>
            <div class="header-right">
                <a href="index.php?page=admin&action=logout" class="logout-btn">Wyloguj się</a>
                <div class="user-info">Zalogowany jako: <strong>' . htmlspecialchars($_SESSION['login']) . '</strong></div>
            </div>
        </div>
        
        <a href="index.php?page=admin&action=add_product" class="btn-add">+ Dodaj nowy produkt</a>
        
        <div class="products-table-container">
            <table class="pages-table products-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Zdjęcie</th>
                        <th>Tytuł</th>
                        <th>Autor</th>
                        <th>Kategoria</th>
                        <th>Cena netto</th>
                        <th>VAT</th>
                        <th>Cena brutto</th>
                        <th>Stan</th>
                        <th>Gabaryt</th>
                        <th>Dostępność</th>
                        <th class="col-actions">Akcje</th>
                    </tr>
                </thead>
                <tbody>';
    
    $query = "SELECT * FROM products ORDER BY id DESC";
    $result = mysqli_query($link, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $counter = 0;
        $max_iterations = 100;
        
        while ($product = mysqli_fetch_assoc($result)) {
            $counter++;
            if ($counter > $max_iterations) {
                echo '<tr><td colspan="13" class="message error">⚠️ Osiągnięto limit produktów (100).</td></tr>';
                break;
            }

            $availability_check = SprawdzDostepnoscProduktu($product);
            $is_available = $availability_check['available'];
            $availability_icon = $is_available ? '✅' : '❌';
            $availability_text = $is_available ? 'Dostępny' : 'Niedostępny';
            $availability_class = $is_available ? 'available' : 'unavailable';
            $availability_tooltip = !empty($availability_check['reasons']) ? implode(', ', $availability_check['reasons']) : '';

            $vat_procent = (float)$product['vat'];
            $price_brutto = $product['cena_netto'] * (1 + $vat_procent / 100);

            $status_label = ($product['status_dostepnosci'] == 1) ? '🟢 Dostępny' : '🔴 Niedostępny';

            $gabaryt_labels = [
                'maly' => '📘 Mały',
                'sredni' => '📗 Średni',
                'duzy' => '📕 Duży'
            ];
            $gabaryt_display = isset($gabaryt_labels[$product['gabaryt']]) ? $gabaryt_labels[$product['gabaryt']] : $product['gabaryt'];

            $image_html = '';
            if (!empty($product['zdjecie'])) {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime_type = $finfo->buffer($product['zdjecie']);

                if (empty($mime_type) || substr($mime_type, 0, 6) !== 'image/') {
                    $mime_type = 'image/jpeg';
                }
                
                $image_data = base64_encode($product['zdjecie']);
                $image_html = '<img src="data:' . htmlspecialchars($mime_type) . ';base64,' . $image_data . '" alt="' . htmlspecialchars($product['tytul']) . '" class="product-thumbnail">';
            } else {
                $image_html = '<div class="product-thumbnail-placeholder">📦</div>';
            }
            
            echo '
                <tr>
                    <td>' . $product['id'] . '</td>
                    <td class="product-image-cell">' . $image_html . '</td>
                    <td><strong>' . htmlspecialchars($product['tytul']) . '</strong></td>
                    <td>' . htmlspecialchars($product['autor']) . '</td>
                    <td>' . PobierzNazweKategorii($product['kategoria']) . '</td>
                    <td>' . number_format($product['cena_netto'], 2, ',', ' ') . ' zł</td>
                    <td>' . $product['vat'] . '%</td>
                    <td><strong>' . number_format($price_brutto, 2, ',', ' ') . ' zł</strong></td>
                    <td>' . $product['stan'] . ' szt.</td>
                    <td>' . $gabaryt_display . '</td>
                    <td>' . $status_label . '</td>
                    <td class="actions">
                        <a href="index.php?page=admin&action=edit_product&id=' . $product['id'] . '" class="btn btn-edit btn-sm">✏️</a>
                        <a href="index.php?page=admin&action=delete_product&id=' . $product['id'] . '" class="btn btn-delete btn-sm" onclick="return confirm(\'Czy na pewno chcesz usunąć produkt: ' . addslashes($product['tytul']) . '?\')">🗑️</a>
                    </td>
                </tr>';
        }
    } else {
        echo '<tr><td colspan="13" class="no-pages">Brak produktów w bazie danych.</td></tr>';
    }
    
    echo '
                </tbody>
            </table>
        </div>
        
        <div class="back-link">
            <a href="index.php?page=admin">← Powrót do panelu głównego</a>
        </div>
    </div>';
}

function DodajProdukt() {
    global $link;
    
    if (!isset($_SESSION['zalogowany']) || $_SESSION['zalogowany'] !== true) {
        header("Location: index.php?page=admin");
        exit();
    }

    if (isset($_POST['save_product'])) {
        $title = mysqli_real_escape_string($link, $_POST['product_title']);
        $author = mysqli_real_escape_string($link, $_POST['product_author']);
        $description = mysqli_real_escape_string($link, $_POST['product_description']);
        $price_net = (float)$_POST['product_price_net'];
        $vat_tax = mysqli_real_escape_string($link, $_POST['product_vat_tax']);
        $stock_quantity = (int)$_POST['product_stock_quantity'];
        $availability_status = (int)$_POST['product_availability_status']; // 0 lub 1
        $category_id = !empty($_POST['product_category']) ? (int)$_POST['product_category'] : 'NULL';
        $dimensions = mysqli_real_escape_string($link, $_POST['product_dimensions']);

        $image_blob = 'NULL';
        if (isset($_FILES['product_image_file']) && $_FILES['product_image_file']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            $file_type = $_FILES['product_image_file']['type'];
            
            if (in_array($file_type, $allowed_types)) {
                $max_size = 5 * 1024 * 1024;
                if ($_FILES['product_image_file']['size'] > $max_size) {
                    echo '<div class="admin-container"><p class="message error">❌ Plik jest za duży! Maksymalny rozmiar: 5MB</p></div>';
                    $image_blob = 'NULL';
                } else {
                    $image_content = file_get_contents($_FILES['product_image_file']['tmp_name']);
                    $image_blob = "'" . mysqli_real_escape_string($link, $image_content) . "'";
                }
            } else {
                echo '<div class="admin-container"><p class="message error">❌ Nieprawidłowy format pliku! Dozwolone: JPG, PNG, GIF, WEBP</p></div>';
            }
        } else {
            if (!isset($_FILES['product_image_file'])) {
                echo '<div class="admin-container"><p class="message error">❌ Nie wybrano pliku!</p></div>';
            } elseif ($_FILES['product_image_file']['error'] != 0) {
                $error_messages = [
                    UPLOAD_ERR_INI_SIZE => 'Plik przekracza dozwolony rozmiar (php.ini)',
                    UPLOAD_ERR_FORM_SIZE => 'Plik przekracza dozwolony rozmiar (formularz)',
                    UPLOAD_ERR_PARTIAL => 'Plik został przesłany tylko częściowo',
                    UPLOAD_ERR_NO_FILE => 'Nie wybrano pliku',
                    UPLOAD_ERR_NO_TMP_DIR => 'Brak folderu tymczasowego',
                    UPLOAD_ERR_CANT_WRITE => 'Nie można zapisać pliku na dysku',
                    UPLOAD_ERR_EXTENSION => 'Rozszerzenie PHP zablokowało upload'
                ];
                $error_code = $_FILES['product_image_file']['error'];
                $error_msg = isset($error_messages[$error_code]) ? $error_messages[$error_code] : 'Nieznany błąd';
                echo '<div class="admin-container"><p class="message error">❌ Błąd uploadu: ' . $error_msg . ' (kod: ' . $error_code . ')</p></div>';
            }
        }
        
        $query = "INSERT INTO products (tytul, autor, opis, cena_netto, vat, stan, status_dostepnosci, kategoria, gabaryt, zdjecie, data_utworzenia, data_modyfikacji) 
                  VALUES ('$title', '$author', '$description', $price_net, '$vat_tax', $stock_quantity, $availability_status, $category_id, '$dimensions', $image_blob, NOW(), NOW())";
        
        if (mysqli_query($link, $query)) {
            $new_id = mysqli_insert_id($link);
            echo '<div class="admin-container">
                    <p class="message success">✅ Produkt "' . htmlspecialchars($title) . '" został dodany! (ID: ' . $new_id . ')</p>
                    <div class="back-link">
                        <a href="index.php?page=admin&action=products">← Powrót do listy produktów</a> | 
                        <a href="index.php?page=admin&action=add_product">+ Dodaj kolejny</a>
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
            <h1>➕ Dodaj nowy produkt</h1>
            <div class="header-right">
                <a href="index.php?page=admin&action=logout" class="logout-btn">Wyloguj się</a>
                <div class="user-info">Zalogowany jako: <strong>' . htmlspecialchars($_SESSION['login']) . '</strong></div>
            </div>
        </div>
        
        <form method="post" action="index.php?page=admin&action=add_product" enctype="multipart/form-data" class="edit-form product-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="product_title">Tytuł książki: </label>
                    <input type="text" id="product_title" name="product_title" required class="form-input">
                </div>
                
                <div class="form-group">
                    <label for="product_author">Autor: </label>
                    <input type="text" id="product_author" name="product_author" required class="form-input">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group full-width">
                    <label for="product_description">Opis produktu: </label>
                    <textarea id="product_description" name="product_description" rows="6" required class="form-input"></textarea>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="product_price_net">Cena netto (zł): </label>
                    <input type="number" id="product_price_net" name="product_price_net" step="0.01" min="0" value="0.00" required class="form-input">
                </div>
                
                <div class="form-group">
                    <label for="product_vat_tax">Podatek VAT: </label>
                    <select id="product_vat_tax" name="product_vat_tax" required class="form-input">
                        <option value="0">0%</option>
                        <option value="5" selected>5%</option>
                        <option value="8">8%</option>
                        <option value="23">23%</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="product_stock_quantity">Ilość w magazynie (szt.): </label>
                    <input type="number" id="product_stock_quantity" name="product_stock_quantity" min="0" value="0" required class="form-input">
                </div>
                
                <div class="form-group">
                    <label for="product_availability_status">Status dostępności: </label>
                    <select id="product_availability_status" name="product_availability_status" required class="form-input">
                        <option value="1">🟢 Dostępny</option>
                        <option value="0">🔴 Niedostępny</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="product_category">Kategoria:</label>
                    <select id="product_category" name="product_category" class="form-input">
                        <option value="">-- Brak kategorii --</option>';
    
    $counter_select = 0;
    BuiltSelect(0, 0, $counter_select, 0, 0, 10);
    
    echo '
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="product_dimensions">Gabaryt produktu: </label>
                    <select id="product_dimensions" name="product_dimensions" required class="form-input">
                        <option value="maly">📘 Mały</option>
                        <option value="sredni" selected>📗 Średni</option>
                        <option value="duzy">📕 Duży</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="product_image_file">📷 Zdjęcie produktu: </label>
                    <input type="file" id="product_image_file" name="product_image_file" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" required class="form-input">
                    <small>Max 5MB</small>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="save_product" class="btn btn-save">💾 Zapisz produkt</button>
                <a href="index.php?page=admin&action=products" class="btn btn-cancel">❌ Anuluj</a>
            </div>
        </form>
        
        <div class="back-link">
            <a href="index.php?page=admin">← Powrót do panelu głównego</a>
        </div>
    </div>';
}

function EdytujProdukt() {
    global $link;
    
    if (!isset($_SESSION['zalogowany']) || $_SESSION['zalogowany'] !== true) {
        header("Location: index.php?page=admin");
        exit();
    }
    
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        echo '<div class="admin-container"><p class="message error">Nie podano ID produktu!</p></div>';
        return;
    }
    
    $id = (int)$_GET['id'];

    if (isset($_POST['save_product'])) {
        $title = mysqli_real_escape_string($link, $_POST['product_title']);
        $author = mysqli_real_escape_string($link, $_POST['product_author']);
        $description = mysqli_real_escape_string($link, $_POST['product_description']);
        $price_net = (float)$_POST['product_price_net'];
        $vat_tax = mysqli_real_escape_string($link, $_POST['product_vat_tax']);
        $stock_quantity = (int)$_POST['product_stock_quantity'];
        $availability_status = (int)$_POST['product_availability_status']; // 0 lub 1
        $category_id = !empty($_POST['product_category']) ? (int)$_POST['product_category'] : 'NULL';
        $dimensions = mysqli_real_escape_string($link, $_POST['product_dimensions']);

        $zdjecie_update = '';
        if (isset($_FILES['product_image_file']) && $_FILES['product_image_file']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            $file_type = $_FILES['product_image_file']['type'];
            
            if (in_array($file_type, $allowed_types)) {
                $max_size = 5 * 1024 * 1024;
                if ($_FILES['product_image_file']['size'] > $max_size) {
                    echo '<div class="admin-container"><p class="message error">❌ Plik jest za duży! Maksymalny rozmiar: 5MB</p></div>';
                } else {
                    $image_content = file_get_contents($_FILES['product_image_file']['tmp_name']);
                    $image_blob_escaped = mysqli_real_escape_string($link, $image_content);
                    $zdjecie_update = ", zdjecie = '$image_blob_escaped'";
                }
            } else {
                echo '<div class="admin-container"><p class="message error">❌ Nieprawidłowy format pliku! Dozwolone: JPG, PNG, GIF, WEBP</p></div>';
            }
        }
        
        $query = "UPDATE products SET 
                  tytul = '$title',
                  autor = '$author',
                  opis = '$description',
                  cena_netto = $price_net,
                  vat = '$vat_tax',
                  stan = $stock_quantity,
                  status_dostepnosci = $availability_status,
                  kategoria = $category_id,
                  gabaryt = '$dimensions',
                  data_modyfikacji = NOW()
                  $zdjecie_update
                  WHERE id = $id LIMIT 1";
        
        if (mysqli_query($link, $query)) {
            echo '<div class="admin-container">
                    <p class="message success">✅ Produkt został zaktualizowany!</p>
                    <div class="back-link">
                        <a href="index.php?page=admin&action=products">← Powrót do listy produktów</a>
                    </div>
                  </div>';
            return;
        } else {
            echo '<div class="admin-container"><p class="message error">❌ Błąd: ' . mysqli_error($link) . '</p></div>';
        }
    }

    $query = "SELECT * FROM products WHERE id = $id LIMIT 1";
    $result = mysqli_query($link, $query);
    
    if (!$result || mysqli_num_rows($result) == 0) {
        echo '<div class="admin-container"><p class="message error">Nie znaleziono produktu o ID: ' . $id . '</p></div>';
        return;
    }
    
    $product = mysqli_fetch_assoc($result);
    
    echo '
    <div class="admin-container">
        <div class="admin-header">
            <h1>✏️ Edycja produktu</h1>
            <div class="header-right">
                <a href="index.php?page=admin&action=logout" class="logout-btn">Wyloguj się</a>
                <div class="user-info">Zalogowany jako: <strong>' . htmlspecialchars($_SESSION['login']) . '</strong></div>
            </div>
        </div>
        
        <form method="post" action="index.php?page=admin&action=edit_product&id=' . $id . '" enctype="multipart/form-data" class="edit-form product-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="product_title">Tytuł książki: </label>
                    <input type="text" id="product_title" name="product_title" value="' . htmlspecialchars($product['tytul']) . '" required class="form-input">
                </div>
                
                <div class="form-group">
                    <label for="product_author">Autor: </label>
                    <input type="text" id="product_author" name="product_author" value="' . htmlspecialchars($product['autor']) . '" required class="form-input">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group full-width">
                    <label for="product_description">Opis produktu: </label>
                    <textarea id="product_description" name="product_description" rows="6" required class="form-input">' . htmlspecialchars($product['opis']) . '</textarea>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="product_price_net">Cena netto (zł): </label>
                    <input type="number" id="product_price_net" name="product_price_net" step="0.01" min="0" value="' . $product['cena_netto'] . '" required class="form-input">
                </div>
                
                <div class="form-group">
                    <label for="product_vat_tax">Podatek VAT: </label>
                    <select id="product_vat_tax" name="product_vat_tax" required class="form-input">
                        <option value="0"' . ($product['vat'] == '0' ? ' selected' : '') . '>0%</option>
                        <option value="5"' . ($product['vat'] == '5' ? ' selected' : '') . '>5%</option>
                        <option value="8"' . ($product['vat'] == '8' ? ' selected' : '') . '>8%</option>
                        <option value="23"' . ($product['vat'] == '23' ? ' selected' : '') . '>23%</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="product_stock_quantity">Ilość w magazynie (szt.): </label>
                    <input type="number" id="product_stock_quantity" name="product_stock_quantity" min="0" value="' . $product['stan'] . '" required class="form-input">
                </div>
                
                <div class="form-group">
                    <label for="product_availability_status">Status dostępności: </label>
                    <select id="product_availability_status" name="product_availability_status" required class="form-input">
                        <option value="1"' . ($product['status_dostepnosci'] == 1 ? ' selected' : '') . '>🟢 Dostępny</option>
                        <option value="0"' . ($product['status_dostepnosci'] == 0 ? ' selected' : '') . '>🔴 Niedostępny</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="product_category">Kategoria:</label>
                    <select id="product_category" name="product_category" class="form-input">
                        <option value="">-- Brak kategorii --</option>';
    
    $counter_select = 0;
    BuiltSelect(0, 0, $counter_select, $product['kategoria'], 0, 10);
    
    echo '
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="product_dimensions">Gabaryt produktu: </label>
                    <select id="product_dimensions" name="product_dimensions" required class="form-input">
                        <option value="maly"' . ($product['gabaryt'] == 'maly' ? ' selected' : '') . '>📘 Mały</option>
                        <option value="sredni"' . ($product['gabaryt'] == 'sredni' ? ' selected' : '') . '>📗 Średni</option>
                        <option value="duzy"' . ($product['gabaryt'] == 'duzy' ? ' selected' : '') . '>📕 Duży</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="product_image_file">📷 Zmień zdjęcie:</label>
                    <input type="file" id="product_image_file" name="product_image_file" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" class="form-input">
                    <small>Max 5MB</small>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group full-width" style="text-align: center;">
                    <label>Obecne zdjęcie:</label><br>';

    if (!empty($product['zdjecie'])) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->buffer($product['zdjecie']);

        if (empty($mime_type) || substr($mime_type, 0, 6) !== 'image/') {
            $mime_type = 'image/jpeg';
        }
        
        $image_data = base64_encode($product['zdjecie']);
        echo '<img src="data:' . htmlspecialchars($mime_type) . ';base64,' . $image_data . '" alt="Obecne zdjęcie" class="product-image-preview">';
    } else {
        echo '<div class="product-image-placeholder">📦 Brak zdjęcia</div>';
    }
    
    echo '
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="save_product" class="btn btn-save">💾 Zapisz zmiany</button>
                <a href="index.php?page=admin&action=products" class="btn btn-cancel">❌ Anuluj</a>
            </div>
        </form>
        
        <div class="back-link">
            <a href="index.php?page=admin">← Powrót do panelu głównego</a>
        </div>
    </div>';
}

function UsunProdukt() {
    global $link;
    
    if (!isset($_SESSION['zalogowany']) || $_SESSION['zalogowany'] !== true) {
        header("Location: index.php?page=admin");
        exit();
    }
    
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        echo '<div class="admin-container"><p class="message error">Nie podano ID produktu!</p></div>';
        return;
    }
    
    $id = (int)$_GET['id'];

    $query_check = "SELECT tytul FROM products WHERE id = $id LIMIT 1";
    $result_check = mysqli_query($link, $query_check);
    
    if (!$result_check || mysqli_num_rows($result_check) == 0) {
        echo '<div class="admin-container"><p class="message error">Produkt o ID ' . $id . ' nie istnieje!</p></div>';
        return;
    }
    
    $row = mysqli_fetch_assoc($result_check);
    $title = htmlspecialchars($row['tytul']);

    $query_delete = "DELETE FROM products WHERE id = $id LIMIT 1";
    
    if (mysqli_query($link, $query_delete)) {
        echo '<div class="admin-container">
                <div class="admin-header">
                    <h1>🗑️ Usuwanie produktu</h1>
                    <div class="header-right">
                        <a href="index.php?page=admin&action=logout" class="logout-btn">Wyloguj się</a>
                        <div class="user-info">Zalogowany jako: <strong>' . htmlspecialchars($_SESSION['login']) . '</strong></div>
                    </div>
                </div>
                
                <p class="message success">✅ Produkt "' . $title . '" (ID: ' . $id . ') został usunięty!</p>
                
                <div class="back-link">
                    <a href="index.php?page=admin&action=products">← Powrót do listy produktów</a>
                </div>
              </div>';
    } else {
        echo '<div class="admin-container">
                <p class="message error">❌ Błąd podczas usuwania: ' . mysqli_error($link) . '</p>
                <div class="back-link">
                    <a href="index.php?page=admin&action=products">← Powrót do listy produktów</a>
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
            
            case 'products':
                ListaProduktow();
                break;
            
            case 'add_product':
                DodajProdukt();
                break;
            
            case 'edit_product':
                EdytujProdukt();
                break;
            
            case 'delete_product':
                UsunProdukt();
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
