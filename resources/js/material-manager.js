/**
 * Alpine.js material management data module
 * This module provides reactive data and methods for managing client-specific materials
 */

// Main data function for Alpine.js component
function materialManagerData() {
    return {
        // Modal state
        showMaterialModal: false,
        
        // Current context
        currentMaterial: null,
        currentKlien: null,
        
        // Form data
        materialForm: {
            id: null,
            nama: '',
            satuan: '',
            spesifikasi: '',
            harga_approved: '',
            status: 'aktif'
        },
        
        // Price history data
        priceHistory: [],

        // Modal management methods
        openMaterialModal(klien, material = null) {
            this.currentKlien = klien;
            this.currentMaterial = material;
            this.errors = {};
            
            if (material) {
                // Edit mode
                this.materialForm = {
                    id: material.id,
                    nama: material.nama,
                    satuan: material.satuan,
                    spesifikasi: material.spesifikasi || '',
                    harga_approved: material.harga_approved || '',
                    status: material.status
                };
            } else {
                // Add mode - reset form
                this.materialForm = {
                    id: null,
                    nama: '',
                    satuan: '',
                    spesifikasi: '',
                    harga_approved: '',
                    status: 'aktif'
                };
            }
            
            this.showMaterialModal = true;
        },

        closeMaterialModal() {
            this.showMaterialModal = false;
            this.currentMaterial = null;
            this.currentKlien = null;
            this.errors = {};
            this.materialForm = {
                id: null,
                nama: '',
                satuan: '',
                spesifikasi: '',
                harga_approved: '',
                status: 'aktif'
            };
        },

        // AJAX operations
        async saveMaterial() {
            this.loading = true;
            this.errors = {};
            
            try {
                const data = {
                    ...this.materialForm,
                    klien_id: this.currentKlien.id
                };

                const isEdit = this.materialForm.id;
                const url = isEdit 
                    ? `/api/klien-materials/${this.materialForm.id}`
                    : '/api/klien-materials';
                
                const method = isEdit ? 'PUT' : 'POST';
                
                const response = await this.makeRequest(url, method, data);
                
                if (response.success) {
                    this.showSuccessMessage(response.message);
                    this.closeMaterialModal();
                    // Reload page to update the table
                    window.location.reload();
                } else {
                    this.handleErrors(response.errors || { general: [response.message] });
                }
            } catch (error) {
                console.error('Save material error:', error);
                this.handleErrors({ general: ['Terjadi kesalahan sistem'] });
            } finally {
                this.loading = false;
            }
        },

        async deleteMaterial(material) {
            if (!confirm(`Hapus material "${material.nama}"?`)) return;
            
            this.loading = true;
            try {
                const response = await this.makeRequest(
                    `/api/klien-materials/${material.id}`, 
                    'DELETE'
                );
                
                if (response.success) {
                    this.showSuccessMessage(response.message);
                    window.location.reload();
                } else {
                    this.showErrorMessage(response.message);
                }
            } catch (error) {
                console.error('Delete material error:', error);
                this.showErrorMessage('Terjadi kesalahan sistem');
            } finally {
                this.loading = false;
            }
        },

        // Utility methods
        async makeRequest(url, method = 'GET', data = null) {
            const options = {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            };

            if (data && method !== 'GET') {
                options.body = JSON.stringify(data);
            }

            const response = await fetch(url, options);
            return await response.json();
        },

        handleErrors(errors) {
            this.errors = errors;
            if (errors.general) {
                this.showErrorMessage(errors.general[0]);
            }
        },

        showSuccessMessage(message) {
            // You can implement toast notifications here
            alert(`✅ ${message}`);
        },

        showErrorMessage(message) {
            // You can implement toast notifications here  
            alert(`❌ ${message}`);
        },

        // Form validation helpers
        hasError(field) {
            return this.errors[field] && this.errors[field].length > 0;
        },

        getError(field) {
            return this.hasError(field) ? this.errors[field][0] : '';
        },

        // Formatting helpers
        formatPrice(price) {
            if (!price) return '-';
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(price);
        }
    };
}

// Make the function globally available
window.materialManagerData = materialManagerData;