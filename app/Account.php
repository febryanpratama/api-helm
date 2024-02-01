<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $primaryKey = 'ID';

    protected $guarded = [];

    public $timestamps = false;

    protected $casts = ['group'];

    public function getGroupAttribute()
    {
        if ($this->Type1 && $this->Type2 && $this->Type3 && $this->Type4) {
            $data = [$this->Type1, $this->Type2, $this->Type3, $this->Type4];

            return implode(' >> ', $data);
        }

        if ($this->Type1 && $this->Type2 && $this->Type3) {
            $data = [$this->Type1, $this->Type2, $this->Type3];

            return implode(' >> ', $data);
        }

        if ($this->Type1 && $this->Type2) {
            $data = [$this->Type1, $this->Type2];

            return implode(' >> ', $data);
        }

        return $this->Type1;

    }

    public function formatData()
    {
        $row['id'] = $this->ID;
        $row['name'] = $this->Name;
        $row['other_name'] = $this->OtherName;
        $row['code'] = $this->Code;
        $row['group'] = $this->group;
        $row['type'] = $this->CurrType;

        return $row;
    }
}
