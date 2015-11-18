@extends('backend/layouts/default')

{{-- Page title --}}
@section('title')
@lang('admin/assets/general.view') {{ $asset->asset_tag }} ::
@parent
@stop

{{-- Page content --}}
@section('content')

<div class="row header">
	<div class="col-md-12">
	 <h3 class="name">
		@lang('admin/assets/general.view')
		{{{ $asset->asset_tag }}}
		@if ($asset->name)
		({{{ $asset->name }}})
		@endif
	</h3>

		<div class="btn-group pull-right">

		<div class="dropdown">
			<button class="btn btn-default dropdown-toggle" data-toggle="dropdown">@lang('button.actions')
				<span class="caret"></span>
			</button>
			<ul class="dropdown-menu pull-right" role="menu" aria-labelledby="dropdownMenu1">

					@if ($asset->assigned_to != '')
							<li role="presentation"><a href="{{ route('checkin/assets', $asset->id) }}">@lang('admin/assets/general.checkin')</a></li>

						@else
							<li role="presentation"><a href="{{ route('checkout/assets', $asset->id)  }}">@lang('admin/assets/general.checkout')</a></li>
						@endif
				<li role="presentation"><a href="{{ route('update/assets', $asset->id) }}">@lang('admin/assets/general.edit')</a></li>
				<li role="presentation"><a href="{{ route('clone/assets', $asset->id) }}">@lang('admin/assets/general.clone')</a></li>
			</ul>
		</div>


	</div>
</div>
</div>

<div class="user-profile">
<div class="row profile">
<div class="col-md-9 bio">

		@if ($asset->model->deleted_at!='')
			<div class="alert alert-warning alert-block">
				<i class="fa fa-warning"></i>
				@lang('admin/assets/general.model_deleted', ['model_id' => $asset->model->id])
			</div>
		@elseif ($asset->deleted_at!='')
			<div class="alert alert-warning alert-block">
				<i class="fa fa-warning"></i>
				@lang('admin/assets/general.deleted', ['asset_id' => $asset->id])
			</div>
		@endif

		@if ($asset->serial)
			<div class="col-md-12" style="padding-bottom: 5px;"><strong>@lang('admin/assets/form.serial'): </strong>
			<em>{{{ $asset->serial }}}</em></div>

		@endif

		@if ($asset->mac_address!='')
			<div class="col-md-12" style="padding-bottom: 5px;"><strong>@lang('admin/assets/form.mac_address'):</strong>
			{{{ $asset->mac_address }}}
			</div>
		@endif

		@if ($asset->model->manufacturer)
			<div class="col-md-12" style="padding-bottom: 5px;"><strong>@lang('admin/assets/form.manufacturer'): </strong>
			<a href="{{ route('update/manufacturer', $asset->model->manufacturer->id) }}">
			{{{ $asset->model->manufacturer->name }}}
			</a> </div>
			<div class="col-md-12" style="padding-bottom: 5px;"><strong>@lang('admin/assets/form.model'):</strong>
			<a href="{{ route('view/model', $asset->model->id) }}">
			{{{ $asset->model->name }}}
			</a>
			 / {{{ $asset->model->modelno }}}</div>
		@endif

		@if ($asset->purchase_date)
			<div class="col-md-12" style="padding-bottom: 5px;"><strong>@lang('admin/assets/form.date'): </strong>
			{{{ $asset->purchase_date }}} </div>
		@endif

		@if ($asset->purchase_cost)
			<div class="col-md-12" style="padding-bottom: 5px;"><strong>@lang('admin/assets/form.cost'):</strong>
			@if (($asset->id) && ($asset->assetloc))
				{{{ $asset->assetloc->currency }}}
			@else
				{{{ Setting::first()->default_currency }}}
			@endif

			{{{ number_format($asset->purchase_cost,2) }}} </div>
		@endif

		@if ($asset->order_number)
			<div class="col-md-12" style="padding-bottom: 5px;"><strong>@lang('admin/assets/form.order'):</strong>
			{{{ $asset->order_number }}} </div>
		@endif

		@if ($asset->supplier_id)
			<div class="col-md-6" style="padding-bottom: 5px;"><strong>@lang('admin/assets/form.supplier'): </strong>
			<a href="{{ route('view/supplier', $asset->supplier_id) }}">
			{{{ $asset->supplier->name }}}
			</a> </div>
		@endif

		@if ($asset->warranty_months)
			<div class="col-md-12" style="padding-bottom: 5px;"><strong>@lang('admin/assets/form.warranty'):</strong>
			{{{ $asset->warranty_months }}}
			@lang('admin/assets/form.months')
			</div>
			<div class="col-md-12 {{{ $asset->warrantee_expires() < date("Y-m-d H:i:s") ? 'ui-state-highlight' : '' }}}"  style="padding-bottom: 5px;"><strong>@lang('admin/assets/form.expires'):</strong>
			{{{ $asset->warrantee_expires() }}}</div>
		@endif

		@if ($asset->depreciation)
			<div class="col-md-12" style="padding-bottom: 5px;"><strong>@lang('admin/assets/form.depreciation'): </strong>
			{{ $asset->depreciation->name }}
				({{{ $asset->depreciation->months }}}
				@lang('admin/assets/form.months')
				)</div>
			<div class="col-md-12" style="padding-bottom: 5px;"><strong>@lang('admin/assets/form.fully_depreciated'): </strong>
			 @if ($asset->time_until_depreciated()->y > 0)
				{{{ $asset->time_until_depreciated()->y }}}
				@lang('admin/assets/form.years'),
			 @endif
		   {{{ $asset->time_until_depreciated()->m }}}
			@lang('admin/assets/form.months')
			   ({{{ $asset->depreciated_date()->format('Y-m-d') }}})
			 </div>
		@endif


		@if ($asset->model->eol)
			<div class="col-md-12" style="padding-bottom: 5px;">
			<strong>@lang('admin/assets/form.eol_rate'): </strong>
			{{{ $asset->model->eol }}}
			@lang('admin/assets/form.months') </div>
			<div class="col-md-12" style="padding-bottom: 5px;">
			<strong>@lang('admin/assets/form.eol_date'): </strong>
			{{{ $asset->eol_date() }}}
			@if ($asset->months_until_eol())
				 (
				 @if ($asset->months_until_eol()->y > 0) {{{ $asset->months_until_eol()->y }}}
				  @lang('general.years'),
				 @endif

				{{{ $asset->months_until_eol()->m }}}
				@lang('general.months')
				)
			@endif
			</div>
		@endif

<div class="col-md-12">
		<!-- Fixtures assets table -->
		<h6>@lang('admin/assets/general.fixtures_assigned')</h6>
		<br>
		<!-- checked out assets table -->
		@if (count($asset->fixturecopies) > 0)
		<table class="table table-hover">
			<thead>
				<tr>
					<th class="col-md-4">@lang('general.name')</th>
					<th class="col-md-4"><span class="line"></span>@lang('admin/fixtures/form.serial')</th>
					<th class="col-md-1"><span class="line"></span>@lang('table.actions')</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($asset->fixturecopies as $copy)
				<tr>
					<td><a href="{{ route('view/fixture', $copy->fixture->id) }}">{{{ $copy->fixture->name }}}</a></td>
					<td>{{{ $copy->fixture->serial }}}</td>
					<td><a href="{{ route('checkin/fixture', $copy->id) }}" class="btn-flat info">@lang('general.checkin')</a>
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
		@else

		<div class="col-md-12">
			<div class="alert alert-info alert-block">
				<i class="fa fa-info-circle"></i>
				@lang('general.no_results')
			</div>
		</div>
		@endif

<!-- Asset Maintenance -->
	<div class="row header">
		<div class="col-md-12">

			<h6>@lang('general.asset_maintenances')
			[ <a href="{{ route('create/asset_maintenances', $asset->id) }}">@lang('button.add')</a> ]
		</h6>
		</div>
	</div>
	<!-- Asset Maintenance table -->
	@if (count($asset->assetmaintenances) > 0)
		<table class="table table-hover">
			<thead>
			<tr>
				<th class="col-md-2">@lang('admin/asset_maintenances/table.supplier_name')</th>
				<th class="col-md-2"><span class="line"></span>@lang('admin/asset_maintenances/form.asset_maintenance_type')</th>
				<th class="col-md-2"><span class="line"></span>@lang('admin/asset_maintenances/form.start_date')</th>
				<th class="col-md-2"><span class="line"></span>@lang('admin/asset_maintenances/form.completion_date')</th>
				<th class="col-md-2"><span class="line"></span>@lang('admin/asset_maintenances/table.is_warranty')</th>
				<th class="col-md-2"><span class="line"></span>@lang('admin/asset_maintenances/form.cost')</th>
				<th class="col-md-1"><span class="line"></span>@lang('table.actions')</th>
			</tr>
			</thead>
			<tbody>
			<?php $totalCost = 0; ?>
			@foreach ($asset->asset_maintenances as $assetMaintenance)
				@if (is_null($assetMaintenance->deleted_at))
				<tr>
					<td><a href="{{ route('view/supplier', $assetMaintenance->supplier_id) }}">{{{ $assetMaintenance->supplier->name }}}</a></td>
					<td>{{{ $assetMaintenance->asset_maintenance_type }}}</td>
					<td>{{{ $assetMaintenance->start_date }}}</td>
					<td>{{{ $assetMaintenance->completion_date }}}</td>
					<td>{{{ $assetMaintenance->is_warranty ? Lang::get('admin/asset_maintenances/message.warranty') : Lang::get('admin/asset_maintenances/message.not_warranty') }}}</td>
					<td>{{{ sprintf( Lang::get( 'general.currency' ) . '%01.2f', $assetMaintenance->cost) }}}</td>
					<?php $totalCost += $assetMaintenance->cost; ?>
					<td><a href="{{ route('update/asset_maintenance', $assetMaintenance->id) }}" class="btn btn-warning"><i class="fa fa-pencil icon-white"></i></a>
					</td>
				</tr>
				@endif
			@endforeach
			</tbody>
			<tfoot>
			<tr>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td>{{{sprintf(Lang::get( 'general.currency' ) . '%01.2f', $totalCost)}}}</td>
			</tr>
			</tfoot>
		</table>
	@else
		<div class="col-md-12">
			<div class="alert alert-info alert-block">
				<i class="fa fa-info-circle"></i>
				@lang('general.no_results')
			</div>
		</div>
	@endif
</div>
<div class="col-md-12">
	<h6>@lang('general.file_uploads') [ <a href="#" data-toggle="modal" data-target="#uploadFileModal">@lang('button.add')</a> ]</h6>
	<table class="table table-hover">
		<thead>
			<tr>
				<th class="col-md-5">@lang('general.notes')</th>
				<th class="col-md-5"><span class="line"></span>@lang('general.file_name')</th>
				<th class="col-md-2"></th>
				<th class="col-md-2"></th>
			</tr>
		</thead>
		<tbody>
			@if (count($asset->uploads) > 0)
				@foreach ($asset->uploads as $file)
				<tr>
					<td>
						@if ($file->note) {{{ $file->note }}}
						@endif
					</td>
					<td>
					{{{ $file->filename }}}
					</td>
					<td>
						@if ($file->filename)
						<a href="{{ route('show/assetfile', [$asset->id, $file->id]) }}" class="btn btn-default">@lang('general.download')</a>
						@endif
					</td>
					<td>
						<a class="btn delete-asset btn-danger btn-sm" href="{{ route('delete/assetfile', [$asset->id, $file->id]) }}"><i class="fa fa-trash icon-white"></i></a>
					</td>
				</tr>
				@endforeach
			@else
				<tr>
					<td colspan="4">
						@lang('general.no_results')
					</td>
				</tr>

			@endif

		</tbody>
	</table>
</div>
<div class="col-md-12">
		<!-- checked out assets table -->
	<table class="table table-hover table-fixed break-word">
		<thead>
			<tr>
				<th class="col-md-3">@lang('general.date')</th>
				<th class="col-md-2"><span class="line"></span>@lang('general.admin')</th>
				<th class="col-md-2"><span class="line"></span>@lang('table.actions')</th>
				<th class="col-md-2"><span class="line"></span>@lang('general.user')</th>
				<th class="col-md-3"><span class="line"></span>@lang('general.notes')</th>
			</tr>
		</thead>
		<tbody>
		@if (count($asset->assetlog) > 0)
			@foreach ($asset->assetlog as $log)

			<tr>
				<td>{{{ $log->created_at }}}</td>
				<td>
					@if (isset($log->user_id))
					{{{ $log->adminlog->fullName() }}}
					@endif
				</td>
				<td>{{ $log->action_type }}</td>
				<td>
					@if ((isset($log->checkedout_to)) && ($log->checkedout_to!=0) && ($log->checkedout_to!=''))

						@if ($log->userlog->deleted_at=='')
							<a href="{{ route('view/user', $log->checkedout_to) }}">
							{{{ $log->userlog->fullName() }}}
							 </a>
						@else
							<del>{{{ $log->userlog->fullName() }}}</del>
						@endif

					@endif
				</td>
				<td>
					@if ($log->note) {{{ $log->note }}}
					@endif
				</td>
			</tr>

			@endforeach
			@endif
			<tr>
				<td>{{ $asset->created_at }}</td>
				<td>
				@if (isset($asset->adminuser->id)) {{{ $asset->adminuser->fullName() }}}
				@else
				@lang('general.unknown_admin')
				@endif
				</td>
				<td>@lang('general.created_asset')</td>
				<td></td>
				<td>
<!--					@if ($asset->notes)
				{{{ $asset->notes }}}
				@endif -->
				</td>
			</tr>
		</tbody>
	</table>
	</div>
</div>
		<!-- side address column -->
		<div class="col-md-3 col-xs-12 address pull-right">

			<!-- Asset notes -->
@if ($asset->notes)

		<h6>@lang('admin/assets/form.notes'):</h6>
		 <div class="break-word">{{ nl2br(e($asset->notes)) }}</div>

@endif

			@if ($qr_code->display)
			<h6>@lang('admin/assets/form.qr')</h6>
			<ul>
				<li>
					<img src="{{{ $qr_code->url }}}" />
				</li>
			</ul>
			@endif


			@if (($asset->assigneduser) && ($asset->assigned_to > 0) && ($asset->deleted_at==''))
				<h6><br>@lang('admin/assets/form.checkedout_to')</h6>
				<ul>

					<li><img src="{{{ $asset->assigneduser->gravatar() }}}" class="img-circle" style="width: 100px; margin-right: 20px;" /><br /><br /></li>
					<li><a href="{{ route('view/user', $asset->assigned_to) }}">{{ $asset->assigneduser->fullName() }}</a></li>


					@if (isset($asset->assetloc->address))
						<li>{{{ $asset->assetloc->address }}}
						@if (isset($asset->assetloc->address2)) {{{ $asset->assetloc->address2 }}}
						@endif
						</li>
						@if (isset($asset->assetloc->city))
							<li>{{{ $asset->assetloc->city }}}, {{{ $asset->assetloc->state }}} {{{ $asset->assetloc->zip }}}</li>
						@endif

					@endif

					@if (isset($asset->assigneduser->email))
						<li><br /><i class="fa fa-envelope-o"></i> <a href="mailto:{{{ $asset->assigneduser->email }}}">{{{ $asset->assigneduser->email }}}</a></li>
					@endif

					@if ((isset($asset->assigneduser->phone)) && ($asset->assigneduser->phone!=''))
						<li><i class="fa fa-phone"></i> {{{ $asset->assigneduser->phone }}}</li>
					@endif


					</ul>

			 @endif

			@if (($asset->status_id ) && ($asset->status_id > 0))
			<!-- Status Info -->

				@if ($asset->assetstatus)
					<h6><br>
						@if (($asset->assetstatus->deployable=='1') && ($asset->assigned_to > 0))
							@lang('admin/assets/general.asset')
							@lang('general.deployed')
						@else
							{{{ $asset->assetstatus->name }}}
							@lang('admin/assets/general.asset')
						@endif
					<ul>

						 @if (($asset->assetstatus->deployable=='1') && ($asset->assigned_to > 0) && ($asset->deleted_at=='') && ($asset->assetlog->first()))
							@if ($asset->assetlog->first()->expected_checkin)
								<li><br />@lang('admin/assets/form.expected_checkin')
									: {{{ date('Y-m-d', strtotime($asset->assetlog->first()->expected_checkin)) }}}</li>
							@endif
						<li><br /><a href="{{ route('checkin/assets', $asset->id) }}" class="btn btn-primary btn-sm">@lang('admin/assets/general.checkin')</a></li>
						@elseif ((($asset->assetstatus->deployable=='1') &&  (($asset->assigned_to=='') || ($asset->assigned_to==0))) && ($asset->deleted_at==''))
						<li><br /><a href="{{ route('checkout/assets', $asset->id) }}" class="btn btn-info btn-sm">@lang('admin/assets/general.checkout')</a></li>
						@elseif  (($asset->deleted_at!='') && ($asset->model->deleted_at==''))

						<li><br /><a href="{{ route('restore/assets', $asset->id) }}" class="btn-flat large info ">@lang('admin/assets/general.restore')</a></li>

						@endif
					</ul>

					@if (($asset->assetstatus->notes) && ($asset->assigned_to==''))
					<div class="col-md-12">
						<div class="alert alert-info alert-block">
							<i class="fa fa-info-circle"></i>
							{{{ $asset->assetstatus->notes }}}

						</div>
					</div>
					@endif

				 @endif
			@endif

		</div>
	</div>
</div>

<!-- Modal -->
<div class="modal fade" id="uploadFileModal" tabindex="-1" role="dialog" aria-labelledby="uploadFileModalLabel" aria-hidden="true">
  <div class="modal-dialog">
	<div class="modal-content">
	  <div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		<h4 class="modal-title" id="uploadFileModalLabel">Upload File</h4>
	  </div>
	  {{ Form::open([
	  'method' => 'POST',
	  'route' => ['upload/assets', $asset->id],
	  'files' => true, 'class' => 'form-horizontal' ]) }}
	  <div class="modal-body">

		<p><p>@lang('admin/assets/general.filetype_info')</p>.</p>

		 <div class="form-group col-md-12">
		 <div class="input-group col-md-12">
			<input class="col-md-12 form-control" type="text" name="notes" id="notes" placeholder="Notes">
		</div>
		</div>
		<div class="form-group col-md-12">
		 <div class="input-group col-md-12">
			{{ Form::file('assetfile[]', ['multiple' => 'multiple']) }}
		</div>
		</div>


	  </div>
	  <div class="modal-footer">
		<button type="button" class="btn btn-default" data-dismiss="modal">@lang('button.cancel')</button>
		<button type="submit" class="btn btn-primary btn-sm">@lang('button.upload')</button>
	  </div>
	  {{ Form::close() }}
	</div>
  </div>
</div>

@stop
