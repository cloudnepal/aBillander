@extends('layouts.master')

@section('title') {{ l('Measure Units - Create') }} @parent @stop


@section('content')

<div class="row">
	<div class="col-md-6 col-md-offset-3" style="margin-top: 50px">
		<div class="panel panel-info">
			<div class="panel-heading"><h3 class="panel-title">{{ l('New Measure Unit') }}</h3></div>
			<div class="panel-body">

				@include('errors.list')

				{!! Form::open(array('route' => 'measureunits.store')) !!}

					@include('measure_units._form')

				{!! Form::close() !!}
			</div>
		</div>
	</div>
</div>

@stop