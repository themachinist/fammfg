<?php

class FixtureSeat extends Elegant
{
	use SoftDeletingTrait;
    protected $dates = ['deleted_at'];
    protected $guarded = 'id';
    protected $table = 'fixture_seats';

    public function fixture()
    {
        return $this->belongsTo('Fixture','fixture_id');
    }

    public function user()
    {
        return $this->belongsTo('User','assigned_to')->withTrashed();
    }

    public function asset()
    {
        return $this->belongsTo('Asset','asset_id')->withTrashed();
    }
}
