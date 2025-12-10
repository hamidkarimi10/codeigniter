<!-- <?php var_dump($monthly_data) ?> -->
<div class="row mb-4">
    <div class="col-12">
        <h2>خوش آمدید، <?= $user->first_name ?>! 👋</h2>
        <p class="text-muted">خلاصه وضعیت مالی شما</p>
    </div>
</div>

<!-- کارت‌های آماری -->
<div class="row mb-4">
    <div class="col-md-4 mb-3 mb-md-0">
        <div class="stat-card income p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1">کل درآمد</h6>
                    <h4 class="mb-0"><?= number_format($financial_summary->total_income ?? 0) ?> تومان</h4>
                </div>
                <i class="bi bi-arrow-up-circle display-4 opacity-75"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-3 mb-md-0">
        <div class="stat-card expense p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1">کل هزینه</h6>
                    <h4 class="mb-0"><?= number_format($financial_summary->total_expense ?? 0) ?> تومان</h4>
                </div>
                <i class="bi bi-arrow-down-circle display-4 opacity-75"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card balance p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1">موجودی</h6>
                    <h4 class="mb-0"><?= number_format($user->balance ?? 0) ?> تومان</h4>
                </div>
                <i class="bi bi-wallet display-4 opacity-75"></i>
            </div>
        </div>
    </div>
</div>

<!-- نمودار و تراکنش‌های اخیر -->
<div class="row">
    <!-- نمودار -->
    <div class="col-lg-8 mb-3 mb-lg-0">
        <div class="card main-card">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">📊 روند درآمد و هزینه (۶ ماه اخیر)</h5>
            </div>
            <div class="card-body">
                <canvas id="financialChart" height="150"></canvas>
            </div>
        </div>
    </div>

    <!-- تراکنش‌های اخیر -->
    <div class="col-lg-4">
        <div class="card main-card">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">🔄 تراکنش‌های اخیر</h5>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($recent_transactions)): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_transactions as $tx): ?>
                            <div class="list-group-item <?= $tx->type == 'income' ? 'transaction-income' : 'transaction-expense' ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?= htmlspecialchars($tx->category_name) ?></h6>
                                        <p class="mb-1 text-muted small"><?= htmlspecialchars($tx->description ?: 'بدون توضیح') ?></p>
                                        <small class="text-muted"><?= jdate('Y/m/d', strtotime($tx->transaction_date)) ?></small>
                                    </div>
                                    <div class="text-end text-nowrap">
                                        <span class="fw-bold <?= $tx->type == 'income' ? 'text-success' : 'text-danger' ?>">
                                            <?= $tx->type == 'income' ? '+' : '-' ?><?= number_format($tx->amount) ?>
                                        </span>
                                        <small class="text-muted">تومان</small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-receipt display-4 text-muted"></i>
                        <p class="text-muted mt-2">تراکنشی یافت نشد</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script
src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js">
</script>

<script>
const rawData = <?= isset($monthly_data) ? $monthly_data : '[]' ?>;

// اگر داده‌ای وجود نداشت، آرایه خالی ایجاد می‌شود
const labels = rawData.map(item => item.month || '');
const incomeData = rawData.map(item => parseFloat(item.income) || 0);
const expenseData = rawData.map(item => parseFloat(item.expense) || 0);

// رندر نمودار
new Chart("financialChart", {
  type: "line", // می‌توانی به 'line' تغییر دهی
  data: {
    labels: labels,
    datasets: [
      {
        label: "درآمد",
        backgroundColor: "rgba(40, 167, 69, 0.6)", // سبز
        borderColor: "rgba(40, 167, 69, 1)",
        data: incomeData
      },
      {
        label: "هزینه",
        backgroundColor: "rgba(220, 53, 69, 0.6)", // قرمز
        borderColor: "rgba(220, 53, 69, 1)",
        data: expenseData
      }
    ]
  },
  options: {
    responsive: true,
    legend: {
      display: true,
      rtl: true
    },
    title: {
      display: true,
      text: "روند ماهانه درآمد و هزینه",
      fontSize: 16
    },
    scales: {
      yAxes: [{
        ticks: {
          beginAtZero: true,
          callback: function(value) {
            return value.toLocaleString() + ' ت';
          }
        }
      }],
      xAxes: [{
        ticks: {
          autoSkip: false
        }
      }]
    },
    tooltips: {
      rtl: true,
      callbacks: {
        label: function(tooltipItem, data) {
          const dataset = data.datasets[tooltipItem.datasetIndex];
          const value = dataset.data[tooltipItem.index];
          return dataset.label + ': ' + value.toLocaleString() + ' تومان';
        }
      }
    }
  }
});
</script>