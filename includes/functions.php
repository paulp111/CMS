<?php
function e(string $string): string
{
    return htmlentities($string, ENT_QUOTES, "UTF-8", false);
}

function pdo_execute(PDO $pdo, string $sql, array $bindings = null): false|PDOStatement
{
    if (!$bindings) {
        return $pdo->query($sql);
    }
    $statement = $pdo->prepare($sql);
    $statement->execute($bindings);

    return $statement;
}

function format_date(string $string): string
{
    $date = date_create_from_format('Y-m-d H:i:s', $string);

    return $date->format('d M. Y');
}

?>

<?php include './includes/header.php'; ?>
<main class="flex flex-wrap container mx-auto">
    <section>
        <img src="uploads/<?= e($article['image_file'] ?? 'placeholder.png') ?>"
             alt="<?= e($article['image_alt']) ?>">
    </section>
    
    <section>
        <h1 class="text-4xl text-blue-500 mb-4 mt-8"><?= e($article['title']) ?></h1>
        <div class="text-gray-500 mb-3"><?= e(format_date($article['created'])) ?></div>
        <div class="text-gray-500"><?= e($article['content']) ?></div>
        <p class="credit text-xs mt-5 mb-5">
            Posted in <a class="text-pink-400" href="category.php?id=<?= $article['category_id'] ?>">
                <?= e($article['category']) ?></a>
            by <a class="text-pink-400" href="user.php?id=<?= $article['user_id'] ?>">
                <?= e($article['author']) ?></a>
        </p>
    </section>
</main>
<?php include './includes/footer.php'; ?>
