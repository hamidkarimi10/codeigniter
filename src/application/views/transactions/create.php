<div id="my-app">
  <div class="row justify-content-center">
    <div class="col-lg-8 col-md-10">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0"><i class="bi bi-plus-circle"></i> ایجاد تراکنش جدید</h5>
        </div>
        <div class="card-body">
          <form @submit.prevent="submitForm">
            <div class="row g-3">
              <!-- نوع تراکنش -->
              <div class="col-md-6">
                <label class="form-label">نوع تراکنش</label>
                <select v-model="type" class="form-select" required>
                  <option value="">انتخاب نوع</option>
                  <option value="income">درآمد</option>
                  <option value="expense">هزینه</option>
                </select>
              </div>
              <!-- دسته‌بندی -->
              <div class="col-md-6">
                <label class="form-label">دسته‌بندی</label>
                <select v-model="category_id" class="form-select" required>
                  <option value="">ابتدا نوع تراکنش را انتخاب کنید</option>
                  <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                    {{ cat.name }}
                  </option>
                </select>
              </div>
              <!-- مبلغ -->
              <div class="col-md-6">
                <label class="form-label">مبلغ (تومان)</label>
                <input type="number" v-model="amount" class="form-control" required>
              </div>
              <!-- تاریخ تراکنش -->
              <div class="col-md-6">
                <label class="form-label">تاریخ تراکنش</label>
                <input
                  type="text"
                  v-model="transaction_date"
                  class="form-control persia-datepicker"
                  required
                  readonly
                />
              </div>
              <!-- روش پرداخت -->
              <div class="col-md-6">
                <label class="form-label">روش پرداخت</label>
                <select v-model="payment_method" class="form-select" required>
                  <option value="cash">نقدی</option>
                  <option value="card">کارت بانکی</option>
                  <option value="online">آنلاین</option>
                  <option value="cheque">چک</option>
                </select>
              </div>
              <!-- شماره مرجع -->
              <div class="col-md-6">
                <label class="form-label">شماره مرجع</label>
                <input type="text" v-model="reference" class="form-control" />
              </div>
              <!-- توضیحات -->
              <div class="col-12">
                <label class="form-label">توضیحات</label>
                <textarea v-model="description" class="form-control" rows="3"></textarea>
              </div>
            </div>
            <div class="mt-4 d-flex justify-content-between">
              <button type="submit" class="btn btn-success">
                <i class="bi bi-save"></i> ذخیره تراکنش
              </button>
              <button type="button" class="btn btn-outline-primary" @click="openCategoryModal">
                <i class="bi bi-tag"></i> افزودن دسته‌بندی
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!-- Modal افزودن دسته‌بندی -->
  <div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header d-flex justify-content-between align-items-center">
          <h5 class="modal-title">افزودن دسته‌بندی جدید</h5>
          <button type="button" class="btn-close ms-0" data-bs-dismiss="modal" aria-label="بستن"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label>نام دسته‌بندی</label>
            <input v-model="newCategory.name" type="text" class="form-control" placeholder="مثلاً حقوق، خوراک..."/>
          </div>
          <div class="mb-3">
            <label>نوع</label>
            <select v-model="newCategory.type" class="form-select">
              <option value="income">درآمد</option>
              <option value="expense">هزینه</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">لغو</button>
          <button type="button" class="btn btn-primary" @click="addCategory">ذخیره</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
const { createApp, ref, watch, onMounted, nextTick } = Vue;

createApp({
  setup() {
    const type = ref('');
    const category_id = ref('');
    const categories = ref([]);
    const amount = ref('');
    const transaction_date = ref('');
    const payment_method = ref('cash');
    const reference = ref('');
    const description = ref('');

    const newCategory = ref({ name: '', type: 'expense' });

    watch(type, async (newType) => {
      if (!newType) {
        categories.value = [];
        category_id.value = '';
        return;
      }
      // دریافت دسته بندی ها
      try {
        const res = await axios.get('/transactions/get_categories', { params: { type: newType } });
        categories.value = res.data;
        category_id.value = '';
      } catch (err) {
        console.error('خطا در دریافت دسته‌بندی‌ها', err);
        categories.value = [];
        category_id.value = '';
      }
    });
    // فعال‌سازی تقویم فارسی
    const initDatepicker = () => {
      const input = document.querySelector('.persia-datepicker');
      if (input) {
        $(input).persianDatepicker({
          format: 'YYYY/MM/DD',
          autoClose: true,
          calendar: { persian: { leapYearMode: 'astronomical' } },
          onSelect: (unix) => {
            const pd = new persianDate(unix).format('YYYY/MM/DD');
            transaction_date.value = pd;
            input.value = pd;
          }
        });
      }
    };
    // باز کردن مدال
    const openCategoryModal = () => {
      const modalEl = document.getElementById('categoryModal');
      if (modalEl) {
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
      }
    };
    // افزودن دسته‌بندی جدید
    const addCategory = async () => {
      if (!newCategory.value.name.trim()) {
        Swal.fire('خطا!', 'نام دسته‌بندی را وارد کنید.', 'error');
        return;
      }
      // ایجاد دسته بندی جدید 
      try {
        const res = await axios.post('/category/create', newCategory.value);
        Swal.fire('موفق!', res.data.message, 'success');

        bootstrap.Modal.getInstance(document.getElementById('categoryModal')).hide();

        // بارگذاری مجدد دسته‌بندی‌ها
        const catRes = await axios.get('/transactions/get_categories', { params: { type: newCategory.value.type } });
        categories.value = catRes.data;
        category_id.value = res.data.category_id || catRes.data[catRes.data.length - 1]?.id;

        newCategory.value.name = '';
      } catch (err) {
        Swal.fire('خطا!', err.response?.data?.message || 'خطا در ایجاد دسته‌بندی.', 'error');
      }
    };

    // سابمیت فرم اصلی
    const submitForm = async () => {
      const payload = {
        type: type.value,
        category_id: category_id.value,
        amount: amount.value,
        transaction_date: transaction_date.value,
        payment_method: payment_method.value,
        reference: reference.value,
        description: description.value
      };

      try {
        const res = await axios.post('/transactions/store', payload);
        if (res.data.status === 'success') {
          Swal.fire({
            icon: 'success',
            title: 'موفق!',
            text: 'تراکنش با موفقیت ثبت شد',
            confirmButtonText: 'باشه',
            confirmButtonColor: '#198754'
          }).then(() => {
            window.location.href = '/transactions';
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'خطا!',
            text: res.data.message || 'خطا در ثبت تراکنش',
            confirmButtonText: 'متوجه شدم'
          });
        }
      } catch (err) {
        Swal.fire({
          icon: 'error',
          title: 'خطا!',
          text: 'عدم دسترسی به سرور. لطفاً دوباره تلاش کنید.',
          confirmButtonText: 'متوجه شدم'
        });
        console.error(err);
      }
    };

    // اجرای اولیه
    onMounted(() => {
      nextTick(() => {
        initDatepicker();
      });
    });

    return {
      // stateها
      type,
      category_id,
      categories,
      amount,
      transaction_date,
      payment_method,
      reference,
      description,
      newCategory,

      openCategoryModal,
      addCategory,
      submitForm
    };
  }
}).mount('#my-app');
</script>