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
use FixtureCopy;
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
use Log;

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
     * Returns a view .
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
        $form_input = Input::all();

        // create a new model instance
        $fixture = new Fixture();

        if ($fixture->validate($form_input)) {
			// attempt to validate form input, in order of appearance on form

            // Save the fixture data
            $fixture->name					= e(Input::get( 'name' ));
            $fixture->serial				= e(Input::get( 'serial' ));
            $fixture->copies				= e(Input::get( 'copies' ));
			$fixture->needs_maintenance		= e(Input::get( 'needs_maintenance' )) == ''	? 0 : e(Input::get( 'needs_maintenance' ));
			switch ( e(Input::get( 'maintenance_interval' )) ) {
				// maintenance interval needs to be converted from dropdown item # to value
				case 0:
					// 1 month
					$fixture->maintenance_interval	= 1;
					break;
				case 1:
					// 3 month
					$fixture->maintenance_interval	= 3;
					break;
				case 2:
					// 6 month
					$fixture->maintenance_interval	= 6;
					break;
				case 3:
					// 9 month
					$fixture->maintenance_interval	= 9;
					break;
				case 4:
					// 12 month
					$fixture->maintenance_interval	= 12;
					break;
			}
			// Purchased
			$fixture->supplier_id			= e(Input::get( 'supplier_id' )) == '' ? NULL : e(Input::get( 'supplier_id' ));
            $fixture->order_number			= e(Input::get( 'order_number' ));
            $fixture->purchase_date			= e(Input::get( 'purchase_date' ));
			$fixture->purchase_date			= (! (($fixture->purchase_date == "") || ($fixture->purchase_date == "0000-00-00")) ? $fixture->purchase_date : NULL );
			$fixture->purchase_cost			= e(Input::get( 'purchase_cost' )) == '' ? 0 : ParseFloat(e(Input::get( 'purchase_cost' )));
			$fixture->purchase_cost			= (! (($fixture->purchase_cost == "") || ($fixture->purchase_cost == "0.00")) ? $fixture->purchase_cost : NULL  );
            $fixture->purchase_order		= e(Input::get( 'purchase_order' ));
            $fixture->expiration_date		= e(Input::get( 'expiration_date' ));
			$fixture->expiration_date		= (! (($fixture->expiration_date == "") || ($fixture->expiration_date == "0000-00-00")) ? $fixture->expiration_date : NULL );
            $fixture->depreciation_id		= e(Input::get( 'depreciation_id' ));
            $fixture->notes					= e(Input::get( 'notes' ));
			// Built in-house
            $fixture->designer_name			= e(Input::get( 'designer_name'));
            $fixture->designer_email		= e(Input::get( 'designer_email'));
            $fixture->build_date			= e(Input::get( 'build_date' ));
			$fixture->build_date			= (! (($fixture->build_date == "") || ($fixture->build_date == "0000-00-00")) ? $fixture->build_date : NULL );
			$fixture->build_cost			= e(Input::get( 'build_cost' )) == '' ? 0 : ParseFloat(e(Input::get( 'build_cost' )));
			$fixture->build_cost			= (! (($fixture->build_cost == "") || ($fixture->build_cost == "0.00")) ? $fixture->build_cost : NULL  );
			$fixture->job_number_built_on	= e(Input::get( 'job_number' ));
            $fixture->user_id				= Sentry::getId();

            // Was the fixture created?
            if($fixture->save()) {
				// save the fixture copy data if the fixture was saved
                $insertedId = $fixture->id;
			
                for ($x=0; $x<$fixture->copies; $x++) {
					// instantiate and save a new copy
                    $fixturecopy = new FixtureCopy();
                    $fixturecopy->fixture_id       = $insertedId;
                    $fixturecopy->user_id          = Sentry::getId();
                    $fixturecopy->assigned_to      = NULL;
                    $fixturecopy->notes            = NULL;
					// needs maintenance set to false by default
                    $fixturecopy->save();
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
            // Redirect to the blogs management page  - what?
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
			// Redirect to index with error message
			return Redirect::to('admin/fixtures')->with('error', Lang::get('admin/fixtures/message.does_not_exist'));
		}
		
		// get the POST data
		$form_input = Input::all();
		
		if ($fixture->validate($form_input)) {
			// attempt to validate form input, in order of appearance on form

            // Save the fixture data
            $fixture->name					= e(Input::get( 'name' ));
            $fixture->serial				= e(Input::get( 'serial' ));
            $fixture->copies				= e(Input::get( 'copies' ));
			$fixture->needs_maintenance		= e(Input::get( 'needs_maintenance' )) == ''	? 0 : e(Input::get( 'needs_maintenance' ));
			switch ( e(Input::get( 'maintenance_interval' )) ) {
				// maintenance interval needs to be converted from dropdown item # to value
				case 0:
					// 1 month
					$fixture->maintenance_interval	= 1;
					break;
				case 1:
					// 3 month
					$fixture->maintenance_interval	= 3;
					break;
				case 2:
					// 6 month
					$fixture->maintenance_interval	= 6;
					break;
				case 3:
					// 9 month
					$fixture->maintenance_interval	= 9;
					break;
				case 4:
					// 12 month
					$fixture->maintenance_interval	= 12;
					break;
			}
			// Purchased
			$fixture->supplier_id			= e(Input::get( 'supplier_id' )) == '' ? NULL : e(Input::get( 'supplier_id' ));
            $fixture->order_number			= e(Input::get( 'order_number' ));
            $fixture->purchase_date			= e(Input::get( 'purchase_date' ));
			$fixture->purchase_date			= (! (($fixture->purchase_date == "") || ($fixture->purchase_date == "0000-00-00")) ? $fixture->purchase_date : NULL );
			$fixture->purchase_cost			= e(Input::get( 'purchase_cost' )) == '' ? 0 : ParseFloat(e(Input::get( 'purchase_cost' )));
			$fixture->purchase_cost			= (! (($fixture->purchase_cost == "") || ($fixture->purchase_cost == "0.00")) ? $fixture->purchase_cost : NULL  );
            $fixture->purchase_order		= e(Input::get( 'purchase_order' ));
            $fixture->expiration_date		= e(Input::get( 'expiration_date' ));
			$fixture->expiration_date		= (! (($fixture->expiration_date == "") || ($fixture->expiration_date == "0000-00-00")) ? $fixture->expiration_date : NULL );
            $fixture->depreciation_id		= e(Input::get( 'depreciation_id' ));
            $fixture->notes					= e(Input::get( 'notes' ));
			// Built in-house
            $fixture->designer_name			= e(Input::get( 'designer_name'));
            $fixture->designer_email		= e(Input::get( 'designer_email'));
            $fixture->build_date			= e(Input::get( 'build_date' ));
			$fixture->build_date			= (! (($fixture->build_date == "") || ($fixture->build_date == "0000-00-00")) ? $fixture->build_date : NULL );
			$fixture->build_cost			= e(Input::get( 'build_cost' )) == '' ? 0 : ParseFloat(e(Input::get( 'build_cost' )));
			$fixture->build_cost			= (! (($fixture->build_cost == "") || ($fixture->build_cost == "0.00")) ? $fixture->build_cost : NULL  );
			$fixture->job_number_built_on	= e(Input::get( 'job_number' ));
            $fixture->user_id				= Sentry::getId();

			//Are we changing the total number of copies?
			if( $difference = e(Input::get('copies')) - $fixture->fixturecopies()->count() ) {

				if( $difference < 0 ) {
					//remove the difference quantity of fixtures

					//Filter out any fixture which have a user attached;
					$copies = $fixture->fixturecopies->filter(function ($copy) {
						return is_null($copy->user);
					});

					//If the remaining collection is as large or larger than the number of copies we want to delete
					// note: why abs() here but not above?
					if($copies->count() >= abs($difference)) {
						// delete our copies then log the action
						for ($i=1; $i <= abs($difference); $i++) {
							$copies->pop()->delete();
							$logaction = new Actionlog();
							$logaction->asset_id = $fixture->id;
							$logaction->asset_type = 'fixture';
							$logaction->user_id = Sentry::getUser()->id;
							$logaction->note = abs($difference)." copies";
							$logaction->checkedout_to =  NULL;
							$log = $logaction->logaction('delete copies');	
						}
					} else {
						// Redirect to the fixture edit page
						return Redirect::to("admin/fixtures/$fixtureId/edit")->with('error', Lang::get('admin/fixtures/message.assoc_users'));
					}
				} else {
					for ($i=1; $i <= $difference; $i++) {
						//Create a copy for this fixture
						$fixture_copy = new FixtureCopy();
						$fixture_copy->fixture_id       = $fixture->id;
						$fixture_copy->user_id          = Sentry::getId();
						$fixture_copy->assigned_to      = NULL;
						$fixture_copy->notes            = NULL;
						$fixture_copy->save();
					}

					//Log the addition of fixture to the log.
					$logaction = new Actionlog();
					$logaction->asset_id = $fixture->id;
					$logaction->asset_type = 'fixture';
					$logaction->user_id = Sentry::getUser()->id;
					$logaction->note = abs($difference)." copies";
					$log = $logaction->logaction('add copies');
				}
				$fixture->copies = e(Input::get('copies'));
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
            // Delete the fixture and the associated fixture copies
            DB::table('fixture_copies')
            ->where('id', $fixture->id)
            ->update(array('assigned_to' => NULL,'asset_id' => NULL));

            $fixture->fixturecopies->each( function($copy){ 
				// delete each copy
				 $copy->delete();
			});
            $fixture->delete();

            // Redirect to the fixtures management page
            return Redirect::to('admin/fixtures')->with('success', Lang::get('admin/fixtures/message.delete.success'));
        }
    }

    /**
    * Check out the asset to a person
    **/
    public function getCheckout($copyId)
    {
        // Check if the asset exists
        if (is_null($fixturecopy = FixtureCopy::find($copyId))) {
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

		return View::make('backend/fixtures/checkout', compact('fixturecopy'))->with('users_list',$users_list)->with('asset_list',$asset_element);
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
        if (is_null($fixturecopy = FixtureCopy::find($seatId))) {
            // Redirect to the asset management page with error
            return Redirect::to('admin/fixtures')->with('error', Lang::get('admin/fixtures/message.not_found'));
        }

		if (Input::get('asset_id') == '') {
            $fixturecopy->asset_id = NULL;
        } else {
            $fixturecopy->asset_id = e(Input::get('asset_id'));
        }

        // Update the asset data
        if ( e(Input::get('assigned_to')) == '') {
                $fixturecopy->assigned_to =  NULL;

        } else {
                $fixturecopy->assigned_to = e(Input::get('assigned_to'));
        }

        // Was the asset updated?
        if($fixturecopy->save()) {
            $logaction = new Actionlog();

            //$logaction->location_id = $assigned_to->location_id;
            $logaction->asset_type = 'software';
            $logaction->user_id = Sentry::getUser()->id;
            $logaction->note = e(Input::get('note'));
            $logaction->asset_id = $fixturecopy->fixture_id;


			$fixture = Fixture::find($fixturecopy->fixture_id);
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
        if (is_null($fixturecopy = FixtureCopy::find($seatId))) {
            // Redirect to the asset management page with error
            return Redirect::to('admin/fixtures')->with('error', Lang::get('admin/fixtures/message.not_found'));
        }
        return View::make('backend/fixtures/checkin', compact('fixturecopy'))->with('backto',$backto);

    }

    /**
    * Check in the item so that it can be checked out again to someone else
    **/
    public function postCheckin($seatId = null, $backto = null)
    {
        // Check if the asset exists
        if (is_null($fixturecopy = FixtureCopy::find($seatId))) {
            // Redirect to the asset management page with error
            return Redirect::to('admin/fixtures')->with('error', Lang::get('admin/fixtures/message.not_found'));
        }

        $fixture = Fixture::find($fixturecopy->fixture_id);

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
		$return_to = $fixturecopy->assigned_to;
        $logaction = new Actionlog();
        $logaction->checkedout_to = $fixturecopy->assigned_to;

        // Update the asset data
        $fixturecopy->assigned_to                   = NULL;
        $fixturecopy->asset_id                      = NULL;

        $user = Sentry::getUser();

        // Was the asset updated?
        if($fixturecopy->save()) {
            $logaction->asset_id = $fixturecopy->fixture_id;
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
				return Redirect::to("admin/fixtures/".$fixturecopy->fixture_id."/view")->with('success', Lang::get('admin/fixtures/message.checkin.success'));
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

						//Log the deletion of copies to the log
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
        ->addColumn('totalCopies', function($fixtures) {
            return $fixtures->totalCopiesByFixtureID();
        })
        ->addColumn('remaining', function($fixtures) {
            return $fixtures->remaincount();
        })
        ->addColumn('notes', function($fixtures) {
            return $fixtures->notes;
        })
        ->addColumn($actions)
        ->searchColumns('name','serial','totalCopies','remaining','actions','notes')
        ->orderColumns('name','serial','totalCopies','remaining','actions','notes')
        ->make();
    }

    public function getFreeFixture($fixtureId) {
        // Check if the asset exists
        if (is_null($fixture = Fixture::find($fixtureId))) {
            // Redirect to the asset management page with error
            return Redirect::to('admin/fixtures')->with('error', Lang::get('admin/fixtures/message.not_found'));
        }
        $copyId = $fixture->freeCopy($fixtureId);
        return Redirect::to('admin/fixtures/'.$copyId.'/checkout');
    }
}
?>
