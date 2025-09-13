<?php
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../partials/header.php';

// Get URL parameters
$idParam = filter_input(INPUT_GET, 'id', FILTER_DEFAULT);
$slugParam = filter_input(INPUT_GET, 'slug', FILTER_DEFAULT);
$sort = filter_input(INPUT_GET, 'sort', FILTER_DEFAULT) ?: '';
$category = filter_input(INPUT_GET, 'category', FILTER_DEFAULT) ?: '';
$search = filter_input(INPUT_GET, 'search', FILTER_DEFAULT) ?: '';

$product = null;
$products = [];
$categories = [];
$id = null;

        // Fetch categories for filter dropdown
        try {
            // Disable any caching headers to ensure fresh data
            header("Cache-Control: no-cache, no-store, must-revalidate");
            header("Pragma: no-cache");
            header("Expires: 0");

            $catStmt = $pdo->query("SELECT id, name, slug FROM categories WHERE is_active = 1 ORDER BY name");
            $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $categories = [];
        }

        // Debug: Log categories fetched
        error_log('Categories fetched: ' . print_r($categories, true));

        // Debug: Log products fetched
        error_log('Products fetched: ' . print_r($products, true));

// Check if we should show single product or product listing
if (($idParam !== null && $idParam !== '') || ($slugParam !== null && $slugParam !== '')) {
    // Single product view
    try {
        if ($idParam !== null && $idParam !== '' && filter_var($idParam, FILTER_VALIDATE_INT)) {
            $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ? AND is_active = 1 LIMIT 1');
            $stmt->execute([(int)$idParam]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            $id = (int)$idParam;
        } elseif ($slugParam !== null && $slugParam !== '') {
            $stmt = $pdo->prepare('SELECT * FROM products WHERE slug = ? AND is_active = 1 LIMIT 1');
            $stmt->execute([$slugParam]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($product) {
                $id = (int)$product['id'];
            }
        }

        if (!$product) {
            http_response_code(404);
            include __DIR__ . '/../partials/404-product.php';
            require_once __DIR__ . '/../partials/footer.php';
            exit;
        }
    } catch (PDOException $e) {
        http_response_code(500);
        exit('Database error');
    }
} else {
    // Product listing view
    try {
        // Build query with filters
        $sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug
                FROM products p
                LEFT JOIN categories c ON c.id = p.category_id
                WHERE p.is_active = 1";

        $params = [];

        // Apply category filter
        if ($category !== '') {
            if (filter_var($category, FILTER_VALIDATE_INT)) {
                $sql .= " AND c.id = :category_id";
                $params[':category_id'] = (int)$category;
            } else {
                $sql .= " AND c.slug = :category_slug";
                $params[':category_slug'] = $category;
            }
        }

        // Apply search filter
        if ($search !== '') {
            $sql .= " AND (p.name LIKE :search OR p.description LIKE :search OR p.short_description LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        // Apply sorting
        switch ($sort) {
            case 'price_asc':
                $sql .= " ORDER BY p.price ASC";
                break;
            case 'price_desc':
                $sql .= " ORDER BY p.price DESC";
                break;
            case 'name_asc':
                $sql .= " ORDER BY p.name ASC";
                break;
            case 'name_desc':
                $sql .= " ORDER BY p.name DESC";
                break;
            case 'newest':
                $sql .= " ORDER BY p.id DESC";
                break;
            default:
                $sql .= " ORDER BY RAND()";
                break;
        }

        // Add LIMIT for randomized products only when no filters or search applied
        if ($sort === '' && $category === '' && $search === '') {
            $sql .= " LIMIT 20";
        }

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $val) {
            if (is_int($val)) {
                $stmt->bindValue($key, $val, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $val, PDO::PARAM_STR);
            }
        }
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $products = [];
    }
}

// Process single product data
$images = [];
$specs = [];
$cat = null;
$parent = null;
$price = null;
$inStock = false;
$minQty = 1;
$reviews = [];
$relatedProducts = [];

if ($product) {
    $images = json_decode($product['images'] ?? '[]', true) ?: [];
    $specs = json_decode($product['specifications'] ?? '{}', true) ?: [];

    // Fetch category and parent for breadcrumbs
    if (!empty($product['category_id'])) {
        $c = $pdo->prepare('SELECT * FROM categories WHERE id = ? AND is_active = 1');
        $c->execute([$product['category_id']]);
        $cat = $c->fetch(PDO::FETCH_ASSOC);
        if ($cat && !empty($cat['parent_id'])) {
            $p = $pdo->prepare('SELECT * FROM categories WHERE id = ? AND is_active = 1');
            $p->execute([$cat['parent_id']]);
            $parent = $p->fetch(PDO::FETCH_ASSOC);
        }
    }

    $price = $product['price'] ?? null;
    $inStock = (int)($product['stock_quantity'] ?? 0) > 0;
    $minQty = max((int)($product['min_order_quantity'] ?? 1), 1);

    // Fetch reviews
    $stmtReviews = $pdo->prepare("SELECT * FROM reviews WHERE product_id = :product_id ORDER BY created_at DESC");
    $stmtReviews->execute([':product_id' => $id]);
    $reviews = $stmtReviews->fetchAll(PDO::FETCH_ASSOC);

    // Fetch related products
    if (!empty($product['category_id'])) {
        $stmtRelated = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? AND is_active = 1 LIMIT 8");
        $stmtRelated->execute([$product['category_id'], $id]);
        $relatedProducts = $stmtRelated->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title><?php if ($product): ?><?= htmlspecialchars($product['name']) ?><?php else: ?>Products<?php endif; ?> • Jungle Mart</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <?php if ($product): ?>
    <meta name="description" content="<?= htmlspecialchars($product['short_description'] ?: substr($product['description'], 0, 160)) ?>" />
    <meta property="og:title" content="<?= htmlspecialchars($product['name']) ?> • Jungle Mart" />
    <meta property="og:description" content="<?= htmlspecialchars($product['short_description'] ?: substr($product['description'], 0, 160)) ?>" />
    <meta property="og:image" content="<?= htmlspecialchars($images[0] ?? '') ?>" />
    <meta property="og:type" content="product" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?= htmlspecialchars($product['name']) ?> • Jungle Mart" />
    <meta name="twitter:description" content="<?= htmlspecialchars($product['short_description'] ?: substr($product['description'], 0, 160)) ?>" />
    <meta name="twitter:image" content="<?= htmlspecialchars($images[0] ?? '') ?>" />
    <?php endif; ?>
    <link rel="stylesheet" href="../css/style.css" />
    <link rel="stylesheet" href="../css/navbar.css" />
    <link rel="stylesheet" href="../css/product.css" />
    <link rel="stylesheet" href="../css/footer.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <script defer src="../js/script.js"></script>
    <script defer src="../js/cart.js"></script>
    <script defer src="../js/product.js"></script>
    <?php if ($product): ?>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Product",
        "name": "<?= htmlspecialchars($product['name']) ?>",
        "sku": "<?= htmlspecialchars($product['sku'] ?? '') ?>",
        "image": [<?= implode(',', array_map(fn($img) => '"' . htmlspecialchars($img) . '"', $images)) ?>],
        "brand": {
            "@type": "Brand",
            "name": "Jungle Mart"
        },
        "offers": {
            "@type": "Offer",
            "price": "<?= $price ?: '' ?>",
            "priceCurrency": "INR",
            "availability": "<?= $inStock ? 'InStock' : 'OutOfStock' ?>"
        }
    }
    </script>
    <?php endif; ?>
</head>
<body>
<?php require_once __DIR__ . '/../partials/navbar.php'; ?>

<div class="container">
    <?php if ($product): ?>
        <!-- Single Product View -->
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="../index.php">Home</a> ›
            <?php if ($parent): ?>
                <a href="../pages/categories.php?id=<?= $parent['id'] ?>"><?= htmlspecialchars($parent['name']) ?></a> ›
            <?php endif; ?>
            <?php if ($cat): ?>
                <a href="../pages/categories.php?id=<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></a> ›
            <?php endif; ?>
            <span aria-current="page"><?= htmlspecialchars($product['name']) ?></span>
        </nav>

        <div class="prod-detail two-column-layout" role="main">
            <div class="left-column">
                <div class="gallery" aria-label="Product image gallery">
                    <div class="main-image" tabindex="0">
                        <img id="main-product-image" src="<?= htmlspecialchars($images[0] ?? '../images/placeholder.png') ?>" alt="<?= htmlspecialchars($product['name']) ?> – image 1" loading="lazy" style="max-width: 300px;" />
                    </div>
                    <?php if (count($images) > 1): ?>
                        <div class="thumbnails" role="list">
                            <?php foreach ($images as $index => $img): ?>
                                <img class="thumbnail <?= $index === 0 ? 'active' : '' ?>" src="<?= htmlspecialchars($img) ?>" data-image="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($product['name']) ?> – image <?= $index + 1 ?>" loading="lazy" style="width: 60px; height: 60px; object-fit: cover;" role="listitem" tabindex="0" />
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="right-column">
                <h1><?= htmlspecialchars($product['name']) ?></h1>

                <div class="price-section">
                    <?php if ($price && is_numeric($price)): ?>
                        <span class="price">₹<?= number_format((float)$price, 2) ?></span>
                    <?php else: ?>
                        <span class="price on-request">Price on request</span>
                    <?php endif; ?>
                    <?php if (!$inStock): ?>
                        <span class="stock-badge" role="alert">Out of stock</span>
                    <?php endif; ?>
                </div>

                <div class="short-description">
                    <?= nl2br(htmlspecialchars($product['short_description'] ?: substr($product['description'], 0, 160))) ?>
                </div>

                <div class="quantity-section" aria-label="Quantity selector">
                    <label for="quantity">Quantity</label>
                    <button class="qty-btn minus" type="button" aria-label="Decrease quantity">-</button>
                    <input type="number" id="quantity" name="quantity" value="<?= $minQty ?>" min="<?= $minQty ?>" max="<?= $inStock ? $product['stock_quantity'] : $minQty ?>" aria-live="polite" />
                    <button class="qty-btn plus" type="button" aria-label="Increase quantity">+</button>
                </div>

                <div class="actions">
                    <button class="btn primary add-to-cart" <?= !$inStock ? 'disabled' : '' ?>
                            data-id="<?= $id ?>"
                            data-title="<?= htmlspecialchars($product['name']) ?>"
                            data-price="<?= $price ?>"
                            data-wholesale="<?= htmlspecialchars($product['wholesale_price'] ?? '') ?>"
                            data-moq="<?= $minQty ?>">
                        Add to Cart
                    </button>
                </div>
            </div>
        </div>

        <div class="product-tabs" role="tablist" aria-label="Product details tabs">
            <div class="tab-buttons">
                <button class="tab-btn active" data-tab="description" role="tab" aria-selected="true" aria-controls="description" id="tab-description">Description</button>
                <button class="tab-btn" data-tab="specifications" role="tab" aria-selected="false" aria-controls="specifications" id="tab-specifications">Specifications</button>
        </div>

        <div id="description" class="tab-content active" role="tabpanel" aria-labelledby="tab-description">
            <?php
            $allowedTags = '<p><ul><li><b><i><strong><em><br>';
            echo strip_tags($product['description'], $allowedTags);
            ?>
        </div>

        <div id="specifications" class="tab-content" role="tabpanel" aria-labelledby="tab-specifications">
            <?php if (!empty($specs)): ?>
                <table>
                    <?php foreach ($specs as $key => $value): ?>
                        <?php if (strtolower($key) !== 'botanical_name'): ?>
                            <tr><td><?= htmlspecialchars($key) ?></td><td><?= htmlspecialchars($value) ?></td></tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Reviews Section -->
    <div class="reviews-section">
        <div class="reviews-layout" style="display: flex; gap: 40px; margin-top: 20px;">
            <div class="reviews-left" style="flex: 1;">
                <h3>Customer Reviews</h3>
                <?php if (!empty($reviews)): ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="review">
                            <strong><?= htmlspecialchars($review['name']) ?></strong>
                            <p><?= htmlspecialchars($review['comment']) ?></p>
                            <small><?= htmlspecialchars($review['created_at']) ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No reviews yet. Be the first to review this product!</p>
                <?php endif; ?>
            </div>
            <div class="reviews-right" style="flex: 1;">
                <h3>Write a Review</h3>
                <form class="review-form" onsubmit="console.log('Review submitted'); return false;">
                    <div class="form-group">
                        <label for="review-name">Name</label>
                        <input type="text" id="review-name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="review-comment">Comment</label>
                        <textarea id="review-comment" name="comment" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn primary">Submit Review</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Related Products Section -->
    <div class="related-section">
        <h2>Related Products</h2>
        <div class="related-products">
            <?php if (!empty($relatedProducts)): ?>
                <?php foreach ($relatedProducts as $rel): ?>
                    <div class="product-card">
                        <img src="<?= htmlspecialchars(json_decode($rel['images'], true)[0] ?? '../images/placeholder.png') ?>" alt="<?= htmlspecialchars($rel['name']) ?>" loading="lazy" />
                        <h3><?= htmlspecialchars($rel['name']) ?></h3>
                        <p>₹<?= number_format($rel['price'], 2) ?></p>
                        <a href="?id=<?= $rel['id'] ?>" class="btn">View</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No related products found.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="lightbox" role="dialog" aria-modal="true" aria-label="Image lightbox">
        <button class="close" aria-label="Close lightbox">&times;</button>
        <img class="lightbox-content" src="" alt="" />
        <button class="prev" aria-label="Previous image"><</button>
        <button class="next" aria-label="Next image">></button>
    </div>

    <?php else: ?>
        <!-- Product Listing View -->
        <h1>Products</h1>

        <!-- Filters and Search -->
        <form method="get" class="filters-row" action="product.php" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
            <div class="filter-group" style="flex: 1 1 30%;">
                <label for="search" style="display: block; font-weight: bold;">Search:</label>
                <input type="text" name="search" id="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search products..." style="width: 100%;" />
            </div>

            <div class="filter-group" style="flex: 1 1 30%;">
                <label for="category" style="display: block; font-weight: bold;">Filter by Category:</label>
                <select name="category" id="category" onchange="this.form.submit()" style="width: 100%;">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['slug']) ?>" <?= $category === $cat['slug'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group" style="flex: 1 1 30%;">
                <label for="sort" style="display: block; font-weight: bold;">Sort by:</label>
                <select name="sort" id="sort" onchange="this.form.submit()" style="width: 100%;">
                    <option value="">Random</option>
                    <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                    <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                    <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Name: A to Z</option>
                    <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : '' ?>>Name: Z to A</option>
                    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
                </select>
            </div>

            <div style="flex: 0 0 auto;">
                <button type="submit" class="btn primary" style="margin-top: 1.8rem;">Apply Filters</button>
            </div>
        </form>

        <!-- Product Grid -->
        <div class="products-grid grid-4-cols">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $prod): ?>
                    <?php
                        $prodImages = json_decode($prod['images'] ?? '[]', true) ?: [];
                        $prodImage = $prodImages[0] ?? '../images/placeholder.png';
                        $prodPrice = $prod['price'] ?? null;
                        $prodInStock = (int)($prod['stock_quantity'] ?? 0) > 0;
                    ?>
                    <div class="product-card">
                        <a href="?id=<?= htmlspecialchars($prod['id']) ?>">
                            <img src="<?= htmlspecialchars($prodImage) ?>" alt="<?= htmlspecialchars($prod['name']) ?>" loading="lazy" />
                            <h3><?= htmlspecialchars($prod['name']) ?></h3>
                        </a>
                        <div class="product-info">
                            <?php if ($prodPrice && is_numeric($prodPrice)): ?>
                                <p class="price">₹<?= number_format((float)$prodPrice, 2) ?></p>
                            <?php else: ?>
                                <p class="price on-request">Price on request</p>
                            <?php endif; ?>
                            <?php if (!$prodInStock): ?>
                                <span class="stock-badge">Out of stock</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-actions">
                            <button class="btn add-to-cart"
                                    data-id="<?= $prod['id'] ?>"
                                    data-title="<?= htmlspecialchars($prod['name']) ?>"
                                    data-price="<?= $prodPrice ?>"
                                    data-wholesale="<?= htmlspecialchars($prod['wholesale_price'] ?? '') ?>"
                                    data-moq="<?= max(1, (int)($prod['min_order_quantity'] ?? 1)) ?>"
                                    <?= !$prodInStock ? 'disabled' : '' ?>>
                                Add to Cart
                            </button>
                            <a href="?id=<?= $prod['id'] ?>" class="btn ghost">View</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-products">
                    <p>No products found matching your criteria.</p>
                    <a href="product.php" class="btn primary">Clear Filters</a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
