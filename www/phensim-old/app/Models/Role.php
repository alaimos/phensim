<?php

namespace App\Models;

use Laratrust\LaratrustRole;

/**
 * App\Models\Role
 *
 * @property int                                                                    $id
 * @property string                                                                 $name
 * @property string                                                                 $display_name
 * @property string                                                                 $description
 * @property \Carbon\Carbon                                                         $created_at
 * @property \Carbon\Carbon                                                         $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Permission[] $permissions
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Role whereDescription($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Role whereDisplayName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Role whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Role whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Role whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Role extends LaratrustRole
{
    //
}
