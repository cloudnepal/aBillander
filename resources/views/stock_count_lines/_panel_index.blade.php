


<div name="search_filter" id="search_filter" @if( Request::has('search_status') AND (Request::input('search_status')==1) ) style="display:block" @else style="display:none" @endif>
<div class="row" style="padding: 0 20px">
    <div class="col-md-12 xcol-md-offset-3">
        <div class="panel panel-info">
            <div class="panel-heading"><h3 class="panel-title">{{ l('Filter Records', [], 'layouts') }}</h3></div>
            <div class="panel-body">

                {!! Form::model(Request::all(), array('route' => array('stockcounts.stockcountlines.index',$list->id), 'method' => 'GET')) !!}

<!-- input type="hidden" value="0" name="search_status" id="search_status" -->
{!! Form::hidden('search_status', null, array('id' => 'search_status')) !!}

<div class="row">
<div class="form-group col-lg-2 col-md-2 col-sm-2">
    {!! Form::label('reference', l('Reference')) !!}
    {!! Form::text('reference', null, array('class' => 'form-control')) !!}
</div>
<div class="form-group col-lg-2 col-md-2 col-sm-2">
    {!! Form::label('name', l('Product Name')) !!}
    {!! Form::text('name', null, array('class' => 'form-control')) !!}
</div>
{{--
<div class="form-group col-lg-2 col-md-2 col-sm-2">
    {!! Form::label('stock', l('Stock')) !!}
    {!! Form::select('stock', array('-1' => l('All', [], 'layouts'),
                                          '0'  => l('No' , [], 'layouts'),
                                          '1'  => l('Yes', [], 'layouts'),
                                          ), null, array('class' => 'form-control')) !!}
</div>
<div class="form-group col-lg-2 col-md-2 col-sm-2">
    {!! Form::label('category_id', l('Category')) !!}
    {!! Form::select('category_id', array('0' => l('All', [], 'layouts')) + $categoryList, null, array('class' => 'form-control')) !!}
</div>

<div class="form-group col-lg-2 col-md-2 col-sm-2">
    {!! Form::label('procurement_type', l('Procurement type'), ['class' => 'control-label']) !!}
    {!! Form::select('procurement_type', ['' => l('All', [], 'layouts')] + $product_procurementtypeList, null, array('class' => 'form-control')) !!}
</div>

<div class="form-group col-lg-2 col-md-2 col-sm-2" style="display: none">
    {!! Form::label('active', l('Active?', [], 'layouts'), ['class' => 'control-label']) !!}
    {!! Form::select('active', array('-1' => l('All', [], 'layouts'),
                                          '0'  => l('No' , [], 'layouts'),
                                          '1'  => l('Yes', [], 'layouts'),
                                          ), null, array('class' => 'form-control')) !!}
</div>
--}}
<div class="form-group col-lg-4 col-md-4 col-sm-4" style="padding-top: 22px">
{!! Form::submit(l('Filter', [], 'layouts'), array('class' => 'btn btn-success')) !!}
{!! link_to_route('stockcounts.stockcountlines.index', l('Reset', [], 'layouts'), [$list->id], array('class' => 'btn btn-warning')) !!}
</div>

</div>

                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
</div>


<div id="div_stockcounts">
   <div class="table-responsive">

@if ($lines->count())
<table id="stockcounts" class="table table-hover">
	<thead>
		<tr>
			<th class="text-left">{{l('ID', [], 'layouts')}}</th>
            <th>{{l('Reference')}}</th>
            <th>{{l('Product Name')}}</th>
            <th>{{l('Quantity')}}</th>
            <th>{{l('Cost Price')}}</th>
            <th>{{l('Average Cost Price')}}</th>
            <th>{{l('Last Purchase Price')}}</th>
			<th> </th>
		</tr>
	</thead>
	<tbody>
	@foreach ($lines as $line)
		<tr>
			<td>{{ $line->id }}</td>
            <td><a href="{{ URL::to('products/' . optional($line->product)->id . '/edit') }}" title="{{l('Edit', [], 'layouts')}}" target="_new">{{ optional($line->product)->reference }}</a></td>
            <td>{{ $line->name }}</td>
            <td>{{ $line->as_quantity('quantity') }}</td>
            <td>
@if ( $line->cost_price == NULL )
            -
@else
            {{ $line->as_price('cost_price') }}
@endif
            </td>
            
            <td>{{ $line->as_price('cost_average') }}</td>
            <td>{{ $line->as_price('last_purchase_price') }}</td>

			<td class="text-right button-pad">
                @if ( !$line->stockcount->processed )
                <a class="btn btn-sm btn-warning" href="{{ URL::to('stockcounts/' . $list->id.'/stockcountlines/' . $line->id . '/edit') }}" title="{{l('Edit', [], 'layouts')}}"><i class="fa fa-pencil"></i></a>
                <a class="btn btn-sm btn-danger delete-item" data-html="false" data-toggle="modal" 
                		href="{{ URL::to('stockcounts/' . $list->id.'/stockcountlines/' . $line->id ) }}" 
                		data-content="{{l('You are going to delete a record. Are you sure?', [], 'layouts')}}" 
                		data-title="{{ l('Stock Count Lines') }} :: ({{$line->id}}) {{ $line->name }} " 
                		onClick="return false;" title="{{l('Delete', [], 'layouts')}}"><i class="fa fa-trash-o"></i></a>
                @endif
			</td>
		</tr>
	@endforeach
	</tbody>
</table>
{!! $lines->appends( Request::all() )->render() !!} 
<ul class="pagination"><li class="active"><span style="color:#333333;">{{l('Found :nbr record(s)', [ 'nbr' => $lines->total() ], 'layouts')}} </span></li></ul>
@else
<div class="alert alert-warning alert-block">
    <i class="fa fa-warning"></i>
    {{l('No records found', [], 'layouts')}}
</div>
@endif

   </div>
</div>

@section('scripts') @parent 

<script type="text/javascript">

$(document).ready(function() {
   $("#b_search_filter").click(function() {
      $('#search_status').val(1);
      $('#search_filter').show();
   });
});

</script>

@endsection


@section('styles') @parent

{{-- Panels with nav tabs :: https://bootsnipp.com/snippets/featured/panels-with-nav-tabs --}}
{{-- See also: https://bootsnipp.com/snippets/featured/panel-with-tabs --}}

<style>

.label-grey {
    color: #333333;
    background-color: #e7e7e7;
    border-color: #cccccc;

    padding: 1px 5px;
    font-size: 12px;
    line-height: 1.5;
    border-radius: 3px;

    display: inline-block;
    margin-bottom: 0;
    font-weight: normal;
    text-align: center;
    vertical-align: middle;
    -ms-touch-action: manipulation;
    touch-action: manipulation;
    cursor: pointer;
    background-image: none;
    border: 1px solid transparent;
    white-space: nowrap;
    padding: 8px 12px;
    font-size: 14px;
    line-height: 1.42857143;
    border-radius: 4px;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;

}

</style>

@endsection

