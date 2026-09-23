@if ($procedureFlow->isNotEmpty())
    @php
        $programme = $procedureFlow->first();
    @endphp
    <div class="modal fade procedure-flow-modal" id="studentProgrammeFlowModal" tabindex="-1"
        aria-labelledby="studentProgrammeFlowModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <div class="text-muted small mb-1">Your programme journey</div>
                        <h5 class="modal-title" id="studentProgrammeFlowModalLabel">
                            {{ $programme->prog_code }} ({{ $programme->prog_mode }})
                        </h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3 p-md-4">
                    <div class="procedure-flow-summary d-flex flex-column flex-lg-row justify-content-between gap-3 p-3 mb-4">
                        <div>
                            <div class="fw-semibold">{{ $programme->prog_name }}</div>
                            <div class="small text-muted">
                                {{ $procedureFlow->count() }} programme
                                {{ Str::plural('activity', $procedureFlow->count()) }} in sequence order.
                            </div>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-3 small" aria-label="Flow legend">
                            <span class="procedure-flow-legend-item">
                                <span class="procedure-flow-legend-mark is-open" aria-hidden="true"></span>
                                Open: available at its semester
                            </span>
                            <span class="procedure-flow-legend-item">
                                <span class="procedure-flow-legend-mark is-locked" aria-hidden="true"></span>
                                Locked: complete earlier activities
                            </span>
                        </div>
                    </div>

                    <div class="procedure-flow-scroll" tabindex="0"
                        aria-label="Scrollable programme flow for {{ $programme->prog_code }} {{ $programme->prog_mode }}">
                        <div class="procedure-flow-track" role="list">
                            @foreach ($procedureFlow as $procedure)
                                @php
                                    $isRecord = (int) $procedure->activity_type === 2;
                                    $isOpen = !$isRecord && (int) $procedure->init_status === 1;
                                    $nodeClass = $isRecord ? 'is-record' : ($isOpen ? 'is-open' : 'is-locked');
                                    $nextProcedure = $procedureFlow->get($loop->index + 1);
                                    $nextIsOpen = $nextProcedure
                                        && (int) $nextProcedure->activity_type !== 2
                                        && (int) $nextProcedure->init_status === 1;
                                @endphp
                                <article class="procedure-flow-node {{ $nodeClass }}" role="listitem">
                                    <div class="p-3 p-lg-4 d-flex flex-column h-100">
                                        <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                                            <span class="procedure-flow-sequence">{{ $procedure->act_seq }}</span>
                                            @if ($isRecord)
                                                <span class="procedure-flow-status is-record">Record-based</span>
                                            @elseif ($isOpen)
                                                <span class="procedure-flow-status is-open">Open</span>
                                            @else
                                                <span class="procedure-flow-status is-locked">Locked</span>
                                            @endif
                                        </div>

                                        <h6 class="mb-3">{{ $procedure->act_name }}</h6>

                                        <div class="procedure-flow-timeline d-flex gap-3 p-3 mb-3">
                                            <div>
                                                <div class="small text-muted">Semester</div>
                                                <div class="fw-bold">{{ $procedure->timeline_sem }}</div>
                                            </div>
                                            <div class="border-start ps-3">
                                                <div class="small text-muted">Week</div>
                                                <div class="fw-bold">{{ $procedure->timeline_week }}</div>
                                            </div>
                                        </div>

                                        <p class="small text-muted mb-3">
                                            @if ($isRecord)
                                                Recorded administratively according to the programme timeline.
                                            @elseif ($isOpen)
                                                Available when you reach this semester; earlier activities are not required.
                                            @else
                                                Available after you reach this semester and complete all earlier activities.
                                            @endif
                                        </p>

                                        @if ((int) $procedure->is_repeatable === 1)
                                            <div class="procedure-flow-repeat-note mb-3">
                                                <i class="ti ti-refresh" aria-hidden="true"></i>
                                                Repeats every semester
                                            </div>
                                        @endif

                                        <div class="d-flex flex-wrap gap-1 mt-auto">
                                            @if ((int) $procedure->is_haveEva === 1)
                                                <span class="badge bg-light-primary text-primary">Evaluation required</span>
                                            @endif
                                            @if ((int) $procedure->is_haveJournalPublication === 1)
                                                <span class="badge bg-light-info text-info">Journal required</span>
                                            @endif
                                            @if ($procedure->material)
                                                <span class="badge bg-light-secondary text-secondary">Guidance material available</span>
                                            @endif
                                        </div>
                                    </div>
                                </article>

                                @unless ($loop->last)
                                    <div class="procedure-flow-connector {{ $nextIsOpen ? 'is-open' : 'is-locked' }}"
                                        aria-hidden="true">
                                        <span class="procedure-flow-connector-label">
                                            {{ $nextIsOpen ? 'Available by timeline' : 'Complete previous' }}
                                        </span>
                                        <span class="procedure-flow-connector-line"></span>
                                    </div>
                                @endunless
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endif
