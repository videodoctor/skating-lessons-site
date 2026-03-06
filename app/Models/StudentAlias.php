<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAlias extends Model
{
    protected $fillable = ['student_id', 'alias'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
