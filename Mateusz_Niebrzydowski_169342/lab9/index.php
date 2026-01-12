<?php

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

include('cfg.php');
include('showpage.php');

$page = isset($_GET['page']) ? mysqli_real_escape_string($link, $_GET['page']) : 'home';

$menu_query = "SELECT id, page_title, alias FROM page_list WHERE status = 1 ORDER BY id ASC";
$menu_result = mysqli_query($link, $menu_query);

if ($page == 'admin') {
    $page_title = 'Panel Administracyjny';
} else {
    $title_query = "SELECT page_title, alias FROM page_list WHERE (page_title = '$page' OR id = '$page') AND status = 1 LIMIT 1";
    $title_result = mysqli_query($link, $title_query);
    
    if ($title_result && mysqli_num_rows($title_result) > 0) {
        $title_row = mysqli_fetch_assoc($title_result);
        if ($title_row['page_title'] == 'home') {
            $page_title = 'BookStore';
        }
        elseif (is_null($title_row['alias'])) {
            $page_title = $title_row['page_title'];
        } else {
            $page_title = $title_row['alias'];
        }
    } else {
        $page_title = 'BookStore';
    }
}

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8">
    <meta http-equiv="Content-Language" content="pl">
    <meta name="Author" content="Mateusz Niebrzydowski">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link rel="icon" type="image/x-icon" href="images/favicon.png">
</head>
<body<?php if($page == 'admin') echo ' class="admin-body"'; ?>>
<div class="container<?php if($page == 'admin') echo ' admin-container-full'; ?>">
    <?php if ($page != 'admin'): ?>
    <header>
        <h1><?php echo $page_title; ?></h1>
    </header>
    <nav>
        <ul>
            <?php
            if ($menu_result && mysqli_num_rows($menu_result) > 0) {
                mysqli_data_seek($menu_result, 0);
                
                while ($menu_item = mysqli_fetch_assoc($menu_result)) {
                    $link_page = !empty($menu_item['page_title']) ? $menu_item['page_title'] : $menu_item['id'];

                    $display_name = !empty($menu_item['alias']) ? $menu_item['alias'] : $menu_item['page_title'];

                    $active_class = ($page == $link_page || $page == $menu_item['id']) ? ' class="active"' : '';
                    
                    echo '<li><a href="index.php?page=' . htmlspecialchars($link_page) . '"' . $active_class . '>' . htmlspecialchars($display_name) . '</a></li>';
                }
            }
            ?>
            <li><a href="index.php?page=admin">Panel administratora</a></li>
        </ul>
    </nav>
    <?php endif; ?>
    <?php
    if ($page == 'admin') {
        include('admin/admin.php');
    } else {
        $find_query = "SELECT id FROM page_list WHERE (page_title = '$page' OR alias = '$page' OR id = '$page') AND status = 1 LIMIT 1";
        $find_result = mysqli_query($link, $find_query);
        
        if ($find_result && mysqli_num_rows($find_result) > 0) {
            $find_row = mysqli_fetch_assoc($find_result);
            $page_id = $find_row['id'];

            $page_content = ShowPage($page_id);
            
            if ($page_content == '[page_not_found]') {
                echo '<section class="center">
                        <h2>Błąd 404</h2>
                        <p>Strona nie została znaleziona.</p>
                        <p><a href="index.php">← Powrót do strony głównej</a></p>
                      </section>';
            } else {
                echo '<section class="center">';
                echo $page_content;
                echo '</section>';
            }
        } else {
            echo '<section class="center">
                    <h2>Błąd 404</h2>
                    <p>Strona nie została znaleziona.</p>
                    <p><a href="index.php">← Powrót do strony głównej</a></p>
                  </section>';
        }
    }
    ?>
    <?php if ($page != 'admin'): ?>
    <footer>
        <?php
        $nr_indeksu = "169342";
        $nrGrupy = "3";
        echo "<p>&copy; 2025 Mateusz Niebrzydowski ".$nr_indeksu." grupa ".$nrGrupy."<br>";
        ?>
    </footer>
    <?php endif; ?>
</div>
</body>
</html>