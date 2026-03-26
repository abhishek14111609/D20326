@extends('admin.layouts.app')

@section('title', 'Quiz Statistics: ' . $quiz->title)

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/apexcharts/apexcharts.css') }}">
<style>
    .stat-card {
        border-radius: 0.5rem;
        transition: transform 0.2s;
        height: 100%;
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }
    .stat-icon {
        width: 3rem;
        height: 3rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .participant-score {
        position: relative;
        height: 3rem;
        background-color: #f5f5f5;
        border-radius: 0.5rem;
        overflow: hidden;
        margin-bottom: 0.5rem;
    }
    .score-bar {
        height: 100%;
        display: flex;
        align-items: center;
        padding-left: 1rem;
        color: white;
        font-weight: 500;
        white-space: nowrap;
    }
    .score-value {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <nav aria-label="breadcrumb" class="d-flex justify-content-between align-items-center mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.competitions.index') }}">Competitions</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('admin.competitions.quizzes.index', $competition) }}">{{ $competition->title }} - Quizzes</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('admin.competitions.quizzes.show', [$competition, $quiz]) }}">{{ $quiz->title }}</a>
            </li>
            <li class="breadcrumb-item active">Statistics</li>
        </ol>
        <div>
            <a href="{{ route('admin.competitions.quizzes.show', [$competition, $quiz]) }}" 
               class="btn btn-outline-primary btn-sm">
                <i class='bx bx-list-ul me-1'></i> Manage Questions
            </a>
            <a href="{{ route('admin.competitions.quizzes.edit', [$competition, $quiz]) }}" 
               class="btn btn-outline-secondary btn-sm">
                <i class='bx bx-edit me-1'></i> Edit Quiz
            </a>
        </div>
    </nav>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Quiz Statistics</h5>
                    <div class="dropdown
                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="{{ route('admin.competitions.quizzes.statistics.export', [$competition, $quiz]) }}">
                                <i class="bx bx-download me-1"></i> Export Data
                            </a>
                            <a class="dropdown-item" href="#" id="printStats">
                                <i class="bx bx-printer me-1"></i> Print Report
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-3 mb-4 mb-md-0">
                            <div class="card bg-primary text-white stat-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-white-50 mb-1">Total Participants</h6>
                                            <h3 class="mb-0">{{ $statistics['total_participants'] }}</h3>
                                        </div>
                                        <div class="stat-icon bg-white bg-opacity-25">
                                            <i class='bx bx-user'></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4 mb-md-0">
                            <div class="card bg-success text-white stat-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-white-50 mb-1">Average Score</h6>
                                            <h3 class="mb-0">{{ number_format($statistics['average_score'], 1) }}%</h3>
                                        </div>
                                        <div class="stat-icon bg-white bg-opacity-25">
                                            <i class='bx bx-line-chart'></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4 mb-md-0">
                            <div class="card bg-info text-white stat-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-white-50 mb-1">Passing Rate</h6>
                                            <h3 class="mb-0">{{ number_format($statistics['passing_rate'], 1) }}%</h3>
                                        </div>
                                        <div class="stat-icon bg-white bg-opacity-25">
                                            <i class='bx bx-check-circle'></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-dark stat-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-dark-50 mb-1">Avg. Time Taken</h6>
                                            <h3 class="mb-0">{{ gmdate('i\m s\s', $statistics['average_time_taken']) }}</h3>
                                        </div>
                                        <div class="stat-icon bg-white bg-opacity-25 text-dark">
                                            <i class='bx bx-time-five'></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5>Score Distribution</h5>
                                </div>
                                <div class="card-body">
                                    <div id="scoreDistributionChart"></div>
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Question Analysis</h5>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="questionFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                            All Questions
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="questionFilterDropdown">
                                            <li><a class="dropdown-item active" href="#" data-filter="all">All Questions</a></li>
                                            <li><a class="dropdown-item" href="#" data-filter="difficult">Most Difficult</a></li>
                                            <li><a class="dropdown-item" href="#" data-filter="easy">Easiest</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover" id="questionAnalysisTable">
                                            <thead>
                                                <tr>
                                                    <th>Question</th>
                                                    <th>Type</th>
                                                    <th>Correct %</th>
                                                    <th>Attempts</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($statistics['questions'] as $question)
                                                    <tr data-difficulty="{{ $question['difficulty'] }}">
                                                        <td>{{ Str::limit($question['question'], 50) }}</td>
                                                        <td>
                                                            <span class="badge bg-label-{{ $question['type_color'] }}">
                                                                {{ $question['type'] }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="progress w-100 me-3" style="height: 6px;">
                                                                    <div class="progress-bar bg-{{ $question['correct_percentage'] > 70 ? 'success' : ($question['correct_percentage'] > 40 ? 'warning' : 'danger') }}" 
                                                                         role="progressbar" 
                                                                         style="width: {{ $question['correct_percentage'] }}%" 
                                                                         aria-valuenow="{{ $question['correct_percentage'] }}" 
                                                                         aria-valuemin="0" 
                                                                         aria-valuemax="100">
                                                                    </div>
                                                                </div>
                                                                <span>{{ $question['correct_percentage'] }}%</span>
                                                            </div>
                                                        </td>
                                                        <td>{{ $question['attempts'] }}</td>
                                                        <td>
                                                            <button class="btn btn-sm btn-outline-primary view-question" 
                                                                    data-question='@json($question)'>
                                                                <i class='bx bx-show'></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Top Performers</h5>
                                </div>
                                <div class="card-body">
                                    @if(count($statistics['top_performers']) > 0)
                                        <div class="mb-4">
                                            @foreach($statistics['top_performers'] as $index => $performer)
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="avatar me-3">
                                                        <span class="avatar-initial rounded-circle bg-label-primary">
                                                            {{ $index + 1 }}
                                                        </span>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-0">{{ $performer['user_name'] }}</h6>
                                                        <small class="text-muted">Score: {{ $performer['score'] }}%</small>
                                                    </div>
                                                    <span class="badge bg-label-{{ $performer['passed'] ? 'success' : 'danger' }}">
                                                        {{ $performer['passed'] ? 'Passed' : 'Failed' }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                        <a href="{{ route('admin.competitions.quizzes.participants', [$competition, $quiz]) }}" class="btn btn-outline-primary w-100">
                                            View All Participants
                                        </a>
                                    @else
                                        <div class="text-center py-4">
                                            <div class="mb-3">
                                                <i class='bx bx-user-x text-muted' style="font-size: 3rem;"></i>
                                            </div>
                                            <h6>No participants yet</h6>
                                            <p class="text-muted">Participants will appear here once they complete the quiz</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Quiz Completion</h5>
                                </div>
                                <div class="card-body">
                                    <div id="completionChart" style="min-height: 300px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Question Details Modal -->
<div class="modal fade" id="questionDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Question Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <h6>Question</h6>
                    <p id="questionText"></p>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div><strong>Type:</strong> <span id="questionType"></span></div>
                            <div><strong>Points:</strong> <span id="questionPoints"></span></div>
                        </div>
                        <div class="col-md-6">
                            <div><strong>Correct %:</strong> <span id="questionCorrectPercentage"></span></div>
                            <div><strong>Attempts:</strong> <span id="questionAttempts"></span></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h6>Options</h6>
                        <div id="questionOptions"></div>
                    </div>
                    
                    <div class="mb-3">
                        <h6>Correct Answer</h6>
                        <div id="correctAnswer" class="alert alert-success"></div>
                    </div>
                    
                    <div>
                        <h6>Common Incorrect Answers</h6>
                        <div id="incorrectAnswers">
                            <p class="text-muted">No common incorrect answers to display.</p>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Performance Over Time</h6>
                    </div>
                    <div class="card-body">
                        <div id="questionPerformanceChart" style="height: 250px;"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class='bx bx-x me-1'></i> Close
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/apexcharts/apexcharts.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize ApexCharts
        if (typeof ApexCharts !== 'undefined') {
            // Score Distribution Chart
            const scoreDistributionChart = new ApexCharts(
                document.querySelector("#scoreDistributionChart"),
                {
                    series: [{ 
                        name: 'Participants',
                        data: @json(array_values($statistics['score_distribution']))
                    }],
                    chart: {
                        type: 'bar',
                        height: 300,
                        toolbar: { show: false }
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            columnWidth: '45%',
                            distributed: true,
                        }
                    },
                    dataLabels: { enabled: false },
                    xaxis: {
                        categories: @json(array_keys($statistics['score_distribution'])),
                        title: { text: 'Score Range %' }
                    },
                    yaxis: {
                        title: { text: 'Number of Participants' }
                    },
                    colors: ['#696cff'],
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return val + ' participant' + (val !== 1 ? 's' : '');
                            }
                        }
                    }
                }
            );
            
            // Completion Chart
            const completionChart = new ApexCharts(
                document.querySelector("#completionChart"),
                {
                    series: [
                        @if($statistics['completion_rate'] > 0)
                            {{ $statistics['completion_rate'] }}, 
                            {{ 100 - $statistics['completion_rate'] }}
                        @else
                            100
                        @endif
                    ],
                    chart: {
                        type: 'radialBar',
                        height: 300,
                    },
                    plotOptions: {
                        radialBar: {
                            startAngle: -135,
                            endAngle: 135,
                            hollow: {
                                size: '70%',
                            },
                            track: {
                                background: '#f5f5f5',
                                strokeWidth: '97%',
                                margin: 5,
                            },
                            dataLabels: {
                                name: {
                                    fontSize: '16px',
                                    color: '#6c757d',
                                },
                                value: {
                                    offsetY: 20,
                                    fontSize: '24px',
                                    fontWeight: '600',
                                    formatter: function(val) {
                                        return val + '%';
                                    }
                                },
                                total: {
                                    show: true,
                                    label: 'Completion Rate',
                                    formatter: function(w) {
                                        return '{{ $statistics["completion_rate"] }}%';
                                    }
                                }
                            }
                        }
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shade: 'dark',
                            shadeIntensity: 0.15,
                            inverseColors: false,
                            opacityFrom: 1,
                            opacityTo: 1,
                            stops: [0, 50, 65, 91]
                        },
                    },
                    stroke: {
                        dashArray: 4
                    },
                    labels: ['Completed', 'Incomplete'],
                    colors: ['#71dd37', '#ffab00'],
                    legend: {
                        show: true,
                        position: 'bottom',
                        horizontalAlign: 'center',
                    }
                }
            );
            
            // Render charts
            scoreDistributionChart.render();
            completionChart.render();
        }
        
        // Question filtering
        document.querySelectorAll('[data-filter]').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const filter = this.dataset.filter;
                
                // Update active state
                document.querySelectorAll('[data-filter]').forEach(link => {
                    link.classList.remove('active');
                });
                this.classList.add('active');
                
                // Update dropdown text
                document.getElementById('questionFilterDropdown').textContent = this.textContent;
                
                // Filter rows
                const rows = document.querySelectorAll('#questionAnalysisTable tbody tr');
                rows.forEach(row => {
                    if (filter === 'all') {
                        row.style.display = '';
                    } else {
                        row.style.display = row.dataset.difficulty === filter ? '' : 'none';
                    }
                });
            });
        });
        
        // View question details
        document.querySelectorAll('.view-question').forEach(button => {
            button.addEventListener('click', function() {
                const question = JSON.parse(this.dataset.question);
                const modal = new bootstrap.Modal(document.getElementById('questionDetailsModal'));
                
                // Set question details
                document.getElementById('questionText').textContent = question.question;
                document.getElementById('questionType').textContent = question.type;
                document.getElementById('questionPoints').textContent = question.points + ' points';
                document.getElementById('questionCorrectPercentage').textContent = question.correct_percentage + '%';
                document.getElementById('questionAttempts').textContent = question.attempts;
                
                // Set options
                const optionsContainer = document.getElementById('questionOptions');
                if (question.options && question.options.length > 0) {
                    optionsContainer.innerHTML = '';
                    question.options.forEach((option, index) => {
                        const optionDiv = document.createElement('div');
                        optionDiv.className = 'form-check mb-2';
                        optionDiv.innerHTML = `
                            <div class="d-flex align-items-center">
                                <div class="form-check">
                                    <input class="form-check-input" type="${question.type === 'multiple_choice' ? 'checkbox' : 'radio'}" 
                                           ${question.correct_answers.includes(option) ? 'checked' : ''} disabled>
                                    <label class="form-check-label">
                                        ${option}
                                    </label>
                                </div>
                                ${question.type === 'multiple_choice' && question.correct_answers.includes(option) ? 
                                    '<span class="badge bg-success ms-2">Correct</span>' : ''}
                            </div>
                        `;
                        optionsContainer.appendChild(optionDiv);
                    });
                } else {
                    optionsContainer.innerHTML = '<p class="text-muted">No options for this question type.</p>';
                }
                
                // Set correct answer
                const correctAnswerEl = document.getElementById('correctAnswer');
                if (question.correct_answers && question.correct_answers.length > 0) {
                    correctAnswerEl.innerHTML = question.correct_answers.join('<br>');
                } else {
                    correctAnswerEl.innerHTML = '<em>No specific correct answer (essay question)</em>';
                }
                
                // Set common incorrect answers
                const incorrectAnswersEl = document.getElementById('incorrectAnswers');
                if (question.incorrect_answers && question.incorrect_answers.length > 0) {
                    incorrectAnswersEl.innerHTML = '';
                    question.incorrect_answers.forEach(answer => {
                        const div = document.createElement('div');
                        div.className = 'd-flex justify-content-between align-items-center mb-2';
                        div.innerHTML = `
                            <span>${answer.answer}</span>
                            <span class="badge bg-label-danger">${answer.count} time${answer.count !== 1 ? 's' : ''}</span>
                        `;
                        incorrectAnswersEl.appendChild(div);
                    });
                } else {
                    incorrectAnswersEl.innerHTML = '<p class="text-muted">No common incorrect answers to display.</p>';
                }
                
                // Initialize performance chart
                if (typeof ApexCharts !== 'undefined') {
                    const performanceChart = new ApexCharts(
                        document.querySelector("#questionPerformanceChart"),
                        {
                            series: [{
                                name: 'Correct %',
                                data: question.performance_over_time || []
                            }],
                            chart: {
                                height: 250,
                                type: 'line',
                                zoom: { enabled: false },
                                toolbar: { show: false }
                            },
                            dataLabels: { enabled: false },
                            stroke: {
                                curve: 'smooth',
                                width: 3
                            },
                            xaxis: {
                                type: 'datetime',
                                labels: {
                                    format: 'MMM dd',
                                    datetimeUTC: false
                                }
                            },
                            yaxis: {
                                min: 0,
                                max: 100,
                                labels: {
                                    formatter: function(val) {
                                        return val + '%';
                                    }
                                }
                            },
                            tooltip: {
                                x: {
                                    format: 'MMM dd, yyyy'
                                },
                                y: {
                                    formatter: function(val) {
                                        return val + '% correct';
                                    }
                                }
                            },
                            colors: ['#696cff']
                        }
                    );
                    
                    // Store chart instance for cleanup
                    if (window.questionPerformanceChart) {
                        window.questionPerformanceChart.destroy();
                    }
                    window.questionPerformanceChart = performanceChart;
                    performanceChart.render();
                }
                
                // Show the modal
                modal.show();
            });
        });
        
        // Print report
        document.getElementById('printStats').addEventListener('click', function(e) {
            e.preventDefault();
            window.print();
        });
        
        // Add print styles
        const style = document.createElement('style');
        style.textContent = `
            @media print {
                body * {
                    visibility: hidden;
                }
                #printableArea, #printableArea * {
                    visibility: visible;
                }
                #printableArea {
                    position: absolute;
                    left: 0;
                    top: 0;
                    width: 100%;
                }
                .card {
                    border: none;
                    box-shadow: none;
                }
                .stat-card {
                    transform: none !important;
                }
            }
        `;
        document.head.appendChild(style);
    });
</script>
@endpush
