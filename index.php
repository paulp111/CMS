<?php
require_once 'includes/db-connect.php';
require_once 'includes/functions.php';



$sql = "SELECT a.id, a.title, a.summary, a.category_id, a.user_id,
c.name AS category,
CONCAT(u.forename, ' ', u.surname) AS author,
i.filename AS image_file,
i.alttext AS image_alt
FROM articles AS a
JOIN category AS c ON a.category_id = c.id
JOIN user AS u ON a.user_id = u.id
LEFT JOIN images AS i ON a.images_id = i.id
WHERE a.published = 1
ORDER BY a.id DESC
LIMIT 6;";
$articles = pdo_execute($pdo, $sql)->fetchAll(PDO::FETCH_ASSOC);

// SQL Abfrage um alle Kategorien zu erhalten
$sql = "SELECT id, name FROM category WHERE navigation =1;";

$navigation = pdo_execute($pdo, $sql)->fetchAll();

$title = 'IT-News';
$description = 'All about IT and New from Software Development and Hardware';
$section = 1;

?>

<?php include './includes/header.php'; ?>
<?php include './includes/footer.php'; ?>
