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
                        <h4>Message templates</h4>

                        <p  class="u-mb-medium"></p>

                        <table class="c-table message-templates-table">
                            <thead class="c-table__head">
                                <tr class="c-table__row">
                                    <th class="c-table__cell c-table__cell--head">Name</th>
                                    <th class="c-table__cell c-table__cell--head">Title</th>
                                    <th class="c-table__cell c-table__cell--head">Message</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($templates->all() as $template)
                                    <tr class="c-table__row">
                                        <th class="c-table__cell"><a href="{{route('admin.message-templates.edit', $template->getId())}}">{{$template->getName()}}</a></th>
                                        <th class="c-table__cell">{{ $template->getTitle() }}</th>
                                        <th class="c-table__cell">{!! $template->getMessage() !!}</th>
                                    </tr>
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
