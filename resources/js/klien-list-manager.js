/**
 * Alpine.js klien list data manager module
 * This module provides reactive data and methods for managing the client list interface
 */

// Main data function for Alpine.js component
function klienListData() {
    return {
        // Search and filters
        search: window.klienPageData?.search || '',
        location: window.klienPageData?.location || '',
        sort: window.klienPageData?.sort || 'nama',
        direction: window.klienPageData?.direction || 'asc',
        
        // UI state
        openGroups: new Set(),
        openBahanBaku: new Set(),
        showDeleteModal: false,
        deleteKlienId: null,
        deleteKlienName: '',
        
        // CRUD state
        showCompanyModal: false,
        showBranchModal: false,
        showConfirmModal: false,
        editingCompany: null,
        editingBranch: null,
        
        // Form data
        companyForm: {
            nama: '',
            errors: {}
        },
        branchForm: {
            id: null,
            company_nama: '',
            cabang: '',
            no_hp: '',
            errors: {}
        },
        
        // Confirmation modal
        confirmModal: {
            title: '',
            message: '',
            warning: '',
            confirmText: 'Hapus',
            action: null
        },
        
        // Search timeout
        searchTimeout: null,

        // Initialize component
        init() {
            // Set up keyboard shortcuts
            this.setupKeyboardShortcuts();
            
            // Initialize from URL parameters if available
            this.initializeFromUrl();
        },

        // Computed property for unique companies
        get uniqueCompanies() {
            const companies = window.klienData ? 
                [...new Set(window.klienData.map(k => k.nama))].sort() : 
                [];
            return companies;
        },

        // Setup keyboard shortcuts
        setupKeyboardShortcuts() {
            document.addEventListener('keydown', (e) => {
                // Escape key to close modals
                if (e.key === 'Escape') {
                    this.closeAllModals();
                }
                
                // Ctrl/Cmd + K to focus search
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    this.focusSearch();
                }
            });
        },

        // Initialize from URL parameters
        initializeFromUrl() {
            const params = new URLSearchParams(window.location.search);
            this.search = params.get('search') || '';
            this.location = params.get('location') || '';
            this.sort = params.get('sort') || 'nama';
            this.direction = params.get('direction') || 'asc';
        },

        // Focus search input
        focusSearch() {
            const searchInput = document.querySelector('input[x-model="search"]');
            if (searchInput) {
                searchInput.focus();
            }
        },

        // Close all modals
        closeAllModals() {
            this.showDeleteModal = false;
            this.closeCompanyModal();
            this.closeBranchModal();
            this.closeConfirmModal();
        },

        // Toggle group dropdown
        toggleGroup(groupId) {
            if (this.openGroups.has(groupId)) {
                this.openGroups.delete(groupId);
            } else {
                this.openGroups.add(groupId);
            }
        },

        // Toggle bahan baku section
        toggleBahanBaku(detailId) {
            if (this.openBahanBaku.has(detailId)) {
                this.openBahanBaku.delete(detailId);
            } else {
                this.openBahanBaku.add(detailId);
            }
        },

        // Toggle sort direction
        toggleDirection() {
            this.direction = this.direction === 'asc' ? 'desc' : 'asc';
            this.applyFilters();
        },

        // Debounced search
        debounceSearch() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.applyFilters();
            }, 500);
        },

        // Apply filters and navigate
        applyFilters() {
            const params = new URLSearchParams();
            
            if (this.search.trim()) params.append('search', this.search.trim());
            if (this.location) params.append('location', this.location);
            if (this.sort) params.append('sort', this.sort);
            if (this.direction) params.append('direction', this.direction);
            
            const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
            window.location.href = newUrl;
        },

        // Delete functionality (existing)
        deleteKlien(id, name) {
            this.deleteKlienId = id;
            this.deleteKlienName = name;
            this.showDeleteModal = true;
        },

        // Confirm delete action (existing)
        confirmDelete() {
            if (!this.deleteKlienId) {
                alert('Tidak ada klien yang dipilih');
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/klien/${this.deleteKlienId}`;
            form.style.display = 'none';

            // Add CSRF token
            const csrf = document.querySelector('meta[name="csrf-token"]');
            if (csrf) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = '_token';
                input.value = csrf.getAttribute('content');
                form.appendChild(input);
            }

            // Add method spoofing for DELETE
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);

            document.body.appendChild(form);
            form.submit();
        },

        // Company CRUD operations
        openCompanyModal() {
            this.resetCompanyForm();
            this.showCompanyModal = true;
        },

        closeCompanyModal() {
            this.showCompanyModal = false;
            this.editingCompany = null;
            this.resetCompanyForm();
        },

        resetCompanyForm() {
            this.companyForm = {
                nama: '',
                errors: {}
            };
        },

        editCompany(nama) {
            this.editingCompany = nama;
            this.companyForm.nama = nama;
            this.showCompanyModal = true;
        },

        async submitCompanyForm() {
            this.companyForm.errors = {};

            if (!this.companyForm.nama.trim()) {
                this.companyForm.errors.nama = 'Nama perusahaan wajib diisi';
                return;
            }

            try {
                const isEdit = this.editingCompany !== null;
                const url = isEdit ? '/klien/company/update' : '/klien/company/store';

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                if (!csrfToken) {
                    alert('CSRF token tidak ditemukan. Silakan refresh halaman.');
                    return;
                }

                const formData = new FormData();
                formData.append('_token', csrfToken);
                if (isEdit) {
                    formData.append('_method', 'PUT');
                    formData.append('old_nama', this.editingCompany);
                }
                formData.append('nama', this.companyForm.nama.trim());

                const response = await fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                let result;
                const contentType = response.headers.get('content-type');
                
                if (contentType && contentType.includes('application/json')) {
                    result = await response.json();
                } else {
                    // Handle non-JSON responses gracefully
                    if (response.status === 302 || response.ok) {
                        result = { success: true, message: 'Perusahaan berhasil disimpan' };
                    } else {
                        result = { success: false, message: 'Respons server tidak dalam format JSON' };
                    }
                }

                if (result.success) {
                    this.closeCompanyModal();
                    window.location.reload();
                } else {
                    this.companyForm.errors = result.errors || {};
                    if (result.message) {
                        this.companyForm.errors.nama = result.message;
                    }
                }
            } catch (error) {
                this.companyForm.errors.nama = 'Terjadi kesalahan saat menyimpan data';
            }
        },

        deleteCompany(nama) {
            this.confirmModal = {
                title: 'Hapus Perusahaan',
                message: `Anda yakin ingin menghapus perusahaan "${nama}"?`,
                warning: 'Semua cabang dari perusahaan ini akan ikut terhapus.',
                confirmText: 'Hapus',
                action: () => this.performCompanyDelete(nama)
            };
            this.showConfirmModal = true;
        },

        async performCompanyDelete(nama) {
            try {
                const formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                formData.append('_method', 'DELETE');
                formData.append('nama', nama);

                const response = await fetch('/klien/company/destroy', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                if (result.success) {
                    this.closeConfirmModal();
                    window.location.reload();
                } else {
                    alert(result.message || 'Terjadi kesalahan saat menghapus data');
                    this.closeConfirmModal();
                }
            } catch (error) {
                alert('Terjadi kesalahan saat menghapus data');
                this.closeConfirmModal();
            }
        },

        // Branch CRUD operations
        openBranchModal() {
            this.resetBranchForm();
            this.showBranchModal = true;
        },

        closeBranchModal() {
            this.showBranchModal = false;
            this.editingBranch = null;
            this.resetBranchForm();
        },

        resetBranchForm() {
            this.branchForm = {
                id: null,
                company_nama: '',
                cabang: '',
                no_hp: '',
                errors: {}
            };
        },

        editBranch(id, nama, cabang, no_hp) {
            this.editingBranch = id;
            this.branchForm = {
                id: id,
                company_nama: nama,
                cabang: cabang,
                no_hp: no_hp || '',
                errors: {}
            };
            this.showBranchModal = true;
        },

        async submitBranchForm() {
            this.branchForm.errors = {};

            if (!this.branchForm.company_nama.trim()) {
                this.branchForm.errors.company_nama = 'Perusahaan wajib dipilih';
                return;
            }

            if (!this.branchForm.cabang.trim()) {
                this.branchForm.errors.cabang = 'Lokasi cabang wajib diisi';
                return;
            }

            try {
                const isEdit = this.editingBranch !== null;
                const url = isEdit ? `/klien/${this.branchForm.id}` : '/klien';

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                if (!csrfToken) {
                    alert('CSRF token tidak ditemukan. Silakan refresh halaman.');
                    return;
                }

                const formData = new FormData();
                formData.append('_token', csrfToken);
                if (isEdit) {
                    formData.append('_method', 'PUT');
                }
                formData.append('nama', this.branchForm.company_nama.trim());
                formData.append('cabang', this.branchForm.cabang.trim());
                formData.append('no_hp', this.branchForm.no_hp.trim());

                const response = await fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                let result;
                const contentType = response.headers.get('content-type');
                
                if (contentType && contentType.includes('application/json')) {
                    result = await response.json();
                } else {
                    // Handle non-JSON responses gracefully
                    if (response.status === 302 || response.ok) {
                        result = { success: true, message: 'Cabang berhasil disimpan' };
                    } else {
                        result = { success: false, message: 'Respons server tidak dalam format JSON' };
                    }
                }

                if (result.success) {
                    this.closeBranchModal();
                    window.location.reload();
                } else {
                    this.branchForm.errors = result.errors || {};
                    if (result.message) {
                        this.branchForm.errors.cabang = result.message;
                    }
                }
            } catch (error) {
                this.branchForm.errors.cabang = 'Terjadi kesalahan saat menyimpan data';
            }
        },

        deleteBranch(id, displayName) {
            this.confirmModal = {
                title: 'Hapus Cabang',
                message: `Anda yakin ingin menghapus cabang "${displayName}"?`,
                warning: 'Data cabang yang dihapus tidak dapat dikembalikan.',
                confirmText: 'Hapus',
                action: () => this.performBranchDelete(id)
            };
            this.showConfirmModal = true;
        },

        async performBranchDelete(id) {
            try {
                const formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                formData.append('_method', 'DELETE');

                const response = await fetch(`/klien/${id}`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                if (result.success) {
                    this.closeConfirmModal();
                    window.location.reload();
                } else {
                    alert(result.message || 'Terjadi kesalahan saat menghapus data');
                    this.closeConfirmModal();
                }
            } catch (error) {
                alert('Terjadi kesalahan saat menghapus data');
                this.closeConfirmModal();
            }
        },

        // Confirmation modal
        closeConfirmModal() {
            this.showConfirmModal = false;
            this.confirmModal = {
                title: '',
                message: '',
                warning: '',
                confirmText: 'Hapus',
                action: null
            };
        },

        confirmAction() {
            if (this.confirmModal.action) {
                this.confirmModal.action();
            }
        },

        // Utility methods
        clearSearch() {
            this.search = '';
            this.applyFilters();
        },

        clearFilters() {
            this.search = '';
            this.location = '';
            this.sort = 'nama';
            this.direction = 'asc';
            this.applyFilters();
        }
    }
}

// Export the main function as default export for ES6 module compatibility
export default klienListData;

// Also make it available globally for direct script usage (backwards compatibility)
if (typeof window !== 'undefined') {
    window.klienListData = klienListData;
}