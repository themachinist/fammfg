<?php

use Cartalyst\Sentry\Users\Eloquent\User as SentryUserModel;

class User extends SentryUserModel
{
    /**
     * Indicates if the model should soft delete.
     *
     * @var bool
     */
    use SoftDeletingTrait;
	protected $dates = ['deleted_at'];


    /**
     * Returns the user full name, it simply concatenates
     * the user first and last name.
     *
     * @return string
     */
    public function fullName()
    {
        return "{$this->first_name} {$this->last_name}";
    }


    /**
     * Returns the user Gravatar image url.
     *
     * @return string
     */
    public function gravatar()
    {
        // Generate the Gravatar hash
        $gravatar = md5(strtolower(trim($this->email)));

        // Return the Gravatar url
        return "//gravatar.com/avatar/{$gravatar}";
    }

    public function assets()
    {
        return $this->hasMany('Asset', 'assigned_to')->withTrashed();
    }

    public function tools()
    {
        return $this->belongsToMany('Tool', 'tools_users', 'assigned_to','tool_id')->withPivot('id')->withTrashed();
    }

    public function consumables()
   {
       return $this->belongsToMany('Consumable', 'consumables_users', 'assigned_to','consumable_id')->withPivot('id')->withTrashed();
   }

    public function licenses()
    {
        return $this->belongsToMany('License', 'license_seats', 'assigned_to', 'license_id')->withPivot('id');
    }

    /**
    * Get action logs for this user
    */
    public function userlog()
    {
        return $this->hasMany('Actionlog','checkedout_to')->orderBy('created_at', 'DESC')->withTrashed();
    }

    /**
    * Get the asset's location based on the assigned user
    **/
    public function userloc()
    {
        return $this->belongsTo('Location','location_id')->withTrashed();
    }

    /**
    * Get the user's manager based on the assigned user
    **/
    public function manager()
    {
        return $this->belongsTo('User','manager_id')->withTrashed();
    }


    public function accountStatus()
    {
        if ($this->sentryThrottle) {
    	    if ($this->sentryThrottle->suspended==1) {
    		 	return 'suspended';
    		} elseif ($this->sentryThrottle->banned==1) {
    		 	return 'banned';
    	 	} else {
    		 	return false;
    	 	}
        } else {
            return false;
        }
    }

    public function assetlog()
    {
        return $this->hasMany('Asset','id')->withTrashed();
    }

    /**
    * Get uploads for this asset
    */
    public function uploads()
    {
        return $this->hasMany('Actionlog','asset_id')
            ->where('asset_type', '=', 'user')
            ->where('action_type', '=', 'uploaded')
            ->whereNotNull('filename')
            ->orderBy('created_at', 'desc');
    }

    public function sentryThrottle() {
	    return $this->hasOne('Throttle');
    }

    public function scopeGetDeleted($query)
	{
		return $query->withTrashed()->whereNotNull('deleted_at');
	}

	public function scopeGetNotDeleted($query)
	{
		return $query->whereNull('deleted_at');
	}

    /**
    * Override the SentryUser getPersistCode method for
    * multiple logins at one time
    **/
    public function getPersistCode()
    {

        if (!Config::get('session.multi_login') || (!$this->persist_code))
        {
            $this->persist_code = $this->getRandomString();

            // Our code got hashed
            $persistCode = $this->persist_code;
            $this->save();
            return $persistCode;
        }
        return $this->persist_code;
    }


}
