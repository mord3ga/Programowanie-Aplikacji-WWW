<?php
function ShowPage($id) {
    global $link;
    $id = (int)$id;

    $query = "SELECT * FROM page_list WHERE id = $id LIMIT 1";
    $result = mysqli_query($link, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result);
        $web = $row['page_content'];
    } else {
        $web = '[page_not_found]';
    }
    
    return $web;
}