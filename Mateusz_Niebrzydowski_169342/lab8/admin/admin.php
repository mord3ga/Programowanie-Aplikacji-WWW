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
        while ($row = mysqli_fetch_array($result)) {
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
