// js/product.js
const qs = new URLSearchParams(window.location.search);
const idParam = qs.get('id');
const productContainer = document.getElementById('productContainer');

function formatPrice(n){ return '₹' + n; }

async function loadProduct(){
  if(!idParam){
    productContainer.innerHTML = '<p class="text-red-600">No product specified.</p>';
    return;
  }

    try{
    const apiUrl = './product-api.php';
    const res = await fetch(`${apiUrl}?id=${encodeURIComponent(idParam)}`);
    const text = await res.text();
    let data;
    try {
      data = JSON.parse(text);
    } catch (jsonErr) {
      console.error('Invalid JSON response from product-api.php:', text);
      productContainer.innerHTML = `<p class="text-red-600">Failed to load product. Invalid server response: ${jsonErr.message}</p>`;
      return;
    }
    if (!res.ok || !data.success){
      console.error('Product API error:', res.status, data);
      productContainer.innerHTML = `<p class="text-gray-700">Product not found. API response: ${data.message || 'Unknown error'}</p>`;
      return;
    }
    const product = data.product;
    const imageList = data.images || [];

    if(!product){
      productContainer.innerHTML = '<p class="text-gray-700">Product not found.</p>';
      return;
    }

    productContainer.innerHTML = `
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="md:col-span-1">
          <img id="mainProductImage" src="${imageList[0] || product.image || 'https://placehold.co/600x600/301040/ffffff?text=' + encodeURIComponent(product.name)}" alt="${product.name}" class="w-full h-auto rounded" onerror="this.src='https://placehold.co/600x600/301040/ffffff?text=${encodeURIComponent(product.name)}'" />
          ${imageList.length > 1 ? `<div class="mt-3 flex gap-2">${imageList.slice(1).map((i, idx) => `<img src="${i}" alt="Product ${idx + 2}" class="w-20 h-20 object-cover rounded cursor-pointer hover:opacity-75" onclick="document.getElementById('mainProductImage').src='${i}'" onerror="this.src='https://placehold.co/100x100/301040/ffffff?text=Image'">`).join('')}</div>` : ''}
        </div>
        <div class="md:col-span-2">
          <h1 class="text-2xl font-bold mb-2">${product.name}</h1>
          <p class="text-gray-600 mb-4">${product.description}</p>
          <div class="text-2xl font-extrabold text-purple-700 mb-4">${formatPrice(product.price)}</div>

          <div class="space-y-3 mb-6">
            <div>
              <label class="block text-sm font-medium mb-1">Size</label>
              <select id="sizeSelect" class="w-40 border border-gray-300 rounded px-3 py-2 text-sm">
                <option>S</option>
                <option selected>M</option>
                <option>L</option>
                <option>XL</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Color <span class="text-gray-500">(Type any color name or hex code)</span></label>
              <input id="colorText" type="text" placeholder="e.g., Black, Red, Blue, Navy, #FF0000, #00FF00" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" value="Black" />
              <p class="text-xs text-gray-500 mt-1">Enter color name, hex code (#RRGGBB), or RGB value</p>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Quantity</label>
              <input id="qtyInput" type="number" value="1" min="1" class="w-24 border border-gray-300 rounded px-3 py-2" />
            </div>
          </div>

          <div class="flex gap-3">
            <button id="addToCartBtn" class="bg-purple-700 hover:bg-purple-800 text-white font-bold px-5 py-2 rounded">Add to Cart</button>
            <a href="cart.php" class="border border-gray-200 px-5 py-2 rounded hover:bg-gray-50">View Cart</a>
          </div>
        </div>
      </div>
    `;

    document.getElementById('addToCartBtn').addEventListener('click', () => addToCart(product));

  }catch(err){
    console.error(err);
    productContainer.innerHTML = '<p class="text-red-600">Failed to load product.</p>';
  }
}

function addToCart(product){
  const size = document.getElementById('sizeSelect').value;
  const color = document.getElementById('colorText').value || 'Black';
  const qty = parseInt(document.getElementById('qtyInput').value) || 1;

  let cart = JSON.parse(localStorage.getItem('cart') || '[]');
  const existing = cart.find(item => item.id === product.id && item.size === size && item.color === color);
  if(existing){ existing.quantity += qty; } else {
    cart.push({ id: product.id, name: product.name, price: product.price, size, color, quantity: qty });
  }
  localStorage.setItem('cart', JSON.stringify(cart));
  // brief feedback
  const btn = document.getElementById('addToCartBtn');
  btn.textContent = '✓ Added';
  btn.disabled = true;
  setTimeout(() => { btn.textContent = 'Add to Cart'; btn.disabled = false; }, 1400);
  // update cart count if present in page
  const countEl = document.getElementById('cartCount');
  if(countEl){
    const total = cart.reduce((s,i)=> s + i.quantity, 0);
    countEl.textContent = total;
  }
}

loadProduct();
