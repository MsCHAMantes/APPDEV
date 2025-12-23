<?php
    session_start();

    if(!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../login.php");
        exit;
    }

    require '../config/db.php';

    $error = '';
    $success = '';

    // Create category
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if ($name === '') {
            $error = 'Name is required.';
        } else {
            try {
                $stmt = $con->prepare("INSERT INTO categories (name, description, is_active) VALUES (:name, :description, :is_active)");
                $stmt->execute([
                    ':name' => $name,
                    ':description' => $description,
                    ':is_active' => $is_active
                ]);
                $success = 'Category created.';
            } catch (Exception $e) {
                $error = 'Failed to create category: ' . $e->getMessage();
            }
        }
    }

    // Update category
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if ($id <= 0 || $name === '') {
            $error = 'Valid id and name are required.';
        } else {
            try {
                $stmt = $con->prepare("UPDATE categories SET name = :name, description = :description, is_active = :is_active WHERE id = :id");
                $stmt->execute([
                    ':name' => $name,
                    ':description' => $description,
                    ':is_active' => $is_active,
                    ':id' => $id
                ]);
                $success = 'Category updated.';
            } catch (Exception $e) {
                $error = 'Failed to update category: ' . $e->getMessage();
            }
        }
    }

    // Toggle active
    if (isset($_GET['toggle'])) {
        $id = (int)$_GET['toggle'];
        $to = isset($_GET['to']) ? (int)$_GET['to'] : 0;
        if ($id > 0) {
            try {
                $stmt = $con->prepare("UPDATE categories SET is_active = :to WHERE id = :id");
                $stmt->execute([':to' => $to, ':id' => $id]);
                header('Location: ./categories.php');
                exit;
            } catch (Exception $e) {
                $error = 'Failed to toggle category: ' . $e->getMessage();
            }
        }
    }

    // Delete category (may fail due to FK constraints)
    if (isset($_GET['delete'])) {
        $id = (int)$_GET['delete'];
        if ($id > 0) {
            try {
                $stmt = $con->prepare("DELETE FROM categories WHERE id = :id");
                $stmt->execute([':id' => $id]);
                header('Location: ./categories.php');
                exit;
            } catch (Exception $e) {
                $error = 'Failed to delete category (in use by products?): ' . $e->getMessage();
            }
        }
    }

    // Editing state
    $editCategory = null;
    if (isset($_GET['edit'])) {
        $id = (int)$_GET['edit'];
        if ($id > 0) {
            try {
                $stmt = $con->prepare("SELECT * FROM categories WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $editCategory = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (Exception $e) {}
        }
    }

    // Fetch categories
    $categories = [];
    try {
        $categories = $con->query("SELECT id, name, description, is_active, created_at FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $categories = [];
    }
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Categories</title>
</head>
<body>
    <header>
        <h1>Categories</h1>
        <p>Signed in as: <?php echo htmlspecialchars($_SESSION['username']); ?> (<?php echo htmlspecialchars($_SESSION['role']); ?>)</p>
    </header>

    <nav>
        <ul>
            <li><a href="./index.php">Overview</a></li>
            <li><a href="./users.php">Users</a></li>
            <li><a href="./categories.php">Categories</a></li>
            <li><a href="./products.php">Products</a></li>
            <li><a href="./orders.php">Orders</a></li>
            <li><a href="./reports.php">Reports</a></li>
            <li><a href="./settings.php">Settings</a></li>
            <li><a href="../logout.php?logout=true">Logout</a></li>
        </ul>
    </nav>

    <main>
        <section>
            <?php if ($error !== ''): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <?php if ($success !== ''): ?>
                <p><?php echo htmlspecialchars($success); ?></p>
            <?php endif; ?>

            <h2>Add New Category</h2>
            <form action="" method="POST">
                <input type="hidden" name="action" value="create">
                <label>Name:</label>
                <input type="text" name="name" required>
                <br>
                <label>Description:</label>
                <input type="text" name="description">
                <br>
                <label>Active:</label>
                <input type="checkbox" name="is_active" checked>
                <br>
                <button type="submit">Create Category</button>
            </form>

            <?php if ($editCategory): ?>
                <h2>Edit Category</h2>
                <form action="" method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars((string)$editCategory['id']); ?>">
                    <label>Name:</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($editCategory['name']); ?>" required>
                    <br>
                    <label>Description:</label>
                    <input type="text" name="description" value="<?php echo htmlspecialchars((string)$editCategory['description']); ?>">
                    <br>
                    <label>Active:</label>
                    <input type="checkbox" name="is_active" <?php echo $editCategory['is_active'] ? 'checked' : ''; ?>>
                    <br>
                    <button type="submit">Update Category</button>
                </form>
            <?php endif; ?>

            <h2>All Categories</h2>
            <table border="1" cellpadding="6" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Active</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $c): ?>
                            <tr>
                                <td><?php echo htmlspecialchars((string)$c['id']); ?></td>
                                <td><?php echo htmlspecialchars($c['name']); ?></td>
                                <td><?php echo htmlspecialchars((string)$c['description']); ?></td>
                                <td><?php echo $c['is_active'] ? 'Yes' : 'No'; ?></td>
                                <td><?php echo htmlspecialchars((string)$c['created_at']); ?></td>
                                <td>
                                    <a href="./categories.php?edit=<?php echo htmlspecialchars((string)$c['id']); ?>">Edit</a> |
                                    <?php if ($c['is_active']): ?>
                                        <a href="./categories.php?toggle=<?php echo htmlspecialchars((string)$c['id']); ?>&to=0">Deactivate</a> |
                                    <?php else: ?>
                                        <a href="./categories.php?toggle=<?php echo htmlspecialchars((string)$c['id']); ?>&to=1">Activate</a> |
                                    <?php endif; ?>
                                    <a href="./categories.php?delete=<?php echo htmlspecialchars((string)$c['id']); ?>" onclick="return confirm('Delete this category?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No categories found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Admin Panel</p>
    </footer>
</body>
</html>


