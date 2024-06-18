<?php
require 'includes/db-connect.php';
require 'includes/functions.php';

$article_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$article_id) {
    include 'page_not_found.php';
    exit;
}



$sql = "SELECT a.title, a.summary, a.content, a.created, a.category_id, a.user_id, c.name AS category,
CONCAT(u.forename, ' ', u.surname) as author, i.filename as image_file, i.alttext as image_alt
FROM articles as a
JOIN category as c ON a.category_id = c.id
JOIN user as u ON a.user_id = u.id
LEFT JOIN images as i ON a.images_id = i.id
WHERE a.id = :id AND a.published = 1;";


$article = pdo_execute($pdo, $sql, ["id" => $article_id])->fetch();
if (!$article) {
    include 'page_not_found.php';
    exit;
}


$sql = "SELECT id, name FROM category WHERE navigation =1;";
$navigation = pdo_execute($pdo, $sql)->fetchAll(PDO::FETCH_ASSOC);
$title = $article['title'];
$description = $article['summary'];
$section = $article['category_id'];
?>

<?php include './includes/header.php'; ?>
<main class="container mx-auto p-4">
    <article>
        <header>
            <h1 class="text-4xl"><?= e($article['title']) ?></h1>
            <p class="text-sm text-gray-500">
                Posted in <a href="category.php?id=<?= e($article['category_id']) ?>"><?= e($article['category']) ?></a>
                by <a href="user.php?id=<?= e($article['user_id']) ?>"><?= e($article['author']) ?></a>
                on <?= e($article['created']) ?>
            </p>
        </header>
        <img src="uploads/<?= e($article['image_file'] ?? 'blank.png') ?>" alt="<?= e($article['image_alt']) ?>">
        <p><?= e($article['content']) ?></p>
    </article>
</main>
<?php include './includes/footer.php'; ?>
