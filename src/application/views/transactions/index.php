<div id="transactions-app">
  <!-- Modal ویرایش -->
  <div class="modal fade" id="editTransactionModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header d-flex justify-content-between align-items-center">
          <h5 class="modal-title">ویرایش تراکنش</h5>
          <button type="button" class="btn-close ms-0" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div v-if="loadingEdit" class="text-center py-3">در حال بارگذاری...</div>
          <form @submit.prevent="updateTransaction">
            <div class="row g-3">
              <div class="col-md-6">
                <label>نوع</label>
                <select v-model="editForm.type" class="form-select" @change="onEditTypeChange" required>
                  <option value="income">درآمد</option>
                  <option value="expense">هزینه</option>
                </select>
              </div>
              <div class="col-md-6">
                <label>دسته‌بندی</label>
                <select v-model="editForm.category_id" class="form-select" required>
                  <option v-for="cat in editCategories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>
              </div>
              <div class="col-md-6">
                <label>مبلغ (تومان)</label>
                <input v-model.number="editForm.amount" type="number" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label>تاریخ</label>
                <input v-model="editForm.transaction_date" type="text" class="form-control persia-datepicker-edit" readonly>
              </div>
              <div class="col-md-6">
                <label>روش پرداخت</label>
                <select v-model="editForm.payment_method" class="form-select">
                  <option value="cash">نقدی</option>
                  <option value="card">کارت</option>
                  <option value="online">آنلاین</option>
                  <option value="cheque">چک</option>
                </select>
              </div>
              <div class="col-md-6">
                <label>شماره مرجع</label>
                <input v-model="editForm.reference" type="text" class="form-control">
              </div>
              <div class="col-12">
                <label>توضیحات</label>
                <textarea v-model="editForm.description" class="form-control" rows="2"></textarea>
              </div>
            </div>
            <button type="submit" class="btn btn-success mt-3 w-100">بروزرسانی تراکنش</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- فیلترها -->
  <div class="card mb-4">
    <div class="card-body">
      <form @submit.prevent="fetchTransactions" class="row g-3">
        <div class="col-md-4">
          <label class="form-label">توضیحات</label>
          <input v-model="filters.search" type="text" class="form-control" placeholder="مثلاً خرید...">
        </div>
        <div class="col-md-2">
          <label class="form-label">نوع تراکنش</label>
          <select v-model="filters.type" class="form-select" @change="onFilterTypeChange">
            <option value="">همه</option>
            <option value="income">درآمد</option>
            <option value="expense">هزینه</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">دسته‌بندی</label>
          <select v-model="filters.category_id" class="form-select">
            <option value="">همه</option>
            <option v-for="cat in allCategories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">از تاریخ</label>
          <input v-model="filters.from" type="text" class="form-control persia-datepicker" readonly>
        </div>
        <div class="col-md-2">
          <label class="form-label">تا تاریخ</label>
          <input v-model="filters.to" type="text" class="form-control persia-datepicker" readonly>
        </div>
        <div class="col-12 text-end d-flex ">
          <button type="submit" class="btn btn-primary ms-2">جستجو</button>
          <button type="button" @click="resetFilters" class="btn btn-secondary">حذف فیلتر</button>
        </div>
      </form>
    </div>
  </div>  

  <!-- نمایش تراکنش‌ها -->
  <div v-if="loading" class="text-center py-3">در حال بارگذاری...</div>
  <div v-else-if="transactions.length === 0" class="text-center py-4">
    <i class="bi bi-receipt display-4 text-muted"></i>
    <p class="text-muted mt-2">تراکنشی یافت نشد</p>
  </div>
  <table v-else class="table table-striped table-bordered">
    <thead class="table-primary">
      <tr>
        <th>تاریخ</th>
        <th>دسته‌بندی</th>
        <th>توضیحات</th>
        <th>مبلغ</th>
        <th>نوع</th>
        <th>عملیات</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="tx in transactions" :key="tx.id">
        <td>{{ tx.transaction_date }}</td>
        <td>{{ tx.category_name }}</td>
        <td>{{ tx.description || '' }}</td>
        <td :class="tx.type === 'income' ? 'text-success' : 'text-danger'">{{ formatNumber(tx.amount) }} تومان</td>
        <td>{{ tx.type === 'income' ? 'درآمد' : 'هزینه' }}</td>
        <td>
          <button @click="openEditModal(tx.id)" class="btn btn-sm btn-warning me-2 ms-2">
            <i class="bi bi-pencil-square"></i>
          </button>
          <button @click="deleteTransaction(tx.id)" class="btn btn-sm btn-danger">
            <i class="bi bi-trash3"></i>
          </button>
        </td>
      </tr>
    </tbody>
  </table>

  <!-- صفحه‌بندی -->
  <nav v-if="totalPages > 1" class="d-flex justify-content-center mt-3">
    <ul class="pagination">
      <li class="page-item" :class="{ disabled: currentPage === 1 }">
        <button @click="changePage(currentPage - 1)" class="page-link">قبلی</button>
      </li>
      <li v-for="page in totalPages" :key="page" class="page-item" :class="{ active: page === currentPage }">
        <button @click="changePage(page)" class="page-link">{{ page }}</button>
      </li>
      <li class="page-item" :class="{ disabled: currentPage === totalPages }">
        <button @click="changePage(currentPage + 1)" class="page-link">بعدی</button>
      </li>
    </ul>
  </nav>
</div>

<script>
const { createApp, ref, onMounted, nextTick } = Vue;

createApp({
  setup() {
    const transactions = ref([]);
    const loading = ref(false);
    const currentPage = ref(1);
    const totalPages = ref(1);
    const filters = ref({ search: '', type: '', from: '', to: '', category_id: '' });
    const allCategories = ref([]);

    const editForm = ref({
      id: null, type: '', category_id: '', amount: '', transaction_date: '', 
      payment_method: 'cash', reference: '', description: ''
    });
    const editCategories = ref([]);
    const loadingEdit = ref(false);

    const formatNumber = num => new Intl.NumberFormat('fa-IR').format(num);

    // دریافت تراکنش‌ها
    const fetchTransactions = async (page = 1) => {
      loading.value = true;
      try {
        const params = { ajax: 1, page, ...filters.value };
        Object.keys(params).forEach(k => { if (!params[k]) delete params[k]; });

        const res = await axios.get('/transactions', { params });
        const data = res.data;
        transactions.value = Array.isArray(data) ? data : data.transactions || data.data || [];
        totalPages.value = data.totalPages || Math.ceil((data.total || transactions.value.length) / (data.perPage || 10));
        currentPage.value = page;
      } catch (err) {
        Swal.fire('خطا!', err.response?.data?.message || 'خطا در بارگذاری تراکنش‌ها.', 'error');
        transactions.value = [];
        totalPages.value = 1;
      } finally { loading.value = false; }
    };

    // دریافت دسته‌بندی‌ها (برای فیلتر)
    const fetchAllCategories = async (type = null) => {
      try {
        const res = await axios.get('/transactions/get_categories', { params: { type } });
        allCategories.value = res.data || [];
        // پاک کردن دسته‌بندی نامرتبط
        if (filters.value.category_id && !allCategories.value.some(c => c.id == filters.value.category_id))
          filters.value.category_id = '';
      } catch (err) { allCategories.value = []; }
    };

    const onFilterTypeChange = () => fetchAllCategories(filters.value.type);

    // بارگذاری دسته‌بندی‌ها برای modal ویرایش
    const loadEditCategories = async (type) => {
      try {
        const res = await axios.get('/transactions/get_categories', { params: { type } });
        editCategories.value = res.data || [];
        if (editForm.value.category_id && !editCategories.value.some(c => c.id == editForm.value.category_id))
          editForm.value.category_id = '';
      } catch (err) { editCategories.value = []; }
    };

    const onEditTypeChange = () => loadEditCategories(editForm.value.type);

    const openEditModal = async (id) => {
      loadingEdit.value = true;
      try {
        const res = await axios.get(`/transactions/api/${id}`);
        const t = res.data;
        editForm.value = { ...t, category_id: String(t.category_id), payment_method: t.payment_method || 'cash', reference: t.reference || '', description: t.description || '' };
        await loadEditCategories(t.type);
        nextTick(() => {
          const input = document.querySelector('.persia-datepicker-edit');
          if (input) $(input).persianDatepicker({ format: 'YYYY/MM/DD', autoClose: true, calendar: { persian: { leapYearMode: 'astronomical' } }, onSelect: unix => { editForm.value.transaction_date = new persianDate(unix).format('YYYY/MM/DD'); input.value = editForm.value.transaction_date; } });
        });
        new bootstrap.Modal(document.getElementById('editTransactionModal')).show();
      } finally { loadingEdit.value = false; }
    };

    const updateTransaction = async () => {
      try {
        await axios.post(`/transactions/update/${editForm.value.id}`, editForm.value);
        Swal.fire('موفق!', 'تراکنش با موفقیت بروزرسانی شد.', 'success');
        bootstrap.Modal.getInstance(document.getElementById('editTransactionModal')).hide();
        fetchTransactions(currentPage.value);
      } catch (err) { Swal.fire('خطا!', err.response?.data?.message || 'خطا در به‌روزرسانی.', 'error'); }
    };

    const deleteTransaction = async (id) => {
      const result = await Swal.fire({ title: 'آیا مطمئن هستید؟', text: 'این تراکنش حذف خواهد شد!', icon: 'warning', showCancelButton: true, confirmButtonText: 'بله، حذف شود!', cancelButtonText: 'لغو' });
      if (result.isConfirmed) { try { await axios.delete(`/transactions/delete/${id}`); Swal.fire('حذف شد!', 'تراکنش با موفقیت حذف شد.', 'success'); fetchTransactions(currentPage.value); } catch (err) { Swal.fire('خطا!', 'خطا در حذف تراکنش.', 'error'); } }
    };

    const resetFilters = () => { filters.value = { search: '', type: '', from: '', to: '', category_id: '' }; fetchTransactions(1); };
    const changePage = page => { if (page >= 1 && page <= totalPages.value) fetchTransactions(page); };

    const initDatepickers = () => {
      document.querySelectorAll('.persia-datepicker').forEach(input => {
        $(input).persianDatepicker({ format: 'YYYY/MM/DD', autoClose: true, calendar: { persian: { leapYearMode: 'astronomical' } }, onSelect: unix => { const dateStr = new persianDate(unix).format('YYYY/MM/DD'); input.value = dateStr; input.dispatchEvent(new Event('input', { bubbles: true })); } });
      });
    };

    onMounted(() => { initDatepickers(); fetchTransactions(); fetchAllCategories(); });

    return {
      transactions, loading, currentPage, totalPages, filters, formatNumber, fetchTransactions, resetFilters, changePage, deleteTransaction, allCategories,
      editForm, editCategories, loadingEdit, openEditModal, updateTransaction, onFilterTypeChange, onEditTypeChange
    };
  }
}).mount('#transactions-app');
</script>
