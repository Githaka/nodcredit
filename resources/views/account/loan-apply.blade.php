@extends('account.layout.main')

@section('content-container-header')

    @widget(\App\Widgets\DisburseWarning::class)

    @widget(\App\Widgets\ChecklistWarning::class)

@endsection


@section('content-container-body')
    <div class="row">
        <div class="col-md-12">
            <div  id="root">Loading...</div>
        </div>
    </div>
@endsection

@section('scripts-footer')
    <script src="{{asset('js/loan-apply.js')}}"></script>
@endsection