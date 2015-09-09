<?php namespace Controllers\Admin;

use AdminController;
use Input;
use Lang;
use Tool;
use Redirect;
use Setting;
use DB;
use Sentry;
use Str;
use Validator;
use View;
use User;
use Actionlog;
use Mail;
use Datatable;
use Slack;
use Config;

class ToolsController extends AdminController
{
    /**
     * Show a list of all the tools.
     *
     * @return View
     */

    public function getIndex()
    {
        return View::make('backend/tools/index');
    }


    /**
     * Tool create.
     *
     * @return View
     */
    public function getCreate()
    {
        // Show the page
        $category_list = array('' => '') + DB::table('categories')->where('category_type','=','tool')->whereNull('deleted_at')->orderBy('name','ASC')->lists('name', 'id');
        return View::make('backend/tools/edit')->with('tool',new Tool)->with('category_list',$category_list);
    }


    /**
     * Tool create form processing.
     *
     * @return Redirect
     */
    public function postCreate()
    {

        // create a new model instance
        $tool = new Tool();

        $validator = Validator::make(Input::all(), $tool->rules);

        if ($validator->fails())
        {
            // The given data did not pass validation
            return Redirect::back()->withInput()->withErrors($validator->messages());
        }
        else{

            // Update the tool data
            $tool->name            		= e(Input::get('name'));
            $tool->category_id            	= e(Input::get('category_id'));
            $tool->qty            			= e(Input::get('qty'));
            $tool->user_id          		= Sentry::getId();

            // Was the tool created?
            if($tool->save()) {
                // Redirect to the new tool  page
                return Redirect::to("admin/tools")->with('success', Lang::get('admin/tools/message.create.success'));
            }
        }

        // Redirect to the tool create page
        return Redirect::to('admin/tools/create')->with('error', Lang::get('admin/tools/message.create.error'));


    }

    /**
     * Tool update.
     *
     * @param  int  $toolId
     * @return View
     */
    public function getEdit($toolId = null)
    {
        // Check if the tool exists
        if (is_null($tool = Tool::find($toolId))) {
            // Redirect to the blogs management page
            return Redirect::to('admin/tools')->with('error', Lang::get('admin/tools/message.does_not_exist'));
        }

		$category_list = array('' => '') + DB::table('categories')->where('category_type','=','tool')->whereNull('deleted_at')->orderBy('name','ASC')->lists('name', 'id');
        return View::make('backend/tools/edit', compact('tool'))->with('category_list',$category_list);
    }


    /**
     * Tool update form processing page.
     *
     * @param  int  $toolId
     * @return Redirect
     */
    public function postEdit($toolId = null)
    {
        // Check if the blog post exists
        if (is_null($tool = Tool::find($toolId))) {
            // Redirect to the blogs management page
            return Redirect::to('admin/tools')->with('error', Lang::get('admin/tools/message.does_not_exist'));
        }


        // get the POST data
        $new = Input::all();

        // attempt validation
        $validator = Validator::make(Input::all(), $tool->validationRules($toolId));


        if ($validator->fails())
        {
            // The given data did not pass validation
            return Redirect::back()->withInput()->withErrors($validator->messages());
        }
        // attempt validation
        else {

            // Update the tool data
            $tool->name            		= e(Input::get('name'));
            $tool->category_id            	= e(Input::get('category_id'));
            $tool->qty            			= e(Input::get('qty'));

            // Was the tool created?
            if($tool->save()) {
                // Redirect to the new tool page
                return Redirect::to("admin/tools")->with('success', Lang::get('admin/tools/message.update.success'));
            }
        }

        // Redirect to the tool management page
        return Redirect::to("admin/tools/$toolID/edit")->with('error', Lang::get('admin/tools/message.update.error'));

    }

    /**
     * Delete the given tool.
     *
     * @param  int  $toolId
     * @return Redirect
     */
    public function getDelete($toolId)
    {
        // Check if the blog post exists
        if (is_null($tool = Tool::find($toolId))) {
            // Redirect to the blogs management page
            return Redirect::to('admin/tools')->with('error', Lang::get('admin/tools/message.not_found'));
        }


		if ($tool->hasUsers() > 0) {
			 return Redirect::to('admin/tools')->with('error', Lang::get('admin/tools/message.assoc_users', array('count'=> $tool->hasUsers())));
		} else {
			$tool->delete();

            // Redirect to the locations management page
            return Redirect::to('admin/tools')->with('success', Lang::get('admin/tools/message.delete.success'));

		}





    }



    /**
    *  Get the tool information to present to the tool view page
    *
    * @param  int  $toolId
    * @return View
    **/
    public function getView($toolID = null)
    {
        $tool = Tool::find($toolID);

        if (isset($tool->id)) {
                return View::make('backend/tools/view', compact('tool'));
        } else {
            // Prepare the error message
            $error = Lang::get('admin/tools/message.does_not_exist', compact('id'));

            // Redirect to the user management page
            return Redirect::route('tools')->with('error', $error);
        }


    }

    /**
    * Check out the tool to a person
    **/
    public function getCheckout($toolId)
    {
        // Check if the tool exists
        if (is_null($tool = Tool::find($toolId))) {
            // Redirect to the tool management page with error
            return Redirect::to('tools')->with('error', Lang::get('admin/tools/message.not_found'));
        }

        // Get the dropdown of users and then pass it to the checkout view
        $users_list = array('' => 'Select a User') + DB::table('users')->select(DB::raw('concat(last_name,", ",first_name," (",username,")") as full_name, id'))->whereNull('deleted_at')->orderBy('last_name', 'asc')->orderBy('first_name', 'asc')->lists('full_name', 'id');

        return View::make('backend/tools/checkout', compact('tool'))->with('users_list',$users_list);

    }

    /**
    * Check out the tool to a person
    **/
    public function postCheckout($toolId)
    {
        // Check if the tool exists
        if (is_null($tool = Tool::find($toolId))) {
            // Redirect to the tool management page with error
            return Redirect::to('tools')->with('error', Lang::get('admin/tools/message.not_found'));
        }

		$admin_user = Sentry::getUser();
        $assigned_to = e(Input::get('assigned_to'));


        // Declare the rules for the form validation
        $rules = array(
            'assigned_to'   => 'required|min:1'
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails()) {
            // Ooops.. something went wrong
            return Redirect::back()->withInput()->withErrors($validator);
        }


        // Check if the user exists
        if (is_null($user = User::find($assigned_to))) {
            // Redirect to the tool management page with error
            return Redirect::to('admin/tools')->with('error', Lang::get('admin/tools/message.user_does_not_exist'));
        }

        // Update the tool data
        $tool->assigned_to            		= e(Input::get('assigned_to'));

        $tool->users()->attach($tool->id, array(
        'tool_id' => $tool->id,
        'assigned_to' => e(Input::get('assigned_to'))));

            $logaction = new Actionlog();
            $logaction->tool_id = $tool->id;
            $logaction->checkedout_to = $tool->assigned_to;
            $logaction->asset_type = 'tool';
            $logaction->location_id = $user->location_id;
            $logaction->user_id = Sentry::getUser()->id;
            $logaction->note = e(Input::get('note'));

            $settings = Setting::getSettings();

			if ($settings->slack_endpoint) {


				$slack_settings = [
				    'username' => $settings->botname,
				    'channel' => $settings->slack_channel,
				    'link_names' => true
				];

				$client = new \Maknz\Slack\Client($settings->slack_endpoint,$slack_settings);

				try {
						$client->attach([
						    'color' => 'good',
						    'fields' => [
						        [
						            'title' => 'Checked Out:',
						            'value' => strtoupper($logaction->asset_type).' <'.Config::get('app.url').'/admin/tools/'.$tool->id.'/view'.'|'.$tool->name.'> checked out to <'.Config::get('app.url').'/admin/users/'.$user->id.'/view|'.$user->fullName().'> by <'.Config::get('app.url').'/admin/users/'.$admin_user->id.'/view'.'|'.$admin_user->fullName().'>.'
						        ],
						        [
						            'title' => 'Note:',
						            'value' => e($logaction->note)
						        ],



						    ]
						])->send('Tool Checked Out');

					} catch (Exception $e) {

					}

			}



            $log = $logaction->logaction('checkout');

            $tool_user = DB::table('tools_users')->where('assigned_to','=',$tool->assigned_to)->where('tool_id','=',$tool->id)->first();

            $data['log_id'] = $logaction->id;
            $data['eula'] = $tool->getEula();
            $data['first_name'] = $user->first_name;
            $data['item_name'] = $tool->name;
            $data['checkout_date'] = $logaction->created_at;
            $data['item_tag'] = '';
            $data['expected_checkin'] = '';
            $data['note'] = $logaction->note;
            $data['require_acceptance'] = $tool->requireAcceptance();


            if (($tool->requireAcceptance()=='1')  || ($tool->getEula())) {

	            Mail::send('emails.accept-asset', $data, function ($m) use ($user) {
	                $m->to($user->email, $user->first_name . ' ' . $user->last_name);
	                $m->subject('Confirm tool delivery');
	            });
            }

            // Redirect to the new tool page
            return Redirect::to("admin/tools")->with('success', Lang::get('admin/tools/message.checkout.success'));



    }


    /**
    * Check the tool back into inventory
    *
    * @param  int  $toolId
    * @return View
    **/
    public function getCheckin($toolUserId = null, $backto = null)
    {
        // Check if the tool exists
        if (is_null($tool_user = DB::table('tools_users')->find($toolUserId))) {
            // Redirect to the tool management page with error
            return Redirect::to('admin/tools')->with('error', Lang::get('admin/tools/message.not_found'));
        }

		$tool = Tool::find($tool_user->tool_id);
        return View::make('backend/tools/checkin', compact('tool'))->with('backto',$backto);
    }


    /**
    * Check in the item so that it can be checked out again to someone else
    *
    * @param  int  $toolId
    * @return View
    **/
    public function postCheckin($toolUserId = null, $backto = null)
    {
        // Check if the tool exists
        if (is_null($tool_user = DB::table('tools_users')->find($toolUserId))) {
            // Redirect to the tool management page with error
            return Redirect::to('admin/tools')->with('error', Lang::get('admin/tools/message.not_found'));
        }


		$tool = Tool::find($tool_user->tool_id);
        $logaction = new Actionlog();
        $logaction->checkedout_to = $tool_user->assigned_to;
        $return_to = $tool_user->assigned_to;
        $admin_user = Sentry::getUser();


        // Was the tool updated?
        if(DB::table('tools_users')->where('id', '=', $tool_user->id)->delete()) {

            $logaction->tool_id = $tool->id;
            $logaction->location_id = NULL;
            $logaction->asset_type = 'tool';
            $logaction->user_id = $admin_user->id;
            $logaction->note = e(Input::get('note'));

            $settings = Setting::getSettings();

			if ($settings->slack_endpoint) {


				$slack_settings = [
				    'username' => $settings->botname,
				    'channel' => $settings->slack_channel,
				    'link_names' => true
				];

				$client = new \Maknz\Slack\Client($settings->slack_endpoint,$slack_settings);

				try {
						$client->attach([
						    'color' => 'good',
						    'fields' => [
						        [
						            'title' => 'Checked In:',
						            'value' => strtoupper($logaction->asset_type).' <'.Config::get('app.url').'/admin/tools/'.$tool->id.'/view'.'|'.$tool->name.'> checked in by <'.Config::get('app.url').'/admin/users/'.$admin_user->id.'/view'.'|'.$admin_user->fullName().'>.'
						        ],
						        [
						            'title' => 'Note:',
						            'value' => e($logaction->note)
						        ],

						    ]
						])->send('Tool Checked In');

					} catch (Exception $e) {

					}

			}


            $log = $logaction->logaction('checkin from');
            
            if(!is_null($tool_user->assigned_to)) {
                $user = User::find($tool_user->assigned_to);
            }

            $data['log_id'] = $logaction->id;
            $data['first_name'] = $user->first_name;
            $data['item_name'] = $tool->name;
            $data['checkin_date'] = $logaction->created_at;
            $data['item_tag'] = '';
            $data['note'] = $logaction->note;

            if (($tool->checkin_email()=='1')) {

                Mail::send('emails.checkin-asset', $data, function ($m) use ($user) {
                    $m->to($user->email, $user->first_name . ' ' . $user->last_name);
                    $m->subject('Confirm Tool Checkin');
                });
            }

            if ($backto=='user') {
				return Redirect::to("admin/users/".$return_to.'/view')->with('success', Lang::get('admin/tools/message.checkin.success'));
			} else {
				return Redirect::to("admin/tools/".$tool->id."/view")->with('success', Lang::get('admin/tools/message.checkin.success'));
			}
        }

        // Redirect to the tool management page with error
        return Redirect::to("admin/tools")->with('error', Lang::get('admin/tools/message.checkin.error'));
    }

    public function getDatatable()
    {
        $tools = Tool::select(array('id','name','qty'))
        ->whereNull('deleted_at')
        ->orderBy('created_at', 'DESC');

        $tools = $tools->get();

        $actions = new \Chumper\Datatable\Columns\FunctionColumn('actions',function($tools)
            {
                return '<a href="'.route('checkout/tool', $tools->id).'" style="margin-right:5px;" class="btn btn-info btn-sm" '.(($tools->numRemaining() > 0 ) ? '' : ' disabled').'>'.Lang::get('general.checkout').'</a><a href="'.route('update/tool', $tools->id).'" class="btn btn-warning btn-sm" style="margin-right:5px;"><i class="fa fa-pencil icon-white"></i></a><a data-html="false" class="btn delete-asset btn-danger btn-sm" data-toggle="modal" href="'.route('delete/tool', $tools->id).'" data-content="'.Lang::get('admin/tools/message.delete.confirm').'" data-title="'.Lang::get('general.delete').' '.htmlspecialchars($tools->name).'?" onClick="return false;"><i class="fa fa-trash icon-white"></i></a>';
            });

        return Datatable::collection($tools)
        ->addColumn('name',function($tools)
            {
                return link_to('admin/tools/'.$tools->id.'/view', $tools->name);
            })
        ->addColumn('qty',function($tools)
            {
                return $tools->qty;
            })
        ->addColumn('numRemaining',function($tools)
            {
                return $tools->numRemaining();
            })
        ->addColumn($actions)
        ->searchColumns('name','qty','numRemaining','actions')
        ->orderColumns('name','qty','numRemaining','actions')
        ->make();
    }

	public function getDataView($toolID)
	{
		$tool = Tool::find($toolID);
        $tool_users = $tool->users;

		$actions = new \Chumper\Datatable\Columns\FunctionColumn('actions',function($tool_users){
			return '<a href="'.route('checkin/tool', $tool_users->pivot->id).'" class="btn-flat info">Checkin</a>';
		});

		return Datatable::collection($tool_users)
		->addColumn('name',function($tool_users)
			{
				return link_to('/admin/users/'.$tool_users->id.'/view', $tool_users->fullName());
			})
		->addColumn($actions)
		->make();
    }

}
