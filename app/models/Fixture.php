<?php

class Fixture extends Depreciable
{
	use SoftDeletingTrait;
    protected $dates = ['deleted_at'];

    public $timestamps = true;

    protected $guarded = 'id';
    protected $table = 'fixtures';
    protected $rules = array(
            'name'			=> 'required|alpha_space|min:3|max:255',
            'serial'		=> 'required|min:5',
            'copies'		=> 'required|min:1|max:10000|integer',
            'fixture_email'	=> 'email|min:0|max:120',
            'fixture_name'  => 'alpha_space|min:0|max:100',
            'note'			=> 'alpha_space',
            'notes'			=> 'alpha_space|min:0',
        );

    /**
     * Get the assigned user
     */
    public function assignedusers()
    {
        return $this->belongsToMany('User','fixture_copies','assigned_to','fixture_id');
    }

    /**
    * Get asset logs for this asset
    */
    public function assetlog()
    {
        return $this->hasMany('Actionlog','asset_id')
            ->where('asset_type', '=', 'software')
            ->orderBy('created_at', 'desc');
    }

    /**
    * Get uploads for this asset
    */
    public function uploads()
    {
        return $this->hasMany('Actionlog','asset_id')
            ->where('asset_type', '=', 'software')
            ->where('action_type', '=', 'uploaded')
            ->whereNotNull('filename')
            ->orderBy('created_at', 'desc');
    }


    /**
    * Get admin user for this asset
    */
    public function adminuser()
    {
        return $this->belongsTo('User','user_id');
    }

    /**
    * Get total fixtures
    */
    public static function assetcount()
    {
        return DB::table('fixture_copies')
                    ->whereNull('deleted_at')
                    ->count();
    }

    /**
    * Get total fixtures
    */
    public function totalCopiesByFixtureID()
    {
        return DB::table('fixture_copies')
		->where('fixture_id', '=', $this->id)
		->whereNull('deleted_at')
		->count();
	}

    /**
    * Get total fixtures not checked out
    */
    public static function availassetcount()
    {
        return DB::table('fixture_copies')
                    ->whereNull('assigned_to')
                    ->whereNull('asset_id')
                    ->whereNull('deleted_at')
                    ->count();
    }

    /**
     * Get the number of available copies
     */
    public function availcount()
    {
        return DB::table('fixture_copies')
                    ->whereNull('assigned_to')
                    ->whereNull('asset_id')
                    ->where('fixture_id', '=', $this->id)
                    ->whereNull('deleted_at')
                    ->count();
    }

    /**
     * Get the number of assigned copies
     *
     */
    public function assignedcount()
    {
		return FixtureCopy::where('fixture_id', '=', $this->id)
			->where( function ( $query )
			{
			$query->whereNotNull('assigned_to')
			->orWhereNotNull('asset_id');
			})
		->count();
    }

    public function remaincount()
    {
    	$total = $this->totalCopiesByFixtureID();
        $taken =  $this->assignedcount();
        $diff =   ($total - $taken);
        return $diff;
    }

    /**
     * Get the total number of copies
     */
    public function totalcount()
    {
        $avail =  $this->availcount();
        $taken =  $this->assignedcount();
        $diff =   ($avail + $taken);
        return $diff;
    }

    /**
     * Get fixture copy data
     */
    public function fixturecopies()
    {
        return $this->hasMany('FixtureCopy');
    }

    public function supplier()
    {
        return $this->belongsTo('Supplier','supplier_id');
    }

	public function freeCopy()
    {
        $copy = FixtureCopy::where('fixture_id','=',$this->id)
                    ->whereNull('deleted_at')
                    ->whereNull('assigned_to')
                    ->whereNull('asset_id')
                    ->first();
        return $copy->id;
    }

	public static function getExpiringFixtures($days = 60) {
	    return Fixture::whereNotNull('expiration_date')
		->whereNull('deleted_at')
		->whereRaw(DB::raw( 'DATE_SUB(`expiration_date`,INTERVAL '.$days.' DAY) <= DATE(NOW()) ' ))
		->where('expiration_date','>',date("Y-m-d"))
		->orderBy('expiration_date', 'ASC')
		->get();
    }
}
