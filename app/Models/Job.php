<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Job extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'brief',
        'description',
        'organization',
        'location',
        'remote',
        'link',
        'source',
        'logo',
        'date_posted',
        'job_link',
        'ext_id',
        'edit_link',
        'co_mail',
        'co_twitter',
        'primary_tag',
        'tags',
        'howto',
        'salary',
        'show_logo',
        'bump',
        'match',
        'yellow_it',
        'brand_color',
        'sticky_day',
        'sticky_week',
        'sticky_month',
        'is_visible',
        'selected_color',
        'state',
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];
}
