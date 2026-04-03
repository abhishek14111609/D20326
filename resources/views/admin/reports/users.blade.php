@extends('admin.layouts.app')

@section('title', 'User Reports')

@push('styles')
    <style>
        .card-hover:hover {
            transform: translateY(-5px);
            transition: transform 0.3s ease;
        }

        .card {
            transition: all 0.3s ease;
        }

        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.7;
        }
    </style>
@endpush

@section('content')
    <div id="userReportsExportRoot" class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold py-3 mb-0">User Reports</h4>
            <div>
                <button class="btn btn-outline-primary me-2" id="printReport">
                    <i class='bx bx-printer me-1'></i> Print
                </button>
                <button class="btn btn-primary" id="exportPdf">
                    <i class='bx bx-export me-1'></i> Export PDF
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card card-hover h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="badge bg-label-primary rounded-pill mb-2">Total</span>
                                <h3 class="mb-0">{{ number_format($reports['total_users']) }}</h3>
                                <p class="mb-0 text-muted">Registered Users</p>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class='bx bx-user-circle stat-icon'></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card card-hover h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="badge bg-label-success rounded-pill mb-2">Active</span>
                                <h3 class="mb-0">{{ number_format($reports['active_today']) }}</h3>
                                <p class="mb-0 text-muted">Active Today</p>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class='bx bx-line-chart stat-icon'></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card card-hover h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="badge bg-label-info rounded-pill mb-2">New</span>
                                <h3 class="mb-0">{{ number_format($reports['new_this_week']) }}</h3>
                                <p class="mb-0 text-muted">New This Week</p>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-info">
                                    <i class='bx bx-user-plus stat-icon'></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card card-hover h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="badge bg-label-warning rounded-pill mb-2">Premium</span>
                                <h3 class="mb-0">{{ number_format($reports['premium_users']) }}</h3>
                                <p class="mb-0 text-muted">Premium Users</p>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class='bx bx-crown stat-icon'></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Growth Chart -->
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">User Growth</h5>
                        <div class="dropdown">
                            <button class="btn p-0" type="button" id="growthDropdown" data-bs-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="growthDropdown">
                                <a class="dropdown-item" href="javascript:void(0);">Last 7 Days</a>
                                <a class="dropdown-item" href="javascript:void(0);">Last Month</a>
                                <a class="dropdown-item" href="javascript:void(0);">Last Year</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="userGrowthChart" class="chartjs" height="400"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Demographics -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">User Status</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="userStatusChart" class="chartjs" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">User Types</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="userTypeChart" class="chartjs" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent User Activities</h5>
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Activity</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-xs me-2">
                                        <span class="avatar-initial rounded-circle bg-label-primary">JD</span>
                                    </div>
                                    <span>John Doe</span>
                                </div>
                            </td>
                            <td>Logged in</td>
                            <td>2 min ago</td>
                            <td><span class="badge bg-label-success">Active</span></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-xs me-2">
                                        <span class="avatar-initial rounded-circle bg-label-info">JS</span>
                                    </div>
                                    <span>Jane Smith</span>
                                </div>
                            </td>
                            <td>Updated profile</td>
                            <td>1 hour ago</td>
                            <td><span class="badge bg-label-success">Active</span></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-xs me-2">
                                        <span class="avatar-initial rounded-circle bg-label-warning">MJ</span>
                                    </div>
                                    <span>Mike Johnson</span>
                                </div>
                            </td>
                            <td>Purchased premium</td>
                            <td>3 hours ago</td>
                            <td><span class="badge bg-label-success">Active</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.2/dist/jspdf.plugin.autotable.min.js"></script>

    <script>
        // User Growth Chart
        const growthCtx = document.getElementById('userGrowthChart');
        new Chart(growthCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                datasets: [{
                    label: 'New Users',
                    data: [65, 78, 66, 89, 96, 88, 115],
                    borderColor: '#7367f0',
                    backgroundColor: 'rgba(115, 103, 240, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + ' new users';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });

        // User Status Chart
        const statusCtx = document.getElementById('userStatusChart');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Active', 'Inactive', 'Banned'],
                datasets: [{
                    data: [{{ $reports['total_users'] - 50 }}, 30, 20],
                    backgroundColor: ['#28c76f', '#ff9f43', '#ea5455'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    }
                },
                cutout: '70%'
            }
        });

        // User Type Chart
        const typeCtx = document.getElementById('userTypeChart');
        new Chart(typeCtx, {
            type: 'pie',
            data: {
                labels: ['Free Users', 'Premium Users'],
                datasets: [{
                    data: [{{ $reports['total_users'] - $reports['premium_users'] }},
                        {{ $reports['premium_users'] }}
                    ],
                    backgroundColor: ['#00cfe8', '#ff9f43'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Print functionality
        document.getElementById('printReport').addEventListener('click', function() {
            window.print();
        });

        // Export PDF functionality
        document.getElementById('exportPdf').addEventListener('click', async function() {
            const exportButton = this;

            if (!window.jspdf) {
                alert('Unable to export report right now. Please try again.');
                return;
            }

            try {
                exportButton.disabled = true;
                exportButton.innerHTML = "<i class='bx bx-loader-alt bx-spin me-1'></i> Exporting...";

                const {
                    jsPDF
                } = window.jspdf;
                const pdf = new jsPDF('p', 'pt', 'a4');
                const pageWidth = pdf.internal.pageSize.getWidth();
                const left = 40;
                let y = 48;

                const reportDate = new Date();
                const dateText = reportDate.toLocaleString();

                // Title
                pdf.setFont('helvetica', 'bold');
                pdf.setFontSize(18);
                pdf.text('User Reports', left, y);

                pdf.setFont('helvetica', 'normal');
                pdf.setFontSize(10);
                pdf.text(`Generated: ${dateText}`, left, y + 16);
                y += 36;

                // Summary stats from server-rendered values
                const stats = [
                    ['Total Registered Users', '{{ number_format($reports['total_users']) }}'],
                    ['Active Today', '{{ number_format($reports['active_today']) }}'],
                    ['New This Week', '{{ number_format($reports['new_this_week']) }}'],
                    ['Premium Users', '{{ number_format($reports['premium_users']) }}'],
                ];

                pdf.setFont('helvetica', 'bold');
                pdf.setFontSize(12);
                pdf.text('Summary', left, y);
                y += 10;

                pdf.autoTable({
                    startY: y,
                    margin: {
                        left,
                        right: 40
                    },
                    head: [
                        ['Metric', 'Value']
                    ],
                    body: stats,
                    styles: {
                        fontSize: 10,
                        cellPadding: 6
                    },
                    headStyles: {
                        fillColor: [57, 73, 171]
                    },
                    theme: 'striped'
                });
                y = pdf.lastAutoTable.finalY + 22;

                // Chart data summary
                const growthData = [65, 78, 66, 89, 96, 88, 115];
                const growthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'];
                const growthRows = growthLabels.map((label, index) => [label, String(growthData[index])]);

                if (y > 680) {
                    pdf.addPage();
                    y = 48;
                }

                pdf.setFont('helvetica', 'bold');
                pdf.setFontSize(12);
                pdf.text('User Growth', left, y);
                y += 10;

                pdf.autoTable({
                    startY: y,
                    margin: {
                        left,
                        right: 40
                    },
                    head: [
                        ['Month', 'New Users']
                    ],
                    body: growthRows,
                    styles: {
                        fontSize: 10,
                        cellPadding: 6
                    },
                    headStyles: {
                        fillColor: [0, 150, 136]
                    },
                    theme: 'grid'
                });
                y = pdf.lastAutoTable.finalY + 22;

                // Recent activities table from DOM
                const activityRows = [];
                document.querySelectorAll('table tbody tr').forEach((tr) => {
                    const tds = tr.querySelectorAll('td');
                    if (tds.length >= 4) {
                        const user = (tds[0].innerText || '').replace(/\s+/g, ' ').trim();
                        const activity = (tds[1].innerText || '').trim();
                        const time = (tds[2].innerText || '').trim();
                        const status = (tds[3].innerText || '').trim();
                        activityRows.push([user, activity, time, status]);
                    }
                });

                if (y > 620) {
                    pdf.addPage();
                    y = 48;
                }

                pdf.setFont('helvetica', 'bold');
                pdf.setFontSize(12);
                pdf.text('Recent User Activities', left, y);
                y += 10;

                pdf.autoTable({
                    startY: y,
                    margin: {
                        left,
                        right: 40
                    },
                    head: [
                        ['User', 'Activity', 'Time', 'Status']
                    ],
                    body: activityRows.length ? activityRows : [
                        ['N/A', 'N/A', 'N/A', 'N/A']
                    ],
                    styles: {
                        fontSize: 9,
                        cellPadding: 5
                    },
                    headStyles: {
                        fillColor: [255, 152, 0]
                    },
                    theme: 'striped'
                });

                // Footer with page numbers
                const pageCount = pdf.getNumberOfPages();
                for (let i = 1; i <= pageCount; i++) {
                    pdf.setPage(i);
                    pdf.setFont('helvetica', 'normal');
                    pdf.setFontSize(9);
                    pdf.text(`Page ${i} of ${pageCount}`, pageWidth - 95, 820);
                }

                const dateStamp = new Date().toISOString().slice(0, 10);
                pdf.save(`user-reports-${dateStamp}.pdf`);
            } catch (error) {
                console.error('PDF export failed:', error);
                alert('PDF export failed. Please try again.');
            } finally {
                exportButton.disabled = false;
                exportButton.innerHTML = "<i class='bx bx-export me-1'></i> Export PDF";
            }
        });
    </script>
@endpush
