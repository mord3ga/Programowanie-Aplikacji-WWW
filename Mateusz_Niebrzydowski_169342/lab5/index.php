<?php

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

$allowed_pages = ['home', 'history', 'genres', 'care', 'contact', 'time', 'animations'];

if (!in_array($page, $allowed_pages)) {
    $page = 'home';
}

$page_titles = [
    'home' => 'BookStore',
    'history' => 'Historia książki',
    'genres' => 'Gatunki literackie',
    'care' => 'Jak dbać o książki?',
    'contact' => 'Kontakt',
    'time' => 'Aktualny czas',
    'animations' => 'Animacje'
];

$page_title = $page_titles[$page];
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
<body>
<div class="container">
    <header>
        <h1><?php echo $page_title; ?></h1>
    </header>
    <nav>
        <ul>
            <li><a href="index.php">Strona główna</a></li>
            <li><a href="index.php?page=history">Historia książki</a></li>
            <li><a href="index.php?page=genres">Gatunki literackie</a></li>
            <li><a href="index.php?page=care">Dbanie o książki</a></li>
            <li><a href="index.php?page=contact">Kontakt</a></li>
            <li><a href="index.php?page=time">Aktualny czas</a></li>
            <li><a href="index.php?page=animations">Animacje</a></li>
        </ul>
    </nav>
    <?php
    $page_file = 'pages/' . $page . '.php';
    if (file_exists($page_file)) {
        include($page_file);
    } else {
        echo '<section class="center"><h2>Błąd 404</h2><p>Strona nie została znaleziona.</p></section>';
    }
    ?>
    <footer>
        <?php
        $nr_indeksu = "169342";
        $nrGrupy = "3";
        echo "<p>&copy; 2025 Mateusz Niebrzydowski ".$nr_indeksu." grupa ".$nrGrupy."<br>";
        ?>
    </footer>
</div>
</body>
</html>