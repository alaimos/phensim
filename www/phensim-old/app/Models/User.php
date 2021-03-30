<?php

namespace App\Models;

use App\Exceptions\SecurityException;
use Auth;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laratrust\Contracts\Ownable;
use Laratrust\Traits\LaratrustUserTrait;
use Laravel\Passport\HasApiTokens;

/**
 * App\Models\User
 *
 * @property int                                                                                                            $id
 * @property string                                                                                                         $name
 * @property string                                                                                                         $email
 * @property string                                                                                                         $password
 * @property string                                                                                                         $affiliation
 * @property string                                                                                                         $secret
 * @property string                                                                                                         $remember_token
 * @property \Carbon\Carbon                                                                                                 $created_at
 * @property \Carbon\Carbon                                                                                                 $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Passport\Client[]                                       $clients
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Job[]                                                $jobs
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Permission[]                                         $permissions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Role[]                                               $roles
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Passport\Token[]                                        $tokens
 * @method static Builder|User whereAffiliation($value)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereName($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereRoleIs($role = '')
 * @method static Builder|User whereSecret($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable implements Ownable
{
    use LaratrustUserTrait, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'remember_token',
        'secret',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'secret',
    ];

    /**
     * Jobs Models HasMany Relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class, 'user_id', 'id');
    }

    /**
     * Checks if the user is an administrator
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('administrator');
    }

    /**
     * Checks if an user can create a user
     *
     * @param null|\App\Models\User $user
     *
     * @return bool
     */
    public static function canBeCreated(User $user = null): bool
    {
        if ($user === null) {
            $user = Auth::user();
        }
        if ($user === null) {
            return false;
        }

        return $user->hasRole('administrator') || $user->can('create-users');
    }

    /**
     * Checks if an user can update this user
     *
     * @param \App\Models\User|null $user
     *
     * @return bool
     */
    public function canBeUpdated(User $user = null): bool
    {
        if ($user === null) {
            $user = Auth::user();
        }
        if ($user === null) {
            return false;
        }

        return $user->hasRole('administrator') || $user->canAndOwns('update-users', $this);
    }

    /**
     * Checks if an user can delete this user
     *
     * @param \App\Models\User|null $user
     *
     * @return bool
     */
    public function canBeDeleted(User $user = null): bool
    {
        if ($user === null) {
            $user = Auth::user();
        }
        if ($user === null) {
            return false;
        }

        return $user->hasRole('administrator') || $user->canAndOwns('delete-users', $this);
    }

    /**
     * Gets the owner key value inside the model or object
     *
     * @return mixed
     */
    public function ownerKey()
    {
        return $this->id;
    }

    /**
     * Save the model to the database.
     *
     * @param array $options
     *
     * @return mixed
     */
    public function save(array $options = [])
    {
        if (!$this->exists) {
            $this->remember_token = null;
            $this->secret = bcrypt(str_random(32));
        }

        return parent::save($options);
    }

    /**
     * Delete the model from the database.
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function delete()
    {
        if (!$this->canBeDeleted()) {
            throw new SecurityException('The current user is not allowed to delete this object');
        }

        return parent::delete();
    }


}
