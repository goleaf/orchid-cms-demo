@php
    /** @var \Illuminate\Support\Collection<int, \App\Enums\LeadStatus> $statuses */
    $pipelineRoute = request()->routeIs('platform.marketing.*') ? 'platform.marketing.pipeline' : 'platform.crm.pipeline';
    $leadEditRoute = request()->routeIs('platform.marketing.*') ? 'platform.marketing.leads.edit' : 'platform.crm.leads.edit';
@endphp

<div class="bg-white rounded shadow-sm p-4 mb-4">
    <form method="GET" action="{{ route($pipelineRoute) }}" class="row g-3 align-items-end">
        <div class="col-md-2">
            <label class="form-label">{{ $labels['manager'] }}</label>
            <select class="form-select" name="manager_id">
                <option value="">{{ $labels['all_managers'] }}</option>
                @forelse ($filterOptions['managers'] as $id => $name)
                    <option value="{{ $id }}" @selected((string) $filters['manager_id'] === (string) $id)>{{ $name }}</option>
                @empty
                    <option value="" disabled>{{ $labels['no_managers_found'] }}</option>
                @endforelse
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">{{ $labels['source'] }}</label>
            <select class="form-select" name="source">
                <option value="">{{ $labels['all_sources'] }}</option>
                @forelse ($filterOptions['sources'] as $value => $label)
                    <option value="{{ $value }}" @selected((string) $filters['source'] === (string) $value)>{{ $label }}</option>
                @empty
                    <option value="" disabled>{{ $labels['no_sources_found'] }}</option>
                @endforelse
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">{{ $labels['course'] }}</label>
            <select class="form-select" name="training_program_id">
                <option value="">{{ $labels['all_courses'] }}</option>
                @forelse ($filterOptions['programs'] as $id => $name)
                    <option value="{{ $id }}" @selected((string) $filters['training_program_id'] === (string) $id)>{{ $name }}</option>
                @empty
                    <option value="" disabled>{{ $labels['no_courses_found'] }}</option>
                @endforelse
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">{{ $labels['category'] }}</label>
            <select class="form-select" name="license_category">
                <option value="">{{ $labels['all_categories'] }}</option>
                @forelse ($filterOptions['categories'] as $value => $label)
                    <option value="{{ $value }}" @selected((string) $filters['license_category'] === (string) $value)>{{ $label }}</option>
                @empty
                    <option value="" disabled>{{ $labels['no_categories_found'] }}</option>
                @endforelse
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">{{ $labels['branch'] }}</label>
            <select class="form-select" name="branch_id">
                <option value="">{{ $labels['all_branches'] }}</option>
                @forelse ($filterOptions['branches'] as $id => $name)
                    <option value="{{ $id }}" @selected((string) $filters['branch_id'] === (string) $id)>{{ $name }}</option>
                @empty
                    <option value="" disabled>{{ $labels['no_branches_found'] }}</option>
                @endforelse
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">{{ $labels['flags'] }}</label>
            <div class="d-flex gap-3">
                <label class="form-check">
                    <input class="form-check-input" type="checkbox" name="only_my" value="1" @checked($filters['only_my'] === '1')>
                    <span class="form-check-label">{{ $labels['only_my'] }}</span>
                </label>
                <label class="form-check">
                    <input class="form-check-input" type="checkbox" name="hot" value="1" @checked($filters['hot'] === '1')>
                    <span class="form-check-label">{{ $labels['hot'] }}</span>
                </label>
                <label class="form-check">
                    <input class="form-check-input" type="checkbox" name="overdue" value="1" @checked($filters['overdue'] === '1')>
                    <span class="form-check-label">{{ $labels['overdue'] }}</span>
                </label>
            </div>
        </div>
        <div class="col-md-2">
            <label class="form-label">{{ $labels['created_from'] }}</label>
            <input class="form-control" type="date" name="created_from" value="{{ $filters['created_from'] }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">{{ $labels['created_to'] }}</label>
            <input class="form-control" type="date" name="created_to" value="{{ $filters['created_to'] }}">
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button class="btn btn-primary w-100" type="submit">{{ $labels['filter'] }}</button>
            <a class="btn btn-outline-secondary" href="{{ route($pipelineRoute) }}">{{ $labels['reset'] }}</a>
        </div>
    </form>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="bg-white rounded shadow-sm p-3">
            <div class="text-muted small">{{ $labels['leads_in_view'] }}</div>
            <div class="h3 mb-0">{{ $report['total'] }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="bg-white rounded shadow-sm p-3">
            <div class="text-muted small">{{ $labels['became_students'] }}</div>
            <div class="h3 mb-0">{{ $report['became_students'] }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="bg-white rounded shadow-sm p-3">
            <div class="text-muted small">{{ $labels['conversion'] }}</div>
            <div class="h3 mb-0">{{ $report['conversion_rate'] }}%</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="bg-white rounded shadow-sm p-3">
            <div class="text-muted small">{{ $labels['loss_rate'] }}</div>
            <div class="h3 mb-0">{{ $report['loss_rate'] }}%</div>
        </div>
    </div>
</div>

<div class="bg-white rounded shadow-sm p-3 mb-4">
    <div class="d-flex flex-wrap gap-3 align-items-center">
        <strong>{{ $labels['status_report'] }}</strong>
        @forelse ($report['by_status'] as $item)
            <span class="badge bg-light text-dark">{{ $statusLabels[$item['status']->value] ?? $item['status']->value }}: {{ $item['count'] }}</span>
        @empty
            <span class="text-muted small">{{ $labels['no_statuses_in_filter'] }}</span>
        @endforelse
    </div>
    <div class="d-flex flex-wrap gap-3 align-items-center mt-2">
        <strong>{{ $labels['loss_reasons'] }}</strong>
        @forelse ($report['loss_reasons'] as $reason => $count)
            <span class="badge bg-warning text-dark">{{ $lostReasonLabels[$reason] ?? $reason }}: {{ $count }}</span>
        @empty
            <span class="text-muted small">{{ $labels['no_loss_reasons'] }}</span>
        @endforelse
    </div>
</div>

<form id="lead-pipeline-move" method="POST" action="{{ route($pipelineRoute, ['method' => 'moveLead']) }}" class="d-none">
    @csrf
    <input type="hidden" name="lead_id" id="lead-pipeline-lead-id">
    <input type="hidden" name="status" id="lead-pipeline-status">
    <input type="hidden" name="reason" id="lead-pipeline-reason">
</form>

<div class="lead-pipeline-board">
    @forelse ($statuses as $status)
        <section class="lead-pipeline-column" data-status="{{ $status->value }}">
            <header class="lead-pipeline-column-head">
                <strong>{{ $statusLabels[$status->value] ?? $status->value }}</strong>
                <span>{{ $columns[$status->value]->count() }}</span>
            </header>

            <div class="lead-pipeline-dropzone" data-status="{{ $status->value }}">
                @forelse ($columns[$status->value] as $lead)
                    <article class="lead-pipeline-card" draggable="true" data-lead-id="{{ $lead->id }}">
                        <div class="d-flex justify-content-between gap-2">
                            <a href="{{ route($leadEditRoute, $lead) }}"><strong>{{ $lead->fullName() }}</strong></a>
                            @if ($lead->is_hot)
                                <span class="badge bg-danger">{{ $labels['hot'] }}</span>
                            @endif
                        </div>
                        <div class="text-muted small">{{ $lead->phone ?? $lead->email ?? $labels['no_contact'] }}</div>
                        <div class="text-muted small">{{ $lead->trainingProgram?->title ?? $labels['no_course'] }} · {{ $lead->branch?->city ?? $labels['no_branch'] }}</div>
                        <div class="d-flex flex-wrap gap-1 mt-2">
                            <span class="badge bg-light text-dark">{{ $sourceLabels[$lead->source] ?? $lead->source }}</span>
                            <span class="badge bg-light text-dark">{{ $lead->license_category ?? '-' }}</span>
                            <span class="badge bg-light text-dark">{{ $lead->responsibleManager?->name ?? $labels['no_manager'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3 small">
                            <span>{{ $lead->budgetForHumans() }}</span>
                            <span @class(['text-danger fw-bold' => $lead->overdue_tasks_count > 0 || ($lead->next_follow_up_at && $lead->next_follow_up_at->isPast())])>
                                {{ $lead->next_follow_up_at?->format('Y-m-d H:i') ?? $labels['no_follow_up'] }}
                            </span>
                        </div>
                        <div class="text-muted small mt-1">
                            {{ strtr($labels['task_summary'], [
                                ':tasks' => $lead->open_tasks_count,
                                ':communications' => $lead->communications_count,
                                ':comments' => $lead->comments_count,
                            ]) }}
                        </div>
                    </article>
                @empty
                    <div class="lead-pipeline-empty">{{ $labels['no_leads'] }}</div>
                @endforelse
            </div>
        </section>
    @empty
        <section class="lead-pipeline-column">
            <header class="lead-pipeline-column-head">
                <strong>{{ $labels['no_statuses'] }}</strong>
                <span>0</span>
            </header>
            <div class="lead-pipeline-dropzone">
                <div class="lead-pipeline-empty">{{ $labels['no_pipeline_statuses'] }}</div>
            </div>
        </section>
    @endforelse
</div>

<style>
    .lead-pipeline-board {
        display: grid;
        grid-auto-columns: minmax(280px, 320px);
        grid-auto-flow: column;
        gap: 14px;
        overflow-x: auto;
        padding-bottom: 10px;
    }

    .lead-pipeline-column {
        display: grid;
        grid-template-rows: auto 1fr;
        min-height: 520px;
        border: 1px solid #d9e0ea;
        border-radius: 8px;
        background: #f7f9fb;
    }

    .lead-pipeline-column-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-height: 52px;
        padding: 12px;
        border-bottom: 1px solid #d9e0ea;
        background: #fff;
        border-radius: 8px 8px 0 0;
    }

    .lead-pipeline-dropzone {
        display: grid;
        align-content: start;
        gap: 10px;
        padding: 10px;
        min-height: 460px;
    }

    .lead-pipeline-dropzone.is-drag-over {
        outline: 2px dashed #0f766e;
        outline-offset: -6px;
        background: #ecfdf5;
    }

    .lead-pipeline-card {
        padding: 12px;
        border: 1px solid #d9e0ea;
        border-radius: 8px;
        background: #fff;
        cursor: grab;
    }

    .lead-pipeline-card:active {
        cursor: grabbing;
    }

    .lead-pipeline-empty {
        padding: 16px;
        border: 1px dashed #d9e0ea;
        border-radius: 8px;
        color: #6b7280;
        text-align: center;
    }
</style>

<script>
    (() => {
        const form = document.getElementById('lead-pipeline-move');
        const leadInput = document.getElementById('lead-pipeline-lead-id');
        const statusInput = document.getElementById('lead-pipeline-status');
        const reasonInput = document.getElementById('lead-pipeline-reason');

        document.querySelectorAll('.lead-pipeline-card').forEach((card) => {
            card.addEventListener('dragstart', (event) => {
                event.dataTransfer.setData('text/plain', card.dataset.leadId);
            });
        });

        document.querySelectorAll('.lead-pipeline-dropzone').forEach((zone) => {
            zone.addEventListener('dragover', (event) => {
                event.preventDefault();
                zone.classList.add('is-drag-over');
            });

            zone.addEventListener('dragleave', () => {
                zone.classList.remove('is-drag-over');
            });

            zone.addEventListener('drop', (event) => {
                event.preventDefault();
                zone.classList.remove('is-drag-over');

                leadInput.value = event.dataTransfer.getData('text/plain');
                statusInput.value = zone.dataset.status;
                reasonInput.value = '';
                form.submit();
            });
        });
    })();
</script>
