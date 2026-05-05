<!-- Edit Product Modal -->
<div id="editModal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-2xl max-w-lg w-full p-8 shadow-2xl relative">
        <button onclick="closeEditModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
            <i class="fas fa-times text-xl"></i>
        </button>
        <h3 class="text-2xl font-bold text-gray-800 mb-6">Edit Product</h3>
        <form action="process_product.php" method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="id" id="edit_id">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                    <input type="text" name="name" id="edit_name" required class="w-full px-4 py-2 rounded-lg border border-gray-200 outline-none focus:ring-2 focus:ring-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Barcode / QR</label>
                    <input type="text" name="barcode" id="edit_barcode" class="w-full px-4 py-2 rounded-lg border border-gray-200 outline-none focus:ring-2 focus:ring-amber-500">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select name="category_id" id="edit_category_id" required class="w-full px-4 py-2 rounded-lg border border-gray-200 outline-none focus:ring-2 focus:ring-amber-500">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Price</label>
                    <input type="number" name="price" id="edit_price" required class="w-full px-4 py-2 rounded-lg border border-gray-200 outline-none focus:ring-2 focus:ring-amber-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" id="edit_description" rows="3" class="w-full px-4 py-2 rounded-lg border border-gray-200 outline-none focus:ring-2 focus:ring-amber-500"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Product Image (Leave blank to keep current)</label>
                <input type="file" name="image" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
            </div>
            <div class="flex items-center space-x-2">
                <input type="checkbox" name="is_featured" id="edit_is_featured" class="w-4 h-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500">
                <label for="edit_is_featured" class="text-sm text-gray-700">Mark as Featured</label>
            </div>
            <button type="submit" class="w-full bg-amber-600 text-white py-3 rounded-lg font-semibold hover:bg-amber-700 transition-all shadow-lg">
                Update Product
            </button>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center p-4 z-[60]">
    <div class="bg-white rounded-2xl max-w-sm w-full p-8 shadow-2xl text-center">
        <div class="w-20 h-20 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-exclamation-triangle text-3xl"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-800 mb-2">Delete Product?</h3>
        <p class="text-gray-500 mb-8">This action cannot be undone. Are you sure you want to delete this product?</p>
        <div class="flex space-x-4">
            <button onclick="closeDeleteModal()" class="flex-1 px-4 py-3 rounded-lg border border-gray-200 text-gray-600 font-semibold hover:bg-gray-50 transition-colors">
                Cancel
            </button>
            <a id="confirmDeleteBtn" href="#" class="flex-1 px-4 py-3 rounded-lg bg-red-500 text-white font-semibold hover:bg-red-600 transition-colors shadow-lg shadow-red-200">
                Delete
            </a>
        </div>
    </div>
</div>

<script>
function openEditModal(id) {
    fetch('get_product.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_name').value = data.name;
            document.getElementById('edit_barcode').value = data.barcode || '';
            document.getElementById('edit_category_id').value = data.category_id;
            document.getElementById('edit_price').value = data.price;
            document.getElementById('edit_description').value = data.description;
            document.getElementById('edit_is_featured').checked = data.is_featured == 1;
            document.getElementById('editModal').classList.remove('hidden');
        });
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
    const scanner = document.getElementById('barcodeScanner');
    if (scanner) scanner.focus();
}

function openDeleteModal(id) {
    document.getElementById('confirmDeleteBtn').href = '?delete=' + id;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    const scanner = document.getElementById('barcodeScanner');
    if (scanner) scanner.focus();
}
</script>
