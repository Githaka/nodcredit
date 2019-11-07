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
                        <h4 class="u-mb-medium">Send Message To {{$user->name}}</h4>

                        <form action="{{route('admin.accounts.message.send', $user->id)}}" method="post">
                            {!! csrf_field() !!}
                            <div class="row">
                                <div class="col-xl-6">

                                    <div class="c-field u-mb-medium">
                                        <label class="c-field__label" for="amount">Subject</label>
                                        <input class="c-input" name="subject" value="{{old('subject')}}" type="text" id="subject" placeholder="Type subject">
                                        <p>This is only applicable to email</p>
                                        @if($errors->has('subject'))
                                            <p style="color: red;">{{$errors->first('subject')}}</p>
                                        @endif
                                    </div>



                                    <div class="c-field u-mb-medium">
                                        <label class="c-field__label" for="message_type">Message Type</label>
                                        <div class="c-select">
                                            <select name="message_type" id="message_type" class="c-select__input">
                                                <option value="sms" @if(old('message_type') == 'sms') selected="selected" @endif>SMS</option>
                                                <option value="email" @if(old('message_type') == 'email') selected="selected" @endif>Email</option>
                                                <option value="both" @if(old('message_type') == 'both') selected="selected" @endif>SMS & Email</option>
                                            </select>
                                        </div>
                                        @if($errors->has('message_type'))
                                            <p style="color: red;">{{$errors->first('message_type')}}</p>
                                        @endif
                                    </div>

                                    <div class="c-field u-mb-medium">
                                        <label class="c-field__label" for="amount">Message</label>
                                        <textarea name="message" id="" cols="30" rows="5" class="c-input">{{old('message')}}</textarea>
                                        <p>SMS must be short (max of 160 characters)</p>
                                        @if($errors->has('message'))
                                            <p style="color: red;">{{$errors->first('message')}}</p>
                                        @endif
                                    </div>

                                    <div class="c-field u-mb-medium">
                                        <button class="c-btn c-btn--info c-btn--fullwidth" type="submit">Send Message</button>
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
