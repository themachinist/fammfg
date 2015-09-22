<?php

    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Log;

    class Actionlog extends Eloquent
    {

        use SoftDeletingTrait;
        protected $dates = [ 'deleted_at' ];

        protected $table      = 'asset_logs';
        public    $timestamps = true;
        protected $fillable   = [ 'created_at' ];

        public function assetlog()
        {

            return $this->belongsTo( 'Asset', 'asset_id' )
                        ->withTrashed();
        }

        public function uploads()
        {

            return $this->belongsTo( 'Asset', 'asset_id' )
                        ->where( 'action_type', '=', 'uploaded' )
                        ->withTrashed();
        }

        public function licenselog()
        {

            return $this->belongsTo( 'License', 'asset_id' )
                        ->withTrashed();
        }

        public function toollog()
        {

            return $this->belongsTo( 'Tool', 'tool_id' )
                        ->withTrashed();
        }

        public function consumablelog()
        {

            return $this->belongsTo( 'Consumable', 'consumable_id' )
                        ->withTrashed();
        }

        public function adminlog()
        {

            return $this->belongsTo( 'User', 'user_id' )
                        ->withTrashed();
        }

        public function userlog()
        {

            return $this->belongsTo( 'User', 'checkedout_to' )
                        ->withTrashed();
        }

        public function childlogs()
        {

            return $this->hasMany( 'ActionLog', 'thread_id' );
        }

        public function parentlog()
        {

            return $this->belongsTo( 'ActionLog', 'thread_id' );
        }

        /**
         * Check if the file exists, and if it does, force a download
         **/
        public function get_src()
        {

            $file = app_path() . '/private_uploads/' . $this->filename;

            return $file;

        }

        /**
         * Get the parent category name
         */
        public function logaction( $actiontype )
        {

            $this->action_type = $actiontype;

            if ($this->save()) {
                return true;
            } else {
                return false;
            }
        }

		/**
		 * getListingOfCheckedOutItems
		 *
		 * @return mixed
		 * @author David Winslow <the@machini.st>
		 * @todo not the "eloquent" way to solve this problem
		 */
		// need to add a limit clause to the query
		public static function getListingOfCheckedOutItems(){
			$checkedout_tools = 
				DB::table( 'tools_users' )
					->select(	'tools_users.user_id',
								'tools_users.tool_id',
								'tools_users.assigned_to', 
								'tools.name', 
								'users.first_name', 
								'users.last_name',
								DB::raw('coalesce(max(`asset_logs`.`created_at`)) as `created_at`'),
								'asset_logs.asset_type',
								'asset_logs.note')
					->join( 'tools', 'tools.id', '=', 'tools_users.tool_id' )
					->leftJoin( 'users', 'users.id', '=', 'tools_users.assigned_to' )
					->leftJoin( 'asset_logs', 'asset_logs.tool_id', '=', 'tools_users.tool_id' )
					->havingRaw( 'tools_users.assigned_to is not null' );
			$checkedout_consumables =
				DB::table( 'consumables_users' )
					->select(	'consumables_users.user_id', 
								'consumables_users.consumable_id',
								'consumables_users.assigned_to',
								'consumables.name', 
								'users.first_name', 
								'users.last_name',
								DB::raw('coalesce(max(`asset_logs`.`created_at`)) as `created_at`'),
								'asset_logs.asset_type',
								'asset_logs.note')
					->join(	'consumables', 'consumables.id', '=', 'consumables_users.consumable_id' )
					->leftJoin( 'users', 'users.id', '=', 'consumables_users.assigned_to' )
					->leftJoin( 'asset_logs', 'asset_logs.consumable_id', '=', 'consumables_users.consumable_id' )
					->havingRaw( 'consumables_users.assigned_to is not null' );
			$checkedout_agg = 
				$checkedout_tools
					->union($checkedout_consumables)
					->get();
			return $checkedout_agg;
		}


        /**
         * getListingOfActionLogsChronologicalOrder
         *
         * @return mixed
         * @author  Vincent Sposato <vincent.sposato@gmail.com>
         * @version v1.0
         */
        public function getListingOfActionLogsChronologicalOrder()
        {

            return DB::table( 'asset_logs' )
                     ->select( '*' )
                     ->orderBy( 'asset_id', 'asc' )
                     ->orderBy( 'created_at', 'asc' )
                     ->get();
        }

        /**
         * getLatestCheckoutActionForAssets
         *
         * @return mixed
         * @author  Vincent Sposato <vincent.sposato@gmail.com>
         * @version v1.0
         */
        public function getLatestCheckoutActionForAssets()
        {

            return DB::table( 'asset_logs' )
                     ->select( DB::raw( 'asset_id, MAX(created_at) as last_created' ) )
                     ->where( 'action_type', '=', 'checkout' )
                     ->groupBy( 'asset_id' )
                     ->get();
		}

        /**
         * scopeCheckoutWithoutAcceptance
         *
         * @param $query
         *
         * @return mixed
         * @author  Vincent Sposato <vincent.sposato@gmail.com>
         * @version v1.0
         */
        public function scopeCheckoutWithoutAcceptance( $query )
        {

            return $query->where( 'action_type', '=', 'checkout' )
                         ->where( 'accepted_id', '=', null );
        }
}
?>
