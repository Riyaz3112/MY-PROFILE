<?php
// Simple admin layout - Shopify-like sidebar + topbar
if (empty($_SESSION['admin'])) {
    header('Location: login.php'); exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo $pageTitle ?? 'Admin'; ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .admin-sidebar { width: 260px; }
  </style>
</head>
<body class="bg-gray-100 text-gray-900">
  <div class="min-h-screen flex">
    <aside class="admin-sidebar bg-white border-r hidden md:block">
      <div class="p-4 border-b"><h2 class="font-bold text-[#301040]">LOOKSTYLO</h2></div>
      <nav class="p-4 space-y-2 text-sm">
        <a href="dashboard.php" class="block px-3 py-2 rounded hover:bg-gray-50">Dashboard</a>
        <a href="products.php" class="block px-3 py-2 rounded hover:bg-gray-50">Products</a>
        <a href="image-upload.php" class="block px-3 py-2 rounded hover:bg-gray-50">Upload Images</a>
        <a href="orders.php" class="block px-3 py-2 rounded hover:bg-gray-50">Orders</a>
        <a href="billing.php" class="block px-3 py-2 rounded hover:bg-gray-50">Billing</a>
        <a href="logout.php" class="block px-3 py-2 rounded hover:bg-gray-50">Logout</a>
      </nav>
    </aside>
    <div class="flex-1">
      <header class="bg-white border-b p-4 flex items-center justify-between">
        <div class="flex items-center gap-4">
          <button id="menuToggle" class="md:hidden px-2 py-1 border rounded">Menu</button>
          <h1 class="text-lg font-semibold"><?php echo $pageTitle ?? 'Admin'; ?></h1>
        </div>
        <div>
          <span class="text-sm text-gray-600">Admin</span>
        </div>
      </header>
      <main class="p-6">
        <?php echo $content ?? ''; ?>
      </main>
    </div>
  </div>
  <script>
    document.getElementById('menuToggle')?.addEventListener('click', ()=>{
      const aside = document.querySelector('aside');
      if (aside) aside.classList.toggle('hidden');
    });
  </script>
</body>
</html>
