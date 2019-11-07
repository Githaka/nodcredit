<script src="{{asset('dashboard/js/mad.min.js')}}"></script>
<script src="/js/app.js?v={{ filemtime(public_path('js/app.js')) }}"></script>

<script>

    function translateErrorMessages(errors) {
        var errorsHtml =[];
        $.each( errors, function( key, value ) {
            $.each( value, function( key2, error ) {
                errorsHtml.push(error);
            });
        });

        alert(errorsHtml.join('\n'));
    }


    setTimeout(function(){
        if ($('.ui-alert').length > 0) {
            $('.ui-alert').remove();
        }
    }, 3000);

    function calculateInvestInterest(amount, days) {
        let paProfit = (amount / 100) * days.percentage;
        let profit =  (paProfit / 365) *  days.days;
        return profit;
    }

    function getInvestmentType(name) {
        var investmentConfig = JSON.parse('{!! get_setting('investmentConfig') !!}');
        return investmentConfig.filter(function(item) {
                return item.value === name;
        })[0];
    }


    $(document).ready(function(){

        let token = document.head.querySelector('meta[name="csrf-token"]');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': token.content
            }
        });

        // update profile form
        $('#account-form').submit(function(e){
            e.preventDefault();
            var self  = $(this);
            self.find('#update-account-btn').attr('disabled', true).text('Updating..');

            $.ajax({
                url: '{{route("account.profile.update.bank")}}',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(data){
                    if(data.status == 'ok') {
                        document.location.reload();
                        return;
                    }
                    self.find('#update-account-btn').attr('disabled', false).text('Update Account');
                    alert(data.message);

                },
                error: function(jqXHR, status, thrown) {
                    self.find('#update-account-btn').attr('disabled', false).text('Update Account');
                    translateErrorMessages(jqXHR.responseJSON.errors);
                    //translateErrorMessages(obj.responseJSON.errors);
                }
            });
        });

        var minInvest = parseInt({{get_setting('investment_min_amount')}});
        var maxInvest = parseInt({{get_setting('investment_max_amount')}});
        var investPercent = $('#invest-percent');
        var investCapital = $('#invest-capital');
        var investmentType = $('#investment-type');
        var investNetprofit = $('#invest-netprofit');

        investmentType.change(function(){
            let amount = parseInt($('#amount').val().replace(/,/g, ''));
            if(amount >= minInvest && amount <= maxInvest) {
                let selectedInstType = getInvestmentType(investmentType.val());
                if(selectedInstType) {
                    investPercent.text(selectedInstType.percentage);
                    var total = calculateInvestInterest(amount, selectedInstType);
                    investCapital.text(formatMoney((total + amount).roundUp(2)));
                    investNetprofit.text(formatMoney(total.roundUp(2)));
                }
            }
        });


        $("#amount").bind("keyup change", function(e) {
             let amount = parseInt(this.value.replace(/,/g, ''));
             if(amount >= minInvest && amount <= maxInvest) {
                  let selectedInstType = getInvestmentType(investmentType.val());
                 if(selectedInstType) {
                     investPercent.text(selectedInstType.percentage);
                     var total = calculateInvestInterest(amount, selectedInstType);
                     investCapital.text(formatMoney((total + amount).roundUp(2)));
                     investNetprofit.text(formatMoney(total.roundUp(2)));
                 }
             }
        });

        // trigger card linking and investment
        $('#link-card-btn').click(function(e){
            var oldValue = $(this).text();
            var self = $(this);
            var action = $(this).data('action');
           // $(this).attr('disabled', true).text('Opening PayStack..');

            if(action != undefined && action.length > 0 && action == 'invest') {


                var investmentConfig = JSON.parse('{!! get_setting('investmentConfig') !!}');

                var amount = parseInt($('#amount').val().replace(/,/g, ''));

                if(amount < minInvest)
                {
                    self.attr('disabled', false).text(oldValue);
                    alert('You must invest at least ' + minInvest);
                    return;
                }

                if(amount > maxInvest)
                {
                    self.attr('disabled', false).text(oldValue);
                    alert('The maximum allowed investment is ' + maxInvest);
                    return;
                }

                var payload = {
                    amount: amount,
                    callback_url: '{{route('account.card.paystack.callback')}}',
                    action: 'invest',
                    investmentType: investmentType.val()
                };
            } else {
                var payload = {
                    callback_url: '{{route('account.card.paystack.callback')}}',
                    action: 'link'
                }
            }

            $.ajax({
               url: '/account/card/init',
               method: 'POST',
               data: payload,
               dataType: 'json',
               success: function(data) {
                 if(data.status == 'success') {
                     document.location = data.data.authorization_url;
                     return;
                 }

                   self.attr('disabled', false).text(oldValue);
                  alert('ERROR: ' + data.message);
               },
               error: function(obj, status, thrown){
                   self.attr('disabled', false).text(oldValue);
                  if(obj.responseJSON.status == 'error') {
                      alert(obj.responseJSON.message);
                      return;
                  }
                  alert('Error from server, please try again');
               }
            });
        });

    });
</script>
