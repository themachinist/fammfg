<?php
    /*
    |--------------------------------------------------------------------------
    | Admin API Routes
    |--------------------------------------------------------------------------
    */
    Route::group( [ 'prefix' => 'api', 'namespace' => 'Controllers\Admin', 'before' => 'admin-auth' ], function () {

        /*---Asset API---*/
        Route::group( [ 'prefix' => 'assets' ], function () {

            Route::get( 'list/{status?}', [ 'as' => 'api.asset.list', 'uses' => 'AssetsController@getDatatable' ] );
        } );

        /*---Status Label API---*/
        Route::group( [ 'prefix' => 'statuslabels' ], function () {

            Route::resource( '/', 'StatuslabelsController' );
            Route::get( '{statuslabelId}/deployable', function ( $statuslabelId ) {

                $statuslabel = Statuslabel::find( $statuslabelId );
                if (( $statuslabel->deployable == '1' ) && ( $statuslabel->pending != '1' )
                    && ( $statuslabel->archived != '1' )
                ) {
                    return '1';
                } else {
                    return '0';
                }
            } );
        } );

        /*---Tools API---*/
        Route::group( [ 'prefix' => 'tools' ], function () {

            Route::get( 'list', [ 'as' => 'api.tools.list', 'uses' => 'ToolsController@getDatatable' ] );
            Route::get( '{toolID}/view',
                [ 'as' => 'api.tools.view', 'uses' => 'ToolsController@getDataView' ] );
        } );

        /*---Consumables API---*/
        Route::group( [ 'prefix' => 'consumables' ], function () {

            Route::get( 'list', [ 'as' => 'api.consumables.list', 'uses' => 'ConsumablesController@getDatatable' ] );
            Route::get( '{toolID}/view',
                [ 'as' => 'api.consumables.view', 'uses' => 'ConsumablesController@getDataView' ] );
        } );

        /*---Users API---*/
        Route::group( [ 'prefix' => 'users' ], function () {
            Route::post( '/', [ 'as' => 'api.users.store', 'uses' => 'UsersController@store' ] );
            Route::get( 'list/{status?}', [ 'as' => 'api.users.list', 'uses' => 'UsersController@getDatatable' ] );
        } );

        /*---Fixtures API---*/
        Route::group( [ 'prefix' => 'fixtures' ], function () {

            Route::get( 'list', [ 'as' => 'api.fixtures.list', 'uses' => 'FixturesController@getDatatable' ] );
        } );

        /*---Locations API---*/
        Route::group( [ 'prefix' => 'locations' ], function () {

            Route::resource( '/', 'LocationsController' );
            Route::get( '{locationID}/check', function ( $locationID ) {

                $location = Location::find( $locationID );

                return $location;
            } );
        } );

        /*---Improvements API---*/
        Route::group( [ 'prefix' => 'asset_maintenances' ], function () {

            Route::get( 'list',
                [ 'as' => 'api.asset_maintenances.list', 'uses' => 'AssetMaintenancesController@getDatatable' ] );
        } );

        /*---Models API---*/
        Route::group( [ 'prefix' => 'models' ], function () {

            Route::resource( '/', 'ModelsController' );
            Route::get( 'list/{status?}', [ 'as' => 'api.models.list', 'uses' => 'ModelsController@getDatatable' ] );
            Route::get( '{modelId}/check', function ( $modelId ) {

                $model = Model::find( $modelId );

                return $model->show_mac_address;
            } );

            Route::get( '{modelID}/view', [ 'as' => 'api.models.view', 'uses' => 'ModelsController@getDataView' ] );
        } );

        /*--- Categories API---*/
        Route::group( [ 'prefix' => 'categories' ], function () {

            Route::get( 'list', [ 'as' => 'api.categories.list', 'uses' => 'CategoriesController@getDatatable' ] );
            Route::get( '{categoryID}/view',
                [ 'as' => 'api.categories.view', 'uses' => 'CategoriesController@getDataView' ] );
        } );

        /*-- Suppliers API (mostly for creating new ones in-line while creating an asset) --*/
        Route::group( [ 'prefix' => 'suppliers' ], function () {

            Route::resource( '/', 'SuppliersController' );
        } );
    } );

    /*
    |--------------------------------------------------------------------------
    | Asset Routes
    |--------------------------------------------------------------------------
    |
    | Register all the asset routes.
    |
    */


    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    |
    | Register all the admin routes.
    |
    */

    Route::group( [ 'prefix' => 'admin', 'before' => 'admin-auth', 'namespace' => 'Controllers\Admin' ], function () {

		Route::group( [ 'prefix' => 'assets' ], function () {

            Route::get( '/',							['as' => 'assets',				'uses' => 'AssetsController@getIndex'] );
            Route::get( 'create/{model?}',				['as' => 'create/asset',		'uses' => 'AssetsController@getCreate'] );
            Route::post('create',						['as' => 'savenew/asset',		'uses' => 'AssetsController@postCreate'] );
            Route::get( '{assetId}/edit',				['as' => 'update/asset',		'uses' => 'AssetsController@getEdit'] );
            Route::get( '{assetId}/clone',				['as' => 'clone/asset',			'uses' => 'AssetsController@getClone' ] );
            Route::post('{assetId}/clone',				 'AssetsController@postCreate' );
            Route::get(	'{assetId}/delete',				['as' => 'delete/asset',		'uses' => 'AssetsController@getDelete' ] );
            Route::get(	'{assetId}/checkout',			['as' => 'checkout/asset',		'uses' => 'AssetsController@getCheckout' ] );
            Route::post('{assetId}/checkout',			 'AssetsController@postCheckout' );
            Route::get( '{assetId}/checkin/{backto?}',	['as' => 'checkin/asset',		'uses' => 'AssetsController@getCheckin' ] );
            Route::post('{assetId}/checkin/{backto?}',	 'AssetsController@postCheckin' );
            Route::get( '{assetId}/view',				['as' => 'view/asset',			'uses' => 'AssetsController@getView' ] );
            Route::get( '{assetId}/qr-view',			['as' => 'qr-view/asset',		'uses' => 'AssetsController@getView' ] );
            Route::get( '{assetId}/qr_code',			['as' => 'qr_code/asset',		'uses' => 'AssetsController@getQrCode' ] );
            Route::get( '{assetId}/restore',			['as' => 'restore/asset',		'uses' => 'AssetsController@getRestore' ] );
            Route::post('{assetId}/upload',				['as' => 'upload/asset',		'uses' => 'AssetsController@postUpload' ] );
            Route::get( '{assetId}/deletefile/{fileId}',['as' => 'delete/assetfile',	'uses' => 'AssetsController@getDeleteFile' ] );
            Route::get( '{assetId}/showfile/{fileId}',	['as' => 'show/assetfile',		'uses' => 'AssetsController@displayFile' ] );
            Route::post( '{assetId}/edit',				 'AssetsController@postEdit' );
            Route::post( 'bulkedit',					['as' => 'asset/bulkedit',		'uses' => 'AssetsController@postBulkEdit' ] );
            Route::post( 'bulksave',					['as'   => 'asset/bulksave',	'uses' => 'AssetsController@postBulkSave' ] );

            # Asset Model Management
            Route::group( [ 'prefix' => 'models', 'before' => 'admin-auth' ], function () {
                Route::get( '/',				['as' => 'models',				'uses' => 'ModelsController@getIndex' ] );
                Route::get( 'create',			['as' => 'create/model',		'uses' => 'ModelsController@getCreate' ] );
                Route::post( 'create',			 'ModelsController@postCreate' );
                Route::get( '{modelId}/edit',	['as' => 'update/model',		'uses' => 'ModelsController@getEdit' ] );
                Route::post( '{modelId}/edit',	 'ModelsController@postEdit' );
                Route::get( '{modelId}/clone',	['as' => 'clone/model',			'uses' => 'ModelsController@getClone' ] );
                Route::post( '{modelId}/clone',  'ModelsController@postCreate' );
                Route::get( '{modelId}/delete', [ 'as' => 'delete/model',		'uses' => 'ModelsController@getDelete' ] );
                Route::get( '{modelId}/view',	[ 'as' => 'view/model',			'uses' => 'ModelsController@getView' ] );
                Route::get( '{modelID}/restore',[ 'as' => 'restore/model',		'uses' => 'ModelsController@getRestore' ] );
            } );
        } );

        # Fixtures
        Route::group( [ 'prefix' => 'fixtures' ], function () {

            Route::get( 'create', [ 'as' => 'create/fixtures', 'uses' => 'FixturesController@getCreate' ] );
            Route::post( 'create', 'FixturesController@postCreate' );
            Route::get( '{fixtureId}/edit', [ 'as' => 'update/fixture', 'uses' => 'FixturesController@getEdit' ] );
            Route::post( '{fixtureId}/edit', 'FixturesController@postEdit' );
            Route::get( '{fixtureId}/clone', [ 'as' => 'clone/fixture', 'uses' => 'FixturesController@getClone' ] );
            Route::post( '{fixtureId}/clone', 'FixturesController@postCreate' );
            Route::get( '{fixtureId}/delete', [ 'as' => 'delete/fixture', 'uses' => 'FixturesController@getDelete' ] );
            Route::get( '{fixtureId}/freecheckout',
                [ 'as' => 'freecheckout/fixture', 'uses' => 'FixturesController@getFreeFixture' ] );
            Route::get( '{fixtureId}/checkout',
                [ 'as' => 'checkout/fixture', 'uses' => 'FixturesController@getCheckout' ] );
            Route::post( '{fixtureId}/checkout', 'FixturesController@postCheckout' );
            Route::get( '{fixtureId}/checkin/{backto?}',
                [ 'as' => 'checkin/fixture', 'uses' => 'FixturesController@getCheckin' ] );
            Route::post( '{fixtureId}/checkin/{backto?}', 'FixturesController@postCheckin' );
            Route::get( '{fixtureId}/view', [ 'as' => 'view/fixture', 'uses' => 'FixturesController@getView' ] );
            Route::post( '{fixtureId}/upload',
                [ 'as' => 'upload/fixture', 'uses' => 'FixturesController@postUpload' ] );
            Route::get( '{fixtureId}/deletefile/{fileId}',
                [ 'as' => 'delete/fixturefile', 'uses' => 'FixturesController@getDeleteFile' ] );
            Route::get( '{fixtureId}/showfile/{fileId}',
                [ 'as' => 'show/fixturefile', 'uses' => 'FixturesController@displayFile' ] );
            Route::get( '/', [ 'as' => 'fixtures', 'uses' => 'FixturesController@getIndex' ] );
        } );

        # Asset Maintenances
        Route::group( [ 'prefix' => 'asset_maintenances' ], function () {

            Route::get( 'create/{assetId?}',
                [ 'as' => 'create/asset_maintenances', 'uses' => 'AssetMaintenancesController@getCreate' ] );
            Route::post( 'create/{assetId?}', 'AssetMaintenancesController@postCreate' );
            Route::get( '/', [ 'as' => 'asset_maintenances', 'uses' => 'AssetMaintenancesController@getIndex' ] );
            Route::get( '{assetMaintenanceId}/edit',
                [ 'as' => 'update/asset_maintenance', 'uses' => 'AssetMaintenancesController@getEdit' ] );
            Route::post( '{assetMaintenanceId}/edit', 'AssetMaintenancesController@postEdit' );
            Route::get( '{assetMaintenanceId}/delete',
                [ 'as' => 'delete/asset_maintenance', 'uses' => 'AssetMaintenancesController@getDelete' ] );
            Route::get( '{assetMaintenanceId}/view',
                [ 'as' => 'view/asset_maintenance', 'uses' => 'AssetMaintenancesController@getView' ] );
        } );

        # Tools
        Route::group( [ 'prefix' => 'tools' ], function () {

            Route::get( 'create', [ 'as' => 'create/tool', 'uses' => 'ToolsController@getCreate' ] );
            Route::post( 'create', 'ToolsController@postCreate' );
            Route::get( '{toolID}/edit',
                [ 'as' => 'update/tool', 'uses' => 'ToolsController@getEdit' ] );
            Route::post( '{toolID}/edit', 'ToolsController@postEdit' );
            Route::get( '{toolID}/delete',
                [ 'as' => 'delete/tool', 'uses' => 'ToolsController@getDelete' ] );
            Route::get( '{toolID}/view', [ 'as' => 'view/tool', 'uses' => 'ToolsController@getView' ] );
            Route::get( '{toolID}/checkout',
                [ 'as' => 'checkout/tool', 'uses' => 'ToolsController@getCheckout' ] );
            Route::post( '{toolID}/checkout', 'ToolsController@postCheckout' );
            Route::get( '{toolID}/checkin/{backto?}',
                [ 'as' => 'checkin/tool', 'uses' => 'ToolsController@getCheckin' ] );
            Route::post( '{toolID}/checkin/{backto?}', 'ToolsController@postCheckin' );
            Route::get( '/', [ 'as' => 'tools', 'uses' => 'ToolsController@getIndex' ] );
        } );

        # Consumables
        Route::group( [ 'prefix' => 'consumables' ], function () {

            Route::get( 'create', [ 'as' => 'create/consumable', 'uses' => 'ConsumablesController@getCreate' ] );
            Route::post( 'create', 'ConsumablesController@postCreate' );
            Route::get( '{consumableID}/edit',
                [ 'as' => 'update/consumable', 'uses' => 'ConsumablesController@getEdit' ] );
            Route::post( '{consumableID}/edit', 'ConsumablesController@postEdit' );
            Route::get( '{consumableID}/delete',
                [ 'as' => 'delete/consumable', 'uses' => 'ConsumablesController@getDelete' ] );
            Route::get( '{consumableID}/view',
                [ 'as' => 'view/consumable', 'uses' => 'ConsumablesController@getView' ] );
            Route::get( '{consumableID}/checkout',
                [ 'as' => 'checkout/consumable', 'uses' => 'ConsumablesController@getCheckout' ] );
            Route::post( '{consumableID}/checkout', 'ConsumablesController@postCheckout' );
            Route::get( '/', [ 'as' => 'consumables', 'uses' => 'ConsumablesController@getIndex' ] );
        } );

        # Admin Settings Routes (for categories, maufactureres, etc)
        Route::group( [ 'prefix' => 'settings', 'before' => 'admin-auth' ], function () {

            # Settings
            Route::group( [ 'prefix' => 'app' ], function () {

                Route::get( '/', [ 'as' => 'app', 'uses' => 'SettingsController@getIndex' ] );
                Route::get( 'edit', [ 'as' => 'edit/settings', 'uses' => 'SettingsController@getEdit' ] );
                Route::post( 'edit', 'SettingsController@postEdit' );
            } );

            # Settings
            Route::group( [ 'prefix' => 'backups' ], function () {

                Route::get( '/', [ 'as' => 'settings/backups', 'uses' => 'SettingsController@getBackups' ] );
                Route::get( 'download/{filename}',
                    [ 'as' => 'settings/download-file', 'uses' => 'SettingsController@downloadFile' ] );
            } );

            # Manufacturers
            Route::group( [ 'prefix' => 'manufacturers' ], function () {

                Route::get( '/', [ 'as' => 'manufacturers', 'uses' => 'ManufacturersController@getIndex' ] );
                Route::get( 'create',
                    [ 'as' => 'create/manufacturer', 'uses' => 'ManufacturersController@getCreate' ] );
                Route::post( 'create', 'ManufacturersController@postCreate' );
                Route::get( '{manufacturerId}/edit',
                    [ 'as' => 'update/manufacturer', 'uses' => 'ManufacturersController@getEdit' ] );
                Route::post( '{manufacturerId}/edit', 'ManufacturersController@postEdit' );
                Route::get( '{manufacturerId}/delete',
                    [ 'as' => 'delete/manufacturer', 'uses' => 'ManufacturersController@getDelete' ] );
                Route::get( '{manufacturerId}/view',
                    [ 'as' => 'view/manufacturer', 'uses' => 'ManufacturersController@getView' ] );
            } );

            # Suppliers
            Route::group( [ 'prefix' => 'suppliers' ], function () {

                Route::get( '/', [ 'as' => 'suppliers', 'uses' => 'SuppliersController@getIndex' ] );
                Route::get( 'create', [ 'as' => 'create/supplier', 'uses' => 'SuppliersController@getCreate' ] );
                Route::post( 'create', 'SuppliersController@postCreate' );
                Route::get( '{supplierId}/edit',
                    [ 'as' => 'update/supplier', 'uses' => 'SuppliersController@getEdit' ] );
                Route::post( '{supplierId}/edit', 'SuppliersController@postEdit' );
                Route::get( '{supplierId}/delete',
                    [ 'as' => 'delete/supplier', 'uses' => 'SuppliersController@getDelete' ] );
                Route::get( '{supplierId}/view', [ 'as' => 'view/supplier', 'uses' => 'SuppliersController@getView' ] );
            } );

            # Categories
            Route::group( [ 'prefix' => 'categories' ], function () {

                Route::get( 'create', [ 'as' => 'create/category', 'uses' => 'CategoriesController@getCreate' ] );
                Route::post( 'create', 'CategoriesController@postCreate' );
                Route::get( '{categoryId}/edit',
                    [ 'as' => 'update/category', 'uses' => 'CategoriesController@getEdit' ] );
                Route::post( '{categoryId}/edit', 'CategoriesController@postEdit' );
                Route::get( '{categoryId}/delete',
                    [ 'as' => 'delete/category', 'uses' => 'CategoriesController@getDelete' ] );
                Route::get( '{categoryId}/view',
                    [ 'as' => 'view/category', 'uses' => 'CategoriesController@getView' ] );
                Route::get( '/', [ 'as' => 'categories', 'uses' => 'CategoriesController@getIndex' ] );
            } );

            # Depreciations
            Route::group( [ 'prefix' => 'depreciations' ], function () {

                Route::get( '/', [ 'as' => 'depreciations', 'uses' => 'DepreciationsController@getIndex' ] );
                Route::get( 'create',
                    [ 'as' => 'create/depreciations', 'uses' => 'DepreciationsController@getCreate' ] );
                Route::post( 'create', 'DepreciationsController@postCreate' );
                Route::get( '{depreciationId}/edit',
                    [ 'as' => 'update/depreciations', 'uses' => 'DepreciationsController@getEdit' ] );
                Route::post( '{depreciationId}/edit', 'DepreciationsController@postEdit' );
                Route::get( '{depreciationId}/delete',
                    [ 'as' => 'delete/depreciations', 'uses' => 'DepreciationsController@getDelete' ] );
            } );

            # Locations
            Route::group( [ 'prefix' => 'locations' ], function () {

                Route::get( '/', [ 'as' => 'locations', 'uses' => 'LocationsController@getIndex' ] );
                Route::get( 'create', [ 'as' => 'create/location', 'uses' => 'LocationsController@getCreate' ] );
                Route::post( 'create', 'LocationsController@postCreate' );
                Route::get( '{locationId}/edit',
                    [ 'as' => 'update/location', 'uses' => 'LocationsController@getEdit' ] );
                Route::post( '{locationId}/edit', 'LocationsController@postEdit' );
                Route::get( '{locationId}/delete',
                    [ 'as' => 'delete/location', 'uses' => 'LocationsController@getDelete' ] );
            } );

            # Status Labels
            Route::group( [ 'prefix' => 'statuslabels' ], function () {

                Route::get( '/', [ 'as' => 'statuslabels', 'uses' => 'StatuslabelsController@getIndex' ] );
                Route::get( 'create', [ 'as' => 'create/statuslabel', 'uses' => 'StatuslabelsController@getCreate' ] );
                Route::post( 'create', 'StatuslabelsController@postCreate' );
                Route::get( '{statuslabelId}/edit',
                    [ 'as' => 'update/statuslabel', 'uses' => 'StatuslabelsController@getEdit' ] );
                Route::post( '{statuslabelId}/edit', 'StatuslabelsController@postEdit' );
                Route::get( '{statuslabelId}/delete',
                    [ 'as' => 'delete/statuslabel', 'uses' => 'StatuslabelsController@getDelete' ] );
            } );

        } );

        # User Management
        Route::group( [ 'prefix' => 'users' ], function () {

            Route::get( 'ldap', ['as' => 'ldap/user', 'uses' => 'UsersController@getLDAP' ] );
            Route::post( 'ldap', 'UsersController@postLDAP' );
            
            Route::get( 'create', [ 'as' => 'create/user', 'uses' => 'UsersController@getCreate' ] );
            Route::post( 'create', 'UsersController@postCreate' );
            Route::get( 'import', [ 'as' => 'import/user', 'uses' => 'UsersController@getImport' ] );
            Route::post( 'import', 'UsersController@postImport' );
            Route::get( '{userId}/edit', [ 'as' => 'update/user', 'uses' => 'UsersController@getEdit' ] );
            Route::post( '{userId}/edit', 'UsersController@postEdit' );
            Route::get( '{userId}/clone', [ 'as' => 'clone/user', 'uses' => 'UsersController@getClone' ] );
            Route::post( '{userId}/clone', 'UsersController@postCreate' );
            Route::get( '{userId}/delete', [ 'as' => 'delete/user', 'uses' => 'UsersController@getDelete' ] );
            Route::get( '{userId}/restore', [ 'as' => 'restore/user', 'uses' => 'UsersController@getRestore' ] );
            Route::get( '{userId}/view', [ 'as' => 'view/user', 'uses' => 'UsersController@getView' ] );
            Route::get( '{userId}/unsuspend', [ 'as' => 'unsuspend/user', 'uses' => 'UsersController@getUnsuspend' ] );
            Route::post( '{userId}/upload', [ 'as' => 'upload/user', 'uses' => 'UsersController@postUpload' ] );
            Route::get( '{userId}/deletefile/{fileId}',
                [ 'as' => 'delete/userfile', 'uses' => 'UsersController@getDeleteFile' ] );
            Route::get( '{userId}/showfile/{fileId}',
                [ 'as' => 'show/userfile', 'uses' => 'UsersController@displayFile' ] );

            Route::post( 'bulkedit',
                [
                    'as'   => 'users/bulkedit',
                    'uses' => 'UsersController@postBulkEdit'
                ] );
            Route::post( 'bulksave',
                [
                    'as'   => 'users/bulksave',
                    'uses' => 'UsersController@postBulkSave'
                ] );

            Route::get( '/', [ 'as' => 'users', 'uses' => 'UsersController@getIndex' ] );

        } );

        # Group Management
        Route::group( [ 'prefix' => 'groups' ], function () {

            Route::get( '/', [ 'as' => 'groups', 'uses' => 'GroupsController@getIndex' ] );
            Route::get( 'create', [ 'as' => 'create/group', 'uses' => 'GroupsController@getCreate' ] );
            Route::post( 'create', 'GroupsController@postCreate' );
            Route::get( '{groupId}/edit', [ 'as' => 'update/group', 'uses' => 'GroupsController@getEdit' ] );
            Route::post( '{groupId}/edit', 'GroupsController@postEdit' );
            Route::get( '{groupId}/delete', [ 'as' => 'delete/group', 'uses' => 'GroupsController@getDelete' ] );
            Route::get( '{groupId}/restore', [ 'as' => 'restore/group', 'uses' => 'GroupsController@getRestore' ] );
            Route::get( '{groupId}/view', [ 'as' => 'view/group', 'uses' => 'GroupsController@getView' ] );
        } );

        # Dashboard
        Route::get( '/', [ 'as' => 'admin', 'uses' => 'DashboardController@getIndex' ] );

    } );

    /*
    |--------------------------------------------------------------------------
    | Authentication and Authorization Routes
    |--------------------------------------------------------------------------
    |
    |
    |
    */

    Route::group( [ 'prefix' => 'auth' ], function () {

        # Login
        Route::get( 'signin', [ 'as' => 'signin', 'uses' => 'AuthController@getSignin' ] );
        Route::post( 'signin', 'AuthController@postSignin' );

        # Register
        #Route::get('signup', array('as' => 'signup', 'uses' => 'AuthController@getSignup'));
        Route::post( 'signup', 'AuthController@postSignup' );

        # Account Activation
        Route::get( 'activate/{activationCode}', [ 'as' => 'activate', 'uses' => 'AuthController@getActivate' ] );

        # Forgot Password
        Route::get( 'forgot-password', [ 'as' => 'forgot-password', 'uses' => 'AuthController@getForgotPassword' ] );
        Route::post( 'forgot-password', 'AuthController@postForgotPassword' );

        # Forgot Password Confirmation
        Route::get( 'forgot-password/{passwordResetCode}',
            [ 'as' => 'forgot-password-confirm', 'uses' => 'AuthController@getForgotPasswordConfirm' ] );
        Route::post( 'forgot-password/{passwordResetCode}', 'AuthController@postForgotPasswordConfirm' );

        # Logout
        Route::get( 'logout', [ 'as' => 'logout', 'uses' => 'AuthController@getLogout' ] );

    } );

    /*
    |--------------------------------------------------------------------------
    | Account Routes
    |--------------------------------------------------------------------------
    |
    |
    |
    */
    Route::group( [ 'prefix' => 'account', 'before' => 'auth', 'namespace' => 'Controllers\Account' ], function () {

        # Profile
        Route::get( 'profile', [ 'as' => 'profile', 'uses' => 'ProfileController@getIndex' ] );
        Route::post( 'profile', 'ProfileController@postIndex' );

        # Change Password
        Route::get( 'change-password', [ 'as' => 'change-password', 'uses' => 'ChangePasswordController@getIndex' ] );
        Route::post( 'change-password', 'ChangePasswordController@postIndex' );

        # View Assets
        Route::get( 'view-assets', [ 'as' => 'view-assets', 'uses' => 'ViewAssetsController@getIndex' ] );

        # Change Email
        Route::get( 'change-email', [ 'as' => 'change-email', 'uses' => 'ChangeEmailController@getIndex' ] );
        Route::post( 'change-email', 'ChangeEmailController@postIndex' );

        # Accept Asset
        Route::get( 'accept-asset/{logID}',
            [ 'as' => 'account/accept-assets', 'uses' => 'ViewAssetsController@getAcceptAsset' ] );
        Route::post( 'accept-asset/{logID}',
            [ 'as' => 'account/asset-accepted', 'uses' => 'ViewAssetsController@postAcceptAsset' ] );

        # Profile
        Route::get( 'requestable-assets',
            [ 'as' => 'requestable-assets', 'uses' => 'ViewAssetsController@getRequestableIndex' ] );
        Route::get( 'request-asset/{assetId}',
            [ 'as' => 'account/request-asset', 'uses' => 'ViewAssetsController@getRequestAsset' ] );

        # Account Dashboard
        Route::get( '/', [ 'as' => 'account', 'uses' => 'DashboardController@getIndex' ] );

    } );

    /*
    |--------------------------------------------------------------------------
    | Application Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register all of the routes for an application.
    | It's a breeze. Simply tell Laravel the URIs it should respond to
    | and give it the Closure to execute when that URI is requested.
    |
    */

    Route::group( [ 'before' => 'reporting-auth', 'namespace' => 'Controllers\Admin' ], function () {

        Route::get( 'reports/depreciation',
            [ 'as' => 'reports/depreciation', 'uses' => 'ReportsController@getDeprecationReport' ] );
        Route::get( 'reports/export/depreciation',
            [ 'as' => 'reports/export/depreciation', 'uses' => 'ReportsController@exportDeprecationReport' ] );
        Route::get( 'reports/asset_maintenances',
            [ 'as' => 'reports/asset_maintenances', 'uses' => 'ReportsController@getAssetMaintenancesReport' ] );
        Route::get( 'reports/export/asset_maintenances',
            [
                'as'   => 'reports/export/asset_maintenances',
                'uses' => 'ReportsController@exportAssetMaintenancesReport'
            ] );
        Route::get( 'reports/fixtures',
            [ 'as' => 'reports/fixtures', 'uses' => 'ReportsController@getFixtureReport' ] );
        Route::get( 'reports/export/fixtures',
            [ 'as' => 'reports/export/fixtures', 'uses' => 'ReportsController@exportFixtureReport' ] );
        Route::get( 'reports/assets', [ 'as' => 'reports/assets', 'uses' => 'ReportsController@getAssetsReport' ] );
        Route::get( 'reports/export/assets',
            [ 'as' => 'reports/export/assets', 'uses' => 'ReportsController@exportAssetReport' ] );
        Route::get( 'reports/tools', [ 'as' => 'reports/tools', 'uses' => 'ReportsController@getToolReport' ] );
        Route::get( 'reports/export/tools',
            [ 'as' => 'reports/export/tools', 'uses' => 'ReportsController@exportToolReport' ] );
        Route::get( 'reports/custom', [ 'as' => 'reports/custom', 'uses' => 'ReportsController@getCustomReport' ] );
        Route::post( 'reports/custom', 'ReportsController@postCustom' );

        Route::get( 'reports/activity',
            [ 'as' => 'reports/activity', 'uses' => 'ReportsController@getActivityReport' ] );
        Route::get( 'reports/unaccepted_assets',
            [ 'as' => 'reports/unaccepted_assets', 'uses' => 'ReportsController@getAssetAcceptanceReport' ] );
        Route::get( 'reports/export/unaccepted_assets',
            [ 'as' => 'reports/export/unaccepted_assets', 'uses' => 'ReportsController@exportAssetAcceptanceReport' ] );
		Route::get( 'reports/checkedout', 
			[ 'as'	=> 'reports/checkedout',
			  'uses'=> 'ReportsController@getCheckedOutReport' 
			] );
    } );

    Route::get( '/',
        [ 'as' => 'home', 'before' => 'admin-auth', 'uses' => 'Controllers\Admin\DashboardController@getIndex' ] );
