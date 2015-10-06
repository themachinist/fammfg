@extends('backend/layouts/default')

{{-- Page title --}}
@section('title')
@lang('admin/fixtures/general.view')
 - {{{ $fixture->name }}}
@parent
@stop

{{-- Page content --}}
@section('content')

<div class="row header">
    <div class="col-md-12">
        <div class="btn-group pull-right">
            <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">@lang('button.actions')
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                <li><a href="{{ route('update/fixture', $fixture->id) }}">@lang('admin/fixtures/general.edit')</a></li>
                <li><a href="{{ route('clone/fixture', $fixture->id) }}">@lang('admin/fixtures/general.clone')</a></li>
            </ul>
        </div>
       <h3 class="name">@lang('general.history_for')
       {{{ $fixture->name }}}</h3>
    </div>
</div>

<div class="user-profile ">
<div class="row profile">
<div class="col-md-9 bio">

@if ($fixture->serial)
	<div class="col-md-12 col-sm-12" style="padding-bottom: 10px; margin-left: 15px; word-wrap: break-word;">
	<strong>@lang('admin/fixtures/form.serial'): </strong>
	{{{ $fixture->serial }}}
	</div>
@endif

<div class="col-md-12" style="padding-bottom: 20px">

@if ($fixture->fixture_name)
<div class="col-md-6" style="padding-bottom: 5px"><strong>@lang('admin/fixtures/form.to_name'): </strong>
{{{ $fixture->fixture_name }}} </div>
@endif

@if ($fixture->fixture_email)
<div class="col-md-6" style="padding-bottom: 5px"><strong>@lang('admin/fixtures/form.to_email'): </strong>
{{{ $fixture->fixture_email }}} </div>
@endif

@if ($fixture->supplier_id)
    <div class="col-md-6" style="padding-bottom: 5px"><strong>@lang('admin/fixtures/form.supplier'): </strong>
    <a href="{{ route('view/supplier', $fixture->supplier_id) }}">
    {{{ $fixture->supplier->name }}}
    </a> </div>
@endif

@if ($fixture->expiration_date > 0)
<div class="col-md-6" style="padding-bottom: 5px"><strong>@lang('admin/fixtures/form.expiration'): </strong>
{{{ $fixture->expiration_date }}} </div>
@endif


 @if ($fixture->depreciation)
	<div class="col-md-6" style="padding-bottom: 5px">
	<strong>@lang('admin/hardware/form.depreciation'): </strong>
	{{{ $fixture->depreciation->name }}}
		({{{ $fixture->depreciation->months }}}
		@lang('admin/hardware/form.months')
		)
	</div>

	<div class="col-md-6" style="padding-bottom: 5px">
		<strong>@lang('admin/hardware/form.depreciates_on'): </strong>
		{{{ $fixture->depreciated_date()->format("Y-m-d") }}}
	</div>

	<div class="col-md-6" style="padding-bottom: 5px">
		<strong>@lang('admin/hardware/form.fully_depreciated'): </strong>
        @if ($fixture->time_until_depreciated()->y > 0)
            {{{ $fixture->time_until_depreciated()->y }}}
            @lang('admin/hardware/form.years'),
        @endif
		{{{ $fixture->time_until_depreciated()->m }}}
		@lang('admin/hardware/form.months')

	 </div>
@endif

</div>


<div class="col-md-12" style="padding-top: 60px;">
                <!-- checked out assets table -->
                <h6>{{ $fixture->seats }} @lang('admin/fixtures/general.fixture_seats')</h6>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th class="col-md-2">@lang('admin/fixtures/general.seat')</th>
                             <th class="col-md-2">@lang('admin/fixtures/general.user')</th>
                             <th class="col-md-4">@lang('admin/fixtures/form.asset')</th>
                             <th class="col-md-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $count=1; ?>
                        @if ($fixture->fixtureseats)
                            @foreach ($fixture->fixtureseats as $fixturedto)

                            <tr>
                                <td>Seat {{ $count }} </td>
                                <td>
                                    @if (($fixturedto->assigned_to) && ($fixturedto->deleted_at == NULL))
                                        <a href="{{ route('view/user', $fixturedto->assigned_to) }}">
                                    {{{ $fixturedto->user->fullName() }}}
                                    </a>
                                    @elseif (($fixturedto->assigned_to) && ($fixturedto->deleted_at != NULL))
                                        <del>{{{ $fixturedto->user->fullName() }}}</del>
                                    @elseif ($fixturedto->asset_id)
                                        @if ($fixturedto->asset->assigned_to != 0)
                                            <a href="{{ route('view/user', $fixturedto->asset->assigned_to) }}">
                                                {{{ $fixturedto->asset->assigneduser->fullName() }}}
                                            </a>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    @if ($fixturedto->asset_id)
                                        <a href="{{ route('view/hardware', $fixturedto->asset_id) }}">
                                        {{{ $fixturedto->asset->name }}} {{{ $fixturedto->asset->asset_tag }}}
                                    </a>
                                    @endif
                                </td>
                                <td>
                                    @if (($fixturedto->assigned_to) || ($fixturedto->asset_id))
                                        @if ($fixture->reassignable)
                                            <a href="{{ route('checkin/fixture', $fixturedto->id) }}" class="btn btn-primary btn-sm">
                                            @lang('general.checkin')
                                            </a>
                                        @else
                                            <span>Assigned</span>
                                        @endif
                                    @else
                                        <a href="{{ route('checkout/fixture', $fixturedto->id) }}" class="btn btn-info btn-sm">
                                        @lang('general.checkout')</a>
                                    @endif
                                </td>

                            </tr>
                            <?php $count++; ?>
                            @endforeach
                            @endif


                    </tbody>
                </table>
</div>

<div class="col-md-12">


 	<h6>@lang('general.file_uploads') [ <a href="#" data-toggle="modal" data-target="#uploadFileModal">@lang('button.add')</a> ]</h6>


 	<table class="table table-hover">
                    <thead>
                        <tr>
                            <th class="col-md-5">@lang('admin/fixtures/form.notes')</th>
                            <th class="col-md-5"><span class="line"></span>@lang('general.file_name')</th>
                            <th class="col-md-2"></th>
                            <th class="col-md-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (count($fixture->uploads) > 0)
							@foreach ($fixture->uploads as $file)
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
									<a href="{{ route('show/fixturefile', [$fixture->id, $file->id]) }}" class="btn btn-default">Download</a>
									@endif
								</td>
								<td>
									<a class="btn delete-asset btn-danger btn-sm" href="{{ route('delete/fixturefile', [$fixture->id, $file->id]) }}" data-content="Are you sure you wish to delete this file?" data-title="Delete {{{ $file->filename }}}?"><i class="fa fa-trash icon-white"></i></a>
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
      'route' => ['upload/fixture', $fixture->id],
      'files' => true, 'class' => 'form-horizontal' ]) }}
      <div class="modal-body">

		<p>@lang('admin/fixtures/general.filetype_info')</p>

		 <div class="form-group col-md-12">
		 <div class="input-group col-md-12">
		 	<input class="col-md-12 form-control" type="text" name="notes" id="notes" placeholder="Notes">
		</div>
		</div>
		<div class="form-group col-md-12">
		 <div class="input-group col-md-12">
			{{ Form::file('fixturefile[]', ['multiple' => 'multiple']) }}
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



<div class="col-md-12">
                <h6>@lang('admin/fixtures/general.checkout_history')</h6>

                <table class="table table-hover table-fixed break-word">
                    <thead>
                        <tr>
                            <th class="col-md-3">@lang('general.date')</th>
                            <th class="col-md-3"><span class="line"></span>@lang('general.admin')</th>
                            <th class="col-md-3"><span class="line"></span>@lang('button.actions')</th>
                            <th class="col-md-3"><span class="line"></span>@lang('admin/fixtures/general.user')</th>
                            <th class="col-md-3"><span class="line"></span>@lang('admin/fixtures/form.notes')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (count($fixture->assetlog) > 0)
                        @foreach ($fixture->assetlog as $log)
                        <tr>
                            <td>{{ $log->created_at }}</td>
                            <td>
                                @if (isset($log->user_id))
                                {{{ $log->adminlog->fullName() }}}
                                @endif
                            </td>
                            <td>{{ $log->action_type }}</td>

                            <td>
                                @if (($log->userlog) && ($log->userlog->id!='0'))
                                <a href="{{ route('view/user', $log->checkedout_to) }}">
                                {{{ $log->userlog->fullName() }}}
                                </a>

                                @elseif ($log->action_type=='uploaded')

                                		{{ $log->filename }}

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
                            <td>{{{ $fixture->created_at }}}</td>
                            <td>
                            @if ($fixture->adminuser) {{{ $fixture->adminuser->fullName() }}}
                            @else
                            @lang('general.unknown_admin')
                            @endif
                            </td>
                            <td>@lang('general.created_asset')</td>
                            <td></td>
                            <td>
                            @if ($fixture->notes)
                            {{{ $fixture->notes }}}
                            @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
        </div>
</div>
        <!-- side address column -->
        <div class="col-md-3 col-xs-12 address pull-right">
        <h6><br>@lang('general.moreinfo'):</h6>
                <ul>
                    @if ($fixture->purchase_order)
                    <li><strong>@lang('admin/fixtures/form.purchase_order'):</strong>
                    {{{ $fixture->purchase_order }}} </li>
                    @endif
                    @if ($fixture->purchase_date > 0)
                    <li><strong>@lang('admin/fixtures/form.date'):</strong>
                    {{{ $fixture->purchase_date }}} </li>
                    @endif
                    @if ($fixture->purchase_cost > 0)
                    <li><strong>@lang('admin/fixtures/form.cost'):</strong>
                    @lang('general.currency')
                    {{{ number_format($fixture->purchase_cost,2) }}} </li>
                    @endif
                    @if ($fixture->order_number)
                    <li><strong>@lang('admin/fixtures/form.order'):</strong>
                    {{{ $fixture->order_number }}} </li>
                    @endif
                    @if (($fixture->seats) && ($fixture->seats) > 0)
                    <li><strong>@lang('admin/fixtures/form.seats'):</strong>
                    {{{ $fixture->seats }}} </li>
                    @endif

                    <li><strong>@lang('admin/fixtures/form.reassignable'):</strong>
                                {{ $fixture->reassignable ? 'Yes' : 'No' }}
                    </li>

                    @if ($fixture->notes)
                    	 <li><strong>@lang('admin/fixtures/form.notes'):</strong>
                        <li class="break-word">{{ nl2br(e($fixture->notes)) }}</li>
                    @endif
                </ul>
        </div>
    </div>
</div>
@stop
