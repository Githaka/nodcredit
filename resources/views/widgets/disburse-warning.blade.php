@if ($automationActive === 0)
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="c-alert c-alert--warning u-mb-medium">
                <span class="c-alert__icon"><i class="feather icon-alert-triangle"></i></span>
                <div class="c-alert__content">

                    @if (! $hasCompletedLoans AND ($hasNewLoan AND $newLoan->required_documents_uploaded))
                        <h5>We are currently not disbursing loans at the moment, system will notify you once we start!</h5>
                    @elseif($hasCompletedLoans AND $hasNewLoan)
                        <h5>We are currently not disbursing loans at the moment, system will notify you once we start!</h5>

                        @if (! $newLoan->required_documents_uploaded)
                            <h5>Go Ahead and Complete your application.</h5>
                        @endif

                    @endif

                </div>
            </div>
        </div>
    </div>

@elseif($automationActive === 1 AND ! $hasCompletedLoans AND ($hasNewLoan AND $newLoan->required_documents_uploaded))
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="c-alert c-alert--warning u-mb-medium">
                <span class="c-alert__icon"><i class="feather icon-alert-triangle"></i></span>
                <div class="c-alert__content">
                    <h5>We are currently not disbursing loans at the moment, system will notify you once we start!</h5>
                </div>
            </div>
        </div>
    </div>
@endif