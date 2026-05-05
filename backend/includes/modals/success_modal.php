<!-- Success Modal -->
<div id="successModal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center p-4 z-[100]">
    <div class="bg-white rounded-2xl max-w-sm w-full p-8 shadow-2xl text-center animate-fadeIn">
        <div class="w-20 h-20 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-check-circle text-4xl"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-800 mb-2">Berhasil!</h3>
        <p id="successMessage" class="text-gray-500 mb-8"></p>
        <button onclick="closeSuccessModal()" class="w-full bg-emerald-500 text-white py-3 rounded-lg font-semibold hover:bg-emerald-600 transition-all shadow-lg shadow-emerald-100">
            Tutup
        </button>
    </div>
</div>

<script>
function showSuccess(message) {
    document.getElementById('successMessage').innerText = message;
    document.getElementById('successModal').classList.remove('hidden');
}

function closeSuccessModal() {
    document.getElementById('successModal').classList.add('hidden');
}
</script>
