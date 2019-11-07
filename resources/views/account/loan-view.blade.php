@extends('account.layout.main')

@section('content-container-body')
    <div class="row">
        <div class="col-md-4">
            <div class="c-card" data-mh="dashboard3-cards">
                <h4>Loan Status: {{$loan->status}}</h4>
                <p class="u-mb-medium">
                    Loan re-payment plan below
                    <br><strong>Amount requested: NGN {{number_format($loan->amount_requested,2)}}</strong>
                </p>


                @foreach($loan->payments as $payment)
                    <div class="o-line u-pb-small u-mb-small u-border-bottom">
                        <div class="o-media">
                            <div class="o-media__body">
                                <h6>{{$payment->due_at}}</h6>
                            </div>
                        </div>
                        <h6>{{$payment->getAmount()}}</h6>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="col-md-4">
            <div class="c-card" data-mh="dashboard3-cards">

                <h4>Upload Loan Documents</h4>
                <p class="u-mb-medium">You are required to upload at least {{$documentInfo->required}} document{{$documentInfo->required > 1 ? 's' : ''}}. <strong>{{implode(',',$documentInfo->requiredNames)}}</strong></p>

                @if (auth()->user()->bank)
                    <div class="c-alert c-alert--warning u-mb-medium">
                        <p>Bank Statement must be provided by <b>{{ auth()->user()->bank->name }}</b>.</p>
                        <p>Make sure your pages are not more than <b>200</b> pages.</p>
                    </div>
                @endif

                <form action="{{route('account.loans.upload-document', $loan->id)}}" method="post" enctype="multipart/form-data">
                    {!! csrf_field() !!}
                    <div class="c-field u-mb-small">
                        <label class="c-field__label">Select Document Type</label>
                        <div class="c-select">
                            <select class="c-select__input js-loan-application-document-type" name="documentType">
                                @foreach($documentTypes as $documentType)

                                    <option value="{{$documentType->id}}">{{$documentType->name}}</option>

                                @endforeach
                            </select>

                            @if($errors->has('documentType'))
                                <p style="color: red;">{{$errors->first('documentType')}}</p>
                            @endif

                        </div>
                    </div>

                    <div class="c-field u-mb-small">
                        <label class="c-field__label">Select file to upload</label>
                        <div class="c-field">
                            <input type="file" name="file" id="" class="c-select__input" />
                            @if($errors->has('file'))
                                <p style="color: red;">{{$errors->first('file')}}</p>
                            @endif
                        </div>
                    </div>

                    <div class="c-field u-mb-small js-loan-application-document-password" style="display: none;" >
                        <label class="c-field__label">Password (if document is locked)</label>
                        <div class="c-field">
                            <input type="text" name="unlock_password" class="c-select__input" />
                        </div>
                    </div>

                    <div class="c-field u-mb-small">
                        <button class="c-btn c-btn--fullwidth c-btn--info" type="submit">Upload</button>
                    </div>

                    <p>You have uploaded <span class="c-badge c-badge--small c-badge--danger">{{$documentInfo->uploadedRequired}} of {{$documentInfo->required}}</span> required documents</p>
                </form>

            </div>
        </div>

        <div class="col-md-4">
            <div class="c-card" data-mh="dashboard3-cards">
                <h4>Uploaded Documents</h4>
                <div class="c-card" data-mh="dashboard3-cards">
                    @foreach($loan->documents as $document)
                        <div class="o-line u-pb-small u-mb-small u-border-bottom">
                            <div class="o-media">
                                <div class="o-media__body">
                                    <h6>{{$document->description}} ({{$document->document_extension}})</h6>
                                    <p>Uploaded on {{$document->created_at}}</p>

                                    @if ($document->unlock_password)
                                        <p>Password: <b>{{ $document->unlock_password }}</b></p>
                                    @endif
                                </div>
                            </div>
                            <h6><a class="c-badge c-badge--small c-badge--danger" href="?deleteDocument={{$document->id}}" onclick="return confirm('Are you sure you want to delete this document?')">X</a></h6>
                        </div>
                    @endforeach
                </div>

            </div>
        </div>
    </div>

    @include('account.loan-actions')

@endsection

@section('scripts-footer')

    <script>
        $(document).ready(function() {

            function refreshUnlockPasswordState() {

                // Bank statement
                if ($('.js-loan-application-document-type').val() === 'ca33a080-a32f-11e8-88df-3bf70087289e') {
                    $('.js-loan-application-document-password').fadeIn();
                }
                else {
                    $('.js-loan-application-document-password').fadeOut();
                }

                $('[data-mh]').matchHeight();
            }

            $('.js-loan-application-document-type').on('change', function () {
                refreshUnlockPasswordState();
            });


            refreshUnlockPasswordState();

        });
    </script>


@endsection
