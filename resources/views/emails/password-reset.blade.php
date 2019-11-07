<p>
    Hello {{$user->name}},
</p>

<p>
    You or someone has requested for a password reset on your NodCredit account, to reset <a href="{{route('auth.reset-password', $token)}}">click here</a> or copy this link to your browser
</p>

<p>
    <code>
        {{route('auth.reset-password', $token)}}
    </code>
</p>

<p>
    If you did not make this request, you can ignore this email.
</p>

<p>
    - NodCredit Team
</p>
