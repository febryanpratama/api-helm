<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = "company";
	protected $primaryKey = "ID";
	public $timestamps = false;

    protected $guarded = [];

    public function admin()
    {
        return $this->hasMany(User::class, 'company_id')->where('users.role_id', 1);
    }

    public function division()
    {
        return $this->hasMany(Division::class, 'IDCompany')->where('IDCompany', auth()->user()->company->ID)->orderBy('ID', 'DESC');
    }

    public function majors()
    {
        return $this->hasMany(Majors::class, 'IDCompany')->where('IDCompany', auth()->user()->company->ID)->orderBy('ID', 'DESC');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'company_id');
    }

    public function officePhotos()
    {
        return $this->hasMany(OfficePhoto::class, 'company_id');
    }

    public function city()
    {
        return $this->belongsTo(MasterLocation::class, 'city_id', 'kota_id');
    }

    public function portfolio()
    {
        return $this->hasMany(Portfolio::class, 'company_id');
    }

    public function TeamPhoto()
    {
        return $this->hasMany(TeamPhoto::class, 'company_id');
    }
}
