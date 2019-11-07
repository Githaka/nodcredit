<form action="{{route('admin.score-config.store')}}" method="post">
    {!! csrf_field() !!}
    <tr class="c-table__row">
        <td class="c-table__cell">
            <input type="hidden" name="type" value="nested">
            <input type="hidden" name="id" value="{{$scoreConfig->id}}">
           {{$scoreConfig->name}}
        </td>

        <td class="c-table__cell">
            <table class="c-table">
                <thead class="c-table__head">
                <tr class="c-table__row">
                    <th class="c-table__cell c-table__cell--head">Name</th>
                    <th class="c-table__cell c-table__cell--head">Range/Amount</th>
                    <th class="c-table__cell c-table__cell--head">Score</th>
                </tr>
                </thead>
                <tbody>

                    @foreach($scoreConfig->frequencies as $frequencies)
                        <tr>
                            <td class="c-table__cell">
                                {{isset($frequencies['between']) ? 'Between' : 'Amount'}}
                                <input type="hidden" name="config_type" value="{{isset($frequencies['between']) ? 'between' : 'amount'}}">
                            </td>
                            <td class="c-table__cell">
                                <input type="text"  class="c-input" value="{{isset($frequencies['between']) ? $frequencies['between'] : $frequencies['amount']}}" name=" {{isset($frequencies['between']) ? 'between[]' : 'amount[]'}}">

                            </td>
                            <td class="c-table__cell">
                                <input type="text"  class="c-input" value="{{$frequencies['score']}}" name="score[]">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </td>

        <td class="c-table__cell">
            <button type="submit" class="c-btn c-btn--primary">Save</button>
        </td>
    </tr>
</form>
