@extends('backend/layouts/default')

{{-- Page title --}}
@section('title')
@lang('general.checkedout_report') ::
@parent
@stop

{{-- Page content --}}
@section('content')


<div class="page-header">

    <h3>@lang('general.checkedout_report')</h3>
</div>


<div class="row">

<div class="table-responsive">
<table id="example">
        <thead>
            <tr role="row">
            <th class="col-sm-1">@lang('general.date')</th>
            <th class="col-sm-1">@lang('general.item')</th>
            <th class="col-sm-1">@lang('general.type')</th>
            <th class="col-sm-1">@lang('general.user')</th>
            <th class="col-sm-1">@lang('general.notes')</th>
        </tr>
    </thead>
    <tbody>

        @foreach ($log_checkedout as $log_action)
        <tr>
            <td>
				{{ date("M d G:i", strtotime($log_action->created_at)) }}
			</td>
			<td>
				{{ $log_action->name }}
			</td>
            <td>
				{{ $log_action->asset_type }}
			</td>
            <td>
				{{ $log_action->first_name }} {{ $log_action->last_name }}
			</td>
            <td>
				{{ $log_action->note }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

</div>

@stop
