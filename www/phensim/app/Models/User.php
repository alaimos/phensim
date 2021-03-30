<?php
/**
 * PHENSIM: Phenotype Simulator
 * @version 2.0.0.2
 * @author  Salvatore Alaimo, Ph.D.
 */

namespace App\Models;

//@todo add this use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'affiliation',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'is_admin',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'is_admin' => false,
    ];

    /**
     * User-to-Simulation relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function simulations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Simulation::class);
    }

    /**
     * Counts owned simulations by state
     *
     * @param  int  $status
     *
     * @return int
     */
    public function countSimulationsByState(int $status): int
    {
        return (!in_array($status, Simulation::VALID_STATES)) ? -1 : $this->simulations()->where('status', $status)->count();
    }
}
