@extends('backend/layouts/default')

{{-- Page title --}}
@section('title')
    @if ($category->id)
        @lang('admin/categories/general.update') ::
    @else
        @lang('admin/categories/general.create') ::
    @endif
@parent
@stop

{{-- Page content --}}
@section('content')


<div class="row header">
    <div class="col-md-12">
        <a href="{{ URL::previous() }}" class="btn-flat gray pull-right"><i class="fa fa-arrow-left icon-white"></i> @lang('general.back')</a>
        <h3>
        @if ($category->id)
            @lang('admin/categories/general.update')
        @else
            @lang('admin/categories/general.create')
        @endif
</h3>
    </div>
</div>

<div class="user-profile">
<div class="row profile">
<div class="col-md-9 bio">

                        <form class="form-horizontal" method="post" action="" autocomplete="off">
                        <!-- CSRF Token -->
                        <input type="hidden" name="_token" value="{{ csrf_token() }}" />

                        <!-- Name -->
                        <div class="form-group {{ $errors->has('name') ? ' has-error' : '' }}">
	                        <div class="col-md-3">
	                        	{{ Form::label('name', Lang::get('admin/categories/general.category_name')) }}
	                        	<i class='fa fa-asterisk'></i>
	                        </div>                        
                            <div class="col-md-9">
                                <input class="form-control" type="text" name="name" id="name" value="{{{ Input::old('name', $category->name) }}}" />
                                {{ $errors->first('name', '<br><span class="alert-msg"><i class="fa fa-times"></i> :message</span>') }}
                            </div>
                        </div>
                        
                        <!-- Type -->
			            <div class="form-group {{ $errors->has('category_type') ? ' has-error' : '' }}">
				            <div class="col-md-3">
			               	{{ Form::label('category_type', Lang::get('general.type')) }}
			               	<i class='fa fa-asterisk'></i>
				            </div>
			                <div class="col-md-7">				                
			                    {{ Form::select('category_type', $category_types , Input::old('category_type', $category->category_type), array('class'=>'select2', 'style'=>'min-width:350px')) }}
			                    {{ $errors->first('category_type', '<br><span class="alert-msg"><i class="fa fa-times"></i> :message</span>') }}
			                </div>
			            </div>
                        						
						
						 <!-- Require Acceptance -->
                        <div class="checkbox col-md-offset-3">
							<label>
								{{ Form::checkbox('require_acceptance', '1', Input::old('require_acceptance', $category->require_acceptance)) }}
								@lang('admin/categories/general.require_acceptance')
							</label>
						</div>
						
						<!-- Email on Checkin -->
                        <div class="checkbox col-md-offset-3">
                            <label>
                                {{ Form::checkbox('checkin_email', '1', Input::old('checkin_email', $category->checkin_email)) }}
                                @lang('admin/categories/general.checkin_email')
                            </label>
                        </div>


						<hr>
                        <!-- Form actions -->
                        <div class="form-group">
                       
                            <div class="col-md-7 col-md-offset-3">
                                <a class="btn btn-link" href="{{ URL::previous() }}">@lang('button.cancel')</a>
                                <button type="submit" class="btn btn-success"><i class="fa fa-check icon-white"></i> @lang('general.save')</button>
                            </div>
                        </div>
                    </form>
                    <br><br><br><br><br>
                    </div>

                    <!-- side address column -->
                    <div class="col-md-3 col-xs-12 address pull-right">
                        <br /><br />
                        <h6>@lang('admin/categories/general.about_asset_categories')</h6>
                        <p>@lang('admin/categories/general.about_categories') </p>

                    </div>
</div>
</div>
@stop
