@extends('backend/layouts/default')

{{-- Page title --}}
@section('title')
@lang('general.dashboard') ::
@parent
@stop

{{-- Page content --}}
@section('content')




<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/lib/morris.css') }}">

<!-- morrisjs -->
<script src="{{ asset('assets/js/raphael-min.js') }}"></script>
<script src="{{ asset('assets/js/morris.min.js') }}"></script>


<div class="row">

<!-- morris bar & donut charts -->

    <div class="col-md-12">
        <h4 class="title">@lang('general.dashboard')</h4>
        <br>
    </div>
    <div class="col-md-9 chart">
        <h5>@lang('general.recent_checkedout_items') (<a href="{{ Config::get('app.url') }}/reports/checkedout">view all</a>)</h5>

        <table class="table table-hover table-fixed break-word">
			<thead>
			    <tr>
			        <th class="col-md-1"><span class="line"></span>@lang('general.date')</th>
			        <th class="col-md-2"><span class="line"></span>@lang('general.user')</th>
			        <th class="col-md-3"><span class="line"></span>@lang('general.item')</th>
			        <th class="col-md-2"><span class="line"></span>@lang('general.type')</th>
			        <th class="col-md-3"><span class="line"></span>@lang('general.notes')</th>
			    </tr>
			</thead>
			<tbody>
			@if (count($checkedout_tools) > 0)
				@foreach (array_slice($checkedout_tools, 0, 5) as $tool)
			    <tr>
					<td>
						{{{ date("M d", strtotime($tool->created_at)) }}}
					</td>
					<td>
						<a href="{{ route('view/user', $tool->assigned_to) }}">{{ $tool->first_name }} {{ $tool->last_name }}</a>
					</td>
					<td>
						@if		($tool->asset_type == "tool")
						<a href="{{ route('view/tool', $tool->tool_id) }}">{{ $tool->name }}</a>
						@elseif ($tool->asset_type == "consumable")
						<a href="{{ route('view/consumable', $tool->tool_id) }}">{{ $tool->name }}</a>
						@elseif ($tool->asset_type == "fixture")
						<a href="{{ route('view/fixture', $tool->tool_id) }}">{{ $tool->name }}</a>
						@endif
					</td>
					<td>
						{{ $tool->asset_type }}
					</td>
					<td>
						{{ $tool->note }}
					</td>
			    </tr>
			   @endforeach
			@endif
			</tbody>
			</table>


    </div>

    <div class="col-md-9 chart">
        <h5>@lang('general.recent_activity') (<a href="{{ Config::get('app.url') }}/reports/activity">view all</a>)</h5>

        <table class="table table-hover table-fixed break-word">
			<thead>
			    <tr>
			        <th class="col-md-1"><span class="line"></span>@lang('general.date')</th>
			        <th class="col-md-2"><span class="line"></span>@lang('general.admin')</th>
			        <th class="col-md-3"><span class="line"></span>@lang('table.item')</th>
			        <th class="col-md-2"><span class="line"></span>@lang('table.actions')</th>
			        <th class="col-md-3"><span class="line"></span>@lang('general.user')</th>
			    </tr>
			</thead>
			<tbody>
			@if (count($recent_activity) > 0)
				@foreach ($recent_activity as $activity)
			    <tr>
			       <td>{{{ date("M d", strtotime($activity->created_at)) }}}</td>
			       <td>
                       @if ($activity->action_type!='requested')
                            <a href="{{ route('view/user', $activity->user_id) }}">{{{ $activity->adminlog->fullName() }}}</a>
                       @endif

                       </td>

			       <td>
			           	@if (($activity->assetlog) && ($activity->asset_type=="asset"))
			            	<a href="{{ route('view/asset', $activity->asset_id) }}">{{ $activity->assetlog->showAssetName() }}</a>
			            @elseif (($activity->fixturelog) && ($activity->asset_type=="fixture"))
			            	<a href="{{ route('view/fixture', $activity->asset_id) }}">{{{ $activity->fixturelog->name }}}</a>
                        @elseif (($activity->consumablelog) && ($activity->asset_type=="consumable"))
    			            <a href="{{ route('view/consumable', $activity->consumable_id) }}">{{{ $activity->consumablelog->name }}}</a>
			            @elseif (($activity->toollog) && ($activity->asset_type=="tool"))
			            	<a href="{{ route('view/tool', $activity->tool_id) }}">{{{ $activity->toollog->name }}}</a>
                        @else
                            @lang('general.bad_data')
			            @endif

			           	</td>
			       <td>
				       {{ strtolower(Lang::get('general.'.str_replace(' ','_',$activity->action_type))) }}
			       </td>
			       <td>
                       @if ($activity->action_type=='requested')
                            <a href="{{ route('view/user', $activity->user_id) }}">{{{ $activity->adminlog->fullName() }}}</a>
                       @elseif ($activity->userlog)
			           		<a href="{{ route('view/user', $activity->checkedout_to) }}">{{{ $activity->userlog->fullName() }}}</a>
			           @endif

			           </td>


			    </tr>
			   @endforeach
			@endif
			</tbody>
			</table>


    </div>

</div>





@stop
