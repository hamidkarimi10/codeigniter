<div id="login-app" class="row justify-content-center align-items-center min-vh-100">
  <div class="col-md-5">
    <div class="card shadow-lg">
      <div class="card-body p-5">
        <div class="text-center mb-4">
          <h3 class="card-title text-primary">ورود به سیستم</h3>
          <p class="text-muted">لطفا اطلاعات حساب خود را وارد کنید</p>
        </div>

        <form @submit.prevent="login">
          <div class="mb-3">
            <label class="form-label">ایمیل</label>
            <input v-model="form.email" type="email" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">رمز عبور</label>
            <input v-model="form.password" type="password" class="form-control" required>
          </div>

          <div class="mb-3">
            <div class="d-flex align-items-center">
              <input v-model="form.remember_me" type="checkbox" class="form-check-input me-2" id="rememberCheck">
              <label class="form-check-label" for="rememberCheck">مرا به خاطر بسپار</label>
            </div>
          </div>
          
          <button type="button" class="btn btn-primary w-100 py-2" @click="login()">
            ورود به سیستم
          </button>
        </form>

        <div class="text-center mt-3">
          <a href="/auth/register">ثبت نام</a>
        </div>
      </div>
    </div>
  </div>
</div>




<script>
const loginApp = Vue.createApp({
  data() {
    return {
      form: {
        email: '',
        password: '',
        remember_me: false
      }
    }
  },
  methods: {
    login() {
      axios.post('/auth/login', this.form, {
        withCredentials: true,
        headers: { 
          'X-Requested-With': 'XMLHttpRequest'
        }
      }).then(res => {
        if (res.status === 200) {
          Swal.fire({
            icon: 'success',
            title: 'ورود موفق',
            text: 'به داشبورد هدایت می‌شوید...',
            timer: 1500,
            showConfirmButton: false
          }).then(() => {
            window.location.href = Application.baseUrl + '/dashboard';
          });
        }
      }).catch(err => {
        Swal.fire({
          icon: 'error',
          title: 'خطا',
          text: 'ایمیل یا رمز عبور اشتباه است',
        });
      });
    }
  }
}).mount('#login-app');
</script>