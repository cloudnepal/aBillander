

  {!! Form::model($bankaccount, array('route' => array('customers.bankaccount', $customer->id), 'method' => 'POST', 'class' => 'form')) !!}
  <input type="hidden" value="{{$customer->id}}" name="bank_customer_id" id="bank_customer_id">

            <div class="panel panel-primary" id="panel_bankaccounts">
               <div class="panel-heading">
                  <h3 class="panel-title">{{ l('Bank Accounts') }}</h3>
               </div>
               <div class="panel-body">

<!-- Datos generales -->

        <div class="row">
            <div class="form-group col-lg-6 col-md-6 col-sm-6 {!! $errors->has('bank_name') ? 'has-error' : '' !!}">
              {{ l('Bank Name') }}
              {!! Form::text('bank_name', null, array('class' => 'form-control', 'id' => 'bank_name')) !!}
              {!! $errors->first('bank_name', '<span class="help-block">:message</span>') !!}
            </div>
        </div>

        <div class="row">
            <div class="form-group col-lg-3 col-md-3 col-sm-3">
            <div class="well well-sm" xstyle="background-color: #d9edf7; border-color: #bce8f1; color: #3a87ad;">
               <b>{{ l('Bank Account Code') }}</b>
            </div>
            </div>
        </div>

        <div class="row">
                  <div class="form-group col-lg-2 col-md-2 col-sm-2 {!! $errors->has('ccc_entidad') ? 'has-error' : '' !!}">
                    {{ l('Bank code') }}
                    {!! Form::text('ccc_entidad', null, array('class' => 'form-control', 'id' => 'ccc_entidad')) !!}
                    {!! $errors->first('ccc_entidad', '<span class="help-block">:message</span>') !!}
                  </div>
                  <div class="form-group col-lg-2 col-md-2 col-sm-2 {!! $errors->has('ccc_oficina') ? 'has-error' : '' !!}">
                    {{ l('Bank Branch code') }}
                    {!! Form::text('ccc_oficina', null, array('class' => 'form-control', 'id' => 'ccc_oficina')) !!}
                    {!! $errors->first('ccc_oficina', '<span class="help-block">:message</span>') !!}
                  </div>
                  <div class="form-group col-lg-1 col-md-1 col-sm-1 {!! $errors->has('ccc_control') ? 'has-error' : '' !!}">
                    {{ l('Control') }}
                    {!! Form::text('ccc_control', null, array('class' => 'form-control', 'id' => 'ccc_control')) !!}
                    {!! $errors->first('ccc_control', '<span class="help-block">:message</span>') !!}
                  </div>
                  <div class="form-group col-lg-2 col-md-2 col-sm-2 {!! $errors->has('ccc_cuenta') ? 'has-error' : '' !!}">
                    {{ l('Account') }}
                    {!! Form::text('ccc_cuenta', null, array('class' => 'form-control', 'id' => 'ccc_cuenta')) !!}
                    {!! $errors->first('ccc_cuenta', '<span class="help-block">:message</span>') !!}
                  </div>
                  <div class="form-group col-lg-2 col-md-2 col-sm-2">
                      <br />
                      <a class="btn xbtn-sm btn-warning calculate_iban"><i class="fa fa-cogs"></i> {{ l('Calculate Iban') }}</a>
                  </div>
        </div>

        <div class="row">
                  <div class="form-group col-lg-4 col-md-4 col-sm-4 {!! $errors->has('iban') ? 'has-error' : '' !!}">
                    {{ l('Iban') }}
                       <a href="javascript:void(0);" data-toggle="popover" data-placement="top" 
                                          data-content="{{ l('To make it more readable, you can enter spaces.') }}">
                              <i class="fa fa-question-circle abi-help"></i>
                       </a>
                    {!! Form::text('iban', null, array('class' => 'form-control', 'id' => 'iban')) !!}
                    {!! $errors->first('iban', '<span class="help-block">:message</span>') !!}
                  </div>
                  <div class="form-group col-lg-2 col-md-2 col-sm-2 {!! $errors->has('swift') ? 'has-error' : '' !!}">
                    {{ l('Swift') }}
                    {!! Form::text('swift', null, array('class' => 'form-control', 'id' => 'swift')) !!}
                    {!! $errors->first('swift', '<span class="help-block">:message</span>') !!}
                  </div>
        </div>

        <div class="row">
            <div class="form-group col-lg-5 col-md-5 col-sm-5">
            <div class="well well-sm" xstyle="background-color: #d9edf7; border-color: #bce8f1; color: #3a87ad;">
               <b>{{ l('Mandate (for automatic payment remittances)') }}</b>
            </div>
            </div>
        </div>

        <div class="row">
                  <div class="form-group col-lg-2 col-md-2 col-sm-2 {!! $errors->has('mandate_reference') ? 'has-error' : '' !!}">
                    {{ l('Mandate Reference') }}
                       <a href="javascript:void(0);" data-toggle="popover" data-placement="top" 
                                          data-content="{{ l('You can use Customer Identification (only letters and digits) plus space plus Mandate Date.') }}">
                              <i class="fa fa-question-circle abi-help"></i>
                       </a>
                    {!! Form::text('mandate_reference', null, array('class' => 'form-control', 'id' => 'mandate_reference')) !!}
                    {!! $errors->first('mandate_reference', '<span class="help-block">:message</span>') !!}
                  </div>
                  <div class="form-group col-lg-2 col-md-2 col-sm-2 {!! $errors->has('mandate_date') ? 'has-error' : '' !!}">
                    {{ l('Mandate Date') }}
                    {!! Form::text('mandate_date_form', null, array('class' => 'form-control', 'id' => 'mandate_date_form')) !!}
                    {!! $errors->first('mandate_date', '<span class="help-block">:message</span>') !!}
                  </div>
        </div>

<!-- Datos generales ENDS -->

               </div>
               <div class="panel-footer text-right">
                  <button class="btn btn-sm btn-info" type="submit" onclick="this.disabled=true;this.form.submit();">
                     <i class="fa fa-hdd-o"></i>
                     &nbsp; {{ l('Save', [], 'layouts') }}
                  </button>
               </div>
            </div>


     {!! Form::close() !!}


@section('styles')    @parent

{{-- Auto Complete --}}

  {{-- !! HTML::style('assets/plugins/AutoComplete/styles.css') !! --}}

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css"></script -->

<style>

  .ui-autocomplete-loading{
    background: white url("{{ asset('assets/theme/images/ui-anim_basic_16x16.gif') }}") right center no-repeat;
  }
  .loading{
    background: white url("{{ asset('assets/theme/images/ui-anim_basic_16x16.gif') }}") left center no-repeat;
  }


/* See: http://fellowtuts.com/twitter-bootstrap/bootstrap-popover-and-tooltip-not-working-with-ajax-content/ 
.modal .popover, .modal .tooltip {
    z-index:100000000;
}
 */
  .ui-datepicker{ z-index: 9999 !important;}


/* Undeliver dropdown effect */
   .hover-item:hover {
      background-color: #d3d3d3 !important;
    }
</style>

@endsection


@section('scripts')    @parent


{{-- Auto Complete --}}
{{-- Date Picker :: http://api.jqueryui.com/datepicker/ --}}

<script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
{!! HTML::script('assets/plugins/jQuery-UI/datepicker/datepicker-'.AbiContext::getContext()->language->iso_code.'.js'); !!}

<script type="text/javascript">

    $(document).ready(function() {


    $( "#mandate_date_form" ).datepicker({
      showOtherMonths: true,
      selectOtherMonths: true,
      dateFormat: "{{ AbiContext::getContext()->language->date_format_lite_view }}"
    });


    $("body").on('click', ".calculate_iban", function() {

            var url = "{{ route('bankaccounts.iban.calculate') }}";
            var token = "{{ csrf_token() }}";

            var payload = { 
                              ccc_entidad : $('#ccc_entidad').val(),
                              ccc_oficina : $('#ccc_oficina').val(),
                              ccc_control : $('#ccc_control').val(),
                              ccc_cuenta  : $('#ccc_cuenta').val(),
                          };

            $('#iban').parent().removeClass('has-success');

            $.ajax({
                url : url,
                headers : {'X-CSRF-TOKEN' : token},
                type : 'POST',
                dataType : 'json',
                data : payload,

                success: function(result){

                    // Poner borde de campo en naranja
                    // showAlertDivWithDelay("#msg-success");

                    console.log(result);

                    $('#iban').val(result.data.iban);

                    $('#iban').parent().addClass('has-success');
                }
            });

        });


    });

</script> 

@endsection
