<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Organism
 *
 * @property int                                                                 $id
 * @property string                                                              $accession
 * @property string                                                              $name
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Edge[]    $edges
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Node[]    $nodes
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Pathway[] $pathways
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Organism whereAccession($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Organism whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Organism whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Organism whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Organism whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Organism extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['accession', 'name'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * References all edges belonging to this organism
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function edges()
    {
        return $this->hasMany('App\Models\Edge', 'organism_id', 'id');
    }

    /**
     * References all pathways belonging to this organism
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pathways()
    {
        return $this->hasMany('App\Models\Pathway', 'organism_id', 'id');
    }
}
