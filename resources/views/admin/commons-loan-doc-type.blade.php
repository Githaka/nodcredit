@include('dashboard.includes.header')
<body>


<div class="o-page">
    <div class="o-page__sidebar js-page-sidebar">
        @include('dashboard.includes.sidebar')
    </div>
    <main class="o-page__content">

        <span id="error-messages"></span>

        @include('dashboard.includes.header-nav')

        <div class="container">

            <div class="row">
                <div class="col-12">
                    <div class="c-table-responsive@wide">
                        <h4>Loan Document Type</h4>
                        <p class="u-mb-medium">You can add validation to the document type.</p>


                        <table class="c-table">
                            <thead class="c-table__head">
                            <tr class="c-table__row">
                                <th class="c-table__cell c-table__cell--head">Name</th>
                                <th class="c-table__cell c-table__cell--head">Is Required</th>
                                <th class="c-table__cell c-table__cell--head">File Type</th>
                                <th class="c-table__cell c-table__cell--head">Action</th>
                            </tr>

                            <form action="{{route('admin.commons.loan.doc-type.store')}}" method="post">
                                {!! csrf_field() !!}
                                <tr class="c-table__row">
                                    <td class="c-table__cell">
                                        <input type="text" class="c-input" placeholder="Document type name" name="name">
                                    </td>
                                    <th class="c-table__cell">
                                        <div class="c-select u-mb-xsmall">
                                            <select class="c-select__input" type="text" name="is_required">
                                                <option value="1">Yes</option>
                                                <option value="0">No</option>
                                            </select>
                                        </div>
                                    </th>
                                    <td class="c-table__cell"><input type="text" class="c-input" placeholder="e.g pdf,csv,docx" name="file_type"></td>
                                    <td class="c-table__cell">
                                        <button class="c-btn c-btn--info" >Create</button>
                                    </td>
                                </tr>
                            </form>
                            </thead>

                            <tbody>


                            @foreach($documentTypes as $documentType)
                                <form action="{{route('admin.commons.loan.doc-type.store')}}" method="post">
                                    {!! csrf_field() !!}
                                    <input type="hidden" name="id" value="{{$documentType->id}}">
                                    <tr class="c-table__row">
                                        <td class="c-table__cell">
                                            <input type="text" class="c-input" placeholder="Document type name" name="name" value="{{$documentType->name}}">
                                        </td>
                                        <th class="c-table__cell">
                                            <div class="c-select u-mb-xsmall">
                                                <select class="c-select__input" type="text" name="is_required">
                                                    <option value="1" @if($documentType->is_required) selected="selected" @endif>Yes</option>
                                                    <option value="0" @if(!$documentType->is_required) selected="selected" @endif>No</option>
                                                </select>
                                            </div>
                                        </th>
                                        <td class="c-table__cell"><input type="text" class="c-input" placeholder="e.g pdf,csv,docx" name="file_type" value="{{$documentType->file_type}}"></td>
                                        <td class="c-table__cell">
                                            <button class="c-btn c-btn--success" >Update</button>
                                        </td>
                                    </tr>
                                </form>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @include('dashboard.includes.footer')

        </div>
    </main>
</div>

<!-- Main JavaScript -->
@include('dashboard.includes.account-footer')

</body>
</html>
