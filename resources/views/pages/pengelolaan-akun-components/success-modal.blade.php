{{-- Modal Sukses Universal --}}
<div id="successModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex min-h-screen items-center justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        {{-- Background overlay --}}
        <div class="fixed inset-0  backdrop-blur-xs transition-opacity"></div>

        {{-- Modal container --}}
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
            {{-- Modal Header --}}
            <div class="bg-gradient-to-r from-green-50 to-green-100 px-6 py-4 border-b border-green-200">
                <div class="flex items-center justify-center">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-check text-white text-lg"></i>
                        </div>
                        <div class="text-center">
                            <h3 class="text-lg font-bold text-green-800" id="successModalTitle">Operasi Berhasil</h3>
                            <p class="text-sm text-green-600" id="successModalSubtitle">Aksi telah berhasil dilakukan</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Content --}}
            <div class="bg-white px-6 py-6">
                <div class="text-center space-y-4">
                    {{-- Success Icon Animation --}}
                    <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-100 mb-4">
                        <div class="success-checkmark">
                            <div class="check-icon">
                                <span class="icon-line line-tip"></span>
                                <span class="icon-line line-long"></span>
                                <div class="icon-circle"></div>
                                <div class="icon-fix"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Success Message --}}
                    <div>
                        <h4 class="text-lg font-semibold text-gray-900 mb-2" id="successModalMessage">Operasi berhasil dilakukan!</h4>
                        <p class="text-sm text-gray-600" id="successModalDescription">Data telah disimpan dengan baik ke sistem.</p>
                    </div>

                    {{-- Additional Info (for password reset, etc) --}}
                    <div id="successModalInfo" class="hidden bg-blue-50 rounded-lg p-3 border border-blue-200">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-blue-500 text-sm"></i>
                            </div>
                            <div class="ml-2">
                                <p class="text-sm text-blue-800" id="successModalInfoText"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="bg-gray-50 px-6 py-3 border-t border-gray-200 flex justify-center">
                <button type="button" onclick="closeSuccessModal()" class="px-6 py-2 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-lg transition-colors focus:ring-2 focus:ring-green-200">
                    <i class="fas fa-check mr-2"></i>
                    OK
                </button>
            </div>
        </div>
    </div>
</div>

{{-- CSS for Success Animation --}}
<style>
.success-checkmark {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: block;
    stroke-width: 3;
    stroke: #10b981;
    stroke-miterlimit: 10;
    box-shadow: inset 0px 0px 0px #10b981;
    animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;
    position: relative;
}

.success-checkmark .icon-circle {
    stroke-dasharray: 166;
    stroke-dashoffset: 166;
    stroke-width: 3;
    stroke-miterlimit: 10;
    stroke: #10b981;
    fill: none;
    animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
}

.success-checkmark .check-icon {
    width: 50px;
    height: 50px;
    position: relative;
    border-radius: 50%;
    box-sizing: border-box;
    border: 3px solid #10b981;
    background: white;
}

.success-checkmark .icon-line {
    height: 3px;
    background-color: #10b981;
    display: block;
    border-radius: 2px;
    position: absolute;
    z-index: 10;
}

.success-checkmark .icon-line.line-tip {
    top: 23px;
    left: 14px;
    width: 8px;
    transform: rotate(45deg);
    animation: icon-line-tip 0.75s;
}

.success-checkmark .icon-line.line-long {
    top: 20px;
    right: 8px;
    width: 18px;
    transform: rotate(-45deg);
    animation: icon-line-long 0.75s;
}

.success-checkmark .icon-circle {
    top: -3px;
    left: -3px;
    z-index: 10;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    position: absolute;
    box-sizing: content-box;
    border: 3px solid #10b981;
}

.success-checkmark .icon-fix {
    top: 10px;
    width: 7px;
    height: 90px;
    position: absolute;
    left: 26px;
    z-index: 1;
    background-color: white;
    transform: rotate(-45deg);
}

@keyframes stroke {
    100% {
        stroke-dashoffset: 0;
    }
}

@keyframes scale {
    0%, 100% {
        transform: none;
    }
    50% {
        transform: scale3d(1.1, 1.1, 1);
    }
}

@keyframes fill {
    100% {
        box-shadow: inset 0px 0px 0px 30px #10b981;
    }
}

@keyframes icon-line-tip {
    0% {
        width: 0;
        left: 1px;
        top: 19px;
    }
    54% {
        width: 0;
        left: 1px;
        top: 19px;
    }
    70% {
        width: 8px;
        left: 14px;
        top: 23px;
    }
    84% {
        width: 10px;
        left: 14px;
        top: 23px;
    }
    100% {
        width: 8px;
        left: 14px;
        top: 23px;
    }
}

@keyframes icon-line-long {
    0% {
        width: 0;
        right: 46px;
        top: 26px;
    }
    65% {
        width: 0;
        right: 46px;
        top: 26px;
    }
    84% {
        width: 18px;
        right: 8px;
        top: 20px;
    }
    100% {
        width: 18px;
        right: 8px;
        top: 20px;
    }
}

/* Auto close animation */
.success-modal-auto-close {
    animation: successAutoClose 3s ease-in-out forwards;
}

@keyframes successAutoClose {
    0% { opacity: 1; transform: scale(1); }
    85% { opacity: 1; transform: scale(1); }
    100% { opacity: 0; transform: scale(0.95); }
}
</style>

<script>
// Function to show success modal
function showSuccessModal(type, message, description = '', additionalInfo = '', autoClose = true) {
    const modal = document.getElementById('successModal');
    const title = document.getElementById('successModalTitle');
    const subtitle = document.getElementById('successModalSubtitle');
    const messageEl = document.getElementById('successModalMessage');
    const descriptionEl = document.getElementById('successModalDescription');
    const infoEl = document.getElementById('successModalInfo');
    const infoTextEl = document.getElementById('successModalInfoText');

    // Configure modal based on type
    switch (type) {
        case 'create':
        case 'tambah':
            title.textContent = 'Akun Berhasil Dibuat';
            subtitle.textContent = 'Pengguna baru telah ditambahkan';
            messageEl.textContent = message || 'Akun pengguna berhasil dibuat!';
            descriptionEl.textContent = description || 'Data pengguna baru telah disimpan ke sistem.';
            break;
        case 'edit':
        case 'update':
            title.textContent = 'Akun Berhasil Diperbarui';
            subtitle.textContent = 'Perubahan telah disimpan';
            messageEl.textContent = message || 'Data pengguna berhasil diperbarui!';
            descriptionEl.textContent = description || 'Semua perubahan telah disimpan ke sistem.';
            break;
        case 'delete':
        case 'hapus':
            title.textContent = 'Akun Berhasil Dihapus';
            subtitle.textContent = 'Pengguna telah dihapus';
            messageEl.textContent = message || 'Akun pengguna berhasil dihapus!';
            descriptionEl.textContent = description || 'Data pengguna telah dihapus dari sistem.';
            break;
        default:
            title.textContent = 'Operasi Berhasil';
            subtitle.textContent = 'Aksi telah berhasil dilakukan';
            messageEl.textContent = message || 'Operasi berhasil dilakukan!';
            descriptionEl.textContent = description || 'Data telah diproses dengan baik.';
    }

    // Show additional info if provided
    if (additionalInfo) {
        infoTextEl.textContent = additionalInfo;
        infoEl.classList.remove('hidden');
    } else {
        infoEl.classList.add('hidden');
    }

    // Show modal
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');

    // Auto close after 3 seconds if enabled
    if (autoClose) {
        setTimeout(() => {
            modal.classList.add('success-modal-auto-close');
            setTimeout(() => {
                closeSuccessModal();
            }, 300); // Wait for animation to complete
        }, 2700); // Start fade out after 2.7 seconds
    }
}

// Function to close success modal
function closeSuccessModal() {
    const modal = document.getElementById('successModal');
    modal.classList.add('hidden');
    modal.classList.remove('success-modal-auto-close');
    document.body.classList.remove('overflow-hidden');
    
    // Reload page after closing modal
    setTimeout(() => {
        window.location.reload();
    }, 300);
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    if (e.target && e.target.id === 'successModal') {
        closeSuccessModal();
    }
});

// Close modal when ESC key is pressed
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('successModal').classList.contains('hidden')) {
        closeSuccessModal();
    }
});
</script>
