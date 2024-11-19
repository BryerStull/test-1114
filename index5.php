<?php

session_start();
require_once 'auth.php';

// Check if user is logged in
// ANY STUPID COMMENT
// COMMENT 2
// COMMENT 3
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$host = 'localhost'; 
$dbname = 'books'; 
$user = 'bryer'; 
$pass = 'bryer';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}

// Handle book search
$search_results = null;
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = '%' . $_GET['search'] . '%';
    $search_sql = 'SELECT joke_id, joke, is_it_good, setup, punchline FROM jokes WHERE joke LIKE :search';
    $search_stmt = $pdo->prepare($search_sql);
    $search_stmt->execute(['search' => $search_term]);
    $search_results = $search_stmt->fetchAll();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['joke']) && isset($_POST['Is_it_good']) && isset($_POST['setup']) && isset($_POST['punchline'])) {
        // Insert new entry
        $joke = htmlspecialchars($_POST['joke']);
        $Is_it_good = htmlspecialchars($_POST['Is_it_good']);
        $setup = htmlspecialchars($_POST['setup']);
        $punchline = htmlspecialchars($_POST['punchline']);

        
        $insert_sql = 'INSERT INTO jokes (joke, Is_it_good, setup, punchline) VALUES (:joke, :Is_it_good, :setup, :punchline)';
        $stmt_insert = $pdo->prepare($insert_sql);
        $stmt_insert->execute(['joke' => $joke, 'Is_it_good' => $Is_it_good, 'setup' => $setup, 'punchline' => $punchline]);
    } elseif (isset($_POST['delete_id'])) {
        // Delete an entry
        $delete_id = (int) $_POST['delete_id'];
        
        $delete_sql = 'DELETE FROM jokes WHERE joke_id = :id';
        $stmt_delete = $pdo->prepare($delete_sql);
        $stmt_delete->execute(['id' => $delete_id]);
    }
}

// Get all books for main table
$sql = 'SELECT joke_id, joke, Is_it_good, setup, punchline FROM jokes';
$stmt = $pdo->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Jokes to Tell</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Hero Section -->
    <div class="hero-section">
        <h1 class="hero-title">Jokes to tell</h1>
        <p class="hero-subtitle">"A place for all of the jokes you've been storing for the perfect moment!"</p>
        
        <!-- Search moved to hero section -->
        <div class="hero-search">
            <h2>Jokes</h2>
            <form action="" method="GET" class="search-form">
                <label for="search">Search by Title:</label>
                <input type="text" id="search" name="search" required>
                <input type="submit" value="Search">
            </form>
            
            <?php if (isset($_GET['search'])): ?>
                <div class="search-results">
                    <h3>Search Results</h3>
                    <?php if ($search_results && count($search_results) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Joke ID</th>
                                    <th>Joke</th>
                                    <th>Is it Good?</th>
                                    <th>Setup</th>
                                    <th>Punchline</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($search_results as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['joke_id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['joke']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Is_it_good']); ?></td>
                                    <td><?php echo htmlspecialchars($row['setup']); ?></td>
                                    <td><?php echo htmlspecialchars($row['punchline']); ?></td>
                                    <td>
                                        <form action="index5.php" method="post" style="display:inline;">
                                            <input type="hidden" name="delete_id" value="<?php echo $row['joke_id']; ?>">
                                            <input type="submit" value="Nobody Laughed">
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No jokes found matching your search.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Table section with container -->
    <div class="table-container">
        <h2>All Jokes in Database</h2>
        <table class="half-width-left-align">
            <thead>
                <tr>
                    <th>Joke ID</th>
                    <th>Joke</th>
                    <th>Is it Good?</th>
                    <th>Setup</th>
                    <th>Punchline</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $stmt->fetch()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['joke_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['joke']); ?></td>
                    <td><?php echo htmlspecialchars($row['Is_it_good']); ?></td>
                    <td><?php echo htmlspecialchars($row['setup']); ?></td>
                    <td><?php echo htmlspecialchars($row['punchline']); ?></td>
                    <td>
                        <form action="index5.php" method="post" style="display:inline;">
                            <input type="hidden" name="delete_id" value="<?php echo $row['joke_id']; ?>">
                            <input type="submit" value="Nobody Laughed">
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Form section with container -->
    <div class="form-container">
        <h2> Add a Joke Today</h2>
        <form action="index5.php" method="post">
            <label for="joke">Joke:</label>
            <input type="text" id="joke" name="joke" required>
            <br><br>
            <label for="Is_it_good">Is It Good?:</label>
            <input type="text" id="Is_it_good" name="Is_it_good" required>
            <br><br>
            <label for="setup">Setup:</label>
            <input type="text" id="setup" name="setup" required>
            <br><br>
            <label for="punchline">Punchline:</label>
            <input type="text" id="punchline" name="punchline" required>
            <input type="submit" value="add joke">
        </form>
    </div>
</body>
</html>