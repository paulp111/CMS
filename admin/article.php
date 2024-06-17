<?php
require '../includes/validate.php';
require '../includes/db-connect.php';
require '../includes/functions.php';

// Variablen initialisieren für Bildupload
$path_to_img = '/uploads/';
$allowed_types = ['image/jpeg', 'image/png'];
$allowed_text = 1080 * 1920 * 2;

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?? '';
$tmp_path = $_FILES['image_file']['tmp_name'] ?? '';
$save_to = '';

$article = [
    'id' => $id,
    'title' => '',
    'summary' => '',
    'content' => '',
    'published' => false,
    'category_id' => 0,
    'user_id' => 0,
    'images_id' => null,
    'filename' => '',
    'alttext' => '',
];

$errors = [
    'issue' => '',
    'title' => '',
    'summary' => '',
    'content' => '',
    'category_id' => '',
    'user_id' => '',
    'filename' => '',
    'alttext' => '',
];

// Lade alle Kategorien von der Datenbank
$sql = "SELECT id, name FROM category";
$categories = pdo_execute($pdo, $sql)->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT id, CONCAT(forename, ' ', surname) AS name FROM user";
$users = pdo_execute($pdo, $sql)->fetchAll(PDO::FETCH_ASSOC);

if ($id) {
    $sql = "SELECT a.id, a.title, a.summary, a.content, a.category_id,
    a.user_id, a.images_id, a.published,
    i.filename, i.alttext FROM  articles a
    LEFT JOIN images i ON a.images_id = i.id
    WHERE a.id = :id";

    $article = pdo_execute($pdo, $sql, ['id' => $id])->fetch(PDO::FETCH_ASSOC);
    if (!$article) {
        redirect('articles.php', ['error' => 'article not found']);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Bild auslesen
    if (isset( $_FILES['image_file'])) {
        $image = $_files['image_file'];
        // Bildgröße validieren
        $errors['filename'] = $image['error'] === 1 ? 'The image is too large ' : '';
        // Wenn ein Bild hochgeladen wurde, dann wird es validiert
        if ( $tmp_path && $image['error'] === UPLOAD_ERR_OK) {
            // Alt-Text wird gesetzt
            $article['alttext'] = filter_input( INPUT_POST, 'image_alt');
            // Alt-TExt validieren
            $errors['alttext'] = is_text ($article['alttext'], 1, 254) ?'': 'Alt text must be between 1 and 254 characters';
            // bildtyp wird validiert
            $typ = mime_content_type( $tmp_path);
            $errors['filename'] .= in_array( $typ, $allowed_types) ? '' : 'The file type is not allowed';
            // Bildendung wird validiert
            $extension = pathinfo( strtolower( $image['name']), PATHINFO_EXTENSION);
            $errors['filename'] .= in_array($extension, $allowed_ext) ? '' : 'The file extension is not allowed';
            // Bildgröße wird validiert
            $errors['filename'] .= $image['size'] > $max_size ? 'The image exceeds the maximum upload size' : '';
            // Wenn es keine Fehler gibt, wird ein Speicerort für das Bild festgelegt
            if ( $errors['filename'] === '' && $errors['alttext'] === '') {
                $article['filename'] = $image['name'];
                $save_to = get_file_path( $image['name'], $path_to_img);
            }
        }
    }
}
//-------------------------------------------------------------------------

// Wenn das Formular mit Daten abgeschickt wurde, dann werden die Daten validiert und gespeichert
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Die Daten werden aus dem Formular ausgelesen und validiert
    $article['title'] = filter_input(INPUT_POST, 'title');
    $article['summary'] = filter_input(INPUT_POST, 'summary');
    $article['content'] = filter_input(INPUT_POST, 'content');
    $article['category_id'] = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $article['user_id'] = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $article['published'] = filter_input(INPUT_POST, 'published', FILTER_VALIDATE_BOOLEAN);
    $article['images_id'] = filter_input(INPUT_POST, 'images_id', FILTER_VALIDATE_INT);

    // Die Daten werden auf Länge und Vorhandensein validiert
    $errors['title'] = is_text($article['title'], 1, 255) ? '' : 'Title must be between 1 and 255 characters';
    $errors['summary'] = is_text($article['summary'], 1, 1000) ? '' : 'Summary must be between 1 and 1000 characters';
    $errors['content'] = is_text($article['content'], 1, 10000) ? '' : 'Content must be between 1 and 10000 characters';
    $errors['category_id'] = $article['category_id'] ? '' : 'Please select a valid category';
    $errors['user_id'] = $article['user_id'] ? '' : 'Please select a valid author';

    // Fehler werden in eine Zeichenkette zusammengefasst
    $problems = implode(array_filter($errors));

    // Wenn es keine Fehler gibt, wird der Artikel gespeichert und der Benutzer zur Artikelliste umgeleitet
    if (!$problems) {
        // Wenn die ID vorhanden ist, wird der Artikel aktualisiert (UPDATE), ansonsten wird ein neuer Artikel in der Datenbank erstellt
        $sql = "INSERT INTO articles (title, summary, content, category_id, user_id, published, images_id) VALUES (:title, :summary, :content, :category_id, :user_id, :published, :images_id)";
        if ($id) {
            $sql = "UPDATE articles SET title = :title, summary = :summary, content = :content, category_id = :category_id, user_id = :user_id, published = :published, images_id = :images_id WHERE id = :id";
        }

        // Die zu speichernden Daten werden in ein Array zusammengefasst, um später die Platzhalter zu ersetzen
        $bindings = [
            'title' => $article['title'],
            'summary' => $article['summary'],
            'content' => $article['content'],
            'category_id' => $article['category_id'],
            'user_id' => $article['user_id'],
            'published' => $article['published'] ? 1 : 0,
            'images_id' => $article['images_id'],
        ];
        if ($id) {
            $bindings['id'] = $id;
        }

        // Die Daten werden in die Datenbank gespeichert und der Benutzer wird zur Artikelliste umgeleitet
        try {
            pdo_execute($pdo, $sql, $bindings);
            redirect('articles.php', ['success' => 'Article successfully saved']);
        } catch (PDOException $e) {
            $errors['issue'] = 'There was an issue saving the article';
        }
    } else {
        $errors['issue'] = 'Please correct the following issues: ' . $problems;
    }
}
?>

<?php include '../includes/header-admin.php'; ?>
<main class="container w-auto mx-auto md:w-1/2 flex justify-center flex-col items-center p-5">
    <form class="w-full grid" action="article.php?id=<?= $id ?? '' ?>" method="POST">
        <h2 class="text-3xl text-blue-500 mb-8"><?= $id ? 'Edit ' : 'New ' ?>Article</h2>
        <?php if ($errors['issue']): ?>
            <p class="error text-red-500 bg-red-200 p-5 rounded-md"><?= $errors['issue'] ?></p>
        <?php endif ?>

        <div class="p-4">
            <label class="block mb-2 text-sm font-medium text-gray-900" for="title">Title</label>
            <input
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                type="text" id="title" name="title" value="<?= e($article['title']) ?>">
            <span class="text-red-500"><?= $errors['title'] ?></span>
        </div>
        <div class="p-4">
            <label class="block mb-2 text-sm font-medium text-gray-900" for="summary">Summary</label>
            <textarea
                class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                id="summary" name="summary"><?= e($article['summary']) ?></textarea>
            <span class="text-red-500"><?= $errors['summary'] ?></span>
        </div>
        <div class="p-4">
            <label class="block mb-2 text-sm font-medium text-gray-900" for="content">Content</label>
            <textarea
                class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                id="content" name="content"><?= e($article['content']) ?></textarea>
            <span class="text-red-500"><?= $errors['content'] ?></span>
        </div>
        <div class="p-4">
            <label class="block mb-2 text-sm font-medium text-gray-900" for="category_id">Category</label>
            <select
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                id="category_id" name="category_id">
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>" <?= ($category['id'] == $article['category_id']) ? 'selected' : '' ?>>
                        <?= e($category['name']) ?>
                    </option>
                <?php endforeach ?>
            </select>
            <span class="text-red-500"><?= $errors['category_id'] ?></span>
        </div>
        <div class="p-4">
            <label class="block mb-2 text-sm font-medium text-gray-900" for="user_id">Author</label>
            <select
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                id="user_id" name="user_id">
                <?php foreach ($users as $user): ?>
                    <option value="<?= $user['id'] ?>" <?= ($user['id'] == $article['user_id']) ? 'selected' : '' ?>>
                        <?= e($user['name']) ?>
                    </option>
                <?php endforeach ?>
            </select>
            <span class="text-red-500"><?= $errors['user_id'] ?></span>
        </div>
        <div class="p-4">
            <input
                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600"
                type="checkbox" id="published" name="published" <?= $article['published'] ? 'checked' : '' ?>>
            <label class="ms-2 text-sm font-medium text-gray-900" for="published">Published</label>
        </div>
        <button type="submit" class="text-white bg-blue-600 p-3 rounded-md hover:bg-blue-700">Save</button>
    </form>
</main>
<?php include '../includes/footer-admin.php'; ?>