<?php
require_once 'db.php';

try {
    // Test database connection
    $test = $pdo->query("SELECT COUNT(*) as count FROM categories WHERE parent_id IS NOT NULL")->fetch();
    echo "<h1>Database Connection Test</h1>";
    echo "<p>Database connection successful!</p>";
    echo "<p>Number of subcategories found: " . $test['count'] . "</p>";
    
    // Show some sample subcategories
    $subcategories = $pdo->query("SELECT id, name FROM categories WHERE parent_id IS NOT NULL LIMIT 5")->fetchAll();
    echo "<h2>Sample Subcategories:</h2>";
    echo "<ul>";
    foreach ($subcategories as $subcat) {
        echo "<li>" . htmlspecialchars($subcat['name']) . " (ID: " . $subcat['id'] . ")</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<h1>Database Connection Error</h1>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
