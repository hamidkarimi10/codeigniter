<div class="row justify-content-center">
    <div class="col-lg-8 col-md-10">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-plus-circle"></i> ایجاد تراکنش جدید</h5>
            </div>
            <div class="card-body">

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <?= form_open('transactions/create') ?>

                <div class="row g-3">

                    <!-- نوع تراکنش -->
                    <div class="col-md-6">
                        <label class="form-label">نوع تراکنش</label>
                        <select name="type" id="type" class="form-select" required>
                            <option value="">انتخاب نوع</option>
                            <option value="income" <?= set_select('type','income') ?>>درآمد</option>
                            <option value="expense" <?= set_select('type','expense') ?>>هزینه</option>
                        </select>
                    </div>

                    <!-- دسته‌بندی -->
                    <div class="col-md-6">
                        <label class="form-label">دسته‌بندی</label>
                        <select name="category_id" id="category_id" class="form-select" required>
                            <option value="">ابتدا نوع تراکنش را انتخاب کنید</option>
                        </select>
                    </div>

                    <!-- مبلغ -->
                    <div class="col-md-6">
                        <label class="form-label">مبلغ (تومان)</label>
                        <input type="number" name="amount" class="form-control" value="<?= set_value('amount') ?>" required>
                    </div>

                    <!-- تاریخ تراکنش -->
                    <div class="col-md-6">
                        <label class="form-label">تاریخ تراکنش</label>
                        <input type="text" name="transaction_date" class="form-control persia-datepicker" value="<?= set_value('transaction_date',jdate('Y/m/d')) ?>" required readonly>
                    </div>

                    <!-- روش پرداخت -->
                    <div class="col-md-6">
                        <label class="form-label">روش پرداخت</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="cash" <?= set_select('payment_method','cash', true) ?>>نقدی</option>
                            <option value="card" <?= set_select('payment_method','card') ?>>کارت بانکی</option>
                            <option value="online" <?= set_select('payment_method','online') ?>>آنلاین</option>
                            <option value="cheque" <?= set_select('payment_method','cheque') ?>>چک</option>
                        </select>
                    </div>

                    <!-- شماره مرجع -->
                    <div class="col-md-6">
                        <label class="form-label">شماره مرجع</label>
                        <input type="text" name="reference" class="form-control" value="<?= set_value('reference') ?>">
                    </div>

                    <!-- توضیحات -->
                    <div class="col-12">
                        <label class="form-label">توضیحات</label>
                        <textarea name="description" class="form-control" rows="3"><?= set_value('description') ?></textarea>
                    </div>

                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> ذخیره تراکنش</button>
                </div>

                <?= form_close() ?>

            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/persian-date@1.1.0/dist/persian-date.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>

<script>
$(document).ready(function() {
    $('#type').change(function() {
        var type = $(this).val();
        if(type) {
            $.ajax({
                url: '<?= base_url("transactions/get_categories") ?>',
                type: 'GET',
                data: { type: type },
                success: function(response) {
                    var categories = response;
                    var options = '<option value="">انتخاب دسته‌بندی</option>';
                    categories.forEach(function(cat) {
                        options += '<option value="'+cat.id+'">'+cat.name+'</option>';
                    });
                    $('#category_id').html(options);
                }
            });
        } else {
            $('#category_id').html('<option value="">ابتدا نوع تراکنش را انتخاب کنید</option>');
        }
    });
});

</script>
<script>
    $(document).ready(function () {
        // persianDate.toLocale('fa').calendar('persian').useUTC(false);
    $('.persia-datepicker').persianDatepicker({
        format: 'YYYY/MM/DD',
        initialValue: false,
        autoClose: true,
        calendar:{
        persian: {
            leapYearMode: 'astronomical'
        }
    }
    });
});

</script>