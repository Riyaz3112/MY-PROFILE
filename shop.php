<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Shop | LookStylo Clothing';
$pdo = getDbConnection();
// handle search & category filter
$q = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? '');
$params = [];
$where = [];
if ($q !== '') {
    $where[] = '(name LIKE ? OR description LIKE ?)';
    $params[] = "%{$q}%"; $params[] = "%{$q}%";
}
if ($category !== '') {
    $where[] = 'category = ?';
    $params[] = $category;
}
$sql = 'SELECT * FROM products' . (empty($where) ? '' : ' WHERE ' . implode(' AND ', $where)) . ' ORDER BY id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
$categories = $pdo->query('SELECT DISTINCT category FROM products')->fetchAll(PDO::FETCH_COLUMN);
include __DIR__ . '/includes/header.php';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-8 text-center">
        <p class="text-sm uppercase tracking-[0.3em] text-purple-700">LookStylo Collection</p>
    </div>
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-3">
            <input id="searchInput" type="search" name="q" placeholder="Search products..." value="<?php echo htmlspecialchars($q); ?>" class="rounded-xl border border-gray-300 px-4 py-2 w-72">
            <button id="searchBtn" class="rounded-full bg-[#301040] px-4 py-2 text-white">Search</button>
        </div>
        <div class="flex items-center gap-3">
            <label class="text-sm text-gray-600">Category</label>
            <select id="categorySelect" class="rounded-xl border border-gray-300 px-3 py-2">
                <option value="">All</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo ($category === $cat) ? 'selected' : ''; ?>><?php echo htmlspecialchars(ucfirst($cat)); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($products as $product): ?>
            <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-soft">
                <a href="product.php?id=<?php echo (int) $product['id']; ?>" class="mb-4 flex h-48 items-center justify-center rounded-2xl bg-gray-100 overflow-hidden hover:opacity-90">
                    <img src="<?php echo htmlspecialchars($product['image'] ?: 'https://placehold.co/300x300/301040/ffffff?text=' . urlencode($product['name'])); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="h-40 w-full object-contain" onerror="this.src='https://placehold.co/300x300/301040/ffffff?text=<?php echo urlencode($product['name']); ?>'">
                </a>
                <div class="flex items-center justify-between">
                    <a href="product.php?id=<?php echo (int) $product['id']; ?>" class="text-lg font-semibold text-gray-900 hover:text-purple-700"><?php echo htmlspecialchars($product['name']); ?></a>
                    <div class="text-lg font-bold text-purple-900">₹<?php echo number_format((float) $product['price'], 0); ?></div>
                </div>
                <p class="mt-3 text-sm text-gray-600"><?php echo htmlspecialchars($product['description']); ?></p>
                <form method="post" action="cart.php" class="mt-6 space-y-3">
                    <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?php echo (int) $product['id']; ?>">
                    <div class="grid gap-3 sm:grid-cols-3">
                        <select name="size" class="rounded-xl border border-gray-300 px-3 py-2 text-sm">
                            <option value="M">Size: M</option>
                            <option value="L">Size: L</option>
                            <option value="XL">Size: XL</option>
                        </select>
                        <input type="text" name="color" placeholder="Color" value="Black" class="rounded-xl border border-gray-300 px-3 py-2 text-sm" />
                        <div>
                            <label class="sr-only">Quantity</label>
                            <input type="number" name="quantity" value="1" min="1" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm" />
                        </div>
                    </div>
                    <button class="w-full rounded-full bg-[#301040] px-4 py-3 text-sm font-semibold text-white hover:bg-purple-900">Add to Cart</button>
                </form>
                <a href="my-orders.php" class="w-full block text-center mt-2 rounded-full border-2 border-[#301040] px-4 py-2 text-sm font-semibold text-[#301040] hover:bg-purple-50">My Orders</a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
<script>
document.getElementById('searchBtn').addEventListener('click', () => {
    const q = encodeURIComponent(document.getElementById('searchInput').value.trim());
    const cat = encodeURIComponent(document.getElementById('categorySelect').value);
    const params = new URLSearchParams();
    if (q) params.set('q', q);
    if (cat) params.set('category', cat);
    window.location.search = params.toString();
});
document.getElementById('categorySelect').addEventListener('change', () => document.getElementById('searchBtn').click());
</script>