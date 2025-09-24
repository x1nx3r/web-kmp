/**
 * TypeScript declaration file for klien-list-manager.js
 * This provides type information for better IDE support
 */

interface CompanyForm {
    nama: string;
    errors: Record<string, string>;
}

interface BranchForm {
    id: number | null;
    company_nama: string;
    cabang: string;
    no_hp: string;
    errors: Record<string, string>;
}

interface ConfirmModal {
    title: string;
    message: string;
    warning: string;
    confirmText: string;
    action: (() => void) | null;
}

interface KlienListData {
    // Search and filters
    search: string;
    location: string;
    sort: string;
    direction: string;
    
    // UI state
    openGroups: Set<string>;
    openBahanBaku: Set<string>;
    showDeleteModal: boolean;
    deleteKlienId: number | null;
    deleteKlienName: string;
    
    // CRUD state
    showCompanyModal: boolean;
    showBranchModal: boolean;
    showConfirmModal: boolean;
    editingCompany: string | null;
    editingBranch: number | null;
    
    // Form data
    companyForm: CompanyForm;
    branchForm: BranchForm;
    confirmModal: ConfirmModal;
    
    // Search timeout
    searchTimeout: number | null;

    // Methods
    init(): void;
    get uniqueCompanies(): string[];
    setupKeyboardShortcuts(): void;
    initializeFromUrl(): void;
    focusSearch(): void;
    closeAllModals(): void;
    toggleGroup(groupId: string): void;
    toggleBahanBaku(detailId: string): void;
    toggleDirection(): void;
    debounceSearch(): void;
    applyFilters(): void;
    deleteKlien(id: number, name: string): void;
    confirmDelete(): void;
    
    // Company operations
    openCompanyModal(): void;
    closeCompanyModal(): void;
    resetCompanyForm(): void;
    editCompany(nama: string): void;
    submitCompanyForm(): Promise<void>;
    deleteCompany(nama: string): void;
    performCompanyDelete(nama: string): Promise<void>;
    
    // Branch operations
    openBranchModal(): void;
    closeBranchModal(): void;
    resetBranchForm(): void;
    editBranch(id: number, nama: string, cabang: string, no_hp: string): void;
    submitBranchForm(): Promise<void>;
    deleteBranch(id: number, displayName: string): void;
    performBranchDelete(id: number): Promise<void>;
    
    // Confirmation modal
    closeConfirmModal(): void;
    confirmAction(): void;
    
    // Utility methods
    clearSearch(): void;
    clearFilters(): void;
}

declare function klienListData(): KlienListData;

export default klienListData;