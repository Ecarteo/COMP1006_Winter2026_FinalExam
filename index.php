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