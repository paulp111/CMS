<?php
require_once 'includes/db-connect.php';
require_once 'includes/functions.php';

$cat_id = filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ( ! $cat_id ) {
    include 'page_not_found.php';
}

$sql = "SELECT id, name, description FROM category WHERE id = :id;";
$category = pdo_execute($pdo, $sql, ['id' => $cat_id ]) ->fetch(PDO::FETCH_ASSOC);
if (! $category) {
    include 'page_not_found.php';
}

$sql = "SELECT  a.id, a.title, a.summary, a.category_id, a.user_id, c.name as category,
CONCAT(u.forename, ' ', u.surname) as author, i.filename as image_file, i.alttext as image_alt
FROM articles as a
JOIN category as c ON a.category_id = c.id
JOIN user as u ON a.user_id = u.id
LEFT JOIN images as i ON a.images_id = i.id
WHERE a.published = 1 AND a.category_id = :id
ORDER BY a.id DESC;";

$articles = pdo_execute($pdo, $sql, ['id' => $cat_id ]) -> fetchAll(PDO::FETCH_ASSOC);