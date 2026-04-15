<?php
require 'config.php';
session_start();

// Session Check (Restrict Access)
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$message = '';

// Create uploads folder if it doesn't exist (just in case though 'cause I already created the folder)
$upload_dir = 'uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Handle Image Delete
if (isset($_GET['delete'])) {
    $id_to_delete = (int)$_GET['delete'];
    
    $stmt = $pdo->prepare("SELECT file_path FROM images WHERE id = ?");
    $stmt->execute([$id_to_delete]);
    $image = $stmt->fetch();

    if ($image) {
        if (file_exists($image['file_path'])) {
            unlink($image['file_path']); // Delete physical file
        }
        $delStmt = $pdo->prepare("DELETE FROM images WHERE id = ?");
        $delStmt->execute([$id_to_delete]); // Delete DB record
        $message = "<p style='color:green;'>Image deleted successfully.</p>";
    }
}

// Handle Image Upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['image'])) {
    $title = trim($_POST['title']);
    $file = $_FILES['image'];

    if (empty($title)) {
        $message = "<p style='color:red;'>Title is required.</p>";
    } elseif ($file['error'] !== UPLOAD_ERR_OK) {
        $message = "<p style='color:red;'>Error uploading file.</p>";
    } else {
        // File validation
        $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_mime_type = mime_content_type($file['tmp_name']);

        if (!in_array($file_mime_type, $allowed_mime_types)) {
            $message = "<p style='color:red;'>Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.</p>";
        } else {
            // Generate unique filename to prevent overwriting
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid('img_', true) . '.' . $ext;
            $destination = $upload_dir . $new_filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $stmt = $pdo->prepare("INSERT INTO images (title, file_path) VALUES (?, ?)");
                $stmt->execute([$title, $destination]);
                $message = "<p style='color:green;'>Image uploaded successfully!</p>";
            } else {
                $message = "<p style='color:red;'>Failed to move uploaded file.</p>";
            }
        }
    }
}

// Fetch all images
$images = $pdo->query("SELECT * FROM images ORDER BY uploaded_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Image Gallery Dashboard</title>
    <style>
        .gallery { display: flex; flex-wrap: wrap; gap: 20px; margin-top: 20px; }
        .card { border: 1px solid #ccc; padding: 10px; text-align: center; width: 220px; }
        .card img { max-width: 100%; height: auto; display: block; margin-bottom: 10px; }
    </style>
</head>
<body>
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>! | <a href="logout.php">Logout</a></h2>

    <?php echo $message; ?>

    <h3>Upload New Image</h3>
    <form method="POST" action="" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="Image Title" required>
        <input type="file" name="image" accept="image/*" required>
        <button type="submit">Upload</button>
    </form>

    <hr>

    <h3>Gallery</h3>
    <div class="gallery">
        <?php if (empty($images)): ?>
            <p>No images uploaded yet.</p>
        <?php else: ?>
            <?php foreach ($images as $img): ?>
                <div class="card">
                    <img src="<?php echo htmlspecialchars($img['file_path']); ?>" alt="<?php echo htmlspecialchars($img['title']); ?>">
                    <strong><?php echo htmlspecialchars($img['title']); ?></strong><br><br>
                    <a href="index.php?delete=<?php echo $img['id']; ?>" onclick="return confirm('Are you sure you want to delete this image?');" style="color: red;">Delete</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>