<!-- Error Modal -->
<div id="errorModal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center p-4 z-[100]">
    <div class="bg-white rounded-2xl max-w-sm w-full p-8 shadow-2xl text-center animate-fadeIn">
        <div class="w-20 h-20 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-exclamation-circle text-4xl"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-800 mb-2">Gagal!</h3>
        <p id="errorMessage" class="text-gray-500 mb-8"></p>
        <button onclick="closeErrorModal()" class="w-full bg-rose-500 text-white py-3 rounded-lg font-semibold hover:bg-rose-600 transition-all shadow-lg shadow-rose-100">
            Tutup
        </button>
    </div>
</div>

<script>
function showError(message) {
    document.getElementById('errorMessage').innerText = message;
    document.getElementById('errorModal').classList.remove('hidden');
}

function closeErrorModal() {
    document.getElementById('errorModal').classList.add('hidden');
}
</script>
