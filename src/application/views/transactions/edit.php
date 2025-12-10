<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                ویرایش تراکنش
            </div>
            <div class="card-body">

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <?= form_open('transactions/edit/' . $transaction->id) ?>

                <div class="mb-3">
                    <label>نوع تراکنش</label>
                    <select name="type" id="type" class="form-control" required>
                        <option value="">انتخاب نوع</option>
                        <option value="income" <?= set_select('type', 'income', $transaction->type === 'income') ?>>درآمد</option>
                        <option value="expense" <?= set_select('type', 'expense', $transaction->type === 'expense') ?>>هزینه</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label>دسته‌بندی</label>
                    <select name="category_id" id="category_id" class="form-control" required>
                        <option value="">ابتدا نوع تراکنش را انتخاب کنید</option>
                        <?php
                        $categories = $transaction->type === 'income' ? $income_categories : $expense_categories;
                        foreach ($categories as $cat):
                        ?>
                            <option value="<?= $cat->id ?>" <?= set_select('category_id', $cat->id, $transaction->category_id == $cat->id) ?>>
                                <?= $cat->name ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label>مبلغ (تومان)</label>
                    <input type="number" name="amount" class="form-control" value="<?= set_value('amount', $transaction->amount) ?>" required>
                </div>

                <div class="mb-3">
                    <label>تاریخ تراکنش</label>
                    <input type="text" name="transaction_date" class="form-control persia-datepicker" value="<?= set_value('transaction_date', isset($transaction->jalali_date) ? $transaction->jalali_date : jdate('Y/m/d')) ?>" required>
                </div>

                <div class="mb-3">
                    <label>توضیحات</label>
                    <textarea name="description" class="form-control"><?= set_value('description', $transaction->description) ?></textarea>
                </div>

                <div class="mb-3">
                    <label>روش پرداخت</label>
                    <select name="payment_method" class="form-control" required>
                        <option value="نقدی" <?= set_select('payment_method', 'نقدی', $transaction->payment_method === 'نقدی') ?>>نقدی</option>
                        <option value="کارت به کارت" <?= set_select('payment_method', 'کارت به کارت', $transaction->payment_method === 'کارت به کارت') ?>>کارت به کارت</option>
                        <option value="چک" <?= set_select('payment_method', 'چک', $transaction->payment_method === 'چک') ?>>چک</option>
                        <option value="دیجیتال" <?= set_select('payment_method', 'دیجیتال', $transaction->payment_method === 'دیجیتال') ?>>دیجیتال</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label>شماره مرجع</label>
                    <input type="text" name="reference" class="form-control" value="<?= set_value('reference', $transaction->reference) ?>">
                </div>

                <button type="submit" class="btn btn-primary">بروزرسانی تراکنش</button>
                <?= form_close() ?>

            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/persian-date@1.1.0/dist/persian-date.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>


<script>
    // AJAX برای بارگذاری دسته‌بندی بر اساس نوع تراکنش
    document.getElementById('type').addEventListener('change', function() {
        const type = this.value;
        const categorySelect = document.getElementById('category_id');
        categorySelect.innerHTML = '<option>در حال بارگذاری...</option>';

        fetch('<?= base_url("transactions/get_categories") ?>?type=' + type)
            .then(res => res.json())
            .then(data => {
                categorySelect.innerHTML = '<option value="">انتخاب دسته‌بندی</option>';
                data.forEach(cat => {
                    const selected = <?= $transaction->category_id ?> == cat.id ? 'selected' : '';
                    categorySelect.innerHTML += `<option value="${cat.id}" ${selected}>${cat.name}</option>`;
                });
            });
    });
</script>

<script>
    $(document).ready(function () {
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
