<?php namespace Controllers\Admin;

use AdminController;
use Input;
use Lang;
use Asset;
use Supplier;
use AssetMaintenance;
use Statuslabel;
use User;
use Setting;
use Redirect;
use DB;
use Actionlog;
use Model;
use Depreciation;
use Sentry;
use Str;
use Validator;
use View;
use Response;
use Config;
use Location;
use Log;
use DNS1D;
use DNS2D;
use Mail;
use Datatable;
use TCPDF;
use Slack;
use Manufacturer; //for embedded-create

class AssetsController extends AdminController
{
    protected $qrCodeDimensions = array( 'height' => 3.5, 'width' => 3.5);
    protected $barCodeDimensions = array( 'height' => 2, 'width' => 22);

    /**
     * Show a list of all the assets.
     *
     * @return View
     */

    public function getIndex()
    {
        return View::make('backend/assets/index');
    }

    /**
     * Asset create.
     *
     * @param null $model_id
     *
     * @return View
     */
    public function getCreate($model_id = null)
    {
        // Grab the dropdown lists
        $model_list = modelList();
        $statuslabel_list = statusLabelList();
        $location_list = locationsList();
        $manufacturer_list = manufacturerList();
        $category_list = categoryList();
        $supplier_list = suppliersList();
        $assigned_to = usersList();
        $statuslabel_types = statusTypeList();

        $view = View::make('backend/asset/edit');
        $view->with('supplier_list',$supplier_list);
        $view->with('model_list',$model_list);
        $view->with('statuslabel_list',$statuslabel_list);
        $view->with('assigned_to',$assigned_to);
        $view->with('location_list',$location_list);
        $view->with('asset',new Asset);
        $view->with('manufacturer',$manufacturer_list);
        $view->with('category',$category_list);
        $view->with('statuslabel_types',$statuslabel_types);

        if (!is_null($model_id)) {
            $selected_model = Model::find($model_id);
            $view->with('selected_model',$selected_model);
        }

        return $view;
    }

    /**
     * Asset create form processing.
     *
     * @return Redirect
     */
    public function postCreate()
    {
        // create a new model instance
        $asset = new Asset();

        //attempt to validate
        $validator = Validator::make(Input::all(), $asset->validationRules());

        if ($validator->fails())
        {
            // The given data did not pass validation
            return Redirect::back()->withInput()->withErrors($validator->messages());
        }
        // attempt validation
        else {

            if ( e(Input::get('status_id')) == '') {
                $asset->status_id =  NULL;
            } else {
                $asset->status_id = e(Input::get('status_id'));
            }

            if (e(Input::get('warranty_months')) == '') {
                $asset->warranty_months =  NULL;
            } else {
                $asset->warranty_months        = e(Input::get('warranty_months'));
            }

            if (e(Input::get('purchase_cost')) == '') {
                $asset->purchase_cost =  NULL;
            } else {
                $asset->purchase_cost = ParseFloat(e(Input::get('purchase_cost')));
            }

            if (e(Input::get('purchase_date')) == '') {
                $asset->purchase_date =  NULL;
            } else {
                $asset->purchase_date        = e(Input::get('purchase_date'));
            }

            if (e(Input::get('assigned_to')) == '') {
                $asset->assigned_to =  NULL;
            } else {
                $asset->assigned_to        = e(Input::get('assigned_to'));
            }

            if (e(Input::get('supplier_id')) == '') {
                $asset->supplier_id =  0;
            } else {
                $asset->supplier_id        = e(Input::get('supplier_id'));
            }

            if (e(Input::get('requestable')) == '') {
                $asset->requestable =  0;
            } else {
                $asset->requestable        = e(Input::get('requestable'));
            }

            if (e(Input::get('rtd_location_id')) == '') {
                $asset->rtd_location_id = NULL;
            } else {
                $asset->rtd_location_id     = e(Input::get('rtd_location_id'));
            }

            $checkModel = Config::get('app.url').'/api/models/'.e(Input::get('model_id')).'/check';
            $asset->mac_address = ($checkModel == true) ? e(Input::get('mac_address')) : NULL;

            // Save the asset data
            $asset->name            		= e(Input::get('name'));
            $asset->serial            		= e(Input::get('serial'));
            $asset->model_id           		= e(Input::get('model_id'));
            $asset->order_number            = e(Input::get('order_number'));
            $asset->notes            		= e(Input::get('notes'));
            $asset->asset_tag            	= e(Input::get('asset_tag'));
            $asset->user_id          		= Sentry::getId();
            $asset->archived          			= '0';
            $asset->physical            		= '1';
            $asset->depreciate          		= '0';

            // Was the asset created?
            if($asset->save()) {

            	if (Input::get('assigned_to')!='') {
					$logaction = new Actionlog();
					$logaction->asset_id = $asset->id;
					$logaction->checkedout_to = $asset->assigned_to;
					$logaction->asset_type = 'asset';
					$logaction->user_id = Sentry::getUser()->id;
					$logaction->note = e(Input::get('note'));
					$log = $logaction->logaction('checkout');
				}

                // Redirect to the asset listing page
                return Redirect::to("asset")->with('success', Lang::get('admin/asset/message.create.success'));
            }
        }

        // Redirect to the asset create page with an error
        return Redirect::to('assets/create')->with('error', Lang::get('admin/asset/message.create.error'));


    }

    /**
     * Asset update.
     *
     * @param  int  $assetId
     * @return View
     */
    public function getEdit($assetId = null)
    {
        // Check if the asset exists
        if (is_null($asset = Asset::find($assetId))) {
            // Redirect to the asset management page
            return Redirect::to('asset')->with('error', Lang::get('admin/asset/message.does_not_exist'));
        }

        // Grab the dropdown lists
        $model_list = modelList();
        $statuslabel_list = statusLabelList();
        $location_list = locationsList();
        $manufacturer_list = manufacturerList();
        $category_list = categoryList();
        $supplier_list = suppliersList();
        $assigned_to = usersList();
        $statuslabel_types = statusTypeList();

        return View::make('backend/asset/edit', compact('asset'))
        ->with('model_list',$model_list)
        ->with('supplier_list',$supplier_list)
        ->with('location_list',$location_list)
        ->with('statuslabel_list',$statuslabel_list)
        ->with('assigned_to',$assigned_to)
        ->with('manufacturer',$manufacturer_list)
        ->with('statuslabel_types',$statuslabel_types)
        ->with('category',$category_list);
    }


    /**
     * Asset update form processing page.
     *
     * @param  int  $assetId
     * @return Redirect
     */
    public function postEdit($assetId = null)
    {
        // Check if the asset exists
        if (is_null($asset = Asset::find($assetId))) {
            // Redirect to the asset management page with error
            return Redirect::to('asset')->with('error', Lang::get('admin/asset/message.does_not_exist'));
        }

        //attempt to validate
        $validator = Validator::make(Input::all(), $asset->validationRules($assetId));

        if ($validator->fails())
        {
            // The given data did not pass validation
            return Redirect::back()->withInput()->withErrors($validator->messages());
        }
        // attempt validation
        else {


            if ( e(Input::get('status_id')) == '' ) {
                $asset->status_id =  NULL;
            } else {
                $asset->status_id = e(Input::get('status_id'));
            }

            if (e(Input::get('warranty_months')) == '') {
                $asset->warranty_months =  NULL;
            } else {
                $asset->warranty_months        = e(Input::get('warranty_months'));
            }

            if (e(Input::get('purchase_cost')) == '') {
                $asset->purchase_cost =  NULL;
            } else {
                $asset->purchase_cost = ParseFloat(e(Input::get('purchase_cost')));
            }

            if (e(Input::get('purchase_date')) == '') {
                $asset->purchase_date =  NULL;
            } else {
                $asset->purchase_date        = e(Input::get('purchase_date'));
            }

            if (e(Input::get('supplier_id')) == '') {
                $asset->supplier_id =  NULL;
            } else {
                $asset->supplier_id        = e(Input::get('supplier_id'));
            }

            if (e(Input::get('requestable')) == '') {
                $asset->requestable =  0;
            } else {
                $asset->requestable        = e(Input::get('requestable'));
            }

            if (e(Input::get('rtd_location_id')) == '') {
                $asset->rtd_location_id = 0;
            } else {
                $asset->rtd_location_id     = e(Input::get('rtd_location_id'));
            }

            $checkModel = Config::get('app.url').'/api/models/'.e(Input::get('model_id')).'/check';
            $asset->mac_address = ($checkModel == true) ? e(Input::get('mac_address')) : NULL;

            // Update the asset data
            $asset->name            		= e(Input::get('name'));
            $asset->serial            		= e(Input::get('serial'));
            $asset->model_id           		= e(Input::get('model_id'));
            $asset->order_number            = e(Input::get('order_number'));
            $asset->asset_tag           	= e(Input::get('asset_tag'));
            $asset->notes            		= e(Input::get('notes'));
            $asset->physical            	= '1';

            // Was the asset updated?
            if($asset->save()) {
                // Redirect to the new asset page
                return Redirect::to("asset/$assetId/view")->with('success', Lang::get('admin/asset/message.update.success'));
            }
            else
            {
                 return Redirect::to('asset')->with('error', Lang::get('admin/asset/message.does_not_exist'));
             }
        }


        // Redirect to the asset management page with error
        return Redirect::to("asset/$assetId/edit")->with('error', Lang::get('admin/asset/message.update.error'));

    }

    /**
     * Delete the given asset.
     *
     * @param  int  $assetId
     * @return Redirect
     */
    public function getDelete($assetId)
    {
        // Check if the asset exists
        if (is_null($asset = Asset::find($assetId))) {
            // Redirect to the asset management page with error
            return Redirect::to('asset')->with('error', Lang::get('admin/asset/message.not_found'));
        }

        if (isset($asset->assigneduser->id) && ($asset->assigneduser->id!=0)) {
            // Redirect to the asset management page
            return Redirect::to('asset')->with('error', Lang::get('admin/asset/message.assoc_users'));
        } else {
            // Delete the asset

            DB::table('assets')
            ->where('id', $asset->id)
            ->update(array('assigned_to' => NULL));


            $asset->delete();

            // Redirect to the asset management page
            return Redirect::to('asset')->with('success', Lang::get('admin/asset/message.delete.success'));
        }



    }

    /**
    * Check out the asset to a person
    **/
    public function getCheckout($assetId)
    {
        // Check if the asset exists
        if (is_null($asset = Asset::find($assetId))) {
            // Redirect to the asset management page with error
            return Redirect::to('asset')->with('error', Lang::get('admin/asset/message.not_found'));
        }

        // Get the dropdown of users and then pass it to the checkout view
        $users_list = usersList();

        return View::make('backend/asset/checkout', compact('asset'))->with('users_list',$users_list);

    }

    /**
    * Check out the asset to a person
    **/
    public function postCheckout($assetId)
    {

        // Check if the asset exists
        if (!$asset = Asset::find($assetId)) {
            return Redirect::to('asset')->with('error', Lang::get('admin/asset/message.not_found'));
        }

        // Declare the rules for the form validation
        $rules = array(
            'assigned_to'   => 'required|min:1',
            'checkout_at'   => 'required|date',
            'note'   => 'alpha_space',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        if ($validator->fails()) {
            return Redirect::back()->withInput()->withErrors($validator);
        }

        if (!$user = User::find(e(Input::get('assigned_to')))) {
            return Redirect::to('asset')->with('error', Lang::get('admin/asset/message.user_does_not_exist'));
        }

        if (!$admin = Sentry::getUser()) {
            return Redirect::to('asset')->with('error', Lang::get('admin/asset/message.admin_user_does_not_exist'));
        }


    	if (Input::get('checkout_at')!= date("Y-m-d")){
			$checkout_at = e(Input::get('checkout_at')).' 00:00:00';
    	} else {
            $checkout_at = date("Y-m-d h:i:s");
        }

        if (Input::has('expected_checkin')) {
        	if (Input::get('expected_checkin')!= date("Y-m-d")){
				$expected_checkin = e(Input::get('expected_checkin')).' 00:00:00';
			}
    	} else {
            $expected_checkin = null;
        }


        if ($asset->checkOutToUser($user, $admin, $checkout_at, $expected_checkin, e(Input::get('note')), e(Input::get('name')))) {
            // Redirect to the new asset page
            return Redirect::to("asset")->with('success', Lang::get('admin/asset/message.checkout.success'));
        }

        // Redirect to the asset management page with error
        return Redirect::to("asset/$assetId/checkout")->with('error', Lang::get('admin/asset/message.checkout.error'));
    }


    /**
    * Check the asset back into inventory
    *
    * @param  int  $assetId
    * @return View
    **/
    public function getCheckin($assetId, $backto = null)
    {
        // Check if the asset exists
        if (is_null($asset = Asset::find($assetId))) {
            // Redirect to the asset management page with error
            return Redirect::to('asset')->with('error', Lang::get('admin/asset/message.not_found'));
        }

        return View::make('backend/asset/checkin', compact('asset'))->with('backto', $backto);
    }


    /**
    * Check in the item so that it can be checked out again to someone else
    *
    * @param  int  $assetId
    * @return View
    **/
    public function postCheckin($assetId = null, $backto = null)
    {
        // Check if the asset exists
        if (is_null($asset = Asset::find($assetId))) {
            // Redirect to the asset management page with error
            return Redirect::to('asset')->with('error', Lang::get('admin/asset/message.not_found'));
        }

        if (!is_null($asset->assigned_to)) {
            $user = User::find($asset->assigned_to);
        }

        // This is just used for the redirect
        $return_to = $asset->assigned_to;

        $logaction = new Actionlog();
        $logaction->checkedout_to = $asset->assigned_to;

        // Update the asset data to null, since it's being checked in
        $asset->assigned_to            		= NULL;
        $asset->accepted                  = NULL;


        // Was the asset updated?
        if($asset->save()) {

        	 if (Input::has('checkin_at')) {

        	 	if (!strtotime(Input::get('checkin_at'))) {
					$logaction->created_at = date("Y-m-d h:i:s");
        	 	} elseif (Input::get('checkin_at')!= date("Y-m-d")) {
					$logaction->created_at = e(Input::get('checkin_at')).' 00:00:00';
				}
        	}

            $logaction->asset_id = $asset->id;
            $logaction->location_id = NULL;
            $logaction->asset_type = 'asset';
            $logaction->note = e(Input::get('note'));
            $logaction->user_id = Sentry::getUser()->id;
            $log = $logaction->logaction('checkin from');

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
						            'value' => strtoupper($logaction->asset_type).' asset <'.Config::get('app.url').'/asset/'.$asset->id.'/view'.'|'.$asset->showAssetName().'> checked in by <'.Config::get('app.url').'/asset/'.$asset->id.'/view'.'|'.Sentry::getUser()->fullName().'>.'
						        ],
						        [
						            'title' => 'Note:',
						            'value' => e($logaction->note)
						        ],

						    ]
						])->send('Asset Checked In');

					} catch (Exception $e) {

					}

			}

			$data['log_id'] = $logaction->id;
            		$data['first_name'] = $user->first_name;
            		$data['item_name'] = $asset->showAssetName();
            		$data['checkin_date'] = $logaction->created_at;
            		$data['item_tag'] = $asset->asset_tag;
            		$data['note'] = $logaction->note;

            		if ((($asset->checkin_email()=='1')) && (!Config::get('app.lock_passwords'))) {
                		Mail::send('emails.checkin-asset', $data, function ($m) use ($user) {
                    			$m->to($user->email, $user->first_name . ' ' . $user->last_name);
                    			$m->subject('Confirm Asset Checkin');
                		});
            		}

			if ($backto=='user') {
				return Redirect::to("admin/users/".$return_to.'/view')->with('success', Lang::get('admin/asset/message.checkin.success'));
			} else {
				return Redirect::to("asset")->with('success', Lang::get('admin/asset/message.checkin.success'));
			}

        }

        // Redirect to the asset management page with error
        return Redirect::to("asset")->with('error', Lang::get('admin/asset/message.checkin.error'));
    }


    /**
    *  Get the asset information to present to the asset view page
    *
    * @param  int  $assetId
    * @return View
    **/
    public function getView($assetId = null)
    {
        $asset = Asset::withTrashed()->find($assetId);

        if (isset($asset->id)) {

            $settings = Setting::getSettings();

            $qr_code = (object) array(
                'display' => $settings->qr_code == '1',
                'url' => route('qr_code/asset', $asset->id)
            );

            return View::make('backend/asset/view', compact('asset', 'qr_code'));
        } else {
            // Prepare the error message
            $error = Lang::get('admin/asset/message.does_not_exist', compact('id'));

            // Redirect to the user management page
            return Redirect::route('asset')->with('error', $error);
        }

    }

    /**
    *  Get the QR code representing the asset
    *
    * @param  int  $assetId
    * @return View
    **/
    public function getQrCode($assetId = null)
    {
        $settings = Setting::getSettings();

        if ($settings->qr_code == '1') {
            $asset = Asset::find($assetId);

            if (isset($asset->id,$asset->asset_tag)) {

                if ($settings->barcode_type == 'C128'){
		$content = DNS1D::getBarcodePNG(route('view/asset', $asset->id), $settings->barcode_type,
                    $this->barCodeDimensions['height'],$this->barCodeDimensions['width']);
		}
		else{
                $content = DNS2D::getBarcodePNG(route('view/asset', $asset->id), $settings->barcode_type,
                    $this->qrCodeDimensions['height'],$this->qrCodeDimensions['width']);
		}
                $img = imagecreatefromstring(base64_decode($content));
                imagepng($img);
                imagedestroy($img);

                $content_disposition = sprintf('attachment;filename=qr_code_%s.png', preg_replace('/\W/', '', $asset->asset_tag));
                $response = Response::make($content, 200);
                $response->header('Content-Type', 'image/png');
                $response->header('Content-Disposition', $content_disposition);
                return $response;
            }
        }

        $response = Response::make('', 404);
        return $response;
    }

    /**
     * Asset clone.
     *
     * @param  int  $assetId
     * @return View
     */
    public function getClone($assetId = null)
    {
        // Check if the asset exists
        if (is_null($asset_to_clone = Asset::find($assetId))) {
            // Redirect to the asset management page
            return Redirect::to('asset')->with('error', Lang::get('admin/asset/message.does_not_exist'));
        }

        // Grab the dropdown lists
        $model_list = modelList();
        $statuslabel_list = statusLabelList();
        $location_list = locationsList();
        $manufacturer_list = manufacturerList();
        $category_list = categoryList();
        $supplier_list = suppliersList();
        $assigned_to = usersList();
        $statuslabel_types = statusTypeList();

        $asset = clone $asset_to_clone;
        $asset->id = null;
        $asset->asset_tag = '';
        $asset->serial = '';
        $asset->assigned_to = '';
        $asset->mac_address = '';
        return View::make('backend/asset/edit')
        ->with('supplier_list',$supplier_list)
        ->with('model_list',$model_list)
        ->with('statuslabel_list',$statuslabel_list)
        ->with('statuslabel_types',$statuslabel_types)
        ->with('assigned_to',$assigned_to)
        ->with('asset',$asset)
        ->with('location_list',$location_list)
        ->with('manufacturer',$manufacturer_list)
        ->with('category',$category_list);

    }


    public function getRestore($assetId = null)
    {

		// Get user information
		$asset = Asset::withTrashed()->find($assetId);

		 if (isset($asset->id)) {

			// Restore the user
			$asset->restore();

			// Prepare the success message
			$success = Lang::get('admin/asset/message.restore.success');

			// Redirect to the user management page
			return Redirect::route('asset')->with('success', $success);

		 } else {
			 return Redirect::to('asset')->with('error', Lang::get('admin/asset/message.not_found'));
		 }

    }


       /**
    *  Upload the file to the server
    *
    * @param  int  $assetId
    * @return View
    **/
    public function postUpload($assetID = null)
    {
        $asset = Asset::find($assetID);

		// the asset is valid
		$destinationPath = app_path().'/private_uploads';

        if (isset($asset->id)) {

        	if (Input::hasFile('assetfile')) {

				foreach(Input::file('assetfile') as $file) {

				$rules = array(
				   'assetfile' => 'required|mimes:png,gif,jpg,jpeg,doc,docx,pdf,txt,zip,rar|max:2000'
				);
				$validator = Validator::make(array('assetfile'=> $file), $rules);

					if($validator->passes()){

						$extension = $file->getClientOriginalExtension();
						$filename = 'asset-'.$asset->id.'-'.str_random(8);
						$filename .= '-'.Str::slug($file->getClientOriginalName()).'.'.$extension;
						$upload_success = $file->move($destinationPath, $filename);

						//Log the deletion of seats to the log
						$logaction = new Actionlog();
						$logaction->asset_id = $asset->id;
						$logaction->asset_type = 'asset';
						$logaction->user_id = Sentry::getUser()->id;
						$logaction->note = e(Input::get('notes'));
						$logaction->checkedout_to =  NULL;
						$logaction->created_at =  date("Y-m-d h:i:s");
						$logaction->filename =  $filename;
						$log = $logaction->logaction('uploaded');
					} else {
						 return Redirect::back()->with('error', Lang::get('admin/asset/message.upload.invalidfiles'));
					}


				}

				if ($upload_success) {
				  	return Redirect::back()->with('success', Lang::get('admin/asset/message.upload.success'));
				} else {
				   return Redirect::back()->with('success', Lang::get('admin/asset/message.upload.error'));
				}

			} else {
				 return Redirect::back()->with('error', Lang::get('admin/asset/message.upload.nofiles'));
			}





        } else {
            // Prepare the error message
            $error = Lang::get('admin/asset/message.does_not_exist', compact('id'));

            // Redirect to the asset management page
            return Redirect::route('asset')->with('error', $error);
        }
    }


    /**
    *  Delete the associated file
    *
    * @param  int  $assetId
    * @return View
    **/
    public function getDeleteFile($assetID = null, $fileId = null)
    {
        $asset = Asset::find($assetID);
        $destinationPath = app_path().'/private_uploads';

		// the asset is valid
        if (isset($asset->id)) {

			$log = Actionlog::find($fileId);
			$full_filename = $destinationPath.'/'.$log->filename;
			if (file_exists($full_filename)) {
				unlink($destinationPath.'/'.$log->filename);
			}
			$log->delete();
			return Redirect::back()->with('success', Lang::get('admin/asset/message.deletefile.success'));

        } else {
            // Prepare the error message
            $error = Lang::get('admin/asset/message.does_not_exist', compact('id'));

            // Redirect to the asset management page
            return Redirect::route('asset')->with('error', $error);
        }
    }



    /**
    *  Display/download the uploaded file
    *
    * @param  int  $assetId
    * @return View
    **/
    public function displayFile($assetID = null, $fileId = null)
    {

        $asset = Asset::find($assetID);

		// the asset is valid
        if (isset($asset->id)) {
				$log = Actionlog::find($fileId);
				$file = $log->get_src();
				return Response::download($file);
        } else {
            // Prepare the error message
            $error = Lang::get('admin/asset/message.does_not_exist', compact('id'));

            // Redirect to the asset management page
            return Redirect::route('asset')->with('error', $error);
        }
    }




    /**
    *  Display bulk edit screen
    *
    * @return View
    **/
    public function postBulkEdit($assets = null)
    {

	    if (!Input::has('edit_asset')) {
			return Redirect::back()->with('error', 'No assets selected');
		} else {
			$asset_raw_array = Input::get('edit_asset');
			foreach ($asset_raw_array as $asset_id => $value) {
				$asset_ids[] = $asset_id;

			}

		}

	    if (Input::has('bulk_actions')) {


		    // Create labels
		    if (Input::get('bulk_actions')=='labels') {
			    $assets = Asset::find($asset_ids);
			    $assetcount = count($assets);
			    $count = 0;

			    $settings = Setting::getSettings();
			    return View::make('backend/asset/labels')->with('assets',$assets)->with('settings',$settings)->with('count',$count);


			 // Bulk edit
			} elseif (Input::get('bulk_actions')=='edit') {

				$assets = Input::get('edit_asset');

				$supplier_list = array('' => '') + Supplier::orderBy('name', 'asc')->lists('name', 'id');
                $statuslabel_list = array('' => '') + Statuslabel::lists('name', 'id');
                $location_list = array('' => '') + Location::lists('name', 'id');

                return View::make('backend/asset/bulk')->with('assets',$assets)->with('supplier_list',$supplier_list)->with('statuslabel_list',$statuslabel_list)->with('location_list',$location_list);


			}

		} else {
			return Redirect::back()->with('error', 'No action selected');
		}



    }



    /**
    *  Save bulk edits
    *
    * @return View
    **/
    public function postBulkSave($assets = null)
    {

		if (Input::has('bulk_edit')) {

			$assets = Input::get('bulk_edit');

			if ( (Input::has('purchase_date')) ||  (Input::has('purchase_cost'))  ||  (Input::has('supplier_id')) ||  (Input::has('order_number')) || (Input::has('warranty_months')) || (Input::has('rtd_location_id'))  || (Input::has('requestable')) ||  (Input::has('status_id')) )  {

				foreach ($assets as $key => $value) {

					$update_array = array();

					if (Input::has('purchase_date')) {
						$update_array['purchase_date'] =  e(Input::get('purchase_date'));
					}

					if (Input::has('purchase_cost')) {
						$update_array['purchase_cost'] =  e(Input::get('purchase_cost'));
					}

					if (Input::has('supplier_id')) {
						$update_array['supplier_id'] =  e(Input::get('supplier_id'));
					}

					if (Input::has('order_number')) {
						$update_array['order_number'] =  e(Input::get('order_number'));
					}

					if (Input::has('warranty_months')) {
						$update_array['warranty_months'] =  e(Input::get('warranty_months'));
					}

					if (Input::has('rtd_location_id')) {
						$update_array['rtd_location_id'] = e(Input::get('rtd_location_id'));
					}

					if (Input::has('status_id')) {
						$update_array['status_id'] = e(Input::get('status_id'));
					}

                    if (Input::get('requestable')=='1') {
						$update_array['requestable'] =  1;
					} else {
                        $update_array['requestable'] =  0;
                    }


					if (DB::table('assets')
		            ->where('id', $key)
		            ->update($update_array)) {

			            $logaction = new Actionlog();
			            $logaction->asset_id = $key;
			            $logaction->asset_type = 'asset';
			            $logaction->created_at =  date("Y-m-d h:i:s");

			            if (Input::has('rtd_location_id')) {
			            	$logaction->location_id = e(Input::get('rtd_location_id'));
			            }
			            $logaction->user_id = Sentry::getUser()->id;
			            $log = $logaction->logaction('update');

		            }

				} // endforeach

				return Redirect::to("asset")->with('success', Lang::get('admin/asset/message.update.success'));

			// no values given, nothing to update
			} else {
				return Redirect::to("asset")->with('info',Lang::get('admin/asset/message.update.nothing_updated'));

			}


		} // endif

		return Redirect::to("asset");

    }


    public function getDatatable($status = null)
    {

       $assets = Asset::with('model','assigneduser','assigneduser.userloc','assetstatus','defaultLoc','assetlog','model','model.category')->Hardware()->select(array('id', 'name','model_id','assigned_to','asset_tag','serial','status_id','purchase_date','deleted_at','rtd_location_id','notes','order_number','mac_address'));


      switch ($status) {
      case 'Deleted':
        $assets->withTrashed()->Deleted();
        break;
      case 'Pending':
      	$assets->Pending();
      	break;
      case 'RTD':
      	$assets->RTD();
      	break;
      case 'Undeployable':
      	$assets->Undeployable();
      	break;
      case 'Archived':
      	$assets->Archived();
      	break;
      case 'Requestable':
      	$assets->RequestableAssets();
      	break;
      case 'Deployed':
      	$assets->Deployed();
      	break;

      }

      if (Input::has('order_number')) {
          $assets->where('order_number','=',e(Input::get('order_number')));
      }

      $assets = $assets->orderBy('asset_tag', 'ASC')->get();


      $actions = new \Chumper\Datatable\Columns\FunctionColumn('actions', function ($assets)
      	{
        	if ($assets->deleted_at=='') {
        		return '<div style=" white-space: nowrap;"><a href="'.route('clone/asset', $assets->id).'" class="btn btn-info btn-sm" title="Clone asset"><i class="fa fa-files-o"></i></a> <a href="'.route('update/asset', $assets->id).'" class="btn btn-warning btn-sm"><i class="fa fa-pencil icon-white"></i></a> <a data-html="false" class="btn delete-asset btn-danger btn-sm" data-toggle="modal" href="'.route('delete/asset', $assets->id).'" data-content="'.Lang::get('admin/asset/message.delete.confirm').'" data-title="'.Lang::get('general.delete').' '.htmlspecialchars($assets->asset_tag).'?" onClick="return false;"><i class="fa fa-trash icon-white"></i></a></div>';
        	} elseif ($assets->deleted_at!='') {
        		return '<a href="'.route('restore/asset', $assets->id).'" class="btn btn-warning btn-sm"><i class="fa fa-recycle icon-white"></i></a>';
        	}

        });

	   $inout = new \Chumper\Datatable\Columns\FunctionColumn('inout', function ($assets)
      	{

            if ($assets->assetstatus) {

                if ($assets->assetstatus->deployable != 0) {
                    if (($assets->assigned_to !='') && ($assets->assigned_to > 0)) {
                        return '<a href="'.route('checkin/asset', $assets->id).'" class="btn btn-primary btn-sm">'.Lang::get('general.checkin').'</a>';
                    } else {
                        return '<a href="'.route('checkout/asset', $assets->id).'" class="btn btn-info btn-sm">'.Lang::get('general.checkout').'</a>';
                    }
                }
            }
        });



        return Datatable::collection($assets)
        ->addColumn('',function($assets)
            {
                return '<div class="text-center"><input type="checkbox" name="edit_asset['.$assets->id.']" class="one_required"></div>';
            })
        ->addColumn('name',function($assets)
	        {
		        return '<a title="'.$assets->name.'" href="asset/'.$assets->id.'/view">'.$assets->name.'</a>';
	        })
	    ->addColumn('asset_tag',function($assets)
	        {
		        return '<a title="'.$assets->asset_tag.'" href="asset/'.$assets->id.'/view">'.$assets->asset_tag.'</a>';
	        })

      ->showColumns('serial')

		->addColumn('model',function($assets)
			{
				if ($assets->model) {
			    	return $assets->model->name;
			    } else {
				    return 'No model';
				}
			})

      ->addColumn('status',function($assets)
        {
          	if ($assets->assigned_to!='') {
            	return link_to(Config::get('app.url').'/admin/users/'.$assets->assigned_to.'/view', $assets->assigneduser->fullName());
            } else {
                if ($assets->assetstatus) {
                    return $assets->assetstatus->name;
                }

            }

	        })
		->addColumn('location',function($assets)
            {
                if ($assets->assigned_to && ($assets->assigneduser->userloc!='')) {
                    return link_to('admin/settings/locations/'.$assets->assigneduser->userloc->id.'/edit', $assets->assigneduser->userloc->name);
                } elseif ($assets->defaultLoc){
                    return link_to('admin/settings/locations/'.$assets->defaultLoc->id.'/edit', $assets->defaultLoc->name);
                }
            })
		->addColumn('category',function($assets)
			{
				if (isset($assets->model->category)) {
			    	return $assets->model->category->name;
			    } else {
				    return 'No category';
				}

      })

      ->addColumn('eol',function($assets)
      {
        return $assets->eol_date();
      })

      ->addColumn('notes',function($assets)
      {
        return $assets->notes;
      })
      ->addColumn('mac_address',function($assets)
      {
        return $assets->mac_address;
      })

      ->addColumn('order_number',function($assets)
      {
        return '<a href="../asset/?order_number='.$assets->order_number.'">'.$assets->order_number.'';
      })


      ->addColumn('checkout_date',function($assets)
        {
            if (($assets->assigned_to!='') && ($assets->assetlog->first())) {
            	return $assets->assetlog->first()->created_at->format('Y-m-d');
            }

        })
      ->addColumn($inout)
      ->addColumn($actions)
      ->searchColumns('name', 'asset_tag', 'serial', 'model', 'status','location','eol','checkout_date', 'inout','category','notes','order_number','mac_address')
      ->orderColumns('name', 'asset_tag', 'serial', 'model', 'status','location','eol','notes','order_number','checkout_date', 'inout','mac_address')
      ->make();

		}
}
