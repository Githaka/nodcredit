<div class="row">
    <div class="col-12">
        <div class="c-table-responsive@wide">
            <div class="c-card" data-mh="dashboard3-cards">
                <h3 class="u-mb-medium">Actions on your loan</h3>

                <table class="c-table">
                    <tbody>
                    @foreach($loanActions as $loanAction)
                        <tr class="c-table__row">
                            <td class="c-table__cell">
                                {{$loanAction->action}} by <small><em>
                                        <strong>{{$loanAction->user->role === 'admin' ? 'admin': $loanAction->user->name}}</strong>
                                    </em></small> at <small>{{$loanAction->created_at}}</small>
                                @if(Auth::user()->role === 'admin')
                                    - Info: <strong>{{$loanAction->finger_print}}</strong>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
