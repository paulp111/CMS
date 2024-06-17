<?php
require '../includes/db-connect.php';
require '../includes/functions.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    redirect('categories.php', ['error' => 'Invalid category ID']);
}

$navigation = [
    ['name' => 'articles', 'id' => ''],
    ['name' => 'categories', 'id' => '']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
        try {
            $sql = "DELETE FROM category WHERE id = :id";
            pdo_execute($pdo, $sql, ['id' => $id]);
            redirect('categories.php', ['success' => 'Category deleted successfully']);
        } catch (PDOException $e) {
            if ($e->getCode() == 1451) {
                redirect('categories.php', ['error' => 'Category cannot be removed, there are Articles in the Category']);
            } else {
                redirect('categories.php', ['error' => 'An error occurred while deleting the category']);
            }
        }
    } else {
        redirect('categories.php', ['success' => 'Category deletion canceled']);
    }
}
?>

<?php include '../includes/header-admin.php'; ?>
<main class="container mx-auto flex justify-center flex-col items-center">
    <form action="category-delete.php?id=<?= $id ?>" method="POST" class="text-center">
        <p class="text-2xl text-blue-500 mb-8">Are you sure you want to delete this category?</p>
        <button type="submit" name="confirm" value="yes" class="text-black bg-blue-600 p-3 rounded-md">Yes</button>
        <button type="submit" name="confirm" value="no" class="text-black bg-pink-600 p-3 rounded-md">No</button>
    </form>
</main>
<?php include '../includes/footer-admin.php'; ?>
