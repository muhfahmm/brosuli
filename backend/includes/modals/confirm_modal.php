<!-- Confirm Modal -->
<div id="confirmModal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center p-4 z-[100]">
    <div class="bg-white rounded-2xl max-w-sm w-full p-8 shadow-2xl text-center animate-fadeIn">
        <div class="w-20 h-20 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-question-circle text-4xl"></i>
        </div>
        <h3 id="confirmTitle" class="text-xl font-bold text-gray-800 mb-2">Konfirmasi</h3>
        <p id="confirmMessage" class="text-gray-500 mb-8"></p>
        <div class="flex space-x-3">
            <button onclick="closeConfirmModal()" class="flex-1 px-4 py-3 rounded-lg border border-gray-200 text-gray-600 font-semibold hover:bg-gray-50 transition-colors">
                Batal
            </button>
            <button id="confirmActionBtn" class="flex-1 px-4 py-3 rounded-lg bg-amber-600 text-white font-semibold hover:bg-amber-700 transition-all shadow-lg shadow-amber-100">
                Ya, Lanjutkan
            </button>
        </div>
    </div>
</div>

<script>
let currentConfirmCallback = null;

function showConfirm(title, message, callback) {
    document.getElementById('confirmTitle').innerText = title;
    document.getElementById('confirmMessage').innerText = message;
    currentConfirmCallback = callback;
    document.getElementById('confirmModal').classList.remove('hidden');
}

function closeConfirmModal() {
    document.getElementById('confirmModal').classList.add('hidden');
}

document.getElementById('confirmActionBtn').addEventListener('click', function() {
    if (currentConfirmCallback) {
        currentConfirmCallback();
    }
    closeConfirmModal();
});
</script>
