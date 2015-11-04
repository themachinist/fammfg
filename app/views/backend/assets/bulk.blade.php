@extends('backend/layouts/default')

{{-- Page title --}}
@section('title')
        	@lang('admin/hardware/form.update') ::

@parent
@stop

{{-- Page content --}}

@section('content')

<div class="row header">
    <div class="col-md-12">
            <a href="{{ URL::previous() }}" class="btn-flat gray pull-right right"><i class="fa fa-arrow-left icon-white"></i> @lang('general.back')</a>
        <h3>

        	@lang('admin/hardware/form.bulk_update')
        </h3>
    </div>
</div>

<div class="row form-wrapper">
            <!-- left column -->
            <div class="col-md-12 column">
	                <p>@lang('admin/hardware/form.bulk_update_help')</p>
	                <p style="color: red"><strong><big>@lang('admin/hardware/form.bulk_update_warn', ['asset_count' => count($assets)])</big></strong></p>


			 <form class="form-horizontal" method="post" action="{{ route('hardware/bulksave') }}" autocomplete="off" role="form">


            <!-- CSRF Token -->
            <input type="hidden" name="_token" value="{{ csrf_token() }}" />


            <!-- Purchase Date -->
            <div class="form-group {{ $errors->has('purchase_date') ? ' has-error' : '' }}">
                <label for="purchase_date" class="col-md-2 control-label">@lang('admin/hardware/form.date')</label>
                <div class="input-group col-md-3">
                    <input type="date" class="datepicker form-control" data-date-format="yyyy-mm-dd" placeholder="Select Date" name="purchase_date" id="purchase_date" value="{{{ Input::old('purchase_date') }}}">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                {{ $errors->first('purchase_date', '<br><span class="alert-msg"><i class="fa fa-times"></i> :message</span>') }}
                </div>
            </div>

            <!-- Status -->
            <div class="form-group {{ $errors->has('status_id') ? ' has-error' : '' }}">
                <label for="status_id" class="col-md-2 control-label">
                  @lang('admin/hardware/form.status')
                </label>
                    <div class="col-md-7">
                        {{ Form::select('status_id', $statuslabel_list , Input::old('status_id'), array('class'=>'select2', 'style'=>'width:350px')) }}
                        {{ $errors->first('status_id', '<br><span class="alert-msg"><i class="fa fa-times"></i> :message</span>') }}
                    </div>
            </div>

            <!-- Default Location -->
            <div class="form-group {{ $errors->has('status_id') ? ' has-error' : '' }}">
                <label for="status_id" class="col-md-2 control-label">@lang('admin/hardware/form.default_location')</label>
                    <div class="col-md-7">
                        {{ Form::select('rtd_location_id', $location_list , Input::old('rtd_location_id'), array('class'=>'select2', 'style'=>'width:350px')) }}
                        {{ $errors->first('status_id', '<br><span class="alert-msg"><i class="fa fa-times"></i> :message</span>') }}
                    </div>
            </div>

            <!-- Purchase Cost -->
            <div class="form-group {{ $errors->has('purchase_cost') ? ' has-error' : '' }}">
                <label for="purchase_cost" class="col-md-2 control-label">@lang('admin/hardware/form.cost')</label>
                <div class="input-group col-md-3">
	                <span class="input-group-addon">@lang('general.currency')</span>
                    <input type="text" class="form-control" placeholder="@lang('admin/hardware/form.cost')" name="purchase_cost" id="purchase_cost" value="{{{ Input::old('purchase_cost') }}}">

                {{ $errors->first('purchase_cost', '<br><span class="alert-msg"><i class="fa fa-times"></i> :message</span>') }}
                </div>
            </div>

            <!-- Supplier -->
            <div class="form-group {{ $errors->has('supplier_id') ? ' has-error' : '' }}">
                <label for="supplier_id" class="col-md-2 control-label">@lang('admin/hardware/form.supplier')</label>
                <div class="col-md-7">
                    {{ Form::select('supplier_id', $supplier_list , Input::old('supplier_id'), array('class'=>'select2', 'style'=>'min-width:350px')) }}
                    {{ $errors->first('supplier_id', '<br><span class="alert-msg"><i class="fa fa-times"></i> :message</span>') }}
                </div>
            </div>

            <!-- Order Number -->
            <div class="form-group {{ $errors->has('order_number') ? ' has-error' : '' }}">
                <label for="order_number" class="col-md-2 control-label">@lang('admin/hardware/form.order')</label>
                <div class="col-md-7">
                    <input class="form-control" type="text" name="order_number" id="order_number" value="{{{ Input::old('order_number') }}}" />
                    {{ $errors->first('order_number', '<br><span class="alert-msg"><i class="fa fa-times"></i> :message</span>') }}
                </div>
            </div>

            <!-- Warranty -->
            <div class="form-group {{ $errors->has('warranty_months') ? ' has-error' : '' }}">
                <label for="warranty_months" class="col-md-2 control-label">@lang('admin/hardware/form.warranty')</label>
                <div class="col-md-2">
                    <div class="input-group">
                    <input class="col-md-2 form-control" type="text" name="warranty_months" id="warranty_months" value="{{{ Input::old('warranty_months') }}}" />   <span class="input-group-addon">@lang('admin/hardware/form.months')</span>
                    {{ $errors->first('warranty_months', '<br><span class="alert-msg"><i class="fa fa-times"></i> :message</span>') }}
                    </div>
                </div>
            </div>

            <!-- Requestable -->
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
				  <div class="checkbox">
					<label>
					  <input type="checkbox" value="1" name="requestable" id="requestable"> @lang('admin/hardware/form.requestable')
					</label>
				  </div>
				</div>
		  	</div>


            @foreach ($assets as $key => $value)
            	<input type="hidden" name="bulk_edit[{{{ $key }}}]" value="1">
            @endforeach


            <!-- Form actions -->
                <div class="form-group">
                <label class="col-md-2 control-label"></label>
                    <div class="col-md-7">
                        <a class="btn btn-link" href="{{ URL::previous() }}">@lang('button.cancel')</a>
                        <button type="submit" class="btn btn-success"><i class="fa fa-check icon-white"></i> @lang('general.save')</button>
                    </div>
                </div>

        </form>
    </div>
</div>
@stop
