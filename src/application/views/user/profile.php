<?php
// application/views/user/profile.php
?>

<div class="row justify-content-center mt-4">
    <div class="col-md-8">
        <div class="card main-card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">پروفایل کاربری</h4>
            </div>
            <div class="card-body">
                <form action="<?= base_url('user/update') ?>" method="post" enctype="multipart/form-data">
                    <div class="text-center mb-3">
                        <img src="<?= base_url($user->avatar ?: 'uploads/avatars/default.png') ?>" 
                             class="rounded-circle border" width="120" height="120" style="object-fit: cover;">
                    </div>

                    <div class="mb-3">
                        <label>نام</label>
                        <input type="text" name="first_name" class="form-control" value="<?= $user->first_name ?>">
                    </div>

                    <div class="mb-3">
                        <label>نام خانوادگی</label>
                        <input type="text" name="last_name" class="form-control" value="<?= $user->last_name ?>">
                    </div>

                    <div class="mb-3">
                        <label>ایمیل</label>
                        <input type="email" name="email" class="form-control" value="<?= $user->email ?>">
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label>رمز عبور جدید (اختیاری)</label>
                        <input type="password" name="password" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label>تایید رمز</label>
                        <input type="password" name="password_confirm" class="form-control">
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label>تصویر پروفایل</label>
                        <input type="file" name="avatar" class="form-control">
                        <P>فایل های مجاز <span class="text-danger">jpg|jpeg|png</span></P>
                    </div>

                    <button class="btn btn-primary">ذخیره تغییرات</button>
                </form>
            </div>
        </div>
    </div>
</div>
