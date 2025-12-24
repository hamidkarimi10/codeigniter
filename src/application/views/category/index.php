<div id="category-app">
  <!-- هدر -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2>مدیریت دسته‌بندی‌ها</h2>
    <button @click="openCreateModal" class="btn btn-primary">
      + دسته‌بندی جدید
    </button>
  </div>

  <!-- فیلتر -->
  <div class="card mb-4">
    <div class="card-body row g-3">
      <div class="col-md-3">
        <select v-model="filters.type" class="form-select">
          <option value="">همه</option>
          <option value="income">درآمد</option>
          <option value="expense">هزینه</option>
        </select>
      </div>
      <div class="col-md-6">
        <input
          v-model="filters.search"
          class="form-control"
          placeholder="جستجو..."
        >
      </div>
    </div>
  </div>

  <!-- لودینگ -->
  <div v-if="loading" class="text-center py-4">
    <div class="spinner-border"></div>
  </div>

  <!-- لیست -->
  <div v-else class="row">
    <div
      v-for="cat in filteredCategories"
      :key="cat.id"
      class="col-md-4 mb-3"
    >
      <div class="card">
        <div class="card-body d-flex justify-content-between">
          <div>
            <strong>{{ cat.name }}</strong>
            <span
              class="badge me-2"
              :class="cat.type === 'income' ? 'bg-success' : 'bg-danger'"
            >
              {{ cat.type === 'income' ? 'درآمد' : 'هزینه' }}
            </span>
          </div>
          <div>
            <button
              class="btn btn-sm btn-warning me-1 ms-2"
              @click="openEditModal(cat)"
            >
              <i class="bi bi-pencil-square"></i>
            </button>
            <button
              class="btn btn-sm btn-danger"
              @click="deleteCategory(cat.id)"
            >
              <i class="bi bi-trash3"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- مودال ایجاد -->
  <div class="modal fade" id="createModal">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header d-flex justify-content-between align-items-center">
          <h5>ایجاد دسته‌بندی</h5>
          <button class="btn-close ms-0" :class="isDarkMode ? 'btn-close-white' : ''" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input
            v-model="createForm.name"
            class="form-control mb-2"
            placeholder="نام"
          >
          <select v-model="createForm.type" class="form-select">
            <option value="" disabled selected>انتخاب نوع...</option>
            <option value="income">درآمد</option>
            <option value="expense">هزینه</option>
          </select>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">لغو</button>
          <button
            class="btn btn-primary"
            :disabled="!createForm.name.trim()"
            @click="createCategory"
          >
            ایجاد
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- مودال ویرایش -->
  <div class="modal fade" id="editModal">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5>ویرایش دسته‌بندی</h5>
          <button class="btn-close" :class="isDarkMode ? 'btn-close-white' : ''" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input
            v-model="editForm.name"
            class="form-control mb-2"
          >
          <select v-model="editForm.type" class="form-select">
            <option value="income">درآمد</option>
            <option value="expense">هزینه</option>
          </select>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">لغو</button>
          <button class="btn btn-success" @click="updateCategory">
            ذخیره
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
const { createApp, ref, computed, onMounted } = Vue;

createApp({
  setup() {
    const categories = ref([]);
    const loading = ref(false);
    const isDarkMode = ref(false);

    const filters = ref({
      type: '',
      search: ''
    });

    const createForm = ref({ name: '', type: 'expense' });
    const editForm = ref({ id: null, name: '', type: 'expense' });

    const filteredCategories = computed(() => {
      return categories.value.filter(c => {
        return (
          (!filters.value.type || c.type === filters.value.type) &&
          (!filters.value.search ||
            c.name.toLowerCase().includes(filters.value.search.toLowerCase()))
        );
      });
    });

    const fetchCategories = async () => {
      loading.value = true;
      try {
        const res = await axios.get('/category/api_list');
        categories.value = res.data;
      } catch (err) {
        console.error(err);
        alert('خطا در دریافت دسته‌بندی‌ها');
      } finally {
        loading.value = false;
      }
    };

    // ایجاد
    const openCreateModal = () => {
      createForm.value = { name: '', type: '' };
      new bootstrap.Modal(document.getElementById('createModal')).show();
    };

const createCategory = async () => {
  try {
    const fd = new FormData();
    fd.append('name', createForm.value.name);
    fd.append('type', createForm.value.type);

    await axios.post('/category/create', fd);
    bootstrap.Modal.getInstance(document.getElementById('createModal')).hide();
    fetchCategories();

    Swal.fire('موفق!', 'دسته‌بندی ایجاد شد.', 'success');
  } catch (err) {
    Swal.fire('خطا!', err.response?.data?.message || 'خطا در ایجاد دسته‌بندی', 'error');
  }
};
    // ویرایش
    const openEditModal = (cat) => {
      editForm.value = { ...cat };
      new bootstrap.Modal(document.getElementById('editModal')).show();
    };

const updateCategory = async () => {
  try {
    const fd = new FormData();
    fd.append('name', editForm.value.name);
    fd.append('type', editForm.value.type);

    await axios.post(`/category/api_update/${editForm.value.id}`, fd);
    bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
    fetchCategories();

    Swal.fire('موفق!', 'دسته‌بندی ویرایش شد.', 'success');
  } catch (err) {
    Swal.fire('خطا!', err.response?.data?.message || 'خطا در ویرایش دسته‌بندی', 'error');
  }
};
    // حذف
const deleteCategory = async (id) => {
  const result = await Swal.fire({
    title: 'حذف دسته‌بندی؟',
    text: 'آیا مطمئن هستید؟',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'بله، حذف شود',
    cancelButtonText: 'لغو'
  });

  if (result.isConfirmed) {
    try {
      await axios.delete(`/category/api_delete/${id}`);
      fetchCategories();
      Swal.fire('حذف شد!', 'دسته‌بندی حذف شد.', 'success');
    } catch (err) {
      Swal.fire('خطا!', err.response?.data?.message || 'خطا در حذف دسته‌بندی', 'error');
    }
  }
};
    onMounted(fetchCategories);

    return {
      filters,
      loading,
      createForm,
      editForm,
      filteredCategories,
      openCreateModal,
      createCategory,
      openEditModal,
      updateCategory,
      deleteCategory
    };
  }
}).mount('#category-app');
</script>
