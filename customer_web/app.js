/**
 * FLIPKART 100% PIXEL-PERFECT COMPLETE MULTI-PAGE PLATFORM
 * Includes SPA Hash Router for:
 * - Homepage (`#/`)
 * - Product Listing / Search (`#/search` or `#/category/...`)
 * - Standalone Product Detail Page (`#/product/:id`)
 * - Standalone Cart Page (`#/cart`)
 * - Checkout 4-Step Accordion (`#/checkout`)
 * - My Account / Profile (`#/account`)
 * - My Orders (`#/account/orders`)
 * - Manage Addresses (`#/account/addresses`)
 * - My Wishlist (`#/wishlist`)
 * - Login & Register (`#/login`)
 * - Seller Hub (`#/seller`)
 * - Super Admin Portal (`#/admin`)
 */

const API_BASE = 'http://localhost/ecommerce_api/api';

// --- SEED CATALOG DATA ---
const CATALOG_PRODUCTS = [
  {
    id: 1,
    title: 'Celvas Back Cover for Apple iPhone 15 (MagSafe Compatible, Shockproof Bumper)',
    subtitle: 'Bold Black, Polycarbonate + TPU, 10ft Military Grade Drop Tested',
    brand: 'Celvas',
    category: 'Mobiles',
    subCategory: 'Cases & Covers',
    price: 399,
    mrp: 1499,
    discount: '73% off',
    rating: 4.6,
    ratingCount: '15,850',
    reviewCount: '1,420',
    fassured: true,
    sponsored: false,
    delivery: 'Free delivery by Tomorrow, 11 PM',
    badge: 'Special Price',
    images: [
      'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=800&auto=format&fit=crop&q=85',
      'https://images.unsplash.com/photo-1580910051074-3eb694886505?w=800&auto=format&fit=crop&q=85',
      'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=800&auto=format&fit=crop&q=85',
      'https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=800&auto=format&fit=crop&q=85'
    ],
    variants: [
      { name: 'Bold Black', price: 399, mrp: 1499, sku: 'CEL-IPH15-MAG-BLK', inStock: true },
      { name: 'Pure White', price: 399, mrp: 1499, sku: 'CEL-IPH15-MAG-WHT', inStock: true },
      { name: 'Cyan Cider', price: 449, mrp: 1599, sku: 'CEL-IPH15-MAG-CYN', inStock: true }
    ],
    specs: {
      'Model Name': 'MagSafe Armor Bumper Edition',
      'Designed For': 'Apple iPhone 15',
      'Color': 'Bold Black (Translucent Back)',
      'Material': 'Polycarbonate, Shock-absorbing TPU',
      'MagSafe Compatible': 'Yes (Built-in N52 Neodymium Magnets)',
      'Drop Protection': 'Military Grade 10ft Drop Certified',
      'Raised Lips': '1.5mm Screen Lip, 2.0mm Camera Ring Guard',
      'Warranty': '6 Months Replacement Warranty by Celvas'
    },
    reviews: [
      { author: 'Rahul Sharma', rating: 5, date: '2 days ago', title: 'Outstanding Quality & MagSafe Grip!', text: 'The magnetic ring snaps onto Apple MagSafe charger firmly. Super snug fit on my iPhone 15 with crystal clear back. Best back cover in this budget!', verified: true },
      { author: 'Priya Sundaram', rating: 5, date: '1 week ago', title: 'Value for Money!', text: 'Fits perfectly. Drop tested accidentally from table and phone didn\'t have a single scratch. Raised bezel protected camera lens.', verified: true },
      { author: 'Arun Kumar', rating: 4, date: '2 weeks ago', title: 'Premium Feel', text: 'Buttons are tactile and clicky. Black matte bumper gives a good grip. Satisfied with purchase.', verified: true }
    ]
  },
  {
    id: 2,
    title: 'boAt Rockerz Plus 440 Dual Driver Bluetooth Headphone (Up to 80H Playtime)',
    subtitle: 'Active Noise Cancellation, Dual Pairing, Fast Charging',
    brand: 'boAt',
    category: 'Electronics',
    subCategory: 'Headphones',
    price: 2199,
    mrp: 4999,
    discount: '56% off',
    rating: 4.1,
    ratingCount: '1,449',
    reviewCount: '230',
    fassured: true,
    sponsored: true,
    delivery: 'Free delivery by Saturday',
    badge: 'Value 365',
    images: [
      'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=800&auto=format&fit=crop&q=85',
      'https://images.unsplash.com/photo-1484704849700-f032a568e944?w=800&auto=format&fit=crop&q=85'
    ],
    variants: [
      { name: 'Pitch Black', price: 2199, mrp: 4999, sku: 'BOAT-ROC-BLK', inStock: true },
      { name: 'Ivory Sand', price: 2299, mrp: 4999, sku: 'BOAT-ROC-SND', inStock: true }
    ],
    specs: {
      'Type': 'Over Ear Wireless Headphone',
      'Bluetooth Version': 'v5.3',
      'Battery Life': 'Up to 80 Hours',
      'Driver Size': '40mm Dual Drivers',
      'Charging Time': '10 Mins ASAP Charge = 10 Hours'
    }
  },
  {
    id: 3,
    title: 'boAt Airdopes 161 / 163 ASAP Charge True Wireless Earbuds (40H Playtime)',
    subtitle: '13mm Drivers, IPX5 Water Resistance, Type-C Charging',
    brand: 'boAt',
    category: 'Electronics',
    subCategory: 'Earbuds',
    price: 1099,
    mrp: 2490,
    discount: '55% off',
    rating: 4.0,
    ratingCount: '15,85,400',
    reviewCount: '84,200',
    fassured: true,
    sponsored: true,
    delivery: 'Free delivery by Tomorrow',
    badge: 'Bestseller',
    images: [
      'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=800&auto=format&fit=crop&q=85',
      'https://images.unsplash.com/photo-1606220588913-b3aacb4d2f46?w=800&auto=format&fit=crop&q=85'
    ],
    variants: [
      { name: 'Active Black', price: 1099, mrp: 2490, sku: 'BOAT-AIR-BLK', inStock: true },
      { name: 'Cool Grey', price: 1099, mrp: 2490, sku: 'BOAT-AIR-GRY', inStock: true }
    ],
    specs: {
      'Driver Size': '13mm',
      'Playtime': 'Up to 40 Hours total',
      'Charge Tech': 'ASAP Fast Charging (10m = 180m)',
      'Water Resistance': 'IPX5 Sweat Resistant'
    }
  },
  {
    id: 4,
    title: 'POCO X8 Power 5G (Celestial Blue, 256 GB, 10,000 mAh Battery)',
    subtitle: 'MediaTek Dimensity 8300 Ultra, 120Hz AMOLED Display',
    brand: 'POCO',
    category: 'Mobiles',
    subCategory: 'Smartphones',
    price: 32999,
    mrp: 39999,
    discount: '17% off',
    rating: 4.5,
    ratingCount: '24,800',
    reviewCount: '3,100',
    fassured: true,
    sponsored: false,
    delivery: 'Free delivery by Monday',
    badge: 'Flipkart Unique',
    images: [
      'https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=800&auto=format&fit=crop&q=85',
      'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=800&auto=format&fit=crop&q=85'
    ],
    variants: [
      { name: '12GB + 256GB', price: 32999, mrp: 39999, sku: 'POCO-X8-12-256', inStock: true },
      { name: '16GB + 512GB', price: 36999, mrp: 44999, sku: 'POCO-X8-16-512', inStock: true }
    ],
    specs: {
      'Display': '6.67 inch 1.5K 120Hz Crystal AMOLED',
      'Processor': 'Dimensity 8300 Ultra Octa Core',
      'Camera': '64MP OIS Triple Rear + 16MP Front',
      'Battery': '10,000 mAh Monster Battery with 67W Turbo Charge'
    }
  },
  {
    id: 5,
    title: 'Samsung Crystal 4K UHD 55 Inch Smart TV with Dynamic Color (2026 Model)',
    subtitle: 'HDR 10+, PurColor, Q-Symphony, Bixby & Alexa Built-in',
    brand: 'Samsung',
    category: 'Appliances',
    subCategory: 'Televisions',
    price: 38990,
    mrp: 59900,
    discount: '34% off',
    rating: 4.4,
    ratingCount: '48,190',
    reviewCount: '4,200',
    fassured: true,
    sponsored: false,
    delivery: 'Free installation by Sunday',
    badge: 'Top Rated',
    images: [
      'https://images.unsplash.com/photo-1593784991095-a205069470b6?w=800&auto=format&fit=crop&q=85',
      'https://images.unsplash.com/photo-1461151304267-38535e780c79?w=800&auto=format&fit=crop&q=85'
    ],
    variants: [
      { name: '55 Inch 4K', price: 38990, mrp: 59900, sku: 'SAM-TV-55-4K', inStock: true }
    ],
    specs: {
      'Resolution': 'Ultra HD (4K) 3840 x 2160 Pixels',
      'Operating System': 'Tizen Smart OS',
      'Sound Output': '20W Dolby Digital Plus'
    }
  },
  {
    id: 6,
    title: 'Sony WH-1000XM5 Wireless Industry Leading Noise Canceling Headphones',
    subtitle: '30 Hrs Playtime, Multi-Point Connection, 4 Beamforming Mics',
    brand: 'Sony',
    category: 'Electronics',
    subCategory: 'Headphones',
    price: 24990,
    mrp: 34990,
    discount: '28% off',
    rating: 4.7,
    ratingCount: '9,280',
    reviewCount: '1,120',
    fassured: true,
    sponsored: false,
    delivery: 'Free delivery by Friday',
    badge: 'Premium Pick',
    images: [
      'https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=800&auto=format&fit=crop&q=85',
      'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=800&auto=format&fit=crop&q=85'
    ],
    variants: [
      { name: 'Midnight Black', price: 24990, mrp: 34990, sku: 'SONY-XM5-BLK', inStock: true }
    ],
    specs: {
      'Noise Cancellation': 'HD Noise Canceling Processor QN1 & V1',
      'Battery': 'Up to 30 Hours (3 min charge = 3 hours)'
    }
  },
  {
    id: 7,
    title: 'Levi\'s Men Slim Fit Dark Wash Stretch Denim Jeans (Blue Ridge)',
    subtitle: 'Cotton Elastane, Mid Rise, Zip Fly, 5 Pocket Classic',
    brand: 'Levi\'s',
    category: 'Fashion',
    subCategory: 'Men\'s Jeans',
    price: 1899,
    mrp: 3799,
    discount: '50% off',
    rating: 4.3,
    ratingCount: '32,100',
    reviewCount: '2,900',
    fassured: true,
    sponsored: false,
    delivery: 'Free delivery by Tomorrow',
    badge: 'Popular',
    images: [
      'https://images.unsplash.com/photo-1542272604-780c96856592?w=800&auto=format&fit=crop&q=85',
      'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?w=800&auto=format&fit=crop&q=85'
    ],
    variants: [
      { name: 'Size 32', price: 1899, mrp: 3799, sku: 'LEV-JNS-32', inStock: true }
    ],
    specs: {
      'Fabric': '98% Cotton, 2% Elastane',
      'Fit': 'Slim Fit',
      'Rise': 'Mid Rise'
    }
  },
  {
    id: 8,
    title: 'Noise ColorFit Pro 5 Smartwatch with 1.85" AMOLED & BT Calling',
    subtitle: 'SOS, 100+ Sports Modes, Health Suite, Functional Crown',
    brand: 'Noise',
    category: 'Electronics',
    subCategory: 'Smartwatches',
    price: 2499,
    mrp: 6999,
    discount: '64% off',
    rating: 4.2,
    ratingCount: '84,900',
    reviewCount: '7,400',
    fassured: true,
    sponsored: true,
    delivery: 'Free delivery by Tomorrow',
    badge: 'Deal of Day',
    images: [
      'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?w=800&auto=format&fit=crop&q=85',
      'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800&auto=format&fit=crop&q=85'
    ],
    variants: [
      { name: 'Jet Black', price: 2499, mrp: 6999, sku: 'NOISE-CF5-BLK', inStock: true }
    ],
    specs: {
      'Display': '1.85 inch Super AMOLED',
      'Battery': 'Up to 7 Days Standard Usage'
    }
  }
];

// --- SEED ORDERS (Matching Logged In Flipkart Screen) ---
const SEED_ORDERS = [
  {
    id: 'OD12948291048192000',
    title: 'Celvas Back Cover for Apple iPhone 15 (MagSafe Compatible, Shockproof Bumper)',
    image: 'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=300',
    variant: 'Bold Black',
    price: 399,
    status: 'DELIVERED',
    statusText: 'Delivered on Sep 10, 2026',
    date: 'Sep 10, 2026',
    subText: 'Your item has been delivered'
  },
  {
    id: 'OD12948291048192001',
    title: 'MYSASHOP Grey, Brown Sling Bag Anti-Theft Water Resistant Crossbody',
    image: 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=300',
    variant: 'Color: Grey, Brown',
    price: 349,
    status: 'DELIVERED',
    statusText: 'Delivered on Jul 01',
    date: 'Jul 01, 2026',
    subText: 'Your item has been delivered'
  },
  {
    id: 'OD12948291048192002',
    title: 'Whirlpool 7 kg Magic Clean 5 Star Fully Automatic Top Load Washing Machine',
    image: 'https://images.unsplash.com/photo-1626806787461-102c1bfaaea1?w=300',
    variant: 'Color: Grey',
    price: 11293,
    status: 'DELIVERED',
    statusText: 'Delivered on Mar 09',
    date: 'Mar 09, 2026',
    subText: 'Your item has been delivered'
  },
  {
    id: 'OD12948291048192003',
    title: 'Installation and Demo for Smart Appliances',
    image: 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?w=300',
    variant: 'Service',
    price: 0,
    status: 'CANCELLED',
    statusText: 'Cancelled on Mar 12',
    date: 'Mar 12, 2026',
    subText: 'Cancelled as per customer request'
  },
  {
    id: 'OD12948291048192004',
    title: 'Mi A series 80 cm (32 inch) HD Ready LED Smart Google TV',
    image: 'https://images.unsplash.com/photo-1593784991095-a205069470b6?w=300',
    variant: 'Color: Black, Size: 32',
    price: 10848,
    status: 'DELIVERED',
    statusText: 'Delivered on Jan 19',
    date: 'Jan 19, 2026',
    subText: 'Your item has been delivered'
  }
];

// --- SEED WISHLIST (Matching Real Flipkart Wishlist) ---
const SEED_WISHLIST = [
  {
    id: 1,
    title: 'Celvas Back Cover for Apple iPhone 15 (MagSafe Compatible, Shockproof Bumper)',
    image: 'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=400',
    price: 399,
    mrp: 1499,
    discount: '73% off',
    fassured: true,
    inStock: true
  },
  {
    id: 101,
    title: 'LIVEWELL Premium Quality Jute Fabric 3 Seater Sofa (Dark Grey)',
    image: 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=400',
    price: 8574,
    mrp: 18999,
    discount: '54% off',
    fassured: false,
    inStock: true
  },
  {
    id: 102,
    title: 'Polycab EUPHORIA EP02 with 2 Year Warranty Anti Dust 1200 mm Ceiling Fan',
    image: 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?w=400',
    price: 1899,
    mrp: 3299,
    discount: '42% off',
    fassured: false,
    inStock: false
  },
  {
    id: 103,
    title: 'LG 80 cm (32 inch) HD Ready LED Smart WebOS TV (2026 Model)',
    image: 'https://images.unsplash.com/photo-1593784991095-a205069470b6?w=400',
    price: 8999,
    mrp: 21990,
    discount: '59% off',
    fassured: true,
    inStock: false
  }
];

// --- APP GLOBAL STATE ---
const state = {
  user: {
    name: 'Murugan Nagaraj',
    firstName: 'Murugan',
    lastName: 'Nagaraj',
    gender: 'Male',
    email: 'nagarajanewlife@gmail.com',
    phone: '+91 63823 79565',
    isLoggedIn: true
  },
  cart: [],
  wishlist: [...SEED_WISHLIST],
  orders: [...SEED_ORDERS],
  products: [...CATALOG_PRODUCTS],
  addresses: [
    {
      id: 1,
      name: 'Murugan Nagaraj',
      phone: '6382379565',
      pincode: '600024',
      locality: 'Kodambakkam',
      address: 'Flat 3B, New Life Residency, Arcot Road',
      city: 'Chennai',
      state: 'Tamil Nadu',
      type: 'HOME',
      isDefault: true
    },
    {
      id: 2,
      name: 'Murugan N (Bengaluru Office)',
      phone: '6382379565',
      pincode: '560103',
      locality: 'Green Glen Layout, Bellandur',
      address: 'Flat 402, Royal Palms Apartment, Outer Ring Road',
      city: 'Bengaluru',
      state: 'Karnataka',
      type: 'WORK',
      isDefault: false
    }
  ],
  selectedAddressId: 1,
  activeCartTab: 'flipkart',
  activeCategory: 'For You',
  activeSort: 'relevance',
  searchQuery: '',
  carouselIndex: 0,
  carouselTimer: null,
  pdpActiveThumb: 0
};

// Default Seed Cart Items (Matches real Flipkart active cart)
const SEED_CART_FLIPKART = [
  {
    productId: 1,
    title: 'Celvas Back Cover for Apple iPhone 15 (MagSafe Compatible, Shockproof Bumper)',
    image: 'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=300',
    price: 399,
    mrp: 1499,
    seller: 'Celvas Retail',
    quantity: 1,
    variant: 'Bold Black',
    cartType: 'flipkart'
  },
  {
    productId: 2,
    title: 'boAt Rockerz Plus 440 Dual Driver Bluetooth Headphone (Up to 80H Playtime)',
    image: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=300',
    price: 2199,
    mrp: 4999,
    seller: 'CORSECA Retail',
    quantity: 1,
    variant: 'Pitch Black',
    cartType: 'flipkart'
  },
  {
    productId: 3,
    title: 'boAt Airdopes 161 / 163 ASAP Charge True Wireless Earbuds (40H Playtime)',
    image: 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=300',
    price: 1099,
    mrp: 2490,
    seller: 'ImagineMarketing',
    quantity: 1,
    variant: 'Active Black',
    cartType: 'flipkart'
  },
  {
    productId: 201,
    title: 'Fortune Sunlite Refined Sunflower Oil Pouch (1 L)',
    image: 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=300',
    price: 135,
    mrp: 180,
    seller: 'SuperComNet Grocery',
    quantity: 2,
    variant: '1 Litre',
    cartType: 'grocery'
  },
  {
    productId: 202,
    title: 'Tata Salt Vacuum Evaporated Iodised Salt (1 kg)',
    image: 'https://images.unsplash.com/photo-1518843025960-d60217f226f5?w=300',
    price: 28,
    mrp: 30,
    seller: 'SuperComNet Grocery',
    quantity: 1,
    variant: '1 kg',
    cartType: 'grocery'
  }
];

// --- INITIALIZATION ---
document.addEventListener('DOMContentLoaded', () => {
  loadSavedState();
  initRouter();
  setupGlobalListeners();
  updateHeaderUserUI();
});

// --- PERSISTENCE ---
function loadSavedState() {
  try {
    const c = localStorage.getItem('fk_cart_items');
    if (c) {
      state.cart = JSON.parse(c);
    }
  } catch (e) {}

  if (!state.cart || state.cart.length === 0) {
    state.cart = [...SEED_CART_FLIPKART];
  }
  updateCartBadge();
}

function saveCartState() {
  try {
    localStorage.setItem('fk_cart_items', JSON.stringify(state.cart));
  } catch (e) {}
  updateCartBadge();
}

function updateCartBadge() {
  const badge = document.getElementById('cartBadge');
  const count = state.cart.reduce((acc, i) => acc + i.quantity, 0);
  if (badge) {
    badge.innerText = count;
    badge.style.display = count > 0 ? 'flex' : 'none';
  }
}

function updateHeaderUserUI() {
  const userBtn = document.getElementById('headerUserBtn');
  if (!userBtn) return;
  if (state.user.isLoggedIn) {
    userBtn.innerHTML = `
      <div style="width: 28px; height: 28px; border-radius: 50%; background: #FFE500; display:flex; align-items:center; justify-content:center; font-size:14px; font-weight:700;">
        👤
      </div>
      <span>Murugan ▾</span>
      <div class="fk-user-menu">
        <a href="#/account" class="fk-menu-item">👤 My Profile</a>
        <a href="#/supercoin" class="fk-menu-item">⚡ SuperCoin Zone</a>
        <a href="#/supercoin" class="fk-menu-item">✨ Flipkart Plus Zone</a>
        <a href="#/account/orders" class="fk-menu-item">📦 Orders</a>
        <a href="#/wishlist" class="fk-menu-item">❤️ Wishlist (${state.wishlist.length})</a>
        <a href="#/account/coupons" class="fk-menu-item">🏷️ Coupons</a>
        <a href="#/account/gift-cards" class="fk-menu-item">🎁 Gift Cards</a>
        <a href="#/account/notifications" class="fk-menu-item">🔔 All Notifications</a>
        <a href="#/account/reviews" class="fk-menu-item">⭐ My Reviews & Ratings</a>
        <div class="fk-menu-divider"></div>
        <a href="#/seller" class="fk-menu-item">🏪 Seller Hub</a>
        <a href="#/admin" class="fk-menu-item">🛡️ Super Admin</a>
        <div class="fk-menu-divider"></div>
        <a href="javascript:void(0)" onclick="logoutUser()" class="fk-menu-item" style="color:#E41D2D;">🚪 Logout</a>
      </div>
    `;
  } else {
    userBtn.innerHTML = `
      <a href="#/login" style="background:#fff; color:#2874F0; border:1px solid #E0E0E0; padding:6px 18px; border-radius:4px; font-weight:700;">
        Login
      </a>
    `;
  }
}

function logoutUser() {
  state.user.isLoggedIn = false;
  updateHeaderUserUI();
  showToast('Logged out of Flipkart');
  navigateTo('#/login');
}

// --- ROUTER ENGINE ---
function initRouter() {
  window.addEventListener('hashchange', handleRoute);
  handleRoute();
}

function navigateTo(hash) {
  window.location.hash = hash;
}

function handleRoute() {
  const hash = window.location.hash || '#/';
  const root = document.getElementById('appRoot');
  if (!root) return;

  window.scrollTo({ top: 0, behavior: 'smooth' });

  // 1. Standalone PDP: #/product/:id
  if (hash.startsWith('#/product/')) {
    const id = parseInt(hash.replace('#/product/', ''), 10);
    renderPDPPage(root, id);
    return;
  }

  // 2. Search / PLP: #/search
  if (hash.startsWith('#/search')) {
    const params = new URLSearchParams(hash.split('?')[1] || '');
    const q = params.get('q') || '';
    renderPLPPage(root, q);
    return;
  }

  // 3. Category Store: #/category/
  if (hash.startsWith('#/category/')) {
    const cat = decodeURIComponent(hash.replace('#/category/', ''));
    renderCategoryStorePage(root, cat);
    return;
  }

  // 4. Dedicated Cart Page: #/cart
  if (hash === '#/cart') {
    renderCartPage(root);
    return;
  }

  // 5. Checkout Accordion: #/checkout
  if (hash === '#/checkout') {
    renderCheckoutPage(root);
    return;
  }

  // 6. My Account / Profile: #/account
  if (hash === '#/account' || hash === '#/account/profile') {
    renderProfilePage(root, 'profile');
    return;
  }

  // 7. My Orders: #/account/orders
  if (hash === '#/account/orders') {
    renderOrdersPage(root);
    return;
  }

  // 7b. Order Details & Live Tracking: #/account/order/
  if (hash.startsWith('#/account/order/')) {
    const orderId = hash.replace('#/account/order/', '');
    renderOrderDetailsPage(root, orderId);
    return;
  }

  // 7c. PAN Card Information: #/account/pan
  if (hash === '#/account/pan') {
    renderPANPage(root);
    return;
  }

  // 7d. Saved Payments: #/account/payments
  if (hash === '#/account/payments') {
    renderPaymentsPage(root);
    return;
  }

  // 7e. 24x7 Help Center: #/help
  if (hash === '#/help') {
    renderHelpCenterPage(root);
    return;
  }

  // 8. Manage Addresses: #/account/addresses
  if (hash === '#/account/addresses') {
    renderAddressesPage(root);
    return;
  }

  // 9. My Wishlist: #/wishlist
  if (hash === '#/wishlist') {
    renderWishlistPage(root);
    return;
  }

  // 10. SuperCoin Zone: #/supercoin or #/plus
  if (hash === '#/supercoin' || hash === '#/plus') {
    renderSuperCoinPage(root);
    return;
  }

  // 11. My Coupons: #/account/coupons
  if (hash === '#/account/coupons') {
    renderCouponsPage(root);
    return;
  }

  // 12. Gift Cards: #/account/gift-cards
  if (hash === '#/account/gift-cards') {
    renderGiftCardsPage(root);
    return;
  }

  // 13. Notifications: #/account/notifications
  if (hash === '#/account/notifications') {
    renderNotificationsPage(root);
    return;
  }

  // 14. Reviews & Ratings: #/account/reviews
  if (hash === '#/account/reviews') {
    renderReviewsPage(root);
    return;
  }

  // 15. Login Page: #/login
  if (hash === '#/login') {
    renderLoginPage(root);
    return;
  }

  // 16. Seller Hub: #/seller
  if (hash === '#/seller') {
    renderSellerPage(root);
    return;
  }

  // 17. Super Admin: #/admin
  if (hash === '#/admin') {
    renderAdminPage(root);
    return;
  }

  // Default: Homepage
  renderHomePage(root);
}

// ==========================================================================
// 1. HOMEPAGE RENDERER
// ==========================================================================
function renderHomePage(root) {
  root.innerHTML = `
    <!-- Category Strip -->
    <div class="fk-category-strip">
      <div class="fk-category-inner" id="categoryStrip"></div>
    </div>

    <div class="fk-main-container">
      <!-- Hero Carousel -->
      <section class="fk-carousel-container">
        <div class="fk-carousel-track" id="carouselTrack"></div>
        <button class="fk-carousel-arrow prev" id="prevSlide">‹</button>
        <button class="fk-carousel-arrow next" id="nextSlide">›</button>
        <div class="fk-carousel-dots" id="carouselDots"></div>
      </section>

      <!-- Triple Promo Tiles -->
      <section class="fk-promo-row">
        <div class="fk-promo-card" onclick="navigateTo('#/product/1')">
          <div>
            <span class="fk-promo-badge" style="background:#FFF3E0; color:#E65100;">HOT LAUNCH</span>
            <h3 class="fk-promo-title">Celvas MagSafe Case</h3>
            <div class="fk-promo-price">From ₹399 • 73% Off</div>
            <p class="fk-promo-sub">Military Grade Drop Tested</p>
          </div>
          <img src="https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=300" class="fk-promo-img" alt="Celvas Case" />
        </div>

        <div class="fk-promo-card" onclick="navigateTo('#/product/4')">
          <div>
            <span class="fk-promo-badge" style="background:#E8F5E9; color:#2E7D32;">MASSIVE BATTERY</span>
            <h3 class="fk-promo-title">POCO X8 Power 5G</h3>
            <div class="fk-promo-price">From ₹32,999*</div>
            <p class="fk-promo-sub">10,000mAh Monster Power</p>
          </div>
          <img src="https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=300" class="fk-promo-img" alt="POCO Phone" />
        </div>

        <div class="fk-promo-card" onclick="navigateTo('#/product/5')">
          <div>
            <span class="fk-promo-badge" style="background:#EDE7F6; color:#512DA8;">SMART HOME</span>
            <h3 class="fk-promo-title">55" 4K Smart TVs</h3>
            <div class="fk-promo-price">From ₹2,352/Month*</div>
            <p class="fk-promo-sub">Upgrade to Theatre Screen</p>
          </div>
          <img src="https://images.unsplash.com/photo-1593784991095-a205069470b6?w=300" class="fk-promo-img" alt="Smart TV" />
        </div>
      </section>

      <!-- Pastel Wishlist Section -->
      <section class="fk-pastel-section">
        <h2 class="fk-pastel-header">Add to your wishlist & Save Big</h2>
        <div class="fk-pastel-grid">
          <div class="fk-pastel-card" onclick="navigateTo('#/product/1')">
            <div class="fk-pastel-img-wrap">
              <img src="https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=400" alt="Celvas Back Cover" />
            </div>
            <div class="fk-pastel-subtitle">Top Rated MagSafe</div>
            <div class="fk-pastel-offer">Special Price ₹399</div>
          </div>
          <div class="fk-pastel-card" onclick="navigateTo('#/product/2')">
            <div class="fk-pastel-img-wrap">
              <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400" alt="boAt Headphones" />
            </div>
            <div class="fk-pastel-subtitle">Bestsellers</div>
            <div class="fk-pastel-offer">Min. 56% Off</div>
          </div>
          <div class="fk-pastel-card" onclick="navigateTo('#/product/7')">
            <div class="fk-pastel-img-wrap">
              <img src="https://images.unsplash.com/photo-1542272604-780c96856592?w=400" alt="Levi's Jeans" />
            </div>
            <div class="fk-pastel-subtitle">New Trend Range</div>
            <div class="fk-pastel-offer">Flat 50% Off</div>
          </div>
          <div class="fk-pastel-card" onclick="navigateTo('#/product/8')">
            <div class="fk-pastel-img-wrap">
              <img src="https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?w=400" alt="Noise Smartwatch" />
            </div>
            <div class="fk-pastel-subtitle">Hand-picked</div>
            <div class="fk-pastel-offer">Under ₹2,499</div>
          </div>
        </div>
      </section>

      <!-- Deals of the Day Horizontal Row -->
      <section class="fk-section-block">
        <div class="fk-section-header">
          <div class="fk-header-title-box">
            <h2 class="fk-section-title">Best Deals on Electronics & Mobiles</h2>
            <span class="fk-timer-pill" id="dealTimer">🕒 18 : 24 : 12 Left</span>
          </div>
          <button class="fk-view-all-btn" onclick="navigateTo('#/search')">VIEW ALL</button>
        </div>
        <div class="fk-products-scroll-row" id="dealsScrollRow"></div>
      </section>

      <!-- Featured 4-Column Grid -->
      <section class="fk-section-block">
        <div class="fk-section-header">
          <h2 class="fk-section-title">Trending Stores & Verified Collections</h2>
        </div>
        <div class="fk-products-grid" id="homeProductsGrid"></div>
      </section>
    </div>
  `;

  renderCategoryStrip();
  initCarousel();
  initCountdownTimer();
  renderDealsRow();

  const grid = document.getElementById('homeProductsGrid');
  if (grid) {
    grid.innerHTML = state.products.map(p => createProductCardHTML(p)).join('');
  }
}

// ==========================================================================
// 2. STANDALONE PRODUCT DETAIL PAGE (PDP) RENDERER
// ==========================================================================
function renderPDPPage(root, productId) {
  const p = state.products.find(item => item.id === productId) || state.products[0];
  state.pdpActiveThumb = 0;

  root.innerHTML = `
    <div class="fk-pdp-container">
      <!-- Left Gallery Sticky -->
      <div class="fk-pdp-gallery-sticky">
        <div class="fk-pdp-gallery-main-row">
          <div class="fk-pdp-thumbs-list">
            ${p.images.map((img, i) => `
              <div class="fk-pdp-thumb-item ${i === 0 ? 'active' : ''}" onclick="switchPdpThumb(${i}, '${img}', this)">
                <img src="${img}" alt="Thumb ${i+1}" />
              </div>
            `).join('')}
          </div>
          <div class="fk-pdp-main-image-wrap">
            <img src="${p.images[0]}" id="pdpMainImg" alt="${p.title}" />
          </div>
        </div>

        <div class="fk-pdp-buttons-row">
          <button class="fk-btn-cart-lg" onclick="addToCartAndGo(${p.id})">
            🛒 ADD TO CART
          </button>
          <button class="fk-btn-buy-lg" onclick="buyNowCheckout(${p.id})">
            ⚡ BUY NOW
          </button>
        </div>
      </div>

      <!-- Right Details Column -->
      <div class="fk-pdp-details-col">
        <div style="font-size:12px; color:#878787;">
          Home > ${p.category} > ${p.subCategory} > <strong>${p.brand}</strong>
        </div>

        <h1 class="fk-pdp-title">${p.title}</h1>

        <div class="fk-card-rating-row">
          <span class="fk-rating-pill" style="font-size:13px; padding:3px 8px;">${p.rating} ★</span>
          <span style="font-size:14px; font-weight:600; color:#878787;">${p.ratingCount} Ratings & ${p.reviewCount} Reviews</span>
          ${p.fassured ? `
            <span class="fk-fassured-badge">
              <svg width="76" height="20" viewBox="0 0 90 24" fill="none">
                <rect width="90" height="24" rx="4" fill="#0056D2"/>
                <path d="M12 6L14.5 11L20 11.8L16 15.7L16.9 21.2L12 18.6L7.1 21.2L8 15.7L4 11.8L9.5 11L12 6Z" fill="#FFE500"/>
                <text x="24" y="16" fill="#FFFFFF" font-size="11" font-weight="900" font-style="italic">Plus F-Assured</text>
              </svg>
            </span>
          ` : ''}
        </div>

        <div style="color:#388E3C; font-weight:700; font-size:14px;">Special Price</div>

        <div class="fk-pdp-price-row">
          <span class="fk-pdp-price-big">₹${p.price.toLocaleString('en-IN')}</span>
          <span class="fk-pdp-price-mrp">₹${p.mrp.toLocaleString('en-IN')}</span>
          <span class="fk-pdp-price-discount">${p.discount}</span>
        </div>

        <!-- Available Offers -->
        <div class="fk-offers-card">
          <div class="fk-offers-title">Available Offers</div>
          <div class="fk-offer-row">
            <span class="fk-offer-icon">🏷️</span>
            <div><strong>Bank Offer:</strong> 5% Cashback on Flipkart Axis Bank Card <span class="fk-offer-tc">T&C</span></div>
          </div>
          <div class="fk-offer-row">
            <span class="fk-offer-icon">🏷️</span>
            <div><strong>Special Price:</strong> Get extra ₹1,100 off (price inclusive of cashback/coupon) <span class="fk-offer-tc">T&C</span></div>
          </div>
          <div class="fk-offer-row">
            <span class="fk-offer-icon">🏷️</span>
            <div><strong>Partner Offer:</strong> Sign up for Flipkart Pay Later and get free Times Prime <span class="fk-offer-tc">T&C</span></div>
          </div>
        </div>

        <!-- Variants -->
        ${p.variants ? `
          <div class="fk-variants-box">
            <div class="fk-variant-label">Select Color / Edition:</div>
            <div class="fk-variant-options">
              ${p.variants.map((v, i) => `
                <div class="fk-variant-chip ${i === 0 ? 'active' : ''}" onclick="selectPdpVariant(${i}, this)">
                  ${v.name} (₹${v.price})
                </div>
              `).join('')}
            </div>
          </div>
        ` : ''}

        <!-- Pincode Check -->
        <div class="fk-pincode-box">
          <div class="fk-variant-label">Delivery Options:</div>
          <div class="fk-pincode-input-row">
            <input type="text" class="fk-pincode-input" id="pdpPinInput" value="560103" />
            <button class="fk-pincode-check-btn" onclick="checkPdpPin()">Check</button>
          </div>
          <div class="fk-pincode-result" id="pdpPinResult">✓ Delivery by Tomorrow, 11 PM | Free Delivery</div>
        </div>

        <!-- Specifications Table -->
        <div class="fk-specs-section">
          <h3 class="fk-specs-title">Product Specifications</h3>
          <table class="fk-specs-table">
            <tbody>
              ${Object.entries(p.specs || {}).map(([k, v]) => `
                <tr>
                  <td class="fk-specs-key">${k}</td>
                  <td class="fk-specs-val">${v}</td>
                </tr>
              `).join('')}
            </tbody>
          </table>
        </div>

        <!-- Customer Reviews Breakdown -->
        <div class="fk-reviews-section">
          <h3 class="fk-specs-title">Ratings & Reviews</h3>
          <div class="fk-rating-breakdown-box">
            <div class="fk-overall-rating">
              <span class="fk-big-score">${p.rating} ★</span>
              <span style="font-size:12px; color:#878787; text-align:center;">${p.ratingCount} Ratings &<br/>${p.reviewCount} Reviews</span>
            </div>
            <div class="fk-star-bars">
              <div class="fk-star-bar-row"><span>5★</span><div class="fk-progress-track"><div class="fk-progress-fill" style="width: 76%;"></div></div><span>12,046</span></div>
              <div class="fk-star-bar-row"><span>4★</span><div class="fk-progress-track"><div class="fk-progress-fill" style="width: 16%;"></div></div><span>2,536</span></div>
              <div class="fk-star-bar-row"><span>3★</span><div class="fk-progress-track"><div class="fk-progress-fill" style="width: 5%;"></div></div><span>792</span></div>
              <div class="fk-star-bar-row"><span>2★</span><div class="fk-progress-track"><div class="fk-progress-fill" style="width: 2%;"></div></div><span>316</span></div>
              <div class="fk-star-bar-row"><span>1★</span><div class="fk-progress-track"><div class="fk-progress-fill" style="width: 1%;"></div></div><span>160</span></div>
            </div>
          </div>

          <!-- Reviews Cards -->
          ${(p.reviews || []).map(r => `
            <div class="fk-review-card">
              <div class="fk-review-top">
                <span class="fk-rating-pill">${r.rating} ★</span>
                <span class="fk-review-headline">${r.title}</span>
              </div>
              <div class="fk-review-body">${r.text}</div>
              <div class="fk-review-meta">
                <span>${r.author}</span>
                <span>• ${r.date}</span>
                <span class="fk-verified-buyer-tag">✓ Certified Buyer</span>
              </div>
            </div>
          `).join('')}
        </div>
      </div>
    </div>
  `;
}

function switchPdpThumb(idx, url, elem) {
  document.querySelectorAll('.fk-pdp-thumb-item').forEach(t => t.classList.remove('active'));
  elem?.classList.add('active');
  const img = document.getElementById('pdpMainImg');
  if (img) img.src = url;
}

function selectPdpVariant(idx, elem) {
  document.querySelectorAll('.fk-variant-chip').forEach(c => c.classList.remove('active'));
  elem?.classList.add('active');
}

function checkPdpPin() {
  const pin = document.getElementById('pdpPinInput')?.value.trim();
  const res = document.getElementById('pdpPinResult');
  if (res) {
    if (pin.length === 6) {
      res.innerText = `✓ Delivery by Tomorrow, 11 PM to ${pin} | Free Delivery`;
      res.style.color = '#388E3C';
    } else {
      res.innerText = `⚠️ Enter valid 6-digit Pincode`;
      res.style.color = '#E41D2D';
    }
  }
}

function addToCartAndGo(productId) {
  const p = state.products.find(item => item.id === productId);
  if (!p) return;
  const existing = state.cart.find(c => c.productId === productId);
  if (existing) {
    existing.quantity++;
  } else {
    state.cart.push({
      productId: p.id,
      title: p.title,
      image: p.images[0],
      price: p.price,
      mrp: p.mrp,
      seller: 'Celvas Official Retail',
      quantity: 1
    });
  }
  saveCartState();
  showToast('Item added to Cart 🛒');
  navigateTo('#/cart');
}

function buyNowCheckout(productId) {
  addToCartAndGo(productId);
  navigateTo('#/checkout');
}

// ==========================================================================
// 3. PRODUCT LISTING & SEARCH PAGE (PLP) WITH COMPLETE WORKING FILTER ENGINE
// ==========================================================================
const plpFilterState = {
  query: '',
  category: 'all',
  selectedBrands: [],
  minPrice: 0,
  maxPrice: 100000,
  minRating: 0,
  minDiscount: 0,
  fassuredOnly: false,
  sortBy: 'relevance'
};

function getPlpAvailableBrands() {
  const counts = {};
  state.products.forEach(p => {
    if (p.brand) {
      counts[p.brand] = (counts[p.brand] || 0) + 1;
    }
  });
  return Object.keys(counts).map(b => ({ brand: b, count: counts[b] }));
}

function getFilteredPlpProducts() {
  return state.products.filter(p => {
    // 1. Search Query
    if (plpFilterState.query) {
      const q = plpFilterState.query.toLowerCase().trim();
      const titleMatch = p.title && p.title.toLowerCase().includes(q);
      const brandMatch = p.brand && p.brand.toLowerCase().includes(q);
      const catMatch = p.category && p.category.toLowerCase().includes(q);
      const subMatch = p.subCategory && p.subCategory.toLowerCase().includes(q);
      if (!titleMatch && !brandMatch && !catMatch && !subMatch) return false;
    }

    // 2. Category
    if (plpFilterState.category && plpFilterState.category !== 'all') {
      if (!p.category || p.category.toLowerCase() !== plpFilterState.category.toLowerCase()) return false;
    }

    // 3. Price
    if (p.price < plpFilterState.minPrice) return false;
    if (plpFilterState.maxPrice > 0 && p.price > plpFilterState.maxPrice) return false;

    // 4. Brands
    if (plpFilterState.selectedBrands.length > 0) {
      if (!plpFilterState.selectedBrands.includes(p.brand)) return false;
    }

    // 5. Customer Rating
    if (plpFilterState.minRating > 0) {
      if (p.rating < plpFilterState.minRating) return false;
    }

    // 6. Discount
    if (plpFilterState.minDiscount > 0) {
      const disc = parseInt(p.discount) || Math.round((1 - p.price / p.mrp) * 100);
      if (disc < plpFilterState.minDiscount) return false;
    }

    // 7. Plus F-Assured
    if (plpFilterState.fassuredOnly && !p.fassured) return false;

    return true;
  }).sort((a, b) => {
    if (plpFilterState.sortBy === 'price_low') return a.price - b.price;
    if (plpFilterState.sortBy === 'price_high') return b.price - a.price;
    if (plpFilterState.sortBy === 'popularity') return (b.ratingCount ? parseInt(String(b.ratingCount).replace(/,/g, '')) : 0) - (a.ratingCount ? parseInt(String(a.ratingCount).replace(/,/g, '')) : 0);
    return b.rating - a.rating; // relevance
  });
}

function renderPLPPage(root, query) {
  if (query !== undefined && query !== null) {
    plpFilterState.query = query;
  }
  const filtered = getFilteredPlpProducts();
  const brands = getPlpAvailableBrands();

  root.innerHTML = `
    <div class="fk-listing-layout">
      <!-- Left Filter Sidebar -->
      <aside class="fk-filter-sidebar">
        <div class="fk-filter-header">
          <h3 class="fk-filter-title">Filters</h3>
          <span class="fk-filter-clear" onclick="clearAllPlpFilters()">CLEAR ALL</span>
        </div>

        <!-- Active Filter Chips -->
        <div class="fk-active-filters-box" id="plpActiveChips" style="${hasActiveFilters() ? 'display:flex;' : 'display:none;'}">
          ${renderActiveFilterChipsHTML()}
        </div>

        <!-- Price Filter -->
        <div class="fk-filter-section">
          <div class="fk-filter-sec-title">PRICE</div>
          <div class="fk-price-range-row">
            <select class="fk-price-select" id="plpMinPriceSelect" onchange="updatePlpPrice()">
              <option value="0" ${plpFilterState.minPrice === 0 ? 'selected' : ''}>Min</option>
              <option value="300" ${plpFilterState.minPrice === 300 ? 'selected' : ''}>₹300</option>
              <option value="500" ${plpFilterState.minPrice === 500 ? 'selected' : ''}>₹500</option>
              <option value="1000" ${plpFilterState.minPrice === 1000 ? 'selected' : ''}>₹1,000</option>
              <option value="2000" ${plpFilterState.minPrice === 2000 ? 'selected' : ''}>₹2,000</option>
              <option value="5000" ${plpFilterState.minPrice === 5000 ? 'selected' : ''}>₹5,000</option>
              <option value="10000" ${plpFilterState.minPrice === 10000 ? 'selected' : ''}>₹10,000</option>
            </select>
            <span class="fk-price-sep">to</span>
            <select class="fk-price-select" id="plpMaxPriceSelect" onchange="updatePlpPrice()">
              <option value="1000" ${plpFilterState.maxPrice === 1000 ? 'selected' : ''}>₹1,000</option>
              <option value="2000" ${plpFilterState.maxPrice === 2000 ? 'selected' : ''}>₹2,000</option>
              <option value="5000" ${plpFilterState.maxPrice === 5000 ? 'selected' : ''}>₹5,000</option>
              <option value="10000" ${plpFilterState.maxPrice === 10000 ? 'selected' : ''}>₹10,000</option>
              <option value="35000" ${plpFilterState.maxPrice === 35000 ? 'selected' : ''}>₹35,000</option>
              <option value="100000" ${plpFilterState.maxPrice >= 100000 ? 'selected' : ''}>₹1,00,000+</option>
            </select>
          </div>
        </div>

        <!-- F-Assured Filter -->
        <div class="fk-filter-section">
          <label class="fk-filter-item" style="cursor:pointer;">
            <input type="checkbox" id="plpFassuredCheck" ${plpFilterState.fassuredOnly ? 'checked' : ''} onchange="togglePlpFassured(this.checked)" />
            <svg width="76" height="20" viewBox="0 0 90 24" fill="none">
              <rect width="90" height="24" rx="4" fill="#0056D2"/>
              <path d="M12 6L14.5 11L20 11.8L16 15.7L16.9 21.2L12 18.6L7.1 21.2L8 15.7L4 11.8L9.5 11L12 6Z" fill="#FFE500"/>
              <text x="24" y="16" fill="#FFFFFF" font-size="11" font-weight="900" font-style="italic">Plus F-Assured</text>
            </svg>
          </label>
        </div>

        <!-- Brand Filter -->
        <div class="fk-filter-section">
          <div class="fk-filter-sec-title">BRAND</div>
          ${brands.map(b => `
            <label class="fk-filter-item">
              <input type="checkbox" class="plp-brand-checkbox" value="${b.brand}" ${plpFilterState.selectedBrands.includes(b.brand) ? 'checked' : ''} onchange="togglePlpBrand('${b.brand}', this.checked)" />
              <span>${b.brand}</span>
              <span class="fk-filter-count">(${b.count})</span>
            </label>
          `).join('')}
        </div>

        <!-- Customer Ratings Filter -->
        <div class="fk-filter-section">
          <div class="fk-filter-sec-title">CUSTOMER RATINGS</div>
          <label class="fk-filter-item">
            <input type="radio" name="plp_rating" ${plpFilterState.minRating === 4 ? 'checked' : ''} onchange="setPlpMinRating(4)" />
            <span class="fk-rating-pill" style="font-size:11px;">4★ & above</span>
          </label>
          <label class="fk-filter-item">
            <input type="radio" name="plp_rating" ${plpFilterState.minRating === 3 ? 'checked' : ''} onchange="setPlpMinRating(3)" />
            <span class="fk-rating-pill" style="font-size:11px;">3★ & above</span>
          </label>
          <label class="fk-filter-item">
            <input type="radio" name="plp_rating" ${plpFilterState.minRating === 0 ? 'checked' : ''} onchange="setPlpMinRating(0)" />
            <span>All Ratings</span>
          </label>
        </div>

        <!-- Discount Filter -->
        <div class="fk-filter-section" style="border-bottom:none;">
          <div class="fk-filter-sec-title">DISCOUNT</div>
          <label class="fk-filter-item">
            <input type="radio" name="plp_disc" ${plpFilterState.minDiscount === 70 ? 'checked' : ''} onchange="setPlpMinDiscount(70)" />
            <span>70% or more</span>
          </label>
          <label class="fk-filter-item">
            <input type="radio" name="plp_disc" ${plpFilterState.minDiscount === 50 ? 'checked' : ''} onchange="setPlpMinDiscount(50)" />
            <span>50% or more</span>
          </label>
          <label class="fk-filter-item">
            <input type="radio" name="plp_disc" ${plpFilterState.minDiscount === 30 ? 'checked' : ''} onchange="setPlpMinDiscount(30)" />
            <span>30% or more</span>
          </label>
          <label class="fk-filter-item">
            <input type="radio" name="plp_disc" ${plpFilterState.minDiscount === 0 ? 'checked' : ''} onchange="setPlpMinDiscount(0)" />
            <span>All Discounts</span>
          </label>
        </div>
      </aside>

      <!-- Right Main Content -->
      <div class="fk-listing-content">
        <!-- Sort Bar -->
        <div class="fk-sort-bar">
          <span class="fk-sort-label">Sort By</span>
          <span class="fk-sort-option ${plpFilterState.sortBy === 'relevance' ? 'active' : ''}" onclick="sortPlp('relevance', this)">Relevance</span>
          <span class="fk-sort-option ${plpFilterState.sortBy === 'popularity' ? 'active' : ''}" onclick="sortPlp('popularity', this)">Popularity</span>
          <span class="fk-sort-option ${plpFilterState.sortBy === 'price_low' ? 'active' : ''}" onclick="sortPlp('price_low', this)">Price -- Low to High</span>
          <span class="fk-sort-option ${plpFilterState.sortBy === 'price_high' ? 'active' : ''}" onclick="sortPlp('price_high', this)">Price -- High to Low</span>
          <span class="fk-results-count" id="plpResultsCount">Showing 1 – ${filtered.length} of ${filtered.length} results</span>
        </div>

        <!-- Product Cards Grid -->
        <div id="plpGrid">
          ${renderPlpGridHTML(filtered)}
        </div>
      </div>
    </div>
  `;
}

function renderPlpGridHTML(items) {
  if (items.length === 0) {
    return `
      <div class="fk-no-results-box">
        <div style="font-size:56px; margin-bottom:12px;">🔍</div>
        <h3 style="font-size:20px; font-weight:700; margin-bottom:8px;">Sorry, no products found!</h3>
        <p style="color:#777; margin-bottom:20px;">Try relaxing your filters or search keywords.</p>
        <button class="fk-view-all-btn" style="padding:10px 24px;" onclick="clearAllPlpFilters()">Clear All Filters</button>
      </div>
    `;
  }

  return `
    <div class="fk-products-grid">
      ${items.map(p => createProductCardHTML(p)).join('')}
    </div>
  `;
}

function hasActiveFilters() {
  return plpFilterState.selectedBrands.length > 0 ||
         plpFilterState.minPrice > 0 ||
         plpFilterState.maxPrice < 100000 ||
         plpFilterState.minRating > 0 ||
         plpFilterState.minDiscount > 0 ||
         plpFilterState.fassuredOnly;
}

function renderActiveFilterChipsHTML() {
  const chips = [];
  if (plpFilterState.fassuredOnly) {
    chips.push(`<span class="fk-filter-chip" onclick="togglePlpFassured(false)">Plus F-Assured <span class="chip-x">✕</span></span>`);
  }
  plpFilterState.selectedBrands.forEach(b => {
    chips.push(`<span class="fk-filter-chip" onclick="togglePlpBrand('${b}', false)">${b} <span class="chip-x">✕</span></span>`);
  });
  if (plpFilterState.minPrice > 0 || plpFilterState.maxPrice < 100000) {
    chips.push(`<span class="fk-filter-chip" onclick="resetPlpPrice()">₹${plpFilterState.minPrice} - ₹${plpFilterState.maxPrice < 100000 ? plpFilterState.maxPrice : 'Max'} <span class="chip-x">✕</span></span>`);
  }
  if (plpFilterState.minRating > 0) {
    chips.push(`<span class="fk-filter-chip" onclick="setPlpMinRating(0)">${plpFilterState.minRating}★ & above <span class="chip-x">✕</span></span>`);
  }
  if (plpFilterState.minDiscount > 0) {
    chips.push(`<span class="fk-filter-chip" onclick="setPlpMinDiscount(0)">Min ${plpFilterState.minDiscount}% Off <span class="chip-x">✕</span></span>`);
  }
  return chips.join('');
}

function applyPlpFilters() {
  const filtered = getFilteredPlpProducts();
  const grid = document.getElementById('plpGrid');
  const countEl = document.getElementById('plpResultsCount');
  const chipsEl = document.getElementById('plpActiveChips');

  if (grid) grid.innerHTML = renderPlpGridHTML(filtered);
  if (countEl) countEl.innerText = `Showing 1 – ${filtered.length} of ${filtered.length} results`;
  if (chipsEl) {
    chipsEl.innerHTML = renderActiveFilterChipsHTML();
    chipsEl.style.display = hasActiveFilters() ? 'flex' : 'none';
  }
}

function togglePlpBrand(brand, isChecked) {
  if (isChecked) {
    if (!plpFilterState.selectedBrands.includes(brand)) plpFilterState.selectedBrands.push(brand);
  } else {
    plpFilterState.selectedBrands = plpFilterState.selectedBrands.filter(b => b !== brand);
    const cb = document.querySelector(`.plp-brand-checkbox[value="${brand}"]`);
    if (cb) cb.checked = false;
  }
  applyPlpFilters();
}

function togglePlpFassured(isChecked) {
  plpFilterState.fassuredOnly = isChecked;
  const cb = document.getElementById('plpFassuredCheck');
  if (cb) cb.checked = isChecked;
  applyPlpFilters();
}

function updatePlpPrice() {
  const min = parseInt(document.getElementById('plpMinPriceSelect')?.value || '0', 10);
  const max = parseInt(document.getElementById('plpMaxPriceSelect')?.value || '100000', 10);
  plpFilterState.minPrice = min;
  plpFilterState.maxPrice = max;
  applyPlpFilters();
}

function resetPlpPrice() {
  plpFilterState.minPrice = 0;
  plpFilterState.maxPrice = 100000;
  const minSel = document.getElementById('plpMinPriceSelect');
  const maxSel = document.getElementById('plpMaxPriceSelect');
  if (minSel) minSel.value = '0';
  if (maxSel) maxSel.value = '100000';
  applyPlpFilters();
}

function setPlpMinRating(r) {
  plpFilterState.minRating = r;
  applyPlpFilters();
}

function setPlpMinDiscount(d) {
  plpFilterState.minDiscount = d;
  applyPlpFilters();
}

function clearAllPlpFilters() {
  plpFilterState.selectedBrands = [];
  plpFilterState.minPrice = 0;
  plpFilterState.maxPrice = 100000;
  plpFilterState.minRating = 0;
  plpFilterState.minDiscount = 0;
  plpFilterState.fassuredOnly = false;
  plpFilterState.sortBy = 'relevance';
  renderPLPPage(document.getElementById('appRoot'), plpFilterState.query);
  showToast('All filters cleared');
}

function sortPlp(type, elem) {
  plpFilterState.sortBy = type;
  document.querySelectorAll('.fk-sort-option').forEach(el => el.classList.remove('active'));
  elem?.classList.add('active');
  applyPlpFilters();
}

// ==========================================================================
// 4. CATEGORY STORE PAGE (With Filter Sidebar)
// ==========================================================================
function renderCategoryStorePage(root, catName) {
  plpFilterState.category = catName;
  plpFilterState.query = '';
  renderPLPPage(root, '');
}

function switchCartTab(tab) {
  state.activeCartTab = tab;
  renderCartPage(document.getElementById('appRoot'));
}

function renderCartPage(root) {
  const currentTab = state.activeCartTab || 'flipkart';
  const flipkartItems = state.cart.filter(i => i.cartType !== 'grocery');
  const groceryItems = state.cart.filter(i => i.cartType === 'grocery');
  const activeItems = currentTab === 'grocery' ? groceryItems : flipkartItems;

  if (state.cart.length === 0) {
    root.innerHTML = `
      <div class="fk-main-container">
        <div style="background:#fff; border-radius:4px; padding:60px; text-align:center; box-shadow:0 1px 2px rgba(0,0,0,0.08);">
          <div style="font-size:64px; margin-bottom:16px;">🛒</div>
          <h2 style="font-size:22px; font-weight:700; margin-bottom:8px;">Your Flipkart Cart is Empty!</h2>
          <p style="color:#777; margin-bottom:24px;">Explore our catalog, special offers, and add your favorite items to cart.</p>
          <button class="fk-view-all-btn" style="padding:12px 32px; font-size:15px;" onclick="navigateTo('#/')">Shop Now</button>
        </div>
      </div>
    `;
    return;
  }

  let totalMrp = 0;
  let totalPrice = 0;
  activeItems.forEach(item => {
    totalMrp += item.mrp * item.quantity;
    totalPrice += item.price * item.quantity;
  });

  const curAddr = state.addresses.find(a => a.id === state.selectedAddressId) || state.addresses[0];

  root.innerHTML = `
    <div class="fk-cart-page-layout">
      <!-- Left Main Column -->
      <div class="fk-cart-main-col">
        <!-- Flipkart vs Grocery Tabs -->
        <div class="fk-cart-tabs-bar">
          <div class="fk-cart-tab-btn ${currentTab === 'flipkart' ? 'active' : ''}" onclick="switchCartTab('flipkart')">
            <span>Flipkart</span>
            <span class="fk-cart-tab-badge">${flipkartItems.length}</span>
          </div>
          <div class="fk-cart-tab-btn ${currentTab === 'grocery' ? 'active' : ''}" onclick="switchCartTab('grocery')">
            <span>🥦 Grocery</span>
            <span class="fk-cart-tab-badge">${groceryItems.length}</span>
          </div>
        </div>

        <!-- Address Bar (Matching real Flipkart Deliver to: Chennai - 600024) -->
        <div class="fk-cart-address-strip">
          <div>
            <span style="color:#777; font-size:13px;">Deliver to: </span>
            <strong>${curAddr.name}, ${curAddr.pincode} (${curAddr.city})</strong>
            <span style="background:#F0F2F5; font-size:11px; font-weight:700; padding:2px 6px; border-radius:3px; margin-left:6px;">${curAddr.type}</span>
          </div>
          <button style="color:#2874F0; font-weight:700; font-size:13px; border:1px solid #E0E0E0; padding:6px 14px; border-radius:4px; background:#fff;" onclick="navigateTo('#/account/addresses')">
            Change
          </button>
        </div>

        <!-- Items List -->
        <div class="fk-cart-items-wrapper">
          ${activeItems.length === 0 ? `
            <div style="background:#fff; padding:40px; text-align:center;">
              <h3>No items in ${currentTab === 'grocery' ? 'Grocery' : 'Flipkart'} cart.</h3>
              <p style="color:#777; margin-top:8px;">Add items to your cart to see them here.</p>
            </div>
          ` : activeItems.map((item) => {
            const origIdx = state.cart.indexOf(item);
            return `
              <div class="fk-cart-page-item">
                <div class="fk-cart-item-img-box">
                  <img src="${item.image}" alt="${item.title}" />
                </div>
                <div class="fk-cart-item-desc">
                  <h3 style="font-size:15px; font-weight:600; margin-bottom:4px; cursor:pointer;" onclick="navigateTo('#/product/${item.productId}')">
                    ${item.title}
                  </h3>
                  <div style="font-size:12px; color:#878787; margin-bottom:6px;">
                    ${item.variant || 'Standard'} • Seller: ${item.seller}
                  </div>
                  <div class="fk-card-price-row">
                    <span class="fk-current-price" style="font-size:18px;">₹${item.price.toLocaleString('en-IN')}</span>
                    <span class="fk-mrp-price">₹${item.mrp.toLocaleString('en-IN')}</span>
                    <span class="fk-discount-tag">${Math.round((1 - item.price / item.mrp) * 100)}% Off</span>
                    <span style="font-size:12px; color:#388E3C; font-weight:700; margin-left:8px;">2 offers applied</span>
                  </div>
                  <div style="display:flex; align-items:center; gap:20px; margin-top:16px;">
                    <div class="fk-qty-control">
                      <button class="fk-qty-btn" onclick="modifyCartQty(${origIdx}, -1)">-</button>
                      <span class="fk-qty-val">${item.quantity}</span>
                      <button class="fk-qty-btn" onclick="modifyCartQty(${origIdx}, 1)">+</button>
                    </div>
                    <span style="font-weight:700; font-size:13px; cursor:pointer;" onclick="saveForLater(${origIdx})">SAVE FOR LATER</span>
                    <span style="font-weight:700; font-size:13px; color:#E41D2D; cursor:pointer;" onclick="removeCartItemPage(${origIdx})">REMOVE</span>
                    <span style="font-weight:700; font-size:13px; color:#FB641B; cursor:pointer;" onclick="buyThisNow(${origIdx})">⚡ BUY THIS NOW</span>
                  </div>
                </div>
                <div style="font-size:13px; color:#388E3C; font-weight:600;">
                  Delivery by Tomorrow | Free
                </div>
              </div>
            `;
          }).join('')}
        </div>

        <!-- Sticky Place Order Bar -->
        <div class="fk-cart-order-bar">
          <button class="fk-btn-checkout" style="padding:14px 48px; font-size:16px;" onclick="navigateTo('#/checkout')">
            PLACE ORDER
          </button>
        </div>
      </div>

      <!-- Right Price Summary Column -->
      <div class="fk-price-summary-card">
        <div class="fk-summary-header">PRICE DETAILS (${activeItems.length} Items)</div>
        <div class="fk-summary-row">
          <span>Total MRP</span>
          <span>₹${totalMrp.toLocaleString('en-IN')}</span>
        </div>
        <div class="fk-summary-row" style="color:#388E3C;">
          <span>Discount</span>
          <span>- ₹${(totalMrp - totalPrice).toLocaleString('en-IN')}</span>
        </div>
        <div class="fk-summary-row" style="color:#388E3C;">
          <span>Coupons for you</span>
          <span>- ₹50 (FLIPKART50)</span>
        </div>
        <div class="fk-summary-row">
          <span>Delivery Charges</span>
          <span style="color:#388E3C;">FREE</span>
        </div>
        <div class="fk-summary-row">
          <span>Secured Packaging Fee</span>
          <span>₹10</span>
        </div>
        <div class="fk-summary-total">
          <span>Total Amount</span>
          <span>₹${Math.max(0, totalPrice - 50 + (activeItems.length > 0 ? 10 : 0)).toLocaleString('en-IN')}</span>
        </div>
        <div class="fk-savings-highlight">
          🎉 You will save ₹${(totalMrp - totalPrice + 50).toLocaleString('en-IN')} on this order
        </div>

        <div style="margin-top:20px; padding-top:16px; border-top:1px solid #E0E0E0; font-size:12px; color:#878787; display:flex; align-items:center; gap:8px;">
          <span>🛡️</span>
          <span>Safe and Secure Payments. Easy returns. 100% Authentic products.</span>
        </div>
      </div>
    </div>
  `;
}

function buyThisNow(idx) {
  const item = state.cart[idx];
  showToast(`Proceeding to checkout with ${item.title.slice(0, 20)}...`);
  navigateTo('#/checkout');
}

function modifyCartQty(idx, delta) {
  state.cart[idx].quantity += delta;
  if (state.cart[idx].quantity <= 0) {
    state.cart.splice(idx, 1);
  }
  saveCartState();
  renderCartPage(document.getElementById('appRoot'));
}

function removeCartItemPage(idx) {
  state.cart.splice(idx, 1);
  saveCartState();
  showToast('Item removed from cart');
  renderCartPage(document.getElementById('appRoot'));
}

function saveForLater(idx) {
  const item = state.cart.splice(idx, 1)[0];
  saveCartState();
  showToast(`"${item.title.slice(0, 20)}..." saved for later!`);
  renderCartPage(document.getElementById('appRoot'));
}

// ==========================================================================
// 6. CHECKOUT ACCORDION PAGE (`#/checkout`)
// ==========================================================================
function renderCheckoutPage(root) {
  const curAddr = state.addresses.find(a => a.id === state.selectedAddressId) || state.addresses[0];
  let totalPrice = state.cart.reduce((sum, i) => sum + i.price * i.quantity, 0);
  const payable = Math.max(0, totalPrice - 50);

  root.innerHTML = `
    <div class="fk-cart-page-layout">
      <div style="display:flex; flex-direction:column; gap:16px;">
        <!-- Step 1: Login -->
        <div style="background:#fff; border-radius:4px; padding:16px 24px; box-shadow:0 1px 2px rgba(0,0,0,0.08);">
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <div>
              <span style="background:#2874F0; color:#fff; font-weight:700; font-size:12px; padding:2px 8px; border-radius:2px; margin-right:8px;">1</span>
              <strong style="font-size:14px; text-transform:uppercase; color:#878787;">Login</strong>
              <div style="margin-left:28px; font-weight:700; font-size:14px; margin-top:4px;">
                ${state.user.name} <span style="font-weight:400; color:#555;">${state.user.phone}</span>
              </div>
            </div>
            <span style="color:#388E3C; font-weight:700; font-size:14px;">✓ Verified</span>
          </div>
        </div>

        <!-- Step 2: Delivery Address -->
        <div style="background:#fff; border-radius:4px; padding:16px 24px; box-shadow:0 1px 2px rgba(0,0,0,0.08);">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <div>
              <span style="background:#2874F0; color:#fff; font-weight:700; font-size:12px; padding:2px 8px; border-radius:2px; margin-right:8px;">2</span>
              <strong style="font-size:14px; text-transform:uppercase;">Delivery Address</strong>
            </div>
            <button style="color:#2874F0; font-weight:700; font-size:13px;" onclick="navigateTo('#/account/addresses')">Change</button>
          </div>
          <div style="margin-left:28px;">
            <div style="font-weight:700; font-size:14px;">${curAddr.name} <span style="background:#F0F2F5; font-size:11px; padding:2px 6px; border-radius:3px;">${curAddr.type}</span> ${curAddr.phone}</div>
            <div style="color:#555; font-size:13px; margin-top:2px;">${curAddr.address}, ${curAddr.locality}, ${curAddr.city}, ${curAddr.state} - <strong>${curAddr.pincode}</strong></div>
          </div>
        </div>

        <!-- Step 3: Order Summary -->
        <div style="background:#fff; border-radius:4px; padding:16px 24px; box-shadow:0 1px 2px rgba(0,0,0,0.08);">
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <div>
              <span style="background:#2874F0; color:#fff; font-weight:700; font-size:12px; padding:2px 8px; border-radius:2px; margin-right:8px;">3</span>
              <strong style="font-size:14px; text-transform:uppercase;">Order Summary (${state.cart.length} Items)</strong>
            </div>
            <span style="color:#2874F0; font-weight:700; font-size:13px; cursor:pointer;" onclick="navigateTo('#/cart')">Modify</span>
          </div>
        </div>

        <!-- Step 4: Payment Options -->
        <div style="background:#fff; border-radius:4px; padding:20px 24px; box-shadow:0 1px 2px rgba(0,0,0,0.08);">
          <div style="margin-bottom:16px;">
            <span style="background:#2874F0; color:#fff; font-weight:700; font-size:12px; padding:2px 8px; border-radius:2px; margin-right:8px;">4</span>
            <strong style="font-size:14px; text-transform:uppercase;">Payment Options</strong>
          </div>

          <div style="display:flex; flex-direction:column; gap:12px; margin-left:28px;">
            <label style="display:flex; align-items:center; gap:10px; cursor:pointer; padding:10px; border:1px solid #E0E0E0; border-radius:4px;">
              <input type="radio" name="pay_option" value="UPI" checked />
              <div>
                <strong>UPI (Google Pay, PhonePe, Paytm, BHIM)</strong>
                <div style="font-size:12px; color:#777;">Pay directly from your bank account</div>
              </div>
            </label>

            <label style="display:flex; align-items:center; gap:10px; cursor:pointer; padding:10px; border:1px solid #E0E0E0; border-radius:4px;">
              <input type="radio" name="pay_option" value="CARD" />
              <div>
                <strong>Credit / Debit / ATM Card</strong>
                <div style="font-size:12px; color:#777;">Visa, MasterCard, RuPay, Maestro</div>
              </div>
            </label>

            <label style="display:flex; align-items:center; gap:10px; cursor:pointer; padding:10px; border:1px solid #E0E0E0; border-radius:4px;">
              <input type="radio" name="pay_option" value="COD" />
              <div>
                <strong>Cash on Delivery (COD)</strong>
                <div style="font-size:12px; color:#777;">Pay cash at your doorstep</div>
              </div>
            </label>
          </div>

          <div style="margin-top:20px; display:flex; justify-content:flex-end;">
            <button class="fk-btn-checkout" style="padding:14px 48px; font-size:16px;" onclick="confirmOrderPlacement()">
              CONFIRM ORDER & PAY ₹${payable.toLocaleString('en-IN')}
            </button>
          </div>
        </div>
      </div>

      <!-- Right Summary -->
      <div class="fk-price-summary-card">
        <div class="fk-summary-header">PRICE DETAILS</div>
        <div class="fk-summary-row">
          <span>Price (${state.cart.length} items)</span>
          <span>₹${totalPrice.toLocaleString('en-IN')}</span>
        </div>
        <div class="fk-summary-row" style="color:#388E3C;">
          <span>Coupon Discount</span>
          <span>- ₹50</span>
        </div>
        <div class="fk-summary-row">
          <span>Delivery Charges</span>
          <span style="color:#388E3C;">FREE</span>
        </div>
        <div class="fk-summary-total">
          <span>Amount Payable</span>
          <span>₹${payable.toLocaleString('en-IN')}</span>
        </div>
      </div>
    </div>
  `;
}

async function confirmOrderPlacement() {
  const orderId = `OD${Date.now().toString().slice(-12)}`;
  const curAddr = state.addresses.find(a => a.id === state.selectedAddressId) || state.addresses[0];

  // Try API
  try {
    await fetch(`${API_BASE}/checkout/place-order`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        user_id: 1,
        shipping_address_id: curAddr.id,
        payment_method: 'CASH_ON_DELIVERY',
        coupon_code: 'FLIPKART50',
        items: state.cart.map(c => ({ variant_id: c.productId, quantity: c.quantity, price: c.price }))
      })
    });
  } catch (e) {}

  // Add to local orders
  state.cart.forEach(item => {
    state.orders.unshift({
      id: orderId,
      title: item.title,
      image: item.image,
      variant: item.variant || 'Standard',
      price: item.price,
      status: 'CONFIRMED',
      statusText: 'Ordered Today, Arriving Tomorrow',
      date: 'Sep 11, 2026',
      subText: `Shipping to ${curAddr.name}, ${curAddr.pincode}`
    });
  });

  state.cart = [];
  saveCartState();

  alert(`🎉 ORDER PLACED SUCCESSFULLY!\n\nOrder ID: ${orderId}\nDelivery: Tomorrow by 11:00 PM\nAddress: ${curAddr.address}, ${curAddr.pincode}\n\nThank you for shopping on Flipkart!`);
  navigateTo('#/account/orders');
}

// ==========================================================================
// 7. MY ACCOUNT / PROFILE PAGE (`#/account`)
// ==========================================================================
function renderProfilePage(root, activeTab) {
  root.innerHTML = `
    <div class="fk-account-layout">
      <!-- Left Sidebar -->
      <aside class="fk-acc-sidebar">
        <div class="fk-acc-user-card">
          <div class="fk-acc-avatar">👤</div>
          <div>
            <div class="fk-acc-greeting">Hello,</div>
            <div class="fk-acc-name">${state.user.name}</div>
          </div>
        </div>

        <div class="fk-acc-nav-box">
          <div class="fk-acc-nav-header" style="cursor:pointer;" onclick="navigateTo('#/account/orders')">
            <span>📦</span>
            <span>MY ORDERS</span>
          </div>

          <div class="fk-menu-divider"></div>

          <div class="fk-acc-nav-header">
            <span>👤</span>
            <span>ACCOUNT SETTINGS</span>
          </div>
          <a class="fk-acc-nav-item active" onclick="navigateTo('#/account')">Profile Information</a>
          <a class="fk-acc-nav-item" onclick="navigateTo('#/account/addresses')">Manage Addresses</a>
          <a class="fk-acc-nav-item" onclick="navigateTo('#/account/pan')">PAN Card Information</a>

          <div class="fk-menu-divider"></div>

          <div class="fk-acc-nav-header">
            <span>💳</span>
            <span>PAYMENTS</span>
          </div>
          <a class="fk-acc-nav-item" onclick="navigateTo('#/account/gift-cards')">Gift Cards <span style="float:right; color:#388E3C; font-weight:700;">₹0</span></a>
          <a class="fk-acc-nav-item" onclick="navigateTo('#/account/payments')">Saved UPI</a>
          <a class="fk-acc-nav-item" onclick="navigateTo('#/account/payments')">Saved Cards</a>

          <div class="fk-menu-divider"></div>

          <div class="fk-acc-nav-header">
            <span>📂</span>
            <span>MY STUFF</span>
          </div>
          <a class="fk-acc-nav-item" onclick="navigateTo('#/account/coupons')">My Coupons</a>
          <a class="fk-acc-nav-item" onclick="navigateTo('#/account/reviews')">My Reviews & Ratings</a>
          <a class="fk-acc-nav-item" onclick="navigateTo('#/account/notifications')">All Notifications</a>
          <a class="fk-acc-nav-item" onclick="navigateTo('#/wishlist')">My Wishlist</a>
        </div>

        <div class="fk-acc-logout-row" onclick="logoutUser()">
          <span>🚪</span>
          <span>Logout</span>
        </div>
      </aside>

      <!-- Right Profile Content -->
      <div class="fk-acc-content-card">
        <div class="fk-profile-sec-header">
          <h2 class="fk-profile-title">Personal Information</h2>
          <span class="fk-profile-edit-btn" onclick="showToast('You can edit details directly below')">Edit</span>
        </div>

        <div class="fk-profile-inputs-row">
          <input type="text" class="fk-profile-input" id="profFirst" value="${state.user.firstName}" placeholder="First Name" />
          <input type="text" class="fk-profile-input" id="profLast" value="${state.user.lastName}" placeholder="Last Name" />
        </div>

        <div style="font-weight:700; font-size:14px; margin-bottom:8px;">Your Gender</div>
        <div class="fk-profile-gender-row">
          <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
            <input type="radio" name="gender" checked /> Male
          </label>
          <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
            <input type="radio" name="gender" /> Female
          </label>
        </div>

        <div class="fk-profile-sec-header">
          <h2 class="fk-profile-title">Email Address</h2>
          <span class="fk-profile-edit-btn" onclick="showToast('Email verified: ' + state.user.email)">Edit</span>
        </div>
        <div class="fk-profile-inputs-row">
          <input type="email" class="fk-profile-input" value="${state.user.email}" readonly style="background:#F0F2F5; color:#555;" />
        </div>

        <div class="fk-profile-sec-header">
          <h2 class="fk-profile-title">Mobile Number</h2>
          <span class="fk-profile-edit-btn" onclick="showToast('OTP verified: ' + state.user.phone)">Edit</span>
        </div>
        <div class="fk-profile-inputs-row">
          <input type="tel" class="fk-profile-input" value="${state.user.phone}" readonly style="background:#F0F2F5; color:#555;" />
        </div>

        <!-- FAQs -->
        <div style="margin-top:40px; border-top:1px solid #F0F0F0; padding-top:20px;">
          <h3 style="font-size:16px; font-weight:700; margin-bottom:12px;">FAQs</h3>
          <div style="font-size:13px; color:#555; line-height:1.6; margin-bottom:12px;">
            <strong>What happens when I update my email address (or mobile number)?</strong><br/>
            Your login email id changes. You'll receive all account-related communications on your updated credentials.
          </div>
          <div style="font-size:13px; color:#555; line-height:1.6; margin-bottom:12px;">
            <strong>When will my Flipkart account be updated with the new email address?</strong><br/>
            It happens as soon as you confirm the verification code sent to your email and save the changes.
          </div>
          <div style="font-size:13px; color:#555; line-height:1.6;">
            <strong>What happens to my existing Flipkart account when I update my email address?</strong><br/>
            Updating your email address doesn't invalidate your account. Your account remains fully active with all order history and wishlist intact.
          </div>
        </div>

        <!-- Bottom Action -->
        <div style="margin-top:30px; border-top:1px solid #F0F0F0; padding-top:20px; display:flex; justify-content:space-between; align-items:center;">
          <span style="color:#E41D2D; font-size:13px; font-weight:700; cursor:pointer;" onclick="showToast('Account cannot be deactivated with pending orders.')">Deactivate Account</span>
          <button class="fk-view-all-btn" onclick="showToast('Profile information saved!')">SAVE CHANGES</button>
        </div>
      </div>
    </div>
  `;
}

// ==========================================================================
// 8. MY ORDERS PAGE (`#/account/orders`)
// ==========================================================================
function renderOrdersPage(root) {
  root.innerHTML = `
    <div style="max-width:1360px; margin:16px auto 0 auto; padding:0 24px; font-size:12px; color:#878787;">
      <a href="#/" style="color:#2874F0;">Home</a> &gt; <a href="#/account" style="color:#2874F0;">My Account</a> &gt; <span>My Orders</span>
    </div>

    <div class="fk-account-layout" style="margin-top:12px;">
      <!-- Left Filters Sidebar -->
      <aside class="fk-filter-sidebar" style="position:static;">
        <h3 class="fk-filter-title" style="margin-bottom:14px;">Filters</h3>

        <div class="fk-filter-section">
          <div class="fk-filter-sec-title">ORDER STATUS</div>
          <div class="fk-filter-item"><input type="checkbox" id="ord_ontheway" onchange="filterOrders()" /> On the way</div>
          <div class="fk-filter-item"><input type="checkbox" id="ord_deliv" checked onchange="filterOrders()" /> Delivered</div>
          <div class="fk-filter-item"><input type="checkbox" id="ord_canc" checked onchange="filterOrders()" /> Cancelled</div>
          <div class="fk-filter-item"><input type="checkbox" id="ord_ret" onchange="filterOrders()" /> Returned</div>
        </div>

        <div class="fk-filter-section" style="border-bottom:none;">
          <div class="fk-filter-sec-title">ORDER TIME</div>
          <div class="fk-filter-item"><input type="checkbox" checked /> Last 30 days</div>
          <div class="fk-filter-item"><input type="checkbox" checked /> 2026</div>
          <div class="fk-filter-item"><input type="checkbox" /> 2025</div>
          <div class="fk-filter-item"><input type="checkbox" /> Older</div>
        </div>
      </aside>

      <!-- Right Orders List -->
      <div>
        <div class="fk-orders-search-bar">
          <input type="text" class="fk-orders-search-input" id="orderSearchInput" placeholder="Search your orders here" />
          <button class="fk-btn-search-orders" onclick="searchOrdersList()">
            🔍 Search Orders
          </button>
        </div>

        <div id="ordersListContainer">
          ${renderOrdersCards(state.orders)}
        </div>
      </div>
    </div>
  `;
}

function renderOrdersCards(orders) {
  if (orders.length === 0) {
    return `
      <div style="background:#fff; padding:60px 20px; text-align:center; border-radius:4px;">
        <div style="font-size:48px; margin-bottom:12px;">📦</div>
        <h3>No Orders Found</h3>
      </div>
    `;
  }

  return orders.map(o => `
    <div class="fk-order-card" onclick="navigateTo('#/account/order/${o.id}')">
      <img src="${o.image}" style="width:70px; height:70px; object-fit:contain;" alt="${o.title}" />
      <div>
        <h4 style="font-size:14px; font-weight:600; margin-bottom:4px;">${o.title}</h4>
        <div style="font-size:12px; color:#878787;">${o.variant}</div>
        <div style="font-size:11px; color:#2874F0; margin-top:4px;">Order ID: ${o.id}</div>
      </div>
      <div style="font-size:15px; font-weight:700;">
        ${o.price === 0 ? 'FREE' : '₹' + o.price.toLocaleString('en-IN')}
      </div>
      <div class="fk-order-status-col">
        <div class="fk-order-status-dot ${o.status === 'CANCELLED' ? 'fk-status-red' : 'fk-status-green'}">
          ● ${o.statusText}
        </div>
        <div style="font-size:12px; color:#777;">${o.subText}</div>
        ${o.status !== 'CANCELLED' ? `
          <div class="fk-rate-review-link" onclick="event.stopPropagation(); openReviewModal('${o.title.replace(/'/g, "\\'")}')">
            ★ Rate & Review Product
          </div>
        ` : ''}
      </div>
    </div>
  `).join('');
}

function filterOrders() {
  const showDeliv = document.getElementById('ord_deliv')?.checked;
  const showCanc = document.getElementById('ord_canc')?.checked;
  const showOntheway = document.getElementById('ord_ontheway')?.checked;
  const showRet = document.getElementById('ord_ret')?.checked;

  const anyChecked = showDeliv || showCanc || showOntheway || showRet;

  const filtered = state.orders.filter(o => {
    if (!anyChecked) return true;
    if (o.status === 'DELIVERED') return showDeliv;
    if (o.status === 'CANCELLED') return showCanc;
    if (o.status === 'ON_THE_WAY' || o.status === 'CONFIRMED') return showOntheway;
    if (o.status === 'RETURNED') return showRet;
    return false;
  });

  const q = document.getElementById('orderSearchInput')?.value.trim().toLowerCase();
  const finalFiltered = q ? filtered.filter(o => o.title.toLowerCase().includes(q) || o.id.toLowerCase().includes(q)) : filtered;

  const container = document.getElementById('ordersListContainer');
  if (container) container.innerHTML = renderOrdersCards(finalFiltered);
}

function searchOrdersList() {
  filterOrders();
}

// ==========================================================================
// 9. MANAGE ADDRESSES PAGE (`#/account/addresses`)
// ==========================================================================
function renderAddressesPage(root) {
  root.innerHTML = `
    <div class="fk-account-layout">
      <!-- Left Sidebar -->
      <aside class="fk-acc-sidebar">
        <div class="fk-acc-user-card">
          <div class="fk-acc-avatar">👤</div>
          <div>
            <div class="fk-acc-greeting">Hello,</div>
            <div class="fk-acc-name">${state.user.name}</div>
          </div>
        </div>

        <div class="fk-acc-nav-box">
          <a class="fk-acc-nav-item" onclick="navigateTo('#/account')">Profile Information</a>
          <a class="fk-acc-nav-item active" onclick="navigateTo('#/account/addresses')">Manage Addresses</a>
          <a class="fk-acc-nav-item" onclick="navigateTo('#/account/orders')">My Orders</a>
          <a class="fk-acc-nav-item" onclick="navigateTo('#/wishlist')">My Wishlist</a>
        </div>
      </aside>

      <!-- Right Addresses Card -->
      <div class="fk-acc-content-card">
        <div class="fk-profile-sec-header" style="justify-content:space-between;">
          <h2 class="fk-profile-title">Manage Addresses</h2>
          <button class="fk-view-all-btn" style="padding:8px 16px;" onclick="addNewAddressPrompt()">+ ADD A NEW ADDRESS</button>
        </div>

        <div style="display:flex; flex-direction:column; gap:16px;">
          ${state.addresses.map((addr, idx) => `
            <div style="border:1px solid ${addr.id === state.selectedAddressId ? '#2874F0' : '#E0E0E0'}; border-radius:4px; padding:18px; position:relative; background:${addr.id === state.selectedAddressId ? '#F0F5FF' : '#fff'};">
              <div style="display:flex; align-items:center; gap:12px; margin-bottom:6px;">
                <span style="background:#F0F2F5; font-size:11px; font-weight:700; padding:2px 6px; border-radius:3px;">${addr.type}</span>
                <strong style="font-size:15px;">${addr.name}</strong>
                <span style="font-weight:700; font-size:14px;">${addr.phone}</span>
                ${addr.id === state.selectedAddressId ? '<span style="color:#2874F0; font-size:12px; font-weight:700; margin-left:auto;">✓ DEFAULT DELIVERY ADDRESS</span>' : `
                  <button style="color:#2874F0; font-size:12px; font-weight:700; margin-left:auto;" onclick="setDefaultAddress(${addr.id})">SET AS DEFAULT</button>
                `}
              </div>
              <div style="font-size:14px; color:#444;">
                ${addr.address}, ${addr.locality}, ${addr.city}, ${addr.state} - <strong>${addr.pincode}</strong>
              </div>
            </div>
          `).join('')}
        </div>
      </div>
    </div>
  `;
}

function setDefaultAddress(id) {
  state.selectedAddressId = id;
  showToast('Default delivery address updated!');
  renderAddressesPage(document.getElementById('appRoot'));
}

function addNewAddressPrompt() {
  const name = prompt('Enter Full Name:', state.user.name);
  const phone = prompt('Enter 10-digit Phone:', '6382379565');
  const pincode = prompt('Enter Pincode:', '560103');
  const addr = prompt('Enter House/Flat & Street Address:', 'Flat 101, Lakeview Horizon');
  if (name && pincode && addr) {
    state.addresses.push({
      id: Date.now(),
      name,
      phone: phone || '6382379565',
      pincode,
      locality: 'Outer Ring Road',
      address: addr,
      city: 'Bengaluru',
      state: 'Karnataka',
      type: 'HOME',
      isDefault: false
    });
    showToast('New Address Added!');
    renderAddressesPage(document.getElementById('appRoot'));
  }
}

// ==========================================================================
// 10. MY WISHLIST PAGE (`#/wishlist`)
// ==========================================================================
function renderWishlistPage(root) {
  root.innerHTML = `
    <div class="fk-account-layout">
      <!-- Left Sidebar -->
      <aside class="fk-acc-sidebar">
        <div class="fk-acc-user-card">
          <div class="fk-acc-avatar">👤</div>
          <div>
            <div class="fk-acc-greeting">Hello,</div>
            <div class="fk-acc-name">${state.user.name}</div>
          </div>
        </div>

        <div class="fk-acc-nav-box">
          <a class="fk-acc-nav-item" onclick="navigateTo('#/account')">Profile Information</a>
          <a class="fk-acc-nav-item" onclick="navigateTo('#/account/addresses')">Manage Addresses</a>
          <a class="fk-acc-nav-item" onclick="navigateTo('#/account/orders')">My Orders</a>
          <a class="fk-acc-nav-item active" onclick="navigateTo('#/wishlist')">My Wishlist</a>
        </div>
      </aside>

      <!-- Right Wishlist Items -->
      <div class="fk-acc-content-card">
        <h2 class="fk-profile-title" style="margin-bottom:20px;">My Wishlist (${state.wishlist.length})</h2>

        ${state.wishlist.length === 0 ? `
          <div style="text-align:center; padding:60px;">
            <div style="font-size:48px; margin-bottom:12px;">❤️</div>
            <h3>Your Wishlist is Empty</h3>
            <p style="color:#777; margin-bottom:20px;">Save items that you like in your wishlist to review and buy later.</p>
            <button class="fk-view-all-btn" onclick="navigateTo('#/')">Explore Products</button>
          </div>
        ` : `
          <div style="display:flex; flex-direction:column;">
            ${state.wishlist.map((item, idx) => `
              <div class="fk-wishlist-item-row">
                <img src="${item.image}" style="width:110px; height:110px; object-fit:contain;" alt="${item.title}" />
                <div style="flex:1;">
                  <h3 style="font-size:15px; font-weight:600; margin-bottom:6px; cursor:pointer;" onclick="navigateTo('#/product/${item.id}')">
                    ${item.title}
                  </h3>
                  ${item.fassured ? `
                    <div style="margin-bottom:6px;">
                      <svg width="68" height="18" viewBox="0 0 90 24" fill="none">
                        <rect width="90" height="24" rx="4" fill="#0056D2"/>
                        <path d="M12 6L14.5 11L20 11.8L16 15.7L16.9 21.2L12 18.6L7.1 21.2L8 15.7L4 11.8L9.5 11L12 6Z" fill="#FFE500"/>
                        <text x="24" y="16" fill="#FFFFFF" font-size="11" font-weight="900" font-style="italic">Plus F-Assured</text>
                      </svg>
                    </div>
                  ` : ''}
                  <div class="fk-card-price-row">
                    <span class="fk-current-price" style="font-size:18px;">₹${item.price.toLocaleString('en-IN')}</span>
                    <span class="fk-mrp-price">₹${item.mrp.toLocaleString('en-IN')}</span>
                    <span class="fk-discount-tag">${item.discount}</span>
                  </div>
                  ${!item.inStock ? `
                    <div style="color:#E41D2D; font-size:13px; font-weight:700; margin-top:4px;">Currently unavailable</div>
                  ` : `
                    <button class="fk-card-quick-add" style="width:160px; margin-top:8px;" onclick="addToCartAndGo(${item.id})">
                      🛒 Move to Cart
                    </button>
                  `}
                </div>
                <div class="fk-wishlist-trash" onclick="removeWishlistItem(${idx})">🗑️</div>
              </div>
            `).join('')}
          </div>
        `}
      </div>
    </div>
  `;
}

function removeWishlistItem(idx) {
  state.wishlist.splice(idx, 1);
  showToast('Item removed from wishlist');
  renderWishlistPage(document.getElementById('appRoot'));
}

// ==========================================================================
// 11. AUTHENTICATION (LOGIN & REGISTER)
// ==========================================================================
function renderLoginPage(root) {
  root.innerHTML = `
    <div class="fk-auth-page-wrap">
      <div class="fk-auth-card">
        <div class="fk-auth-left-banner">
          <div>
            <h2>Login</h2>
            <p>Get access to your Orders, Wishlist and Recommendations</p>
          </div>
          <div style="font-size:48px;">🛍️</div>
        </div>

        <div class="fk-auth-right-form">
          <div>
            <h3 style="font-size:18px; font-weight:700; margin-bottom:4px;">Log in for the best experience</h3>
            <p style="font-size:13px; color:#777; margin-bottom:24px;">Enter your phone number or Email to continue</p>

            <div class="fk-auth-input-box">
              <input type="text" class="fk-auth-input" id="loginInput" placeholder="Enter Email / Mobile Number" value="nagarajanewlife@gmail.com" />
              <input type="password" class="fk-auth-input" id="passInput" placeholder="Enter Password" value="Password@123" />
            </div>

            <button class="fk-btn-continue" style="width:100%; border:none; cursor:pointer;" onclick="submitLogin()">
              Continue
            </button>

            <div style="font-size:12px; color:#878787; text-align:center; margin-top:16px;">
              By continuing, you agree to Flipkart's <a href="#" style="color:#2874F0;">Terms of Use</a> and <a href="#" style="color:#2874F0;">Privacy Policy</a>.
            </div>
          </div>

          <button class="fk-btn-request-otp" onclick="submitLogin()">
            Request OTP
          </button>
        </div>
      </div>
    </div>
  `;
}

function submitLogin() {
  state.user.isLoggedIn = true;
  updateHeaderUserUI();
  showToast('Welcome back, Murugan! Logged in successfully.');
  navigateTo('#/account');
}

// ==========================================================================
// 12. SELLER HUB (`#/seller`)
// ==========================================================================
function renderSellerPage(root) {
  root.innerHTML = `
    <div class="fk-main-container">
      <div style="background:#172337; color:#fff; padding:28px 32px; border-radius:4px; display:flex; justify-content:space-between; align-items:center;">
        <div>
          <h1 style="font-size:24px; font-weight:800;">Flipkart Seller Hub</h1>
          <p style="opacity:0.8; font-size:13px;">Merchant Portal • Celvas Official Retail • GSTIN: 29AABCC1234F1Z5</p>
        </div>
        <button class="fk-view-all-btn" onclick="addNewProductSeller()">+ Add New Product</button>
      </div>

      <!-- Seller KPIs -->
      <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:16px; margin:20px 0;">
        <div style="background:#fff; padding:20px; border-radius:4px; box-shadow:0 1px 2px rgba(0,0,0,0.08);">
          <div style="color:#777; font-size:12px; font-weight:700;">TOTAL REVENUE (30D)</div>
          <div style="font-size:28px; font-weight:900; color:#2874F0; margin-top:4px;">₹4,28,950</div>
        </div>
        <div style="background:#fff; padding:20px; border-radius:4px; box-shadow:0 1px 2px rgba(0,0,0,0.08);">
          <div style="color:#777; font-size:12px; font-weight:700;">ACTIVE ORDERS</div>
          <div style="font-size:28px; font-weight:900; color:#388E3C; margin-top:4px;">18 Orders</div>
        </div>
        <div style="background:#fff; padding:20px; border-radius:4px; box-shadow:0 1px 2px rgba(0,0,0,0.08);">
          <div style="color:#777; font-size:12px; font-weight:700;">ACTIVE CATALOG SKUS</div>
          <div style="font-size:28px; font-weight:900; color:#212121; margin-top:4px;">14 SKUs</div>
        </div>
        <div style="background:#fff; padding:20px; border-radius:4px; box-shadow:0 1px 2px rgba(0,0,0,0.08);">
          <div style="color:#777; font-size:12px; font-weight:700;">SELLER QUALITY SCORE</div>
          <div style="font-size:28px; font-weight:900; color:#FF9F00; margin-top:4px;">4.8 ★</div>
        </div>
      </div>

      <!-- Inventory Table -->
      <div class="fk-section-block">
        <h2 class="fk-section-title" style="margin-bottom:16px;">Product Catalog & Live Inventory</h2>
        <table style="width:100%; border-collapse:collapse; font-size:14px;">
          <thead>
            <tr style="background:#F0F5FF; text-align:left;">
              <th style="padding:12px;">Image</th>
              <th style="padding:12px;">Product Title</th>
              <th style="padding:12px;">SKU</th>
              <th style="padding:12px;">Selling Price</th>
              <th style="padding:12px;">Live Stock</th>
              <th style="padding:12px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            ${state.products.map(p => `
              <tr style="border-bottom:1px solid #E0E0E0;">
                <td style="padding:12px;"><img src="${p.images[0]}" style="width:40px; height:40px; object-fit:contain;" /></td>
                <td style="padding:12px; font-weight:600;">${p.title}</td>
                <td style="padding:12px; font-size:12px; color:#777;">CEL-${p.id}00</td>
                <td style="padding:12px; font-weight:700;">₹${p.price.toLocaleString('en-IN')}</td>
                <td style="padding:12px; color:#388E3C; font-weight:700;">45 units</td>
                <td style="padding:12px;">
                  <button style="color:#2874F0; font-weight:700; font-size:13px;" onclick="showToast('Adjusted stock for ${p.title.slice(0, 20)}...')">Edit Stock</button>
                </td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      </div>
    </div>
  `;
}

function addNewProductSeller() {
  const t = prompt('Enter Product Title:');
  const pr = prompt('Enter Price (₹):', '499');
  if (t && pr) {
    state.products.unshift({
      id: Date.now(),
      title: t,
      brand: 'Celvas',
      category: 'Mobiles',
      subCategory: 'Accessories',
      price: parseInt(pr, 10),
      mrp: parseInt(pr, 10) * 2,
      discount: '50% off',
      rating: 4.5,
      ratingCount: '10',
      reviewCount: '2',
      fassured: true,
      images: ['https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=800'],
      variants: [],
      specs: {}
    });
    showToast('Product added to live catalog!');
    renderSellerPage(document.getElementById('appRoot'));
  }
}

// ==========================================================================
// 13. SUPER ADMIN PORTAL (`#/admin`)
// ==========================================================================
function renderAdminPage(root) {
  root.innerHTML = `
    <div class="fk-main-container">
      <div style="background:#0F172A; color:#fff; padding:28px 32px; border-radius:4px; display:flex; justify-content:space-between; align-items:center;">
        <div>
          <h1 style="font-size:24px; font-weight:800;">Flipkart Super Admin Control Center</h1>
          <p style="opacity:0.8; font-size:13px;">Platform Moderation • MySQL flipkartdb Master Dashboard</p>
        </div>
        <span style="background:#388E3C; color:#fff; font-size:12px; font-weight:700; padding:6px 14px; border-radius:14px;">● System Online (HTTP 200 OK)</span>
      </div>

      <!-- Admin Platform KPIs -->
      <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:16px; margin:20px 0;">
        <div style="background:#fff; padding:20px; border-radius:4px; box-shadow:0 1px 2px rgba(0,0,0,0.08);">
          <div style="color:#777; font-size:12px; font-weight:700;">GROSS MERCHANDISE VALUE (GMV)</div>
          <div style="font-size:28px; font-weight:900; color:#2874F0; margin-top:4px;">₹1,84,90,200</div>
        </div>
        <div style="background:#fff; padding:20px; border-radius:4px; box-shadow:0 1px 2px rgba(0,0,0,0.08);">
          <div style="color:#777; font-size:12px; font-weight:700;">REGISTERED USERS</div>
          <div style="font-size:28px; font-weight:900; color:#388E3C; margin-top:4px;">1,24,850</div>
        </div>
        <div style="background:#fff; padding:20px; border-radius:4px; box-shadow:0 1px 2px rgba(0,0,0,0.08);">
          <div style="color:#777; font-size:12px; font-weight:700;">VERIFIED SELLERS</div>
          <div style="font-size:28px; font-weight:900; color:#FF9F00; margin-top:4px;">45 Sellers</div>
        </div>
        <div style="background:#fff; padding:20px; border-radius:4px; box-shadow:0 1px 2px rgba(0,0,0,0.08);">
          <div style="color:#777; font-size:12px; font-weight:700;">TOTAL ORDERS PLACED</div>
          <div style="font-size:28px; font-weight:900; color:#212121; margin-top:4px;">38,420</div>
        </div>
      </div>

      <!-- Admin Actions -->
      <div class="fk-section-block">
        <h2 class="fk-section-title" style="margin-bottom:16px;">Platform Moderation & Security Oversight</h2>
        <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:16px;">
          <div style="border:1px solid #E0E0E0; padding:18px; border-radius:4px;">
            <h4 style="font-size:16px; font-weight:700; margin-bottom:6px;">Seller Applications</h4>
            <p style="font-size:13px; color:#555; margin-bottom:12px;">3 pending seller merchant applications waiting for GST & bank account verification.</p>
            <button class="fk-view-all-btn" style="padding:6px 14px; font-size:12px;" onclick="showToast('Sellers verified!')">Review & Approve</button>
          </div>
          <div style="border:1px solid #E0E0E0; padding:18px; border-radius:4px;">
            <h4 style="font-size:16px; font-weight:700; margin-bottom:6px;">Coupons & Campaigns</h4>
            <p style="font-size:13px; color:#555; margin-bottom:12px;">Active Campaign: Big Billion Days (FLIPKART50 coupon live with 1,420 redemptions).</p>
            <button class="fk-view-all-btn" style="padding:6px 14px; font-size:12px;" onclick="showToast('Coupon code FLIPKART50 active!')">Manage Coupons</button>
          </div>
          <div style="border:1px solid #E0E0E0; padding:18px; border-radius:4px;">
            <h4 style="font-size:16px; font-weight:700; margin-bottom:6px;">MySQL flipkartdb Health</h4>
            <p style="font-size:13px; color:#555; margin-bottom:12px;">42 relational tables • Row-level locks active • InnoDB atomic transactions operating smoothly.</p>
            <button class="fk-view-all-btn" style="padding:6px 14px; font-size:12px; background:#388E3C;" onclick="showToast('Database flipkartdb 100% Healthy')">Run DB Health Check</button>
          </div>
        </div>
      </div>
    </div>
  `;
}

// ==========================================================================
// UTILITY FUNCTIONS & EVENT LISTENERS
// ==========================================================================
function setupGlobalListeners() {
  const input = document.getElementById('searchInput');
  const dropdown = document.getElementById('searchDropdown');

  if (input) {
    input.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        const val = input.value.trim();
        dropdown?.classList.remove('open');
        navigateTo(`#/search?q=${encodeURIComponent(val)}`);
      }
    });

    input.addEventListener('input', (e) => {
      const q = e.target.value.trim().toLowerCase();
      if (!dropdown) return;
      if (!q) {
        dropdown.classList.remove('open');
        return;
      }
      const matches = state.products.filter(p => p.title.toLowerCase().includes(q) || p.brand.toLowerCase().includes(q));
      dropdown.innerHTML = `
        <div class="fk-dropdown-header">Matching Products</div>
        ${matches.slice(0, 5).map(m => `
          <div class="fk-dropdown-item" onclick="selectSearch(${m.id})">
            <img src="${m.images[0]}" alt="${m.title}" />
            <div>
              <div style="font-weight:600; font-size:13px;">${m.title}</div>
              <div style="font-size:12px; color:#2874F0; font-weight:700;">₹${m.price.toLocaleString('en-IN')}</div>
            </div>
          </div>
        `).join('')}
      `;
      dropdown.classList.add('open');
    });
  }

  document.addEventListener('click', (e) => {
    if (!e.target.closest('.fk-search-container')) {
      document.getElementById('searchDropdown')?.classList.remove('open');
    }
  });
}

function selectSearch(id) {
  document.getElementById('searchDropdown')?.classList.remove('open');
  navigateTo(`#/product/${id}`);
}

function showToast(msg) {
  let t = document.getElementById('fkToast');
  if (!t) {
    t = document.createElement('div');
    t.id = 'fkToast';
    t.className = 'fk-toast';
    document.body.appendChild(t);
  }
  t.innerText = msg;
  t.classList.add('show');
  clearTimeout(t._timer);
  t._timer = setTimeout(() => t.classList.remove('show'), 2600);
}

// Carousel
function initCarousel() {
  const track = document.getElementById('carouselTrack');
  const dots = document.getElementById('carouselDots');
  if (!track) return;

  const slides = [
    {
      tag: 'EXCLUSIVE LAUNCH',
      title: 'Celvas MagSafe Armor for iPhone 15',
      sub: '10ft Military Drop Certified • Built-in N52 Magnets • 73% Off Today',
      btn: 'Shop Now at ₹399',
      bg: 'linear-gradient(135deg, #0b1f3a 0%, #173d74 60%, #2874F0 100%)',
      img: 'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=600',
      pId: 1
    },
    {
      tag: 'BIG BILLION SAVINGS',
      title: 'POCO X8 Power 5G Monster Edition',
      sub: '10,000mAh Battery • 12GB RAM • Sale Live Now at ₹32,999*',
      btn: 'Explore Deals',
      bg: 'linear-gradient(135deg, #1f1b04 0%, #4a3e08 60%, #eab308 100%)',
      img: 'https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=600',
      pId: 4
    },
    {
      tag: 'CRYSTAL 4K THEATRE',
      title: '55" Samsung 4K Smart TV',
      sub: 'Dynamic Color Boost • HDR10+ • Upgrade From ₹2,352/Month*',
      btn: 'View TV Offers',
      bg: 'linear-gradient(135deg, #111827 0%, #1e293b 60%, #334155 100%)',
      img: 'https://images.unsplash.com/photo-1593784991095-a205069470b6?w=600',
      pId: 5
    }
  ];

  track.innerHTML = slides.map(s => `
    <div class="fk-carousel-slide" style="background:${s.bg};" onclick="navigateTo('#/product/${s.pId}')">
      <div class="fk-slide-content">
        <span class="fk-slide-tag">${s.tag}</span>
        <h2 class="fk-slide-title">${s.title}</h2>
        <p class="fk-slide-sub">${s.sub}</p>
        <button class="fk-slide-btn" onclick="event.stopPropagation(); navigateTo('#/product/${s.pId}')">${s.btn} ➔</button>
      </div>
      <img src="${s.img}" class="fk-slide-img" alt="${s.title}" />
    </div>
  `).join('');

  if (dots) {
    dots.innerHTML = slides.map((_, i) => `<div class="fk-dot ${i === 0 ? 'active' : ''}" onclick="goToSlide(${i})"></div>`).join('');
  }

  clearInterval(state.carouselTimer);
  state.carouselTimer = setInterval(() => {
    state.carouselIndex = (state.carouselIndex + 1) % slides.length;
    goToSlide(state.carouselIndex);
  }, 4500);
}

function goToSlide(idx) {
  state.carouselIndex = idx;
  const track = document.getElementById('carouselTrack');
  const dots = document.querySelectorAll('.fk-dot');
  if (track) track.style.transform = `translateX(-${idx * 100}%)`;
  dots.forEach((d, i) => d.classList.toggle('active', i === idx));
}

function initCountdownTimer() {
  const el = document.getElementById('dealTimer');
  if (!el) return;
  let sec = 18 * 3600 + 24 * 60 + 12;
  setInterval(() => {
    sec--;
    if (sec < 0) sec = 24 * 3600;
    const h = String(Math.floor(sec / 3600)).padStart(2, '0');
    const m = String(Math.floor((sec % 3600) / 60)).padStart(2, '0');
    const s = String(sec % 60).padStart(2, '0');
    el.innerText = `🕒 ${h} : ${m} : ${s} Left`;
  }, 1000);
}

function renderCategoryStrip() {
  const container = document.getElementById('categoryStrip');
  if (!container) return;
  const cats = [
    { name: 'For You', icon: '🛍️' },
    { name: 'Mobiles', icon: '📱' },
    { name: 'Electronics', icon: '💻' },
    { name: 'Fashion', icon: '👗' },
    { name: 'Appliances', icon: '📺' },
    { name: 'Home', icon: '🛋️' },
    { name: 'Beauty', icon: '💄' },
    { name: 'Toys', icon: '🧸' },
    { name: 'Sports', icon: '🏏' },
    { name: 'Travel', icon: '✈️' }
  ];

  container.innerHTML = cats.map(c => `
    <div class="fk-cat-item ${state.activeCategory === c.name ? 'active' : ''}" onclick="navigateToCategory('${c.name}')">
      <div class="fk-cat-icon" style="background:#F0F5FF;">${c.icon}</div>
      <span class="fk-cat-label">${c.name}</span>
    </div>
  `).join('');
}

function navigateToCategory(name) {
  state.activeCategory = name;
  if (name === 'For You') {
    navigateTo('#/');
  } else {
    navigateTo(`#/category/${encodeURIComponent(name)}`);
  }
}

function createProductCardHTML(p) {
  return `
    <div class="fk-product-card" onclick="navigateTo('#/product/${p.id}')">
      <div class="fk-card-heart" onclick="event.stopPropagation(); showToast('Toggled wishlist ❤️')">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
        </svg>
      </div>

      <div class="fk-card-img-box">
        <img src="${p.images[0]}" alt="${p.title}" loading="lazy" />
      </div>

      ${p.sponsored ? '<span class="fk-sponsored-tag">Sponsored</span>' : ''}
      <h3 class="fk-card-title" title="${p.title}">${p.title}</h3>

      <div class="fk-card-rating-row">
        <span class="fk-rating-pill">${p.rating} ★</span>
        <span class="fk-rating-count">(${p.ratingCount})</span>
        ${p.fassured ? `
          <span class="fk-fassured-badge">
            <svg width="68" height="18" viewBox="0 0 90 24" fill="none">
              <rect width="90" height="24" rx="4" fill="#0056D2"/>
              <path d="M12 6L14.5 11L20 11.8L16 15.7L16.9 21.2L12 18.6L7.1 21.2L8 15.7L4 11.8L9.5 11L12 6Z" fill="#FFE500"/>
              <text x="24" y="16" fill="#FFFFFF" font-size="11" font-weight="900" font-style="italic">Plus F-Assured</text>
            </svg>
          </span>
        ` : ''}
      </div>

      <div class="fk-card-price-row">
        <span class="fk-current-price">₹${p.price.toLocaleString('en-IN')}</span>
        <span class="fk-mrp-price">₹${p.mrp.toLocaleString('en-IN')}</span>
        <span class="fk-discount-tag">${p.discount}</span>
      </div>

      <div class="fk-offer-tag">🏷️ Bank Offer Available</div>
      <div class="fk-card-delivery">${p.delivery}</div>

      <button class="fk-card-quick-add" onclick="event.stopPropagation(); addToCartAndGo(${p.id})">
        🛒 Add to Cart
      </button>
    </div>
  `;
}

function renderDealsRow() {
  const container = document.getElementById('dealsScrollRow');
  if (!container) return;
  container.innerHTML = state.products.map(p => createProductCardHTML(p)).join('');
}

// ==========================================================================
// 12. SUPERCOIN ZONE PAGE (`#/supercoin` & `#/plus`)
// ==========================================================================
function renderSuperCoinPage(root) {
  root.innerHTML = `
    <div class="fk-sc-container">
      <!-- Top Hero Banner -->
      <div class="fk-sc-hero">
        <div>
          <div style="font-size:14px; text-transform:uppercase; letter-spacing:1px; opacity:0.9; margin-bottom:4px;">Flipkart Plus & Rewards</div>
          <h1 style="font-size:32px; font-weight:900; margin-bottom:8px;">⚡ SuperCoin Zone</h1>
          <p style="font-size:15px; opacity:0.95; max-width:600px;">Earn 2 SuperCoins for every ₹100 spent. Redeem for instant extra discounts, gift vouchers, and partner subscriptions!</p>
        </div>
        <div class="fk-sc-balance-card">
          <div style="font-size:12px; font-weight:700; text-transform:uppercase; opacity:0.9;">Total Balance</div>
          <div style="font-size:36px; font-weight:900; color:#FFE500; margin:4px 0;">⚡ 0</div>
          <div style="font-size:13px; text-decoration:underline; cursor:pointer;" onclick="showToast('No SuperCoins transactions yet')">Transaction History &gt;</div>
        </div>
      </div>

      <!-- Navigation Tabs -->
      <div class="fk-sc-tabs-bar">
        <button class="fk-sc-tab active" onclick="showToast('Viewing All Reward Offers')">% All Reward Deals</button>
        <button class="fk-sc-tab" onclick="showToast('Earn SuperCoins on every purchase!')">⚡ How to Earn</button>
        <button class="fk-sc-tab" onclick="showToast('Use SuperCoins across 100+ partner brands')">💎 Partner Benefits</button>
        <button class="fk-sc-tab" onclick="showToast('Explore Gift Cards')">🎁 Gift Vouchers</button>
      </div>

      <!-- Reward Store Vouchers -->
      <div class="fk-section-header" style="margin-bottom:16px;">
        <h2 class="fk-section-title">Reward Store (Claim with SuperCoins)</h2>
        <span style="font-size:13px; color:#2874F0; font-weight:700; cursor:pointer;" onclick="showToast('All 48 brand vouchers available')">View All (48) &gt;</span>
      </div>

      <div class="fk-sc-rewards-grid">
        <div class="fk-sc-reward-card">
          <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
            <div style="font-size:36px;">🍕</div>
            <span style="background:#FFF3E0; color:#E65100; font-weight:700; font-size:11px; padding:3px 8px; border-radius:4px;">POPULAR</span>
          </div>
          <h3 style="font-size:16px; font-weight:700; margin-bottom:4px;">Domino's Pizza Flat ₹100 Off</h3>
          <p style="font-size:13px; color:#666; margin-bottom:16px;">Valid on minimum order of ₹350 across all outlets.</p>
          <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid #F0F0F0; padding-top:12px;">
            <span style="font-weight:800; color:#B78103;">⚡ 25 SuperCoins</span>
            <button class="fk-view-all-btn" style="padding:6px 16px; font-size:12px;" onclick="claimReward('Domino\\'s ₹100 Off', 25)">Claim</button>
          </div>
        </div>

        <div class="fk-sc-reward-card">
          <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
            <div style="font-size:36px;">🍔</div>
            <span style="background:#E8F5E9; color:#2E7D32; font-weight:700; font-size:11px; padding:3px 8px; border-radius:4px;">FOOD</span>
          </div>
          <h3 style="font-size:16px; font-weight:700; margin-bottom:4px;">McDonald's Free McAloo Tikki</h3>
          <p style="font-size:13px; color:#666; margin-bottom:16px;">Get 1 free burger with any medium meal.</p>
          <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid #F0F0F0; padding-top:12px;">
            <span style="font-weight:800; color:#B78103;">⚡ 40 SuperCoins</span>
            <button class="fk-view-all-btn" style="padding:6px 16px; font-size:12px;" onclick="claimReward('McDonald\\'s Free Burger', 40)">Claim</button>
          </div>
        </div>

        <div class="fk-sc-reward-card">
          <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
            <div style="font-size:36px;">🎬</div>
            <span style="background:#EDE7F6; color:#512DA8; font-weight:700; font-size:11px; padding:3px 8px; border-radius:4px;">ENTERTAINMENT</span>
          </div>
          <h3 style="font-size:16px; font-weight:700; margin-bottom:4px;">SonyLIV Premium (12 Months)</h3>
          <p style="font-size:13px; color:#666; margin-bottom:16px;">Stream live sports, UEFA Champions League, and web series.</p>
          <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid #F0F0F0; padding-top:12px;">
            <span style="font-weight:800; color:#B78103;">⚡ 150 SuperCoins</span>
            <button class="fk-view-all-btn" style="padding:6px 16px; font-size:12px;" onclick="claimReward('SonyLIV 12M Premium', 150)">Claim</button>
          </div>
        </div>

        <div class="fk-sc-reward-card">
          <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
            <div style="font-size:36px;">🏋️</div>
            <span style="background:#FFF9C4; color:#F57F17; font-weight:700; font-size:11px; padding:3px 8px; border-radius:4px;">FITNESS</span>
          </div>
          <h3 style="font-size:16px; font-weight:700; margin-bottom:4px;">Cult.fit 1 Month Elite Pass</h3>
          <p style="font-size:13px; color:#666; margin-bottom:16px;">Unlimited access to Cult gym & group workouts.</p>
          <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid #F0F0F0; padding-top:12px;">
            <span style="font-weight:800; color:#B78103;">⚡ 200 SuperCoins</span>
            <button class="fk-view-all-btn" style="padding:6px 16px; font-size:12px;" onclick="claimReward('Cult.fit Elite Pass', 200)">Claim</button>
          </div>
        </div>
      </div>

      <!-- SuperCoin Multiplier Deals Section -->
      <div class="fk-section-header" style="margin-bottom:16px;">
        <h2 class="fk-section-title">Multiplier upto 100x Off Deal!</h2>
      </div>
      <div class="fk-pastel-grid">
        <div class="fk-pastel-card" style="background:#FFF3E0; border-color:#FFE0B2;" onclick="navigateTo('#/search?q=Milton')">
          <div class="fk-pastel-tag" style="background:#FFE0B2; color:#E65100;">HOT BRANDS</div>
          <h3 class="fk-pastel-title">Milton & Borosil</h3>
          <div class="fk-pastel-subtitle">Up to 70% + 50 SuperCoins</div>
          <div style="font-size:40px; margin-top:12px;">🥣</div>
        </div>
        <div class="fk-pastel-card" style="background:#E8F5E9; border-color:#C8E6C9;" onclick="navigateTo('#/search?q=Cookware')">
          <div class="fk-pastel-tag" style="background:#C8E6C9; color:#2E7D32;">KITCHEN</div>
          <h3 class="fk-pastel-title">Prestige & Hawkins</h3>
          <div class="fk-pastel-subtitle">Cookware from ₹499</div>
          <div style="font-size:40px; margin-top:12px;">🍳</div>
        </div>
        <div class="fk-pastel-card" style="background:#EDE7F6; border-color:#D1C4E9;" onclick="navigateTo('#/search?q=Shoes')">
          <div class="fk-pastel-tag" style="background:#D1C4E9; color:#512DA8;">SPORTS</div>
          <h3 class="fk-pastel-title">Nike, Puma & Asics</h3>
          <div class="fk-pastel-subtitle">Min 40% Off + 100 Coins</div>
          <div style="font-size:40px; margin-top:12px;">👟</div>
        </div>
      </div>
    </div>
  `;
}

function claimReward(rewardName, coins) {
  alert(`⚡ Flipkart SuperCoin Reward\n\nYou clicked to claim: ${rewardName}\nRequired SuperCoins: ${coins}\nCurrent SuperCoins Balance: 0\n\nShop more on Flipkart to earn SuperCoins! Every ₹100 spent gives you 2 SuperCoins.`);
}

// ==========================================================================
// 13. MY COUPONS PAGE (`#/account/coupons`)
// ==========================================================================
function renderCouponsPage(root) {
  root.innerHTML = `
    <div style="max-width:1360px; margin:16px auto 0 auto; padding:0 24px; font-size:12px; color:#878787;">
      <a href="#/" style="color:#2874F0;">Home</a> &gt; <a href="#/account" style="color:#2874F0;">My Account</a> &gt; <span>My Coupons</span>
    </div>

    <div class="fk-account-layout" style="margin-top:12px;">
      <!-- Left Sidebar -->
      <aside class="fk-acc-sidebar">
        <div class="fk-acc-user-card">
          <div class="fk-acc-avatar">👤</div>
          <div>
            <div class="fk-acc-greeting">Hello,</div>
            <div class="fk-acc-name">${state.user.name}</div>
          </div>
        </div>

        <div class="fk-acc-nav-box">
          <a class="fk-acc-nav-item" onclick="navigateTo('#/account')">Profile Information</a>
          <a class="fk-acc-nav-item" onclick="navigateTo('#/account/addresses')">Manage Addresses</a>
          <a class="fk-acc-nav-item" onclick="navigateTo('#/account/orders')">My Orders</a>
          <a class="fk-acc-nav-item" onclick="navigateTo('#/wishlist')">My Wishlist</a>
          <a class="fk-acc-nav-item active" onclick="navigateTo('#/account/coupons')">My Coupons</a>
          <a class="fk-acc-nav-item" onclick="navigateTo('#/account/gift-cards')">Gift Cards</a>
        </div>
      </aside>

      <!-- Right Content -->
      <div class="fk-acc-content-card">
        <h2 class="fk-profile-title" style="margin-bottom:20px;">Available Coupons (3)</h2>

        <div style="display:flex; flex-direction:column; gap:16px;">
          <div style="border:1px dashed #2874F0; background:#F0F5FF; border-radius:6px; padding:20px; display:flex; justify-content:space-between; align-items:center;">
            <div>
              <div style="display:flex; align-items:center; gap:10px; margin-bottom:6px;">
                <span style="background:#2874F0; color:#fff; font-weight:800; padding:3px 10px; border-radius:3px; font-size:13px; letter-spacing:0.5px;">FLIPKART50</span>
                <span style="color:#388E3C; font-weight:700; font-size:13px;">✓ ACTIVE</span>
              </div>
              <div style="font-weight:700; font-size:15px; margin-bottom:4px;">Flat ₹50 Off on Any Order Above ₹299</div>
              <div style="font-size:12px; color:#666;">Valid till 31 Oct, 2026 • Applicable automatically on checkout</div>
            </div>
            <button class="fk-view-all-btn" onclick="navigateTo('#/')">Use Coupon</button>
          </div>

          <div style="border:1px dashed #388E3C; background:#E8F5E9; border-radius:6px; padding:20px; display:flex; justify-content:space-between; align-items:center;">
            <div>
              <div style="display:flex; align-items:center; gap:10px; margin-bottom:6px;">
                <span style="background:#388E3C; color:#fff; font-weight:800; padding:3px 10px; border-radius:3px; font-size:13px; letter-spacing:0.5px;">BIGBILLION10</span>
                <span style="color:#388E3C; font-weight:700; font-size:13px;">✓ FESTIVE PASS</span>
              </div>
              <div style="font-weight:700; font-size:15px; margin-bottom:4px;">10% Instant Discount on Electronics & Mobiles</div>
              <div style="font-size:12px; color:#666;">Valid on HDFC / SBI Cards • Max discount ₹1,500</div>
            </div>
            <button class="fk-view-all-btn" onclick="navigateTo('#/category/Electronics')">Shop Electronics</button>
          </div>

          <div style="border:1px dashed #E65100; background:#FFF3E0; border-radius:6px; padding:20px; display:flex; justify-content:space-between; align-items:center;">
            <div>
              <div style="display:flex; align-items:center; gap:10px; margin-bottom:6px;">
                <span style="background:#E65100; color:#fff; font-weight:800; padding:3px 10px; border-radius:3px; font-size:13px; letter-spacing:0.5px;">PLUSFREEDEL</span>
                <span style="color:#388E3C; font-weight:700; font-size:13px;">✓ UNLIMITED</span>
              </div>
              <div style="font-weight:700; font-size:15px; margin-bottom:4px;">Free Express Shipping on All F-Assured Orders</div>
              <div style="font-size:12px; color:#666;">Flipkart Plus exclusive member benefit</div>
            </div>
            <button class="fk-view-all-btn" onclick="navigateTo('#/')">Explore Store</button>
          </div>
        </div>
      </div>
    </div>
  `;
}

// ==========================================================================
// 14. FLIPKART GIFT CARDS PAGE (`#/account/gift-cards`)
// ==========================================================================
function renderGiftCardsPage(root) {
  root.innerHTML = `
    <div style="max-width:1360px; margin:16px auto 0 auto; padding:0 24px; font-size:12px; color:#878787;">
      <a href="#/" style="color:#2874F0;">Home</a> &gt; <a href="#/account" style="color:#2874F0;">My Account</a> &gt; <span>Gift Cards</span>
    </div>

    <div class="fk-account-layout" style="margin-top:12px;">
      <!-- Left Sidebar -->
      <aside class="fk-acc-sidebar">
        <div class="fk-acc-user-card">
          <div class="fk-acc-avatar">👤</div>
          <div>
            <div class="fk-acc-greeting">Hello,</div>
            <div class="fk-acc-name">${state.user.name}</div>
          </div>
        </div>

        <div class="fk-acc-nav-box">
          <a class="fk-acc-nav-item" onclick="navigateTo('#/account')">Profile Information</a>
          <a class="fk-acc-nav-item" onclick="navigateTo('#/account/addresses')">Manage Addresses</a>
          <a class="fk-acc-nav-item" onclick="navigateTo('#/account/orders')">My Orders</a>
          <a class="fk-acc-nav-item" onclick="navigateTo('#/wishlist')">My Wishlist</a>
          <a class="fk-acc-nav-item" onclick="navigateTo('#/account/coupons')">My Coupons</a>
          <a class="fk-acc-nav-item active" onclick="navigateTo('#/account/gift-cards')">Gift Cards</a>
        </div>
      </aside>

      <!-- Right Content -->
      <div class="fk-acc-content-card">
        <h2 class="fk-profile-title" style="margin-bottom:8px;">Flipkart Gift Cards</h2>
        <p style="color:#777; font-size:13px; margin-bottom:24px;">Add a gift card to your balance or send digital gift cards instantly to friends and family.</p>

        <!-- Balance Card -->
        <div style="background:linear-gradient(135deg, #2874F0 0%, #1259C7 100%); color:#fff; border-radius:8px; padding:24px; margin-bottom:28px; display:flex; justify-content:space-between; align-items:center;">
          <div>
            <div style="font-size:13px; opacity:0.9;">Available Gift Card Balance</div>
            <div style="font-size:32px; font-weight:800; margin:4px 0;">₹0.00</div>
            <div style="font-size:12px; opacity:0.85;">No active gift cards linked</div>
          </div>
          <button style="background:#FFE500; color:#000; font-weight:700; padding:10px 20px; border-radius:4px;" onclick="showToast('Flipkart Digital Gift Cards store opened')">
            + BUY A GIFT CARD
          </button>
        </div>

        <!-- Add Gift Card Form -->
        <h3 style="font-size:16px; font-weight:700; margin-bottom:14px;">Add a Gift Card to Your Wallet</h3>
        <div style="display:flex; flex-direction:column; gap:14px; max-width:480px;">
          <input type="text" class="fk-profile-input" id="gcNumber" placeholder="16-Digit Gift Card Number" maxlength="16" />
          <input type="password" class="fk-profile-input" id="gcPin" placeholder="6-Digit Gift Card PIN" maxlength="6" />
          <button class="fk-btn-checkout" style="padding:12px; border-radius:4px;" onclick="addGiftCard()">
            APPLY TO BALANCE
          </button>
        </div>
      </div>
    </div>
  `;
}

function addGiftCard() {
  const num = document.getElementById('gcNumber')?.value.trim();
  const pin = document.getElementById('gcPin')?.value.trim();
  if (!num || !pin) {
    alert('Please enter a valid 16-digit Gift Card number and 6-digit PIN.');
    return;
  }
  showToast('Gift Card verified and linked to your Flipkart account!');
}

// ==========================================================================
// 15. NOTIFICATIONS PAGE (`#/account/notifications`)
// ==========================================================================
function renderNotificationsPage(root) {
  root.innerHTML = `
    <div style="max-width:1360px; margin:16px auto 0 auto; padding:0 24px; font-size:12px; color:#878787;">
      <a href="#/" style="color:#2874F0;">Home</a> &gt; <a href="#/account" style="color:#2874F0;">My Account</a> &gt; <span>All Notifications</span>
    </div>

    <div class="fk-account-layout" style="margin-top:12px;">
      <!-- Left Sidebar -->
      <aside class="fk-acc-sidebar">
        <div class="fk-acc-user-card">
          <div class="fk-acc-avatar">👤</div>
          <div>
            <div class="fk-acc-greeting">Hello,</div>
            <div class="fk-acc-name">${state.user.name}</div>
          </div>
        </div>

        <div class="fk-acc-nav-box">
          <a class="fk-acc-nav-item" onclick="navigateTo('#/account')">Profile Information</a>
          <a class="fk-acc-nav-item" onclick="navigateTo('#/account/addresses')">Manage Addresses</a>
          <a class="fk-acc-nav-item" onclick="navigateTo('#/account/orders')">My Orders</a>
          <a class="fk-acc-nav-item" onclick="navigateTo('#/wishlist')">My Wishlist</a>
          <a class="fk-acc-nav-item active" onclick="navigateTo('#/account/notifications')">All Notifications</a>
        </div>
      </aside>

      <!-- Right Content -->
      <div class="fk-acc-content-card">
        <h2 class="fk-profile-title" style="margin-bottom:20px;">All Notifications</h2>

        <div style="display:flex; flex-direction:column; gap:16px;">
          <div style="border-bottom:1px solid #F0F0F0; padding-bottom:14px;">
            <div style="font-weight:700; font-size:14px; margin-bottom:2px;">⚡ The Big Billion Days is Coming!</div>
            <div style="font-size:13px; color:#555;">Early bird VIP access for Murugan Nagaraj starts soon. Add wishlist items to lock prices.</div>
            <div style="font-size:11px; color:#878787; margin-top:4px;">2 hours ago</div>
          </div>

          <div style="border-bottom:1px solid #F0F0F0; padding-bottom:14px;">
            <div style="font-weight:700; font-size:14px; margin-bottom:2px;">📦 Order Delivered</div>
            <div style="font-size:13px; color:#555;">Your package containing "Celvas Back Cover for Apple iPhone 15" was delivered successfully.</div>
            <div style="font-size:11px; color:#878787; margin-top:4px;">Sep 10, 2026</div>
          </div>

          <div style="border-bottom:1px solid #F0F0F0; padding-bottom:14px;">
            <div style="font-weight:700; font-size:14px; margin-bottom:2px;">🎉 50 SuperCoins Unlocked</div>
            <div style="font-size:13px; color:#555;">Congratulations! You have qualified for bonus reward coins on your next eligible purchase.</div>
            <div style="font-size:11px; color:#878787; margin-top:4px;">Sep 05, 2026</div>
          </div>
        </div>
      </div>
    </div>
  `;
}

// ==========================================================================
// 16. REVIEWS & RATINGS PAGE (`#/account/reviews`)
// ==========================================================================
function renderReviewsPage(root) {
  root.innerHTML = `
    <div style="max-width:1360px; margin:16px auto 0 auto; padding:0 24px; font-size:12px; color:#878787;">
      <a href="#/" style="color:#2874F0;">Home</a> &gt; <a href="#/account" style="color:#2874F0;">My Account</a> &gt; <span>My Reviews & Ratings</span>
    </div>

    <div class="fk-account-layout" style="margin-top:12px;">
      <!-- Left Sidebar -->
      <aside class="fk-acc-sidebar">
        <div class="fk-acc-user-card">
          <div class="fk-acc-avatar">👤</div>
          <div>
            <div class="fk-acc-greeting">Hello,</div>
            <div class="fk-acc-name">${state.user.name}</div>
          </div>
        </div>

        <div class="fk-acc-nav-box">
          <a class="fk-acc-nav-item" onclick="navigateTo('#/account')">Profile Information</a>
          <a class="fk-acc-nav-item" onclick="navigateTo('#/account/addresses')">Manage Addresses</a>
          <a class="fk-acc-nav-item" onclick="navigateTo('#/account/orders')">My Orders</a>
          <a class="fk-acc-nav-item" onclick="navigateTo('#/wishlist')">My Wishlist</a>
          <a class="fk-acc-nav-item active" onclick="navigateTo('#/account/reviews')">My Reviews & Ratings</a>
        </div>
      </aside>

      <!-- Right Content -->
      <div class="fk-acc-content-card">
        <h2 class="fk-profile-title" style="margin-bottom:20px;">My Reviews & Ratings (2)</h2>

        <div style="display:flex; flex-direction:column; gap:16px;">
          <div style="border:1px solid #E0E0E0; border-radius:6px; padding:18px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
              <strong style="font-size:15px;">Celvas Back Cover for Apple iPhone 15</strong>
              <span class="fk-rating-pill">5 ★</span>
            </div>
            <div style="font-weight:700; font-size:13px; color:#212121; margin-bottom:4px;">Outstanding Quality & MagSafe Grip!</div>
            <div style="font-size:13px; color:#555; line-height:1.5;">The magnetic ring snaps onto Apple MagSafe charger firmly. Super snug fit on my iPhone 15 with crystal clear back. Best back cover in this budget!</div>
            <div style="font-size:11px; color:#878787; margin-top:8px;">Murugan Nagaraj • Certified Buyer • Sep 10, 2026</div>
          </div>

          <div style="border:1px solid #E0E0E0; border-radius:6px; padding:18px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
              <strong style="font-size:15px;">boAt Airdopes 161 True Wireless Earbuds</strong>
              <span class="fk-rating-pill">4 ★</span>
            </div>
            <div style="font-weight:700; font-size:13px; color:#212121; margin-bottom:4px;">Great Bass and Quick Charging</div>
            <div style="font-size:13px; color:#555; line-height:1.5;">Sound is crisp with solid low-end thump. 10 minutes charging easily lasts for around 3 hours. Great daily driver earbuds.</div>
            <div style="font-size:11px; color:#878787; margin-top:8px;">Murugan Nagaraj • Certified Buyer • Aug 22, 2026</div>
          </div>
        </div>
      </div>
    </div>
  `;
}

// ==========================================================================
// 17. INTERACTIVE STAR RATING & REVIEW MODAL CONTROLS
// ==========================================================================
let currentReviewRating = 5;
let currentReviewProduct = 'Celvas Back Cover for Apple iPhone 15';

function openReviewModal(productName) {
  currentReviewProduct = productName || 'Product';
  const modal = document.getElementById('reviewModal');
  const sub = document.getElementById('reviewModalSubtitle');
  if (sub) sub.innerText = currentReviewProduct;
  if (modal) modal.classList.add('open');
}

function closeReviewModal() {
  const modal = document.getElementById('reviewModal');
  if (modal) modal.classList.remove('open');
}

function setReviewRating(r) {
  currentReviewRating = r;
  const stars = document.querySelectorAll('#starsPicker .fk-star-btn');
  stars.forEach((s, idx) => {
    s.classList.toggle('active', idx < r);
  });
}

function submitProductReview() {
  const title = document.getElementById('reviewTitleInput')?.value.trim() || 'Great Product!';
  const text = document.getElementById('reviewTextInput')?.value.trim() || '';
  closeReviewModal();
  showToast(`🎉 Review submitted! Thank you for rating "${currentReviewProduct.slice(0, 24)}..." with ${currentReviewRating} stars.`);
}

// ==========================================================================
// 18. ORDER DETAILS & LIVE DELIVERY TRACKER PAGE (`#/account/order/:id`)
// ==========================================================================
function renderOrderDetailsPage(root, orderId) {
  const order = state.orders.find(o => o.id === orderId) || state.orders[0];
  const curAddr = state.addresses.find(a => a.id === state.selectedAddressId) || state.addresses[0];
  const isCancelled = order.status === 'CANCELLED';

  root.innerHTML = `
    <div style="max-width:1360px; margin:16px auto 0 auto; padding:0 24px; font-size:12px; color:#878787;">
      <a href="#/" style="color:#2874F0;">Home</a> &gt; <a href="#/account" style="color:#2874F0;">My Account</a> &gt; <a href="#/account/orders" style="color:#2874F0;">My Orders</a> &gt; <span>${order.id}</span>
    </div>

    <div style="max-width:1360px; margin:16px auto 40px auto; padding:0 24px;">
      <!-- Delivery Address & Actions Bar -->
      <div style="background:#fff; border-radius:4px; padding:20px 24px; box-shadow:var(--fk-shadow-sm); margin-bottom:16px; display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px;">
        <div>
          <div style="font-size:14px; font-weight:700; text-transform:uppercase; margin-bottom:8px; color:#212121;">Delivery Address</div>
          <div style="font-size:14px; font-weight:700; margin-bottom:4px;">${curAddr.name} <span style="background:#F0F2F5; font-size:11px; padding:2px 6px; border-radius:3px;">${curAddr.type}</span></div>
          <div style="font-size:13px; color:#555; line-height:1.5;">${curAddr.address}, ${curAddr.locality}, ${curAddr.city}, ${curAddr.state} - <strong>${curAddr.pincode}</strong></div>
          <div style="font-size:13px; margin-top:4px;"><strong>Phone Number:</strong> ${curAddr.phone}</div>
        </div>

        <div style="display:flex; flex-direction:column; gap:8px;">
          <button style="display:flex; align-items:center; gap:6px; border:1px solid #2874F0; color:#2874F0; font-weight:700; font-size:13px; padding:8px 18px; border-radius:4px; background:#fff; cursor:pointer;" onclick="downloadInvoice('${order.id}')">
            <span>📄</span> Download Invoice
          </button>
          <button style="display:flex; align-items:center; gap:6px; border:1px solid #E0E0E0; color:#212121; font-weight:700; font-size:13px; padding:8px 18px; border-radius:4px; background:#fff; cursor:pointer;" onclick="navigateTo('#/help')">
            <span>❓</span> Need Help?
          </button>
        </div>
      </div>

      <!-- Order Tracking Stepper -->
      <div class="fk-tracker-container">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid #F0F0F0; padding-bottom:12px;">
          <div>
            <span style="font-size:15px; font-weight:700; color:#212121;">Order Status: </span>
            <span style="font-weight:700; color:${isCancelled ? '#E41D2D' : '#388E3C'};">${order.statusText}</span>
          </div>
          <div style="font-size:12px; color:#878787;">Order ID: ${order.id}</div>
        </div>

        ${isCancelled ? `
          <div style="background:#FFEBEE; border:1px solid #FFCDD2; border-radius:4px; padding:16px; color:#C62828; display:flex; align-items:center; gap:12px;">
            <span style="font-size:24px;">🚫</span>
            <div>
              <strong style="display:block;">This order was cancelled.</strong>
              <span style="font-size:13px;">As per your request, the order has been cancelled and any paid amount has been refunded to source.</span>
            </div>
          </div>
        ` : `
          <div class="fk-tracker-steps">
            <div class="fk-tracker-line">
              <div class="fk-tracker-line-fill"></div>
            </div>

            <div class="fk-tracker-step-item">
              <div class="fk-tracker-dot done">✓</div>
              <strong style="font-size:13px;">Ordered</strong>
              <span style="font-size:11px; color:#777;">Confirmed</span>
            </div>

            <div class="fk-tracker-step-item">
              <div class="fk-tracker-dot done">✓</div>
              <strong style="font-size:13px;">Packed</strong>
              <span style="font-size:11px; color:#777;">Seller Hub</span>
            </div>

            <div class="fk-tracker-step-item">
              <div class="fk-tracker-dot done">✓</div>
              <strong style="font-size:13px;">Shipped</strong>
              <span style="font-size:11px; color:#777;">Ekart Logistics</span>
            </div>

            <div class="fk-tracker-step-item">
              <div class="fk-tracker-dot done">✓</div>
              <strong style="font-size:13px; color:#388E3C;">Delivered</strong>
              <span style="font-size:11px; color:#388E3C; font-weight:700;">${order.date}</span>
            </div>
          </div>
        `}
      </div>

      <!-- Item Card & Price Summary -->
      <div style="background:#fff; border-radius:4px; padding:24px; box-shadow:var(--fk-shadow-sm); display:flex; gap:24px; align-items:center; flex-wrap:wrap;">
        <img src="${order.image}" style="width:100px; height:100px; object-fit:contain;" alt="${order.title}" />
        <div style="flex:1;">
          <h3 style="font-size:16px; font-weight:600; margin-bottom:6px;">${order.title}</h3>
          <div style="font-size:13px; color:#777; margin-bottom:4px;">${order.variant}</div>
          <div style="font-size:13px; color:#2874F0;">Seller: RetailNet (Flipkart Assured)</div>
          <div style="font-size:18px; font-weight:800; margin-top:8px;">${order.price === 0 ? 'FREE' : '₹' + order.price.toLocaleString('en-IN')}</div>
        </div>

        <div style="display:flex; flex-direction:column; gap:10px;">
          ${!isCancelled ? `
            <button class="fk-view-all-btn" style="padding:10px 20px; font-size:13px;" onclick="openReviewModal('${order.title.replace(/'/g, "\\'")}')">
              ★ Rate & Review Product
            </button>
          ` : ''}
          <button style="border:1px solid #E0E0E0; padding:10px 20px; font-size:13px; font-weight:700; border-radius:4px; background:#fff; cursor:pointer;" onclick="navigateTo('#/product/1')">
            Buy Again
          </button>
        </div>
      </div>
    </div>
  `;
}

function downloadInvoice(orderId) {
  alert(`📄 FLIPKART TAX INVOICE\n\nOrder ID: ${orderId}\nBuyer: ${state.user.name}\nGSTIN: 29AAFCS1234F1Z5\nInvoice Status: PAID (Original For Recipient)\n\nDownloading PDF Invoice to your downloads folder...`);
  showToast('Tax Invoice PDF downloaded successfully!');
}

// ==========================================================================
// 19. PAN CARD INFORMATION PAGE (`#/account/pan`)
// ==========================================================================
function renderPANPage(root) {
  root.innerHTML = `
    <div style="max-width:1360px; margin:16px auto 0 auto; padding:0 24px; font-size:12px; color:#878787;">
      <a href="#/" style="color:#2874F0;">Home</a> &gt; <a href="#/account" style="color:#2874F0;">My Account</a> &gt; <span>PAN Card Information</span>
    </div>

    <div class="fk-account-layout" style="margin-top:12px;">
      <!-- Left Sidebar -->
      <aside class="fk-acc-sidebar">
        <div class="fk-acc-user-card">
          <div class="fk-acc-avatar">👤</div>
          <div>
            <div class="fk-acc-greeting">Hello,</div>
            <div class="fk-acc-name">${state.user.name}</div>
          </div>
        </div>

        <div class="fk-acc-nav-box">
          <a class="fk-acc-nav-item" onclick="navigateTo('#/account')">Profile Information</a>
          <a class="fk-acc-nav-item" onclick="navigateTo('#/account/addresses')">Manage Addresses</a>
          <a class="fk-acc-nav-item active" onclick="navigateTo('#/account/pan')">PAN Card Information</a>
          <a class="fk-acc-nav-item" onclick="navigateTo('#/account/orders')">My Orders</a>
          <a class="fk-acc-nav-item" onclick="navigateTo('#/wishlist')">My Wishlist</a>
        </div>
      </aside>

      <!-- Right Content -->
      <div class="fk-acc-content-card">
        <div class="fk-profile-sec-header">
          <h2 class="fk-profile-title">PAN Card Information</h2>
          <span style="color:#388E3C; font-weight:700; font-size:13px;">✓ Verified by NSDL</span>
        </div>
        <p style="color:#777; font-size:13px; margin-bottom:20px;">Your PAN card information is securely saved for high-value transactions above ₹50,000 as per RBI regulations.</p>

        <div style="display:flex; flex-direction:column; gap:14px; max-width:480px;">
          <div>
            <label style="font-size:13px; font-weight:700; color:#333; display:block; margin-bottom:4px;">PAN Card Number</label>
            <input type="text" class="fk-profile-input" value="ABCDE1234F" placeholder="Enter 10-digit PAN" style="text-transform:uppercase; letter-spacing:1px;" />
          </div>

          <div>
            <label style="font-size:13px; font-weight:700; color:#333; display:block; margin-bottom:4px;">Full Name on PAN Card</label>
            <input type="text" class="fk-profile-input" value="${state.user.name}" placeholder="Full Name as per PAN" />
          </div>

          <div style="display:flex; align-items:center; gap:8px; font-size:12px; color:#666;">
            <input type="checkbox" checked />
            <span>I declare the PAN provided belongs to me and is valid.</span>
          </div>

          <button class="fk-view-all-btn" style="padding:12px; font-size:14px; width:160px;" onclick="showToast('PAN card details updated successfully!')">
            SAVE DETAILS
          </button>
        </div>
      </div>
    </div>
  `;
}

// ==========================================================================
// 20. SAVED PAYMENTS (UPI & CARDS) PAGE (`#/account/payments`)
// ==========================================================================
function renderPaymentsPage(root) {
  root.innerHTML = `
    <div style="max-width:1360px; margin:16px auto 0 auto; padding:0 24px; font-size:12px; color:#878787;">
      <a href="#/" style="color:#2874F0;">Home</a> &gt; <a href="#/account" style="color:#2874F0;">My Account</a> &gt; <span>Saved Payments</span>
    </div>

    <div class="fk-account-layout" style="margin-top:12px;">
      <!-- Left Sidebar -->
      <aside class="fk-acc-sidebar">
        <div class="fk-acc-user-card">
          <div class="fk-acc-avatar">👤</div>
          <div>
            <div class="fk-acc-greeting">Hello,</div>
            <div class="fk-acc-name">${state.user.name}</div>
          </div>
        </div>

        <div class="fk-acc-nav-box">
          <a class="fk-acc-nav-item" onclick="navigateTo('#/account')">Profile Information</a>
          <a class="fk-acc-nav-item" onclick="navigateTo('#/account/addresses')">Manage Addresses</a>
          <a class="fk-acc-nav-item" onclick="navigateTo('#/account/orders')">My Orders</a>
          <a class="fk-acc-nav-item active" onclick="navigateTo('#/account/payments')">Saved Payments (UPI & Cards)</a>
          <a class="fk-acc-nav-item" onclick="navigateTo('#/account/gift-cards')">Gift Cards</a>
        </div>
      </aside>

      <!-- Right Content -->
      <div class="fk-acc-content-card">
        <!-- Saved UPI -->
        <h2 class="fk-profile-title" style="margin-bottom:8px;">Saved UPI IDs</h2>
        <p style="color:#777; font-size:13px; margin-bottom:16px;">Pay effortlessly with 1-click UPI verification on Flipkart.</p>

        <div style="border:1px solid #E0E0E0; border-radius:6px; padding:16px 20px; display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
          <div style="display:flex; align-items:center; gap:12px;">
            <div style="font-size:24px;">📱</div>
            <div>
              <strong style="font-size:14px;">6382379565@okaxis</strong>
              <div style="font-size:12px; color:#388E3C; font-weight:700;">✓ Primary UPI ID (Verified)</div>
            </div>
          </div>
          <span style="color:#E41D2D; font-size:13px; font-weight:700; cursor:pointer;" onclick="showToast('Primary UPI cannot be deleted.')">Delete</span>
        </div>

        <!-- Saved Cards -->
        <h2 class="fk-profile-title" style="margin-bottom:8px;">Saved Cards</h2>
        <p style="color:#777; font-size:13px; margin-bottom:16px;">Securely saved cards as per RBI tokenization standards.</p>

        <div style="display:flex; flex-direction:column; gap:12px;">
          <div style="border:1px solid #E0E0E0; border-radius:6px; padding:16px 20px; display:flex; justify-content:space-between; align-items:center;">
            <div style="display:flex; align-items:center; gap:12px;">
              <div style="font-size:24px;">💳</div>
              <div>
                <strong style="font-size:14px;">HDFC Bank Platinum Credit Card</strong>
                <div style="font-size:12px; color:#777;">•••• •••• •••• 4812 | Expires 08/29</div>
              </div>
            </div>
            <span style="color:#2874F0; font-size:12px; font-weight:700;">✓ Tokenized</span>
          </div>

          <div style="border:1px solid #E0E0E0; border-radius:6px; padding:16px 20px; display:flex; justify-content:space-between; align-items:center;">
            <div style="display:flex; align-items:center; gap:12px;">
              <div style="font-size:24px;">💳</div>
              <div>
                <strong style="font-size:14px;">State Bank of India (SBI) RuPay Debit Card</strong>
                <div style="font-size:12px; color:#777;">•••• •••• •••• 9021 | Expires 11/30</div>
              </div>
            </div>
            <span style="color:#2874F0; font-size:12px; font-weight:700;">✓ Tokenized</span>
          </div>
        </div>
      </div>
    </div>
  `;
}

// ==========================================================================
// 21. 24x7 HELP CENTER & SUPPORT (`#/help`)
// ==========================================================================
function renderHelpCenterPage(root) {
  root.innerHTML = `
    <div style="background:#2874F0; color:#fff; padding:40px 24px; text-align:center;">
      <h1 style="font-size:28px; font-weight:800; margin-bottom:8px;">Flipkart 24x7 Help Center</h1>
      <p style="font-size:15px; opacity:0.95; margin-bottom:20px;">We are here to help you with your orders, returns, refunds, and queries.</p>
      <div style="max-width:600px; margin:0 auto; position:relative;">
        <input type="text" class="fk-search-input" style="padding:14px 20px; border-radius:4px; border:none; width:100%; font-size:14px;" placeholder="Search Help Topics (e.g. tracking, refund, cancel order)..." />
      </div>
    </div>

    <div style="max-width:1100px; margin:32px auto 60px auto; padding:0 24px;">
      <h2 style="font-size:18px; font-weight:800; margin-bottom:16px;">What issue are you facing?</h2>

      <div class="fk-help-grid">
        <div class="fk-help-tile" onclick="navigateTo('#/account/orders')">
          <div style="font-size:32px;">📦</div>
          <div>
            <strong style="font-size:14px; display:block;">I want to track my order</strong>
            <span style="font-size:12px; color:#777;">Check status & live tracking</span>
          </div>
        </div>

        <div class="fk-help-tile" onclick="navigateTo('#/account/orders')">
          <div style="font-size:32px;">🔄</div>
          <div>
            <strong style="font-size:14px; display:block;">I want to return an item</strong>
            <span style="font-size:12px; color:#777;">Return, replace, or exchange</span>
          </div>
        </div>

        <div class="fk-help-tile" onclick="navigateTo('#/account/payments')">
          <div style="font-size:32px;">💰</div>
          <div>
            <strong style="font-size:14px; display:block;">Help with Refunds</strong>
            <span style="font-size:12px; color:#777;">Check refund timeline to UPI/Bank</span>
          </div>
        </div>

        <div class="fk-help-tile" onclick="showToast('Flipkart 24x7 Support Toll-Free: 1800 202 9898')">
          <div style="font-size:32px;">📞</div>
          <div>
            <strong style="font-size:14px; display:block;">Call Customer Care</strong>
            <span style="font-size:12px; color:#777;">Toll-free 1800 202 9898</span>
          </div>
        </div>
      </div>

      <!-- FAQs -->
      <h2 style="font-size:18px; font-weight:800; margin:32px 0 16px 0;">Frequently Asked Questions</h2>
      <div style="background:#fff; border-radius:6px; border:1px solid #E0E0E0; padding:20px; display:flex; flex-direction:column; gap:16px;">
        <div style="border-bottom:1px solid #F0F0F0; padding-bottom:12px;">
          <strong style="font-size:14px; display:block; margin-bottom:4px;">How do I track my Flipkart shipment?</strong>
          <span style="font-size:13px; color:#555;">Go to My Orders, click on the order you placed, and you will see the 4-step delivery stepper with Ekart tracking updates.</span>
        </div>
        <div style="border-bottom:1px solid #F0F0F0; padding-bottom:12px;">
          <strong style="font-size:14px; display:block; margin-bottom:4px;">What is Flipkart 10-Minute Minutes delivery?</strong>
          <span style="font-size:13px; color:#555;">Flipkart Minutes delivers essentials, snacks, and electronics in 10-15 minutes from nearest hyperlocal fulfillment darkstores.</span>
        </div>
        <div>
          <strong style="font-size:14px; display:block; margin-bottom:4px;">How do SuperCoins work?</strong>
          <span style="font-size:13px; color:#555;">You earn 2 SuperCoins for every ₹100 spent. Coins never expire for Plus members and can be used for extra discounts and food/OTT brand vouchers.</span>
        </div>
      </div>
    </div>
  `;
}


