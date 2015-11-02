@extends('backend/layouts/default')

{{-- Page title --}}
@section('title')
@lang('general.fixture_report') ::
@parent
@stop

{{-- Page content --}}
@section('content')


<div class="page-header">

    <div class="pull-right">
        <a href="{{ route('reports/export/fixtures') }}" class="btn btn-flat gray pull-right"><i class="fa fa-download icon-white"></i>
        @lang('admin/hardware/table.dl_csv')</a>
        </div>

    <h3>@lang('general.fixture_report')</h3>
</div>

<div class="row">

<div class="table-responsive">
<table id="example">
        <thead>
            <tr role="row">
            <th class="col-sm-1">@lang('admin/fixtures/table.title')</th>
            <th class="col-sm-1">@lang('admin/fixtures/table.serial')</th>
            <th class="col-sm-1">@lang('admin/fixtures/form.seats')</th>
            <th class="col-sm-1">@lang('admin/fixtures/form.remaining_seats')</th>
            <th class="col-sm-1">@lang('admin/fixtures/form.expiration')</th>
            <th class="col-sm-1">@lang('admin/fixtures/form.date')</th>
            <th class="col-sm-1">@lang('admin/fixtures/form.cost')</th>
        </tr>
    </thead>
    <tbody>

        @foreach ($fixtures as $fixture)
        <tr>
            <td>{{{ $fixture->name }}}</td>
            <td>{{{ mb_strimwidth($fixture->serial, 0, 50, "...") }}}</td>
            <td>{{ $fixture->seats }}</td>
            <td>{{ $fixture->remaincount() }}</td>
            <td>{{ $fixture->expiration_date }}</td>
            <td>{{ $fixture->purchase_date }}</td>
            <td>
            {{{ Setting::first()->default_currency }}}
            {{{ number_format($fixture->purchase_cost) }}}</td>
        </tr>
        @endforeach
    </tbody>
</table>

</div>

@stop
