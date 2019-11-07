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
                <div class="col-md-12">
                    <div class="c-card" data-mh="dashboard3-cards">
                        <h4>Edit message "{{ $template->getName() }}"</h4>
                        <p  class="u-mb-medium"></p>

                        <form action="{{route('admin.message-templates.store', $template->getId())}}" method="post">
                            {!! csrf_field() !!}
                            <div class="row">
                                <div class="col-xl-6">

                                    <div class="c-field u-mb-medium">
                                        <label class="c-field__label">Channel</label>
                                        <select class="c-input" name="channel">
                                            @foreach($channels as $channel)
                                                <option value="{{ $channel['value'] }}" {{ $template->getChannel() === $channel['value'] ? 'selected' : '' }}>{{ $channel['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="c-field u-mb-medium">
                                        <label class="c-field__label">Title</label>
                                        <input class="c-input" name="title" value="{{ $template->getTitle() ? $template->getTitle() :  old('title')}}" type="text" placeholder="Type subject">
                                        <p>This is only applicable to email</p>
                                    </div>


                                    <div class="c-field u-mb-medium">
                                        <label class="c-field__label">Message</label>
                                        <textarea name="message" cols="30" rows="5" class="c-input">{!! $template->getMessage() ? $template->getMessage() : old('message') !!}</textarea>
                                        <p>SMS must be short (max of 160 characters)</p>
                                    </div>

                                    @if ($errors)
                                    <div class="form-errors">
                                        @foreach($errors as $error)
                                            <p style="color: red;">{{ $error[0] }}</p>
                                        @endforeach
                                    </div>
                                    @endif

                                    <div class="c-field u-mb-medium">
                                        <button class="c-btn c-btn--info c-btn--fullwidth" type="submit">Save</button>
                                    </div>

                                </div>
                            </div>
                        </form>
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
