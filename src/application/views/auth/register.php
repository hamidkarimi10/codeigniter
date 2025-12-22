<div id="register-app" class="row justify-content-center align-items-center min-vh-100">
  <div class="col-md-5">
    <div class="card shadow-lg">
      <div class="card-body p-5">
        <div class="text-center mb-4">
          <h3 class="card-title text-primary">ثبت نام</h3>
          <p class="text-muted">اطلاعات خود را وارد کنید</p>
        </div>

        <form @submit.prevent="register">
          <div class="mb-3">
            <label class="form-label">نام</label>
            <input v-model="form.first_name" type="text" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">نام خانوادگی</label>
            <input v-model="form.last_name" type="text" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">ایمیل</label>
            <input v-model="form.email" type="email" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">رمز عبور</label>
            <input v-model="form.password" type="password" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">تکرار رمز عبور</label>
            <input v-model="form.confirm_password" type="password" class="form-control" required>
          </div>

          <button type="submit" class="btn btn-primary w-100 py-2" :disabled="loading">
            <span v-if="loading">در حال ثبت‌نام...</span>
            <span v-else>ثبت نام</span>
          </button>
        </form>

        <div class="text-center mt-3">
          <p class="mb-0">قبلاً ثبت نام کرده‌اید؟ 
            <a href="/auth/login" class="text-decoration-none">ورود کنید</a>
          </p>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios@1.6.7/dist/axios.min.js"></script>
<script>
const registerApp = Vue.createApp({
  data() {
    return {
      form: {
        first_name: '',
        last_name: '',
        email: '',
        password: '',
        confirm_password: ''
      },
      loading: false
    };
  },
  methods: {
    async register() {
      if (this.form.password !== this.form.confirm_password) {
    Swal.fire({
      icon: 'error',
      title: 'خطا',
      text: 'رمز عبور و تکرار آن یکسان نیستند'
    });
    return; 
  }
      this.loading = true;

      try {
        const response = await axios.post('/auth/register', this.form, {
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          withCredentials: true
        });

        Swal.fire({
          icon: 'success',
          title: 'ثبت نام موفق',
          text: response.data.message || 'ثبت‌نام با موفقیت انجام شد.',
          timer: 1500,
          showConfirmButton: false
        }).then(() => {
          window.location.href = '/auth/login';
        });

      } catch (err) {
        if (err.response) {
          if (err.response.status === 422) {
            const errors = err.response.data.errors;
            let messages = [];
            for (let key in errors) {
              messages.push(errors[key]);
            }
            Swal.fire({
              icon: 'error',
              title: 'خطا در ثبت نام',
              html: messages.join('<br>')
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'خطا',
              text: err.response.data.message || 'خطا در ثبت‌نام. لطفاً دوباره تلاش کنید.'
            });
          }
        } else {
          Swal.fire({
            icon: 'error',
            title: 'خطا',
            text: 'عدم دسترسی به سرور.'
          });
        }
      } finally {
        this.loading = false;
      }
    }
  }
}).mount('#register-app');
</script>