<?php
require '../includes/db-connect.php';
require '../includes/functions.php';
?>

<?php include '../includes/header-admin.php' ?>
<main class="container mx-auto flex justify-center flex-col items-center">
    <form action="category-delete.php?id=<?= $id ?>" method="POST" class="text-center">
        <p class="text-2xl text-blue-500 mb-8">Are you sure you want to delete this category?</p>
        <button type="submit" class="text-black bg-blue-600 p-3 rounded-md">Yes</button>
        <button type="submit" class="text-black bg-pink-600 p-3 rounded-md">No</button>
    </form>
</main>
<?php include '../includes/footer-admin.php'; ?>
