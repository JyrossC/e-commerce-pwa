<?php
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/csrf.php';
require_once dirname(__DIR__, 2) . '/includes/validator.php';

Auth::requireAdminPage();
$pageTitle = 'Manage Products'; $activePage = 'admin';
include dirname(__DIR__, 2) . '/includes/header.php';
?>
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/admin.css">
<div class="container section">
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:var(--space-md);margin-bottom:var(--space-xl);">
    <div>
      <h1 style="font-size:var(--text-2xl);font-weight:800;">📦 Products</h1>
      <p style="color:var(--text-3);font-size:var(--text-sm);">Create, edit and delete products</p>
    </div>
    <button class="btn btn-primary" onclick="openModal()">+ Add Product</button>
  </div>

  <nav class="admin-nav">
    <a href="<?= APP_URL ?>/pages/admin/dashboard.php">📊 Dashboard</a>
    <a href="<?= APP_URL ?>/pages/admin/products.php" class="active">📦 Products</a>
    <a href="<?= APP_URL ?>/pages/admin/categories.php">🗂 Categories</a>
    <a href="<?= APP_URL ?>/pages/admin/orders.php">🧾 Orders</a>
    <a href="<?= APP_URL ?>/pages/admin/users.php">👥 Users</a>
  </nav>

  <!-- Search & Filter -->
  <div style="display:flex;gap:var(--space-sm);margin-bottom:var(--space-lg);flex-wrap:wrap;">
    <input type="search" id="prod-search" class="form-control" placeholder="Search products…" style="max-width:280px;height:38px;">
    <select id="cat-filter" class="filter-select">
      <option value="">All Categories</option>
    </select>
    <select id="stock-filter" class="filter-select">
      <option value="">All Stock</option>
      <option value="in">In Stock</option>
      <option value="low">Low Stock (≤5)</option>
      <option value="out">Out of Stock</option>
    </select>
  </div>

  <div id="prod-loading" style="text-align:center;padding:40px;"><div class="spinner"></div></div>
  <div id="prod-table-wrap" class="hidden" style="overflow-x:auto;">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th>
        </tr>
      </thead>
      <tbody id="prod-tbody"></tbody>
    </table>
    <div id="prod-pagination" class="pagination" style="margin-top:var(--space-lg);"></div>
  </div>
  <div id="prod-empty" class="empty-state hidden">
    <div class="empty-icon">📦</div>
    <h2 class="empty-title">No products found</h2>
    <button class="btn btn-primary" onclick="openModal()">Add First Product</button>
  </div>
</div>

<!-- Product Modal -->
<div class="modal-overlay hidden" id="prod-modal" onclick="if(event.target===this)closeModal()">
  <div class="modal-box">
    <div class="modal-header">
      <h2 class="modal-title" id="modal-title">Add Product</h2>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <div id="modal-alert" class="hidden"></div>
    <form id="prod-form" enctype="multipart/form-data" novalidate>
      <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= CSRF::token() ?>">
      <input type="hidden" id="prod-id" name="id" value="0">

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-md);">
        <div class="form-group" style="grid-column:1/-1;">
          <label class="form-label">Product Name *</label>
          <input type="text" id="f-name" name="name" class="form-control" placeholder="e.g. ProSound X5 Headphones" required>
        </div>
        <div class="form-group">
          <label class="form-label">Category *</label>
          <select id="f-cat" name="category_id" class="form-control" required>
            <option value="">Select category</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">SKU</label>
          <input type="text" id="f-sku" name="sku" class="form-control" placeholder="ELEC-001">
        </div>
        <div class="form-group">
          <label class="form-label">Price (TZS) *</label>
          <input type="number" id="f-price" name="price" class="form-control" placeholder="0.00" min="0" step="0.01" required>
        </div>
        <div class="form-group">
          <label class="form-label">Sale Price (TZS)</label>
          <input type="number" id="f-sale" name="sale_price" class="form-control" placeholder="Leave blank for no sale" min="0" step="0.01">
        </div>
        <div class="form-group">
          <label class="form-label">Stock Qty *</label>
          <input type="number" id="f-stock" name="stock" class="form-control" placeholder="0" min="0" required>
        </div>
        <div class="form-group" style="display:flex;align-items:center;gap:var(--space-md);padding-top:24px;">
          <label class="form-check">
            <input type="checkbox" id="f-featured" name="is_featured" value="1"> Featured product
          </label>
          <label class="form-check">
            <input type="checkbox" id="f-active" name="is_active" value="1" checked> Active
          </label>
        </div>
        <div class="form-group" style="grid-column:1/-1;">
          <label class="form-label">Description</label>
          <textarea id="f-desc" name="description" class="form-control" rows="3" placeholder="Describe the product…"></textarea>
        </div>
        <div class="form-group" style="grid-column:1/-1;">
          <label class="form-label">Product Image</label>
          <div style="display:flex;align-items:center;gap:var(--space-md);">
            <img id="img-preview" class="img-preview" src="https://fastly.picsum.photos/id/1/80/80.jpg" alt="Preview">
            <div>
              <label class="img-upload-label">
                📸 Upload Image
                <input type="file" id="f-img" name="image" accept="image/*" onchange="previewImg(this)">
              </label>
              <div style="margin-top:var(--space-sm);">
                <label class="form-label" style="font-size:var(--text-xs);">Or enter image URL:</label>
                <input type="url" id="f-imgurl" name="image_url" class="form-control" placeholder="https://example.com/image.jpg" style="height:36px;font-size:var(--text-xs);">
              </div>
            </div>
          </div>
        </div>
      </div>

      <div style="display:flex;gap:var(--space-md);justify-content:flex-end;margin-top:var(--space-lg);">
        <button type="button" class="btn btn-ghost" onclick="closeModal()">Cancel</button>
        <button type="submit" class="btn btn-primary" id="modal-save-btn">Save Product</button>
      </div>
    </form>
  </div>
</div>

<script>
const CSRF='<?= Validator::escape(CSRF::token()) ?>',APPURL='<?= APP_URL ?>';
function esc(s){const d=document.createElement('div');d.textContent=s;return d.innerHTML;}
function fmt(n){return 'TZS '+Number(n).toLocaleString('en-TZ',{minimumFractionDigits:2,maximumFractionDigits:2});}
let allCats=[],currentPage=1;

async function loadCategories(){
  const res=await fetch(`${APPURL}/api/categories/index.php`);
  const json=await res.json();
  if(json.success){
    allCats=json.data.categories;
    const sel=document.getElementById('f-cat'),filter=document.getElementById('cat-filter');
    allCats.forEach(c=>{
      sel.innerHTML+=`<option value="${c.id}">${esc(c.name)}</option>`;
      filter.innerHTML+=`<option value="${c.slug}">${esc(c.name)}</option>`;
    });
  }
}

async function loadProducts(page=1){
  currentPage=page;
  const loading=document.getElementById('prod-loading');
  const wrap=document.getElementById('prod-table-wrap');
  const empty=document.getElementById('prod-empty');
  const q=document.getElementById('prod-search').value.trim();
  const cat=document.getElementById('cat-filter').value;
  loading.style.display='block'; wrap.classList.add('hidden'); empty.classList.add('hidden');

  const params=new URLSearchParams({page,limit:20});
  if(q)params.set('q',q);
  if(cat)params.set('category',cat);

  const res=await fetch(`${APPURL}/api/products/index.php?${params}`);
  const json=await res.json();
  loading.style.display='none';
  if(!json.success){wrap.innerHTML=`<div class="alert alert-error">${json.message}</div>`;return;}

  let products=json.data.products;
  const stockF=document.getElementById('stock-filter').value;
  if(stockF==='in')  products=products.filter(p=>p.stock>5);
  if(stockF==='low') products=products.filter(p=>p.stock>0&&p.stock<=5);
  if(stockF==='out') products=products.filter(p=>p.stock===0);

  if(!products.length){empty.classList.remove('hidden');return;}
  wrap.classList.remove('hidden');

  document.getElementById('prod-tbody').innerHTML=products.map(p=>`
    <tr>
      <td><img src="${p.image||'https://fastly.picsum.photos/id/1/80/80.jpg'}" alt="" onerror="this.src='https://fastly.picsum.photos/id/1/80/80.jpg'"></td>
      <td><div style="font-weight:600;">${esc(p.name)}</div><div style="font-size:var(--text-xs);color:var(--text-3);">ID: ${p.id}</div></td>
      <td><span class="tag">${esc(p.category_name||'')}</span></td>
      <td>${p.sale_price?`<div style="font-weight:700;color:var(--primary);">${fmt(p.sale_price)}</div><div style="font-size:11px;text-decoration:line-through;color:var(--text-3);">${fmt(p.price)}</div>`:fmt(p.price)}</td>
      <td><span style="color:${p.stock===0?'var(--error)':p.stock<=5?'var(--warning)':'var(--success)'};font-weight:700;">${p.stock}</span></td>
      <td><span class="${p.is_active?'tag':'tag'}" style="background:${p.is_active?'rgba(16,185,129,0.15)':'rgba(239,68,68,0.15)'};color:${p.is_active?'var(--success)':'var(--error)'};">${p.is_active?'Active':'Inactive'}</span></td>
      <td style="white-space:nowrap;">
        <button class="btn btn-ghost btn-sm" onclick='editProduct(${JSON.stringify(p)})'>✏️ Edit</button>
        <button class="btn btn-danger btn-sm" onclick="deleteProduct(${p.id},'${esc(p.name)}')">🗑</button>
      </td>
    </tr>`).join('');

  const pg=json.data.pagination;
  const pag=document.getElementById('prod-pagination');
  if(pg.total_pages>1){
    let h=`<button class="page-btn" onclick="loadProducts(${pg.page-1})" ${pg.page===1?'disabled':''}>‹</button>`;
    for(let i=1;i<=pg.total_pages;i++) h+=`<button class="page-btn ${i===pg.page?'active':''}" onclick="loadProducts(${i})">${i}</button>`;
    h+=`<button class="page-btn" onclick="loadProducts(${pg.page+1})" ${pg.page===pg.total_pages?'disabled':''}>›</button>`;
    pag.innerHTML=h;
  } else { pag.innerHTML=''; }
}

function openModal(editData=null){
  document.getElementById('prod-modal').classList.remove('hidden');
  document.getElementById('modal-alert').className='hidden';
  document.getElementById('modal-title').textContent=editData?'Edit Product':'Add Product';
  const form=document.getElementById('prod-form');
  form.reset();
  document.getElementById('prod-id').value=editData?.id||0;
  if(editData){
    document.getElementById('f-name').value=editData.name||'';
    document.getElementById('f-cat').value=editData.category_id||'';
    document.getElementById('f-sku').value=editData.sku||'';
    document.getElementById('f-price').value=editData.price||'';
    document.getElementById('f-sale').value=editData.sale_price||'';
    document.getElementById('f-stock').value=editData.stock||0;
    document.getElementById('f-desc').value=editData.description||'';
    document.getElementById('f-featured').checked=!!parseInt(editData.is_featured);
    document.getElementById('f-active').checked=!!parseInt(editData.is_active);
    if(editData.image) document.getElementById('img-preview').src=editData.image;
  } else {
    document.getElementById('img-preview').src='https://fastly.picsum.photos/id/1/80/80.jpg';
    document.getElementById('f-active').checked=true;
  }
}

function closeModal(){ document.getElementById('prod-modal').classList.add('hidden'); }

function editProduct(p){ openModal(p); }

async function deleteProduct(id,name){
  if(!confirm(`Delete "${name}"? This action cannot be undone.`)) return;
  try{
    const res=await fetch(`${APPURL}/api/products/delete.php`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF},body:JSON.stringify({id,'<?= CSRF_TOKEN_NAME ?>':CSRF})});
    const json=await res.json();
    if(json.success){NovaMart.toast('success','Deleted',json.message);loadProducts(currentPage);}
    else NovaMart.toast('error','Error',json.message);
  }catch{NovaMart.toast('error','Error','Failed to delete product');}
}

function previewImg(input){
  if(input.files&&input.files[0]){
    const reader=new FileReader();
    reader.onload=e=>document.getElementById('img-preview').src=e.target.result;
    reader.readAsDataURL(input.files[0]);
  }
}

document.getElementById('prod-form').addEventListener('submit',async function(e){
  e.preventDefault();
  const btn=document.getElementById('modal-save-btn');
  const alertEl=document.getElementById('modal-alert');
  alertEl.className='hidden';
  btn.disabled=true; btn.textContent='Saving…';

  const id=parseInt(document.getElementById('prod-id').value);
  const url=id?`${APPURL}/api/products/update.php`:`${APPURL}/api/products/create.php`;

  // Use FormData to support file upload
  const fd=new FormData(this);
  fd.set('<?= CSRF_TOKEN_NAME ?>',CSRF);
  fd.set('is_featured',document.getElementById('f-featured').checked?1:0);
  fd.set('is_active',document.getElementById('f-active').checked?1:0);

  try{
    const res=await fetch(url,{method:'POST',body:fd,headers:{'X-CSRF-Token':CSRF}});
    const json=await res.json();
    if(json.success){
      NovaMart.toast('success',id?'Updated!':'Created!',json.message);
      closeModal(); loadProducts(currentPage);
    }else{
      alertEl.className='alert alert-error';
      alertEl.innerHTML=json.message+(json.errors?'<ul style="margin-top:4px;padding-left:20px;">'+Object.values(json.errors).flat().map(m=>`<li>${esc(m)}</li>`).join('')+'</ul>':'');
    }
  }catch{alertEl.className='alert alert-error';alertEl.textContent='Network error.';}
  finally{btn.disabled=false;btn.textContent='Save Product';}
});

// Live search
let searchTimer;
document.getElementById('prod-search').addEventListener('input',()=>{ clearTimeout(searchTimer); searchTimer=setTimeout(()=>loadProducts(1),400); });
document.getElementById('cat-filter').addEventListener('change',()=>loadProducts(1));
document.getElementById('stock-filter').addEventListener('change',()=>loadProducts(1));

loadCategories();
loadProducts();
</script>
<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
