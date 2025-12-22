<div id="profileApp" class="container mt-4">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card main-card">
        <div class="card-header bg-primary text-white">
          <h4 class="mb-0">پروفایل کاربری</h4>
        </div>
        <div class="card-body">
          <div v-if="loading" class="text-center py-3">در حال بارگذاری...</div>

          <form v-else @submit.prevent="updateProfile" v-if="user.id">
            <div class="text-center mb-3">
              <img :src="avatarPreview" class="rounded-circle border" width="120" height="120" style="object-fit: cover;">

              <div class="mt-2">
                <label class="btn btn-sm btn-outline-primary me-2 ms-2">
                  آپلود عکس
                  <input type="file" @change="onFileChange" accept="image/*" style="display:none">
                </label>
                <button type="button" @click="removeAvatar" class="btn btn-sm btn-outline-danger" :disabled="loading">
                  حذف عکس
                </button>
                <p>فرمت های مجاز : <span style="color:red">jpg|jpeg|png</span></p>
                <p>  حداکثر سایز فایل : <span style="color:red">1 مگابایت</span></p>

              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label>نام</label>
                <input v-model="form.first_name" type="text" class="form-control">
                <div v-if="errors.first_name" class="text-danger small mt-1">{{ errors.first_name }}</div>
              </div>
              <div class="col-md-6 mb-3">
                <label>نام خانوادگی</label>
                <input v-model="form.last_name" type="text" class="form-control">
                <div v-if="errors.last_name" class="text-danger small mt-1">{{ errors.last_name }}</div>
              </div>
            </div>

            <div class="mb-3">
              <label>ایمیل</label>
              <input v-model="form.email" type="email" class="form-control">
              <div v-if="errors.email" class="text-danger small mt-1">{{ errors.email }}</div>
            </div>

            <hr>

            <!-- تغییر رمز عبور -->
            <div class="mb-3">
              <label>رمز عبور جدید (اختیاری)</label>
              <input v-model="form.password" type="password" class="form-control">
            </div>

            <div class="mb-3">
              <label>تایید رمز عبور</label>
              <input v-model="form.password_confirm" type="password" class="form-control">
              <div v-if="errors.password" class="text-danger small mt-1">{{ errors.password }}</div>
            </div>

            <button type="submit" class="btn btn-primary" :disabled="loading">
              <span v-if="loading">در حال ذخیره...</span>
              <span v-else>ذخیره تغییرات</span>
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>


<script>
const { createApp, ref, onMounted } = Vue;

createApp({
  setup() {
    const user = ref({
      id: null,
      first_name: '',
      last_name: '',
      email: '',
      avatar: ''
    });
    
    const form = ref({
      first_name: '',
      last_name: '',
      email: '',
      password: '',
      password_confirm: ''
    });

    const avatarPreview = ref('');
    const avatarFile = ref(null);
    const loading = ref(false);
    const errors = ref({});

    // بارگذاری داده‌ها
    const loadProfile = async () => {
      try {
        const res = await axios.get('<?= base_url("user/api_profile") ?>');
        user.value = res.data;
        form.value.first_name = res.data.first_name;
        form.value.last_name = res.data.last_name;
        form.value.email = res.data.email;
        avatarPreview.value = res.data.avatar;
      } catch (err) {
        Swal.fire('خطا!', 'خطا در بارگذاری پروفایل.', 'error');
      }
    };

    const onFileChange = (e) => {
  const file = e.target.files[0];
  if (!file) return;

  const maxSize = 1 * 1024 * 1024; 
  if (file.size > maxSize) {
    Swal.fire({
      icon: 'error',
      title: 'خطا!',
      text: 'حجم فایل پروفایل نباید بیشتر از ۱ مگابایت باشد.',
      confirmButtonText: 'باشه'
    });
    e.target.value = ''; 
    avatarFile.value = null;
    return;
  }

  const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
  if (!allowedTypes.includes(file.type)) {
    Swal.fire({
      icon: 'error',
      title: 'فرمت نامعتبر!',
      text: 'لطفاً فایل‌های JPG، JPEG یا PNG انتخاب کنید.',
      confirmButtonText: 'باشه'
    });
    e.target.value = '';
    avatarFile.value = null;
    return;
  }

  avatarFile.value = file;
  avatarPreview.value = URL.createObjectURL(file);
};

    // حذف عکس
    const removeAvatar = async () => {
      const result = await Swal.fire({
        title: 'حذف عکس؟',
        text: 'آیا مطمئن هستید که می‌خواهید عکس پروفایل را حذف کنید؟',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'بله، حذف شود',
        cancelButtonText: 'لغو'
      });

      if (result.isConfirmed) {
        try {
          const res = await axios.post('<?= base_url("user/api_remove_avatar") ?>');
          Swal.fire('حذف شد!', res.data.message, 'success');
          avatarPreview.value = res.data.avatar_url;
          avatarFile.value = null;
        } catch (err) {
          Swal.fire('خطا!', err.response?.data?.message || 'خطا در حذف.', 'error');
        }
      }
    };

    // آپلود عکس (در صورت وجود)
    const uploadAvatarIfNeeded = async () => {
      if (!avatarFile.value) return null;

      const formData = new FormData();
      formData.append('avatar', avatarFile.value);

      try {
        const res = await axios.post('<?= base_url("user/api_upload_avatar") ?>', formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
        Swal.fire('موفق!', res.data.message, 'success');
        return res.data.avatar_url;
      } catch (err) {
        Swal.fire('خطا!', err.response?.data?.message || 'خطا در آپلود.', 'error');
        throw err; // برای متوقف کردن به‌روزرسانی اصلی
      }
    };

    // به‌روزرسانی پروفایل
    const updateProfile = async () => {
      loading.value = true;
      errors.value = {};

      try {
        if (avatarFile.value) {
          await uploadAvatarIfNeeded();
        }

        const payload = { ...form.value };
        if (!payload.password) delete payload.password;
        if (!payload.password_confirm) delete payload.password_confirm;

        const res = await axios.post('<?= base_url("user/api_update") ?>', payload);
        Swal.fire('موفق!', res.data.message, 'success');

        setTimeout(() => {
          window.location.href = '<?= base_url("dashboard") ?>';
        }, 1500);

      } catch (err) {
        if (err.response?.status === 422) {
          errors.value = err.response.data.errors || {};
        }
        // خطاها در uploadAvatarIfNeeded یا update قبلاً نمایش داده شده
      } finally {
        loading.value = false;
      }
    };

    onMounted(() => {
      loadProfile();
    });

    return {
      user,
      form,
      avatarPreview,
      loading,
      errors,
      onFileChange,
      removeAvatar,
      updateProfile
    };
  }
}).mount('#profileApp');
</script>