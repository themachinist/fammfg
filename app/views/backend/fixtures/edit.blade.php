@extends('backend/layouts/default')

{{-- Page title --}}
@section('title')
    @if ($fixture->id)
        @lang('admin/fixtures/form.update') ::
    @else
        @lang('admin/fixtures/form.create') ::
    @endif
@parent
@stop

{{-- Page content --}}
@section('content')

<div class="row header">
    <div class="col-md-12">
            <a href="{{ URL::previous() }}" class="btn-flat gray pull-right right">
            <i class="fa fa-arrow-left icon-white"></i> @lang('general.back')</a>
        <h3>
        @if ($fixture->id)
            @lang('admin/fixtures/form.update')
        @else
            @lang('admin/fixtures/form.create')
        @endif
        </h3>
    </div>
</div>


<!-- Tabs -->
<ul class="nav nav-tabs">
    <li class="active"><a href="#tab-general" data-toggle="tab">Purchased</a></li>
    <li><a href="#tab-built" data-toggle="tab">Built In-house</a></li>
</ul>

<form class="form-horizontal" method="post" action="" autocomplete="off">
    <!-- CSRF Token -->
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

		<div class="row form-wrapper"> <br><br>

			<!-- Fixture -->
			<div class="form-group {{ $errors->has('name') ? ' has-error' : '' }}">
				<label for="name" class="col-md-3 control-label">@lang('admin/fixtures/form.name')
				 <i class='fa fa-asterisk'></i></label>
				 </label>
					<div class="col-md-7">
						<input class="form-control" type="text" name="name" id="name" value="{{ Input::old('name', $fixture->name) }}" />
						{{ $errors->first('name', '<br><span class="alert-msg"><i class="fa fa-times"></i> :message</span>') }}
					</div>
			</div>

			<!-- Serial -->
			<div class="form-group {{ $errors->has('serial') ? ' has-error' : '' }}">
				<label for="serial" class="col-md-3 control-label">@lang('admin/fixtures/form.serial')
					 <i class='fa fa-asterisk'></i>
				 </label>
				<div class="col-md-7">
					<input class="form-control" type="text" name="serial" id="serial">{{ Input::old('serial', $fixture->serial) }}</input>
					{{ $errors->first('serial', '<br><span class="alert-msg"><i class="fa fa-times"></i> :message</span>') }}
				</div>
			</div>

			<!-- Seats -->
			<div class="form-group {{ $errors->has('copies') ? ' has-error' : '' }}">
				<label for="copies" class="col-md-3 control-label">@lang('admin/fixtures/form.copies')
				 <i class='fa fa-asterisk'></i></label>
				 </label>
					<div class="col-md-3">
						<input class="form-control" type="text" name="copies" id="copies" value="{{ Input::old('copies', $fixture->copies) }}" />
						{{ $errors->first('copies', '<br><span class="alert-msg"><i class="fa fa-times"></i> :message</span>') }}
					</div>
			</div>

			<!-- Needs maintenance? -->
			<div class="form-group {{ $errors->has('need_maintenance') ? ' has-error' : '' }}">
				<label for="needs_maintenance" class="col-md-3 control-label">@lang('admin/fixtures/form.needs_maintenance')</label>


				<div class="checkbox col-md-7 input-group" style="padding-left: 35px;">
					{{ Form::Checkbox('needs_maintenance', '1', Input::old('needs_maintenance', $fixture->needs_maintenance)) }}
					@lang('general.yes')

				</div>
			</div>

			<!-- Maintenance Interval -->
			<div class="form-group {{ $errors->has('maintenance_interval') ? ' has-error' : '' }}">
				<label for="maintenance_interval" class="col-md-3 control-label">@lang('admin/fixtures/form.maintenance_interval')</label>
				<div class="input-group col-md-3" style="padding-top: 4px;">
					<span>every&nbsp;&nbsp;</span>
					{{ Form::select('maintenance_interval', array(1,3,6,9,12), Input::old('maintenance_interval', $fixture->maintenance_interval), array('class'=>'select2', 'style'=>'min-width:50px,display:inline-block')) }}
					<span>&nbsp;&nbsp;months</span>
					{{ $errors->first('maintenance_interval', '<br><span class="alert-msg"><i class="fa fa-times"></i> :message</span>') }}
				</div>
			</div>

		</div>


    <!-- Tabs Content -->
    <div class="tab-content">
        <!-- General tab -->
        <div class="tab-pane active" id="tab-general">
			
			<div class="row form-wrapper"> <br><br>
				<!-- Supplier -->
				<div class="form-group {{ $errors->has('supplier_id') ? ' has-error' : '' }}">
					<label for="supplier_id" class="col-md-3 control-label">@lang('admin/fixtures/form.supplier')</label>
					<div class="col-md-7">
						{{ Form::select('supplier_id', $supplier_list , Input::old('supplier_id', $fixture->supplier_id), array('class'=>'select2', 'style'=>'min-width:350px')) }}
						{{ $errors->first('supplier_id', '<br><span class="alert-msg"><i class="fa fa-times"></i> :message</span>') }}
					</div>
				</div>

				<!-- Order Number -->
				<div class="form-group {{ $errors->has('order_number') ? ' has-error' : '' }}">
					<label for="order_number" class="col-md-3 control-label">@lang('admin/fixtures/form.order')</label>
						<div class="col-md-7">
							<input class="form-control" type="text" name="order_number" id="order_number" value="{{ Input::old('order_number', $fixture->order_number) }}" />
							{{ $errors->first('order_number', '<br><span class="alert-msg"><i class="fa fa-times"></i> :message</span>') }}
						</div>
				</div>

				<!-- Purchase Date -->
				<div class="form-group {{ $errors->has('purchase_date') ? ' has-error' : '' }}">
					<label for="purchase_date" class="col-md-3 control-label">@lang('admin/fixtures/form.date')</label>
					<div class="input-group col-md-2">
						<input type="date" class="datepicker form-control" data-date-format="yyyy-mm-dd" placeholder="Select Date" name="purchase_date" id="purchase_date" value="{{ Input::old('purchase_date', $fixture->purchase_date) }}">
						<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
					{{ $errors->first('purchase_date', '<br><span class="alert-msg"><i class="fa fa-times"></i> :message</span>') }}
					</div>
				</div>

				<!-- Purchase Cost -->
				<div class="form-group {{ $errors->has('purchase_cost') ? ' has-error' : '' }}">
					<label for="purchase_cost" class="col-md-3 control-label">@lang('admin/fixtures/form.cost')</label>
					<div class="col-md-2">
						<div class="input-group">
							<span class="input-group-addon">@lang('general.currency')</span>
							<input class="col-md-2 form-control" type="text" name="purchase_cost" id="purchase_cost" value="{{ Input::old('purchase_cost', number_format($fixture->purchase_cost,2)) }}" />
							{{ $errors->first('purchase_cost', '<br><span class="alert-msg"><i class="fa fa-times"></i> :message</span>') }}
						 </div>
					 </div>
				</div>

				<!-- Purchase Order -->
				<div class="form-group {{ $errors->has('purchase_order') ? ' has-error' : '' }}">
					<label for="purchase_order" class="col-md-3 control-label">@lang('admin/fixtures/form.purchase_order')</label>
						<div class="col-md-7">
							<input class="form-control" type="text" name="purchase_order" id="purchase_order" value="{{ Input::old('purchase_order', $fixture->purchase_order) }}" />
							{{ $errors->first('purchase_order', '<br><span class="alert-msg"><i class="fa fa-times"></i> :message</span>') }}
						</div>
				</div>

				<!-- Expiration Date -->
				<div class="form-group {{ $errors->has('expiration_date') ? ' has-error' : '' }}">
					<label for="expiration_date" class="col-md-3 control-label">@lang('admin/fixtures/form.expiration')</label>
					<div class="input-group col-md-2">
						<input type="date" class="datepicker form-control" data-date-format="yyyy-mm-dd" placeholder="Select Date" name="expiration_date" id="expiration_date" value="{{ Input::old('expiration_date', $fixture->expiration_date) }}">
						<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
					{{ $errors->first('expiration_date', '<br><span class="alert-msg"><i class="fa fa-times"></i> :message</span>') }}
					</div>
				</div>

				<!-- Depreciation -->
				<div class="form-group {{ $errors->has('depreciation_id') ? ' has-error' : '' }}">
					<label for="parent" class="col-md-3 control-label">@lang('admin/fixtures/form.depreciation')</label>
						<div class="col-md-7">
							{{ Form::select('depreciation_id', $depreciation_list , Input::old('depreciation_id', $fixture->depreciation_id), array('class'=>'select2', 'style'=>'width:350px')) }}
							{{ $errors->first('depreciation_id', '<br><span class="alert-msg"><i class="fa fa-times"></i> :message</span>') }}
						</div>
				</div>

				<!-- Notes -->
				<div class="form-group {{ $errors->has('notes') ? ' has-error' : '' }}">
					<label for="notes" class="col-md-3 control-label">@lang('admin/fixtures/form.notes')</label>
					<div class="col-md-7">
						<textarea class="col-md-6 form-control" id="notes" name="notes">{{{ Input::old('notes', $fixture->notes) }}}</textarea>
						{{ $errors->first('notes', '<br><span class="alert-msg"><i class="fa fa-times"></i> :message</span>') }}
					</div>
				</div>
			</div>
		</div>

        <!-- Built in-house tab -->
        <div class="tab-pane" id="tab-built">
			
			<div class="row form-wrapper"> <br><br>

				<!-- Designer name -->
				<div class="form-group {{ $errors->has('designer_name') ? ' has-error' : '' }}">
					<label for="designer_name" class="col-md-3 control-label">@lang('admin/designers/form.to_name')</label>
						<div class="col-md-7">
							<input class="form-control" type="text" name="designer_name" id="designer_name" value="{{ Input::old('designer_name', $fixture->designer_name) }}" />
							{{ $errors->first('designer_name', '<br><span class="alert-msg"><i class="fa fa-times"></i> :message</span>') }}
						</div>
				</div>

				<!-- Designer email -->
				<div class="form-group {{ $errors->has('designer_email') ? ' has-error' : '' }}">
					<label for="designer_email" class="col-md-3 control-label">@lang('admin/designers/form.to_email')</label>
						<div class="col-md-7">
							<input class="form-control" type="text" name="designer_email" id="designer_email" value="{{ Input::old('designer_email', $fixture->designer_email) }}" />
							{{ $errors->first('designer_email', '<br><span class="alert-msg"><i class="fa fa-times"></i> :message</span>') }}
						</div>
				</div>

				<!-- Build Date -->
				<div class="form-group {{ $errors->has('build_date') ? ' has-error' : '' }}">
					<label for="build_date" class="col-md-3 control-label">@lang('admin/fixtures/form.build_date')</label>
					<div class="input-group col-md-2">
						<input type="date" class="datepicker form-control" data-date-format="yyyy-mm-dd" placeholder="Select Date" name="build_date" id="build_date" value="{{ Input::old('build_date', $fixture->build_date) }}">
						<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
					{{ $errors->first('build_date', '<br><span class="alert-msg"><i class="fa fa-times"></i> :message</span>') }}
					</div>
				</div>

				<!-- Build Cost -->
				<div class="form-group {{ $errors->has('build_cost') ? ' has-error' : '' }}">
					<label for="build_cost" class="col-md-3 control-label">@lang('admin/fixtures/form.build_cost')</label>
					<div class="col-md-2">
						<div class="input-group">
							<span class="input-group-addon">@lang('general.currency')</span>
							<input class="col-md-2 form-control" type="text" name="build_cost" id="build_cost" value="{{ Input::old('build_cost', number_format($fixture->build_cost,2)) }}" />
							{{ $errors->first('build_cost', '<br><span class="alert-msg"><i class="fa fa-times"></i> :message</span>') }}
						 </div>
					 </div>
				</div>

				<!-- Job Number -->
				<div class="form-group {{ $errors->has('job_number') ? ' has-error' : '' }}">
					<label for="job_number" class="col-md-3 control-label">@lang('admin/fixtures/form.job_number')</label>
						<div class="col-md-7">
							<input class="form-control" type="text" name="job_number" id="job_number" value="{{ Input::old('job_number', $fixture->job_number_built_on) }}" />
							{{ $errors->first('job_number', '<br><span class="alert-msg"><i class="fa fa-times"></i> :message</span>') }}
						</div>
				</div>
				
			</div>
		</div>
	</div>

	<!-- Form actions -->
	<div class="form-group">
	<label class="col-md-3 control-label"></label>
		<div class="col-md-7">

			<a class="btn btn-link" href="{{ URL::previous() }}">@lang('button.cancel')</a>
			<button type="submit" class="btn btn-success"><i class="fa fa-check icon-white"></i> @lang('general.save')</button>
		</div>
	</div>

</form>
</div>

@stop
