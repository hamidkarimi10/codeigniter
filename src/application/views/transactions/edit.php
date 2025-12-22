<div id="edit-app">
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                ویرایش تراکنش
            </div>
            <div class="card-body">

                <div v-if="error" class="alert alert-danger">{{ error }}</div>

                <form @submit.prevent="submitForm">

                    <div class="mb-3">
                        <label>نوع تراکنش</label>
                        <select v-model="type" class="form-control" required>
                            <option value="">انتخاب نوع</option>
                            <option value="income">درآمد</option>
                            <option value="expense">هزینه</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>دسته‌بندی</label>
                        <select v-model="category_id" class="form-control" required>
                            <option value="">انتخاب دسته‌بندی</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                {{ cat.name }}
                            </option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>مبلغ (تومان)</label>
                        <input type="number" v-model="amount" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>تاریخ تراکنش</label>
                        <input type="text"
                            class="form-control persia-datepicker" v-model="transaction_date" required>
                    </div>

                    <div class="mb-3">
                        <label>توضیحات</label>
                        <textarea v-model="description" class="form-control"></textarea>
                    </div>

                    <div class="mb-3">
                        <label>روش پرداخت</label>
                        <select v-model="payment_method" class="form-control" required>
                            <option value="cash">نقدی</option>
                            <option value="card">کارت بانکی</option>
                            <option value="online">آنلاین</option>
                            <option value="cheque">چک</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>شماره مرجع</label>
                        <input type="text" v-model="reference" class="form-control">
                    </div>

                    <button type="submit" class="btn btn-success">
                        بروزرسانی تراکنش
                    </button>

                </form>

            </div>
        </div>
    </div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
const app = Vue.createApp({
  data() {
    return {
      transaction_id: null,
      type: '',
      category_id: '',
      categories: [],
      amount: '',
      transaction_date: '',
      description: '',
      payment_method: 'cash',
      reference: '',
      error: '',
      datepickerInitialized: false
    };
  },

  watch: {
    type(newType) {
      if (!newType) {
        this.categories = [];
        this.category_id = '';
        return;
      }
      axios.get('/transactions/get_categories', {
        params: { type: newType }
      }).then(res => {
        this.categories = res.data;
        if (!this.categories.some(c => String(c.id) === this.category_id)) {
          this.category_id = '';
        }
      });
    }
  },

  mounted() {
    
     $('.persia-datepicker').persianDatepicker({
            format: 'YYYY/MM/DD',
            initialValue: true,
            initialValueType: 'persian',
            autoClose: true,
            calendar:{
                persian: { leapYearMode: 'astronomical' }
            },
             onSelect: (unix) => {
                const pd = new persianDate(unix).format('YYYY/MM/DD');
                this.transaction_date = pd;
            }
        });
        
    this.transaction_id = window.location.pathname.split('/').pop();

    axios.get('/transactions/get/' + this.transaction_id)
      .then(res => {
        const t = res.data;
        this.type = t.type;
        this.amount = t.amount;
        this.category_id = String(t.category_id); 
        this.transaction_date = t.transaction_date;
        this.description = t.description || '';
        this.payment_method = t.payment_method || 'cash';
        this.reference = t.reference || '';

        this.$nextTick(() => {
          this.initDatePicker();
        });
      })
      .catch(err => {
        this.error = 'خطا در بارگذاری تراکنش';
        console.error(err);
      });
  },

  methods: {
    submitForm() {
      const payload = {
        type: this.type,
        category_id: this.category_id,
        amount: this.amount,
        transaction_date: this.transaction_date,
        description: this.description,
        payment_method: this.payment_method,
        reference: this.reference
      };

      axios.post('/transactions/update/' + this.transaction_id, payload)
        .then(res => {
          if (res.data.status === 'success') {
          Swal.fire({
            icon: 'success',
            title: 'موفق!',
            text: 'تراکنش با موفقیت ویرایش شد',
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
        })
        .catch(() => {
          Swal.fire({
            icon: 'error',
            title: 'خطا!',
            text: res.data.message || 'خطا در ارتباط با سرور ',
            confirmButtonText: 'متوجه شدم'
          });        
        });
    }
  }
});

app.mount('#edit-app');
</script>