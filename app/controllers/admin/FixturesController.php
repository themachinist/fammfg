<?php namespace Controllers\Admin;

use Assets;
use AdminController;
use Input;
use Lang;
use Fixture;
use Asset;
use User;
use Actionlog;
use DB;
use Redirect;
use FixtureSeat;
use Depreciation;
use Setting;
use Sentry;
use Str;
use Supplier;
use Validator;
use View;
use Response;
use Datatable;
use Slack;
use Config;
use Session;

class FixturesController extends AdminController
{
    /**
     * Show a list of all the fixtures.
     *
     * @return View
     */




    public function getIndex()
    {
        // Show the page
        return View::make('backend/fixtures/index');
    }


    /**
     * Fixture create.
     *
     * @return View
     */
    public function getCreate()
    {
        // Show the page
        $fixture_options = array('0' => 'Top Level') + Fixture::lists('name', 'id');
        // Show the page
        $depreciation_list = array('0' => Lang::get('admin/fixtures/form.no_depreciation')) + Depreciation::lists('name', 'id');
        $supplier_list = array('' => 'Select Supplier') + Supplier::orderBy('name', 'asc')->lists('name', 'id');
        $maintained_list = array('' => 'Maintained', '1' => 'Yes', '0' => 'No');
        return View::make('backend/fixtures/edit')
            ->with('fixture_options',$fixture_options)
            ->with('depreciation_list',$depreciation_list)
            ->with('supplier_list',$supplier_list)
            ->with('maintained_list',$maintained_list)
            ->with('fixture',new Fixture);
    }


    /**
     * Fixture create form processing.
     *
     * @return Redirect
     */
    public function postCreate()
    {


        // get the POST data
        $new = Input::all();

        // create a new model instance
        $fixture = new Fixture();

        // attempt validation
        if ($fixture->validate($new)) {

            if ( e(Input::get('purchase_cost')) == '') {
                    $fixture->purchase_cost =  NULL;
            } else {
                    $fixture->purchase_cost = ParseFloat(e(Input::get('purchase_cost')));
                    //$fixture->purchase_cost = e(Input::get('purchase_cost'));
            }

            if ( e(Input::get('supplier_id')) == '') {
                $fixture->supplier_id = NULL;
            } else {
                $fixture->supplier_id = e(Input::get('supplier_id'));
            }

            if ( e(Input::get('maintained')) == '') {
                $fixture->maintained = 0;
            } else {
                $fixture->maintained = e(Input::get('maintained'));
            }

            if ( e(Input::get('reassignable')) == '') {
                $fixture->reassignable = 0;
            } else {
                $fixture->reassignable = e(Input::get('reassignable'));
            }

            if ( e(Input::get('purchase_order')) == '') {
                $fixture->purchase_order = '';
            } else {
                $fixture->purchase_order = e(Input::get('purchase_order'));
            }

            // Save the fixture data
            $fixture->name              = e(Input::get('name'));
            $fixture->serial            = e(Input::get('serial'));
            $fixture->fixture_email     = e(Input::get('fixture_email'));
            $fixture->fixture_name      = e(Input::get('fixture_name'));
            $fixture->notes             = e(Input::get('notes'));
            $fixture->order_number      = e(Input::get('order_number'));
            $fixture->seats             = e(Input::get('seats'));
            $fixture->purchase_date     = e(Input::get('purchase_date'));
            $fixture->purchase_order    = e(Input::get('purchase_order'));
            $fixture->depreciation_id   = e(Input::get('depreciation_id'));
            $fixture->expiration_date   = e(Input::get('expiration_date'));
            $fixture->user_id           = Sentry::getId();

            if (($fixture->purchase_date == "") || ($fixture->purchase_date == "0000-00-00")) {
                $fixture->purchase_date = NULL;
            }

            if (($fixture->expiration_date == "") || ($fixture->expiration_date == "0000-00-00")) {
                $fixture->expiration_date = NULL;
            }

            if (($fixture->purchase_cost == "") || ($fixture->purchase_cost == "0.00")) {
                $fixture->purchase_cost = NULL;
            }

            // Was the fixture created?
            if($fixture->save()) {

                $insertedId = $fixture->id;
                // Save the fixture seat data
                for ($x=0; $x<$fixture->seats; $x++) {
                    $fixture_seat = new FixtureSeat();
                    $fixture_seat->fixture_id       = $insertedId;
                    $fixture_seat->user_id          = Sentry::getId();
                    $fixture_seat->assigned_to      = NULL;
                    $fixture_seat->notes            = NULL;
                    $fixture_seat->save();
                }


                // Redirect to the new fixture page
                return Redirect::to("admin/fixtures")->with('success', Lang::get('admin/fixtures/message.create.success'));
            }
        } else {
            // failure
            $errors = $fixture->errors();
            return Redirect::back()->withInput()->withErrors($errors);
        }

        // Redirect to the fixture create page
        return Redirect::to('admin/fixtures/edit')->with('error', Lang::get('admin/fixtures/message.create.error'))->with('fixture',new Fixture);

    }

    /**
     * Fixture update.
     *
     * @param  int  $fixtureId
     * @return View
     */
    public function getEdit($fixtureId = null)
    {
        // Check if the fixture exists
        if (is_null($fixture = Fixture::find($fixtureId))) {
            // Redirect to the blogs management page
            return Redirect::to('admin/fixtures')->with('error', Lang::get('admin/fixtures/message.does_not_exist'));
        }

            if ($fixture->purchase_date == "0000-00-00") {
                $fixture->purchase_date = NULL;
            }

            if ($fixture->purchase_cost == "0.00") {
                $fixture->purchase_cost = NULL;
            }

        // Show the page
        $fixture_options = array('' => 'Top Level') + DB::table('assets')->where('id', '!=', $fixtureId)->lists('name', 'id');
        $depreciation_list = array('0' => Lang::get('admin/fixtures/form.no_depreciation')) + Depreciation::lists('name', 'id');
        $supplier_list = array('' => 'Select Supplier') + Supplier::orderBy('name', 'asc')->lists('name', 'id');
        $maintained_list = array('' => 'Maintained', '1' => 'Yes', '0' => 'No');
        return View::make('backend/fixtures/edit', compact('fixture'))
            ->with('fixture_options',$fixture_options)
            ->with('depreciation_list',$depreciation_list)
            ->with('supplier_list',$supplier_list)
            ->with('maintained_list',$maintained_list);
    }


    /**
     * Fixture update form processing page.
     *
     * @param  int  $fixtureId
     * @return Redirect
     */
    public function postEdit($fixtureId = null)
    {
        // Check if the fixture exists
        if (is_null($fixture = Fixture::find($fixtureId))) {
            // Redirect to the blogs management page
            return Redirect::to('admin/fixtures')->with('error', Lang::get('admin/fixtures/message.does_not_exist'));
        }


        // get the POST data
        $new = Input::all();



        // attempt validation
        if ($fixture->validate($new)) {

            // Update the fixture data
            $fixture->name              = e(Input::get('name'));
            $fixture->serial            = e(Input::get('serial'));
            $fixture->fixture_email     = e(Input::get('fixture_email'));
            $fixture->fixture_name      = e(Input::get('fixture_name'));
            $fixture->notes             = e(Input::get('notes'));
            $fixture->order_number      = e(Input::get('order_number'));
            $fixture->depreciation_id   = e(Input::get('depreciation_id'));
            $fixture->purchase_order    = e(Input::get('purchase_order'));
            $fixture->maintained        = e(Input::get('maintained'));
            $fixture->reassignable      = e(Input::get('reassignable'));

            if ( e(Input::get('supplier_id')) == '') {
                $fixture->supplier_id = NULL;
            } else {
                $fixture->supplier_id = e(Input::get('supplier_id'));
            }

            // Update the asset data
            if ( e(Input::get('purchase_date')) == '') {
                    $fixture->purchase_date =  NULL;
            } else {
                    $fixture->purchase_date = e(Input::get('purchase_date'));
            }

            if ( e(Input::get('expiration_date')) == '') {
                $fixture->expiration_date = NULL;
            } else {
                $fixture->expiration_date = e(Input::get('expiration_date'));
            }

            // Update the asset data
            if ( e(Input::get('termination_date')) == '') {
                $fixture->termination_date =  NULL;
            } else {
                $fixture->termination_date = e(Input::get('termination_date'));
            }

            if ( e(Input::get('purchase_cost')) == '') {
                    $fixture->purchase_cost =  NULL;
            } else {
                    $fixture->purchase_cost = ParseFloat(e(Input::get('purchase_cost')));
                    //$fixture->purchase_cost = e(Input::get('purchase_cost'));
            }

            if ( e(Input::get('maintained')) == '') {
                $fixture->maintained = 0;
            } else {
                $fixture->maintained = e(Input::get('maintained'));
            }

            if ( e(Input::get('reassignable')) == '') {
                $fixture->reassignable = 0;
            } else {
                $fixture->reassignable = e(Input::get('reassignable'));
            }

            if ( e(Input::get('purchase_order')) == '') {
                $fixture->purchase_order = '';
            } else {
                $fixture->purchase_order = e(Input::get('purchase_order'));
            }


            //Are we changing the total number of seats?
            if( $fixture->seats != e(Input::get('seats'))) {
                //Determine how many seats we are dealing with
                $difference = e(Input::get('seats')) - $fixture->fixtureseats()->count();

                if( $difference < 0 ) {
                    //Filter out any fixture which have a user attached;
                    $seats = $fixture->fixtureseats->filter(function ($seat) {
                        return is_null($seat->user);
                    });


                    //If the remaining collection is as large or larger than the number of seats we want to delete
                    if($seats->count() >= abs($difference)) {
                        for ($i=1; $i <= abs($difference); $i++) {
                            //Delete the appropriate number of seats
                            $seats->pop()->delete();
                        }

                        //Log the deletion of seats to the log
                        $logaction = new Actionlog();
                        $logaction->asset_id = $fixture->id;
                        $logaction->asset_type = 'software';
                        $logaction->user_id = Sentry::getUser()->id;
                        $logaction->note = abs($difference)." seats";
                        $logaction->checkedout_to =  NULL;
                        $log = $logaction->logaction('delete seats');

                    } else {
                        // Redirect to the fixture edit page
                        return Redirect::to("admin/fixtures/$fixtureId/edit")->with('error', Lang::get('admin/fixtures/message.assoc_users'));
                    }
                } else {

                    for ($i=1; $i <= $difference; $i++) {
                        //Create a seat for this fixture
                        $fixture_seat = new FixtureSeat();
                        $fixture_seat->fixture_id       = $fixture->id;
                        $fixture_seat->user_id          = Sentry::getId();
                        $fixture_seat->assigned_to      = NULL;
                        $fixture_seat->notes            = NULL;
                        $fixture_seat->save();
                    }

                    //Log the addition of fixture to the log.
                    $logaction = new Actionlog();
                    $logaction->asset_id = $fixture->id;
                    $logaction->asset_type = 'software';
                    $logaction->user_id = Sentry::getUser()->id;
                    $logaction->note = abs($difference)." seats";
                    $log = $logaction->logaction('add seats');
                }
                $fixture->seats             = e(Input::get('seats'));
            }

            // Was the asset created?
            if($fixture->save()) {
                // Redirect to the new fixture page
                return Redirect::to("admin/fixtures/$fixtureId/view")->with('success', Lang::get('admin/fixtures/message.update.success'));
            }
        } else {
            // failure
            $errors = $fixture->errors();
            return Redirect::back()->withInput()->withErrors($errors);
        }

        // Redirect to the fixture edit page
        return Redirect::to("admin/fixtures/$fixtureId/edit")->with('error', Lang::get('admin/fixtures/message.update.error'));

    }

    /**
     * Delete the given fixture.
     *
     * @param  int  $fixtureId
     * @return Redirect
     */
    public function getDelete($fixtureId)
    {
        // Check if the fixture exists
        if (is_null($fixture = Fixture::find($fixtureId))) {
            // Redirect to the fixture management page
            return Redirect::to('admin/fixtures')->with('error', Lang::get('admin/fixtures/message.not_found'));
        }

        if (($fixture->assignedcount()) && ($fixture->assignedcount() > 0)) {

            // Redirect to the fixture management page
            return Redirect::to('admin/fixtures')->with('error', Lang::get('admin/fixtures/message.assoc_users'));

        } else {

            // Delete the fixture and the associated fixture seats
            DB::table('fixture_seats')
            ->where('id', $fixture->id)
            ->update(array('assigned_to' => NULL,'asset_id' => NULL));

            $fixtureseats = $fixture->fixtureseats();
            $fixtureseats->delete();
            $fixture->delete();




            // Redirect to the fixtures management page
            return Redirect::to('admin/fixtures')->with('success', Lang::get('admin/fixtures/message.delete.success'));
        }


    }


    /**
    * Check out the asset to a person
    **/
    public function getCheckout($seatId)
    {
        // Check if the asset exists
        if (is_null($fixtureseat = FixtureSeat::find($seatId))) {
            // Redirect to the asset management page with error
            return Redirect::to('admin/fixtures')->with('error', Lang::get('admin/fixtures/message.not_found'));
        }

        // Get the dropdown of users and then pass it to the checkout view
         $users_list = array('' => 'Select a User') + DB::table('users')->select(DB::raw('concat(last_name,", ",first_name," (",username,")") as full_name, id'))->whereNull('deleted_at')->orderBy('last_name', 'asc')->orderBy('first_name', 'asc')->lists('full_name', 'id');


        // Left join to get a list of assets and some other helpful info
        $asset = DB::table('assets')
            ->leftJoin('users', 'users.id', '=', 'assets.assigned_to')
            ->leftJoin('models', 'assets.model_id', '=', 'models.id')
            ->select('assets.id', 'assets.name', 'first_name', 'last_name','asset_tag',
            DB::raw('concat(first_name," ",last_name) as full_name, assets.id as id, models.name as modelname'))
            ->whereNull('assets.deleted_at')
            ->get();

            $asset_array = json_decode(json_encode($asset), true);
            $asset_element[''] = 'Please select an asset';

            // Build a list out of the data results
            for ($x=0; $x<count($asset_array); $x++) {

                if ($asset_array[$x]['full_name']!='') {
                    $full_name = ' ('.$asset_array[$x]['full_name'].') '.$asset_array[$x]['modelname'];
                } else {
                    $full_name = ' (Unassigned) '.$asset_array[$x]['modelname'];
                }
                $asset_element[$asset_array[$x]['id']] = $asset_array[$x]['asset_tag'].' - '.$asset_array[$x]['name'].$full_name;

            }

        return View::make('backend/fixtures/checkout', compact('fixtureseat'))->with('users_list',$users_list)->with('asset_list',$asset_element);

    }



    /**
    * Check out the asset to a person
    **/
    public function postCheckout($seatId)
    {


        $assigned_to = e(Input::get('assigned_to'));
        $asset_id = e(Input::get('asset_id'));
        $user = Sentry::getUser();

        // Declare the rules for the form validation
        $rules = array(

            'note'   => 'alpha_space',
            'asset_id'  => 'required_without:assigned_to',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails()) {
            // Ooops.. something went wrong
            return Redirect::back()->withInput()->withErrors($validator);
        }

        if ($assigned_to!='') {
        // Check if the user exists
            if (is_null($is_assigned_to = User::find($assigned_to))) {
                // Redirect to the asset management page with error
                return Redirect::to('admin/fixtures')->with('error', Lang::get('admin/fixtures/message.user_does_not_exist'));
            }
        }

        if ($asset_id!='') {

            if (is_null($is_asset_id = Asset::find($asset_id))) {
                // Redirect to the asset management page with error
                return Redirect::to('admin/fixtures')->with('error', Lang::get('admin/fixtures/message.asset_does_not_exist'));
            }

            if (($is_asset_id->assigned_to!=$assigned_to) && ($assigned_to!=''))  {
                //echo 'asset assigned to: '.$is_asset_id->assigned_to.'<br>fixture assigned to: '.$assigned_to;
                return Redirect::to('admin/fixtures')->with('error', Lang::get('admin/fixtures/message.owner_doesnt_match_asset'));
            }

        }



		// Check if the asset exists
        if (is_null($fixtureseat = FixtureSeat::find($seatId))) {
            // Redirect to the asset management page with error
            return Redirect::to('admin/fixtures')->with('error', Lang::get('admin/fixtures/message.not_found'));
        }

		if (Input::get('asset_id') == '') {
            $fixtureseat->asset_id = NULL;
        } else {
            $fixtureseat->asset_id = e(Input::get('asset_id'));
        }

        // Update the asset data
        if ( e(Input::get('assigned_to')) == '') {
                $fixtureseat->assigned_to =  NULL;

        } else {
                $fixtureseat->assigned_to = e(Input::get('assigned_to'));
        }

        // Was the asset updated?
        if($fixtureseat->save()) {

            $logaction = new Actionlog();

            //$logaction->location_id = $assigned_to->location_id;
            $logaction->asset_type = 'software';
            $logaction->user_id = Sentry::getUser()->id;
            $logaction->note = e(Input::get('note'));
            $logaction->asset_id = $fixtureseat->fixture_id;


			$fixture = Fixture::find($fixtureseat->fixture_id);
            $settings = Setting::getSettings();


            // Update the asset data
            if ( e(Input::get('assigned_to')) == '') {
                $logaction->checkedout_to = NULL;
                $slack_msg = strtoupper($logaction->asset_type).' fixture <'.Config::get('app.url').'/admin/fixtures/'.$fixture->id.'/view'.'|'.$fixture->name.'> checked out to <'.Config::get('app.url').'/hardware/'.$is_asset_id->id.'/view|'.$is_asset_id->showAssetName().'> by <'.Config::get('app.url').'/admin/users/'.$user->id.'/view'.'|'.$user->fullName().'>.';
            } else {
                $logaction->checkedout_to = e(Input::get('assigned_to'));
                $slack_msg = strtoupper($logaction->asset_type).' fixture <'.Config::get('app.url').'/admin/fixtures/'.$fixture->id.'/view'.'|'.$fixture->name.'> checked out to <'.Config::get('app.url').'/admin/users/'.$is_assigned_to->id.'/view|'.$is_assigned_to->fullName().'> by <'.Config::get('app.url').'/admin/users/'.$user->id.'/view'.'|'.$user->fullName().'>.';
            }



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
						            'value' => $slack_msg
						        ],
						        [
						            'title' => 'Note:',
						            'value' => e($logaction->note)
						        ],



						    ]
						])->send('Fixture Checked Out');

					} catch (Exception $e) {

					}

			}

            $log = $logaction->logaction('checkout');


            // Redirect to the new asset page
            return Redirect::to("admin/fixtures")->with('success', Lang::get('admin/fixtures/message.checkout.success'));
        }

        // Redirect to the asset management page with error
        return Redirect::to('admin/fixtures/$assetId/checkout')->with('error', Lang::get('admin/fixtures/message.create.error'))->with('fixture',new Fixture);
    }


    /**
    * Check the fixture back into inventory
    **/
    public function getCheckin($seatId = null, $backto = null)
    {
        // Check if the asset exists
        if (is_null($fixtureseat = FixtureSeat::find($seatId))) {
            // Redirect to the asset management page with error
            return Redirect::to('admin/fixtures')->with('error', Lang::get('admin/fixtures/message.not_found'));
        }
        return View::make('backend/fixtures/checkin', compact('fixtureseat'))->with('backto',$backto);

    }



    /**
    * Check in the item so that it can be checked out again to someone else
    **/
    public function postCheckin($seatId = null, $backto = null)
    {
        // Check if the asset exists
        if (is_null($fixtureseat = FixtureSeat::find($seatId))) {
            // Redirect to the asset management page with error
            return Redirect::to('admin/fixtures')->with('error', Lang::get('admin/fixtures/message.not_found'));
        }

        $fixture = Fixture::find($fixtureseat->fixture_id);

        if(!$fixture->reassignable) {
            // Not allowed to checkin
            Session::flash('error', 'Fixture not reassignable.');
            return Redirect::back()->withInput();
        }

        // Declare the rules for the form validation
        $rules = array(
            'note'   => 'alpha_space',
            'notes'   => 'alpha_space',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails()) {
            // Ooops.. something went wrong
            return Redirect::back()->withInput()->withErrors($validator);
        }
		$return_to = $fixtureseat->assigned_to;
        $logaction = new Actionlog();
        $logaction->checkedout_to = $fixtureseat->assigned_to;

        // Update the asset data
        $fixtureseat->assigned_to                   = NULL;
        $fixtureseat->asset_id                      = NULL;

        $user = Sentry::getUser();

        // Was the asset updated?
        if($fixtureseat->save()) {
            $logaction->asset_id = $fixtureseat->fixture_id;
            $logaction->location_id = NULL;
            $logaction->asset_type = 'software';
            $logaction->note = e(Input::get('note'));
            $logaction->user_id = $user->id;

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
						            'value' => strtoupper($logaction->asset_type).' <'.Config::get('app.url').'/admin/fixtures/'.$fixture->id.'/view'.'|'.$fixture->name.'> checked in by <'.Config::get('app.url').'/admin/users/'.$user->id.'/view'.'|'.$user->fullName().'>.'
						        ],
						        [
						            'title' => 'Note:',
						            'value' => e($logaction->note)
						        ],

						    ]
						])->send('Fixture Checked In');

					} catch (Exception $e) {

					}

			}


            $log = $logaction->logaction('checkin from');



			if ($backto=='user') {
				return Redirect::to("admin/users/".$return_to.'/view')->with('success', Lang::get('admin/fixtures/message.checkin.success'));
			} else {
				return Redirect::to("admin/fixtures/".$fixtureseat->fixture_id."/view")->with('success', Lang::get('admin/fixtures/message.checkin.success'));
			}

        }

        // Redirect to the fixture page with error
        return Redirect::to("admin/fixtures")->with('error', Lang::get('admin/fixtures/message.checkin.error'));
    }

    /**
    *  Get the asset information to present to the asset view page
    *
    * @param  int  $assetId
    * @return View
    **/
    public function getView($fixtureId = null)
    {
        $fixture = Fixture::find($fixtureId);

        if (isset($fixture->id)) {
                return View::make('backend/fixtures/view', compact('fixture'));
        } else {
            // Prepare the error message
            $error = Lang::get('admin/fixtures/message.does_not_exist', compact('id'));

            // Redirect to the user management page
            return Redirect::route('fixtures')->with('error', $error);
        }
    }

    public function getClone($fixtureId = null)
    {
         // Check if the fixture exists
        if (is_null($fixture_to_clone = Fixture::find($fixtureId))) {
            // Redirect to the blogs management page
            return Redirect::to('admin/fixtures')->with('error', Lang::get('admin/fixtures/message.does_not_exist'));
        }

          // Show the page
        $fixture_options = array('0' => 'Top Level') + Fixture::lists('name', 'id');
		$maintained_list = array('' => 'Maintained', '1' => 'Yes', '0' => 'No');
        //clone the orig
        $fixture = clone $fixture_to_clone;
        $fixture->id = null;
        $fixture->serial = null;

        // Show the page
        $depreciation_list = array('0' => Lang::get('admin/fixtures/form.no_depreciation')) + Depreciation::lists('name', 'id');
        $supplier_list = array('' => 'Select Supplier') + Supplier::orderBy('name', 'asc')->lists('name', 'id');
        return View::make('backend/fixtures/edit')->with('fixture_options',$fixture_options)->with('depreciation_list',$depreciation_list)->with('supplier_list',$supplier_list)->with('fixture',$fixture)->with('maintained_list',$maintained_list);

    }


    /**
    *  Upload the file to the server
    *
    * @param  int  $assetId
    * @return View
    **/
    public function postUpload($fixtureId = null)
    {
        $fixture = Fixture::find($fixtureId);

		// the fixture is valid
		$destinationPath = app_path().'/private_uploads';

        if (isset($fixture->id)) {

        	if (Input::hasFile('fixturefile')) {

				foreach(Input::file('fixturefile') as $file) {

				$rules = array(
				   'fixturefile' => 'required|mimes:png,gif,jpg,jpeg,doc,docx,pdf,txt,zip,rar|max:2000'
				);
				$validator = Validator::make(array('fixturefile'=> $file), $rules);

					if($validator->passes()){

						$extension = $file->getClientOriginalExtension();
						$filename = 'fixture-'.$fixture->id.'-'.str_random(8);
						$filename .= '-'.Str::slug($file->getClientOriginalName()).'.'.$extension;
						$upload_success = $file->move($destinationPath, $filename);

						//Log the deletion of seats to the log
						$logaction = new Actionlog();
						$logaction->asset_id = $fixture->id;
						$logaction->asset_type = 'software';
						$logaction->user_id = Sentry::getUser()->id;
						$logaction->note = e(Input::get('notes'));
						$logaction->checkedout_to =  NULL;
						$logaction->created_at =  date("Y-m-d h:i:s");
						$logaction->filename =  $filename;
						$log = $logaction->logaction('uploaded');
					} else {
						 return Redirect::back()->with('error', Lang::get('admin/fixtures/message.upload.invalidfiles'));
					}


				}

				if ($upload_success) {
				  	return Redirect::back()->with('success', Lang::get('admin/fixtures/message.upload.success'));
				} else {
				   return Redirect::back()->with('success', Lang::get('admin/fixtures/message.upload.error'));
				}

			} else {
				 return Redirect::back()->with('error', Lang::get('admin/fixtures/message.upload.nofiles'));
			}





        } else {
            // Prepare the error message
            $error = Lang::get('admin/fixtures/message.does_not_exist', compact('id'));

            // Redirect to the licence management page
            return Redirect::route('fixtures')->with('error', $error);
        }
    }


    /**
    *  Delete the associated file
    *
    * @param  int  $assetId
    * @return View
    **/
    public function getDeleteFile($fixtureId = null, $fileId = null)
    {
        $fixture = Fixture::find($fixtureId);
        $destinationPath = app_path().'/private_uploads';

		// the fixture is valid
        if (isset($fixture->id)) {

			$log = Actionlog::find($fileId);
			$full_filename = $destinationPath.'/'.$log->filename;
			if (file_exists($full_filename)) {
				unlink($destinationPath.'/'.$log->filename);
			}
			$log->delete();
			return Redirect::back()->with('success', Lang::get('admin/fixtures/message.deletefile.success'));

        } else {
            // Prepare the error message
            $error = Lang::get('admin/fixtures/message.does_not_exist', compact('id'));

            // Redirect to the licence management page
            return Redirect::route('fixtures')->with('error', $error);
        }
    }



    /**
    *  Display/download the uploaded file
    *
    * @param  int  $assetId
    * @return View
    **/
    public function displayFile($fixtureId = null, $fileId = null)
    {

        $fixture = Fixture::find($fixtureId);

		// the fixture is valid
        if (isset($fixture->id)) {
				$log = Actionlog::find($fileId);
				$file = $log->get_src();
				return Response::download($file);
        } else {
            // Prepare the error message
            $error = Lang::get('admin/fixtures/message.does_not_exist', compact('id'));

            // Redirect to the licence management page
            return Redirect::route('fixtures')->with('error', $error);
        }
    }

    public function getDatatable() {
        $fixtures = Fixture::orderBy('created_at', 'DESC')->get();

        $actions = new \Chumper\Datatable\Columns\FunctionColumn('actions', function($fixtures) {
            return '<span style="white-space: nowrap;"><a href="'.route('freecheckout/fixture', $fixtures->id).'" class="btn btn-primary btn-sm" style="margin-right:5px;" '.(($fixtures->remaincount() > 0) ? '' : 'disabled').'>'.Lang::get('general.checkout').'</a> <a href="'.route('clone/fixture', $fixtures->id).'" class="btn btn-info btn-sm" style="margin-right:5px;" title="Clone asset"><i class="fa fa-files-o"></i></a><a href="'.route('update/fixture', $fixtures->id).'" class="btn btn-warning btn-sm" style="margin-right:5px;"><i class="fa fa-pencil icon-white"></i></a><a data-html="false" class="btn delete-asset btn-danger btn-sm" data-toggle="modal" href="'.route('delete/fixture', $fixtures->id).'" data-content="'.Lang::get('admin/fixtures/message.delete.confirm').'" data-title="'.Lang::get('general.delete').' '.htmlspecialchars($fixtures->name).'?" onClick="return false;"><i class="fa fa-trash icon-white"></i></a></span>';
        });

        return Datatable::collection($fixtures)
        ->addColumn('name', function($fixtures) {
            return link_to('/admin/fixtures/'.$fixtures->id.'/view', $fixtures->name);
        })
        ->addColumn('serial', function($fixtures) {
            return link_to('/admin/fixtures/'.$fixtures->id.'/view', mb_strimwidth($fixtures->serial, 0, 50, "..."));
        })
        ->addColumn('totalSeats', function($fixtures) {
            return $fixtures->totalSeatsByFixtureID();
        })
        ->addColumn('remaining', function($fixtures) {
            return $fixtures->remaincount();
        })
        ->addColumn('purchase_date', function($fixtures) {
            return $fixtures->purchase_date;
        })
        ->addColumn('notes', function($fixtures) {
            return $fixtures->notes;
        })
        ->addColumn($actions)
        ->searchColumns('name','serial','totalSeats','remaining','purchase_date','actions','notes')
        ->orderColumns('name','serial','totalSeats','remaining','purchase_date','actions','notes')
        ->make();
    }

    public function getFreeFixture($fixtureId) {
        // Check if the asset exists
        if (is_null($fixture = Fixture::find($fixtureId))) {
            // Redirect to the asset management page with error
            return Redirect::to('admin/fixtures')->with('error', Lang::get('admin/fixtures/message.not_found'));
        }
        $seatId = $fixture->freeSeat($fixtureId);
        return Redirect::to('admin/fixtures/'.$seatId.'/checkout');
    }
}
