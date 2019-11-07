<form action="{{route('admin.score-config.store')}}" method="post">
    {!! csrf_field() !!}

    <tr class="c-table__row">
        <td class="c-table__cell">
            <input type="hidden" name="type" value="flat">
            <input type="hidden" name="id" value="{{$scoreConfig->id}}">
            <input type="text" class="c-input" value="{{$scoreConfig->name}}" name="name">
        </td>

        <td class="c-table__cell">
            <input type="text" class="c-input" value="{{$scoreConfig->score}}" name="score">
        </td>

        <td class="c-table__cell">
            <button type="submit" class="c-btn c-btn--primary">Save</button>
        </td>
    </tr>
</form>
