@extends('backend/layouts/default')

{{-- Page title --}}
@section('title')
@lang('general.tool_report') ::
@parent
@stop

{{-- Page content --}}
@section('content')


<div class="page-header">

    <div class="pull-right">
        <a href="{{ route('reports/export/tools') }}" class="btn btn-flat gray pull-right"><i class="fa fa-download icon-white"></i>
        @lang('admin/tools/table.dl_csv')</a>
        </div>

    <h3>@lang('general.tool_report')</h3>
</div>

<div class="row">

<div class="table-responsive">
<table id="example">
        <thead>
            <tr role="row">
            <th class="col-sm-1">@lang('admin/tools/table.title')</th>
            <th class="col-sm-1">@lang('admin/tools/general.total')</th>
            <th class="col-sm-1">@lang('admin/tools/general.remaining')</th>
        </tr>
    </thead>
    <tbody>

        @foreach ($tools as $tool)
        <tr>
            <td>{{ $tool->name }}</td>
            <td>{{ $tool->qty }}</td>
            <td>{{ $tool->numRemaining() }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

</div>

@stop
