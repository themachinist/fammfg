@extends('backend/layouts/default')

{{-- Page title --}}
@section('title')
@lang('general.activity_report') ::
@parent
@stop

{{-- Page content --}}
@section('content')


<div class="page-header">

    <h3>@lang('general.activity_report')</h3>
</div>


<div class="row">

<div class="table-responsive">
<table id="example">
        <thead>
            <tr role="row">
            <th class="col-sm-1">@lang('general.admin')</th>
            <th class="col-sm-1">@lang('general.action')</th>
            <th class="col-sm-1">@lang('general.type')</th>
            <th class="col-sm-1">@lang('general.item')</th>
            <th class="col-sm-1">@lang('general.user')</th>
            <th class="col-sm-1">@lang('general.date')</th>
        </tr>
    </thead>
    <tbody>

        @foreach ($log_actions as $log_action)
        <tr>
            <td><a href="../admin/users/{{ $log_action->adminlog->id }}/view">{{{ $log_action->adminlog->fullName() }}}</a></td>
            <td>{{{ $log_action->action_type }}}</td>
            <td>
	            @if ($log_action->asset_type=="asset")
	            	Asset
	            @elseif ($log_action->asset_type=="fixture")
	            	Fixture
	            @elseif ($log_action->asset_type=="tool")
	            	Tool
                @elseif ($log_action->asset_type=="consumable")
    	            Consumable
	            @endif
            </td>

            <td>
            @if (($log_action->assetlog) && ($log_action->asset_type=="asset"))
                 {{ $log_action->assetlog->showAssetName() }}
             @elseif (($log_action->fixturelog) && ($log_action->asset_type=="fixture"))
                 {{ $log_action->fixturelog->name }}
             @elseif (($log_action->consumablelog) && ($log_action->asset_type=="consumable"))
                 {{ $log_action->consumablelog->name }}
             @elseif (($log_action->toollog) && ($log_action->asset_type=="tool"))
                 {{ $log_action->toollog->name }}
             @else
                 @lang('general.bad_data')
             @endif
            </td>
            <td>
	            @if ($log_action->userlog)
	            	<a href="../admin/users/{{ $log_action->userlog->id }}/view">{{{ $log_action->userlog->fullName() }}}</a>
	            @endif
            </td>

            <td>{{{ $log_action->created_at }}}</td>
        </tr>
        @endforeach
    </tbody>
</table>

</div>

@stop
